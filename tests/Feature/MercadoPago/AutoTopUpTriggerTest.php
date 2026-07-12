<?php

use App\Jobs\ProcessarAutoTopUpJob;
use App\Models\RecargaAutomatica;
use App\Models\User;
use App\Services\SaldoService;
use Illuminate\Support\Facades\Queue;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

function recargaSaldo(User $user, array $over = []): RecargaAutomatica
{
    return RecargaAutomatica::create(array_merge([
        'user_id' => $user->id, 'gatilho' => 'saldo', 'limite_creditos' => 50,
        'pacote' => 'business', 'creditos' => 1000, 'valor' => 200,
        'status' => 'ativa', 'mp_customer_id' => 'CUS', 'mp_card_id' => 'CARD',
        'cobranca_em_andamento' => false,
    ], $over));
}

it('deduct que deixa saldo < limite despacha o job', function () {
    Queue::fake();
    $user = User::factory()->create(['credits' => 60]);
    recargaSaldo($user);
    (new SaldoService)->deduct($user, 20); // 60-20=40 < 50
    Queue::assertPushed(ProcessarAutoTopUpJob::class);
});

it('deduct que mantém saldo >= limite NÃO despacha', function () {
    Queue::fake();
    $user = User::factory()->create(['credits' => 100]);
    recargaSaldo($user);
    (new SaldoService)->deduct($user, 20); // 80 >= 50
    Queue::assertNotPushed(ProcessarAutoTopUpJob::class);
});

it('cobranca_em_andamento bloqueia o dispatch', function () {
    Queue::fake();
    $user = User::factory()->create(['credits' => 60]);
    recargaSaldo($user, ['cobranca_em_andamento' => true]);
    (new SaldoService)->deduct($user, 20);
    Queue::assertNotPushed(ProcessarAutoTopUpJob::class);
});

it('dentro do cooldown bloqueia o dispatch', function () {
    Queue::fake();
    config(['services.mercadopago.auto_topup.cooldown_minutos' => 5]);
    $user = User::factory()->create(['credits' => 60]);
    recargaSaldo($user, ['ultima_tentativa_em' => now()->subMinutes(2)]);
    (new SaldoService)->deduct($user, 20);
    Queue::assertNotPushed(ProcessarAutoTopUpJob::class);
});

it('gatilho=tempo nunca dispara auto top-up por saldo', function () {
    Queue::fake();
    $user = User::factory()->create(['credits' => 60]);
    recargaSaldo($user, ['gatilho' => 'tempo', 'limite_creditos' => null]);
    (new SaldoService)->deduct($user, 20);
    Queue::assertNotPushed(ProcessarAutoTopUpJob::class);
});
