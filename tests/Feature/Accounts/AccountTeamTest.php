<?php

use App\Models\Account;
use App\Models\AccountInvitation;
use App\Models\AccountMember;
use App\Models\AccountSubscription;
use App\Models\Alerta;
use App\Models\AlertaAuditoria;
use App\Models\Cliente;
use App\Models\ConsentLog;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\AccountInvitationNotification;
use App\Services\Accounts\AccountService;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SubscriptionPlanSeeder::class);
});

function accountOwnerWithPlan(string $plan = 'profissional'): array
{
    $owner = User::factory()->create(['empresa' => 'Conta Teste', 'cnpj' => '11222333000181']);
    $membership = app(AccountService::class)->ensureForOwner($owner);
    $subscriptionPlan = SubscriptionPlan::where('codigo', $plan)->firstOrFail();
    AccountSubscription::create([
        'user_id' => $owner->id,
        'subscription_plan_id' => $subscriptionPlan->id,
        'status' => 'ativa',
        'ciclo' => 'mensal',
    ]);

    return [$owner, $membership->account];
}

it('cria o schema multiusuario e provisiona owner idempotentemente', function () {
    expect(Schema::hasTable('accounts'))->toBeTrue()
        ->and(Schema::hasTable('account_members'))->toBeTrue()
        ->and(Schema::hasTable('account_invitations'))->toBeTrue()
        ->and(Schema::hasTable('account_activity_logs'))->toBeTrue();

    $user = User::factory()->create(['empresa' => 'ACME']);
    $first = app(AccountService::class)->ensureForOwner($user);
    $second = app(AccountService::class)->ensureForOwner($user);

    expect($first->id)->toBe($second->id)
        ->and($first->papel)->toBe(AccountMember::PAPEL_OWNER)
        ->and($first->account->owner_user_id)->toBe($user->id)
        ->and(AccountMember::where('user_id', $user->id)->count())->toBe(1);
});

it('instalador é idempotente e faz backfill dos logins existentes', function () {
    User::factory()->count(2)->create();

    $this->artisan('accounts:instalar')->assertSuccessful();
    $this->artisan('accounts:instalar')->assertSuccessful();

    expect(Account::count())->toBe(2)
        ->and(AccountMember::count())->toBe(2)
        ->and(AccountMember::where('papel', AccountMember::PAPEL_OWNER)->count())->toBe(2);
});

it('contabiliza membros e convites pendentes nos assentos do plano', function () {
    Notification::fake();
    [$owner, $account] = accountOwnerWithPlan('profissional'); // 3 assentos

    $this->actingAs($owner)->post(route('app.equipe.convites.criar'), [
        'email' => 'colega@acme.test',
        'papel' => 'operador',
        'permissoes' => array_fill_keys(AccountMember::MODULOS, 1),
    ])->assertRedirect();

    expect($account->invitations()->count())->toBe(1)
        ->and(app(AccountService::class)->seatsIncluded($account))->toBe(3)
        ->and(app(AccountService::class)->seatsUsed($account))->toBe(2);

    Notification::assertSentOnDemand(AccountInvitationNotification::class);
});

it('entrega presets distintos por papel para a tela de equipe', function () {
    [$owner] = accountOwnerWithPlan('profissional');

    $response = $this->actingAs($owner)->get(route('app.equipe.index'))->assertOk();
    preg_match(
        '/<script type="application\/json" id="team-role-presets">(.*?)<\/script>/s',
        $response->getContent(),
        $matches,
    );
    $presets = json_decode($matches[1] ?? '', true, flags: JSON_THROW_ON_ERROR);

    expect($presets['admin'])->toBe(array_fill_keys(AccountMember::MODULOS, true))
        ->and($presets['operador'])->toBe([
            'painel' => true,
            'clientes' => true,
            'documentos' => true,
            'consultas' => true,
            'relatorios' => false,
        ])
        ->and($presets['leitura'])->toBe([
            'painel' => true,
            'clientes' => true,
            'documentos' => true,
            'consultas' => false,
            'relatorios' => true,
        ]);

    $response->assertSee('data-team-permissions-form', false)
        ->assertSee('data-team-role', false)
        ->assertSee('data-team-permission="consultas"', false)
        ->assertSee('<option value="operador" selected', false);
});

it('reserva ao dono a concessão do papel de administrador', function () {
    [$owner, $account] = accountOwnerWithPlan('profissional');
    $admin = User::factory()->create();
    AccountMember::create([
        'account_id' => $account->id,
        'user_id' => $admin->id,
        'papel' => AccountMember::PAPEL_ADMIN,
        'permissoes' => AccountMember::permissoesPadrao(AccountMember::PAPEL_ADMIN),
        'entrou_em' => now(),
    ]);
    $operator = User::factory()->create();
    $operatorMembership = AccountMember::create([
        'account_id' => $account->id,
        'user_id' => $operator->id,
        'papel' => AccountMember::PAPEL_OPERADOR,
        'permissoes' => AccountMember::permissoesPadrao(AccountMember::PAPEL_OPERADOR),
        'entrou_em' => now(),
    ]);

    $this->actingAs($admin)->get(route('app.equipe.index'))
        ->assertOk()
        ->assertDontSee('<option value="admin"', false);

    $this->actingAs($admin)->patch(route('app.equipe.membros.atualizar', $operatorMembership->id), [
        'papel' => AccountMember::PAPEL_ADMIN,
        'permissoes' => array_fill_keys(AccountMember::MODULOS, 1),
    ])->assertSessionHasErrors('papel');

    expect($operatorMembership->fresh()->papel)->toBe(AccountMember::PAPEL_OPERADOR);
});

it('não permite convite quando todos os assentos estão ocupados', function () {
    Notification::fake();
    [$owner, $account] = accountOwnerWithPlan('essencial'); // 2 assentos: owner + 1 membro
    $member = User::factory()->create();
    AccountMember::create([
        'account_id' => $account->id,
        'user_id' => $member->id,
        'papel' => 'leitura',
        'permissoes' => AccountMember::permissoesPadrao('leitura'),
        'entrou_em' => now(),
    ]);

    $this->actingAs($owner)->post(route('app.equipe.convites.criar'), [
        'email' => 'sem-vaga@acme.test',
        'papel' => 'leitura',
        'permissoes' => ['painel' => 1],
    ])->assertSessionHasErrors('email');

    expect($account->invitations()->count())->toBe(0);
});

it('não mantém assentos extras de uma assinatura cancelada', function () {
    [$owner, $account] = accountOwnerWithPlan('profissional');
    $owner->subscription()->update([
        'status' => AccountSubscription::STATUS_CANCELADA,
        'assentos_extras' => 5,
    ]);
    $owner->unsetRelation('subscription');

    expect(app(AccountService::class)->seatsIncluded($account->fresh('owner.subscription')))->toBe(1);
});

it('permite reenviar um convite sem consumir outro assento', function () {
    Notification::fake();
    [$owner, $account] = accountOwnerWithPlan('profissional');

    $payload = [
        'email' => 'reenviar@acme.test',
        'papel' => 'operador',
        'permissoes' => ['painel' => 1],
    ];

    $this->actingAs($owner)->post(route('app.equipe.convites.criar'), $payload)->assertRedirect();

    $member = User::factory()->create();
    AccountMember::create([
        'account_id' => $account->id,
        'user_id' => $member->id,
        'papel' => 'operador',
        'permissoes' => AccountMember::permissoesPadrao('operador'),
        'entrou_em' => now(),
    ]);

    expect(app(AccountService::class)->seatsUsed($account))->toBe(3);

    $this->actingAs($owner)->post(route('app.equipe.convites.criar'), $payload)
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($account->invitations()->whereNull('revogado_em')->count())->toBe(1)
        ->and(app(AccountService::class)->seatsUsed($account))->toBe(3);
});

it('aceita convite com cadastro individual e vincula o login à conta', function () {
    [$owner, $account] = accountOwnerWithPlan('profissional');
    $token = 'token-seguro-de-teste';

    $invitation = AccountInvitation::create([
        'account_id' => $account->id,
        'email' => 'novo-membro@acme.test',
        'papel' => 'operador',
        'permissoes' => AccountMember::permissoesPadrao('operador'),
        'token_hash' => hash('sha256', $token),
        'convidado_por' => $owner->id,
        'expira_em' => now()->addDay(),
    ]);

    $this->post(route('equipe.convite.confirmar', ['token' => $token]), [
        'name' => 'Novo',
        'sobrenome' => 'Membro',
        'telefone' => '(11) 99999-9999',
        'password' => 'senha123',
        'password_confirmation' => 'senha123',
        'terms_aceitos' => 1,
    ])->assertRedirect('/app/dashboard');

    $user = User::where('email', 'novo-membro@acme.test')->firstOrFail();
    expect($user->accountMembership->account_id)->toBe($account->id)
        ->and($user->accountMembership->papel)->toBe('operador')
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($invitation->fresh()->aceito_em)->not->toBeNull()
        ->and(ConsentLog::where('user_id', $user->id)->where('tipo', ConsentLog::TIPO_TERMOS)->count())->toBe(1)
        ->and(ConsentLog::where('user_id', $user->id)->where('tipo', ConsentLog::TIPO_PRIVACIDADE)->count())->toBe(1);
});

it('não deixa usuário órfão quando o assento deixa de estar disponível', function () {
    [$owner, $account] = accountOwnerWithPlan('essencial');
    $token = 'token-sem-assento';

    AccountInvitation::create([
        'account_id' => $account->id,
        'email' => 'sem-assento@acme.test',
        'papel' => 'leitura',
        'permissoes' => AccountMember::permissoesPadrao('leitura'),
        'token_hash' => hash('sha256', $token),
        'convidado_por' => $owner->id,
        'expira_em' => now()->addDay(),
    ]);

    // Simula a vaga ter sido ocupada por outro login depois que o convite foi emitido.
    $member = User::factory()->create();
    AccountMember::create([
        'account_id' => $account->id,
        'user_id' => $member->id,
        'papel' => 'leitura',
        'permissoes' => AccountMember::permissoesPadrao('leitura'),
        'entrou_em' => now(),
    ]);

    $this->post(route('equipe.convite.confirmar', ['token' => $token]), [
        'name' => 'Sem',
        'sobrenome' => 'Assento',
        'telefone' => '(11) 98888-7777',
        'password' => 'senha123',
        'password_confirmation' => 'senha123',
        'terms_aceitos' => 1,
    ])->assertSessionHasErrors('email');

    expect(User::where('email', 'sem-assento@acme.test')->exists())->toBeFalse();
});

it('membro usa o tenant e saldo do owner sem herdar o perfil de administrador FiscalDock', function () {
    [$owner, $account] = accountOwnerWithPlan();
    $owner->update(['is_admin' => true, 'credits' => 500]);
    Cliente::create([
        'user_id' => $owner->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '33444555000191',
        'razao_social' => 'Cliente Compartilhado',
        'is_empresa_propria' => false,
    ]);

    $member = User::factory()->create(['name' => 'Colaborador']);
    AccountMember::create([
        'account_id' => $account->id,
        'user_id' => $member->id,
        'papel' => 'operador',
        'permissoes' => AccountMember::permissoesPadrao('operador'),
        'entrou_em' => now(),
    ]);

    $this->actingAs($member)->get('/app/clientes')
        ->assertOk()
        ->assertSee('Cliente Compartilhado');

    $this->actingAs($member)->get(route('app.admin.index'))->assertForbidden();
});

it('mantém privacidade e consentimentos vinculados ao ator real', function () {
    [$owner, $account] = accountOwnerWithPlan();
    $owner->update(['marketing_opt_in' => true]);
    $member = User::factory()->create([
        'email' => 'titular-membro@acme.test',
        'marketing_opt_in' => true,
    ]);
    AccountMember::create([
        'account_id' => $account->id,
        'user_id' => $member->id,
        'papel' => AccountMember::PAPEL_OPERADOR,
        'permissoes' => AccountMember::permissoesPadrao(AccountMember::PAPEL_OPERADOR),
        'entrou_em' => now(),
    ]);

    $this->actingAs($member)->get('/app/privacidade/exportar')
        ->assertOk()
        ->assertJsonPath('perfil.email', 'titular-membro@acme.test');

    $this->actingAs($member)->post('/app/privacidade/marketing/revogar')->assertRedirect();

    expect($member->fresh()->marketing_opt_in)->toBeFalse()
        ->and($owner->fresh()->marketing_opt_in)->toBeTrue();
});

it('atribui ao membro a auditoria de uma ação nos dados compartilhados', function () {
    [$owner, $account] = accountOwnerWithPlan();
    $member = User::factory()->create(['name' => 'Operador Real']);
    AccountMember::create([
        'account_id' => $account->id,
        'user_id' => $member->id,
        'papel' => AccountMember::PAPEL_OPERADOR,
        'permissoes' => AccountMember::permissoesPadrao(AccountMember::PAPEL_OPERADOR),
        'entrou_em' => now(),
    ]);
    $alerta = Alerta::create([
        'user_id' => $owner->id,
        'tipo' => 'notas_duplicadas',
        'categoria' => 'notas_fiscais',
        'severidade' => 'media',
        'titulo' => 'Alerta compartilhado',
        'descricao' => 'Teste de autoria.',
        'status' => 'ativo',
        'hash' => hash('sha256', 'alerta-compartilhado-'.$owner->id),
    ]);

    $this->actingAs($member)->postJson(route('app.alertas.status', $alerta->id), [
        'status' => 'resolvido',
    ])->assertOk();

    $auditoria = AlertaAuditoria::where('alerta_id', $alerta->id)
        ->where('acao', 'resolvido')
        ->firstOrFail();

    expect($auditoria->user_id)->toBe($member->id)
        ->and($auditoria->ator_nome)->toBe('Operador Real');
});

it('bloqueia também as subrotas do dashboard quando o painel não foi liberado', function () {
    [$owner, $account] = accountOwnerWithPlan();
    $member = User::factory()->create();
    $permissions = AccountMember::permissoesPadrao(AccountMember::PAPEL_OPERADOR);
    $permissions['painel'] = false;
    AccountMember::create([
        'account_id' => $account->id,
        'user_id' => $member->id,
        'papel' => AccountMember::PAPEL_OPERADOR,
        'permissoes' => $permissions,
        'entrou_em' => now(),
    ]);

    $this->actingAs($member)->getJson(route('app.dashboard.dados'))->assertForbidden();
});

it('aplica somente leitura e reserva cobrança exclusivamente ao owner', function () {
    [$owner, $account] = accountOwnerWithPlan();
    $member = User::factory()->create();
    AccountMember::create([
        'account_id' => $account->id,
        'user_id' => $member->id,
        'papel' => 'leitura',
        'permissoes' => AccountMember::permissoesPadrao('leitura'),
        'entrou_em' => now(),
    ]);

    $this->actingAs($member)->postJson('/app/cliente/novo', [
        'tipo_pessoa' => 'PJ',
        'documento' => '55666777000191',
        'razao_social' => 'Não pode criar',
    ])->assertForbidden();

    $this->actingAs($member)->get('/app/planos')->assertForbidden();
});

it('suspende colaboradores quando a conta do owner está bloqueada', function () {
    [$owner, $account] = accountOwnerWithPlan();
    $owner->update(['bloqueado_em' => now()]);

    $member = User::factory()->create();
    AccountMember::create([
        'account_id' => $account->id,
        'user_id' => $member->id,
        'papel' => 'operador',
        'permissoes' => AccountMember::permissoesPadrao('operador'),
        'entrou_em' => now(),
    ]);

    $this->actingAs($member)->get('/app/clientes')->assertRedirect(route('login'));
    expect(auth()->check())->toBeFalse();
});

it('owner contrata assento extra: debita pró-rata do saldo e redireciona pro billing', function () {
    [$owner, $account] = accountOwnerWithPlan('essencial'); // assento extra R$ 39,00
    app(App\Services\SaldoService::class)->add($owner, 100, 'manual_add');

    $this->actingAs($owner)
        ->post(route('app.equipe.assentos'), ['assentos_extras' => 1])
        ->assertRedirect(route('app.saldo'));

    $sub = $owner->subscription()->first();
    expect($sub->assentos_extras)->toBe(1);
    // sem âncora de ciclo → fração 1.0 → cobra mês cheio (39,00)
    expect((float) $owner->fresh()->credits)->toBe(61.0);
});

it('assento extra sem saldo volta com erro e não altera a conta', function () {
    [$owner, $account] = accountOwnerWithPlan('essencial');
    app(App\Services\SaldoService::class)->add($owner, 10, 'manual_add');

    $this->actingAs($owner)
        ->post(route('app.equipe.assentos'), ['assentos_extras' => 1])
        ->assertRedirect(route('app.equipe.index'))
        ->assertSessionHasErrors('assentos_extras');

    expect($owner->subscription()->first()->assentos_extras)->toBe(0)
        ->and((float) $owner->fresh()->credits)->toBe(10.0);
});

it('membro comum não pode contratar assento (owner-only)', function () {
    [$owner, $account] = accountOwnerWithPlan('essencial');
    $member = User::factory()->create();
    AccountMember::create([
        'account_id' => $account->id, 'user_id' => $member->id, 'papel' => 'admin',
        'permissoes' => AccountMember::permissoesPadrao('admin'), 'entrou_em' => now(),
    ]);

    $this->actingAs($member)
        ->post(route('app.equipe.assentos'), ['assentos_extras' => 1])
        ->assertForbidden();
});

it('a tela de equipe renderiza o card de assentos extras pro owner', function () {
    [$owner, $account] = accountOwnerWithPlan('essencial');

    $this->actingAs($owner)->get(route('app.equipe.index'))
        ->assertOk()
        ->assertSee('Assentos extras')
        ->assertSee('modal-assentos');
});
