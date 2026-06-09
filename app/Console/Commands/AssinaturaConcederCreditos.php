<?php

namespace App\Console\Commands;

use App\Models\AccountSubscription;
use App\Services\Subscription\ConcederCreditosService;
use Illuminate\Console\Command;

/**
 * Concede os créditos inclusos mensais das assinaturas ativas cujo proximo_grant_em
 * já venceu. Roda pra mensal E anual (anual recebe 12 concessões ao longo do ano).
 * Idempotente: ConcederCreditosService avança proximo_grant_em a cada concessão.
 */
class AssinaturaConcederCreditos extends Command
{
    protected $signature = 'assinatura:conceder-creditos';

    protected $description = 'Concede os créditos inclusos mensais das assinaturas ativas vencidas';

    public function handle(ConcederCreditosService $conceder): int
    {
        $assinaturas = AccountSubscription::query()
            ->where('status', AccountSubscription::STATUS_ATIVA)
            ->whereNotNull('proximo_grant_em')
            ->where('proximo_grant_em', '<=', now())
            ->get();

        foreach ($assinaturas as $sub) {
            $conceder->conceder($sub, primeiraComoCompra: false);
            $this->info("✓ assinatura #{$sub->id} (user {$sub->user_id}) — créditos concedidos");
        }

        $this->line("Concessões: {$assinaturas->count()}");

        return self::SUCCESS;
    }
}
