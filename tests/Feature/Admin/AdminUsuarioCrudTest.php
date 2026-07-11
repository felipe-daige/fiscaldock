<?php

use App\Models\AccountSubscription;
use App\Models\AdminActionLog;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Entitlements\EntitlementService;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SubscriptionPlanSeeder::class);
    $this->admin = User::factory()->create(['is_admin' => true]);
});

it('admin cria usuário manualmente com trilha de auditoria', function () {
    actingAs($this->admin)
        ->post(route('app.admin.usuarios.store'), [
            'name' => 'Maria',
            'sobrenome' => 'Operacao',
            'email' => 'maria.ops@example.com',
            'telefone' => '(11) 98888-7777',
            'password' => 'senha-segura',
            'credits' => 5, // R$ — controller converte pra 25 cr
            'empresa' => 'Maria Contabil',
            'email_verified' => '1',
            'motivo' => 'criação solicitada pelo suporte',
        ])
        ->assertRedirect();

    $usuario = User::where('email', 'maria.ops@example.com')->firstOrFail();

    expect($usuario->name)->toBe('Maria');
    expect($usuario->telefone)->toBe('11988887777');
    expect((int) $usuario->credits)->toBe(25);
    expect($usuario->terms_version)->toBeNull();
    $this->assertDatabaseHas('admin_action_logs', [
        'target_user_id' => $usuario->id,
        'acao' => 'usuario_criar',
    ]);
});

it('admin edita cadastro do usuário e registra auditoria', function () {
    $alvo = User::factory()->create(['email' => 'antes@example.com', 'empresa' => 'Antes']);

    actingAs($this->admin)
        ->put(route('app.admin.usuarios.update', $alvo->id), [
            'name' => 'Nome Novo',
            'sobrenome' => 'Sobrenome Novo',
            'email' => 'depois@example.com',
            'telefone' => '(21) 97777-6666',
            'empresa' => 'Depois',
            'cargo' => 'Controller',
            'email_verified' => '1',
            'marketing_opt_in' => '1',
            'alertas_operacionais' => '1',
            'alertas_monitoramento' => '1',
            'resumo_periodico' => '0',
            'motivo' => 'correção cadastral',
        ])
        ->assertRedirect(route('app.admin.usuarios.show', $alvo->id));

    $alvo->refresh();
    expect($alvo->email)->toBe('depois@example.com');
    expect($alvo->empresa)->toBe('Depois');
    expect($alvo->marketing_opt_in)->toBeTrue();
    expect($alvo->resumo_periodico)->toBeFalse();
    $this->assertDatabaseHas('admin_action_logs', [
        'target_user_id' => $alvo->id,
        'acao' => 'usuario_editar',
    ]);
});

it('edição de usuário bloqueia auto-rebaixamento e auto-bloqueio', function () {
    actingAs($this->admin)
        ->put(route('app.admin.usuarios.update', $this->admin->id), [
            'name' => 'Admin',
            'sobrenome' => 'FiscalDock',
            'email' => $this->admin->email,
            'telefone' => $this->admin->telefone,
            'is_admin' => '0',
            'bloqueado' => '1',
            'alertas_operacionais' => '1',
            'alertas_monitoramento' => '1',
            'resumo_periodico' => '1',
            'motivo' => 'tentativa inválida',
        ])
        ->assertSessionHasErrors('motivo');

    expect($this->admin->fresh()->is_admin)->toBeTrue();
    expect($this->admin->fresh()->bloqueado_em)->toBeNull();
});

it('admin altera plano local do usuário e o entitlement passa a usar o plano ativo', function () {
    $alvo = User::factory()->create();
    $profissional = SubscriptionPlan::where('codigo', 'profissional')->firstOrFail();

    expect(app(EntitlementService::class)->planFor($alvo)->codigo)->toBe('free');

    actingAs($this->admin)
        ->from(route('app.admin.usuarios.index'))
        ->post(route('app.admin.usuarios.assinatura', $alvo->id), [
            'subscription_plan_id' => $profissional->id,
            'status' => 'ativa',
            'ciclo' => 'mensal',
            // Digitados em R$ — o controller converte pra unidade do ledger (÷ 0,20).
            'creditos_inclusos_saldo' => '220.00',
            'limite_consumo_automatico' => '100.00',
            'assentos_extras' => '1.00',
            'motivo' => 'upgrade manual',
        ])
        ->assertRedirect(route('app.admin.usuarios.index'));

    expect(AccountSubscription::where('user_id', $alvo->id)->first()->plan->codigo)->toBe('profissional');
    expect(AccountSubscription::where('user_id', $alvo->id)->first()->creditos_inclusos_saldo)->toBe(1100);
    expect(app(EntitlementService::class)->planFor($alvo->fresh())->codigo)->toBe('profissional');
    $this->assertDatabaseHas('admin_action_logs', ['target_user_id' => $alvo->id, 'acao' => 'assinatura_editar']);
});

it('assinatura cancelada ou removida pelo admin volta o usuário para Free', function () {
    $alvo = User::factory()->create();
    $essencial = SubscriptionPlan::where('codigo', 'essencial')->firstOrFail();
    AccountSubscription::create([
        'user_id' => $alvo->id,
        'subscription_plan_id' => $essencial->id,
        'status' => 'ativa',
        'ciclo' => 'mensal',
    ]);

    actingAs($this->admin)
        ->post(route('app.admin.usuarios.assinatura', $alvo->id), [
            'subscription_plan_id' => $essencial->id,
            'status' => 'cancelada',
            'ciclo' => 'mensal',
            'motivo' => 'cancelamento manual',
        ])
        ->assertRedirect();

    expect(app(EntitlementService::class)->planFor($alvo->fresh())->codigo)->toBe('free');

    actingAs($this->admin)
        ->post(route('app.admin.usuarios.assinatura', $alvo->id), [
            'subscription_plan_id' => '',
            'motivo' => 'remover linha local',
        ])
        ->assertRedirect();

    expect(AccountSubscription::where('user_id', $alvo->id)->exists())->toBeFalse();
    $this->assertDatabaseHas('admin_action_logs', ['target_user_id' => $alvo->id, 'acao' => 'assinatura_remover']);
});

it('admin ajusta trial do usuário', function () {
    $alvo = User::factory()->create();

    actingAs($this->admin)
        ->post(route('app.admin.usuarios.trial', $alvo->id), [
            'trial_used' => '1',
            'trial_started_at' => now()->format('Y-m-d H:i:s'),
            'trial_expires_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'trial_credits_granted' => 60,
            'trial_credits_remaining' => 45,
            'trial_credits_expired' => 0,
            'trial_source' => 'admin',
            'motivo' => 'reconcessão operacional',
        ])
        ->assertRedirect();

    $alvo->refresh();
    expect($alvo->trial_used)->toBeTrue();
    expect($alvo->trial_credits_remaining)->toBe(45);
    expect($alvo->hasActiveTrial())->toBeTrue();
    $this->assertDatabaseHas('admin_action_logs', ['target_user_id' => $alvo->id, 'acao' => 'trial_editar']);
});

it('admin anonimiza usuário comum preservando linha e cancelando assinatura local', function () {
    $alvo = User::factory()->create([
        'email' => 'titular@example.com',
        'empresa' => 'Empresa PII',
        'cnpj' => '11222333000181',
    ]);
    $plano = SubscriptionPlan::where('codigo', 'essencial')->firstOrFail();
    AccountSubscription::create([
        'user_id' => $alvo->id,
        'subscription_plan_id' => $plano->id,
        'status' => 'ativa',
        'ciclo' => 'mensal',
    ]);

    actingAs($this->admin)
        ->delete(route('app.admin.usuarios.destroy', $alvo->id), [
            'motivo' => 'pedido LGPD processado pelo suporte',
            'confirmacao' => 'ANONIMIZAR',
        ])
        ->assertRedirect(route('app.admin.usuarios.show', $alvo->id));

    $alvo->refresh();
    expect($alvo->email)->toBe('anon-'.$alvo->id.'@anonimizado.invalid');
    expect($alvo->empresa)->toBeNull();
    expect($alvo->cnpj)->toBeNull();
    expect($alvo->bloqueado_em)->not->toBeNull();
    expect($alvo->anonimizado_em)->not->toBeNull();
    expect($alvo->subscription->status)->toBe('cancelada');
    $this->assertDatabaseHas('admin_action_logs', ['target_user_id' => $alvo->id, 'acao' => 'usuario_anonimizar']);
});

it('anonimização rejeita self e outro admin', function () {
    $outroAdmin = User::factory()->create(['is_admin' => true]);

    actingAs($this->admin)
        ->delete(route('app.admin.usuarios.destroy', $this->admin->id), [
            'motivo' => 'self',
            'confirmacao' => 'ANONIMIZAR',
        ])
        ->assertSessionHasErrors('confirmacao');

    actingAs($this->admin)
        ->delete(route('app.admin.usuarios.destroy', $outroAdmin->id), [
            'motivo' => 'admin',
            'confirmacao' => 'ANONIMIZAR',
        ])
        ->assertSessionHasErrors('confirmacao');

    expect(AdminActionLog::where('acao', 'usuario_anonimizar')->count())->toBe(0);
});
