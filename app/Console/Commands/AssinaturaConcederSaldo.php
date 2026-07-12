<?php

namespace App\Console\Commands;

use App\Models\AccountSubscription;
use App\Services\Subscription\ConcederSaldoService;
use Illuminate\Console\Command;

/**
 * Concede o saldo incluso mensal das assinaturas ativas cujo proximo_grant_em
 * já venceu. Roda pra mensal E anual (anual recebe 12 concessões ao longo do ano).
 * Idempotente: ConcederSaldoService avança proximo_grant_em a cada concessão.
 */
class AssinaturaConcederSaldo extends Command
{
    protected $signature = 'assinatura:conceder-saldo';

    protected $description = 'Concede o saldo incluso mensal das assinaturas ativas vencidas';

    public function handle(ConcederSaldoService $conceder): int
    {
        $assinaturas = AccountSubscription::query()
            ->where('status', AccountSubscription::STATUS_ATIVA)
            ->whereNotNull('proximo_grant_em')
            ->where('proximo_grant_em', '<=', now())
            ->get();

        foreach ($assinaturas as $sub) {
            $conceder->conceder($sub, primeiraComoCompra: false);
            $this->info("✓ assinatura #{$sub->id} (user {$sub->user_id}) — saldo concedido");
        }

        $this->line("Concessões: {$assinaturas->count()}");

        return self::SUCCESS;
    }
}
