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
    $plan = SubscriptionPlan::where('codigo', 'essencial')->first(); // 175 unidades/mês (R$ 35), rollover 1x

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

    expect($user->fresh()->credits)->toBe(175);
    expect($sub->fresh()->creditos_inclusos_saldo)->toBe(175);
    expect($sub->fresh()->proximo_grant_em)->not->toBeNull();
    expect(SaldoTransacao::where('user_id', $user->id)->where('type', 'purchase')->count())->toBe(1);
});

it('concessão mensal posterior usa type subscription_credit', function () {
    $user = User::factory()->create(['credits' => 0]);
    $sub = assinaturaEssencial($user);

    (new ConcederSaldoService)->conceder($sub, primeiraComoCompra: false);

    expect($user->fresh()->credits)->toBe(175);
    expect(SaldoTransacao::where('type', 'subscription_credit')->count())->toBe(1);
    expect(SaldoTransacao::where('type', 'purchase')->count())->toBe(0);
});

it('rollover cap: saldo igual ao cap não expira e concede o mês cheio', function () {
    $user = User::factory()->create(['credits' => 175]);
    $sub = assinaturaEssencial($user, ['creditos_inclusos_saldo' => 175]);

    (new ConcederSaldoService)->conceder($sub, primeiraComoCompra: false);

    // cap = 1x175 = 175 bancado; nada expira (175 <= 175); concede +175.
    expect($user->fresh()->credits)->toBe(350);
    expect($sub->fresh()->creditos_inclusos_saldo)->toBe(350);
});

it('rollover cap: saldo acima do cap expira o excedente antes de conceder', function () {
    $user = User::factory()->create(['credits' => 500]);
    $sub = assinaturaEssencial($user, ['creditos_inclusos_saldo' => 500]);

    (new ConcederSaldoService)->conceder($sub, primeiraComoCompra: false);

    // cap 175: expira 500-175=325 (credits 500→175), depois concede 175 → 350.
    expect($user->fresh()->credits)->toBe(350);
    expect($sub->fresh()->creditos_inclusos_saldo)->toBe(350);
    expect(SaldoTransacao::where('type', 'subscription_expiration')->sum('amount'))->toBe(-325);
});

it('expira no máximo o que ainda existe no saldo do usuário (já gastou parte)', function () {
    // bucket registrado 500, mas usuário só tem 100 créditos (gastou o resto). Excedente
    // do cap = 325, mas só há 100 no saldo → expira 100 (saldo→0). Depois concede 175 → 175.
    $user = User::factory()->create(['credits' => 100]);
    $sub = assinaturaEssencial($user, ['creditos_inclusos_saldo' => 500]);

    (new ConcederSaldoService)->conceder($sub, primeiraComoCompra: false);

    expect($user->fresh()->credits)->toBe(175);
});
