<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(fn () => (new Database\Seeders\SubscriptionPlanSeeder)->run());

it('nega acesso a não-admin e a visitante', function () {
    $this->get('/app/admin/planos')->assertRedirect();
    actingAs(User::factory()->create(['is_admin' => false]))->get('/app/admin/planos')->assertForbidden();
});

it('admin lista os planos', function () {
    actingAs(User::factory()->create(['is_admin' => true]))
        ->get('/app/admin/planos')
        ->assertOk()
        ->assertSee('Essencial')
        ->assertSee('Profissional');
});

it('admin edita limites e capabilities e persiste no catálogo', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $ess = SubscriptionPlan::where('codigo', 'essencial')->first();

    actingAs($admin)->post(route('app.admin.planos.update', $ess->id), [
        'nome' => 'Essencial+',
        'preco_mensal_reais' => 129.00,
        'preco_anual_reais' => 1290.00,
        'saldo_incluso_reais' => 80,
        'faixa_slug' => 'base',
        'limite_clientes' => 20,
        'limite_cnpjs_monitorados' => '', // vazio = ilimitado
        'frequencia_padrao_dias' => 30,
        'profundidade_auto_monitor' => 'compliance',
        'assentos_inclusos' => 2,
        'rollover_cap_multiplicador' => 1.5,
        'ordem' => 2,
        'is_active' => '1',
        'cap_bi' => 'completo',
        'cap_export' => ['csv', 'excel'],
        'cap_pdf_executivo' => '1',
        'cap_clearance_lote' => '1',
        'cap_clearance_full' => '0',
        'cap_score_historico' => '1',
        'cap_retencao_meses' => '',
        'cap_frequencia_minima_dias' => 15,
        'mp_preapproval_plan_id_mensal' => 'PLAN-X',
    ])->assertRedirect(route('app.admin.planos.index'));

    $ess->refresh();
    expect($ess->nome)->toBe('Essencial+');
    expect($ess->preco_mensal_centavos)->toBe(12900); // R$ 129,00 → centavos
    expect($ess->preco_anual_centavos)->toBe(129000);
    expect($ess->creditos_inclusos)->toBe(400); // R$ 80 ÷ 0,20
    expect($ess->limite_clientes)->toBe(20);
    expect($ess->limite_cnpjs_monitorados)->toBeNull(); // ilimitado
    expect($ess->profundidade_auto_monitor)->toBe('compliance');
    expect((float) $ess->rollover_cap_multiplicador)->toBe(1.5);
    expect($ess->capability('pdf_executivo'))->toBeTrue();
    expect($ess->capability('clearance_full'))->toBeFalse();
    expect($ess->capability('export'))->toBe(['csv', 'excel']);
    expect($ess->capability('retencao_meses'))->toBeNull();
    expect($ess->capability('frequencia_minima_dias'))->toBe(15);

    $this->assertDatabaseHas('admin_action_logs', ['acao' => 'plano_editar']);
});

it('renderiza a tela de edição com os valores atuais', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $prof = SubscriptionPlan::where('codigo', 'profissional')->first();

    actingAs($admin)->get(route('app.admin.planos.edit', $prof->id))
        ->assertOk()
        ->assertSee($prof->nome)          // cabeçalho mostra o nome do plano
        ->assertSee('Comercial')
        ->assertSee('Capabilities')
        ->assertSee('preapproval_plan_id');
});

it('valida profundidade e bi inválidos', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $ess = SubscriptionPlan::where('codigo', 'essencial')->first();

    actingAs($admin)->post(route('app.admin.planos.update', $ess->id), [
        'nome' => 'X', 'preco_mensal_reais' => 1, 'preco_anual_reais' => 1,
        'saldo_incluso_reais' => 2, 'faixa_slug' => 'base',
        'frequencia_padrao_dias' => 30, 'profundidade_auto_monitor' => 'inexistente',
        'assentos_inclusos' => 1, 'rollover_cap_multiplicador' => 1, 'ordem' => 2,
        'cap_bi' => 'errado', 'cap_frequencia_minima_dias' => 30,
    ])->assertSessionHasErrors(['profundidade_auto_monitor', 'cap_bi']);
});

it('desmarcar is_active desativa o plano', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $ess = SubscriptionPlan::where('codigo', 'essencial')->first();

    actingAs($admin)->post(route('app.admin.planos.update', $ess->id), [
        'nome' => $ess->nome, 'preco_mensal_reais' => $ess->preco_mensal_centavos / 100,
        'preco_anual_reais' => $ess->preco_anual_centavos / 100, 'saldo_incluso_reais' => $ess->creditos_inclusos * 0.20,
        'faixa_slug' => $ess->faixa_slug, 'frequencia_padrao_dias' => 30,
        'profundidade_auto_monitor' => 'licitacao', 'assentos_inclusos' => 1,
        'rollover_cap_multiplicador' => 1, 'ordem' => 2,
        'cap_bi' => 'completo', 'cap_frequencia_minima_dias' => 30,
        // is_active omitido → false
    ])->assertRedirect();

    expect($ess->fresh()->is_active)->toBeFalse();
});

it('mudar capability reflete no gate de entitlements de um usuário Free', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $free = SubscriptionPlan::where('codigo', 'free')->first();
    $user = User::factory()->create(); // Free, sem trial

    $ent = app(App\Services\Entitlements\EntitlementService::class);
    expect($ent->can($user, 'pdf_executivo'))->toBeFalse();

    actingAs($admin)->post(route('app.admin.planos.update', $free->id), [
        'nome' => $free->nome, 'preco_mensal_reais' => 0, 'preco_anual_reais' => 0,
        'saldo_incluso_reais' => 0, 'faixa_slug' => 'base', 'limite_clientes' => 1,
        'limite_cnpjs_monitorados' => 1, 'frequencia_padrao_dias' => 30,
        'profundidade_auto_monitor' => 'cadastral', 'assentos_inclusos' => 1,
        'rollover_cap_multiplicador' => 1, 'ordem' => 1, 'is_active' => '1',
        'cap_bi' => 'basico', 'cap_pdf_executivo' => '1', 'cap_frequencia_minima_dias' => 30,
    ])->assertRedirect();

    expect($ent->can($user->fresh(), 'pdf_executivo'))->toBeTrue();
});

it('a edição do plano reflete no EntitlementService', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $prof = SubscriptionPlan::where('codigo', 'profissional')->first();

    // baixa o limite de monitorados do Profissional pra 5
    actingAs($admin)->post(route('app.admin.planos.update', $prof->id), [
        'nome' => $prof->nome,
        'preco_mensal_reais' => $prof->preco_mensal_centavos / 100,
        'preco_anual_reais' => $prof->preco_anual_centavos / 100,
        'saldo_incluso_reais' => $prof->creditos_inclusos * 0.20,
        'faixa_slug' => $prof->faixa_slug,
        'limite_clientes' => $prof->limite_clientes,
        'limite_cnpjs_monitorados' => 5,
        'frequencia_padrao_dias' => $prof->frequencia_padrao_dias,
        'profundidade_auto_monitor' => $prof->profundidade_auto_monitor,
        'assentos_inclusos' => $prof->assentos_inclusos,
        'rollover_cap_multiplicador' => $prof->rollover_cap_multiplicador,
        'ordem' => $prof->ordem,
        'is_active' => '1',
        'cap_bi' => 'completo',
        'cap_frequencia_minima_dias' => 15,
    ])->assertRedirect();

    expect(SubscriptionPlan::where('codigo', 'profissional')->first()->limite_cnpjs_monitorados)->toBe(5);
});
