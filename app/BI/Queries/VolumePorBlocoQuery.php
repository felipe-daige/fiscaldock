<?php

namespace App\BI\Queries;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VolumePorBlocoQuery extends BiQuery
{
    public function execute(): array
    {
        $inicio = $this->filtros['data_inicio_iso'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fim = $this->filtros['data_fim_iso'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $userId = $this->filtros['user_id'];

        $base = [
            'A' => ['valor' => 0.0, 'notas' => 0],
            'C' => ['valor' => 0.0, 'notas' => 0],
            'D' => ['valor' => 0.0, 'notas' => 0],
        ];

        // Bloco A: EFD Contribuições (PIS/COFINS) — tipo_efd = 'EFD PIS/COFINS'
        $blocoA = DB::table('efd_notas')
            ->join('sped_importacoes', 'efd_notas.importacao_id', '=', 'sped_importacoes.id')
            ->where('efd_notas.user_id', $userId)
            ->whereBetween('efd_notas.data_emissao', [$inicio, $fim])
            ->where('sped_importacoes.tipo_efd', 'EFD PIS/COFINS')
            ->selectRaw('SUM(efd_notas.valor_total) AS valor, COUNT(*) AS notas')
            ->first();

        // Bloco C: EFD Fiscal (ICMS/IPI) — excluindo CT-e (modelo 57)
        $blocoC = DB::table('efd_notas')
            ->join('sped_importacoes', 'efd_notas.importacao_id', '=', 'sped_importacoes.id')
            ->where('efd_notas.user_id', $userId)
            ->whereBetween('efd_notas.data_emissao', [$inicio, $fim])
            ->where('sped_importacoes.tipo_efd', 'EFD ICMS/IPI')
            ->where('efd_notas.modelo', '!=', '57')
            ->selectRaw('SUM(efd_notas.valor_total) AS valor, COUNT(*) AS notas')
            ->first();

        // Bloco D: EFD Fiscal — apenas CT-e (modelo 57, transporte)
        $blocoD = DB::table('efd_notas')
            ->join('sped_importacoes', 'efd_notas.importacao_id', '=', 'sped_importacoes.id')
            ->where('efd_notas.user_id', $userId)
            ->whereBetween('efd_notas.data_emissao', [$inicio, $fim])
            ->where('sped_importacoes.tipo_efd', 'EFD ICMS/IPI')
            ->where('efd_notas.modelo', '57')
            ->selectRaw('SUM(efd_notas.valor_total) AS valor, COUNT(*) AS notas')
            ->first();

        $base['A'] = ['valor' => (float) ($blocoA->valor ?? 0), 'notas' => (int) ($blocoA->notas ?? 0)];
        $base['C'] = ['valor' => (float) ($blocoC->valor ?? 0), 'notas' => (int) ($blocoC->notas ?? 0)];
        $base['D'] = ['valor' => (float) ($blocoD->valor ?? 0), 'notas' => (int) ($blocoD->notas ?? 0)];

        return $base;
    }
}
