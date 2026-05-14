<?php

use App\Models\Cliente;
use App\Models\MonitoramentoConsulta;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\MonitoramentoPlanoSeeder::class);
    $this->user = User::factory()->create();
    $this->plano = MonitoramentoPlano::where('codigo', 'validacao')->first();
    $this->cliente = Cliente::create(['user_id' => $this->user->id, 'documento' => '12345678000190', 'razao_social' => 'C1']);
    $this->part = Participante::create(['user_id' => $this->user->id, 'documento' => '11222333000144', 'razao_social' => 'P1']);

    MonitoramentoConsulta::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente->id, 'plano_id' => $this->plano->id,
        'tipo' => 'assinatura', 'status' => 'sucesso', 'creditos_cobrados' => 5, 'executado_em' => now(),
    ]);
    MonitoramentoConsulta::create([
        'user_id' => $this->user->id, 'participante_id' => $this->part->id, 'plano_id' => $this->plano->id,
        'tipo' => 'assinatura', 'status' => 'sucesso', 'creditos_cobrados' => 5, 'executado_em' => now(),
    ]);
});

it('lista todas por padrão', function () {
    $r = $this->actingAs($this->user)->get('/app/monitoramento/historico');
    $r->assertOk()->assertSee('C1')->assertSee('P1');
});

it('filtra só clientes', function () {
    $r = $this->actingAs($this->user)->get('/app/monitoramento/historico?tipo=cliente');
    $r->assertOk()->assertSee('C1')->assertDontSee('P1');
});

it('filtra só participantes', function () {
    $r = $this->actingAs($this->user)->get('/app/monitoramento/historico?tipo=participante');
    $r->assertOk()->assertSee('P1')->assertDontSee('C1');
});

it('renderiza sub-abas com contagens', function () {
    $r = $this->actingAs($this->user)->get('/app/monitoramento/historico');
    $r->assertOk()
        ->assertSee('Tudo')
        ->assertSee('Clientes')
        ->assertSee('Participantes');
    expect($r->viewData('contagens'))->toMatchArray(['tudo' => 2, 'cliente' => 1, 'participante' => 1]);
});
