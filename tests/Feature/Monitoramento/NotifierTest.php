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
