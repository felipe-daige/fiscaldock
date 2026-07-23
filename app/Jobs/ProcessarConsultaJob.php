<?php

namespace App\Jobs;

use App\Services\Consultas\ComprovanteArquivador;
use App\Services\Consultas\Contracts\ConsultaProvider;
use App\Services\Consultas\Contracts\Fonte;
use App\Services\Consultas\Dto\RespostaProvider;
use App\Services\Consultas\Dto\ResultadoFonte;
use App\Services\Consultas\FonteRegistry;
use App\Services\Consultas\Fontes\CndMunicipalFonte;
use App\Services\Consultas\InscricaoMunicipalResolver;
use App\Services\Consultas\Persistencia\PersistenciaCnpj;
use App\Services\Consultas\ThrottleProvider;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ProcessarConsultaJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $loteId,
        public string $alvoTipo,   // 'participante' | 'cliente'
        public int $alvoId,
        public int $userId,
        public string $tabId,
        public array $consultasIncluidas,
        public array $alvo,
        public array $etapas,
        // Posição (1-based) deste alvo no lote e total de alvos — para o progresso ser GLOBAL
        // (não resetar a 0% a cada empresa num lote multi-CNPJ). Default = lote de 1 alvo.
        public int $alvoIndice = 1,
        public int $totalAlvos = 1,
        // Reconsulta: quando não-null, só estas fontes (por chave) são processadas — o resto do
        // plano é pulado. Usado pelo retry pra re-bater a InfoSimples apenas nas fontes que
        // falharam, sem custo nas que já deram certo. Default null = processa o plano inteiro.
        public ?array $somenteFontes = null,
        // Faixa da barra ocupada por esta fase (default 0→100 = a Consulta CNPJ inteira, sem
        // mudança de comportamento). O clearance completo usa 50→95, porque os documentos já
        // consumiram a primeira metade — sem isso a barra voltava pra 0 na fase da contraparte.
        public int $pctBase = 0,
        public int $pctSpan = 100,
        // Emitir a etapa `inicializacao` (1) ao começar o alvo. No clearance completo isso é
        // desligado: a etapa 1 já foi cumprida pela fase dos documentos, e reemiti-la faria o strip
        // VOLTAR pra "Preparando consulta" no meio da consulta da contraparte.
        public bool $emitirInicializacao = true,
        // Lote avulso por fontes (advocacia): PREÇO DE VENDA por chave de fonte, em R$. O estorno
        // de falha estornável devolve o que foi COBRADO do usuário (ex.: R$ 1,00/fonte), não o
        // custo interno InfoSimples (consultas.fontes.*). Null = lote de plano (comportamento
        // original: estorna o custo interno da fonte).
        public ?array $precosVenda = null,
    ) {}

    public function handle(
        FonteRegistry $registry,
        ThrottleProvider $throttle,
        PersistenciaCnpj $persistencia,
        ComprovanteArquivador $comprovanteArquivador,
        InscricaoMunicipalResolver $imResolver,
    ): void {
        $total = count($this->etapas);
        // Etapa inicial: inicializacao. Suprimida quando esta é a 2ª fase de um fluxo maior
        // (clearance completo) — ver $emitirInicializacao.
        if ($this->emitirInicializacao) {
            [$nIni, $lIni] = $this->etapaPorChave('inicializacao', 'Preparando consulta');
            $this->progresso(
                etapa: $nIni, total: $total, label: $lIni, status: 'processando',
                progresso: $this->pctGlobal(0, 1), mensagem: $this->prefixoAlvo().$lIni,
            );
        }

        // Alvo mutável: a UF autoritativa vem do cadastro (minhareceita) e alimenta as
        // fontes UF-dependentes (ex: CND Estadual). O cadastro é a 1ª fonte de todo plano.
        $alvo = $this->alvo;

        // CPF do SOLICITANTE (requerente) das certidões judiciais que o exigem (CEAT TRT24). É a
        // identidade de QUEM PEDE — o dono da conta, não o alvo consultado. Injetado uma vez aqui
        // (choke point de todo lote: plano e avulso) a partir do usuário; a Fonte lê do alvo.
        if (empty($alvo['cpf_solicitante'])) {
            $cpfSolicitante = \App\Models\User::where('id', $this->userId)->value('cpf');
            if (! empty($cpfSolicitante)) {
                $alvo['cpf_solicitante'] = \App\Support\Cpf::digitos($cpfSolicitante);
            }
        }

        // Ordena as fontes pela ETAPA (cadastrais→federais→estaduais) para que o
        // progresso avance de forma monotônica. O cadastro (etapa 2) cai naturalmente em
        // primeiro, garantindo a captura de UF/município antes das fontes UF-dependentes.
        $tipoPessoa = strtoupper((string) ($alvo['tipo_pessoa'] ?? (
            strlen(preg_replace('/\D/', '', (string) ($alvo['documento'] ?? $alvo['cnpj'] ?? ''))) === 11
                ? 'PF'
                : 'PJ'
        )));
        $fontes = $registry->fontesDe($this->consultasIncluidas, $tipoPessoa);
        // Fontes DERIVADAS (ex.: analise_fiscal) não fazem chamada externa: são só flag de unlock
        // do bloco enriquecido do cadastro. Fora do loop de provedores (não viram etapa/consulta).
        $fontes = array_values(array_filter($fontes, fn ($f) => $f->provider() !== 'derivado'));
        usort($fontes, fn ($a, $b) => $this->etapaParaFonte($a->chave())[0] <=> $this->etapaParaFonte($b->chave())[0]);

        // Reconsulta escopada: processa SÓ as fontes selecionadas. Filtrar ANTES do loop faz o
        // índice/total do progresso refletirem o escopo do retry (ex.: "1 de 1"), não o plano todo.
        if ($this->somenteFontes !== null) {
            $fontes = array_values(array_filter(
                $fontes,
                fn ($fonte) => in_array($fonte->chave(), $this->somenteFontes, true),
            ));
        }

        // Idempotência de retry: fontes pagas já persistidas numa tentativa anterior não são
        // re-consultadas (evita re-cobrar InfoSimples se o worker matar/re-executar o job).
        $persistencia->gravarContextoAlvo($this->loteId, $this->alvoTipo, $this->alvoId, $alvo);
        $jaPersistidas = $persistencia->chavesPersistidas($this->loteId, $this->alvoTipo, $this->alvoId);

        $totalFontes = count($fontes);
        $creditosFalhos = 0.0;
        $retentaveis = [];
        foreach ($fontes as $i => $fonte) {
            // Progresso por GRUPO de etapa da fonte (várias fontes → mesma etapa; sem loop).
            [$nEtapa, $lEtapa] = $this->etapaParaFonte($fonte->chave());

            // % GLOBAL (alvos concluídos + fração de fontes do alvo atual), monotônico — a UI
            // não tem clamp anti-retrocesso, então o valor precisa só crescer. Várias fontes
            // caem na mesma etapa (federais/estaduais): emitir esse % + a mensagem/campos
            // por fonte faz a barra AVANÇAR dentro do grupo (antes ficava "parada" porque o
            // payload por grupo era idêntico e o SSE dedup-a por hash) E faz lotes multi-CNPJ
            // não resetarem a 0% a cada empresa. A mensagem nomeia a fonte (e a empresa, se >1).
            $pct = $this->pctGlobal($i, $totalFontes);
            $nomeFonte = $this->nomeFonte($fonte->chave());
            $mensagem = $this->prefixoAlvo().'Consultando '.$nomeFonte.' ('.($i + 1)." de {$totalFontes})";

            // Posta ANTES de processar: a barra mostra o grupo atual enquanto a chamada
            // (lenta, com throttle + retry) está em andamento. Postar depois deixava a barra
            // travada na etapa anterior (ex: "Dados cadastrais") durante toda a 1ª consulta.
            // fonte_nome/indice/total são estruturados p/ a checklist por fonte no front montar
            // sem precisar parsear a string da mensagem.
            $this->progresso(
                etapa: $nEtapa, total: $total, label: $lEtapa, status: 'processando',
                progresso: $pct, mensagem: $mensagem,
                fonteNome: $nomeFonte, fonteIndice: $i + 1, fonteTotal: $totalFontes,
            );

            // Já consultada numa tentativa anterior (retry) → não re-chamar nem re-cobrar.
            // Cadastro (minhareceita, gratuito) não tem chave própria no blob, então sempre roda.
            if (in_array($fonte->chave(), $jaPersistidas, true)) {
                continue;
            }

            // CND Municipal: injeta a inscrição municipal (IM) no alvo ANTES do aplicavelPara.
            // Prefeituras que exigem IM (ex.: Ribeirão Preto/SP) só consultam com esse número; o
            // aplicavelPara abaixo usa a IM já resolvida pra decidir se pula (sem IM → INDISPONIVEL,
            // sem chamar/cobrar o 606 billable). O número é resolvido UMA vez (perfil → cross →
            // acervo XML) e persistido; próximas consultas vêm do perfil, sem reconsulta — só a
            // certidão reconsulta. Ver InscricaoMunicipalResolver.
            if ($fonte instanceof CndMunicipalFonte) {
                $im = $imResolver->resolver($alvo, $this->alvoTipo, $this->alvoId, $this->userId);
                if ($im !== null) {
                    $alvo['inscricao_municipal'] = $im;
                }
            }

            // Cobertura do provedor indisponível p/ este alvo (ex: UF/cidade fora do mapa, ou
            // município que exige IM sem IM no perfil) → pula sem chamar nem cobrar; persiste como
            // INDISPONIVEL com o MOTIVO (não é falha estornável).
            if (! $fonte->aplicavelPara($alvo)) {
                $persistencia->gravar($this->loteId, $this->alvoTipo, $this->alvoId, new ResultadoFonte(
                    $fonte->chave(),
                    $fonte->normalizar(['_motivo' => $fonte->motivoIndisponivel($alvo)], 'nao_aplicavel'),
                    'nao_aplicavel', 0, $fonte->motivoIndisponivel($alvo),
                ));

                continue;
            }

            $resultado = $this->consultarFonte($fonte, $alvo, $throttle, $persistencia, $comprovanteArquivador);

            // Colheita grátis: a resposta da CND Municipal traz a inscrição municipal do
            // contribuinte. Grava no perfil (resolve o NÚMERO 1x) pra reusar via cross-cadastro
            // e nas prefeituras que EXIGEM IM de entrada. Sem chamada extra — vem da CND que já
            // rodou. `persistir` não sobrescreve IM já salva.
            if ($fonte instanceof CndMunicipalFonte && $resultado?->status === 'sucesso') {
                $imColhida = trim((string) ($resultado->dados['cnd_municipal']['inscricao_municipal'] ?? ''));
                if ($imColhida !== '') {
                    $imResolver->persistir($this->alvoTipo, $this->alvoId, $imColhida);
                }
            }

            if ($resultado?->ehFalhaEstornavel()) {
                $creditosFalhos += $this->valorEstornavel($fonte->chave(), $resultado->custoCreditos);
            }

            // Falha transitória (classe `retry`) entra na passada de auto-retry ao fim do alvo.
            // Cadastro fica fora: as fontes UF-dependentes já rodaram com o alvo sem a mutação,
            // então retentá-lo tarde demais não corrige nada.
            if ($resultado?->status === 'retry' && $fonte->chave() !== 'cadastro') {
                $retentaveis[] = ['fonte' => $fonte, 'falhouEm' => microtime(true)];
            }
        }

        $creditosFalhos = $this->retentarFontes(
            $retentaveis,
            $alvo,
            $throttle,
            $persistencia,
            $comprovanteArquivador,
            $creditosFalhos,
            $totalFontes,
            $total,
        );

        // Re-grava o contexto com o alvo JÁ ENRIQUECIDO pelo cadastro (data_inicio_atividade,
        // razão social e município oficiais). A gravação da linha 119 acontece antes do loop,
        // quando esses campos ainda não existem — sem esta segunda passada, a reconsulta manual
        // de uma fonte que depende deles (BCB Valores a Receber de PJ) cairia em INDISPONÍVEL
        // mesmo tendo funcionado na consulta original. Sem mudança, não gera escrita.
        $persistencia->gravarContextoAlvo($this->loteId, $this->alvoTipo, $this->alvoId, $alvo);

        // Fecha o progresso DESTE alvo em pctGlobal(total, total). Sem isso a última emissão
        // parava em (N-1)/N — e num retry escopado a 1 fonte a ÚNICA emissão era 0%, deixando
        // a barra parada durante a reconsulta inteira. Multi-alvo segue monotônico: o fechamento
        // do alvo K coincide com o ponto de partida do alvo K+1.
        [$nFim, $lFim] = $totalFontes > 0
            ? $this->etapaParaFonte($fontes[$totalFontes - 1]->chave())
            : [$total, 'Consulta'];
        $this->progresso(
            etapa: $nFim, total: $total, label: $lFim, status: 'processando',
            progresso: $this->pctGlobal($totalFontes, max(1, $totalFontes)),
            mensagem: $this->prefixoAlvo().'Fontes consultadas ('.$totalFontes.' de '.$totalFontes.')',
            fonteNome: $totalFontes > 0 ? $this->nomeFonte($fontes[$totalFontes - 1]->chave()) : null,
            fonteIndice: $totalFontes, fonteTotal: $totalFontes,
        );

        // Estorno preciso: total por participante (overwrite = idempotente em retry do job).
        // Somado por FecharLoteService ao fechar o lote. Ver project_camada_consultas_laravel.
        Cache::put("consulta_estorno:{$this->loteId}:{$this->alvoTipo}:{$this->alvoId}", $creditosFalhos, 86400);
    }

    /**
     * Consulta UMA fonte e persiste o desfecho (resultado, marca de erro de integração ou de
     * erro interno). Retorna null quando a fonte estourou exceção nossa (já marcada como
     * 'interno'). $alvo é mutável: o cadastro grava UF/município autoritativos nele.
     */
    private function consultarFonte(
        Fonte $fonte,
        array &$alvo,
        ThrottleProvider $throttle,
        PersistenciaCnpj $persistencia,
        ComprovanteArquivador $comprovanteArquivador,
    ): ?ResultadoFonte {
        try {
            $provider = $this->resolverProvider($fonte->provider());
            $resp = $this->consultarProvider($fonte, $alvo, $provider, $throttle);

            $dados = $fonte->normalizar($resp->raw, $resp->status);

            $bloco = $dados[$fonte->chave()] ?? null;
            if (is_array($bloco) && ! empty($bloco['comprovante'])) {
                $arquivo = $comprovanteArquivador->arquivar(
                    (string) $bloco['comprovante'],
                    $this->userId,
                    ComprovanteArquivador::rotuloFonte(
                        $fonte->chave(),
                        (string) ($alvo['documento'] ?? $alvo['cpf'] ?? $alvo['cnpj'] ?? ''),
                    ),
                );

                if ($arquivo !== null) {
                    $dados[$fonte->chave()]['comprovante_arquivo'] = $arquivo['path'];
                    $dados[$fonte->chave()]['comprovante_arquivado_em'] = $arquivo['arquivado_em'];
                }
            }

            // UF e município do cadastro são autoritativos p/ as fontes UF/cidade-dependentes.
            if ($fonte->chave() === 'cadastro') {
                if (! empty($dados['endereco']['uf'])) {
                    $alvo['uf'] = $dados['endereco']['uf'];
                }
                if (! empty($dados['endereco']['municipio'])) {
                    $alvo['municipio'] = $dados['endereco']['municipio'];
                }
                // Razão social oficial (RFB) — exigida como `nome` por fontes de busca nominal
                // (ex.: CEAT TRT). Sobrescreve a razão vinda do banco no alvo inicial.
                if (! empty($dados['razao_social'])) {
                    $alvo['razao_social'] = $dados['razao_social'];
                }
                // Indicador oficial (RFB) de matriz/filial, propagado pras fontes seguintes
                // (ex.: CndFederalFonte::params()) — mais confiável que a ORDEM do CNPJ.
                if (! empty($dados['matriz_filial'])) {
                    $alvo['matriz_filial'] = $dados['matriz_filial'];
                }
                // Abertura oficial da PJ — complemento exigido pelo BCB Valores a Receber.
                // Propaga em memória para que a fonte seguinte use o dado fresco sem persistir
                // alterações no Participante/Cliente.
                if (! empty($dados['data_inicio_atividade'])) {
                    $alvo['data_inicio_atividade'] = $dados['data_inicio_atividade'];
                }

                // Raio-X tributário (regime + estimativa) só é PROCESSADO quando a Análise Fiscal
                // (paga) está na seleção — sentinela `regime_tributario` em consultasIncluidas.
                // Sem ela, o cadastro grátis não completa nem estima regime e o bloco enriquecido
                // é descartado antes de persistir (abaixo).
                $analiseComprada = in_array('regime_tributario', $this->consultasIncluidas, true);

                // Regime tributário é da PJ inteira, mas a RFB só publica no CNPJ da
                // matriz — filial consultada ficava "Não informado". 1 chamada extra
                // (grátis, minhareceita) pra matriz completa o regime; falha aqui não
                // derruba o cadastro (fica "Não informado" como antes).
                $cnpjAlvo = \App\Support\Cnpj::digitos((string) ($alvo['cnpj'] ?? ''));
                if ($analiseComprada
                    && $fonte instanceof \App\Services\Consultas\Fontes\CadastroFonte
                    && $resp->status === 'sucesso'
                    && $fonte->regimeIndefinido($dados)
                    && ($alvo['matriz_filial'] ?? null) === 'filial') {
                    try {
                        $throttle->aguardar($fonte->provider());
                        $respMatriz = $provider->consultar('', ['cnpj' => \App\Support\Cnpj::matriz($cnpjAlvo)]);
                        if ($respMatriz->status === 'sucesso') {
                            $dados = $fonte->aplicarRegimeDaMatriz($dados, $respMatriz->raw);
                        }
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }

                // Regime seguiu indefinido (RFB não publica, nem na matriz) → estima pelo
                // perfil (natureza/CNAE/EFD/exclusão do Simples) com origem 'estimado'. A
                // ficha nunca deixa a estimativa sobrescrever regime real já persistido.
                if ($analiseComprada
                    && $fonte instanceof \App\Services\Consultas\Fontes\CadastroFonte
                    && $resp->status === 'sucesso'
                    && $fonte->regimeIndefinido($dados)) {
                    $dados = app(\App\Services\Consultas\RegimeEstimadoResolver::class)
                        ->aplicar($dados, $this->userId, $this->alvoTipo, $this->alvoId);
                }

                // Análise Fiscal NÃO comprada → descarta o bloco enriquecido (regime/Simples/MEI):
                // dado da mesma chamada grátis, mas é produto pago. Identidade/endereço/situação
                // (grátis) permanecem. `consultas_realizadas` também perde os itens enriquecidos.
                if (! $analiseComprada && $fonte instanceof \App\Services\Consultas\Fontes\CadastroFonte) {
                    foreach (\App\Services\Consultas\Fontes\AnaliseFiscalFonte::CHAVES_BLOQUEADAS as $chaveBloqueada) {
                        unset($dados[$chaveBloqueada]);
                    }
                    if (isset($dados['consultas_realizadas']) && is_array($dados['consultas_realizadas'])) {
                        $dados['consultas_realizadas'] = array_values(array_diff(
                            $dados['consultas_realizadas'],
                            \App\Services\Consultas\Fontes\AnaliseFiscalFonte::CHAVES_BLOQUEADAS,
                        ));
                    }
                }
            }

            // Cadastro PF pago também é fonte de identidade para as consultas seguintes do mesmo
            // alvo (TSE/BNMP). Não toca no model Participante; só enriquece o alvo em memória.
            if ($fonte->chave() === 'cadastro_pf' && ! empty($dados['cadastro_pf']['nome'])) {
                $alvo['nome'] = $dados['cadastro_pf']['nome'];
                $alvo['razao_social'] = $dados['cadastro_pf']['nome'];
            }

            $resultado = new ResultadoFonte(
                $fonte->chave(), $dados,
                $resp->status, $fonte->custoCreditos(), $resp->mensagem,
            );
            $persistencia->gravar($this->loteId, $this->alvoTipo, $this->alvoId, $resultado);

            // Fonte de 2 ETAPAS (fase 4): a etapa 1 (aqui) só CADASTROU o pedido no tribunal. Cria
            // a máquina de estados (CertidaoPedido) que agenda o follow-up da etapa 2 (obter). NÃO
            // grava em `certidoes` — o bloco vem com status "Em andamento" (o registro já o pula).
            if ($resp->status === 'sucesso' && $fonte instanceof \App\Services\Consultas\Contracts\FonteDuasEtapas) {
                try {
                    app(\App\Services\Consultas\CertidaoPedidoService::class)->criar(
                        $fonte,
                        $alvo,
                        (array) ($resp->raw['data'][0] ?? []),
                        $this->userId,
                        $this->alvoTipo,
                        $this->alvoId,
                        (string) ($alvo['documento'] ?? $alvo['cpf'] ?? $alvo['cnpj'] ?? ''),
                        $this->loteId,
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            // Registro canônico de certidão emitida (tabela `certidoes`): alimenta os alertas de
            // vencimento e aponta o PDF já arquivado. Falha aqui nunca derruba a consulta.
            if ($resp->status === 'sucesso' && $fonte instanceof \App\Services\Consultas\Fontes\FonteCertidaoInfoSimples) {
                try {
                    app(\App\Services\Consultas\CertidaoRegistro::class)->registrar(
                        $fonte->chave(),
                        (array) ($dados[$fonte->chave()] ?? []),
                        $this->userId,
                        $this->alvoTipo,
                        $this->alvoId,
                        (string) ($alvo['documento'] ?? $alvo['cpf'] ?? $alvo['cnpj'] ?? ''),
                        $this->loteId,
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            // Fonte pedida que não produziu resultado (retry/fatal/erro_participante → blob
            // vazio, chave ausente) = falha NA INTEGRAÇÃO. Marca a origem p/ a UI distinguir
            // de erro interno e de "fora do plano".
            if (empty($dados) && $resp->status !== 'sucesso') {
                $persistencia->marcarErroFonte($this->loteId, $this->alvoTipo, $this->alvoId, $fonte->chave(), 'integracao', $resp->status, $resp->httpCode);
            }

            return $resultado;
        } catch (\Throwable $e) {
            // Exceção no nosso processamento da fonte = ERRO INTERNO. Não derruba as demais
            // fontes do alvo (antes uma exceção matava o job inteiro).
            report($e);
            $persistencia->marcarErroFonte($this->loteId, $this->alvoTipo, $this->alvoId, $fonte->chave(), 'interno');

            return null;
        }
    }

    /**
     * Chamada ao provedor com DEDUP intra-lote: requisições idênticas (provider+slug+params)
     * dentro do mesmo lote reutilizam a resposta de SUCESSO já obtida — ex.: CND Federal de N
     * filiais normaliza pro MESMO CNPJ de matriz (N chamadas pagas viram 1). Só sucesso é
     * cacheado (falha transitória de um alvo não contamina o outro); lote novo re-consulta.
     */
    private function consultarProvider(Fonte $fonte, array $alvo, ConsultaProvider $provider, ThrottleProvider $throttle): RespostaProvider
    {
        $slug = $fonte->slugPara($alvo);
        $params = $fonte->params($alvo);
        $chaveDedup = "consulta_dedup:{$this->loteId}:".md5($fonte->provider().'|'.$slug.'|'.json_encode($params));

        $hit = Cache::get($chaveDedup);
        if (is_array($hit)) {
            return new RespostaProvider($hit['status'], (int) $hit['httpCode'], (array) $hit['raw'], $hit['mensagem'] ?? null);
        }

        $throttle->aguardar($fonte->provider());
        $resp = $provider->consultar($slug, $params);

        if ($resp->status === 'sucesso') {
            Cache::put($chaveDedup, [
                'status' => $resp->status,
                'httpCode' => $resp->httpCode,
                'raw' => $resp->raw,
                'mensagem' => $resp->mensagem,
            ], 21600);
        }

        return $resp;
    }

    /**
     * Auto-retry das fontes que falharam com classe `retry` (transitória): re-tenta cada uma
     * até consultas.retry.auto.max_tentativas vezes, com BACKOFF crescente por tentativa
     * (cooldown × nº da tentativa: 15s na 1ª, 30s na 2ª). A espera é contada desde a falha, então
     * o tempo já gasto nas fontes seguintes abate. Ajusta o estorno: fonte recuperada deixa de
     * ser estornada; re-falha não conta duas vezes.
     *
     * @param  array<int, array{fonte: Fonte, falhouEm: float}>  $retentaveis
     * @return int unidades internas do valor a estornar
     */
    private function retentarFontes(
        array $retentaveis,
        array $alvo,
        ThrottleProvider $throttle,
        PersistenciaCnpj $persistencia,
        ComprovanteArquivador $comprovanteArquivador,
        float $creditosFalhos,
        int $totalFontes,
        int $totalEtapas,
    ): float {
        $maxTentativas = (int) config('consultas.retry.auto.max_tentativas', 1);
        $cooldown = (int) config('consultas.retry.auto.cooldown_segundos', 30);

        for ($t = 1; $t <= $maxTentativas && $retentaveis; $t++) {
            $aindaFalhando = [];
            foreach ($retentaveis as $r) {
                /** @var Fonte $fonte */
                $fonte = $r['fonte'];

                // Backoff crescente: a espera-alvo cresce com o nº da tentativa (15s, 30s, ...);
                // desconta o tempo já decorrido desde a falha (fontes seguintes já "gastaram" parte).
                $espera = ($cooldown * $t) - (microtime(true) - $r['falhouEm']);
                if ($espera > 0) {
                    usleep((int) ($espera * 1_000_000));
                }

                [$nEtapa, $lEtapa] = $this->etapaParaFonte($fonte->chave());
                $nomeFonte = $this->nomeFonte($fonte->chave());
                // % da última fonte do alvo (o maior já emitido) — mantém a barra monotônica.
                $this->progresso(
                    etapa: $nEtapa, total: $totalEtapas, label: $lEtapa, status: 'processando',
                    progresso: $this->pctGlobal(max(0, $totalFontes - 1), $totalFontes),
                    mensagem: $this->prefixoAlvo()."Nova tentativa: {$nomeFonte} ({$t} de {$maxTentativas})",
                    fonteNome: $nomeFonte, fonteIndice: $totalFontes, fonteTotal: $totalFontes,
                );

                $persistencia->incrementarTentativaFonte($this->loteId, $this->alvoTipo, $this->alvoId, $fonte->chave());
                $resultado = $this->consultarFonte(
                    $fonte,
                    $alvo,
                    $throttle,
                    $persistencia,
                    $comprovanteArquivador,
                );

                if ($resultado?->status === 'retry') {
                    $aindaFalhando[] = ['fonte' => $fonte, 'falhouEm' => microtime(true)];
                } elseif ($resultado !== null && ! $resultado->ehFalhaEstornavel()) {
                    // Recuperou (sucesso/nao_encontrado/indeterminado/erro_participante): o dado
                    // foi entregue/cobrado — cancela o estorno contado na 1ª falha.
                    $creditosFalhos = max(0, $creditosFalhos - $this->valorEstornavel($fonte->chave(), $resultado->custoCreditos));
                }
                // retry→fatal mantém o estorno já contado; retry→exceção interna (null) idem.
            }
            $retentaveis = $aindaFalhando;
        }

        return $creditosFalhos;
    }

    /**
     * Valor a estornar por falha estornável DESTA fonte: no lote avulso é o preço de venda
     * cobrado (precosVenda); no lote de plano é o custo interno da fonte (comportamento original).
     */
    private function valorEstornavel(string $chaveFonte, float $custoInterno): float
    {
        if ($this->precosVenda !== null) {
            return (float) ($this->precosVenda[$chaveFonte] ?? 0);
        }

        return $custoInterno;
    }

    private function resolverProvider(string $nome)
    {
        return app(\App\Services\Consultas\ProviderResolver::class)->resolver($nome);
    }

    /** Etapa (numero, label) por chave no array de etapas do plano. */
    private function etapaPorChave(string $chave, string $fallbackLabel): array
    {
        foreach ($this->etapas as $e) {
            if (is_array($e) && ($e['chave'] ?? null) === $chave) {
                return [(int) ($e['numero'] ?? 0), (string) ($e['label'] ?? $fallbackLabel)];
            }
        }

        return [count($this->etapas), $fallbackLabel];
    }

    /** Etapa (numero, label) do GRUPO ao qual a fonte pertence (config consultas.fonte_etapa). */
    private function etapaParaFonte(string $chaveFonte): array
    {
        $chaveEtapa = (string) config("consultas.fonte_etapa.{$chaveFonte}", 'cadastrais');

        return $this->etapaPorChave($chaveEtapa, $chaveFonte);
    }

    /** Nome amigável da fonte p/ a mensagem de progresso (config consultas.fonte_nome). */
    private function nomeFonte(string $chaveFonte): string
    {
        return (string) config("consultas.fonte_nome.{$chaveFonte}", $chaveFonte);
    }

    /**
     * % GLOBAL do lote, monotônico: alvos já concluídos + a fração de fontes do alvo atual,
     * normalizado pelo total de alvos. Garante que um lote de N empresas vá de 0 a 100 sem
     * resetar a cada empresa (cada alvo ocupa uma faixa de 1/N).
     */
    private function pctGlobal(int $fonteIndice, int $totalFontes): int
    {
        $base = max(0, $this->alvoIndice - 1);            // alvos concluídos antes deste (0-based)
        $frac = $totalFontes > 0 ? $fonteIndice / $totalFontes : 0;
        $fracao = ($base + $frac) / max(1, $this->totalAlvos);   // 0..1 desta fase

        return (int) round($this->pctBase + $fracao * $this->pctSpan);
    }

    /** Prefixo "Empresa X de N · " na mensagem quando o lote tem mais de um alvo. */
    private function prefixoAlvo(): string
    {
        return $this->totalAlvos > 1 ? "Empresa {$this->alvoIndice} de {$this->totalAlvos} · " : '';
    }

    private function progresso(
        int $etapa,
        int $total,
        string $label,
        string $status,
        ?int $progresso = null,
        ?string $mensagem = null,
        ?string $fonteNome = null,
        ?int $fonteIndice = null,
        ?int $fonteTotal = null,
    ): void {
        $payload = [
            'tab_id' => $this->tabId,
            'etapa' => $etapa,
            'total_etapas' => $total,
            'etapa_label' => $label,
            'status' => $status,
        ];

        // Campos opcionais consumidos pela UI (consulta-lote-detalhe.js): `progresso` move a barra
        // (resolveProgressPercent), `mensagem` é o feedback textual (resolveProgressMessage) e
        // fonte_nome/indice/total alimentam a checklist por fonte. O SSE encaminha o payload inteiro.
        if ($progresso !== null) {
            $payload['progresso'] = $progresso;
        }
        if ($mensagem !== null) {
            $payload['mensagem'] = $mensagem;
        }
        if ($fonteNome !== null) {
            $payload['fonte_nome'] = $fonteNome;
        }
        if ($fonteIndice !== null) {
            $payload['fonte_indice'] = $fonteIndice;
        }
        if ($fonteTotal !== null) {
            $payload['fonte_total'] = $fonteTotal;
        }

        Cache::put("progresso:{$this->userId}:{$this->tabId}", $payload, 600);
    }
}
