<?php

namespace App\Services\Bi;

use App\Models\ParticipanteScore;
use App\Services\Efd\CruzamentoApuracaoService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Cruzamentos EFD-internos da página /app/bi/cruzamentos (fase 3, 2026-07-11).
 *
 * Dois cruzamentos que NÃO existem no Resumo Fiscal (lá vive o declarado×notas
 * de ICMS/PIS/COFINS e o retido×deduzido agregado — fonte única
 * CruzamentoApuracaoService/ResumoFiscalService, não duplicar aqui):
 *
 *  1. Receitas não tributadas (M400, PIS) × itens de saída com CST não tributado
 *     (04–09) do arquivo de contribuições, por competência. Receita isenta/alíquota
 *     zero declarada no M400 deve bater com o que as notas escrituram; divergência
 *     = receita classificada errado (tributada como isenta ou vice-versa).
 *     M800 (COFINS) não é persistido pelo n8n — cruzamento é PIS-only.
 *  2. Retenções na fonte (F600) × fonte pagadora × regularidade. Agrupa o retido
 *     por CNPJ da fonte, casa com `participantes` (user_id+documento) e lê a
 *     regularidade da projeção canônica `participante_scores` (mesmos motivos do
 *     cruzamento de fornecedores — CruzamentosConsultasClearanceService).
 *
 * Fase 4 (2026-07-11) — cruzamentos construídos data-ready (a base atual não tem
 * massa; as seções degradam pra empty-state explicativo):
 *
 *  3. ICMS-ST das compras × regime do fornecedor. ST destacado nas entradas
 *     (efd_notas_consolidados — C190, fiscal-only por construção, sem dedup) agrupado
 *     por fornecedor com o regime tributário da última consulta. Contexto E210
 *     (st_icms_recolher) ao lado.
 *  4. Estoque declarado (H010) × movimentação do item. Inventário mais recente por
 *     cliente (efd_estoque, ind_prop 0|1 = estoque próprio) contra a movimentação
 *     item a item (C170 fiscal) nos 12 meses até a data do inventário. Item sem giro
 *     = capital parado ou omissão de saída. Limitação: saída escriturada só no C190
 *     (sem item) não conta como movimentação.
 *
 * Flags de divergência: CruzamentoApuracaoService::classificarFlag (limites
 * canônicos verde/amarelo/vermelho do sistema).
 */
class CruzamentosEfdInternosService
{
    /** CSTs de PIS de saída sem tributação — universo do M400 (isenta, alíq. zero, suspensão, sem incidência). */
    private const CSTS_NAO_TRIBUTADOS = ['04', '05', '06', '07', '08', '09'];

    /** Corte de exibição do cruzamento de estoque (itens ordenados por valor desc). */
    private const ESTOQUE_LIMITE_ITENS = 100;

    /** Janela de movimentação olhando pra trás a partir da data do inventário. */
    private const ESTOQUE_JANELA_MESES = 12;

    public function __construct(
        private CruzamentoApuracaoService $flags,
        private CruzamentosConsultasClearanceService $regularidade,
    ) {}

    /**
     * M400 declarado × soma dos itens de saída com CST 04–09, por competência.
     * Só competências que têm apuração de contribuições importada.
     *
     * @param  array{cliente_id?:int|null, data_inicio?:string|null, data_fim?:string|null}  $filtros
     * @return Collection<int, array{competencia:string, declarado:float, computado:float, delta:float, delta_pct:float, flag:string}>
     */
    public function receitasNaoTributadasPorCompetencia(int $userId, array $filtros = []): Collection
    {
        $apuracoes = DB::table('efd_apuracoes_contribuicoes as a')
            ->join('efd_importacoes as i', 'i.id', '=', 'a.importacao_id')
            ->where('a.user_id', $userId)
            ->when(! empty($filtros['cliente_id']), fn ($q) => $q->where('a.cliente_id', $filtros['cliente_id']))
            ->selectRaw("COALESCE(
                to_char(i.periodo_inicio, 'YYYY-MM'),
                (SELECT to_char(MIN(n.data_emissao), 'YYYY-MM') FROM efd_notas n WHERE n.importacao_id = a.importacao_id)
            ) as competencia, a.pis_nao_tributado")
            ->get()
            ->filter(fn ($r) => $r->competencia !== null);

        // Declarado por competência = soma dos PIS_RECEITA_TOTAL do M400 (pode haver
        // mais de uma linha M400 e mais de um cliente na mesma competência).
        $declaradoPorMes = [];
        foreach ($apuracoes as $a) {
            $json = json_decode((string) $a->pis_nao_tributado, true) ?: [];
            $soma = 0.0;
            foreach (($json['M400'] ?? []) as $linha) {
                $soma += (float) ($linha['PIS_RECEITA_TOTAL'] ?? 0);
            }
            $declaradoPorMes[$a->competencia] = ($declaradoPorMes[$a->competencia] ?? 0.0) + $soma;
        }

        if ($declaradoPorMes === []) {
            return collect();
        }

        // Período: recorta as competências exibidas (o dado é mensal por natureza).
        $mesIni = ! empty($filtros['data_inicio']) ? substr($filtros['data_inicio'], 0, 7) : null;
        $mesFim = ! empty($filtros['data_fim']) ? substr($filtros['data_fim'], 0, 7) : null;
        $declaradoPorMes = array_filter(
            $declaradoPorMes,
            fn ($mes) => (! $mesIni || $mes >= $mesIni) && (! $mesFim || $mes <= $mesFim),
            ARRAY_FILTER_USE_KEY
        );

        if ($declaradoPorMes === []) {
            return collect();
        }

        $computadoPorMes = DB::table('efd_notas_itens as it')
            ->join('efd_notas as n', 'n.id', '=', 'it.efd_nota_id')
            ->where('n.user_id', $userId)
            ->where('n.origem_arquivo', 'contribuicoes')
            ->where('n.tipo_operacao', 'saida')
            ->where('n.cancelada', false)
            ->whereIn('it.cst_pis', self::CSTS_NAO_TRIBUTADOS)
            ->when(! empty($filtros['cliente_id']), fn ($q) => $q->where('n.cliente_id', $filtros['cliente_id']))
            ->whereIn(DB::raw("to_char(n.data_emissao, 'YYYY-MM')"), array_keys($declaradoPorMes))
            ->selectRaw("to_char(n.data_emissao, 'YYYY-MM') as competencia, SUM(it.valor_total) as valor")
            ->groupBy(DB::raw("to_char(n.data_emissao, 'YYYY-MM')"))
            ->pluck('valor', 'competencia');

        ksort($declaradoPorMes);

        return collect($declaradoPorMes)
            ->map(function (float $declarado, string $mes) use ($computadoPorMes) {
                $linha = $this->flags->classificarFlag(
                    round($declarado, 2),
                    round((float) ($computadoPorMes[$mes] ?? 0), 2)
                );
                $linha['competencia'] = $mes;

                return $linha;
            })
            ->values();
    }

    /**
     * Retenções na fonte (F600) agrupadas por CNPJ da fonte pagadora, com regularidade
     * da fonte (participante_scores — mesma projeção do cruzamento de fornecedores).
     *
     * @return Collection<int, array{cnpj:string, razao_social:string, participante_id:int|null, qtd:int, valor_pis:float, valor_cofins:float, valor_total:float, motivos:array<int,string>, consultada:bool}>
     */
    public function retencoesPorFonte(int $userId, array $filtros = []): Collection
    {
        $fontes = DB::table('efd_retencoes_fonte as r')
            ->leftJoin('participantes as p', fn ($j) => $j
                ->on('p.user_id', '=', 'r.user_id')
                ->whereColumn('p.documento', 'r.cnpj'))
            ->where('r.user_id', $userId)
            ->whereNotNull('r.cnpj')
            ->where('r.cnpj', '!=', '')
            ->when(! empty($filtros['cliente_id']), fn ($q) => $q->where('r.cliente_id', $filtros['cliente_id']))
            ->when(! empty($filtros['data_inicio']), fn ($q) => $q->where('r.data_retencao', '>=', $filtros['data_inicio']))
            ->when(! empty($filtros['data_fim']), fn ($q) => $q->where('r.data_retencao', '<=', $filtros['data_fim']))
            ->selectRaw('r.cnpj, MAX(p.razao_social) as razao_social, MAX(p.id) as participante_id,
                COUNT(*) as qtd, SUM(r.valor_pis) as valor_pis, SUM(r.valor_cofins) as valor_cofins,
                SUM(r.valor_total) as valor_total')
            ->groupBy('r.cnpj')
            ->orderByDesc(DB::raw('SUM(r.valor_total)'))
            ->get();

        $ids = $fontes->pluck('participante_id')->filter()->unique()->values()->all();
        $scores = $ids === []
            ? collect()
            : ParticipanteScore::where('user_id', $userId)
                ->whereIn('participante_id', $ids)
                ->get()
                ->keyBy('participante_id');

        return $fontes->map(function ($f) use ($scores) {
            $score = $f->participante_id ? $scores->get($f->participante_id) : null;

            return [
                'cnpj' => $f->cnpj,
                'razao_social' => $f->razao_social ?? '—',
                'participante_id' => $f->participante_id ? (int) $f->participante_id : null,
                'qtd' => (int) $f->qtd,
                'valor_pis' => round((float) $f->valor_pis, 2),
                'valor_cofins' => round((float) $f->valor_cofins, 2),
                'valor_total' => round((float) $f->valor_total, 2),
                'motivos' => $score ? $this->regularidade->motivosIrregularidade($score) : [],
                'consultada' => $score !== null,
            ];
        })->values();
    }

    /**
     * ICMS-ST destacado nas compras × regime tributário do fornecedor (fase 4).
     *
     * Fonte do ST: efd_notas_consolidados (C190) das entradas fiscais não canceladas —
     * fiscal-only por construção, então sem risco de dupla contagem com contribuições.
     * Regime vem de `participantes.regime_tributario` (última consulta cadastral).
     * Contexto: soma do st_icms_recolher (E210) das apurações do período.
     *
     * @param  array{cliente_id?:int|null, data_inicio?:string|null, data_fim?:string|null}  $filtros
     * @return array{fornecedores: Collection<int, array{participante_id:int|null, razao_social:string, documento:string, regime:string|null, qtd_notas:int, bc_st:float, valor_st:float}>, e210_st_recolher: float}
     */
    public function icmsStRegime(int $userId, array $filtros = []): array
    {
        $fornecedores = DB::table('efd_notas_consolidados as c')
            ->join('efd_notas as n', 'n.id', '=', 'c.efd_nota_id')
            ->leftJoin('participantes as p', 'p.id', '=', 'n.participante_id')
            ->where('n.user_id', $userId)
            ->where('n.tipo_operacao', 'entrada')
            ->where('n.cancelada', false)
            ->where(fn ($q) => $q->where('c.valor_icms_st', '>', 0)->orWhere('c.valor_bc_icms_st', '>', 0))
            ->when(! empty($filtros['cliente_id']), fn ($q) => $q->where('n.cliente_id', $filtros['cliente_id']))
            ->when(! empty($filtros['data_inicio']), fn ($q) => $q->where('n.data_emissao', '>=', $filtros['data_inicio']))
            ->when(! empty($filtros['data_fim']), fn ($q) => $q->where('n.data_emissao', '<=', $filtros['data_fim']))
            ->selectRaw('n.participante_id, MAX(p.razao_social) as razao_social, MAX(p.documento) as documento,
                MAX(p.regime_tributario) as regime, COUNT(DISTINCT n.id) as qtd_notas,
                SUM(c.valor_bc_icms_st) as bc_st, SUM(c.valor_icms_st) as valor_st')
            ->groupBy('n.participante_id')
            ->orderByDesc(DB::raw('SUM(c.valor_icms_st)'))
            ->get()
            ->map(fn ($f) => [
                'participante_id' => $f->participante_id ? (int) $f->participante_id : null,
                'razao_social' => $f->razao_social ?? 'Sem identificação',
                'documento' => $f->documento ?? '—',
                'regime' => $f->regime !== null && trim((string) $f->regime) !== '' && $f->regime !== 'Não informado'
                    ? (string) $f->regime : null,
                'qtd_notas' => (int) $f->qtd_notas,
                'bc_st' => round((float) $f->bc_st, 2),
                'valor_st' => round((float) $f->valor_st, 2),
            ])
            ->values();

        $e210 = (float) DB::table('efd_apuracoes_icms')
            ->where('user_id', $userId)
            ->when(! empty($filtros['cliente_id']), fn ($q) => $q->where('cliente_id', $filtros['cliente_id']))
            ->when(! empty($filtros['data_inicio']), fn ($q) => $q->where('periodo_fim', '>=', $filtros['data_inicio']))
            ->when(! empty($filtros['data_fim']), fn ($q) => $q->where('periodo_inicio', '<=', $filtros['data_fim']))
            ->sum('st_icms_recolher');

        return [
            'fornecedores' => $fornecedores,
            'e210_st_recolher' => round($e210, 2),
        ];
    }

    /**
     * Estoque declarado (H010) × movimentação do item (fase 4).
     *
     * Inventário de referência = o mais recente por cliente (dt_inventario) dentro do
     * filtro; só estoque próprio (ind_prop 0|1). Movimentação = soma de valor dos itens
     * de nota fiscais (C170) do MESMO cliente com o mesmo código, nos 12 meses até a
     * data do inventário, separada em entradas e saídas. Item sem nenhuma movimentação
     * = flag `sem_movimentacao` (capital parado — ou saída escriturada só no C190,
     * que não tem item; a tela explica a limitação).
     *
     * @param  array{cliente_id?:int|null, data_inicio?:string|null, data_fim?:string|null}  $filtros
     * @return array{inventarios: Collection<int, object>, itens: Collection<int, array<string, mixed>>, itens_total: int, parados_qtd: int, parados_valor: float}
     */
    public function estoqueVsMovimentacao(int $userId, array $filtros = []): array
    {
        $inventarios = DB::table('efd_estoque as e')
            ->join('clientes as cl', 'cl.id', '=', 'e.cliente_id')
            ->where('e.user_id', $userId)
            ->whereIn('e.ind_prop', ['0', '1'])
            ->when(! empty($filtros['cliente_id']), fn ($q) => $q->where('e.cliente_id', $filtros['cliente_id']))
            ->when(! empty($filtros['data_inicio']), fn ($q) => $q->where('e.dt_inventario', '>=', $filtros['data_inicio']))
            ->when(! empty($filtros['data_fim']), fn ($q) => $q->where('e.dt_inventario', '<=', $filtros['data_fim']))
            ->selectRaw('e.cliente_id, MAX(cl.razao_social) as cliente_nome, MAX(e.dt_inventario) as dt_inventario')
            ->groupBy('e.cliente_id')
            ->get();

        if ($inventarios->isEmpty()) {
            return ['inventarios' => collect(), 'itens' => collect(), 'itens_total' => 0, 'parados_qtd' => 0, 'parados_valor' => 0.0];
        }

        $itens = collect();
        foreach ($inventarios as $inv) {
            $doInventario = DB::table('efd_estoque as e')
                ->leftJoin('efd_catalogo_itens as cat', fn ($j) => $j
                    ->on('cat.cliente_id', '=', 'e.cliente_id')
                    ->whereColumn('cat.cod_item', 'e.cod_item'))
                ->where('e.user_id', $userId)
                ->where('e.cliente_id', $inv->cliente_id)
                ->where('e.dt_inventario', $inv->dt_inventario)
                ->whereIn('e.ind_prop', ['0', '1'])
                ->selectRaw('e.cliente_id, e.cod_item, MAX(cat.descr_item) as descricao, MAX(e.unid) as unid,
                    SUM(e.qtd) as qtd, SUM(e.vl_item) as vl_item')
                ->groupBy('e.cliente_id', 'e.cod_item')
                ->get();

            $janelaInicio = \Illuminate\Support\Carbon::parse((string) $inv->dt_inventario)
                ->subMonths(self::ESTOQUE_JANELA_MESES)->toDateString();

            $movimentacao = DB::table('efd_notas_itens as it')
                ->join('efd_notas as n', 'n.id', '=', 'it.efd_nota_id')
                ->where('n.user_id', $userId)
                ->where('n.cliente_id', $inv->cliente_id)
                ->where('n.origem_arquivo', 'fiscal')
                ->where('n.cancelada', false)
                ->whereBetween('n.data_emissao', [$janelaInicio, (string) $inv->dt_inventario])
                ->whereIn('it.codigo_item', $doInventario->pluck('cod_item')->all())
                ->selectRaw("it.codigo_item,
                    SUM(CASE WHEN n.tipo_operacao = 'entrada' THEN it.valor_total ELSE 0 END) as entradas,
                    SUM(CASE WHEN n.tipo_operacao = 'saida' THEN it.valor_total ELSE 0 END) as saidas")
                ->groupBy('it.codigo_item')
                ->get()
                ->keyBy('codigo_item');

            foreach ($doInventario as $item) {
                $mov = $movimentacao->get($item->cod_item);
                $entradas = round((float) ($mov->entradas ?? 0), 2);
                $saidas = round((float) ($mov->saidas ?? 0), 2);

                $itens->push([
                    'cliente_id' => (int) $item->cliente_id,
                    'cliente_nome' => $inv->cliente_nome,
                    'dt_inventario' => substr((string) $inv->dt_inventario, 0, 10),
                    'cod_item' => $item->cod_item,
                    'descricao' => $item->descricao,
                    'unid' => $item->unid,
                    'qtd' => (float) $item->qtd,
                    'vl_item' => round((float) $item->vl_item, 2),
                    'mov_entradas' => $entradas,
                    'mov_saidas' => $saidas,
                    'sem_movimentacao' => $entradas == 0.0 && $saidas == 0.0,
                ]);
            }
        }

        $parados = $itens->where('sem_movimentacao', true);

        return [
            'inventarios' => $inventarios,
            'itens' => $itens->sortByDesc('vl_item')->take(self::ESTOQUE_LIMITE_ITENS)->values(),
            'itens_total' => $itens->count(),
            'parados_qtd' => $parados->count(),
            'parados_valor' => round((float) $parados->sum('vl_item'), 2),
        ];
    }
}
