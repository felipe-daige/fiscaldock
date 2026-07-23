<?php

namespace App\Services\Efd;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Consolidado fiscal agregado do mês (C190/D190) de uma importação — soma as linhas de
 * `efd_notas_consolidados` por operação/CFOP/CST/alíquota. É o resumo tributário que faz
 * sentido no varejo: em vez de abrir 1432 NFC-e, mostra "quanto vendeu em cada regime".
 *
 * Computado na exibição (query agregada leve) — não persiste no resumo_final. Ignora
 * notas canceladas. Só entra o que foi escriturado por C190/D190 (o consolidado nativo
 * da NFC-e e da saída perfil B).
 */
class ConsolidadoFiscalService
{
    /**
     * @return array{
     *   linhas: Collection<int, object>,
     *   saidas: array{notas:int, operacao:float, bc:float, icms:float, icms_st:float, ipi:float},
     *   entradas: array{notas:int, operacao:float, bc:float, icms:float, icms_st:float, ipi:float},
     *   tem_dados: bool
     * }
     */
    public function porImportacao(int $importacaoId, int $userId): array
    {
        return $this->agregar($userId, fn ($q) => $q->where('n.importacao_id', $importacaoId));
    }

    /**
     * Visão ACUMULADA do cliente: todas as importações EFD dele. `periodo` opcional
     * (['de' => 'Y-m-d', 'ate' => 'Y-m-d']) filtra por data de emissão da nota.
     *
     * @param  array{de?:?string, ate?:?string}  $periodo
     * @return array{linhas: Collection<int, object>, saidas: array, entradas: array, tem_dados: bool}
     */
    public function porCliente(int $clienteId, int $userId, array $periodo = []): array
    {
        return $this->agregar($userId, function ($q) use ($clienteId, $periodo) {
            $q->where('n.cliente_id', $clienteId);
            if (! empty($periodo['de'])) {
                $q->where('n.data_emissao', '>=', $periodo['de']);
            }
            if (! empty($periodo['ate'])) {
                $q->where('n.data_emissao', '<=', $periodo['ate']);
            }
        });
    }

    /**
     * @param  callable(\Illuminate\Database\Query\Builder): void  $escopo
     * @return array{linhas: Collection<int, object>, saidas: array, entradas: array, tem_dados: bool}
     */
    private function agregar(int $userId, callable $escopo): array
    {
        $linhas = DB::table('efd_notas_consolidados as c')
            ->join('efd_notas as n', 'n.id', '=', 'c.efd_nota_id')
            ->where('n.user_id', $userId)
            ->where('n.cancelada', false)
            ->tap($escopo)
            ->groupBy('n.tipo_operacao', 'c.cfop', 'c.cst_icms', 'c.aliquota_icms')
            ->selectRaw('
                n.tipo_operacao,
                c.cfop,
                c.cst_icms,
                c.aliquota_icms,
                COUNT(DISTINCT c.efd_nota_id) AS notas,
                SUM(c.valor_operacao)   AS operacao,
                SUM(c.valor_bc_icms)    AS bc,
                SUM(c.valor_icms)       AS icms,
                SUM(c.valor_icms_st)    AS icms_st,
                SUM(c.valor_ipi)        AS ipi
            ')
            ->orderByDesc('operacao')
            ->get();

        // Notas DISTINTAS por operação — não dá pra somar 'notas' das linhas (uma NFC-e com
        // 2 CFOPs apareceria em 2 grupos e seria contada 2×).
        $notasPorOperacao = DB::table('efd_notas_consolidados as c')
            ->join('efd_notas as n', 'n.id', '=', 'c.efd_nota_id')
            ->where('n.user_id', $userId)
            ->where('n.cancelada', false)
            ->tap($escopo)
            ->groupBy('n.tipo_operacao')
            ->selectRaw('n.tipo_operacao, COUNT(DISTINCT c.efd_nota_id) AS notas')
            ->pluck('notas', 'tipo_operacao');

        return [
            'linhas' => $linhas,
            'saidas' => $this->totalizar($linhas->where('tipo_operacao', 'saida'), (int) ($notasPorOperacao['saida'] ?? 0)),
            'entradas' => $this->totalizar($linhas->where('tipo_operacao', 'entrada'), (int) ($notasPorOperacao['entrada'] ?? 0)),
            'tem_dados' => $linhas->isNotEmpty(),
        ];
    }

    /**
     * @param  Collection<int, object>  $linhas
     * @return array{notas:int, operacao:float, bc:float, icms:float, icms_st:float, ipi:float}
     */
    private function totalizar(Collection $linhas, int $notasDistintas): array
    {
        return [
            'notas' => $notasDistintas,
            'operacao' => (float) $linhas->sum('operacao'),
            'bc' => (float) $linhas->sum('bc'),
            'icms' => (float) $linhas->sum('icms'),
            'icms_st' => (float) $linhas->sum('icms_st'),
            'ipi' => (float) $linhas->sum('ipi'),
        ];
    }
}
