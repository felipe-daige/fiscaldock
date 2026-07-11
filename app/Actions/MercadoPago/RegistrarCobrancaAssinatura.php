<?php

namespace App\Actions\MercadoPago;

use App\Models\AccountSubscription;
use App\Models\MercadoPagoPayment;
use App\Notifications\AssinaturaPagamentoFalhouNotification;
use App\Notifications\AssinaturaRenovadaNotification;
use App\Services\MercadoPago\MercadoPagoClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Processa um authorized_payment de assinatura (cada cobrança recorrente).
 *
 * NÃO concede crédito (concessão é do scheduler assinatura:conceder-creditos).
 * Aqui: registra a cobrança (auditoria/idempotência) e trata dunning —
 * approved mantém ativa + avança renova_em; rejected/cancelled → inadimplente.
 */
class RegistrarCobrancaAssinatura
{
    public function __construct(private MercadoPagoClient $client = new MercadoPagoClient) {}

    public function execute(string $authorizedPaymentId): ?MercadoPagoPayment
    {
        $dados = $this->client->buscarAuthorizedPayment($authorizedPaymentId);
        $status = $dados['status'] ?? null;
        $preapprovalId = $dados['preapproval_id'] ?? null;
        $valor = (float) ($dados['transaction_amount'] ?? 0);

        $recibo = null;

        $pagamento = DB::transaction(function () use ($authorizedPaymentId, $status, $preapprovalId, $valor, $dados, &$recibo) {
            $sub = $preapprovalId
                ? AccountSubscription::lockForUpdate()->where('mp_preapproval_id', $preapprovalId)->first()
                : null;

            if ($sub === null) {
                return null;
            }

            // Idempotência: 1 linha por authorized_payment.
            $pagamento = MercadoPagoPayment::firstOrNew(['mp_payment_id' => $authorizedPaymentId]);
            $novo = ! $pagamento->exists;
            if ($novo) {
                $pagamento->fill([
                    'user_id' => $sub->user_id,
                    'account_subscription_id' => $sub->id,
                    'tipo' => 'subscription',
                    'pacote' => $sub->plan->codigo ?? 'assinatura',
                    'valor' => $valor,
                    'creditos' => 0, // concessão é do scheduler
                    'idempotency_key' => 'sub-ap-'.$authorizedPaymentId.'-'.Str::random(6),
                ]);
            }
            $pagamento->fill(['status' => $status ?? 'unknown', 'payload' => $dados])->save();

            if ($status === 'approved') {
                $sub->update([
                    'status' => AccountSubscription::STATUS_ATIVA,
                    'renova_em' => $sub->ciclo === 'anual' ? now()->addYear() : now()->addMonthNoOverflow(),
                ]);
            } elseif (in_array($status, ['rejected', 'cancelled'], true)) {
                $sub->update(['status' => AccountSubscription::STATUS_INADIMPLENTE]);
            }

            // Marca o recibo p/ disparar DEPOIS do commit — só na 1ª vez que vemos este
            // authorized_payment (idempotente: re-entrega do webhook não reenvia e-mail).
            if ($novo && in_array($status, ['approved', 'rejected', 'cancelled'], true)) {
                $recibo = [
                    'user' => $sub->user,
                    'status' => $status,
                    'plano' => $sub->plan->nome ?? ($sub->plan->codigo ?? 'assinatura'),
                    'valor' => $valor,
                    'renova_em' => optional($sub->renova_em)->format('d/m/Y'),
                ];
            }

            return $pagamento;
        });

        if ($recibo !== null && $recibo['user'] !== null) {
            $notificacao = $recibo['status'] === 'approved'
                ? new AssinaturaRenovadaNotification($recibo['plano'], $recibo['valor'], $recibo['renova_em'])
                : new AssinaturaPagamentoFalhouNotification($recibo['plano']);

            $recibo['user']->notify($notificacao);
        }

        return $pagamento;
    }
}
