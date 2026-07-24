<?php

namespace App\Services\Consultas;

use App\Models\Cliente;
use App\Models\ConsultaResultado;
use App\Models\Participante;
use App\Support\CertidaoBadge;
use App\Support\Cnpj;
use App\Support\Cpf;
use App\Support\MensagemPublica;

/**
 * Transforma o `resultado_dados` (jsonb por fonte) de um ConsultaResultado em blocos
 * exibíveis no detalhe expansível por CNPJ. Cada fonte vira um card com badge, itens
 * (label/valor/tooltip), listas (CNAEs, QSA...), mensagem oficial e
 * link de comprovante quando houver.
 *
 * Objetivo: exibir TUDO que a consulta trouxe — inclusive fontes que a tabela resumida
 * não mostra (CND Estadual/Municipal, SINTEGRA).
 *
 * Bloco:
 *   ['chave','titulo','badge'(array|null),'itens'[],'listas'[],'mensagem'(?string),'comprovante_url'(?string)]
 */
class ResultadoDetalhePresenter
{
    /** Ordem canônica dos blocos (cadastro sempre primeiro). */
    private const ORDEM = [
        'cadastro_pf',
        'cnd_federal',
        'cnd_estadual',
        'cnd_municipal',
        'crf_fgts',
        'cndt',
        'sintegra',
        // Vertical advocacia (lote avulso) — shape de certidão/lista normalizado p/ o mesmo pipeline.
        'certidao_stj',
        'certidao_trf',
        'ceat_trt',
        'certidao_mpt',
        'certidao_mpf',
        'certidao_tjms',
        'certidao_tcu',
        'tcu_cnp',
        'tcu_cni_inidoneo',
        'tcu_cni_inabilitado',
        'improbidade',
        'ceis',
        'cnep',
        'protestos',
        'falencias',
        'pgfn_devedores',
        'ibama_embargos',
        'ibama_debitos',
        'ibama_regularidade',
        'ibama_autuacoes',
        'bcb_valores_receber',
        'inpi_marcas_titular',
        'sigef_parcelas',
        'quitacao_eleitoral',
        'antecedentes_pf',
        'mandado_prisao',
    ];

    /** Fontes de regularidade exibidas no strip agrupado da tabela (sigla compacta). */
    private const SIGLAS = [
        'cnd_federal' => 'FED',
        'cnd_estadual' => 'EST',
        'cnd_municipal' => 'MUN',
        'crf_fgts' => 'FGTS',
        'cndt' => 'CNDT',
        'sintegra' => 'SINT',
        'certidao_stj' => 'STJ',
        'certidao_trf' => 'TRF',
        'ceat_trt' => 'CEAT',
        'certidao_tjms' => 'TJMS',
        'certidao_mpt' => 'MPT',
        'certidao_mpf' => 'MPF',
        'certidao_tcu' => 'TCU',
        'tcu_cnp' => 'TCU-CNP',
        'tcu_cni_inidoneo' => 'TCU-INID',
        'tcu_cni_inabilitado' => 'TCU-INAB',
        'improbidade' => 'IMPR',
        'ceis' => 'CEIS',
        'cnep' => 'CNEP',
        'protestos' => 'PROT',
        'falencias' => 'FALÊN',
        'pgfn_devedores' => 'PGFN',
        'ibama_embargos' => 'IBAMA-EMB',
        'ibama_debitos' => 'IBAMA-DEB',
        'ibama_regularidade' => 'IBAMA-REG',
        'ibama_autuacoes' => 'IBAMA-AUT',
        'bcb_valores_receber' => 'BCB',
        'inpi_marcas_titular' => 'INPI',
        'sigef_parcelas' => 'SIGEF',
        'cadastro_pf' => 'CPF',
        'quitacao_eleitoral' => 'TSE',
        'antecedentes_pf' => 'ANT',
        'mandado_prisao' => 'BNMP',
    ];

    // Órgão oficial dono de cada fonte — nomeado na mensagem de falha p/ deixar claro que a
    // indisponibilidade é na ORIGEM (governo/Caixa/etc.), não no FiscalDock. Público: fonte
    // única também do campo `orgao` do registro de certidões (CertidaoRegistro).
    public const ORGAO = [
        'cnd_federal' => 'A Receita Federal/PGFN',
        'cnd_estadual' => 'A Secretaria da Fazenda Estadual (SEFAZ)',
        'cnd_municipal' => 'A Prefeitura',
        'cndt' => 'O Tribunal Superior do Trabalho (TST)',
        'crf_fgts' => 'A Caixa Econômica Federal',
        'sintegra' => 'O SINTEGRA (SEFAZ)',
        'certidao_stj' => 'O Superior Tribunal de Justiça (STJ)',
        'certidao_trf' => 'A Justiça Federal (TRFs)',
        'ceat_trt' => 'O Tribunal Regional do Trabalho (TRT)',
        'certidao_tjms' => 'O Tribunal de Justiça de Mato Grosso do Sul (TJMS)',
        'certidao_mpt' => 'O Ministério Público do Trabalho (MPT)',
        'certidao_mpf' => 'O Ministério Público Federal (MPF)',
        'certidao_tcu' => 'O Tribunal de Contas da União (TCU)',
        'tcu_cnp' => 'O Tribunal de Contas da União (TCU)',
        'tcu_cni_inidoneo' => 'O Tribunal de Contas da União (TCU)',
        'tcu_cni_inabilitado' => 'O Tribunal de Contas da União (TCU)',
        'improbidade' => 'O Conselho Nacional de Justiça (CNJ)',
        'ceis' => 'O Portal da Transparência (CGU)',
        'cnep' => 'O Portal da Transparência (CGU)',
        'protestos' => 'O IEPTB (cartórios de protesto)',
        'falencias' => 'O Tribunal Superior do Trabalho (TST)',
        'pgfn_devedores' => 'A Procuradoria-Geral da Fazenda Nacional (PGFN)',
        'ibama_embargos' => 'O Instituto Brasileiro do Meio Ambiente (IBAMA)',
        'ibama_debitos' => 'O Instituto Brasileiro do Meio Ambiente (IBAMA)',
        'ibama_regularidade' => 'O Instituto Brasileiro do Meio Ambiente (IBAMA)',
        'ibama_autuacoes' => 'O Instituto Brasileiro do Meio Ambiente (IBAMA)',
        'bcb_valores_receber' => 'O Banco Central do Brasil (BCB)',
        'inpi_marcas_titular' => 'O Instituto Nacional da Propriedade Industrial (INPI)',
        'sigef_parcelas' => 'O INCRA (SIGEF)',
        'cadastro_pf' => 'A Receita Federal',
        'quitacao_eleitoral' => 'O Tribunal Superior Eleitoral (TSE)',
        'antecedentes_pf' => 'A Polícia Federal',
        'mandado_prisao' => 'O Conselho Nacional de Justiça (CNJ/BNMP)',
    ];

    /**
     * Certidões de regularidade que viram badge (CertidaoBadge) no resumo/situação geral.
     * Distinta de SIGLAS: exclui `sintegra` (inscrição estadual, não certidão de regularidade).
     */
    private const CERTIDOES_BADGE = ['cnd_federal', 'cnd_estadual', 'cnd_municipal', 'crf_fgts', 'cndt'];

    /**
     * Certidões de regularidade (dentre as canônicas) que um plano inclui — usar como
     * `$esperadas` em blocos()/certidoes() para não marcar como "erro na integração" uma
     * certidão que nunca fez parte do plano contratado (nunca foi consultada). Fonte única
     * do filtro: chamadores não devem repetir a lista de certidões inline.
     *
     * @param  array<int, string>|null  $consultasIncluidas  ex.: $lote->plano?->consultas_incluidas
     * @return array<int, string>
     */
    public function esperadasDoPlano(?array $consultasIncluidas): array
    {
        return array_values(array_intersect($consultasIncluidas ?? [], array_keys(self::SIGLAS)));
    }

    /**
     * Universo de TODAS as consultas de regularidade que o partial sabe renderizar (chaves de
     * SIGLAS/ORDEM). Passar isto como `esperadas` faz o detalhe mostrar TODA consulta possível —
     * as consultadas com dado, as não-consultadas como placeholder "disponível". No modelo à la
     * carte substitui o `consultas_incluidas` do plano (que morreu). Intersecção opcional com
     * `$restringirA` (ex.: só as fontes prontas/à venda do catálogo) pra não listar fonte pausada.
     *
     * @param  array<int,string>|null  $restringirA
     * @return array<int,string>
     */
    public function esperadasTodas(?array $restringirA = null): array
    {
        $todas = array_keys(self::SIGLAS);

        return $restringirA === null
            ? $todas
            : array_values(array_intersect($todas, $restringirA));
    }

    /**
     * Atalho de esperadasDoPlano() a partir do próprio resultado (via lote->plano). Sem
     * lote/plano associado (dado legado) devolve vazio: fonte ausente fica neutra, não erro.
     *
     * @return array<int, string>
     */
    public function esperadasDoResultado(ConsultaResultado $resultado): array
    {
        return $this->esperadasDoPlano($resultado->lote?->plano?->consultas_incluidas);
    }

    /**
     * Detalhe COMPLETO da última consulta bem-sucedida de um participante, no contrato do partial
     * `autenticado.consulta.partials.detalhe-blocos` — o mesmo conteúdo do "Ver detalhes" da
     * Consulta CNPJ e do Score Fiscal.
     *
     * FONTE ÚNICA: quem precisa mostrar o resultado de um CNPJ (Consulta CNPJ, Score Fiscal,
     * Clearance completo) monta por aqui e renderiza o MESMO partial — as telas não podem divergir
     * na leitura de `consulta_resultados` (ver memory feedback_fonte_unica_entre_features).
     *
     * Null = participante nunca consultado com sucesso.
     *
     * @param  bool  $somenteConsultadas  true = mostra APENAS as fontes que trouxeram dado.
     *                                    Por padrão (false) as fontes que o PLANO pedia mas que não
     *                                    voltaram viram placeholder de falha — é o que o Score Fiscal
     *                                    quer (distinguir "pediu e falhou" de "não pediu").
     * @param  array<int,string>|null  $somenteFontes  Restringe a estas chaves de fonte (o `cadastro`
     *                                                 entra sempre — é a identidade do CNPJ). Serve pra um produto
     *                                                 não exibir fonte que ELE não cobre: a última consulta do
     *                                                 participante pode ser de uma Consulta CNPJ de plano maior,
     *                                                 e o Clearance completo (3 fontes) mostraria EST/MUN/FGTS/CNDT
     *                                                 que ele nunca consultou.
     * @return array{blocos: array, resumo: ?string, certidoes: array, cabecalho: array}|null
     */
    public function detalheDoParticipante(Participante $participante, bool $somenteConsultadas = false, ?array $somenteFontes = null, ?array $esperadasOverride = null): ?array
    {
        $ultima = ConsultaResultado::where('participante_id', $participante->id)
            ->where('status', ConsultaResultado::STATUS_SUCESSO)
            ->whereHas('lote', fn ($query) => $query->where('user_id', $participante->user_id))
            ->with('lote.plano')
            ->orderByDesc('consultado_em')
            ->first();

        if (! $ultima) {
            return null;
        }

        return $this->montarDetalhe(
            $ultima,
            [
                'razao' => $participante->razao_social,
                'documento' => $participante->cnpj_formatado ?? $participante->documento,
                'uf' => $participante->uf,
                'situacao' => $participante->situacao_cadastral
                    ?? ($ultima->resultado_dados['situacao_cadastral']
                    ?? ($ultima->resultado_dados['cadastro_pf']['situacao_cadastral'] ?? null)),
            ],
            $somenteConsultadas,
            $somenteFontes,
            $esperadasOverride,
        );
    }

    /**
     * Detalhe da consulta mais recente do cliente. Considera tanto resultados vinculados
     * diretamente ao cliente quanto o participante equivalente pelo mesmo CNPJ.
     *
     * @param  array<int,string>|null  $somenteFontes
     * @return array{blocos: array, resumo: ?string, certidoes: array, cabecalho: array, consultado_em: mixed}|null
     */
    public function detalheDoCliente(Cliente $cliente, bool $somenteConsultadas = false, ?array $somenteFontes = null, ?array $esperadasOverride = null): ?array
    {
        $documento = Cnpj::digitos((string) $cliente->documento);
        $participanteIds = strlen($documento) === 14
            ? Participante::query()
                ->where('user_id', $cliente->user_id)
                ->where('documento', $documento)
                ->pluck('id')
            : collect();

        $ultima = ConsultaResultado::query()
            ->where('status', ConsultaResultado::STATUS_SUCESSO)
            ->whereHas('lote', fn ($query) => $query->where('user_id', $cliente->user_id))
            ->where(function ($query) use ($cliente, $participanteIds) {
                $query->where('cliente_id', $cliente->id);

                if ($participanteIds->isNotEmpty()) {
                    $query->orWhereIn('participante_id', $participanteIds);
                }
            })
            ->with('lote.plano')
            ->orderByDesc('consultado_em')
            ->first();

        if (! $ultima) {
            return null;
        }

        return $this->montarDetalhe(
            $ultima,
            [
                'razao' => $cliente->razao_social ?? $cliente->nome,
                'documento' => $cliente->documento_formatado ?? $cliente->documento,
                'uf' => $cliente->uf,
                'situacao' => $cliente->situacao_cadastral
                    ?? ($ultima->resultado_dados['situacao_cadastral']
                    ?? ($ultima->resultado_dados['cadastro_pf']['situacao_cadastral'] ?? null)),
            ],
            $somenteConsultadas,
            $somenteFontes,
            $esperadasOverride,
        );
    }

    /**
     * @param  array<string, mixed>  $cabecalho
     * @param  array<int,string>|null  $somenteFontes
     * @return array{blocos: array, resumo: ?string, certidoes: array, cabecalho: array, consultado_em: mixed}
     */
    private function montarDetalhe(
        ConsultaResultado $ultima,
        array $cabecalho,
        bool $somenteConsultadas,
        ?array $somenteFontes,
        ?array $esperadasOverride = null,
    ): array {
        // esperadas vazio = fonte sem dado simplesmente não aparece (nem card, nem chip).
        // Override (ex.: "todas as consultas possíveis" da tela de seleção) vence o do plano.
        $esperadas = $esperadasOverride
            ?? ($somenteConsultadas ? [] : $this->esperadasDoResultado($ultima));

        return [
            'blocos' => $this->filtrarPorFonte($this->blocos($ultima, $esperadas), $somenteFontes),
            'resumo' => $this->resumoTextual($ultima),
            'certidoes' => $this->filtrarPorFonte($this->certidoes($ultima, $esperadas), $somenteFontes),
            'cabecalho' => $cabecalho,
            'consultado_em' => $ultima->consultado_em,
        ];
    }

    /**
     * Mantém só os itens (blocos ou certidões) cujas chaves de fonte o produto cobre.
     * `cadastro` nunca é filtrado: é a identidade do CNPJ, não uma certidão.
     *
     * @param  array<int, array<string, mixed>>  $itens
     * @param  array<int, string>|null  $fontes  null = não filtra
     * @return array<int, array<string, mixed>>
     */
    private function filtrarPorFonte(array $itens, ?array $fontes): array
    {
        if ($fontes === null) {
            return $itens;
        }

        $permitidas = array_merge(['cadastro'], $fontes);

        return array_values(array_filter(
            $itens,
            fn (array $item) => in_array($item['chave'] ?? null, $permitidas, true),
        ));
    }

    /**
     * @param  array<int, string>  $esperadas  chaves de fonte que o plano do lote inclui.
     *                                         Quando informado, certidões pedidas mas ausentes
     *                                         no resultado viram placeholder de erro (em vez de
     *                                         sumir) — separando erro interno × falha na integração.
     * @return array<int, array<string, mixed>>
     */
    public function blocos(ConsultaResultado $resultado, array $esperadas = []): array
    {
        $dados = is_array($resultado->resultado_dados) ? $resultado->resultado_dados : [];
        $blocos = [];

        // UF do alvo (endereço cadastral) p/ completar certidões cuja resposta vem sem UF
        // (ex.: SEFAZ/CND Estadual costuma retornar uf=null, mas foi consultada para a UF do alvo).
        $ufFallback = trim((string) ($dados['endereco']['uf'] ?? $dados['uf'] ?? '')) ?: null;
        $errosFonte = is_array($dados['_fontes_erro'] ?? null) ? $dados['_fontes_erro'] : [];
        // Quando o alvo é filial, a CND Federal é emitida para a matriz (regra RFB) — explica no card.
        $notaFederal = $this->notaMatrizFederal($resultado);

        if ($cadastro = $this->blocoCadastro($dados)) {
            $blocos[] = $cadastro;
        }

        foreach (self::ORDEM as $chave) {
            $temDado = array_key_exists($chave, $dados) && is_array($dados[$chave]) && ! empty($dados[$chave]);

            if (! $temDado) {
                if (isset(self::SIGLAS[$chave]) && in_array($chave, $esperadas, true)) {
                    // Distingue FALHA real de NUNCA CONSULTADA: só há falha quando existe registro
                    // de erro (`_fontes_erro`). Sem ele, a fonte esperada nunca foi de fato pedida
                    // (caso do "todas as consultas possíveis" da tela de seleção) → card neutro.
                    $blocos[] = isset($errosFonte[$chave])
                        ? $this->blocoFalhou($chave, $errosFonte[$chave])
                        : $this->blocoNaoConsultada($chave);
                }

                continue;
            }

            $bloco = match ($chave) {
                'sintegra' => $this->blocoSintegra($dados[$chave], $ufFallback, $resultado->getKey()),
                'cadastro_pf' => $this->blocoCadastroPf($dados[$chave], $resultado->getKey()),
                'quitacao_eleitoral' => $this->blocoQuitacaoEleitoral($dados[$chave], $resultado->getKey()),
                'mandado_prisao' => $this->blocoMandadoPrisao($dados[$chave]),
                'tcu_cnp',
                'tcu_cni_inidoneo',
                'tcu_cni_inabilitado',
                'pgfn_devedores',
                'ibama_embargos',
                'ibama_debitos',
                'ibama_regularidade',
                'ibama_autuacoes',
                'bcb_valores_receber',
                'inpi_marcas_titular',
                'sigef_parcelas' => $this->blocoConsultaPublica(
                    $chave,
                    $dados[$chave],
                    $resultado->getKey(),
                ),
                default => $this->blocoCertidao(
                    $chave,
                    $dados[$chave],
                    $ufFallback,
                    $chave === 'cnd_federal' ? $notaFederal : null,
                    $resultado->getKey(),
                ),
            };

            if ($bloco) {
                $blocos[] = $bloco;
            }
        }

        return $blocos;
    }

    /**
     * Strip compacto de certidões pra coluna agrupada da tabela do lote: 1 mini-badge por
     * fonte de regularidade (sigla + glyph + cor). Inclui o estado "Falhou" pra fonte que o
     * plano pediu mas que não retornou (sem isso a coluna fica "—", indistinguível de "fora
     * do plano"). Reusa a MESMA classificação dos cards (CertidaoBadge) p/ não divergir.
     *
     * @param  array<int, string>  $esperadas  chaves de fonte que o plano do lote inclui
     * @return array<int, array{chave: string, sigla: string, titulo: string, label: string, hex: string, estado: string, glyph: string, motivo: ?string}>
     */
    public function certidoes(ConsultaResultado $resultado, array $esperadas = []): array
    {
        $dados = is_array($resultado->resultado_dados) ? $resultado->resultado_dados : [];
        $ufFallback = trim((string) ($dados['endereco']['uf'] ?? $dados['uf'] ?? '')) ?: null;
        $errosFonte = is_array($dados['_fontes_erro'] ?? null) ? $dados['_fontes_erro'] : [];

        $strip = [];

        foreach (self::SIGLAS as $chave => $sigla) {
            $temDado = array_key_exists($chave, $dados) && is_array($dados[$chave]) && ! empty($dados[$chave]);

            if (! $temDado) {
                if (! in_array($chave, $esperadas, true)) {
                    continue; // fora do universo esperado e ausente → não mostra
                }

                // Sem registro de erro = nunca consultada (neutra), não falha.
                if (! isset($errosFonte[$chave])) {
                    $strip[] = [
                        'chave' => $chave,
                        'sigla' => $sigla,
                        'titulo' => $this->tituloCertidao($chave),
                        'label' => 'Não consultada',
                        'hex' => '#9ca3af',
                        'estado' => 'neutro',
                        'glyph' => '·',
                        'motivo' => null,
                        'descricao' => 'Esta consulta ainda não foi realizada para este documento.',
                    ];

                    continue;
                }

                $erro = $this->erroCert($errosFonte[$chave], $chave);
                $strip[] = [
                    'chave' => $chave,
                    'sigla' => $sigla,
                    'titulo' => $this->tituloCertidao($chave),
                    'label' => $erro['label'],
                    'hex' => $erro['hex'],
                    'estado' => $erro['estado'],
                    'glyph' => '⚠',
                    'motivo' => $erro['descricao'],
                    'descricao' => $erro['descricao'],
                ];

                continue;
            }

            $d = $dados[$chave];
            // aplicarIndeterminado em TODA certidão: fonte que não emitiu (conseguiu_emitir=false,
            // sem documento) é INDETERMINADA, nunca irregular — mesma regra canônica da Federal.
            $badge = $chave === 'sintegra'
                ? CertidaoBadge::classificar(['situacao' => $d['situacao'] ?? null])
                : CertidaoBadge::classificar($d, true);

            $estado = $this->bucketHex($badge['hex'] ?? null);
            $uf = $chave === 'cnd_estadual' || $chave === 'cnd_municipal'
                ? (trim((string) ($d['uf'] ?? '')) ?: $ufFallback)
                : null;

            $strip[] = [
                'chave' => $chave,
                'sigla' => $sigla,
                'titulo' => $this->tituloCertidao($chave, $uf),
                'label' => $badge['label'] ?? '—',
                'hex' => $badge['hex'] ?? CertidaoBadge::HEX_NEUTRO,
                'estado' => $estado,
                'glyph' => $this->glyph($estado, $badge['label'] ?? ''),
                'motivo' => $badge['motivo'] ?? null,
                'descricao' => $this->descricaoCert($estado, $badge['label'] ?? '', $badge['motivo'] ?? null, $d['mensagem'] ?? null),
            ];
        }

        return $strip;
    }

    private function glyph(string $estado, string $label): string
    {
        return match (true) {
            $estado === 'regular' => '✓',
            $estado === 'atencao' => '✗',
            $estado === 'indeterminado' => '?',
            str_contains(mb_strtolower($label), 'indispon') => '—',
            str_contains(mb_strtolower($label), 'encontrad') => '—',
            default => '·',
        };
    }

    /**
     * Classifica a falha de uma fonte pedida que não retornou. Default = integração: no pipeline
     * atual a chave só fica ausente quando a fonte externa falhou (retry/fatal). 'interno' só
     * quando o job registra exceção nossa (mapa `_fontes_erro`).
     *
     * @return array{estado: string, label: string, hex: string, descricao: string}
     */
    private function erroCert(string|array|null $origem, ?string $chave = null): array
    {
        // _fontes_erro migrou pra objeto {origem, codigo, status, tentativas} (feature de retry);
        // aceita tanto o objeto quanto a string legada.
        $codigo = is_array($origem) ? (int) ($origem['codigo'] ?? 0) : 0;
        $status = is_array($origem) ? (string) ($origem['status'] ?? '') : '';
        $origem = is_array($origem) ? ($origem['origem'] ?? null) : $origem;

        if ($origem === 'interno') {
            return [
                'estado' => 'erro_interno',
                'label' => 'Erro interno',
                'hex' => CertidaoBadge::HEX_ERRO_INTERNO,
                'descricao' => 'Erro interno no processamento desta consulta.',
            ];
        }

        // 615 (e demais códigos de retry da InfoSimples) = o site do órgão oficial está fora do ar.
        // Nomeia o órgão dono da fonte e deixa claro que a indisponibilidade é NA ORIGEM (governo/
        // Caixa/etc.), não no FiscalDock. Mantém estado/hex de falha de integração p/ o rollup do lote.
        if ($status === 'retry' || $codigo === 615) {
            $orgao = self::ORGAO[$chave] ?? 'O órgão oficial responsável';

            return [
                'estado' => 'erro_integracao',
                'label' => 'Órgão fora do ar',
                'hex' => CertidaoBadge::HEX_FALHOU,
                'descricao' => $orgao.' está fora do ar no momento — a indisponibilidade é no '
                    .'sistema do próprio órgão, não no FiscalDock. Já tentamos algumas vezes '
                    .'automaticamente sem retorno. Refaça a consulta mais tarde; se o problema '
                    .'persistir, entre em contato com o suporte.',
            ];
        }

        return [
            'estado' => 'erro_integracao',
            'label' => 'Erro com o site de consultas do provedor',
            'hex' => CertidaoBadge::HEX_FALHOU,
            'descricao' => 'O site de consultas oficial está instável no momento. Refaça a consulta.',
        ];
    }

    /** Texto curto p/ o tooltip rápido do badge (status + breve resumo). */
    private function descricaoCert(string $estado, string $label, ?string $motivo, ?string $mensagem): string
    {
        $msg = $this->trecho($mensagem);

        return match ($estado) {
            'regular' => $msg ?: 'Situação regular, sem pendências.',
            'atencao' => $msg ?: 'Pendência identificada na consulta.',
            'indeterminado' => $motivo ?: ($msg ?: 'Não foi possível emitir a certidão.'),
            default => $motivo ?: ($msg ?: ($label !== '' ? $label : 'Sem informação disponível.')),
        };
    }

    private function trecho(?string $texto): ?string
    {
        $t = $this->limpar($texto);

        return $t !== null ? \Illuminate\Support\Str::limit($t, 140) : null;
    }

    private function blocoFalhou(string $chave, string|array|null $origem = null): array
    {
        $erro = $this->erroCert($origem, $chave);

        return $this->bloco(
            $chave,
            $this->tituloCertidao($chave),
            ['label' => $erro['label'], 'hex' => $erro['hex']],
            [],
            [],
            $erro['descricao'],
        );
    }

    /**
     * Card NEUTRO de fonte que nunca foi consultada (esperada no universo "todas as consultas",
     * mas sem registro de resultado nem de erro). Diferente de FALHA: é oferta, não problema.
     */
    private function blocoNaoConsultada(string $chave): array
    {
        return $this->bloco(
            $chave,
            $this->tituloCertidao($chave),
            ['label' => 'Não consultada', 'hex' => '#9ca3af'],
            [],
            [],
            'Esta consulta ainda não foi realizada para este documento.',
        );
    }

    /** Buckets canônicos por cor do badge (usados na análise agregada e no rollup por CNPJ). */
    private const RANK = ['atencao' => 3, 'indeterminado' => 2, 'regular' => 1, 'neutro' => 0, 'falha' => 0];

    /**
     * Resumo escrito (1 parágrafo) da situação de UM participante — leitura rápida do que a
     * consulta apurou: situação cadastral e regularidade das certidões.
     */
    public function resumoTextual(ConsultaResultado $resultado): ?string
    {
        $dados = is_array($resultado->resultado_dados) ? $resultado->resultado_dados : [];
        if (empty($dados)) {
            return null;
        }

        $frases = [];

        $situacao = trim((string) ($dados['situacao_cadastral'] ?? ''));
        if ($situacao !== '') {
            $regime = trim((string) $this->regimeDisplay($dados));
            $frase = "Situação cadastral {$situacao} na Receita Federal";
            $frase .= $regime !== '' ? " ({$regime})." : '.';
            $frases[] = $frase;
        }

        // Certidões presentes → conta regulares × com pendência × indeterminadas.
        $certidoes = self::CERTIDOES_BADGE;
        $regulares = [];
        $pendencias = [];
        $indeterminadas = [];
        foreach ($certidoes as $chave) {
            if (! isset($dados[$chave]) || ! is_array($dados[$chave])) {
                continue;
            }
            $badge = CertidaoBadge::classificar($dados[$chave], true);
            $bucket = $this->bucketHex($badge['hex'] ?? null);
            $nome = $this->tituloCertidao($chave);
            match ($bucket) {
                'regular' => $regulares[] = $nome,
                'atencao' => $pendencias[] = $nome,
                'indeterminado' => $indeterminadas[] = $nome,
                default => null,
            };
        }

        if ($pendencias) {
            $frases[] = 'Pendências em: '.implode(', ', $pendencias).'.';
        }
        if ($regulares && ! $pendencias) {
            $frases[] = 'Certidões consultadas regulares ('.implode(', ', $regulares).').';
        } elseif ($regulares) {
            $frases[] = 'Regulares: '.implode(', ', $regulares).'.';
        }
        if ($indeterminadas) {
            $frases[] = 'Sem emissão (indeterminada): '.implode(', ', $indeterminadas).'.';
        }

        $texto = trim(implode(' ', array_filter($frases)));

        return $texto !== '' ? $texto : null;
    }

    /**
     * Deriva a situação geral (regular/atencao/irregular) e se há pendências
     * a partir do resultado_dados, reusando a mesma classificação de certidões
     * (CertidaoBadge) do resumo. Usado pelo monitoramento contínuo.
     *
     * @return array{situacao_geral: string, tem_pendencias: bool}
     */
    public function situacaoGeral(ConsultaResultado $resultado): array
    {
        $dados = is_array($resultado->resultado_dados) ? $resultado->resultado_dados : [];

        $temPendencia = false;      // certidão com pendência (atenção)
        $temIndeterminada = false;  // certidão sem emissão
        foreach (self::CERTIDOES_BADGE as $chave) {
            if (! isset($dados[$chave]) || ! is_array($dados[$chave])) {
                continue;
            }
            $bucket = $this->bucketHex(CertidaoBadge::classificar($dados[$chave], true)['hex'] ?? null);
            if ($bucket === 'atencao') {
                $temPendencia = true;
            } elseif ($bucket === 'indeterminado') {
                $temIndeterminada = true;
            }
        }

        // Situações cadastrais irregulares da Receita Federal (null/ATIVA não contam).
        $cadastral = strtoupper(trim((string) ($dados['situacao_cadastral'] ?? '')));
        $cadastralIrregular = in_array($cadastral, ['BAIXADA', 'INAPTA', 'SUSPENSA', 'NULA'], true);

        $situacaoGeral = match (true) {
            $temPendencia || $cadastralIrregular => 'irregular',
            $temIndeterminada => 'atencao',
            default => 'regular',
        };

        return [
            'situacao_geral' => $situacaoGeral,
            'tem_pendencias' => $temPendencia,
        ];
    }

    /**
     * Análise agregada do lote: contagem por fonte (regular/atenção/indeterminado/neutro),
     * rollup por CNPJ (pior status) e uma frase-resumo. Alimenta tabela + gráfico do topo.
     *
     * @param  iterable<array{detalhe_blocos?: array}>  $rows  linhas de detalhe (com detalhe_blocos)
     */
    public function analiseLote(iterable $rows): array
    {
        $rows = is_array($rows) ? $rows : iterator_to_array($rows);
        $total = count($rows);

        $fonteAgg = [];
        $cnpjs = ['regular' => 0, 'pendencia' => 0, 'indeterminado' => 0, 'sem_info' => 0];
        // Falhas de integração (retry/fatal/erro interno) são contadas por FONTE, não por CNPJ:
        // não dizem nada sobre regularidade (consulta nem rodou), mas precisam aparecer no gráfico
        // distintas de "não consultado". O rollup por CNPJ (regular/pendência/...) ignora falha.
        $falhasFontes = 0;

        foreach ($rows as $row) {
            $blocos = $row['detalhe_blocos'] ?? [];
            $piorRank = -1;

            foreach ($blocos as $b) {
                $chave = $b['chave'] ?? null;
                if ($chave === null || $chave === 'cadastro' || empty($b['badge'])) {
                    continue;
                }

                $bucket = $this->bucketHex($b['badge']['hex'] ?? null);

                if (! isset($fonteAgg[$chave])) {
                    $fonteAgg[$chave] = [
                        'chave' => $chave,
                        'titulo' => $b['titulo'] ?? $this->nomeFonte($chave),
                        'regular' => 0, 'atencao' => 0, 'indeterminado' => 0, 'falha' => 0, 'neutro' => 0, 'total' => 0,
                    ];
                }
                $fonteAgg[$chave][$bucket]++;
                $fonteAgg[$chave]['total']++;

                if ($bucket === 'falha') {
                    $falhasFontes++;
                }

                $piorRank = max($piorRank, self::RANK[$bucket]);
            }

            $cnpjs[match (true) {
                $piorRank === self::RANK['atencao'] => 'pendencia',
                $piorRank === self::RANK['indeterminado'] => 'indeterminado',
                $piorRank === self::RANK['regular'] => 'regular',
                default => 'sem_info',
            }]++;
        }

        // Ordena por_fonte na ordem canônica.
        $porFonte = [];
        foreach (self::ORDEM as $chave) {
            if (isset($fonteAgg[$chave])) {
                $porFonte[] = $fonteAgg[$chave];
            }
        }

        return [
            'total' => $total,
            'por_fonte' => $porFonte,
            'cnpjs' => $cnpjs,
            'falhas' => $falhasFontes,
            'texto' => $this->textoAnalise($total, $cnpjs, $falhasFontes),
        ];
    }

    private function textoAnalise(int $total, array $cnpjs, int $falhas = 0): string
    {
        if ($total === 0) {
            return 'Nenhum documento consultado neste lote.';
        }

        $plural = $total > 1 ? 'documentos consultados' : 'documento consultado';
        $partes = [];
        if ($cnpjs['regular'] > 0) {
            $partes[] = $cnpjs['regular'].' totalmente '.($cnpjs['regular'] > 1 ? 'regulares' : 'regular');
        }
        if ($cnpjs['pendencia'] > 0) {
            $partes[] = $cnpjs['pendencia'].' com '.($cnpjs['pendencia'] > 1 ? 'pendências' : 'pendência');
        }
        if ($cnpjs['indeterminado'] > 0) {
            $partes[] = $cnpjs['indeterminado'].' '.($cnpjs['indeterminado'] > 1 ? 'indeterminados' : 'indeterminado');
        }
        if ($cnpjs['sem_info'] > 0) {
            $partes[] = $cnpjs['sem_info'].' sem fontes de regularidade';
        }

        $detalhe = $partes ? ': '.$this->juntar($partes).'.' : '.';

        $sufixoFalha = $falhas > 0
            ? ' '.$falhas.' '.($falhas > 1 ? 'consultas falharam' : 'consulta falhou').' por instabilidade e podem ser reconsultadas.'
            : '';

        return "{$total} {$plural}{$detalhe}{$sufixoFalha}";
    }

    private function juntar(array $partes): string
    {
        if (count($partes) <= 1) {
            return implode('', $partes);
        }
        $ultimo = array_pop($partes);

        return implode(', ', $partes).' e '.$ultimo;
    }

    private function bucketHex(?string $hex): string
    {
        return match ($hex) {
            CertidaoBadge::HEX_REGULAR => 'regular',
            CertidaoBadge::HEX_IRREGULAR => 'atencao',
            CertidaoBadge::HEX_INDETERMINADO => 'indeterminado',
            CertidaoBadge::HEX_FALHOU, CertidaoBadge::HEX_ERRO_INTERNO => 'falha',
            default => 'neutro',
        };
    }

    private function nomeFonte(string $chave): string
    {
        return (string) config("consultas.fonte_nome.{$chave}", $chave);
    }

    private function bloco(string $chave, string $titulo, ?array $badge, array $itens, array $listas = [], ?string $mensagem = null, ?string $comprovante = null, ?string $nota = null): array
    {
        return [
            'chave' => $chave,
            'titulo' => $titulo,
            'badge' => $badge,
            'itens' => array_values(array_filter($itens, fn ($i) => ($i['valor'] ?? null) !== null && $i['valor'] !== '')),
            'listas' => array_values(array_filter($listas, fn ($l) => ! empty($l['linhas']))),
            'mensagem' => $this->limpar($mensagem),
            'comprovante_url' => $this->urlValida($comprovante),
            'nota' => $this->limpar($nota),
        ];
    }

    private function item(string $label, mixed $valor, ?string $tooltip = null): array
    {
        return ['label' => $label, 'valor' => $this->texto($valor), 'tooltip' => $this->limpar($tooltip)];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Cadastro (minhareceita)
    // ──────────────────────────────────────────────────────────────────────────

    private function blocoCadastro(array $d): ?array
    {
        $temCadastro = isset($d['razao_social']) || isset($d['situacao_cadastral']) || isset($d['cnaes']) || isset($d['qsa']);
        if (! $temCadastro) {
            return null;
        }

        $situacao = trim((string) ($d['situacao_cadastral'] ?? ''));
        $motivo = trim((string) ($d['motivo_situacao_cadastral'] ?? ''));

        $itens = [
            $this->item('Nome fantasia', $d['nome_fantasia'] ?? null),
            $this->item('Situação cadastral', $situacao !== '' ? $situacao : null, $motivo !== '' ? "Motivo: {$motivo}" : null),
            $this->item('Natureza jurídica', $d['natureza_juridica'] ?? null),
            $this->item('Porte', $d['porte'] ?? null),
            $this->item('Capital social', $this->moeda($d['capital_social'] ?? null)),
            $this->item('Matriz/Filial', isset($d['matriz_filial']) ? ucfirst((string) $d['matriz_filial']) : null),
            $this->item('Início de atividade', $d['data_inicio_atividade'] ?? null),
            $this->item('Regime tributário', $this->regimeDisplay($d), match ($d['regime_tributario_origem'] ?? null) {
                'matriz' => 'Publicado pela RFB no CNPJ da matriz (regime é da empresa).',
                'estimado' => (string) ($d['regime_tributario_nota'] ?? 'Estimado pelo sistema — a RFB não publica o regime deste CNPJ.'),
                default => null,
            }),
            $this->item('Endereço', $this->endereco($d['endereco'] ?? null)),
            $this->item('Telefone', $d['telefone_1'] ?? null),
        ];

        $listas = [
            $this->lista('CNAEs', array_map(function ($c) {
                $cod = trim((string) ($c['codigo'] ?? ''));
                $desc = trim((string) ($c['descricao'] ?? ''));
                $marca = ! empty($c['principal']) ? ' (principal)' : '';

                return trim(($cod !== '' ? "{$cod} — " : '').$desc).$marca;
            }, is_array($d['cnaes'] ?? null) ? $d['cnaes'] : [])),
            $this->lista('Quadro societário (QSA)', array_map(function ($s) {
                $nome = trim((string) ($s['nome'] ?? ''));
                $qual = trim((string) ($s['qualificacao'] ?? ''));
                $entrada = trim((string) ($s['data_entrada'] ?? ''));
                $extra = array_filter([$qual, $entrada !== '' ? "desde {$entrada}" : '']);

                return $nome.(! empty($extra) ? ' — '.implode(', ', $extra) : '');
            }, is_array($d['qsa'] ?? null) ? $d['qsa'] : [])),
            $this->lista('Histórico de regime (RFB)', array_map(function ($r) {
                $ano = trim((string) ($r['ano'] ?? ''));
                $forma = trim((string) ($r['forma'] ?? ''));

                return trim("{$ano} — {$forma}", ' —');
            }, is_array($d['regime_tributario_historico'] ?? null) ? $d['regime_tributario_historico'] : [])),
        ];

        // Badge da situação cadastral — mesma régua do RiskScoreService::scoreCadastral
        // (ATIVA=0, SUSPENSA=50, INAPTA/BAIXADA/NULA=100): a situação já pesa 15% no
        // score, então ganha badge em "Regularidade & Score" como as certidões.
        $badge = null;
        if ($situacao !== '') {
            $badge = match (mb_strtoupper($situacao)) {
                'ATIVA' => ['label' => 'Ativa', 'hex' => CertidaoBadge::HEX_REGULAR],
                'SUSPENSA' => ['label' => 'Suspensa', 'hex' => CertidaoBadge::HEX_INDETERMINADO],
                'INAPTA', 'BAIXADA', 'NULA' => ['label' => ucfirst(mb_strtolower($situacao)), 'hex' => CertidaoBadge::HEX_IRREGULAR],
                default => ['label' => mb_strtoupper($situacao), 'hex' => CertidaoBadge::HEX_OUTRO],
            };
        }

        return $this->bloco('cadastro', 'Dados cadastrais', $badge, $itens, $listas);
    }

    private function blocoCadastroPf(array $d, int|string|null $resultadoId = null): array
    {
        $situacao = trim((string) ($d['situacao_cadastral'] ?? $d['status'] ?? ''));
        $badge = ! empty($d['falecido'])
            ? ['label' => 'Óbito informado', 'hex' => CertidaoBadge::HEX_IRREGULAR]
            : CertidaoBadge::classificar(['status' => $situacao], true);

        return $this->bloco(
            'cadastro_pf',
            'Cadastro e situação do CPF',
            $badge,
            [
                $this->item('Nome', $d['nome'] ?? null),
                $this->item('Nome civil', $d['nome_civil'] ?? null),
                $this->item('Nome social', $d['nome_social'] ?? null),
                $this->item('CPF', isset($d['cpf']) ? Cpf::formatar((string) $d['cpf']) : null),
                $this->item('Situação cadastral', $situacao !== '' ? $situacao : null),
                $this->item('Nascimento', $d['data_nascimento'] ?? null),
                $this->item('Inscrição no CPF', $d['data_inscricao'] ?? null),
                $this->item('Ano do óbito', $d['ano_obito'] ?? null),
                $this->item('Consulta realizada em', $d['consulta_em'] ?? null),
            ],
            [],
            ! empty($d['falecido']) ? 'A Receita Federal informa ano de óbito para este CPF.' : null,
            $this->comprovanteUrl($resultadoId, 'cadastro_pf', $d),
        );
    }

    private function blocoQuitacaoEleitoral(array $d, int|string|null $resultadoId = null): array
    {
        $domicilio = is_array($d['domicilio_eleitoral'] ?? null) ? $d['domicilio_eleitoral'] : [];
        $domicilioTexto = $this->juntar(array_values(array_filter([
            $domicilio['municipio'] ?? null,
            $domicilio['uf'] ?? null,
            isset($domicilio['zona']) ? 'Zona '.$domicilio['zona'] : null,
            isset($domicilio['secao']) ? 'Seção '.$domicilio['secao'] : null,
        ])));

        return $this->bloco(
            'quitacao_eleitoral',
            'Quitação Eleitoral (TSE)',
            CertidaoBadge::classificar($d, true),
            [
                $this->item('Situação informada', $d['status'] ?? null),
                $this->item('Nome', $d['nome'] ?? null),
                $this->item('Nascimento', $d['data_nascimento'] ?? null),
                $this->item('Título eleitoral', $d['titulo_eleitoral'] ?? null),
                $this->item('Biometria coletada', $this->simNao($d['biometria_coletada'] ?? null)),
                $this->item('Certidão nº', $d['certidao_codigo'] ?? null),
                $this->item('Emissão', $d['emissao_data'] ?? null),
                $this->item('Domicílio eleitoral', $domicilioTexto),
            ],
            [],
            $d['mensagem'] ?? null,
            $this->comprovanteUrl($resultadoId, 'quitacao_eleitoral', $d),
        );
    }

    private function blocoMandadoPrisao(array $d): array
    {
        $registros = is_array($d['registros'] ?? null) ? $d['registros'] : [];
        $linhas = array_map(function (array $registro): string {
            $identificador = trim((string) ($registro['mandado'] ?? ''));
            $processo = trim((string) ($registro['processo'] ?? ''));
            $cabecalho = $identificador !== '' ? "Mandado {$identificador}" : 'Mandado';
            if ($processo !== '') {
                $cabecalho .= " · Processo {$processo}";
            }

            $detalhes = array_values(array_filter([
                $registro['situacao'] ?? null,
                $registro['tribunal'] ?? $registro['orgao_judicial'] ?? null,
                $registro['especie_prisao'] ?? null,
                $registro['normalizado_validade_data'] ?? $registro['validade_data'] ?? null,
            ]));

            return $cabecalho.($detalhes !== [] ? ' — '.implode(' · ', $detalhes) : '');
        }, $registros);

        return $this->bloco(
            'mandado_prisao',
            'Mandados de Prisão vigentes (CNJ/BNMP)',
            CertidaoBadge::classificar($d, true),
            [
                $this->item('Situação informada', $d['status'] ?? null),
                $this->item('Mandados vigentes', $d['total_registros'] ?? count($registros)),
            ],
            [$this->lista('Mandados encontrados', $linhas)],
            $d['mensagem'] ?? null,
        );
    }

    /**
     * Card comum das fontes públicas de expansão. Preserva totais/paginação e transforma os
     * subconjuntos normalizados de registros em linhas legíveis, sem expor o payload bruto.
     */
    private function blocoConsultaPublica(
        string $chave,
        array $d,
        int|string|null $resultadoId = null,
    ): array {
        $registros = is_array($d['registros'] ?? null)
            ? $d['registros']
            : (is_array($d['processos'] ?? null) ? $d['processos'] : []);
        $rotulos = [
            'numero' => 'Nº',
            'processo' => 'Processo',
            'acordao' => 'Acórdão',
            'marca' => 'Marca',
            'registro' => 'Registro',
            'tipo' => 'Tipo',
            'data' => 'Data',
            'prioridade' => 'Prioridade',
            'situacao' => 'Situação',
            'status_debito' => 'Débito',
            'valor_multa' => 'Multa',
            'estado' => 'UF',
            'municipio' => 'Município',
            'classe' => 'Classe',
            'entrada_cadastro' => 'Entrada',
            'saida_cadastro' => 'Saída',
        ];
        $linhas = array_map(function ($registro) use ($rotulos): string {
            $partes = [];
            foreach ($rotulos as $campo => $rotulo) {
                $valor = trim((string) (((array) $registro)[$campo] ?? ''));
                if ($valor !== '') {
                    $partes[] = "{$rotulo}: {$valor}";
                }
            }

            return $partes !== [] ? implode(' · ', $partes) : 'Registro retornado pela fonte.';
        }, $registros);

        $badge = $chave === 'bcb_valores_receber'
            ? null
            : CertidaoBadge::classificar($d, true);
        $itens = [
            $this->item('Situação informada', $d['status'] ?? null),
            $this->item('Nome', $d['nome'] ?? $d['interessado'] ?? null),
            $this->item('Documento', $d['cpf_cnpj'] ?? null),
            $this->item('Certidão nº', $d['certidao_codigo'] ?? null),
            $this->item('Emissão', $d['emissao_data'] ?? null),
            $this->item('Validade', $d['data_validade'] ?? null),
            $this->item('Total de registros', $d['total_registros'] ?? $d['total_processos'] ?? null),
            $this->item('Valor total', $d['valor_infracoes'] ?? $d['total_divida'] ?? null),
            $this->item('Página', $d['pagina_atual'] ?? $d['pagina'] ?? null),
            $this->item('Total de páginas', $d['total_paginas'] ?? null),
            $this->item(
                'Possui valores a receber',
                array_key_exists('possui_valores_receber', $d)
                    ? $this->simNao($d['possui_valores_receber'])
                    : null,
            ),
        ];

        return $this->bloco(
            $chave,
            $this->nomeFonte($chave),
            $badge,
            $itens,
            [$this->lista('Registros encontrados', $linhas)],
            $d['mensagem'] ?? null,
            $this->comprovanteUrl($resultadoId, $chave, $d),
            ! empty($d['tem_mais_paginas'])
                ? 'Existem páginas adicionais; elas não são consultadas automaticamente para evitar novas cobranças.'
                : null,
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Certidões (CND Federal/Estadual/Municipal, FGTS, CNDT)
    // ──────────────────────────────────────────────────────────────────────────

    private function blocoCertidao(
        string $chave,
        array $d,
        ?string $ufFallback = null,
        ?string $nota = null,
        int|string|null $resultadoId = null,
    ): array {
        $badge = CertidaoBadge::classificar($d, true);

        // CND Estadual/Municipal: a resposta pode vir sem UF — completa com a UF do alvo.
        $uf = trim((string) ($d['uf'] ?? '')) ?: ($chave === 'cnd_estadual' || $chave === 'cnd_municipal' ? $ufFallback : null);

        // O `status` das certidões sem `tipo` é DERIVADO por nós de conseguiu_emitir=false
        // ("Positiva") — quando a classificação é INDETERMINADA (nada foi emitido), exibir
        // "Positiva" engana; mostra o fato real: a fonte não emitiu online.
        $situacaoInformada = ! empty($badge['indeterminado']) ? 'Sem emissão online' : ($d['status'] ?? null);

        $itens = [
            $this->item('Situação informada', $situacaoInformada),
            $this->item('UF', $uf),
            $this->item('Município', $d['municipio'] ?? null),
            $this->item('Certidão nº', $d['certidao_codigo'] ?? null),
            $this->item('Emissão', $d['emissao_data'] ?? null),
            $this->item('Validade', $d['data_validade'] ?? null),
        ];

        if ($chave === 'cnd_federal') {
            $itens[] = $this->item('Débitos RFB', $this->simNao($d['debitos_rfb'] ?? null));
            $itens[] = $this->item('Débitos PGFN', $this->simNao($d['debitos_pgfn'] ?? null));
        }

        $mensagem = $d['mensagem'] ?? ($badge['motivo'] ?? null);
        // FGTS/Caixa não devolve a frase da certidão (só `situacao`); compõe uma linha-resumo
        // equivalente à das demais certidões, a partir do status + validade do CRF.
        if (! $mensagem && $chave === 'crf_fgts') {
            $mensagem = $this->resumoFgts($d, $badge);
        }

        // Certidão INDETERMINADA (fonte recusou a emissão online): a mensagem oficial é jurídica
        // e crua — a nota traduz o que significa, onde o contribuinte verifica e o que NÃO prova.
        if (! empty($badge['indeterminado'])) {
            $notaSemEmissao = $this->notaSemEmissao($chave, $uf);
            $nota = trim(($nota ? $nota.' ' : '').$notaSemEmissao) ?: null;
        }

        return $this->bloco(
            $chave,
            $this->tituloCertidao($chave, $uf),
            $badge,
            $itens,
            [],
            $mensagem,
            $this->comprovanteUrl($resultadoId, $chave, $d),
            $nota,
        );
    }

    /** Linha-resumo do CRF FGTS (a Caixa não devolve frase pronta como as demais certidões). */
    private function resumoFgts(array $d, ?array $badge): ?string
    {
        if (($badge['label'] ?? null) === 'Regular') {
            $validade = $d['data_validade'] ?? null;

            return 'Empregador em situação regular perante o FGTS'
                .($validade ? " — Certificado de Regularidade (CRF) válido até {$validade}." : '.');
        }

        return 'Empregador sem Certificado de Regularidade do FGTS válido perante a Caixa.';
    }

    /**
     * Texto explicativo (retrátil) para a CND Federal quando o CNPJ consultado é uma filial:
     * a certidão federal (RFB/PGFN) é unificada por base e só emite para a matriz (regra da Receita).
     * Retorna null quando o alvo é matriz ou o documento não é um CNPJ completo.
     */
    private function notaMatrizFederal(ConsultaResultado $resultado): ?string
    {
        $cnpj = $resultado->participante?->documento ?: $resultado->cliente?->documento;
        if (! $cnpj) {
            return null;
        }

        // Indicador oficial (RFB) tem prioridade sobre a ORDEM do CNPJ — já vimos matriz real
        // fora de 0001 (ver lote #220), o que faria a ordem sozinha mostrar a nota errada.
        $matrizFilial = $resultado->getDado('matriz_filial');
        $ehFilial = $matrizFilial !== null ? $matrizFilial === 'filial' : Cnpj::ehFilial((string) $cnpj);
        if (! $ehFilial) {
            return null;
        }

        $matriz = Cnpj::formatar(Cnpj::matriz((string) $cnpj));

        return "A CND Federal foi emitida para o CNPJ da matriz ({$matriz}), não para a filial consultada. "
            .'É uma exigência da Receita Federal: a certidão de débitos federais (RFB/PGFN) é unificada por base de CNPJ '
            .'e só é emitida para a matriz, valendo para a empresa inteira (matriz e filiais). '
            .'Os demais dados desta consulta — cadastro, CND estadual/municipal, FGTS, SINTEGRA — referem-se ao CNPJ consultado.';
    }

    /**
     * Tradução didática do caso "Indeterminada — sem emissão": por que a fonte oficial recusou
     * a certidão online, onde o CONTRIBUINTE verifica o detalhe (que não é público) e o aviso de
     * que a recusa, sozinha, não comprova irregularidade. Complementa a mensagem oficial do órgão.
     */
    private function notaSemEmissao(string $chave, ?string $uf): string
    {
        $ufU = $uf ? strtoupper($uf) : null;

        return match ($chave) {
            'cnd_estadual' => 'O que isso significa: a própria SEFAZ'.($ufU ? "-{$ufU}" : '').' recusou a emissão online — '
                .'o sistema estadual acusa pendência (débito em aberto ou obrigação acessória, como declaração não entregue) '
                .'em algum estabelecimento da empresa no estado, pois a regra considera matriz e filiais em conjunto. '
                .'O detalhe da pendência não é público: '.$this->ondeVerificarEstadual($ufU)
                .' Sem certidão emitida, a recusa por si só não comprova irregularidade fiscal.',
            'cnd_municipal' => 'O que isso significa: a prefeitura não emitiu a certidão pela internet — pode haver pendência '
                .'ou situação cadastral municipal que exige verificação. O detalhe é restrito ao contribuinte, no portal da '
                .'prefeitura ou no atendimento presencial. Sem certidão emitida, a recusa por si só não comprova irregularidade fiscal.',
            'cnd_federal' => 'O que isso significa: a Receita Federal/PGFN não conseguiu emitir a certidão pela internet — '
                .'normalmente há pendência ou dados em análise que exigem verificação do próprio contribuinte, pelo e-CAC '
                .'(cav.receita.fazenda.gov.br) ou em uma unidade da RFB. Sem certidão emitida, a recusa por si só não comprova irregularidade fiscal.',
            'crf_fgts' => 'O que isso significa: a Caixa recusou a emissão do CRF FGTS pela internet, geralmente por constar '
                .'impedimento junto à Caixa e/ou à PGFN. O detalhe é restrito ao empregador, no Conectividade Social '
                .'(conectividadesocialv2.caixa.gov.br) ou no Regularize da PGFN (regularize.pgfn.gov.br). '
                .'Sem certidão emitida, a recusa por si só não comprova irregularidade fiscal.',
            default => 'O que isso significa: a fonte oficial não emitiu a certidão pela internet. O detalhe do motivo é '
                .'restrito ao próprio contribuinte, no portal do órgão ou no atendimento presencial. Sem certidão emitida, '
                .'a recusa por si só não comprova irregularidade fiscal.',
        };
    }

    /** Onde o contribuinte verifica a pendência estadual — orientação específica por UF quando conhecida. */
    private function ondeVerificarEstadual(?string $uf): string
    {
        return match ($uf) {
            'MS' => 'só o contribuinte logado no Portal e-Fazenda (efazenda.servicos.ms.gov.br) ou uma Agência Fazendária consegue vê-lo.',
            'SP' => 'só o contribuinte, no "Relatório de Pendências Fiscais" do portal da SEFAZ-SP (acesso com certificado digital), '
                .'consegue vê-lo — e a certidão pode ser pedida em papel pelo SIPET, com análise manual.',
            default => 'só o próprio contribuinte, no portal da SEFAZ (com login ou certificado digital) ou no atendimento presencial, consegue vê-lo.',
        };
    }

    private function tituloCertidao(string $chave, ?string $uf = null): string
    {
        // SEFAZ é estadual: mostrar a UF no título dá contexto imediato (ex.: "CND Estadual (SEFAZ-MS)").
        $sufixoUf = $uf ? '-'.strtoupper($uf) : '';

        return match ($chave) {
            'cnd_federal' => 'CND Federal (Receita/PGFN)',
            'cnd_estadual' => 'CND Estadual (SEFAZ'.$sufixoUf.')',
            'cnd_municipal' => 'CND Municipal'.($uf ? ' ('.strtoupper($uf).')' : ''),
            'crf_fgts' => 'CRF FGTS (Caixa)',
            'cndt' => 'CNDT (débitos trabalhistas)',
            default => $this->nomeFonte($chave),
        };
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SINTEGRA
    // ──────────────────────────────────────────────────────────────────────────

    private function blocoSintegra(
        array $d,
        ?string $ufFallback = null,
        int|string|null $resultadoId = null,
    ): array {
        $badge = CertidaoBadge::classificar(['situacao' => $d['situacao'] ?? null]);

        $itens = [
            $this->item('Situação', $d['situacao'] ?? null),
            $this->item('Inscrição estadual', $d['inscricao_estadual'] ?? null),
            $this->item('UF', trim((string) ($d['uf'] ?? '')) ?: $ufFallback),
            $this->item('Regime de apuração', $d['regime_apuracao'] ?? null),
            $this->item('Atividade econômica', $d['atividade_economica'] ?? null),
            $this->item('Data da situação', $d['data_situacao'] ?? null),
        ];

        $uf = trim((string) ($d['uf'] ?? '')) ?: $ufFallback;
        // SINTEGRA tb não traz frase pronta — compõe a partir de situação/UF/IE.
        $mensagem = $d['mensagem'] ?? $this->resumoSintegra($d, $uf);

        return $this->bloco(
            'sintegra',
            'SINTEGRA',
            $badge,
            $itens,
            [],
            $mensagem,
            $this->comprovanteUrl($resultadoId, 'sintegra', $d),
        );
    }

    /** Linha-resumo do SINTEGRA (o provedor não devolve frase; deriva de situação/UF/IE). */
    private function resumoSintegra(array $d, ?string $uf): ?string
    {
        $sit = trim((string) ($d['situacao'] ?? ''));
        if ($sit === '') {
            return null;
        }

        $ufTxt = $uf ? '-'.strtoupper($uf) : '';
        $ie = trim((string) ($d['inscricao_estadual'] ?? ''));
        $ieTxt = $ie !== '' ? " (IE {$ie})" : '';

        return "Contribuinte {$sit} no cadastro SINTEGRA{$ufTxt}{$ieTxt}.";
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function lista(string $titulo, array $linhas): array
    {
        return ['titulo' => $titulo, 'linhas' => array_values(array_filter(array_map('trim', $linhas), fn ($l) => $l !== ''))];
    }

    private function texto(mixed $v): ?string
    {
        if ($v === null || $v === '' || (is_array($v) && empty($v))) {
            return null;
        }
        if (is_bool($v)) {
            return $v ? 'Sim' : 'Não';
        }

        return is_scalar($v) ? trim((string) $v) : null;
    }

    private function simNao(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }

        return $v ? 'Sim' : 'Não';
    }

    private function moeda(mixed $v): ?string
    {
        if ($v === null || $v === '' || ! is_numeric($v)) {
            return null;
        }

        return 'R$ '.number_format((float) $v, 2, ',', '.');
    }

    /** Regime estimado nunca se apresenta como dado oficial — o valor carrega a marca. */
    private function regimeDisplay(array $d): ?string
    {
        $regime = trim((string) ($d['regime_tributario'] ?? ''));
        if ($regime === '') {
            return null;
        }

        return ($d['regime_tributario_origem'] ?? null) === 'estimado'
            ? $regime.' (estimado)'
            : $regime;
    }

    private function endereco(mixed $e): ?string
    {
        if (! is_array($e)) {
            return null;
        }

        $logradouro = trim(implode(' ', array_filter([
            trim((string) ($e['tipo_logradouro'] ?? '')),
            trim((string) ($e['logradouro'] ?? '')),
        ])));
        $partes = array_filter([
            $logradouro,
            trim((string) ($e['numero'] ?? '')),
            trim((string) ($e['bairro'] ?? '')),
            trim(implode('/', array_filter([trim((string) ($e['municipio'] ?? '')), trim((string) ($e['uf'] ?? ''))]))),
            trim((string) ($e['cep'] ?? '')),
        ], fn ($p) => $p !== '');

        $texto = implode(', ', $partes);

        return $texto !== '' ? $texto : null;
    }

    private function limpar(?string $texto): ?string
    {
        if ($texto === null) {
            return null;
        }
        // Neutraliza qualquer referência ao provedor terceirizado antes de exibir.
        $texto = preg_replace('/\s+/u', ' ', trim((string) MensagemPublica::neutralizar($texto)));

        return $texto !== '' ? $texto : null;
    }

    private function urlValida(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    private function comprovanteUrl(int|string|null $resultadoId, string $fonte, array $dados): ?string
    {
        if ($resultadoId !== null && ! empty($dados['comprovante_arquivo'])) {
            return route('app.consulta.comprovante', [
                'resultado' => $resultadoId,
                'fonte' => $fonte,
            ]);
        }

        return $this->urlValida($dados['comprovante'] ?? null);
    }
}
