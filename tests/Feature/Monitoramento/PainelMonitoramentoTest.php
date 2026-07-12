<?php

use App\Models\AccountSubscription;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\ParticipanteGrupo;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush(); // guards 1×/ciclo do freio vivem no cache — isolar entre testes
});

/** Assinatura de conta (paga) — como freioAssinar em FreioConsumoV2Test, plano `essencial`. */
function painelAssinar(User $user, array $overrides = []): void
{
    test()->seed(SubscriptionPlanSeeder::class);
    $plano = SubscriptionPlan::where('codigo', 'essencial')->first();
    AccountSubscription::create(array_merge([
        'user_id' => $user->id, 'subscription_plan_id' => $plano->id,
        'status' => 'ativa', 'ciclo' => 'mensal', 'ultimo_grant_em' => now()->subDay(),
    ], $overrides));
}

/** Consumo já registrado no ciclo corrente do freio (deduções type=monitoramento_assinatura). */
function painelConsumoNoCiclo(User $user, int $creditos): void
{
    DB::table('credit_transactions')->insert([
        'user_id' => $user->id, 'amount' => -$creditos, 'balance_after' => 0,
        'type' => 'monitoramento_assinatura', 'created_at' => now(), 'updated_at' => now(),
    ]);
}

it('painel lista monitorados dos 3 tipos com plano/frequência/custo', function () {
    $user = User::factory()->create();
    $plano = MonitoramentoPlano::porCodigo('licitacao') ?? MonitoramentoPlano::firstOrFail();

    $p = Participante::create(['user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'Part Monitorado', 'uf' => 'SP']);
    MonitoramentoAssinatura::create(['user_id' => $user->id, 'participante_id' => $p->id, 'plano_id' => $plano->id, 'status' => 'ativo', 'frequencia_dias' => 30]);

    $grupo = ParticipanteGrupo::create(['user_id' => $user->id, 'nome' => 'Grupo Monitorado']);
    $grupo->participantes()->attach($p->id);
    MonitoramentoAssinatura::create(['user_id' => $user->id, 'grupo_id' => $grupo->id, 'plano_id' => $plano->id, 'status' => 'pausado', 'frequencia_dias' => 7]);

    $resp = actingAs($user)->get(route('app.monitoramento.painel'));

    $resp->assertOk();
    $resp->assertSee('Part Monitorado');
    $resp->assertSee('Grupo Monitorado');
    $resp->assertSee('1 membro', false);
    $resp->assertSee('painel-monitorados', false);
    $resp->assertSee('painel-grupos', false);
});

it('painel não vaza assinaturas de outro usuário', function () {
    $dono = User::factory()->create();
    $plano = MonitoramentoPlano::porCodigo('licitacao') ?? MonitoramentoPlano::firstOrFail();
    $p = Participante::create(['user_id' => $dono->id, 'documento' => '11222333000181', 'razao_social' => 'Alheio Secreto', 'uf' => 'SP']);
    MonitoramentoAssinatura::create(['user_id' => $dono->id, 'participante_id' => $p->id, 'plano_id' => $plano->id, 'status' => 'ativo', 'frequencia_dias' => 30]);

    actingAs(User::factory()->create())
        ->get(route('app.monitoramento.painel'))
        ->assertOk()
        ->assertDontSee('Alheio Secreto');
});

it('grupos/{id}/participantes devolve JSON de membros pro modal do painel', function () {
    $user = User::factory()->create();
    $grupo = ParticipanteGrupo::create(['user_id' => $user->id, 'nome' => 'Grupo Membros']);
    $p = Participante::create(['user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'Membro Um', 'uf' => 'SP']);
    $grupo->participantes()->attach($p->id);

    // Membro de outro usuário anexado ao grupo não pode vazar.
    $outro = Participante::create(['user_id' => User::factory()->create()->id, 'documento' => '99888777000166', 'razao_social' => 'Alheio', 'uf' => 'SP']);
    $grupo->participantes()->attach($outro->id);

    $resp = actingAs($user)->getJson(route('app.monitoramento.grupos.participantes', ['id' => $grupo->id]));

    $resp->assertOk()
        ->assertJson(['success' => true])
        ->assertJsonCount(1, 'participantes')
        ->assertJsonPath('participantes.0.nome', 'Membro Um');

    // Grupo alheio → 404
    actingAs(User::factory()->create())
        ->getJson(route('app.monitoramento.grupos.participantes', ['id' => $grupo->id]))
        ->assertNotFound();
});

it('/app/monitoramento/grupos redireciona 301 pro painel', function () {
    actingAs(User::factory()->create())
        ->get('/app/monitoramento/grupos')
        ->assertRedirect(route('app.monitoramento.painel'));
});

it('/app/consulta/painel serve a tela e /app/consulta/nova redireciona', function () {
    $user = User::factory()->create();
    actingAs($user)->get('/app/consulta/painel')->assertOk();
    actingAs($user)->get('/app/consulta/nova')->assertRedirect('/app/consulta/painel');
});

it('painel mostra badge do motivo em assinatura pausada por saldo', function () {
    $user = User::factory()->create();
    $plano = MonitoramentoPlano::porCodigo('licitacao') ?? MonitoramentoPlano::firstOrFail();
    $p = Participante::create(['user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'Part Pausado', 'uf' => 'SP']);
    $assinatura = MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => $p->id, 'plano_id' => $plano->id,
        'status' => 'ativo', 'frequencia_dias' => 30,
    ]);
    $assinatura->pausar('saldo');

    $resp = actingAs($user)->get(route('app.monitoramento.painel'));

    $resp->assertOk()->assertSee('sem saldo');
});

it('painel mostra badge aguardando próximo ciclo quando o freio segura a assinatura', function () {
    $user = User::factory()->create(['credits' => 100]);
    painelAssinar($user, ['limite_consumo_automatico' => 5]); // cap 5
    painelConsumoNoCiclo($user, 5); // consumo 5 já feito no ciclo

    $plano = MonitoramentoPlano::porCodigo('licitacao') ?? MonitoramentoPlano::firstOrFail(); // custo 20 > cap 5
    $p = Participante::create(['user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'Part Aguardando', 'uf' => 'SP']);
    MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => $p->id, 'plano_id' => $plano->id,
        'status' => 'ativo', 'frequencia_dias' => 30, 'proxima_execucao_em' => now()->subDay(),
    ]);

    $resp = actingAs($user)->get(route('app.monitoramento.painel'));

    $resp->assertOk()->assertSee('aguardando próximo ciclo');
});

it('painel renderiza a barra de consumo do ciclo quando há cap', function () {
    $user = User::factory()->create(['credits' => 100]);
    painelAssinar($user, ['limite_consumo_automatico' => 50]);

    $plano = MonitoramentoPlano::porCodigo('licitacao') ?? MonitoramentoPlano::firstOrFail();
    $p = Participante::create(['user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'Part Barra', 'uf' => 'SP']);
    MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => $p->id, 'plano_id' => $plano->id,
        'status' => 'ativo', 'frequencia_dias' => 30, 'proxima_execucao_em' => now()->subDay(),
    ]);

    $resp = actingAs($user)->get(route('app.monitoramento.painel'));

    $resp->assertOk()
        ->assertSee('Consumo do ciclo')
        ->assertSee('Projetado at')
        ->assertSee('id="teto-efetivo-valor"', false)
        ->assertSee('id="consumo-ciclo-percentual"', false)
        ->assertSee('id="consumo-ciclo-barra"', false)
        ->assertSee('id="input-limite-consumo" inputmode="decimal"', false)
        ->assertSee('value="10,00"', false)
        ->assertSee('data-max-unidades="1000000"', false);
});

it('modal novo monitorado expõe o estimador de custo mensal/trimestral', function () {
    $user = User::factory()->create();
    $grupo = ParticipanteGrupo::create(['user_id' => $user->id, 'nome' => 'Grupo Custo']);
    $p = Participante::create(['user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'Membro', 'uf' => 'SP']);
    $grupo->participantes()->attach($p->id);

    $resp = actingAs($user)->get(route('app.monitoramento.painel'));

    $resp->assertOk()
        // linha do estimador (escondida até ter alvo selecionado)
        ->assertSee('id="mon-custo-estimado"', false)
        // dados que o JS usa pro cálculo: custo em créditos por plano,
        // nº de membros por grupo e preço unitário do crédito no form
        ->assertSee('data-custo="', false)
        ->assertSee('data-membros="1"', false)
        ->assertSee('id="form-monitorar" data-saldo-unit-price="', false);
});

// ── Fase 5.1: gating de frequência por tier no modal "Novo monitorado" ──────

it('modal novo monitorado trava frequências acima do tier (Free = mensal apenas)', function () {
    test()->seed(SubscriptionPlanSeeder::class);
    $user = User::factory()->create(); // sem assinatura → Free (mínimo 30 dias)

    $resp = actingAs($user)->get(route('app.monitoramento.painel'));

    $resp->assertOk()
        ->assertSee('requer plano superior')
        ->assertSee('Seu plano permite monitorar no máximo a cada 30 dias', false)
        ->assertSee('value="diario" disabled', false)
        ->assertSee('value="semanal" disabled', false)
        ->assertSee('value="quinzenal" disabled', false);
});

it('modal novo monitorado libera quinzenal no tier profissional (mínimo 15 dias) e trava as menores', function () {
    test()->seed(SubscriptionPlanSeeder::class);
    $user = User::factory()->create();
    painelAssinar($user, [
        'subscription_plan_id' => SubscriptionPlan::where('codigo', 'profissional')->firstOrFail()->id,
    ]);

    $html = actingAs($user)->get(route('app.monitoramento.painel'))->assertOk()->getContent();

    expect($html)->toContain('value="diario" disabled')
        ->toContain('value="semanal" disabled')
        ->not->toContain('value="quinzenal" disabled')
        ->toContain('Seu plano permite monitorar no máximo a cada 15 dias');
});

it('modal novo monitorado libera todas as frequências no trial', function () {
    test()->seed(SubscriptionPlanSeeder::class);
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get(route('app.monitoramento.painel'))->assertOk()->getContent();

    expect($html)->not->toContain('requer plano superior')
        ->not->toContain('value="diario" disabled')
        ->not->toContain('Seu plano permite monitorar no máximo');
});
