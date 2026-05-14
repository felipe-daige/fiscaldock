<?php

use App\Models\Alerta;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoConsulta;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use Database\Seeders\MonitoramentoPlanoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(MonitoramentoPlanoSeeder::class);
    $this->plano = MonitoramentoPlano::query()->where('custo_creditos', '>', 0)->first();
    config()->set('services.webhook.monitoramento_cnpj_participante_url', 'https://n8n.test/monitoramento/participante');
    config()->set('services.webhook.monitoramento_cnpj_cliente_url', 'https://n8n.test/monitoramento/cliente');
    Http::fake(['n8n.test/*' => Http::response(['ok' => true], 200)]);
});

function participanteDe(User $user): Participante
{
    return Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181',
        'tipo_documento' => 'PJ', 'razao_social' => 'Fornecedor X',
    ]);
}

it('dispara assinatura vencida com saldo e reagenda', function () {
    $user = User::factory()->create(['credits' => 100]);
    $assinatura = MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => participanteDe($user)->id,
        'plano_id' => $this->plano->id, 'status' => 'ativo', 'frequencia_dias' => 30,
        'proxima_execucao_em' => now()->subDay(),
    ]);

    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();

    expect(MonitoramentoConsulta::where('assinatura_id', $assinatura->id)->count())->toBe(1);
    $assinatura->refresh();
    expect($assinatura->proxima_execucao_em->isFuture())->toBeTrue();
    expect($assinatura->ultima_execucao_em)->not->toBeNull();
});

it('pausa e alerta quando o usuário não tem saldo', function () {
    $user = User::factory()->create(['credits' => 0]);
    $assinatura = MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => participanteDe($user)->id,
        'plano_id' => $this->plano->id, 'status' => 'ativo', 'frequencia_dias' => 30,
        'proxima_execucao_em' => now()->subDay(),
    ]);

    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();

    expect($assinatura->fresh()->status)->toBe('pausado');
    expect(MonitoramentoConsulta::where('assinatura_id', $assinatura->id)->count())->toBe(0);
    expect(Alerta::where('tipo', 'monitoramento_pausado_saldo')->count())->toBe(1);
});

it('ignora assinatura cuja proxima_execucao_em ainda é futura', function () {
    $user = User::factory()->create(['credits' => 100]);
    MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => participanteDe($user)->id,
        'plano_id' => $this->plano->id, 'status' => 'ativo', 'frequencia_dias' => 30,
        'proxima_execucao_em' => now()->addDays(10),
    ]);

    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();

    expect(MonitoramentoConsulta::count())->toBe(0);
});

it('é idempotente — rodar duas vezes não duplica consultas', function () {
    $user = User::factory()->create(['credits' => 100]);
    MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => participanteDe($user)->id,
        'plano_id' => $this->plano->id, 'status' => 'ativo', 'frequencia_dias' => 30,
        'proxima_execucao_em' => now()->subDay(),
    ]);

    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();
    $this->artisan('monitoramento:executar-pendentes')->assertSuccessful();

    expect(MonitoramentoConsulta::count())->toBe(1);
});
