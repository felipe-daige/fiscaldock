<?php

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\AccountSubscription;
use App\Models\SaldoTransacao;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\AddonSuspensoNotification;
use App\Services\SaldoService;
use App\Services\Subscription\AddonService;
use App\Services\Subscription\ConcederSaldoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    (new Database\Seeders\SubscriptionPlanSeeder)->run();
});

function assinaturaAtivaAddon(User $user, string $codigo = 'essencial', array $extra = []): AccountSubscription
{
    $plan = SubscriptionPlan::where('codigo', $codigo)->first();

    return AccountSubscription::create(array_merge([
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
        'status' => AccountSubscription::STATUS_ATIVA,
        'ciclo' => 'mensal',
        'creditos_inclusos_saldo' => 0,
        // ciclo de 30 dias, 15 restantes → fração 0,5
        'ultimo_grant_em' => now()->subDays(15),
        'proximo_grant_em' => now()->addDays(15),
    ], $extra));
}

it('compra de assento extra debita pró-rata do saldo e incrementa assentos_extras', function () {
    $user = User::factory()->create();
    assinaturaAtivaAddon($user); // essencial: assento extra R$ 39,00
    app(SaldoService::class)->add($user, 100, 'manual_add');

    app(AddonService::class)->definirAssentosExtras($user, 1);

    $sub = $user->subscription()->first();
    expect($sub->assentos_extras)->toBe(1);
    // 39,00 × 0,5 = 19,50 (tolerância de segundos na fração)
    $tx = SaldoTransacao::where('user_id', $user->id)->where('type', 'addon_purchase')->first();
    expect($tx)->not->toBeNull();
    expect(abs((float) $tx->amount) - 19.50)->toBeLessThan(0.10);
    expect((float) $user->fresh()->credits)->toBeLessThan(100.0);
});

it('saldo insuficiente barra a compra sem mudar nada', function () {
    $user = User::factory()->create();
    assinaturaAtivaAddon($user);
    app(SaldoService::class)->add($user, 1, 'manual_add');

    expect(fn () => app(AddonService::class)->definirAssentosExtras($user, 2))
        ->toThrow(RuntimeException::class);

    expect($user->subscription()->first()->assentos_extras)->toBe(0)
        ->and((float) $user->fresh()->credits)->toBe(1.0);
});

it('sem assinatura ativa não compra add-on', function () {
    $user = User::factory()->create();
    app(SaldoService::class)->add($user, 100, 'manual_add');

    expect(fn () => app(AddonService::class)->definirAssentosExtras($user, 1))
        ->toThrow(RuntimeException::class);
});

it('redução de assentos abaixo dos ocupados é barrada; redução válida não estorna', function () {
    $user = User::factory()->create();
    assinaturaAtivaAddon($user, 'essencial', ['assentos_extras' => 2]); // 2 inclusos + 2 extras = 4
    app(SaldoService::class)->add($user, 100, 'manual_add');

    $account = Account::create(['owner_user_id' => $user->id, 'nome' => 'Conta']);
    AccountMember::create(['account_id' => $account->id, 'user_id' => $user->id, 'papel' => AccountMember::PAPEL_OWNER, 'permissoes' => AccountMember::permissoesPadrao(AccountMember::PAPEL_OWNER), 'entrou_em' => now()]);
    foreach (range(1, 3) as $i) {
        $m = User::factory()->create();
        AccountMember::create(['account_id' => $account->id, 'user_id' => $m->id, 'papel' => AccountMember::PAPEL_LEITURA, 'permissoes' => AccountMember::permissoesPadrao(AccountMember::PAPEL_LEITURA), 'entrou_em' => now()]);
    }
    // 4 membros ocupando; capacidade com 1 extra = 3 → barrado
    expect(fn () => app(AddonService::class)->definirAssentosExtras($user, 1))
        ->toThrow(RuntimeException::class);

    // reduzir dentro do limite (4 membros, 2 inclusos + 2 extras): manter 2 é no-op; sem estorno
    $saldoAntes = (float) $user->fresh()->credits;
    app(AddonService::class)->definirAssentosExtras($user, 2);
    expect((float) $user->fresh()->credits)->toBe($saldoAntes)
        ->and(SaldoTransacao::where('user_id', $user->id)->where('type', 'addon_purchase')->count())->toBe(0);
});

it('renovação debita a mensalidade dos add-ons junto do grant', function () {
    $user = User::factory()->create();
    $sub = assinaturaAtivaAddon($user, 'essencial', [
        'assentos_extras' => 2,
        'espaco_extra_pacotes' => 1,
        'proximo_grant_em' => now()->subMinute(),
    ]);
    app(SaldoService::class)->add($user, 200, 'manual_add');

    app(ConcederSaldoService::class)->conceder($sub);

    $user->refresh();
    // essencial concede 35 → 200 + 35 − (2×39) − (1×19,90) = 137,10
    expect((float) $user->credits)->toBe(137.10);
    expect(SaldoTransacao::where('user_id', $user->id)->where('type', 'addon_renewal')->count())->toBe(2);
});

it('renovação sem saldo zera o add-on, preserva membros e notifica', function () {
    Notification::fake();
    $user = User::factory()->create();
    $sub = assinaturaAtivaAddon($user, 'essencial', [
        'assentos_extras' => 2,
        'proximo_grant_em' => now()->subMinute(),
    ]);
    // saldo 0 + grant de 35 do essencial não cobre 2×39 = 78

    app(ConcederSaldoService::class)->conceder($sub);

    $sub->refresh();
    expect($sub->assentos_extras)->toBe(0);
    Notification::assertSentTo($user, AddonSuspensoNotification::class);
});

it('preço zero não debita na renovação (cortesia por plano)', function () {
    $user = User::factory()->create();
    SubscriptionPlan::where('codigo', 'essencial')->update(['preco_assento_extra_centavos' => 0]);
    $sub = assinaturaAtivaAddon($user, 'essencial', [
        'assentos_extras' => 1,
        'proximo_grant_em' => now()->subMinute(),
    ]);

    app(ConcederSaldoService::class)->conceder($sub);

    expect($sub->fresh()->assentos_extras)->toBe(1)
        ->and(SaldoTransacao::where('user_id', $user->id)->where('type', 'addon_renewal')->count())->toBe(0);
});

it('renovação sem add-ons é idêntica ao comportamento atual', function () {
    $user = User::factory()->create();
    $sub = assinaturaAtivaAddon($user, 'essencial', ['proximo_grant_em' => now()->subMinute()]);

    app(ConcederSaldoService::class)->conceder($sub);

    expect((float) $user->fresh()->credits)->toBe(35.0)
        ->and(SaldoTransacao::where('user_id', $user->id)->where('type', 'addon_renewal')->count())->toBe(0);
});
