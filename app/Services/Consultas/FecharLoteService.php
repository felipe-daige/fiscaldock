<?php

namespace App\Services\Consultas;

use App\Models\ConsultaLote;
use App\Services\CreditService;
use Illuminate\Support\Facades\DB;

class FecharLoteService
{
    public function __construct(private CreditService $creditService) {}

    public function fechar(int $loteId, int $creditosFalhos, array $resumo): void
    {
        DB::transaction(function () use ($loteId, $creditosFalhos, $resumo) {
            /** @var ConsultaLote $lote */
            $lote = ConsultaLote::lockForUpdate()->findOrFail($loteId);

            $lote->status = ConsultaLote::STATUS_CONCLUIDO;
            $lote->resultado_resumo = $resumo;
            $lote->processado_em = now();
            $lote->save();

            if ($creditosFalhos > 0) {
                $this->creditService->add(
                    $lote->user,
                    $creditosFalhos,
                    type: 'consulta_refund',
                    description: "Estorno de {$creditosFalhos} crédito(s) — fontes com falha no lote #{$lote->id}",
                    source: $lote,
                );
            }
        });
    }
}
