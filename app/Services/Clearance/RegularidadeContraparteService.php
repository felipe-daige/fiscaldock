<?php

namespace App\Services\Clearance;

use App\Jobs\ProcessarConsultaJob;
use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\CteConsulta;
use App\Models\NfeConsulta;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use App\Services\Consultas\FecharLoteService;
use App\Services\Consultas\FonteRegistry;
use App\Services\SaldoService;
use App\Support\CertidaoBadge;
use App\Support\Cnpj;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;

/**
 * Clearance Full — Camada A: investiga a REGULARIDADE da CONTRAPARTE (participante
 * externo) das notas conferidas — situação cadastral (grátis) + SINTEGRA + CND Federal.
 * Reusa integralmente o motor da Consulta CNPJ (ProcessarConsultaJob →
 * consulta_resultados → FecharLoteService → participante_scores). NÃO recomputa nada
 * próprio: participante_scores é a fonte canônica de regularidade.
 *
 * Idempotente por FRESCURA: só consulta participantes stale/missing (dedup por CNPJ);
 * os fresh reaproveitam o participante_scores sem custo nem chamada externa.
 *
 * Billing PER-CNPJ (quando $cobrar): debita `preco_regularidade_creditos` por
 * participante efetivamente consultado. Estorno de fonte com falha é reconciliado pelo
 * FecharLoteService (mesma settlement da Consulta CNPJ). A empresa do PRÓPRIO usuário nunca
 * é alvo: ela tem cadastro em `clientes` e frequentemente TAMBÉM em `participantes`, então o
 * filtro é por documento de cliente (ver `participantesContraparte`), não por tabela.
 *
 * Spec: docs/clearance/clearance-full-camada-a.md
 */
class RegularidadeContraparteService
{
    public function __construct(
        private FonteRegistry $registry,
        private CnpjMascaradoResolver $mascarado,
        private SaldoService $saldoService,
    ) {}

    /**
     * Resolve as contrapartes das notas de um lote de clearance (pelos snapshots persistidos) e
     * investiga a regularidade delas.
     *
     * NÃO cobra aqui: o Clearance completo é preço FECHADO por nota (R$ 2,00 —
     * `ValidacaoContabilService::CUSTO_DOCUMENTO_FULL`), já debitado ao iniciar o lote. Dedup e
     * cache de frescura continuam valendo: eles reduzem o CUSTO EXTERNO (margem), não o preço.
     */
    public function investigarPorLoteClearance(int $loteId, int $userId, ?string $tabId = null, ?int $fecharClearanceLoteId = null): array
    {
        $cnpjs = $this->cnpjsContrapartesDoLote($loteId, $userId);

        if ($cnpjs->isEmpty()) {
            return $this->vazio();
        }

        $ids = $this->participantesContraparte($userId, $cnpjs)->pluck('id');

        return $this->investigar($userId, $ids, $tabId, $fecharClearanceLoteId);
    }

    /**
     * Participantes que são CONTRAPARTE (não a empresa do próprio usuário) para os CNPJs dados.
     *
     * O mesmo CNPJ pode existir em `clientes` E em `participantes` — a empresa própria costuma ter
     * cadastro nas duas tabelas (ver `ValidacaoContabilService::participanteDaEmpresaPropriaId`).
     * Filtrar só por "está em participantes" faria a empresa DO USUÁRIO virar contraparte: ela
     * apareceria no bloco de regularidade e — pior — seria COBRADA como contraparte nova.
     * Contraparte = participante cujo documento NÃO é de nenhum cliente do usuário.
     *
     * @param  Collection<int,string>  $cnpjs
     * @return Collection<int,Participante>
     */
    private function participantesContraparte(int $userId, Collection $cnpjs): Collection
    {
        $docsClientes = Cliente::where('user_id', $userId)
            ->pluck('documento')
            ->map(fn ($d) => Cnpj::digitos((string) $d))
            ->filter()
            ->flip();

        return Participante::where('user_id', $userId)
            ->whereIn('documento', $cnpjs->all())
            ->get()
            ->reject(fn (Participante $p) => $docsClientes->has(Cnpj::digitos((string) $p->documento)))
            ->values();
    }

    /**
     * READ-SIDE da tela de resultado: regularidade das contrapartes das notas do lote.
     *
     * Lê de `participante_scores` (fonte canônica) e classifica com o MESMO motor das outras
     * telas — `CruzamentosConsultasClearanceService::motivosIrregularidade` (situação cadastral
     * + subscore de certidão). Não reclassifica nada aqui: features do mesmo domínio não podem
     * divergir.
     *
     * @return array{ativo:bool, contrapartes:array<int,array<string,mixed>>, total:int, irregulares:int, pendentes:int}
     */
    public function resumoPorLoteClearance(int $loteId, int $userId): array
    {
        $vazio = ['ativo' => false, 'contrapartes' => [], 'total' => 0, 'irregulares' => 0, 'pendentes' => 0];

        if (! config('clearance.full.habilitado')) {
            return $vazio;
        }

        // Só lote CONTRATADO como completo mostra o bloco. Num lote básico nenhuma consulta de
        // regularidade foi disparada — exibir contraparte "em apuração" ali seria mentira (nada
        // vem responder). O tier fica no resultado_resumo, gravado ao iniciar o lote.
        $lote = ConsultaLote::where('id', $loteId)->where('user_id', $userId)->first();
        if (($lote?->resultado_resumo['tier'] ?? null) !== 'full') {
            return $vazio;
        }

        $notas = $this->notasDoLote($loteId, $userId);
        if ($notas->isEmpty()) {
            return $vazio;
        }

        // Só contrapartes: o CNPJ do próprio usuário (Cliente) costuma ter cadastro em
        // `participantes` também e não pode entrar aqui. Mesma regra do caminho de cobrança.
        $cnpjs = $notas->flatMap(fn ($n) => [$n->emit_cnpj, $n->dest_cnpj])
            ->filter()
            ->map(fn ($c) => Cnpj::digitos((string) $c))
            ->unique()
            ->values();

        $participantes = $this->participantesContraparte($userId, $cnpjs)
            ->keyBy(fn (Participante $p) => Cnpj::digitos((string) $p->documento));

        if ($participantes->isEmpty()) {
            return $vazio;
        }

        $scores = ParticipanteScore::where('user_id', $userId)
            ->whereIn('participante_id', $participantes->pluck('id'))
            ->get()
            ->keyBy('participante_id');

        $cruzamentos = app(\App\Services\Bi\CruzamentosConsultasClearanceService::class);
        $detalhePresenter = app(\App\Services\Consultas\ResultadoDetalhePresenter::class);

        $contrapartes = [];
        foreach ($notas as $nota) {
            foreach ([$nota->emit_cnpj, $nota->dest_cnpj] as $cnpj) {
                $doc = Cnpj::digitos((string) $cnpj);
                $p = $participantes->get($doc);
                if (! $p) {
                    continue; // empresa própria (Cliente) ou contraparte sem cadastro (ex.: mascarada)
                }

                if (! isset($contrapartes[$p->id])) {
                    $score = $scores->get($p->id);
                    $motivos = $score ? $cruzamentos->motivosIrregularidade($score) : [];

                    $contrapartes[$p->id] = [
                        'participante_id' => $p->id,
                        'documento' => $p->documento,
                        'razao_social' => $p->razao_social,
                        'consultado_em' => $score?->ultima_consulta_em,
                        'classificacao' => $score?->classificacao,
                        'certidoes' => $score ? $this->badgesCertidoes($score) : [],
                        'motivos' => $motivos,
                        'irregular' => $motivos !== [],
                        'pendente' => $score === null,
                        // Resultado do CNPJ — o mesmo detalhe da Consulta CNPJ
                        // (ResultadoDetalhePresenter + partial `detalhe-blocos`). Não é uma
                        // segunda leitura: é a MESMA que o Score Fiscal usa.
                        //
                        // Escopo do CLEARANCE: só as 3 fontes que ele cobre. A última consulta do
                        // participante pode ser de uma Consulta CNPJ de plano maior (o clearance
                        // reusa o cache), e sem o filtro o wrapper mostrava EST/MUN/FGTS/CNDT —
                        // certidões que este produto NÃO consultou.
                        'detalhe' => $detalhePresenter->detalheDoParticipante(
                            $p,
                            somenteConsultadas: true,
                            somenteFontes: $this->chavesDasFontes(),
                        ),
                        'notas' => [],
                    ];
                }

                $contrapartes[$p->id]['notas'][] = [
                    'chave' => $nota->chave_acesso,
                    'numero' => $nota->numero,
                    'status' => $nota->status,
                    // O cruzamento que só o clearance enxerga: documento VÁLIDO na SEFAZ,
                    // emitido por contraparte IRREGULAR (crédito exposto a glosa).
                    'alerta' => $nota->status === 'AUTORIZADA' && $contrapartes[$p->id]['irregular'],
                ];
            }
        }

        $lista = array_values($contrapartes);
        usort($lista, fn ($a, $b) => [$b['irregular'], $a['pendente']] <=> [$a['irregular'], $b['pendente']]);

        return [
            'ativo' => true,
            'contrapartes' => $lista,
            'total' => count($lista),
            'irregulares' => count(array_filter($lista, fn ($c) => $c['irregular'])),
            'pendentes' => count(array_filter($lista, fn ($c) => $c['pendente'])),
        ];
    }

    /** Badges das 3 fontes da Camada A, classificadas pelo CertidaoBadge canônico. */
    private function badgesCertidoes(ParticipanteScore $score): array
    {
        $dados = is_array($score->dados_consultados) ? $score->dados_consultados : [];

        $situacao = strtoupper(trim((string) ($dados['situacao_cadastral'] ?? '')));
        $cadastral = match (true) {
            $situacao === '' => ['label' => '—', 'hex' => CertidaoBadge::HEX_NEUTRO],
            in_array($situacao, ['BAIXADA', 'INAPTA', 'SUSPENSA', 'NULA'], true) => ['label' => $situacao, 'hex' => CertidaoBadge::HEX_IRREGULAR],
            $situacao === 'ATIVA' => ['label' => 'Ativa', 'hex' => CertidaoBadge::HEX_REGULAR],
            default => ['label' => $situacao, 'hex' => CertidaoBadge::HEX_OUTRO],
        };

        return [
            ['label' => 'Situação cadastral', 'badge' => $cadastral],
            ['label' => 'SINTEGRA (IE)', 'badge' => CertidaoBadge::classificar($dados['sintegra'] ?? null)],
            ['label' => 'CND Federal', 'badge' => CertidaoBadge::classificar($dados['cnd_federal'] ?? null, true)],
        ];
    }

    /** Notas (snapshots) do lote — NF-e + CT-e. */
    private function notasDoLote(int $loteId, int $userId): Collection
    {
        $cols = ['chave_acesso', 'numero', 'status', 'emit_cnpj', 'dest_cnpj'];

        return NfeConsulta::where('user_id', $userId)->where('consulta_lote_id', $loteId)->get($cols)
            ->concat(CteConsulta::where('user_id', $userId)->where('consulta_lote_id', $loteId)->get($cols));
    }

    /**
     * Investiga a regularidade dos participantes (contrapartes) informados.
     *
     * SEM cobrança: o Clearance completo já foi pago por nota (preço fechado). Dedup + frescura
     * seguem valendo — economizam a CHAMADA externa, não o preço.
     *
     * @param  iterable<int>  $participanteIds
     * @return array{consultados:int, reusados:int, ignorados:int, lote_id:?int}
     */
    public function investigar(int $userId, iterable $participanteIds, ?string $tabId = null, ?int $fecharClearanceLoteId = null): array
    {
        $incluidas = $this->consultasIncluidas();

        // Sem cobertura InfoSimples (gate desligado ou sem token) → não há o que consultar.
        if (! $this->registry->cobre($incluidas)) {
            return $this->vazio();
        }

        $ids = collect($participanteIds)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return $this->vazio();
        }

        $participantes = Participante::where('user_id', $userId)->whereIn('id', $ids)->get();

        // CNPJ mascarado (00000...) ou inválido nunca vira consulta — não chuta documento.
        [$consultaveis, $ignorados] = $participantes
            ->partition(fn (Participante $p) => $this->cnpjConsultavel($p->documento));

        // Dedup por CNPJ real: N notas do mesmo fornecedor = 1 consulta.
        $consultaveis = $consultaveis
            ->unique(fn (Participante $p) => Cnpj::digitos((string) $p->documento))
            ->values();

        if ($consultaveis->isEmpty()) {
            return $this->vazio(ignorados: $ignorados->count());
        }

        [$stale, $fresh] = $this->particionarPorFrescura($userId, $consultaveis);

        if ($stale->isEmpty()) {
            return $this->vazio(reusados: $fresh->count(), ignorados: $ignorados->count());
        }

        $loteId = $this->dispararConsulta($userId, $stale, $incluidas, $tabId, $fecharClearanceLoteId);

        return [
            'consultados' => $stale->count(),
            'reusados' => $fresh->count(),
            'ignorados' => $ignorados->count(),
            'lote_id' => $loteId,
        ];
    }

    /**
     * Separa em [stale, fresh].
     *
     * `fresh` exige DUAS coisas: consulta dentro da janela **E** o dado das 3 fontes que o
     * Clearance completo promete. Só a data não basta — um `participante_scores` gravado por uma
     * Consulta CNPJ de plano menor (só cadastral) é recente mas **não tem** SINTEGRA nem CND
     * Federal. Reusá-lo entregava 1 fonte a quem pagou por 3 (bug real, 2026-07-13: contraparte
     * aparecia com "Situação cadastral: Ativa · SINTEGRA — · CND Federal —").
     *
     * @return array{0: Collection<int,Participante>, 1: Collection<int,Participante>}
     */
    private function particionarPorFrescura(int $userId, Collection $participantes): array
    {
        $janela = now()->subDays($this->frescuraDias());

        $scores = ParticipanteScore::where('user_id', $userId)
            ->whereIn('participante_id', $participantes->pluck('id'))
            ->where('ultima_consulta_em', '>=', $janela)
            ->get()
            ->keyBy('participante_id');

        [$fresh, $stale] = $participantes->partition(function (Participante $p) use ($scores) {
            $score = $scores->get($p->id);

            return $score !== null && $this->temTodasAsFontes($score);
        });

        return [$stale->values(), $fresh->values()];
    }

    /** O score em cache cobre as 3 fontes do Clearance completo? (senão, reconsulta) */
    private function temTodasAsFontes(ParticipanteScore $score): bool
    {
        $dados = is_array($score->dados_consultados) ? $score->dados_consultados : [];

        // Cadastral (minhareceita) grava os campos no topo; SINTEGRA e CND Federal em blocos.
        return filled($dados['situacao_cadastral'] ?? null)
            && filled($dados['sintegra'] ?? null)
            && filled($dados['cnd_federal'] ?? null);
    }

    /**
     * Cria um ConsultaLote interno e despacha um ProcessarConsultaJob por participante,
     * reusando o pipeline da Consulta CNPJ. Ao fechar o batch, projeta os scores em
     * participante_scores. `creditos_cobrados = 0`: este lote é o BRAÇO INTERNO do clearance
     * completo — o dinheiro foi debitado por nota no lote de clearance, não aqui.
     */
    private function dispararConsulta(int $userId, Collection $stale, array $incluidas, ?string $tabId, ?int $fecharClearanceLoteId = null): int
    {
        $lote = ConsultaLote::create([
            'user_id' => $userId,
            'plano_id' => null,
            'status' => ConsultaLote::STATUS_PROCESSANDO,
            'total_participantes' => $stale->count(),
            'creditos_cobrados' => 0,
            'tab_id' => $tabId,
        ]);

        $lote->participantes()->attach($stale->pluck('id')->all());

        // Trilha canônica do clearance: as fontes da contraparte caem nas etapas 3/4/5 via
        // config('consultas.fonte_etapa') — por isso sempre a trilha 'full' aqui.
        $etapas = ClearanceEtapas::para('full');
        // 2ª fase de um clearance completo: a etapa 1 já foi cumprida pelos documentos, reemiti-la
        // faria o strip voltar pra "Preparando consulta".
        $segundaFase = $fecharClearanceLoteId !== null;
        $total = $stale->count();

        $jobs = $stale->values()->map(fn (Participante $p, int $i) => new ProcessarConsultaJob(
            loteId: $lote->id,
            alvoTipo: 'participante',
            alvoId: $p->id,
            userId: $userId,
            tabId: (string) ($tabId ?? ''),
            consultasIncluidas: $incluidas,
            alvo: [
                'cnpj' => Cnpj::digitos((string) $p->documento),
                'uf' => $p->uf,
                'crt' => $p->crt,
            ],
            etapas: $etapas,
            alvoIndice: $i + 1,
            totalAlvos: $total,
            // Segunda metade da barra: os documentos do clearance já ocuparam 0→50.
            pctBase: $segundaFase ? 50 : 0,
            pctSpan: $segundaFase ? 45 : 100,
            emitirInicializacao: ! $segundaFase,
        ))->all();

        $loteId = $lote->id;

        Bus::batch($jobs)
            ->name("clearance-regularidade-{$loteId}")
            ->finally(function () use ($loteId, $fecharClearanceLoteId) {
                // Só projeta os scores em participante_scores — sem settlement monetário: este
                // lote não cobrou nada (o preço está na nota do clearance completo).
                app(FecharLoteService::class)->persistirScores($loteId);
                ConsultaLote::whereKey($loteId)->update([
                    'status' => ConsultaLote::STATUS_CONCLUIDO,
                    'processado_em' => now(),
                ]);

                // Fecha o lote de CLEARANCE só agora — 'finalizado' (100%) marca o fim das duas
                // fases. `finally` (não `then`) garante o fechamento mesmo se uma contraparte
                // falhar: o clearance dos documentos já foi entregue e não pode ficar preso em
                // "processando" por causa da regularidade.
                if ($fecharClearanceLoteId !== null) {
                    app(FecharClearanceLoteService::class)->fechar($fecharClearanceLoteId);
                }
            })
            ->dispatch();

        return $loteId;
    }

    /** CNPJs (14 dígitos) das contrapartes dos snapshots persistidos deste lote (NF-e + CT-e). */
    private function cnpjsContrapartesDoLote(int $loteId, int $userId): Collection
    {
        $nfe = NfeConsulta::where('user_id', $userId)->where('consulta_lote_id', $loteId)->get(['emit_cnpj', 'dest_cnpj']);
        $cte = CteConsulta::where('user_id', $userId)->where('consulta_lote_id', $loteId)->get(['emit_cnpj', 'dest_cnpj']);

        return $nfe->concat($cte)
            ->flatMap(fn ($s) => [$s->emit_cnpj, $s->dest_cnpj])
            ->filter()
            ->map(fn ($c) => Cnpj::digitos((string) $c))
            ->filter(fn ($c) => strlen($c) === 14)
            ->unique()
            ->values();
    }

    /** CNPJ real e não mascarado — pré-requisito pra qualquer consulta. */
    private function cnpjConsultavel(?string $documento): bool
    {
        $digitos = Cnpj::digitos((string) $documento);

        return strlen($digitos) === 14 && ! $this->mascarado->estaMascarado($digitos);
    }

    /** @return array{consultados:int, reusados:int, ignorados:int, lote_id:null} */
    private function vazio(int $reusados = 0, int $ignorados = 0): array
    {
        return ['consultados' => 0, 'reusados' => $reusados, 'ignorados' => $ignorados, 'lote_id' => null];
    }

    /** @return array<int,string> */
    private function consultasIncluidas(): array
    {
        return (array) config('clearance.full.consultas_incluidas', ['situacao_cadastral', 'sintegra', 'cnd_federal']);
    }

    /**
     * Chaves das FONTES que o Clearance completo cobre (cadastro, sintegra, cnd_federal), derivadas
     * do FonteRegistry — `consultas_incluidas` guarda sub-atributos, não chaves de fonte. Mexer nas
     * fontes do produto ajusta a tela sozinho.
     *
     * @return array<int,string>
     */
    private function chavesDasFontes(): array
    {
        return array_map(
            fn ($fonte) => $fonte->chave(),
            $this->registry->fontesDe($this->consultasIncluidas()),
        );
    }

    private function frescuraDias(): int
    {
        return (int) config('clearance.full.frescura_dias', 30);
    }
}
