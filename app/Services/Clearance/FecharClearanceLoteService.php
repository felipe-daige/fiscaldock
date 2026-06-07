<?php

namespace App\Services\Clearance;

use App\Models\ConsultaLote;
use App\Services\CreditService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Fecha o lote de clearance e aplica o estorno preciso por documento que falhou
 * (acumulado no cache pelos ProcessarClearanceJob). Espelha FecharLoteService, mas
 * itera as chaves do lote em vez de ConsultaResultado.
 */
class FecharClearanceLoteService
{
    public function __construct(private CreditService $creditService) {}

    public function fechar(int $loteId, array $resumo = []): void
    {
        $chaves = (array) Cache::get("clearance_lote_chaves:{$loteId}", []);

        $estorno = 0;
        foreach ($chaves as $chave) {
            $estorno += (int) Cache::pull("clearance_estorno:{$loteId}:{$chave}", 0);
        }

        $lote = DB::transaction(function () use ($loteId, $estorno, $resumo) {
            /** @var ConsultaLote $lote */
            $lote = ConsultaLote::lockForUpdate()->findOrFail($loteId);

            $lote->status = ConsultaLote::STATUS_CONCLUIDO;
            $lote->resultado_resumo = array_merge(['engine' => 'laravel-clearance'], $resumo);
            $lote->processado_em = now();
            $lote->save();

            if ($estorno > 0) {
                $this->creditService->add(
                    $lote->user,
                    $estorno,
                    'clearance_refund',
                    "Estorno de {$estorno} crédito(s) — documentos com falha no clearance #{$lote->id}",
                    $lote,
                );
            }

            return $lote;
        });

        // Terminal no cache de progresso → o SSE (streamProgresso) emite 'finalizado' e o
        // clearance-resultado.js para de mostrar a barra e carrega o resultado (sem F4 manual).
        if ($lote->tab_id) {
            Cache::put("progresso:{$lote->user_id}:{$lote->tab_id}", [
                'tab_id' => $lote->tab_id,
                'status' => ConsultaLote::STATUS_FINALIZADO,
                'progresso' => 100,
                'etapa' => 2,
                'total_etapas' => 2,
                'etapa_label' => 'Resultados prontos',
                'mensagem' => 'Clearance finalizado.',
            ], 600);
        }

        Cache::forget("clearance_lote_chaves:{$loteId}");
    }
}
