<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPlan;
use App\Services\MercadoPago\MercadoPagoClient;
use Illuminate\Console\Command;

/**
 * Cria/atualiza no Mercado Pago um preapproval_plan por tier pago × ciclo
 * (mensal/anual) e grava o id resultante em subscription_plans. Free e Enterprise
 * (preço 0 / sob consulta) são pulados.
 *
 * Idempotência simples: só cria quando o id ainda não está gravado. Re-rodar não
 * duplica (mas não reconcilia preço — pra trocar preço, use --force).
 */
class SincronizarPreapprovalPlans extends Command
{
    protected $signature = 'assinatura:sincronizar-planos {--force : recria mesmo se já houver id}';

    protected $description = 'Cria os preapproval_plans dos tiers no Mercado Pago e grava os ids';

    public function handle(MercadoPagoClient $client): int
    {
        $teto = (int) config('services.mercadopago.preapproval_teto_centavos', 400000);

        $tiers = SubscriptionPlan::whereNotIn('codigo', ['free', 'enterprise'])
            ->where('is_active', true)->orderBy('ordem')->get();

        foreach ($tiers as $plan) {
            foreach (['mensal' => 1, 'anual' => 12] as $ciclo => $frequencia) {
                $coluna = "mp_preapproval_plan_id_{$ciclo}";
                $centavos = $plan->precoCentavos($ciclo);

                if ($centavos <= 0) {
                    continue;
                }
                // Acima do teto do MP: não cria preapproval_plan (a venda vai pro checkout
                // assistido via WhatsApp). Deixa a coluna nula de propósito.
                if ($centavos > $teto) {
                    $this->warn("· {$plan->codigo}/{$ciclo}: R$ ".number_format($centavos / 100, 2, ',', '.').' acima do teto MP (R$ '.number_format($teto / 100, 0, ',', '.').') → checkout assistido, sem preapproval_plan');

                    continue;
                }
                if ($plan->{$coluna} && ! $this->option('force')) {
                    $this->line("· {$plan->codigo}/{$ciclo}: já sincronizado ({$plan->{$coluna}})");

                    continue;
                }

                $resp = $client->criarPreapprovalPlan([
                    'reason' => "FiscalDock {$plan->nome} ({$ciclo})",
                    'auto_recurring' => [
                        'frequency' => $frequencia,
                        'frequency_type' => 'months',
                        'transaction_amount' => round($centavos / 100, 2),
                        'currency_id' => 'BRL',
                    ],
                    'back_url' => url('/app/planos'),
                ]);

                $id = $resp['id'] ?? null;
                if ($id === null) {
                    $this->error("✗ {$plan->codigo}/{$ciclo}: MP não retornou id — ".json_encode($resp));

                    return self::FAILURE;
                }

                $plan->update([$coluna => $id]);
                $this->info("✓ {$plan->codigo}/{$ciclo} → {$id}");
            }
        }

        return self::SUCCESS;
    }
}
