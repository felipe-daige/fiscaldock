<?php

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use App\Services\Consultas\FecharLoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('a rota score-fiscal renderiza o dashboard real (nao mais placeholder)', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/app/score-fiscal')
        ->assertOk()
        ->assertSee('Score Fiscal')
        ->assertSee('Como funciona o Score Fiscal')
        ->assertSee('Avaliados');
});

it('separa participantes em Consultados e Nao consultados', function () {
    $user = User::factory()->create();

    $semScore = Participante::create([
        'user_id' => $user->id, 'documento' => '55444333000122', 'razao_social' => 'PENDENTE LTDA',
    ]);
    $comScore = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'AVALIADA LTDA',
    ]);

    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => MonitoramentoPlano::porCodigo('licitacao')->id,
        'status' => ConsultaLote::STATUS_PROCESSANDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 10,
        'tab_id' => (string) Str::uuid(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $comScore->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['situacao_cadastral' => 'ATIVA'],
    ]);
    app(FecharLoteService::class)->fechar($lote->id);

    actingAs($user)
        ->get('/app/score-fiscal')
        ->assertOk()
        ->assertSee('Consultados')
        ->assertSee('Não consultados')
        ->assertSee('AVALIADA LTDA')
        ->assertSee('PENDENTE LTDA');
});

it('mostra clientes consultados no score e exclui CPF da lista', function () {
    $user = User::factory()->create();

    // cliente CNPJ consultado -> deve aparecer em Consultados
    $cliente = \App\Models\Cliente::create([
        'user_id' => $user->id, 'documento' => '99888777000166', 'razao_social' => 'HIDRATOP LTDA',
    ]);
    // participante CPF não consultado -> NÃO deve aparecer (só CNPJ)
    Participante::create([
        'user_id' => $user->id, 'documento' => '12345678901', 'razao_social' => 'FULANO CPF',
    ]);

    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => MonitoramentoPlano::porCodigo('due_diligence')->id,
        'status' => ConsultaLote::STATUS_PROCESSANDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 35,
        'tab_id' => (string) Str::uuid(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'cliente_id' => $cliente->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['situacao_cadastral' => 'ATIVA', 'cnd_federal' => ['status' => 'Negativa']],
    ]);
    app(FecharLoteService::class)->fechar($lote->id);

    actingAs($user)
        ->get('/app/score-fiscal')
        ->assertOk()
        ->assertSee('HIDRATOP LTDA')
        ->assertSee('Cliente')
        ->assertDontSee('FULANO CPF');
});

it('o detalhe do participante mostra subscores avaliados e ESG/Protestos em breve', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'ACME LTDA',
    ]);

    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => MonitoramentoPlano::porCodigo('due_diligence')->id,
        'status' => ConsultaLote::STATUS_PROCESSANDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 35,
        'tab_id' => (string) Str::uuid(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $part->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'situacao_cadastral' => 'ATIVA',
            'cnd_federal' => ['status' => 'Negativa'],
        ],
    ]);
    app(FecharLoteService::class)->fechar($lote->id);

    actingAs($user)
        ->get("/app/score-fiscal/participante/{$part->id}")
        ->assertOk()
        ->assertSee('Em breve')
        ->assertSee('Situação Cadastral');
});
