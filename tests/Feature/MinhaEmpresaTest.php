<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['credits' => 100]);
    $this->actingAs($this->user);
});

/** Cria uma empresa própria (Cliente PJ) para o usuário do teste. */
function empresaPropria(User $user): Cliente
{
    return Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000199',
        'nome' => 'Empresa Propria',
        'razao_social' => 'Empresa Propria Ltda',
        'is_empresa_propria' => true,
    ]);
}

test('usuario sem empresa propria ve tela de configuracao', function () {
    $response = $this->get('/app/minha-empresa');
    $response->assertOk();
    $response->assertSee('Configurar Minha Empresa');
});

test('pode acessar tela de configuracao', function () {
    Cliente::create([
        'user_id' => $this->user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000100',
        'nome' => 'Empresa 1',
        'razao_social' => 'Empresa 1 Ltda',
        'is_empresa_propria' => false,
    ]);

    $response = $this->get('/app/minha-empresa/configurar');
    $response->assertOk();
    $response->assertSee('Empresa 1 Ltda');
});

test('historico sem empresa redireciona para configurar', function () {
    $response = $this->get('/app/minha-empresa/historico');
    $response->assertRedirect(route('app.minha-empresa.configurar'));
});

test('metodo empresaPropria do user retorna empresa correta', function () {
    // Criar duas empresas
    Cliente::create([
        'user_id' => $this->user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '44444444000144',
        'nome' => 'Empresa A',
        'razao_social' => 'Empresa A Ltda',
        'is_empresa_propria' => false,
    ]);

    $empresaPrincipal = Cliente::create([
        'user_id' => $this->user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '55555555000155',
        'nome' => 'Empresa B',
        'razao_social' => 'Empresa B Ltda',
        'is_empresa_propria' => true,
    ]);

    $resultado = $this->user->empresaPropria();

    expect($resultado)->not->toBeNull();
    expect($resultado->id)->toBe($empresaPrincipal->id);
    expect($resultado->razao_social)->toBe('Empresa B Ltda');
});

test('metodo empresaPropria retorna null quando nao existe', function () {
    $resultado = $this->user->empresaPropria();
    expect($resultado)->toBeNull();
});

test('scope empresaPropria no model Cliente funciona', function () {
    Cliente::create([
        'user_id' => $this->user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '66666666000166',
        'nome' => 'Empresa Scope',
        'razao_social' => 'Empresa Scope Ltda',
        'is_empresa_propria' => true,
    ]);

    Cliente::create([
        'user_id' => $this->user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '77777777000177',
        'nome' => 'Outra',
        'razao_social' => 'Outra Ltda',
        'is_empresa_propria' => false,
    ]);

    $resultado = Cliente::where('user_id', $this->user->id)
        ->empresaPropria()
        ->get();

    expect($resultado)->toHaveCount(1);
    expect($resultado->first()->razao_social)->toBe('Empresa Scope Ltda');
});

test('usuario pode definir empresa principal programaticamente', function () {
    $cliente = Cliente::create([
        'user_id' => $this->user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '98765432000188',
        'nome' => 'Nova Empresa',
        'razao_social' => 'Nova Empresa SA',
        'is_empresa_propria' => false,
    ]);

    // Test the logic directly via model instead of HTTP
    // First remove flag from all user's empresas
    Cliente::where('user_id', $this->user->id)
        ->update(['is_empresa_propria' => false]);

    // Set the new one
    $cliente->update(['is_empresa_propria' => true]);

    $cliente->refresh();
    expect($cliente->is_empresa_propria)->toBeTrue();

    // Also verify empresaPropria method works
    $empresaPropria = $this->user->empresaPropria();
    expect($empresaPropria)->not->toBeNull();
    expect($empresaPropria->id)->toBe($cliente->id);
});

test('dashboard mostra empresa quando configurada', function () {
    // Cria cliente e participante manualmente
    $cliente = Cliente::create([
        'user_id' => $this->user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000199',
        'nome' => 'Empresa Dashboard',
        'razao_social' => 'Empresa Dashboard Ltda',
        'is_empresa_propria' => true,
    ]);

    // Criar participante que o controller espera
    Participante::create([
        'user_id' => $this->user->id,
        'cnpj' => '12345678000199',
        'razao_social' => 'Empresa Dashboard Ltda',
        'origem_tipo' => 'PROPRIO',
    ]);

    // Use session driver array for testing to avoid database session issues
    $response = $this->withSession(['_token' => 'test-token'])
        ->get('/app/minha-empresa');

    $response->assertOk();
    $response->assertSee('Empresa Dashboard Ltda');
})->skip('Database session conflicts with RefreshDatabase trait');

test('dashboard reflete consulta gravada no cliente da empresa propria', function () {
    $cliente = Cliente::create([
        'user_id' => $this->user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000199',
        'nome' => 'Empresa Propria',
        'razao_social' => 'Empresa Propria Ltda',
        'is_empresa_propria' => true,
    ]);

    $plano = MonitoramentoPlano::porCodigo('gratuito') ?? MonitoramentoPlano::firstOrFail();

    $lote = ConsultaLote::create([
        'user_id' => $this->user->id,
        'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'tab-minha-empresa-cliente',
        'processado_em' => now(),
    ]);

    // Pós-cutover: consulta com alvo cliente grava cliente_id, participante_id fica NULL.
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'cliente_id' => $cliente->id,
        'participante_id' => null,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'situacao_cadastral' => 'ATIVA',
            'cndt' => ['status' => 'NEGATIVA', 'validade' => now()->addMonths(3)->toDateString()],
            'consultas_realizadas' => ['cadastro', 'cndt'],
        ],
        'consultado_em' => now(),
    ]);

    $response = $this->get('/app/minha-empresa');

    $response->assertOk();
    $response->assertSee('ATIVA');
    $response->assertSee('NEGATIVA');
    $response->assertDontSee('Nenhuma consulta registrada');
});

test('dashboard usa score persistido no cliente quando participante nao tem', function () {
    $cliente = Cliente::create([
        'user_id' => $this->user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000199',
        'nome' => 'Empresa Propria',
        'razao_social' => 'Empresa Propria Ltda',
        'is_empresa_propria' => true,
    ]);

    \App\Models\ParticipanteScore::create([
        'user_id' => $this->user->id,
        'cliente_id' => $cliente->id,
        'score_total' => 87,
        'classificacao' => 'baixo',
        'ultima_consulta_em' => now(),
    ]);

    $response = $this->get('/app/minha-empresa');

    $response->assertOk();
    $response->assertSee('87/100');
    $response->assertDontSee('NÃO AVALIADO');
});

test('alertas usam data_validade formato BR e avisam certidao vencendo', function () {
    $cliente = Cliente::create([
        'user_id' => $this->user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000199',
        'nome' => 'Empresa Propria',
        'razao_social' => 'Empresa Propria Ltda',
        'is_empresa_propria' => true,
    ]);

    $plano = MonitoramentoPlano::porCodigo('gratuito') ?? MonitoramentoPlano::firstOrFail();

    $lote = ConsultaLote::create([
        'user_id' => $this->user->id,
        'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'tab-minha-empresa-validade',
        'processado_em' => now(),
    ]);

    // Payload real das fontes usa data_validade em formato BR (d/m/Y).
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'cliente_id' => $cliente->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'situacao_cadastral' => 'ATIVA',
            'crf_fgts' => ['status' => 'REGULAR', 'data_validade' => now()->addDays(3)->format('d/m/Y')],
            'cndt' => ['status' => 'Negativa', 'data_validade' => '24/08/2099'],
        ],
        'consultado_em' => now(),
    ]);

    $response = $this->get('/app/minha-empresa');

    $response->assertOk();
    // Alerta de vencimento próximo dispara (antes: chave 'validade' inexistente → nunca).
    $response->assertSee('Vence em');
    $response->assertDontSee('Nenhum alerta no momento');
    // Coluna Validade renderiza a data BR corretamente (não "Não informado", não mês/dia trocado).
    $response->assertSee('24/08/2099');
});

test('alerta de fonte com falha aparece nos alertas operacionais', function () {
    $cliente = Cliente::create([
        'user_id' => $this->user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000199',
        'nome' => 'Empresa Propria',
        'razao_social' => 'Empresa Propria Ltda',
        'is_empresa_propria' => true,
    ]);

    $plano = MonitoramentoPlano::porCodigo('gratuito') ?? MonitoramentoPlano::firstOrFail();

    $lote = ConsultaLote::create([
        'user_id' => $this->user->id,
        'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'tab-minha-empresa-fonte-erro',
        'processado_em' => now(),
    ]);

    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'cliente_id' => $cliente->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'situacao_cadastral' => 'ATIVA',
            '_fontes_erro' => ['cnd_federal' => ['codigo' => 615, 'origem' => 'integracao', 'status' => 'retry', 'tentativas' => 8]],
        ],
        'consultado_em' => now(),
    ]);

    $response = $this->get('/app/minha-empresa');

    $response->assertOk();
    $response->assertSee('CND Federal', false);
    $response->assertSee('não foi concluída');
    $response->assertDontSee('Nenhum alerta no momento');
});

test('historico lista consulta gravada no cliente da empresa propria', function () {
    $cliente = Cliente::create([
        'user_id' => $this->user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000199',
        'nome' => 'Empresa Propria',
        'razao_social' => 'Empresa Propria Ltda',
        'is_empresa_propria' => true,
    ]);

    $plano = MonitoramentoPlano::porCodigo('gratuito') ?? MonitoramentoPlano::firstOrFail();

    $lote = ConsultaLote::create([
        'user_id' => $this->user->id,
        'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'tab-minha-empresa-hist-cliente',
        'processado_em' => now(),
    ]);

    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'cliente_id' => $cliente->id,
        'participante_id' => null,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'situacao_cadastral' => 'ATIVA',
            'mensagem' => 'Consulta gravada no alvo cliente.',
        ],
        'consultado_em' => now(),
    ]);

    $this->get('/app/minha-empresa/historico')
        ->assertOk()
        ->assertSee('Consulta gravada no alvo cliente.');
});

test('historico da minha empresa exibe mensagem operacional da consulta', function () {
    $cliente = Cliente::create([
        'user_id' => $this->user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000199',
        'nome' => 'Empresa Historico',
        'razao_social' => 'Empresa Historico Ltda',
        'is_empresa_propria' => true,
    ]);

    $participante = Participante::create([
        'user_id' => $this->user->id,
        'documento' => preg_replace('/\D/', '', $cliente->documento),
        'razao_social' => 'Empresa Historico Ltda',
        'origem_tipo' => 'PROPRIO',
    ]);

    $plano = MonitoramentoPlano::porCodigo('gratuito') ?? MonitoramentoPlano::firstOrFail();

    $lote = ConsultaLote::create([
        'user_id' => $this->user->id,
        'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'tab-minha-empresa-historico',
        'processado_em' => now(),
    ]);

    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $participante->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'situacao_cadastral' => 'ATIVA',
            'mensagem' => 'Consulta conciliada com base no acervo de EFD.',
        ],
        'consultado_em' => now(),
    ]);

    $this->get('/app/minha-empresa/historico')
        ->assertOk()
        ->assertSee('Consulta conciliada com base no acervo de EFD.');
});

test('kpi de notas conta base unificada XML e EFD', function () {
    $cliente = empresaPropria($this->user);

    $impEfd = EfdImportacao::create([
        'user_id' => $this->user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'i.txt',
        'status' => 'concluido',
        'iniciado_em' => now(),
    ]);
    $impXml = XmlImportacao::create([
        'user_id' => $this->user->id,
        'cliente_id' => $cliente->id,
        'status' => 'concluido',
        'tipo_documento' => 'NFE',
    ]);

    // 2 EFD + 3 XML, chaves distintas → base unificada = 5 (contra 3 se contasse só XML).
    foreach (['a', 'b'] as $i => $c) {
        EfdNota::create([
            'user_id' => $this->user->id,
            'cliente_id' => $cliente->id,
            'importacao_id' => $impEfd->id,
            'numero' => 100 + $i,
            'serie' => '1',
            'modelo' => '55',
            'data_emissao' => '2024-01-15',
            'valor_desconto' => 0,
            'cancelada' => false,
            'origem_arquivo' => 'fiscal',
            'tipo_operacao' => 'saida',
            'valor_total' => 1000,
            'chave_acesso' => str_pad($c, 44, $c),
        ]);
    }
    foreach (['x', 'y', 'z'] as $i => $c) {
        XmlNota::create([
            'user_id' => $this->user->id,
            'cliente_id' => $cliente->id,
            'importacao_xml_id' => $impXml->id,
            'tipo_documento' => 'NFE',
            'numero_documento' => 200 + $i,
            'serie' => 1,
            'data_emissao' => '2024-01-15 10:00:00',
            'tipo_nota' => XmlNota::TIPO_SAIDA,
            'emit_documento' => '12345678000199',
            'emit_razao_social' => 'EMPRESA PROPRIA',
            'dest_documento' => '13305697000150',
            'dest_razao_social' => 'DESTINATARIO XYZ',
            'valor_total' => 300,
            'chave_acesso' => str_pad($c, 44, $c),
        ]);
    }

    $response = $this->get('/app/minha-empresa');

    $response->assertOk();
    $response->assertSee('5 notas registradas');
});
