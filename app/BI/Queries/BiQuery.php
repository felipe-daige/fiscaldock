<?php

namespace App\BI\Queries;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

abstract class BiQuery
{
    public function __construct(
        protected array $filtros
    ) {}

    abstract public function execute(): array;

    protected function resolverIntervalo(): array
    {
        $dataInicio = $this->filtros['data_inicio'] ?? null;
        $dataFim    = $this->filtros['data_fim']    ?? null;
        $ano        = $this->filtros['ano']         ?? null;
        $mes        = $this->filtros['mes']         ?? null;

        if ($dataInicio && $dataFim) {
            return [
                'inicio' => Carbon::parse($dataInicio)->startOfDay(),
                'fim'    => Carbon::parse($dataFim)->endOfDay(),
            ];
        }

        if ($ano && $mes) {
            $inicio = Carbon::createFromDate($ano, $mes, 1)->startOfMonth();

            return [
                'inicio' => $inicio,
                'fim'    => $inicio->copy()->endOfMonth(),
            ];
        }

        if ($ano) {
            return [
                'inicio' => Carbon::createFromDate($ano, 1, 1)->startOfYear(),
                'fim'    => Carbon::createFromDate($ano, 12, 31)->endOfYear(),
            ];
        }

        return [
            'inicio' => now()->startOfMonth(),
            'fim'    => now()->endOfMonth(),
        ];
    }

    protected function baseQuery(string $tabela)
    {
        return DB::table($tabela)->where('user_id', $this->filtros['user_id']);
    }
}
