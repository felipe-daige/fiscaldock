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
        ->assertSee('Filtrar')
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
        ->get('/app/score-fiscal?cliente_id=todos')
        ->assertOk()
        ->assertSee('Consultados')
        ->assertSee('Não consultados')
        ->assertSee('AVALIADA LTDA')
        ->assertSee('PENDENTE LTDA');
});

it('visualizacao por cliente e obrigatoria, com opcao Todos os CNPJs', function () {
    $user = User::factory()->create();
    $ep = \App\Models\Cliente::create(['user_id' => $user->id, 'documento' => '10000000000100', 'razao_social' => 'EMPRESA PROPRIA', 'is_empresa_propria' => true]);
    $cliA = \App\Models\Cliente::create(['user_id' => $user->id, 'documento' => '20000000000200', 'razao_social' => 'CLIENTE A']);
    $cliB = \App\Models\Cliente::create(['user_id' => $user->id, 'documento' => '30000000000300', 'razao_social' => 'CLIENTE B']);

    $pa = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliA->id, 'documento' => '44444444000144', 'razao_social' => 'PARTDOA']);
    $pb = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliB->id, 'documento' => '55555555000155', 'razao_social' => 'PARTDOB']);
    foreach ([$pa->id, $pb->id] as $pid) {
        \App\Models\ParticipanteScore::create([
            'participante_id' => $pid, 'user_id' => $user->id,
            'score_total' => 0, 'classificacao' => 'baixo', 'ultima_consulta_em' => now(),
        ]);
    }

    // default = Todos os CNPJs -> mostra todos
    actingAs($user)->get('/app/score-fiscal')
        ->assertOk()->assertSee('Todos os CNPJs')
        ->assertSee('PARTDOA')->assertSee('PARTDOB');

    // cliente A -> só os do A
    actingAs($user)->get('/app/score-fiscal?cliente_id='.$cliA->id)
        ->assertOk()->assertSee('PARTDOA')->assertDontSee('PARTDOB');

    // todos explícito -> ambos
    actingAs($user)->get('/app/score-fiscal?cliente_id=todos')
        ->assertOk()->assertSee('PARTDOA')->assertSee('PARTDOB');
});

it('marca o papel do participante (fornecedor/comprador/ambos) pelas notas EFD', function () {
    $user = User::factory()->create();
    $imp = \App\Models\EfdImportacao::create(['user_id' => $user->id, 'tipo_efd' => 'EFD ICMS/IPI']);
    $cli = \App\Models\Cliente::create(['user_id' => $user->id, 'documento' => '11111111000111', 'razao_social' => 'MINHA EMP']);

    // Fornecedor: nos compramos dele (entrada)
    $forn = Participante::create(['user_id' => $user->id, 'documento' => '22222222000122', 'razao_social' => 'FORNECEDOR SA']);
    // Ambos: entrada + saida
    $ambos = Participante::create(['user_id' => $user->id, 'documento' => '33333333000133', 'razao_social' => 'PARCEIRO SA']);

    foreach ([$forn->id => ['entrada'], $ambos->id => ['entrada', 'saida']] as $pid => $ops) {
        foreach ($ops as $i => $op) {
            \Illuminate\Support\Facades\DB::table('efd_notas')->insert([
                'user_id' => $user->id, 'cliente_id' => $cli->id, 'importacao_id' => $imp->id,
                'participante_id' => $pid, 'modelo' => '55', 'numero' => (string) ($pid + $i),
                'tipo_operacao' => $op, 'valor_total' => 100,
            ]);
        }
    }

    // ambos têm score (aparecem em Consultados)
    foreach ([$forn->id, $ambos->id] as $pid) {
        \App\Models\ParticipanteScore::create([
            'participante_id' => $pid, 'user_id' => $user->id,
            'score_total' => 0, 'classificacao' => 'baixo', 'ultima_consulta_em' => now(),
        ]);
    }

    actingAs($user)
        ->get('/app/score-fiscal?cliente_id=todos')
        ->assertOk()
        ->assertSee('Fornecedor')
        ->assertSee('Ambos');
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
        ->get('/app/score-fiscal?cliente_id=todos')
        ->assertOk()
        ->assertSee('HIDRATOP LTDA')
        ->assertSee('Cliente')
        ->assertDontSee('FULANO CPF');
});

it('exclui o participante PROPRIO (empresa propria duplicada) da listagem, mantendo o cliente', function () {
    $user = User::factory()->create();

    // Empresa própria como CLIENTE (representação correta) — deve aparecer.
    \App\Models\Cliente::create([
        'user_id' => $user->id, 'documento' => '63112970000107',
        'razao_social' => 'MINHA EMPRESA CLIENTE', 'is_empresa_propria' => true,
    ]);

    // Mesma empresa duplicada como PARTICIPANTE origem PROPRIO (criada pela tela Minha Empresa),
    // sem score → cairia em "Não consultados". Não deve aparecer.
    Participante::create([
        'user_id' => $user->id, 'documento' => '63112970000107',
        'razao_social' => 'MINHA EMPRESA PARTICIPANTE', 'origem_tipo' => 'PROPRIO',
    ]);

    actingAs($user)
        ->get('/app/score-fiscal?cliente_id=todos')
        ->assertOk()
        ->assertSee('MINHA EMPRESA CLIENTE')
        ->assertDontSee('MINHA EMPRESA PARTICIPANTE');
});

it('exclui o participante PROPRIO mesmo quando ja tem score (Consultados)', function () {
    $user = User::factory()->create();

    \App\Models\Cliente::create([
        'user_id' => $user->id, 'documento' => '63112970000107',
        'razao_social' => 'PROPRIA CLIENTE', 'is_empresa_propria' => true,
    ]);

    $partProprio = Participante::create([
        'user_id' => $user->id, 'documento' => '63112970000107',
        'razao_social' => 'PROPRIA PARTICIPANTE', 'origem_tipo' => 'PROPRIO',
    ]);
    \App\Models\ParticipanteScore::create([
        'participante_id' => $partProprio->id, 'user_id' => $user->id,
        'score_total' => 50, 'classificacao' => 'medio', 'ultima_consulta_em' => now(),
    ]);

    actingAs($user)
        ->get('/app/score-fiscal?cliente_id=todos')
        ->assertOk()
        ->assertDontSee('PROPRIA PARTICIPANTE');
});

it('o detalhe do participante mostra subscores avaliados', function () {
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
        ->assertSee('Situação Cadastral');
});

it('detalhe exibe certidões estruturadas da última consulta (não JSON cru) e badge de situação', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'ACME LTDA',
        'situacao_cadastral' => 'BAIXADA',
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
            'situacao_cadastral' => 'BAIXADA',
            'cnd_federal' => ['status' => 'Negativa'],
        ],
    ]);
    app(FecharLoteService::class)->fechar($lote->id);

    $resp = actingAs($user)
        ->get("/app/score-fiscal/participante/{$part->id}")
        ->assertOk()
        ->assertSee('Última Consulta — Certidões e Cadastro')
        ->assertSee('BAIXADA')          // badge de situação
        ->assertSee('Ficha completa');  // link pra ficha do participante

    // JSON cru de dados_consultados não aparece mais
    expect($resp->getContent())->not->toContain('JSON_PRETTY_PRINT')
        ->and($resp->getContent())->not->toContain('"situacao_cadastral":');
});

it('detalhe explica o piso quando a classificação persistida supera a faixa numérica', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'PISO LTDA',
    ]);
    // Score numérico baixo (14) mas classificação persistida 'alto' (piso por CND positiva)
    \App\Models\ParticipanteScore::create([
        'participante_id' => $part->id, 'user_id' => $user->id,
        'score_cadastral' => 0, 'score_cnd_federal' => 70,
        'score_total' => 14, 'classificacao' => 'alto', 'ultima_consulta_em' => now(),
    ]);

    actingAs($user)
        ->get("/app/score-fiscal/participante/{$part->id}")
        ->assertOk()
        ->assertSee('Alto Risco')
        ->assertSee('Classificação elevada por irregularidade conhecida');
});

it('origem NULL com notas EFD vinculadas exibe "EFD (SPED importado)" e o card explica o cálculo', function () {
    $user = User::factory()->create();
    $cliente = \App\Models\Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true, 'tipo_pessoa' => 'PJ',
        'documento' => '00000000000191', 'razao_social' => 'Propria',
    ]);
    // origem_tipo NULL = como a extração EFD (n8n) cria
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'FORN EFD LTDA',
        'regime_tributario' => 'MEI',
    ]);
    \App\Models\EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
    ]);
    \App\Models\EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'participante_id' => $part->id,
        'importacao_id' => \App\Models\EfdImportacao::first()->id,
        'chave_acesso' => str_pad('77', 44, '0', STR_PAD_LEFT), 'modelo' => '55', 'numero' => 77, 'serie' => '0',
        'data_emissao' => '2026-01-15', 'tipo_operacao' => 'entrada', 'valor_total' => 1000,
        'valor_desconto' => 0, 'origem_arquivo' => 'fiscal', 'metadados' => [],
    ]);

    actingAs($user)
        ->get("/app/score-fiscal/participante/{$part->id}")
        ->assertOk()
        ->assertSee('EFD (SPED importado)')
        ->assertSee('Como é calculado')
        ->assertSee('Crédito potencial')
        // MEI: fator 0 → em risco = 100% do potencial (1000 × 28,5% = 285)
        ->assertSee('R$ 285,00')
        // Período coberto pelo volume (nota única de 01/2026)
        ->assertSee('Emissões de 01/2026')
        ->assertSee('acumulado do período');
});

it('detalhe tem consulta pré-selecionada, dossiê PDF, papel e metodologia expansível', function () {
    $user = User::factory()->create();
    $cliente = \App\Models\Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true, 'tipo_pessoa' => 'PJ',
        'documento' => '00000000000191', 'razao_social' => 'Propria',
    ]);
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'FORN LTDA',
        'situacao_cadastral' => 'BAIXADA',
    ]);
    \App\Models\ParticipanteScore::create([
        'participante_id' => $part->id, 'user_id' => $user->id,
        'score_cadastral' => 100, 'score_total' => 100, 'classificacao' => 'critico', 'ultima_consulta_em' => now(),
    ]);
    $imp = \App\Models\EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
    ]);
    \App\Models\EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'participante_id' => $part->id,
        'importacao_id' => $imp->id,
        'chave_acesso' => str_pad('88', 44, '0', STR_PAD_LEFT), 'modelo' => '55', 'numero' => 88, 'serie' => '0',
        'data_emissao' => '2026-01-15', 'tipo_operacao' => 'entrada', 'valor_total' => 500,
        'valor_desconto' => 0, 'origem_arquivo' => 'fiscal', 'metadados' => [],
    ]);

    $resp = actingAs($user)
        ->get("/app/score-fiscal/participante/{$part->id}")
        ->assertOk()
        // Consulta pré-preenchida (rota /painel — /app/consulta cru dava 404)
        ->assertSee('/app/consulta/painel?participantes='.$part->id, false)
        // Dossiê PDF sem data-link (download)
        ->assertSee('/app/participante/'.$part->id.'/dossie', false)
        ->assertSee('Dossiê PDF')
        // Papel derivado das notas EFD (entrada = fornecedor)
        ->assertSee('Fornecedor')
        // Metodologia expansível
        ->assertSee('Como o risco é classificado')
        ->assertSee('Piso por irregularidade conhecida')
        // BAIXADA no vermelho crítico, não cinza
        ->assertSee('background-color: #b91c1c', false);

    expect($resp->getContent())->not->toContain('href="/app/consulta"');
});

it('origem NULL sem vínculo EFD segue exibindo traço', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'AVULSO LTDA',
    ]);

    actingAs($user)
        ->get("/app/score-fiscal/participante/{$part->id}")
        ->assertOk()
        ->assertDontSee('EFD (SPED importado)');
});

it('o dashboard mostra o explicador de Crédito IBS/CBS da Reforma', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/app/score-fiscal')
        ->assertOk()
        ->assertSee('Crédito IBS/CBS na Reforma Tributária', false)
        ->assertSee('LC 214/2025');
});

it('a tela de detalhe mostra o cartão de crédito IBS/CBS do fornecedor', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'FORN MEI LTDA', 'regime_tributario' => 'MEI',
    ]);

    actingAs($user)
        ->get("/app/score-fiscal/participante/{$part->id}")
        ->assertOk()
        ->assertSee('Crédito IBS/CBS', false)
        ->assertSee('Não gera crédito', false);
});
