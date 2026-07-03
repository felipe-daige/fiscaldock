<?php

use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoPlano;
use App\Models\ParticipanteGrupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('cria assinatura de GRUPO via grupo_id', function () {
    // trial libera os gates de tier (Fase 5/5.1) — o teste é do alvo grupo, não dos gates
    $user = User::factory()->trialAtivo()->create();
    $grupo = ParticipanteGrupo::create(['user_id' => $user->id, 'nome' => 'G']);
    $plano = MonitoramentoPlano::porCodigo('licitacao') ?? MonitoramentoPlano::firstOrFail();

    $resp = actingAs($user)->postJson(route('app.monitoramento.assinatura.criar'), [
        'grupo_id' => $grupo->id,
        'plano_id' => $plano->id,
        'frequencia' => 'mensal',
    ]);

    $resp->assertOk()->assertJson(['success' => true]);
    $ass = MonitoramentoAssinatura::where('grupo_id', $grupo->id)->first();
    expect($ass)->not->toBeNull();
    expect($ass->alvoTipo())->toBe('grupo');
    expect($ass->status)->toBe('ativo');
    expect($ass->proxima_execucao_em)->not->toBeNull();
});

it('grupo de OUTRO usuário → 403', function () {
    $dono = User::factory()->create();
    $outro = User::factory()->create();
    $grupo = ParticipanteGrupo::create(['user_id' => $dono->id, 'nome' => 'G']);
    $plano = MonitoramentoPlano::porCodigo('licitacao') ?? MonitoramentoPlano::firstOrFail();

    actingAs($outro)->postJson(route('app.monitoramento.assinatura.criar'), [
        'grupo_id' => $grupo->id, 'plano_id' => $plano->id, 'frequencia' => 'mensal',
    ])->assertForbidden();
});

it('grupo + participante no mesmo request → 400', function () {
    $user = User::factory()->create();
    $grupo = ParticipanteGrupo::create(['user_id' => $user->id, 'nome' => 'G']);
    $p = \App\Models\Participante::create(['user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'P', 'uf' => 'SP']);
    $plano = MonitoramentoPlano::porCodigo('licitacao') ?? MonitoramentoPlano::firstOrFail();

    actingAs($user)->postJson(route('app.monitoramento.assinatura.criar'), [
        'grupo_id' => $grupo->id, 'participante_id' => $p->id,
        'plano_id' => $plano->id, 'frequencia' => 'mensal',
    ])->assertStatus(400);
});

it('grupo já monitorado → não duplica', function () {
    $user = User::factory()->trialAtivo()->create();
    $grupo = ParticipanteGrupo::create(['user_id' => $user->id, 'nome' => 'G']);
    $plano = MonitoramentoPlano::porCodigo('licitacao') ?? MonitoramentoPlano::firstOrFail();
    MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'grupo_id' => $grupo->id, 'plano_id' => $plano->id,
        'status' => 'ativo', 'frequencia_dias' => 30,
    ]);

    actingAs($user)->postJson(route('app.monitoramento.assinatura.criar'), [
        'grupo_id' => $grupo->id, 'plano_id' => $plano->id, 'frequencia' => 'mensal',
    ]);

    expect(MonitoramentoAssinatura::where('grupo_id', $grupo->id)->count())->toBe(1);
});
