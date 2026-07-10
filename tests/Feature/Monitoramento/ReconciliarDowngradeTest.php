<?php

use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoPlano;
use App\Models\User;
use App\Services\Entitlements\EntitlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    (new Database\Seeders\SubscriptionPlanSeeder)->run();
    (new Database\Seeders\MonitoramentoPlanoSeeder)->run();
});

/** Cria N monitoramentos ativos (participante_id null é ok — a contagem é por user/status). */
function criarMonitorados(User $user, int $n): array
{
    $plano = MonitoramentoPlano::first();
    $ids = [];
    for ($i = 0; $i < $n; $i++) {
        $ids[] = MonitoramentoAssinatura::create([
            'user_id' => $user->id,
            'plano_id' => $plano->id,
            'status' => 'ativo',
            'frequencia_dias' => 30,
        ])->id;
    }

    return $ids;
}

it('detecta excedente quando ativos passam do cap do tier (Free = 1)', function () {
    $user = User::factory()->create(); // sem assinatura/trial → Free, cap 1
    criarMonitorados($user, 3);

    $rec = app(EntitlementService::class)->excedeLimiteMonitoramento($user);

    expect($rec)->toMatchArray(['cap' => 1, 'ocupados' => 3, 'excedente' => 2]);
});

it('reconcilia mantendo os escolhidos ativos e pausando o excedente como downgrade_auto', function () {
    $user = User::factory()->create();
    [$a1, $a2, $a3] = criarMonitorados($user, 3);
    actingAs($user);

    postJson(route('app.monitoramento.reconciliar-limite'), ['manter' => [$a1]])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('ocupados', 1);

    expect(MonitoramentoAssinatura::find($a1)->status)->toBe('ativo');
    foreach ([$a2, $a3] as $id) {
        $mon = MonitoramentoAssinatura::find($id);
        expect($mon->status)->toBe('pausado');
        expect($mon->pausada_motivo)->toBe(MonitoramentoAssinatura::MOTIVO_DOWNGRADE);
    }

    // pausados por downgrade não ocupam slot → não excede mais
    expect(app(EntitlementService::class)->excedeLimiteMonitoramento($user))->toBeNull();
    expect(app(EntitlementService::class)->cnpjsMonitoradosOcupados($user))->toBe(1);
});

it('permite trocar quais manter: reativa o escolhido e pausa o antes-ativo', function () {
    $user = User::factory()->create();
    [$a1, $a2, $a3] = criarMonitorados($user, 3);
    actingAs($user);

    // 1ª reconciliação mantém a1
    postJson(route('app.monitoramento.reconciliar-limite'), ['manter' => [$a1]])->assertOk();
    // troca: agora quero manter a2 (estava pausado por downgrade)
    postJson(route('app.monitoramento.reconciliar-limite'), ['manter' => [$a2]])->assertOk();

    expect(MonitoramentoAssinatura::find($a2)->status)->toBe('ativo');
    expect(MonitoramentoAssinatura::find($a2)->pausada_motivo)->toBeNull();
    expect(MonitoramentoAssinatura::find($a1)->status)->toBe('pausado');
    expect(MonitoramentoAssinatura::find($a1)->pausada_motivo)->toBe(MonitoramentoAssinatura::MOTIVO_DOWNGRADE);
    expect(app(EntitlementService::class)->cnpjsMonitoradosOcupados($user))->toBe(1);
});

it('manter vazio pausa todos como downgrade', function () {
    $user = User::factory()->create();
    $ids = criarMonitorados($user, 2);
    actingAs($user);

    postJson(route('app.monitoramento.reconciliar-limite'), ['manter' => []])
        ->assertOk()->assertJsonPath('ocupados', 0);

    foreach ($ids as $id) {
        expect(MonitoramentoAssinatura::find($id)->pausada_motivo)->toBe(MonitoramentoAssinatura::MOTIVO_DOWNGRADE);
    }
});

it('trial ativo não dispara reconciliação (limite ilimitado)', function () {
    $user = User::factory()->trialAtivo()->create();
    criarMonitorados($user, 5);

    expect(app(EntitlementService::class)->excedeLimiteMonitoramento($user))->toBeNull();
    expect(app(EntitlementService::class)->limiteCnpjsMonitorados($user))->toBeNull();
});

it('cancelados não contam pro cap ocupado', function () {
    $user = User::factory()->create();
    $plano = MonitoramentoPlano::first();
    MonitoramentoAssinatura::create(['user_id' => $user->id, 'plano_id' => $plano->id, 'status' => 'ativo', 'frequencia_dias' => 30]);
    MonitoramentoAssinatura::create(['user_id' => $user->id, 'plano_id' => $plano->id, 'status' => 'cancelado', 'frequencia_dias' => 30]);

    expect(app(EntitlementService::class)->cnpjsMonitoradosOcupados($user))->toBe(1);
});

it('após reconciliar, podeMonitorarMaisCnpj respeita o slot liberado', function () {
    $user = User::factory()->create(); // Free cap 1
    [$a1] = criarMonitorados($user, 3);
    actingAs($user);
    postJson(route('app.monitoramento.reconciliar-limite'), ['manter' => [$a1]])->assertOk();

    $ent = app(EntitlementService::class);
    // ocupados=1, cap=1 → não cabe mais
    expect($ent->podeMonitorarMaisCnpj($user, $ent->cnpjsMonitoradosOcupados($user)))->toBeFalse();
});

it('recusa seleção acima do cap', function () {
    $user = User::factory()->create();
    [$a1, $a2] = criarMonitorados($user, 2);
    actingAs($user);

    postJson(route('app.monitoramento.reconciliar-limite'), ['manter' => [$a1, $a2]])
        ->assertStatus(422)
        ->assertJsonPath('success', false);

    // nada mudou
    expect(MonitoramentoAssinatura::find($a1)->status)->toBe('ativo');
    expect(MonitoramentoAssinatura::find($a2)->status)->toBe('ativo');
});

it('pausa por downgrade não conta pro cap de novos monitoramentos', function () {
    $user = User::factory()->create();
    $plano = MonitoramentoPlano::first();
    MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'plano_id' => $plano->id,
        'status' => 'pausado', 'pausada_motivo' => MonitoramentoAssinatura::MOTIVO_DOWNGRADE,
        'frequencia_dias' => 30,
    ]);
    // pausa manual ocupa slot
    MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'plano_id' => $plano->id,
        'status' => 'pausado', 'pausada_motivo' => 'manual',
        'frequencia_dias' => 30,
    ]);

    expect(app(EntitlementService::class)->cnpjsMonitoradosOcupados($user))->toBe(1);
});
