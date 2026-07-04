<?php

use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\ParticipanteGrupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

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
