<?php

namespace App\Services\Subscription;

use App\Models\AccountSubscription;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Support\Facades\DB;

/**
 * Concede os créditos inclusos de uma assinatura, espelhando o mecanismo de trial
 * do CreditService: aplica o rollover cap (expira o excedente não-bancado do ciclo
 * anterior) e então credita o mês corrente.
 *
 * - 1ª concessão (ativação): type=purchase (amount>0) → satisfaz "1ª compra" e
 *   destrava Compliance/DD (mesmo critério de credit_transactions.type=purchase).
 * - Concessões seguintes: type=subscription_credit.
 * - Expiração do excedente: type=subscription_expiration (débito).
 *
 * O bucket de créditos inclusos vive em account_subscriptions.creditos_inclusos_saldo.
 */
class ConcederCreditosService
{
    public function __construct(private CreditService $credits = new CreditService) {}

    public function conceder(AccountSubscription $sub, bool $primeiraComoCompra = false): void
    {
        DB::transaction(function () use ($sub, $primeiraComoCompra) {
            $sub = AccountSubscription::lockForUpdate()->with('plan')->find($sub->id);
            $plan = $sub->plan;
            $user = User::find($sub->user_id);

            $mensal = (int) $plan->creditos_inclusos;
            $capBancado = (int) floor(((float) $plan->rollover_cap_multiplicador) * $mensal);

            // 1) Rollover cap: expira o excedente do bucket acima do cap, limitado ao
            //    que ainda existe no saldo do usuário (ele pode já ter gastado).
            $saldoBucket = (int) $sub->creditos_inclusos_saldo;
            if ($saldoBucket > $capBancado) {
                $excedente = $saldoBucket - $capBancado;
                $expira = min($excedente, $this->credits->getBalance($user));
                if ($expira > 0) {
                    $this->credits->deduct(
                        $user,
                        $expira,
                        'subscription_expiration',
                        "Expiração de créditos inclusos acima do limite de acúmulo ({$capBancado}).",
                        $sub,
                    );
                }
                $sub->creditos_inclusos_saldo = $saldoBucket - $excedente; // bucket cai pro cap
            }

            // 2) Concede o mês corrente.
            if ($mensal > 0) {
                $this->credits->add(
                    $user,
                    $mensal,
                    $primeiraComoCompra ? 'purchase' : 'subscription_credit',
                    "Créditos inclusos do plano {$plan->nome} ({$mensal} créditos).",
                    $sub,
                );
                $sub->creditos_inclusos_saldo = (int) $sub->creditos_inclusos_saldo + $mensal;
            }

            // 3) Agenda a próxima concessão (cadência mensal pra mensal E anual).
            $sub->ultimo_grant_em = now();
            $sub->proximo_grant_em = now()->addMonthNoOverflow();
            $sub->save();
        });
    }
}
