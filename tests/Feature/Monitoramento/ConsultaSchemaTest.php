<?php

use App\Models\MonitoramentoConsulta;
use App\Models\MonitoramentoPlano;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('cria consulta de retry ligada ao pai e ao lote', function () {
    $user = User::factory()->create();
    $plano = MonitoramentoPlano::porCodigo('licitacao');

    $pai = MonitoramentoConsulta::create([
        'user_id' => $user->id, 'plano_id' => $plano->id, 'tipo' => 'assinatura', 'status' => 'erro',
        'creditos_cobrados' => 10,
    ]);
    $filha = MonitoramentoConsulta::create([
        'user_id' => $user->id, 'plano_id' => $plano->id, 'tipo' => 'assinatura', 'status' => 'pendente',
        'creditos_cobrados' => 10, 'parent_consulta_id' => $pai->id, 'consulta_lote_id' => null,
    ]);

    expect($filha->parent_consulta_id)->toBe($pai->id)
        ->and($filha->participante_id)->toBeNull();
});
