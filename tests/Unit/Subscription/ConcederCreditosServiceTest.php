<?php

use App\Models\AccountSubscription;
use App\Models\CreditTransaction;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Subscription\ConcederCreditosService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    (new Database\Seeders\SubscriptionPlanSeeder)->run();
});

function assinaturaEssencial(User $user, array $overrides = []): AccountSubscription
{
    $plan = SubscriptionPlan::where('codigo', 'essencial')->first(); // 300 cr/mês, rollover 1x

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

    (new ConcederCreditosService)->conceder($sub, primeiraComoCompra: true);

    expect($user->fresh()->credits)->toBe(300);
    expect($sub->fresh()->creditos_inclusos_saldo)->toBe(300);
    expect($sub->fresh()->proximo_grant_em)->not->toBeNull();
    expect(CreditTransaction::where('user_id', $user->id)->where('type', 'purchase')->count())->toBe(1);
});

it('concessão mensal posterior usa type subscription_credit', function () {
    $user = User::factory()->create(['credits' => 0]);
    $sub = assinaturaEssencial($user);

    (new ConcederCreditosService)->conceder($sub, primeiraComoCompra: false);

    expect($user->fresh()->credits)->toBe(300);
    expect(CreditTransaction::where('type', 'subscription_credit')->count())->toBe(1);
    expect(CreditTransaction::where('type', 'purchase')->count())->toBe(0);
});

it('rollover cap: saldo igual ao cap não expira e concede o mês cheio', function () {
    $user = User::factory()->create(['credits' => 300]);
    $sub = assinaturaEssencial($user, ['creditos_inclusos_saldo' => 300]);

    (new ConcederCreditosService)->conceder($sub, primeiraComoCompra: false);

    // cap = 1x300 = 300 bancado; nada expira (300 <= 300); concede +300.
    expect($user->fresh()->credits)->toBe(600);
    expect($sub->fresh()->creditos_inclusos_saldo)->toBe(600);
});

it('rollover cap: saldo acima do cap expira o excedente antes de conceder', function () {
    $user = User::factory()->create(['credits' => 500]);
    $sub = assinaturaEssencial($user, ['creditos_inclusos_saldo' => 500]);

    (new ConcederCreditosService)->conceder($sub, primeiraComoCompra: false);

    // cap 300: expira 500-300=200 (credits 500→300), depois concede 300 → 600.
    expect($user->fresh()->credits)->toBe(600);
    expect($sub->fresh()->creditos_inclusos_saldo)->toBe(600);
    expect(CreditTransaction::where('type', 'subscription_expiration')->sum('amount'))->toBe(-200);
});

it('expira no máximo o que ainda existe no saldo do usuário (já gastou parte)', function () {
    // bucket registrado 500, mas usuário só tem 100 créditos (gastou o resto). Excedente
    // do cap = 200, mas só há 100 no saldo → expira 100 (saldo→0). Depois concede 300 → 300.
    $user = User::factory()->create(['credits' => 100]);
    $sub = assinaturaEssencial($user, ['creditos_inclusos_saldo' => 500]);

    (new ConcederCreditosService)->conceder($sub, primeiraComoCompra: false);

    expect($user->fresh()->credits)->toBe(300);
});
