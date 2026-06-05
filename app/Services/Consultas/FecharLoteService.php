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
        // Estorno preciso: soma o total por alvo (participante OU cliente) acumulado pelos
        // jobs (cache, overwrite por alvo → idempotente). Só fontes em falha estornável contam.
        $alvos = ConsultaResultado::where('consulta_lote_id', $loteId)
            ->get(['participante_id', 'cliente_id']);

        $creditosFalhos = 0;
        foreach ($alvos as $alvo) {
            [$tipo, $id] = $alvo->cliente_id
                ? ['cliente', $alvo->cliente_id]
                : ['participante', $alvo->participante_id];
            $creditosFalhos += (int) Cache::pull("consulta_estorno:{$loteId}:{$tipo}:{$id}", 0);
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
