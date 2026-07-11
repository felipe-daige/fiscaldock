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

            // Proration da troca de plano (marker setado em TrocarPlanoMercadoPago): a 1ª concessão
            // do tier destino reconcilia o ciclo em curso — expira o incluso antigo pro-rata e
            // concede o novo pro-rata pelos dias restantes. Renovação normal NÃO tem marker e cai
            // no rollover cap. Os dois eventos são mutuamente exclusivos.
            $proration = $sub->proration_pendente;
            $fracao = is_array($proration) ? (float) ($proration['fracao_restante'] ?? 0) : 0.0;

            if ($fracao > 0) {
                $this->concederComProration($sub, $plan, $user, $mensal, $fracao);
            } else {
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
                            'Expiração de saldo incluso acima do limite de acúmulo (R$ '.number_format(app(\App\Services\PricingCatalogService::class)->creditsToCurrency($capBancado), 2, ',', '.').').',
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
                        "Saldo incluso do plano {$plan->nome} (R$ ".number_format(app(\App\Services\PricingCatalogService::class)->creditsToCurrency($mensal), 2, ',', '.').').',
                        $sub,
                    );
                    $sub->creditos_inclusos_saldo = (int) $sub->creditos_inclusos_saldo + $mensal;
                }
            }

            // 3) Agenda a próxima concessão (cadência mensal pra mensal E anual) e limpa o marker.
            $sub->ultimo_grant_em = now();
            $sub->proximo_grant_em = now()->addMonthNoOverflow();
            $sub->proration_pendente = null;
            $sub->save();
        });
    }

    /**
     * Reconciliação pro-rata da troca de plano.
     *
     * `$fracao` = parte do ciclo antigo ainda não usada. O usuário mantém a parte já decorrida
     * do incluso antigo (1-fração, já "ganha") e recebe a parte restante do incluso novo (fração):
     *
     *   expira = round(bucket_antigo × fração)            → devolve a alocação não usada do tier antigo
     *   concede = round(creditos_inclusos_novo × fração)  → aloca o tier novo só pelos dias restantes
     *
     * A próxima renovação concede o mensal cheio do tier novo (rollover cap normal).
     */
    private function concederComProration(
        AccountSubscription $sub,
        \App\Models\SubscriptionPlan $plan,
        User $user,
        int $mensal,
        float $fracao,
    ): void {
        $saldoBucket = (int) $sub->creditos_inclusos_saldo;
        $expira = min((int) round($saldoBucket * $fracao), $this->credits->getBalance($user));
        if ($expira > 0) {
            $this->credits->deduct(
                $user,
                $expira,
                'subscription_proration',
                'Ajuste pro-rata da troca de plano: expira R$ '.number_format(app(\App\Services\PricingCatalogService::class)->creditsToCurrency($expira), 2, ',', '.').' de saldo incluso não usado do plano anterior.',
                $sub,
            );
        }
        $sub->creditos_inclusos_saldo = max(0, $saldoBucket - $expira);

        $concede = (int) round($mensal * $fracao);
        if ($concede > 0) {
            $this->credits->add(
                $user,
                $concede,
                'subscription_proration',
                "Ajuste pro-rata da troca de plano: concede {$concede} créditos inclusos do plano {$plan->nome} pelos dias restantes do ciclo.",
                $sub,
            );
            $sub->creditos_inclusos_saldo = (int) $sub->creditos_inclusos_saldo + $concede;
        }
    }
}
