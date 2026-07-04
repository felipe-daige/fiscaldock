<?php

use App\Models\MonitoramentoConsulta;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use App\Support\Monitoramento\MonitoramentoNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('registra alerta in-app quando a situação piora', function () {
    $user = User::factory()->create();
    $plano = MonitoramentoPlano::porCodigo('licitacao');
    $p = Participante::create(['user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'ACME']);

    $anterior = MonitoramentoConsulta::create([
        'user_id' => $user->id, 'plano_id' => $plano->id, 'participante_id' => $p->id,
        'tipo' => 'assinatura', 'status' => 'sucesso', 'situacao_geral' => 'regular', 'creditos_cobrados' => 0,
    ]);
    $atual = MonitoramentoConsulta::create([
        'user_id' => $user->id, 'plano_id' => $plano->id, 'participante_id' => $p->id,
        'tipo' => 'assinatura', 'status' => 'sucesso', 'situacao_geral' => 'irregular', 'creditos_cobrados' => 0,
    ]);

    app(MonitoramentoNotifier::class)->situacaoPiorou($atual, $anterior);

    assertDatabaseHas('alertas', [
        'user_id' => $user->id,
        'participante_id' => $p->id,
        'tipo' => 'monitoramento_situacao_piorou',
        'categoria' => 'monitoramento',
    ]);
});

it('freioAtuou registra alerta de conta 1 linha por usuário', function () {
    $user = User::factory()->create();

    app(MonitoramentoNotifier::class)->freioAtuou($user, 3, now()->addDays(10));

    assertDatabaseHas('alertas', [
        'user_id' => $user->id,
        'tipo' => 'monitoramento_freio_atuou',
        'categoria' => 'monitoramento',
        'severidade' => 'media',
    ]);
});

it('consumoProximoDoLimite registra alerta de 80% com valores em R$', function () {
    $user = User::factory()->create();

    app(MonitoramentoNotifier::class)->consumoProximoDoLimite($user, 8, 10);

    assertDatabaseHas('alertas', [
        'user_id' => $user->id,
        'tipo' => 'monitoramento_consumo_80',
        'severidade' => 'media',
    ]);
});
