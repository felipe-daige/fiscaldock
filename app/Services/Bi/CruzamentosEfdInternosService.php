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
 * Fora por falta de dado (não construir sem massa): estoque H010 (bloco H não é
 * extraído pelo n8n) e ICMS-ST × regime do fornecedor (zero movimento ST na base).
 *
 * Flags de divergência: CruzamentoApuracaoService::classificarFlag (limites
 * canônicos verde/amarelo/vermelho do sistema).
 */
class CruzamentosEfdInternosService
{
    /** CSTs de PIS de saída sem tributação — universo do M400 (isenta, alíq. zero, suspensão, sem incidência). */
    private const CSTS_NAO_TRIBUTADOS = ['04', '05', '06', '07', '08', '09'];

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
}
