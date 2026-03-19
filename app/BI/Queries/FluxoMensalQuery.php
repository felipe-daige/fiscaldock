<?php

namespace App\BI\Queries;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FluxoMensalQuery extends BiQuery
{
    public function execute(): array
    {
        $dataFim = Carbon::parse($this->filtros['data_fim_iso'] ?? now()->format('Y-m-d'));
        $userId = $this->filtros['user_id'];

        $labelsMap = [
            '01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr',
            '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
            '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez',
        ];

        $meses = [];
        for ($i = 11; $i >= 0; $i--) {
            $mes = $dataFim->copy()->startOfMonth()->subMonths($i);
            $meses[$mes->format('Y-m')] = [
                'mes' => $mes->format('Y-m'),
                'label' => $labelsMap[$mes->format('m')].'/'.$mes->format('y'),
                'entradas' => 0.0,
                'saidas' => 0.0,
                'saldo' => 0.0,
            ];
        }

        $inicioConsulta = array_key_first($meses).'-01';

        $rows = DB::table('efd_notas')
            ->selectRaw("TO_CHAR(data_emissao, 'YYYY-MM') AS mes")
            ->selectRaw("SUM(CASE WHEN tipo_operacao = 'entrada' THEN valor_total ELSE 0 END) AS entradas")
            ->selectRaw("SUM(CASE WHEN tipo_operacao = 'saida'   THEN valor_total ELSE 0 END) AS saidas")
            ->where('user_id', $userId)
            ->whereBetween('data_emissao', [$inicioConsulta, $dataFim->format('Y-m-d')])
            ->groupByRaw("TO_CHAR(data_emissao, 'YYYY-MM')")
            ->get();

        foreach ($rows as $row) {
            if (isset($meses[$row->mes])) {
                $entradas = (float) $row->entradas;
                $saidas = (float) $row->saidas;
                $meses[$row->mes]['entradas'] = $entradas;
                $meses[$row->mes]['saidas'] = $saidas;
                $meses[$row->mes]['saldo'] = $entradas - $saidas;
            }
        }

        return array_values($meses);
    }
}
