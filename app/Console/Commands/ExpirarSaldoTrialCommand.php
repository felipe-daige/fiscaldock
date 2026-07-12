<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SaldoService;
use Illuminate\Console\Command;

class ExpirarSaldoTrialCommand extends Command
{
    protected $signature = 'trial:expirar-saldo';

    protected $description = 'Expira o saldo promocional remanescente de trials vencidos';

    public function handle(SaldoService $saldoService)
    {
        $expiredUsers = 0;
        $saldoExpirado = 0;

        User::query()
            ->where('trial_used', true)
            ->whereNotNull('trial_expires_at')
            ->where('trial_expires_at', '<=', now())
            ->where('trial_credits_remaining', '>', 0)
            ->chunkById(100, function ($users) use ($saldoService, &$expiredUsers, &$saldoExpirado) {
                foreach ($users as $user) {
                    $expired = $saldoService->expireTrialBalance($user);

                    if ($expired > 0) {
                        $expiredUsers++;
                        $saldoExpirado += $expired;
                        $this->info("Usuário {$user->id}: saldo promocional expirado.");
                    }
                }
            });

        $this->newLine();
        $this->info("Usuários com saldo expirado: {$expiredUsers}");
        $this->info('Saldo expirado no total: '.\App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency($saldoExpirado)));

        return Command::SUCCESS;
    }
}
