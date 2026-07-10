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

function coberturaComPlano(User $user, string $codigo): User
{
    $plano = SubscriptionPlan::where('codigo', $codigo)->first();
    AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $plano->id,
        'status' => 'ativa', 'ciclo' => 'mensal',
    ]);

    return $user;
}

function coberturaTrialExpirado(User $user): User
{
    $user->forceFill([
        'trial_used' => true,
        'trial_started_at' => now()->subDays(90),
        'trial_expires_at' => now()->subDay(),
        'trial_credits_remaining' => 10,
    ])->save();

    return $user;
}

// ---- Trial EXPIRADO volta a valer o plano (Free puro) ----

it('trial expirado se comporta como Free: sem bi_completo, sem export, retenção 6m', function () {
    $svc = app(EntitlementService::class);
    $user = coberturaTrialExpirado(User::factory()->create());

    expect($svc->permits($user, 'bi_completo'))->toBeFalse()
        ->and($svc->permits($user, 'export'))->toBeFalse()
        ->and($svc->permitsExportFormat($user, 'csv'))->toBeFalse()
        ->and($svc->retencaoMeses($user))->toBe(6);
});

it('trial expirado recebe 403 em aba analítica e em export', function () {
    $user = coberturaTrialExpirado(User::factory()->create());

    actingAs($user)->get('/app/bi/cfop')->assertStatus(403);
    actingAs($user)->get('/app/bi/exportar')->assertStatus(403);
});

// ---- Matriz completa da capability export por plano ----

it('formats por plano batem com o seeder (free/essencial/profissional/escritorio/enterprise)', function () {
    $svc = app(EntitlementService::class);

    expect($svc->exportFormats(User::factory()->create()))->toBe([])
        ->and($svc->exportFormats(coberturaComPlano(User::factory()->create(), 'essencial')))->toBe(['csv'])
        ->and($svc->exportFormats(coberturaComPlano(User::factory()->create(), 'profissional')))->toBe(['csv', 'excel'])
        ->and($svc->exportFormats(coberturaComPlano(User::factory()->create(), 'escritorio')))->toBe(['csv', 'excel'])
        ->and($svc->exportFormats(coberturaComPlano(User::factory()->create(), 'enterprise')))->toBe(['csv', 'excel', 'api']);
});

it('formato desconhecido nunca é permitido (nem no Enterprise)', function () {
    $svc = app(EntitlementService::class);

    expect($svc->permitsExportFormat(coberturaComPlano(User::factory()->create(), 'enterprise'), 'xml'))->toBeFalse();
});

it('PDF (gate :export genérico) é liberado já no Essencial', function () {
    $status = actingAs(coberturaComPlano(User::factory()->create(), 'essencial'))
        ->get('/app/bi/exportar-pdf')->getStatusCode();
    expect($status)->not->toBe(403);
});

// ---- Gate fino aplicado em TODAS as rotas de export XLSX (não só BI) ----

it('Essencial (sem excel) recebe 403 em toda rota de export XLSX', function (string $metodo, string $rota) {
    $user = coberturaComPlano(User::factory()->create(), 'essencial');

    $resp = $metodo === 'post'
        ? actingAs($user)->post($rota)
        : actingAs($user)->get($rota);

    $resp->assertStatus(403);
})->with([
    ['get', '/app/alertas/exportar-xlsx'],
    ['post', '/app/clientes/exportar-xlsx'],
    ['post', '/app/participantes/exportar-xlsx'],
    ['get', '/app/resumo-fiscal/exportar-xlsx'],
    ['get', '/app/catalogo/exportar-xlsx'],
    ['get', '/app/clearance/dashboard/exportar-xlsx'],
    ['get', '/app/notas/dashboard/exportar-xlsx'],
    ['get', '/app/bi/catalogo-itens/exportar-xlsx'],
]);

it('Free puro recebe 403 em toda rota de export (antes ficavam SEM gate)', function (string $metodo, string $rota) {
    $user = User::factory()->create();

    $resp = $metodo === 'post'
        ? actingAs($user)->post($rota)
        : actingAs($user)->get($rota);

    $resp->assertStatus(403);
})->with([
    ['get', '/app/alertas/exportar-pdf'],
    ['get', '/app/alertas/exportar-csv'],
    ['post', '/app/clientes/exportar-pdf'],
    ['post', '/app/clientes/exportar-csv'],
    ['post', '/app/participantes/exportar-pdf'],
    ['post', '/app/participantes/exportar-csv'],
    ['get', '/app/importacao/efd/1/exportar'],
]);

it('Profissional passa pelo gate nas rotas XLSX (não-403)', function (string $metodo, string $rota) {
    $user = coberturaComPlano(User::factory()->create(), 'profissional');

    $resp = $metodo === 'post'
        ? actingAs($user)->post($rota)
        : actingAs($user)->get($rota);

    expect($resp->getStatusCode())->not->toBe(403);
})->with([
    ['get', '/app/alertas/exportar-xlsx'],
    ['post', '/app/clientes/exportar-xlsx'],
    ['post', '/app/participantes/exportar-xlsx'],
    ['get', '/app/resumo-fiscal/exportar-xlsx'],
]);

it('Essencial passa pelo gate nas rotas CSV (não-403)', function (string $metodo, string $rota) {
    $user = coberturaComPlano(User::factory()->create(), 'essencial');

    $resp = $metodo === 'post'
        ? actingAs($user)->post($rota)
        : actingAs($user)->get($rota);

    expect($resp->getStatusCode())->not->toBe(403);
})->with([
    ['get', '/app/alertas/exportar-csv'],
    ['post', '/app/clientes/exportar-csv'],
    ['post', '/app/participantes/exportar-csv'],
    ['get', '/app/importacao/efd/1/exportar'],
]);

// ---- bi_completo: POSTs de alerta do catálogo-itens + Enterprise ----

it('POSTs de alerta do catálogo-itens são barrados para Free puro', function () {
    $user = User::factory()->create();

    actingAs($user)->post('/app/bi/catalogo-itens/alerta/descartar')->assertStatus(403);
    actingAs($user)->post('/app/bi/catalogo-itens/alerta/restaurar')->assertStatus(403);
});

it('POSTs de alerta do catálogo-itens passam o gate no Essencial (não-403)', function () {
    $user = coberturaComPlano(User::factory()->create(), 'essencial');

    expect(actingAs($user)->post('/app/bi/catalogo-itens/alerta/descartar')->getStatusCode())->not->toBe(403)
        ->and(actingAs($user)->post('/app/bi/catalogo-itens/alerta/restaurar')->getStatusCode())->not->toBe(403);
});

it('Enterprise tem bi_completo', function () {
    $svc = app(EntitlementService::class);

    expect($svc->permits(coberturaComPlano(User::factory()->create(), 'enterprise'), 'bi_completo'))->toBeTrue();
});

// ---- Retenção: bordas e KPIs ----

function coberturaCriarLote(User $user, \Illuminate\Support\Carbon $criadoEm): ConsultaLote
{
    $plano = MonitoramentoPlano::porCodigo('gratuito') ?? MonitoramentoPlano::firstOrFail();

    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'tab-cobertura-'.uniqid(),
    ]);

    $lote->timestamps = false;
    $lote->forceFill(['created_at' => $criadoEm])->save();

    return $lote;
}

it('borda da retenção: dentro de 6 meses aparece, além não', function () {
    $this->seed(MonitoramentoPlanoSeeder::class);

    $user = User::factory()->create();
    $dentroDaJanela = coberturaCriarLote($user, now()->subMonths(6)->addDay());
    $foraDaJanela = coberturaCriarLote($user, now()->subMonths(6)->subDay());

    $resp = actingAs($user)->get('/app/consulta/historico')->assertOk();

    $ids = collect($resp->viewData('lotes')->items())->pluck('id');
    expect($ids)->toContain($dentroDaJanela->id)
        ->and($ids)->not->toContain($foraDaJanela->id);
});

it('KPIs do histórico também excluem lotes fora da retenção', function () {
    $this->seed(MonitoramentoPlanoSeeder::class);

    $user = User::factory()->create();
    coberturaCriarLote($user, now()->subMonths(8));
    coberturaCriarLote($user, now()->subDays(3));

    $resp = actingAs($user)->get('/app/consulta/historico')->assertOk();

    expect($resp->viewData('kpis')['total_lotes'])->toBe(1);
});

it('retenção convive com filtro de data do usuário (interseção)', function () {
    $this->seed(MonitoramentoPlanoSeeder::class);

    $user = User::factory()->create();
    coberturaCriarLote($user, now()->subMonths(8));
    $recente = coberturaCriarLote($user, now()->subDays(3));

    $resp = actingAs($user)
        ->get('/app/consulta/historico?data_inicio='.now()->subYear()->toDateString())
        ->assertOk();

    $ids = collect($resp->viewData('lotes')->items())->pluck('id')->all();
    expect($ids)->toBe([$recente->id]);
});

it('banner de retenção aparece para Free e não aparece para plano pago', function () {
    $this->seed(MonitoramentoPlanoSeeder::class);

    actingAs(User::factory()->create())
        ->get('/app/consulta/historico')
        ->assertOk()
        ->assertSee('últimos <strong>6 meses</strong>', false);

    actingAs(coberturaComPlano(User::factory()->create(), 'essencial'))
        ->get('/app/consulta/historico')
        ->assertOk()
        ->assertDontSee('últimos <strong>6 meses</strong>', false);
});

// ---- UI do BI: abas trancadas pro Free, livres pro pago ----

it('BI index do Free mostra abas avançadas trancadas e aviso de upgrade', function () {
    $resp = actingAs(User::factory()->create())->get('/app/bi/dashboard')->assertOk();

    $resp->assertSee('BI completo', false);
    $resp->assertSee('disabled', false);
    // Abas básicas continuam clicáveis
    $resp->assertSee('data-tab="faturamento"', false);
    $resp->assertDontSee('data-tab="cfop"', false);
});

it('BI index do pago não tranca nenhuma aba', function () {
    $resp = actingAs(coberturaComPlano(User::factory()->create(), 'essencial'))
        ->get('/app/bi/dashboard')->assertOk();

    $resp->assertSee('data-tab="cfop"', false);
    $resp->assertDontSee('disponível nos planos pagos');
});
