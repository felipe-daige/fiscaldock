<?php

use App\Models\AccountSubscription;
use App\Models\ConsultaLote;
use App\Models\MonitoramentoPlano;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Entitlements\EntitlementService;
use Database\Seeders\MonitoramentoPlanoSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SubscriptionPlanSeeder::class);
});

function residuaisTrialAtivo(User $user): User
{
    $user->forceFill([
        'trial_used' => true,
        'trial_started_at' => now(),
        'trial_expires_at' => now()->addDays(30),
        'trial_credits_remaining' => 50,
    ])->save();

    return $user;
}

function residuaisComPlano(User $user, string $codigo): User
{
    $plano = SubscriptionPlan::where('codigo', $codigo)->first();
    AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $plano->id,
        'status' => 'ativa', 'ciclo' => 'mensal',
    ]);

    return $user;
}

// ---- Export fino por formato (csv × excel) ----

it('permitsExportFormat respeita a lista de formatos do plano', function () {
    $svc = app(EntitlementService::class);

    $free = User::factory()->create();
    expect($svc->permitsExportFormat($free, 'csv'))->toBeFalse();

    $essencial = residuaisComPlano(User::factory()->create(), 'essencial');
    expect($svc->permitsExportFormat($essencial, 'csv'))->toBeTrue()
        ->and($svc->permitsExportFormat($essencial, 'excel'))->toBeFalse();

    $profissional = residuaisComPlano(User::factory()->create(), 'profissional');
    expect($svc->permitsExportFormat($profissional, 'excel'))->toBeTrue();

    $trial = residuaisTrialAtivo(User::factory()->create());
    expect($svc->permitsExportFormat($trial, 'excel'))->toBeTrue();
});

it('Essencial (export=[csv]) recebe 403 no export XLSX do BI', function () {
    actingAs(residuaisComPlano(User::factory()->create(), 'essencial'))
        ->get('/app/bi/exportar-xlsx')
        ->assertStatus(403);
});

it('Essencial NÃO é barrado no export CSV do BI', function () {
    $status = actingAs(residuaisComPlano(User::factory()->create(), 'essencial'))
        ->get('/app/bi/exportar')->getStatusCode();
    expect($status)->not->toBe(403);
});

it('Profissional (export=[csv,excel]) NÃO é barrado no export XLSX', function () {
    $status = actingAs(residuaisComPlano(User::factory()->create(), 'profissional'))
        ->get('/app/bi/exportar-xlsx')->getStatusCode();
    expect($status)->not->toBe(403);
});

it('trial ativo NÃO é barrado no export XLSX', function () {
    $status = actingAs(residuaisTrialAtivo(User::factory()->create()))
        ->get('/app/bi/exportar-xlsx')->getStatusCode();
    expect($status)->not->toBe(403);
});

// ---- BI completo (abas analíticas) ----

it('permits(bi_completo) é falso no Free e verdadeiro nos pagos', function () {
    $svc = app(EntitlementService::class);

    expect($svc->permits(User::factory()->create(), 'bi_completo'))->toBeFalse()
        ->and($svc->permits(residuaisComPlano(User::factory()->create(), 'essencial'), 'bi_completo'))->toBeTrue()
        ->and($svc->permits(residuaisTrialAtivo(User::factory()->create()), 'bi_completo'))->toBeTrue();
});

it('Free puro recebe 403 nas abas analíticas do BI', function (string $rota) {
    actingAs(User::factory()->create())->get($rota)->assertStatus(403);
})->with([
    '/app/bi/tributos',
    '/app/bi/riscos',
    '/app/bi/tributario-efd',
    '/app/bi/apuracao-notas',
    '/app/bi/cfop',
]);

it('Free puro vê paywall (não 403) nas telas dedicadas gateadas', function (string $rota) {
    actingAs(User::factory()->create())->get($rota)
        ->assertOk()
        ->assertSee('BI completo', false)
        ->assertSee('/app/planos', false);
})->with([
    '/app/bi/catalogo-itens',
    '/app/bi/cruzamentos',
]);

it('Free puro NÃO é barrado nas abas básicas do BI', function (string $rota) {
    $status = actingAs(User::factory()->create())->get($rota)->status();
    expect($status)->not->toBe(403);
})->with([
    '/app/bi/dashboard',
    '/app/bi/faturamento',
    '/app/bi/compras',
    '/app/bi/efd',
    '/app/bi/participantes',
]);

it('Essencial NÃO é barrado nas abas analíticas do BI', function () {
    $status = actingAs(residuaisComPlano(User::factory()->create(), 'essencial'))
        ->get('/app/bi/cfop')->status();
    expect($status)->not->toBe(403);
});

it('trial ativo NÃO é barrado nas abas analíticas do BI', function () {
    $status = actingAs(residuaisTrialAtivo(User::factory()->create()))
        ->get('/app/bi/cfop')->status();
    expect($status)->not->toBe(403);
});

// ---- Retenção de histórico (Free = 6 meses) ----

function residuaisCriarLote(User $user, \Illuminate\Support\Carbon $criadoEm): ConsultaLote
{
    $plano = MonitoramentoPlano::porCodigo('gratuito') ?? MonitoramentoPlano::firstOrFail();

    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'tab-retencao-'.uniqid(),
    ]);

    $lote->timestamps = false;
    $lote->forceFill(['created_at' => $criadoEm])->save();

    return $lote;
}

it('Free só vê lotes dos últimos 6 meses no histórico', function () {
    $this->seed(MonitoramentoPlanoSeeder::class);

    $user = User::factory()->create();
    $antigo = residuaisCriarLote($user, now()->subMonths(7));
    $recente = residuaisCriarLote($user, now()->subDays(3));

    $resp = actingAs($user)->get('/app/consulta/historico')->assertOk();

    $ids = collect($resp->viewData('lotes')->items())->pluck('id');
    expect($ids)->toContain($recente->id)
        ->and($ids)->not->toContain($antigo->id);
});

it('plano pago (retenção ilimitada) vê o histórico inteiro', function () {
    $this->seed(MonitoramentoPlanoSeeder::class);

    $user = residuaisComPlano(User::factory()->create(), 'essencial');
    $antigo = residuaisCriarLote($user, now()->subMonths(13));
    $recente = residuaisCriarLote($user, now()->subDays(3));

    $resp = actingAs($user)->get('/app/consulta/historico')->assertOk();

    $ids = collect($resp->viewData('lotes')->items())->pluck('id');
    expect($ids)->toContain($recente->id)
        ->and($ids)->toContain($antigo->id);
});

it('trial ativo vê o histórico inteiro', function () {
    $this->seed(MonitoramentoPlanoSeeder::class);

    $user = residuaisTrialAtivo(User::factory()->create());
    $antigo = residuaisCriarLote($user, now()->subMonths(7));

    $resp = actingAs($user)->get('/app/consulta/historico')->assertOk();

    $ids = collect($resp->viewData('lotes')->items())->pluck('id');
    expect($ids)->toContain($antigo->id);
});

it('retencaoMeses lê a capability do plano', function () {
    $svc = app(EntitlementService::class);

    expect($svc->retencaoMeses(User::factory()->create()))->toBe(6)
        ->and($svc->retencaoMeses(residuaisComPlano(User::factory()->create(), 'essencial')))->toBeNull()
        ->and($svc->retencaoMeses(residuaisTrialAtivo(User::factory()->create())))->toBeNull();
});
