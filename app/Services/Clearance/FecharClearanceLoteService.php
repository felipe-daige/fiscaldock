<?php

namespace App\Services\Clearance;

use App\Models\ConsultaLote;
use App\Services\SaldoService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Fecha o lote de clearance e aplica o estorno preciso por documento que falhou
 * (acumulado no cache pelos ProcessarClearanceJob). Espelha FecharLoteService, mas
 * itera as chaves do lote em vez de ConsultaResultado.
 */
class FecharClearanceLoteService
{
    public function __construct(private SaldoService $saldoService) {}

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
            // Preserva o que foi gravado na criação (ex.: `tier`) — sobrescrever aqui apagava o
            // tier contratado e a tela de resultado deixava de saber se era clearance completo.
            $lote->resultado_resumo = array_merge(
                (array) ($lote->resultado_resumo ?? []),
                ['engine' => 'laravel-clearance'],
                $resumo,
            );
            $lote->processado_em = now();
            $lote->save();

            if ($estorno > 0) {
                $this->saldoService->add(
                    $lote->user,
                    $estorno,
                    'clearance_refund',
                    'Estorno de R$ '.number_format(app(\App\Services\PricingCatalogService::class)->creditsToCurrency($estorno), 2, ',', '.')." — documentos com falha no clearance #{$lote->id}",
                    $lote,
                );
            }

            return $lote;
        });

        // Terminal no cache de progresso → o SSE (streamProgresso) emite 'finalizado' e o
        // clearance-resultado.js para de mostrar a barra e carrega o resultado (sem F4 manual).
        if ($lote->tab_id) {
            // Fecha na ÚLTIMA etapa da trilha do tier, com o NOME dela — não com um rótulo
            // genérico. Antes fechava sempre em "etapa 2 de 2", o que no clearance completo
            // (5 etapas) desalinhava o strip e fazia o JS cair no fallback "Etapa N".
            $tier = ClearanceEtapas::tierDoLote($lote->resultado_resumo);
            $ultima = ClearanceEtapas::ultima($tier);

            Cache::put("progresso:{$lote->user_id}:{$lote->tab_id}", [
                'tab_id' => $lote->tab_id,
                'status' => ConsultaLote::STATUS_FINALIZADO,
                'progresso' => 100,
                'etapa' => $ultima['numero'],
                'total_etapas' => ClearanceEtapas::total($tier),
                'etapa_label' => $ultima['label'],
                'mensagem' => 'Clearance finalizado.',
            ], 600);
        }

        Cache::forget("clearance_lote_chaves:{$loteId}");
    }
}
