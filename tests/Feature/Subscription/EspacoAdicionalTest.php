<?php

use App\Models\AccountMember;
use App\Models\AccountSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Accounts\AccountService;
use App\Services\Arquivos\ArquivoUsuarioService;
use App\Services\SaldoService;
use App\Services\Subscription\AddonService;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SubscriptionPlanSeeder::class);
});

function ownerComAssinatura(string $plano = 'essencial'): User
{
    $owner = User::factory()->create(['empresa' => 'Conta Espaço', 'cnpj' => '11222333000181']);
    app(AccountService::class)->ensureForOwner($owner);
    $plan = SubscriptionPlan::where('codigo', $plano)->firstOrFail();
    AccountSubscription::create([
        'user_id' => $owner->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'ativa',
        'ciclo' => 'mensal',
    ]);

    return $owner;
}

it('quota soma os pacotes de espaço extra contratados', function () {
    $owner = ownerComAssinatura('essencial'); // base 2 GB (essencial capability)
    $base = app(ArquivoUsuarioService::class)->quotaBytes($owner);

    $owner->subscription()->first()->update(['espaco_extra_pacotes' => 2]);

    $comExtra = app(ArquivoUsuarioService::class)->quotaBytes($owner->fresh());
    $pacoteMb = app(AddonService::class)->pacoteEspacoMb();

    expect($comExtra - $base)->toBe(2 * $pacoteMb * 1024 * 1024);
});

it('plano ilimitado ignora pacotes de espaço (quota permanece null)', function () {
    $owner = ownerComAssinatura('essencial');
    // simula plano ilimitado
    $plan = $owner->subscription()->first()->plan;
    $caps = $plan->capabilities;
    $caps['armazenamento_mb'] = null;
    $plan->update(['capabilities' => $caps]);
    $owner->subscription()->first()->update(['espaco_extra_pacotes' => 5]);

    expect(app(ArquivoUsuarioService::class)->quotaBytes($owner->fresh()))->toBeNull();
});

it('owner contrata espaço: debita saldo e redireciona pro billing', function () {
    $owner = ownerComAssinatura('essencial'); // pacote R$ 19,90
    app(SaldoService::class)->add($owner, 100, 'manual_add');

    $this->actingAs($owner)
        ->post(route('app.arquivos.espaco'), ['espaco_extra_pacotes' => 1])
        ->assertRedirect(route('app.saldo'));

    expect($owner->subscription()->first()->espaco_extra_pacotes)->toBe(1)
        ->and((float) $owner->fresh()->credits)->toBe(80.10); // 100 − 19,90 (fração 1.0)
});

it('espaço sem saldo volta com erro sem mudar nada', function () {
    $owner = ownerComAssinatura('essencial');
    app(SaldoService::class)->add($owner, 5, 'manual_add');

    $this->actingAs($owner)
        ->post(route('app.arquivos.espaco'), ['espaco_extra_pacotes' => 1])
        ->assertRedirect(route('app.arquivos.index'))
        ->assertSessionHasErrors('espaco_extra_pacotes');

    expect($owner->subscription()->first()->espaco_extra_pacotes)->toBe(0)
        ->and((float) $owner->fresh()->credits)->toBe(5.0);
});

it('membro comum não pode contratar espaço (owner-only)', function () {
    $owner = ownerComAssinatura('essencial');
    $account = $owner->ownedAccount()->first();
    $member = User::factory()->create();
    AccountMember::create([
        'account_id' => $account->id, 'user_id' => $member->id, 'papel' => 'admin',
        'permissoes' => AccountMember::permissoesPadrao('admin'), 'entrou_em' => now(),
    ]);

    $this->actingAs($member)
        ->post(route('app.arquivos.espaco'), ['espaco_extra_pacotes' => 1])
        ->assertForbidden();
});

it('a tela de arquivos mostra o botão de contratar espaço pro owner', function () {
    $owner = ownerComAssinatura('essencial');

    $this->actingAs($owner)->get(route('app.arquivos.index'))
        ->assertOk()
        ->assertSee('Contratar espaço')
        ->assertSee('modal-espaco');
});
