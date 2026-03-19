<?php

namespace App\BI\Queries;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KpisGeraisQuery extends BiQuery
{
    public function execute(): array
    {
        $inicio = $this->filtros['data_inicio_iso'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fim    = $this->filtros['data_fim_iso']    ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $userId = $this->filtros['user_id'];

        // Entradas e saídas agregadas
        $totais = DB::table('efd_notas')
            ->select([
                DB::raw("SUM(CASE WHEN tipo_operacao = 'entrada' THEN valor_total ELSE 0 END) AS total_entradas_valor"),
                DB::raw("SUM(CASE WHEN tipo_operacao = 'saida'   THEN valor_total ELSE 0 END) AS total_saidas_valor"),
                DB::raw("COUNT(CASE WHEN tipo_operacao = 'entrada' THEN 1 END) AS total_entradas_notas"),
                DB::raw("COUNT(CASE WHEN tipo_operacao = 'saida'   THEN 1 END) AS total_saidas_notas"),
                DB::raw('COUNT(DISTINCT participante_id) AS participantes_ativos'),
            ])
            ->where('user_id', $userId)
            ->whereBetween('data_emissao', [$inicio, $fim])
            ->first();

        // Carga tributária: soma ICMS + PIS + COFINS dos itens das notas no período
        $cargaTributaria = DB::table('efd_notas_itens AS i')
            ->join('efd_notas AS n', 'n.id', '=', 'i.efd_nota_id')
            ->where('n.user_id', $userId)
            ->whereBetween('n.data_emissao', [$inicio, $fim])
            ->selectRaw('SUM(COALESCE(i.valor_icms, 0) + COALESCE(i.valor_pis, 0) + COALESCE(i.valor_cofins, 0)) AS total')
            ->value('total');

        // Notas em risco: participante com situação cadastral inativa
        $notasEmRisco = DB::table('efd_notas AS n')
            ->join('participantes AS p', 'p.id', '=', 'n.participante_id')
            ->where('n.user_id', $userId)
            ->whereBetween('n.data_emissao', [$inicio, $fim])
            ->whereRaw("UPPER(p.situacao_cadastral) NOT IN ('02', 'ATIVA')")
            ->whereNotNull('n.participante_id')
            ->count();

        $entradas = (float) ($totais->total_entradas_valor ?? 0);
        $saidas   = (float) ($totais->total_saidas_valor ?? 0);

        return [
            'total_entradas_valor' => $entradas,
            'total_entradas_notas' => (int) ($totais->total_entradas_notas ?? 0),
            'total_saidas_valor'   => $saidas,
            'total_saidas_notas'   => (int) ($totais->total_saidas_notas ?? 0),
            'saldo_liquido'        => $entradas - $saidas,
            'carga_tributaria'     => (float) ($cargaTributaria ?? 0),
            'participantes_ativos' => (int) ($totais->participantes_ativos ?? 0),
            'notas_em_risco'       => (int) $notasEmRisco,
        ];
    }
}
