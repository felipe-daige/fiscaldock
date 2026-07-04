<?php

use App\Models\AccountSubscription;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoConsulta;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Support\Monitoramento\MonitoramentoNotifier;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush(); // guards 1×/ciclo vivem no cache — isolar entre testes
});

function freioAssinatura(User $user, Participante $p, array $overrides = []): MonitoramentoAssinatura
{
    return MonitoramentoAssinatura::create(array_merge([
        'user_id' => $user->id, 'participante_id' => $p->id,
        'plano_id' => MonitoramentoPlano::porCodigo('licitacao')->id, // custo 20
        'status' => 'ativo', 'frequencia_dias' => 30, 'proxima_execucao_em' => now()->subDay(),
    ], $overrides));
}

function freioParticipante(User $user, string $doc = '11222333000181'): Participante
{
    return Participante::create(['user_id' => $user->id, 'documento' => $doc, 'razao_social' => 'ACME']);
}

it('pausar() registra o motivo e reativar() limpa', function () {
    $user = User::factory()->create();
    $a = freioAssinatura($user, freioParticipante($user));

    $a->pausar('saldo');
    expect($a->fresh()->status)->toBe('pausado')
        ->and($a->fresh()->pausada_motivo)->toBe('saldo');

    $a->reativar();
    expect($a->fresh()->status)->toBe('ativo')
        ->and($a->fresh()->pausada_motivo)->toBeNull();
});

it('pausar() sem argumento assume motivo manual', function () {
    $user = User::factory()->create();
    $a = freioAssinatura($user, freioParticipante($user));

    $a->pausar();
    expect($a->fresh()->pausada_motivo)->toBe('manual');
});

function freioAssinar(User $user, array $overrides = []): void
{
    test()->seed(SubscriptionPlanSeeder::class);
    $plano = SubscriptionPlan::where('codigo', 'essencial')->first();
    AccountSubscription::create(array_merge([
        'user_id' => $user->id, 'subscription_plan_id' => $plano->id,
        'status' => 'ativa', 'ciclo' => 'mensal', 'ultimo_grant_em' => now()->subDay(),
    ], $overrides));
}

function freioConsumoNoCiclo(User $user, int $creditos): void
{
    DB::table('credit_transactions')->insert([
        'user_id' => $user->id, 'amount' => -$creditos, 'balance_after' => 0,
        'type' => 'monitoramento_assinatura', 'created_at' => now(), 'updated_at' => now(),
    ]);
}

it('cap estourado ADIA sem pausar: assinatura segue ativa e vencida, sem consulta', function () {
    Bus::fake();
    $user = User::factory()->create(['credits' => 100]);
    freioAssinar($user, ['limite_consumo_automatico' => 5]);
    freioConsumoNoCiclo($user, 5); // cap 5 já consumido
    $a = freioAssinatura($user, freioParticipante($user)); // custo 20

    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();

    expect($a->fresh()->status)->toBe('ativo')
        ->and($a->fresh()->pausada_motivo)->toBeNull()
        ->and($a->fresh()->proxima_execucao_em->isPast())->toBeTrue()
        ->and(MonitoramentoConsulta::where('assinatura_id', $a->id)->count())->toBe(0);
});

it('adiada dispara sozinha quando o cap sobe no meio do ciclo', function () {
    Bus::fake();
    $user = User::factory()->create(['credits' => 100]);
    freioAssinar($user, ['limite_consumo_automatico' => 5]);
    freioConsumoNoCiclo($user, 5);
    $a = freioAssinatura($user, freioParticipante($user));

    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();
    expect(MonitoramentoConsulta::where('assinatura_id', $a->id)->count())->toBe(0);

    $user->subscription()->first()->update(['limite_consumo_automatico' => 100]);

    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();
    expect(MonitoramentoConsulta::where('assinatura_id', $a->id)->count())->toBe(1);
});

it('adiada dispara sozinha quando o ciclo vira (consumo antigo sai da janela)', function () {
    Bus::fake();
    $user = User::factory()->create(['credits' => 100]);
    // cap PRECISA comportar o custo (20) com consumo zerado — senão nunca dispararia
    freioAssinar($user, ['limite_consumo_automatico' => 22]);
    freioConsumoNoCiclo($user, 5); // 5 + 20 > 22 => adia no ciclo atual
    $a = freioAssinatura($user, freioParticipante($user));

    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();
    expect(MonitoramentoConsulta::where('assinatura_id', $a->id)->count())->toBe(0);

    // ciclo novo: âncora do grant avança pra depois do consumo antigo (addMinute evita
    // empate no segundo com o created_at do consumo — o filtro do ciclo é >=)
    $user->subscription()->first()->update(['ultimo_grant_em' => now()->addMinute()]);

    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();
    expect(MonitoramentoConsulta::where('assinatura_id', $a->id)->count())->toBe(1);
});

it('ordena por custo: a barata dispara, a cara (que estouraria) adia', function () {
    Bus::fake();
    $user = User::factory()->create(['credits' => 100]);
    freioAssinar($user, ['limite_consumo_automatico' => 18]);
    $pCara = freioParticipante($user, '11222333000181');
    $pBarata = freioParticipante($user, '11444777000161');
    // criada ANTES (id menor) mas custo maior — sem ordenação ela comeria o cap primeiro
    $cara = freioAssinatura($user, $pCara);   // licitacao, custo 20 > cap 18 sozinha
    $barata = freioAssinatura($user, $pBarata, [
        'plano_id' => MonitoramentoPlano::porCodigo('validacao')->id, // custo 15
    ]);

    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();

    expect(MonitoramentoConsulta::where('assinatura_id', $barata->id)->count())->toBe(1)
        ->and(MonitoramentoConsulta::where('assinatura_id', $cara->id)->count())->toBe(0)
        ->and($cara->fresh()->status)->toBe('ativo');
});

it('notifica freioAtuou 1x por ciclo mesmo com 2 runs', function () {
    Bus::fake();
    $user = User::factory()->create(['credits' => 100]);
    freioAssinar($user, ['limite_consumo_automatico' => 5]);
    freioConsumoNoCiclo($user, 5);
    freioAssinatura($user, freioParticipante($user));

    $spy = $this->spy(MonitoramentoNotifier::class);

    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();
    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();

    $spy->shouldHaveReceived('freioAtuou')->once();
});

it('notifica consumoProximoDoLimite ao cruzar 80% do cap, 1x por ciclo', function () {
    Bus::fake();
    $user = User::factory()->create(['credits' => 100]);
    freioAssinar($user, ['limite_consumo_automatico' => 24]); // custo 20 => 20/24 = 83%
    $a = freioAssinatura($user, freioParticipante($user));

    $spy = $this->spy(MonitoramentoNotifier::class);

    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();

    $spy->shouldHaveReceived('consumoProximoDoLimite')->once();
    expect(MonitoramentoConsulta::where('assinatura_id', $a->id)->count())->toBe(1);
});

it('saldo insuficiente segue pausando, com motivo saldo', function () {
    Bus::fake();
    $user = User::factory()->create(['credits' => 0]);
    $a = freioAssinatura($user, freioParticipante($user));

    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();

    expect($a->fresh()->status)->toBe('pausado')
        ->and($a->fresh()->pausada_motivo)->toBe('saldo');
});
