<?php

use App\Models\User;
use App\Services\SaldoService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

// Regressão da re-base R$ (2026-07-15): trial_credits_remaining virou R$ float.
// O `(int)` no deduct zerava saldos de trial abaixo de R$ 1 → o consumo do trial
// não decrementava e a expiração double-contava saldo já gasto.
it('decrementa trial_credits_remaining abaixo de R$ 1 ao descontar (não trava por (int))', function () {
    $user = User::factory()->create([
        'credits' => 0.50,
        'trial_credits_remaining' => 0.50,
        'trial_credits_granted' => 20.00,
    ]);

    $ok = app(SaldoService::class)->deduct($user, 0.20, 'consulta_lote');

    expect($ok)->toBeTrue();
    $fresh = $user->fresh();
    expect($fresh->credits)->toBe(0.30);
    // O bug travava isto em 0.50; com o fix decrementa junto com o saldo.
    expect($fresh->trial_credits_remaining)->toBe(0.30);
});

it('consome o trial na mesma proporção do débito quando o saldo é fracionário', function () {
    $user = User::factory()->create([
        'credits' => 10.00,
        'trial_credits_remaining' => 0.90,
    ]);

    app(SaldoService::class)->deduct($user, 0.40, 'consulta_lote');

    $fresh = $user->fresh();
    expect($fresh->credits)->toBe(9.60);
    expect($fresh->trial_credits_remaining)->toBe(0.50);
});
