<?php

namespace App\Actions\Monitoramento;

use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoConsulta;
use App\Services\CreditService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Dispara um ciclo de consulta recorrente de monitoramento contínuo:
 * cria a MonitoramentoConsulta, debita o crédito do ciclo e aciona o
 * webhook dedicado do n8n. Em falha de disparo, marca erro + estorna.
 */
class DispararConsultaMonitoramento
{
    public function __construct(private CreditService $creditService) {}

    public function execute(MonitoramentoAssinatura $assinatura, ?MonitoramentoConsulta $parent = null): MonitoramentoConsulta
    {
        $plano = $assinatura->plano;
        $custo = (int) ($plano->custo_creditos ?? 0);
        $user = $assinatura->user;

        $consulta = MonitoramentoConsulta::create([
            'user_id' => $assinatura->user_id,
            'participante_id' => $assinatura->participante_id,
            'cliente_id' => $assinatura->cliente_id,
            'plano_id' => $assinatura->plano_id,
            'assinatura_id' => $assinatura->id,
            'parent_consulta_id' => $parent?->id,
            'tipo' => 'assinatura',
            'status' => 'pendente',
            'creditos_cobrados' => $custo,
        ]);

        $debitado = $this->creditService->deduct(
            $user,
            $custo,
            'monitoramento_assinatura',
            "Monitoramento contínuo — assinatura #{$assinatura->id}",
            $consulta,
        );

        if (! $debitado) {
            $consulta->marcarErro('saldo_insuficiente', 'Saldo insuficiente no momento do disparo.');

            return $consulta;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'X-API-Token' => config('services.api.token'),
                    'Content-Type' => 'application/json',
                ])
                ->post($this->resolveWebhookUrl($assinatura), $this->buildPayload($assinatura, $consulta));

            if (! $response->successful()) {
                throw new \RuntimeException("Webhook respondeu HTTP {$response->status()}");
            }
        } catch (\Throwable $e) {
            $consulta->marcarErro('webhook_dispatch_failed', $e->getMessage());
            $this->creditService->add(
                $user,
                $custo,
                'monitoramento_refund',
                "Estorno — falha no disparo da consulta #{$consulta->id}",
                $consulta,
            );
            Log::error('Falha ao disparar consulta de monitoramento contínuo', [
                'assinatura_id' => $assinatura->id,
                'consulta_id' => $consulta->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $consulta;
    }

    private function resolveWebhookUrl(MonitoramentoAssinatura $assinatura): string
    {
        $url = $assinatura->cliente_id
            ? config('services.webhook.monitoramento_cnpj_cliente_url')
            : config('services.webhook.monitoramento_cnpj_participante_url');

        if (empty($url)) {
            throw new \RuntimeException('Webhook de monitoramento não configurado.');
        }

        return $url;
    }

    private function buildPayload(MonitoramentoAssinatura $assinatura, MonitoramentoConsulta $consulta): array
    {
        $alvo = $assinatura->alvo();
        $plano = $assinatura->plano;

        return [
            'user_id' => $assinatura->user_id,
            'assinatura_id' => $assinatura->id,
            'consulta_id' => $consulta->id,
            'plano_codigo' => $plano->codigo,
            'consultas_incluidas' => $plano->resolvedConsultasIncluidas(),
            'tipo_alvo' => $assinatura->alvoTipo(),
            'alvo' => [
                'id' => $alvo->id,
                'cnpj' => preg_replace('/[^0-9]/', '', (string) $alvo->documento),
                'razao_social' => $alvo->razao_social,
            ],
            'resultado_url' => url('/api/monitoramento/consulta/resultado'),
        ];
    }
}
