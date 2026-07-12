<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SaldoService;
use Illuminate\Console\Command;

class AdicionarSaldoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saldo:adicionar {user_id : ID do usuário} {valor : Valor em reais a adicionar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adiciona saldo em reais a um usuário';

    /**
     * Execute the console command.
     */
    public function handle(SaldoService $saldoService)
    {
        $userId = $this->argument('user_id');
        $valorReais = (float) $this->argument('valor');
        $amount = app(\App\Services\PricingCatalogService::class)->currencyToCredits($valorReais);

        $user = User::find($userId);

        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado.");
            return Command::FAILURE;
        }

        $saldoAnterior = $saldoService->getBalance($user);

        $this->info("Usuário: {$user->name} ({$user->email})");
        $this->info('Saldo anterior: '.\App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency($saldoAnterior)));
        $this->info('Adicionando: '.\App\Support\Dinheiro::brl($valorReais));

        $saldoService->add($user, $amount);

        $saldoAtual = $saldoService->getBalance($user);

        $this->info('Saldo atual: '.\App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency($saldoAtual)));
        $this->newLine();
        $this->info('✓ Saldo adicionado com sucesso!');

        return Command::SUCCESS;
    }
}







