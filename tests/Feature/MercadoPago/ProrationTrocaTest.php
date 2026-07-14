<?php

use App\Models\AccountSubscription;
use App\Models\SaldoTransacao;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SaldoService;
use App\Services\Subscription\ConcederSaldoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.mercadopago.access_token' => 'TEST-token',
        'services.mercadopago.base_url' => 'https://api.mercadopago.com',
        'services.mercadopago.preapproval_teto_centavos' => 400000,
    ]);
    (new Database\Seeders\SubscriptionPlanSeeder)->run();
});

it('a troca grava o marker de proration com a fração restante do ciclo', function () {
    SubscriptionPlan::where('codigo', 'essencial')->update(['mp_preapproval_plan_id_mensal' => 'PLAN-ESS-MES']);
    SubscriptionPlan::where('codigo', 'profissional')->update(['mp_preapproval_plan_id_mensal' => 'PLAN-PRO-MES']);

    $essencial = SubscriptionPlan::where('codigo', 'essencial')->first();
    $user = User::factory()->create();
    $sub = AccountSubscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $essencial->id,
        'status' => AccountSubscription::STATUS_ATIVA,
        'ciclo' => 'mensal',
        'mp_preapproval_id' => 'PRE-OLD',
        'creditos_inclusos_saldo' => 175,
        // ciclo de 30 dias, 20 restantes → fração ≈ 0,667
        'ultimo_grant_em' => now()->subDays(10),
        'proximo_grant_em' => now()->addDays(20),
    ]);
    actingAs($user);

    Http::fake([
        'api.mercadopago.com/preapproval/PRE-OLD' => Http::response(['id' => 'PRE-OLD', 'status' => 'cancelled'], 200),
        'api.mercadopago.com/preapproval' => Http::response(['id' => 'PRE-NEW', 'status' => 'pending'], 201),
    ]);

    postJson(route('app.assinatura.trocar'), [
        'plano' => 'profissional', 'ciclo' => 'mensal', 'token' => 'card-token',
    ])->assertOk();

    $marker = $sub->fresh()->proration_pendente;
    expect($marker)->toBeArray();
    expect((float) $marker['fracao_restante'])->toBeGreaterThan(0.6)->toBeLessThan(0.72);
});

it('a 1ª concessão pós-troca expira o incluso antigo pro-rata e concede o novo pro-rata', function () {
    $prof = SubscriptionPlan::where('codigo', 'profissional')->first(); // 400 inclusos
    $user = User::factory()->create();
    app(SaldoService::class)->add($user, 175, 'subscription_credit');

    $sub = AccountSubscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $prof->id,
        'status' => AccountSubscription::STATUS_ATIVA,
        'ciclo' => 'mensal',
        'creditos_inclusos_saldo' => 175,
        'proration_pendente' => ['fracao_restante' => 0.5],
    ]);

    app(ConcederSaldoService::class)->conceder($sub);

    $user->refresh();
    $sub->refresh();

    // expira 88 (175×0,5) e concede 200 (400×0,5) → 175 − 88 + 200 = 287
    expect($user->credits)->toBe(287);
    expect($sub->creditos_inclusos_saldo)->toBe(287);
    expect($sub->proration_pendente)->toBeNull();
    expect(SaldoTransacao::where('user_id', $user->id)->where('type', 'subscription_proration')->count())->toBe(2);
});

it('proration de downgrade concede menos (novo tier menor que o antigo)', function () {
    $ess = SubscriptionPlan::where('codigo', 'essencial')->first(); // destino: 175 inclusos
    $user = User::factory()->create();
    app(SaldoService::class)->add($user, 400, 'subscription_credit'); // vinha do profissional

    $sub = AccountSubscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $ess->id,
        'status' => AccountSubscription::STATUS_ATIVA,
        'ciclo' => 'mensal',
        'creditos_inclusos_saldo' => 400,
        'proration_pendente' => ['fracao_restante' => 0.5],
    ]);

    app(ConcederSaldoService::class)->conceder($sub);

    $user->refresh();
    // expira 200 (400×0,5) e concede 88 (175×0,5) → 400 − 200 + 88 = 288
    expect($user->credits)->toBe(288);
    expect($sub->fresh()->proration_pendente)->toBeNull();
});

it('a expiração pro-rata é limitada ao saldo real (usuário já gastou parte)', function () {
    $prof = SubscriptionPlan::where('codigo', 'profissional')->first(); // 400
    $user = User::factory()->create();
    app(SaldoService::class)->add($user, 50, 'subscription_credit'); // bucket 175 mas só 50 sobrou

    $sub = AccountSubscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $prof->id,
        'status' => AccountSubscription::STATUS_ATIVA,
        'ciclo' => 'mensal',
        'creditos_inclusos_saldo' => 175,
        'proration_pendente' => ['fracao_restante' => 0.5],
    ]);

    app(ConcederSaldoService::class)->conceder($sub);

    // expira min(88, 50)=50 → 0; concede 200 → 200
    expect($user->refresh()->credits)->toBe(200);
});

it('sem ciclo ancorado (nunca concedido) a troca não grava marker', function () {
    SubscriptionPlan::where('codigo', 'essencial')->update(['mp_preapproval_plan_id_mensal' => 'PLAN-ESS-MES']);
    SubscriptionPlan::where('codigo', 'profissional')->update(['mp_preapproval_plan_id_mensal' => 'PLAN-PRO-MES']);
    $ess = SubscriptionPlan::where('codigo', 'essencial')->first();

    $user = User::factory()->create();
    $sub = AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $ess->id,
        'status' => AccountSubscription::STATUS_ATIVA, 'ciclo' => 'mensal',
        'mp_preapproval_id' => 'PRE-OLD', 'creditos_inclusos_saldo' => 175,
        // sem ultimo_grant_em / iniciada_em / proximo_grant_em
    ]);
    actingAs($user);
    Http::fake([
        'api.mercadopago.com/preapproval/PRE-OLD' => Http::response(['id' => 'PRE-OLD', 'status' => 'cancelled'], 200),
        'api.mercadopago.com/preapproval' => Http::response(['id' => 'PRE-NEW', 'status' => 'pending'], 201),
    ]);

    postJson(route('app.assinatura.trocar'), ['plano' => 'profissional', 'ciclo' => 'mensal', 'token' => 't'])->assertOk();

    expect($sub->fresh()->proration_pendente)->toBeNull();
});

it('falha ao criar a nova preapproval restaura o snapshot e não deixa marker', function () {
    SubscriptionPlan::where('codigo', 'essencial')->update(['mp_preapproval_plan_id_mensal' => 'PLAN-ESS-MES']);
    SubscriptionPlan::where('codigo', 'profissional')->update(['mp_preapproval_plan_id_mensal' => 'PLAN-PRO-MES']);
    $ess = SubscriptionPlan::where('codigo', 'essencial')->first();

    $user = User::factory()->create();
    $sub = AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $ess->id,
        'status' => AccountSubscription::STATUS_ATIVA, 'ciclo' => 'mensal',
        'mp_preapproval_id' => 'PRE-OLD', 'creditos_inclusos_saldo' => 175,
        'ultimo_grant_em' => now()->subDays(10), 'proximo_grant_em' => now()->addDays(20),
    ]);
    actingAs($user);
    // MP não devolve id → CriarAssinatura lança RuntimeException
    Http::fake(['api.mercadopago.com/preapproval' => Http::response(['status' => 'error'], 500)]);

    postJson(route('app.assinatura.trocar'), ['plano' => 'profissional', 'ciclo' => 'mensal', 'token' => 't'])
        ->assertStatus(422);

    $sub->refresh();
    expect($sub->subscription_plan_id)->toBe($ess->id); // restaurado
    expect($sub->status)->toBe(AccountSubscription::STATUS_ATIVA);
    expect($sub->proration_pendente)->toBeNull(); // marker limpo no restore
});

it('concessão normal sem marker concede o mensal cheio (rollover cap)', function () {
    $ess = SubscriptionPlan::where('codigo', 'essencial')->first(); // 175 inclusos
    $user = User::factory()->create();

    $sub = AccountSubscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $ess->id,
        'status' => AccountSubscription::STATUS_ATIVA,
        'ciclo' => 'mensal',
        'creditos_inclusos_saldo' => 0,
    ]);

    app(ConcederSaldoService::class)->conceder($sub, primeiraComoCompra: true);

    $user->refresh();
    expect($user->credits)->toBe(175);
    expect(SaldoTransacao::where('user_id', $user->id)->where('type', 'purchase')->count())->toBe(1);
    expect(SaldoTransacao::where('user_id', $user->id)->where('type', 'subscription_proration')->count())->toBe(0);
});
