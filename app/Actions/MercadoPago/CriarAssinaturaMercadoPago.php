<?php

namespace App\Actions\MercadoPago;

use App\Models\AccountSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\MercadoPago\MercadoPagoClient;
use RuntimeException;

/**
 * Cria uma assinatura recorrente (preapproval) no Mercado Pago.
 *
 * Regra dura: preço e ciclo vêm do catálogo do backend (subscription_plans),
 * NUNCA do front — o front só envia o card_token coletado pelo Brick.
 */
class CriarAssinaturaMercadoPago
{
    public function __construct(private MercadoPagoClient $client = new MercadoPagoClient) {}

    public function execute(User $user, string $codigoPlano, string $ciclo, string $cardToken): AccountSubscription
    {
        $ciclo = $ciclo === 'anual' ? 'anual' : 'mensal';

        $plan = SubscriptionPlan::where('codigo', $codigoPlano)->where('is_active', true)->first();

        if ($plan === null || in_array($plan->codigo, ['free', 'enterprise'], true)) {
            throw new RuntimeException('Plano não assinável.');
        }

        $planId = $plan->mpPlanId($ciclo);
        $centavos = $plan->precoCentavos($ciclo);

        // Teto do preapproval MP: acima disso a cobrança automática é recusada pelo provedor.
        // Esses casos vão pro checkout assistido (WhatsApp) — nunca self-service por cartão.
        $teto = (int) config('services.mercadopago.preapproval_teto_centavos', 400000);
        if ($centavos > $teto) {
            throw new RuntimeException('Valor acima do limite de cobrança automática. Fale com o atendimento para assinar este plano.');
        }

        if ($planId === null || $centavos <= 0) {
            throw new RuntimeException('Plano sem preapproval_plan sincronizado no Mercado Pago.');
        }

        // Persiste pendente ANTES de chamar o MP (external_reference + idempotência de estado).
        // updateOrCreate por user_id: a tabela tem UNIQUE(user_id) — reusa a linha ao
        // re-assinar depois de um cancelamento (não estoura violação de unique). Não zera
        // o saldo incluso já concedido (guardrail; o rollover cap apara na próxima concessão).
        $sub = AccountSubscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'subscription_plan_id' => $plan->id,
                'status' => AccountSubscription::STATUS_PENDENTE,
                'ciclo' => $ciclo,
                'mp_preapproval_id' => null,
            ],
        );

        $resp = $this->client->criarPreapproval([
            'preapproval_plan_id' => $planId,
            'reason' => "FiscalDock {$plan->nome} ({$ciclo})",
            'payer_email' => $user->email,
            'card_token_id' => $cardToken,
            'auto_recurring' => [
                'frequency' => $ciclo === 'anual' ? 12 : 1,
                'frequency_type' => 'months',
                'transaction_amount' => round($centavos / 100, 2),
                'currency_id' => 'BRL',
            ],
            'back_url' => url('/app/planos'),
            'status' => 'authorized',
            'external_reference' => (string) $sub->id,
        ]);

        $mpId = $resp['id'] ?? null;

        if ($mpId === null) {
            $sub->update(['status' => AccountSubscription::STATUS_CANCELADA]);
            throw new RuntimeException('Mercado Pago não criou a assinatura: '.json_encode($resp));
        }

        $sub->update(['mp_preapproval_id' => (string) $mpId]);

        return $sub->fresh();
    }
}
