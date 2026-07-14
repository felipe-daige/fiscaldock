<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\CteConsulta;
use App\Models\NfeConsulta;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use App\Models\User;
use App\Services\Consultas\FecharLoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('remove a lateral do cliente e mostra as consultas CNPJ e clearance no card final', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000195',
        'razao_social' => 'Cliente Histórico Ltda',
        'is_empresa_propria' => false,
        'ativo' => true,
    ]);

    $plano = App\Models\MonitoramentoPlano::create([
        'codigo' => 'historico_cliente',
        'nome' => 'Compliance Histórico',
        'descricao' => 'Plano de teste',
        'custo_creditos' => 0,
        'ativo' => true,
        'consultas_incluidas' => ['situacao_cadastral'],
    ]);
    $loteCnpj = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'processado_em' => now()->subDay(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $loteCnpj->id,
        'cliente_id' => $cliente->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['situacao_cadastral' => 'ATIVA'],
        'consultado_em' => now()->subDay(),
    ]);

    $loteClearance = ConsultaLote::create([
        'user_id' => $user->id,
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'resultado_resumo' => ['tier' => 'basico', 'fluxo_origem' => 'lote'],
        'processado_em' => now(),
    ]);
    $chave = str_repeat('7', 44);
    NfeConsulta::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'consulta_lote_id' => $loteClearance->id,
        'chave_acesso' => $chave,
        'tipo_documento' => 'NFE',
        'modelo' => '55',
        'numero' => '9876',
        'status' => 'AUTORIZADA',
        'emit_cnpj' => $cliente->documento,
        'emit_nome' => $cliente->razao_social,
        'consultado_em' => now(),
    ]);

    $outroUser = User::factory()->create();
    NfeConsulta::create([
        'user_id' => $outroUser->id,
        'chave_acesso' => str_repeat('8', 44),
        'tipo_documento' => 'NFE',
        'status' => 'AUTORIZADA',
        'emit_cnpj' => $cliente->documento,
        'emit_nome' => 'NÃO PODE VAZAR',
        'consultado_em' => now()->addMinute(),
    ]);

    $response = $this->actingAs($user)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get('/app/cliente/'.$cliente->id);

    $response->assertOk()
        ->assertSee('Identificação, contato, localização e enquadramento fiscal.')
        ->assertSee('Últimas Consultas deste CNPJ')
        ->assertSeeInOrder(['Clearance NF-e', 'Consulta CNPJ'])
        ->assertSee('NF-e nº 9876')
        ->assertSee($chave)
        ->assertSee('/app/clearance/notas/resultado/'.$loteClearance->id, false)
        ->assertSee('/app/consulta/lote/'.$loteCnpj->id, false)
        ->assertDontSee('Situação do Cadastro')
        ->assertDontSee('Metadados')
        ->assertDontSee('perfil-grid', false)
        ->assertDontSee('NÃO PODE VAZAR');
});

it('pagina o histórico unificado em grupos de cinco consultas', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '45997418000153',
        'razao_social' => 'Cliente com Histórico Longo Ltda',
        'is_empresa_propria' => false,
        'ativo' => true,
    ]);

    foreach (range(1, 7) as $indice) {
        NfeConsulta::create([
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
            'chave_acesso' => str_pad((string) $indice, 44, (string) $indice),
            'tipo_documento' => 'NFE',
            'modelo' => '55',
            'numero' => 'PAG-0'.$indice,
            'status' => 'AUTORIZADA',
            'emit_cnpj' => $cliente->documento,
            'emit_nome' => $cliente->razao_social,
            'consultado_em' => now()->subMinutes($indice),
        ]);
    }

    $primeiraPagina = $this->actingAs($user)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get('/app/cliente/'.$cliente->id);

    $primeiraPagina->assertOk()
        ->assertSee('7 consultas')
        ->assertSee('Exibindo 1–5 de 7')
        ->assertSeeInOrder(['NF-e nº PAG-01', 'NF-e nº PAG-05'])
        ->assertDontSee('NF-e nº PAG-06')
        ->assertSee('consultas_page=2', false)
        ->assertSee('Próxima');

    $segundaPagina = $this->actingAs($user)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get('/app/cliente/'.$cliente->id.'?consultas_page=2');

    $segundaPagina->assertOk()
        ->assertSee('Exibindo 6–7 de 7')
        ->assertSeeInOrder(['NF-e nº PAG-06', 'NF-e nº PAG-07'])
        ->assertDontSee('NF-e nº PAG-05')
        ->assertSee('Anterior');
});

it('clearance completo alimenta o histórico e os cards fiscais do participante', function () {
    $user = User::factory()->create();
    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => '11444777000161',
        'razao_social' => 'Fornecedor Consultado Ltda',
        'origem_tipo' => 'NFE',
        'uf' => 'SP',
        'latitude' => -23.5505,
        'longitude' => -46.6333,
    ]);

    $loteClearance = ConsultaLote::create([
        'user_id' => $user->id,
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'resultado_resumo' => ['tier' => 'full', 'fluxo_origem' => 'lote'],
        'processado_em' => now()->subMinute(),
    ]);
    $chave = str_repeat('4', 44);
    NfeConsulta::create([
        'user_id' => $user->id,
        'consulta_lote_id' => $loteClearance->id,
        'chave_acesso' => $chave,
        'tipo_documento' => 'NFE',
        'modelo' => '55',
        'numero' => '4455',
        'status' => 'AUTORIZADA',
        'emit_cnpj' => $participante->documento,
        'emit_nome' => $participante->razao_social,
        'dest_cnpj' => '00000000000191',
        'consultado_em' => now()->subMinute(),
    ]);

    // Resultado produzido pelo braço de regularidade do Clearance completo. O fechamento usa
    // exatamente este caminho para atualizar participante_scores e a ficha cadastral.
    $loteRegularidade = ConsultaLote::create([
        'user_id' => $user->id,
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'processado_em' => now(),
    ]);
    $loteRegularidade->participantes()->attach($participante->id);
    ConsultaResultado::create([
        'consulta_lote_id' => $loteRegularidade->id,
        'participante_id' => $participante->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'consultado_em' => now(),
        'resultado_dados' => [
            'consultas_realizadas' => ['situacao_cadastral', 'cnd_federal', 'sintegra'],
            'situacao_cadastral' => 'ATIVA',
            'razao_social' => $participante->razao_social,
            'cnd_federal' => ['status' => 'Negativa'],
            'sintegra' => ['situacao' => 'Habilitada', 'inscricao_estadual' => '110042490114'],
        ],
    ]);

    app(FecharLoteService::class)->persistirScores($loteRegularidade->id);

    expect(ParticipanteScore::where('participante_id', $participante->id)->exists())->toBeTrue()
        ->and($participante->fresh()->ultima_consulta_em)->not->toBeNull();

    $response = $this->actingAs($user)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get('/app/participante/'.$participante->id);

    $response->assertOk()
        ->assertSee('Identificação, localização e enquadramento do participante.')
        ->assertSee('Últimas Consultas deste CNPJ')
        ->assertSee('Clearance NF-e')
        ->assertSee('NF-e nº 4455')
        ->assertSee($chave)
        ->assertSee('Consulta CNPJ')
        ->assertSee('Certidões e Cadastros Fiscais')
        ->assertSee('CND Federal (Receita/PGFN)')
        ->assertSee('SINTEGRA');
});

it('reconhece participante em todos os papéis do CT-e sem vazar DF-e para CPF', function () {
    $user = User::factory()->create();
    $tomador = Participante::create([
        'user_id' => $user->id,
        'documento' => '19131243000197',
        'razao_social' => 'Tomador do CT-e',
        'origem_tipo' => 'CTE',
    ]);
    $cpf = Participante::create([
        'user_id' => $user->id,
        'documento' => '12345678901',
        'razao_social' => 'Pessoa Física',
        'origem_tipo' => 'MANUAL',
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'resultado_resumo' => ['tier' => 'basico', 'fluxo_origem' => 'lote'],
    ]);
    CteConsulta::create([
        'user_id' => $user->id,
        'consulta_lote_id' => $lote->id,
        'chave_acesso' => str_repeat('5', 44),
        'tipo_documento' => 'CTE',
        'modelo' => '57',
        'numero' => '321',
        'status' => 'AUTORIZADA',
        'emit_cnpj' => '11444777000161',
        'tomador_cnpj' => $tomador->documento,
        'tomador_nome' => $tomador->razao_social,
        'consultado_em' => now(),
    ]);

    $service = app(App\Services\Consultas\PerfilConsultaHistoricoService::class);
    $historicoTomador = $service->paraParticipante($tomador);
    $historicoCpf = $service->paraParticipante($cpf);

    expect($historicoTomador->total())->toBe(1)
        ->and($historicoTomador->first()['origem_label'])->toBe('Clearance CT-e')
        ->and($historicoTomador->first()['descricao'])->toContain('Tomador')
        ->and($historicoCpf->total())->toBe(0)
        ->and($historicoCpf->count())->toBe(0);
});
