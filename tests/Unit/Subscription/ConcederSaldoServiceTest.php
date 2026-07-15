<?php

use App\Models\AccountSubscription;
use App\Models\SaldoTransacao;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Subscription\ConcederSaldoService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    (new Database\Seeders\SubscriptionPlanSeeder)->run();
});

function assinaturaEssencial(User $user, array $overrides = []): AccountSubscription
{
    $plan = SubscriptionPlan::where('codigo', 'essencial')->first(); // R$ 35/mês inclusos, rollover 1x

    return AccountSubscription::create(array_merge([
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
        'status' => AccountSubscription::STATUS_ATIVA,
        'ciclo' => 'mensal',
        'creditos_inclusos_saldo' => 0,
    ], $overrides));
}

it('1ª concessão credita como purchase (destrava 1ª compra) e agenda o próximo mês', function () {
    $user = User::factory()->create(['credits' => 0]);
    $sub = assinaturaEssencial($user);

    (new ConcederSaldoService)->conceder($sub, primeiraComoCompra: true);

    expect((float) $user->fresh()->credits)->toBe(35.0);
    expect((float) $sub->fresh()->creditos_inclusos_saldo)->toBe(35.0);
    expect($sub->fresh()->proximo_grant_em)->not->toBeNull();
    expect(SaldoTransacao::where('user_id', $user->id)->where('type', 'purchase')->count())->toBe(1);
});

it('concessão mensal posterior usa type subscription_credit', function () {
    $user = User::factory()->create(['credits' => 0]);
    $sub = assinaturaEssencial($user);

    (new ConcederSaldoService)->conceder($sub, primeiraComoCompra: false);

    expect((float) $user->fresh()->credits)->toBe(35.0);
    expect(SaldoTransacao::where('type', 'subscription_credit')->count())->toBe(1);
    expect(SaldoTransacao::where('type', 'purchase')->count())->toBe(0);
});

it('rollover cap: saldo igual ao cap não expira e concede o mês cheio', function () {
    $user = User::factory()->create(['credits' => 35]);
    $sub = assinaturaEssencial($user, ['creditos_inclusos_saldo' => 35]);

    (new ConcederSaldoService)->conceder($sub, primeiraComoCompra: false);

    // cap = 1x35 = 35 bancado; nada expira (35 <= 35); concede +35.
    expect((float) $user->fresh()->credits)->toBe(70.0);
    expect((float) $sub->fresh()->creditos_inclusos_saldo)->toBe(70.0);
});

it('rollover cap: saldo acima do cap expira o excedente antes de conceder', function () {
    $user = User::factory()->create(['credits' => 100]);
    $sub = assinaturaEssencial($user, ['creditos_inclusos_saldo' => 100]);

    (new ConcederSaldoService)->conceder($sub, primeiraComoCompra: false);

    // cap 35: expira 100-35=65 (credits 100→35), depois concede 35 → 70.
    expect((float) $user->fresh()->credits)->toBe(70.0);
    expect((float) $sub->fresh()->creditos_inclusos_saldo)->toBe(70.0);
    expect((float) SaldoTransacao::where('type', 'subscription_expiration')->sum('amount'))->toBe(-65.0);
});

it('expira no máximo o que ainda existe no saldo do usuário (já gastou parte)', function () {
    // bucket registrado 100, mas usuário só tem 20 no saldo (gastou o resto). Excedente
    // do cap = 65, mas só há 20 no saldo → expira 20 (saldo→0). Depois concede 35 → 35.
    $user = User::factory()->create(['credits' => 20]);
    $sub = assinaturaEssencial($user, ['creditos_inclusos_saldo' => 100]);

    (new ConcederSaldoService)->conceder($sub, primeiraComoCompra: false);

    expect((float) $user->fresh()->credits)->toBe(35.0);
});
