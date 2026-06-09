<?php

namespace App\Actions\MercadoPago;

use App\Models\AccountSubscription;
use App\Services\MercadoPago\MercadoPagoClient;
use App\Services\Subscription\ConcederCreditosService;
use Illuminate\Support\Facades\DB;

/**
 * Processa uma notificação de preapproval (ciclo de vida da assinatura).
 *
 * Fonte de verdade: consulta /preapproval/{id} no MP. Idempotente:
 * - authorized + ainda não ativa → ativa e concede o 1º mês (purchase). Reentrega: no-op.
 * - cancelled/finished → cancelada (saldo preservado).
 * - paused → inadimplente.
 */
class AtivarAssinaturaMercadoPago
{
    public function __construct(
        private MercadoPagoClient $client = new MercadoPagoClient,
        private ConcederCreditosService $conceder = new ConcederCreditosService,
    ) {}

    public function execute(string $preapprovalId): ?AccountSubscription
    {
        $dados = $this->client->buscarPreapproval($preapprovalId);
        $status = $dados['status'] ?? null;

        return DB::transaction(function () use ($preapprovalId, $status) {
            $sub = AccountSubscription::lockForUpdate()
                ->where('mp_preapproval_id', $preapprovalId)->first();

            if ($sub === null) {
                return null;
            }

            if ($status === 'authorized') {
                // Ativa + concede o 1º mês SÓ se ainda não estava ativa (idempotência).
                if ($sub->status !== AccountSubscription::STATUS_ATIVA) {
                    $sub->update([
                        'status' => AccountSubscription::STATUS_ATIVA,
                        'iniciada_em' => $sub->iniciada_em ?? now(),
                    ]);
                    $this->conceder->conceder($sub, primeiraComoCompra: true);
                }
            } elseif (in_array($status, ['cancelled', 'finished'], true)) {
                $sub->update(['status' => AccountSubscription::STATUS_CANCELADA]);
            } elseif ($status === 'paused') {
                $sub->update(['status' => AccountSubscription::STATUS_INADIMPLENTE]);
            }

            return $sub->fresh();
        });
    }
}
