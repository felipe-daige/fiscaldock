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

/** Cria N monitoramentos ativos (participante_id null é aceito; a contagem é por usuário/status). */
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

it('não cria excedente por quantidade de CNPJs monitorados', function () {
    $user = User::factory()->create();
    criarMonitorados($user, 5);

    $entitlements = app(EntitlementService::class);

    expect($entitlements->limiteCnpjsMonitorados($user))->toBeNull()
        ->and($entitlements->excedeLimiteMonitoramento($user))->toBeNull()
        ->and($entitlements->podeMonitorarMaisCnpj($user, 5))->toBeTrue();
});

it('mantém todos ativos ao receber uma reconciliação legada', function () {
    $user = User::factory()->create();
    [$a1, $a2, $a3] = criarMonitorados($user, 3);

    actingAs($user);

    postJson(route('app.monitoramento.reconciliar-limite'), ['manter' => [$a1]])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('ocupados', 3);

    foreach ([$a1, $a2, $a3] as $id) {
        expect(MonitoramentoAssinatura::find($id)->status)->toBe('ativo')
            ->and(MonitoramentoAssinatura::find($id)->pausada_motivo)->toBeNull();
    }
});

it('cancelados não entram na contagem operacional', function () {
    $user = User::factory()->create();
    $plano = MonitoramentoPlano::first();

    MonitoramentoAssinatura::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => 'ativo',
        'frequencia_dias' => 30,
    ]);
    MonitoramentoAssinatura::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => 'cancelado',
        'frequencia_dias' => 30,
    ]);

    expect(app(EntitlementService::class)->cnpjsMonitoradosOcupados($user))->toBe(1);
});

it('pausas automáticas antigas ficam preservadas fora da contagem operacional', function () {
    $user = User::factory()->create();
    $plano = MonitoramentoPlano::first();

    MonitoramentoAssinatura::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => 'pausado',
        'pausada_motivo' => MonitoramentoAssinatura::MOTIVO_DOWNGRADE,
        'frequencia_dias' => 30,
    ]);
    MonitoramentoAssinatura::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => 'pausado',
        'pausada_motivo' => 'manual',
        'frequencia_dias' => 30,
    ]);

    expect(app(EntitlementService::class)->cnpjsMonitoradosOcupados($user))->toBe(1);
});
