<?php

namespace App\Services\Consultas;

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Services\CreditService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FecharLoteService
{
    public function __construct(private CreditService $creditService) {}

    public function fechar(int $loteId, array $resumo = []): void
    {
        // Estorno preciso: soma o total por participante acumulado pelos jobs (cache,
        // overwrite por participante → idempotente). Só fontes em falha estornável contam.
        $participanteIds = ConsultaResultado::where('consulta_lote_id', $loteId)
            ->pluck('participante_id');

        $creditosFalhos = 0;
        foreach ($participanteIds as $pid) {
            $creditosFalhos += (int) Cache::pull("consulta_estorno:{$loteId}:{$pid}", 0);
        }

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
