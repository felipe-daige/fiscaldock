<?php

namespace App\Actions\MercadoPago;

use App\Models\AccountSubscription;
use App\Models\User;
use App\Services\MercadoPago\MercadoPagoClient;
use RuntimeException;

/**
 * Cancela a assinatura ativa do usuário: PUT status=cancelled no preapproval do MP
 * e marca a linha como cancelada. NÃO apaga saldo/dados (guardrail de downgrade).
 * O webhook de preapproval cancelled confirma de forma idempotente.
 */
class CancelarAssinaturaMercadoPago
{
    public function __construct(private MercadoPagoClient $client = new MercadoPagoClient) {}

    public function execute(User $user): AccountSubscription
    {
        $sub = AccountSubscription::where('user_id', $user->id)
            ->whereIn('status', [AccountSubscription::STATUS_ATIVA, AccountSubscription::STATUS_INADIMPLENTE])
            ->first();

        if ($sub === null || $sub->mp_preapproval_id === null) {
            throw new RuntimeException('Nenhuma assinatura ativa para cancelar.');
        }

        $this->client->cancelarPreapproval($sub->mp_preapproval_id);
        $sub->update(['status' => AccountSubscription::STATUS_CANCELADA]);

        return $sub->fresh();
    }
}
