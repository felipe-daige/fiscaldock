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

it('mostra clientes CNPJ consultados e CPF em seção própria de risco de crédito', function () {
    $user = User::factory()->create();

    // cliente CNPJ consultado -> deve aparecer em Consultados
    $cliente = \App\Models\Cliente::create([
        'user_id' => $user->id, 'documento' => '99888777000166', 'razao_social' => 'HIDRATOP LTDA',
    ]);
    // participante CPF não entra como CNPJ não consultado; aparece em seção própria.
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
        ->assertSee('Pessoas físicas — risco de crédito')
        ->assertSee('FULANO CPF')
        ->assertSee('Não avaliado');
});

it('detalhe de CPF trata risco de crédito sem oferecer Consulta CNPJ', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id,
        'documento' => '12345678901',
        'razao_social' => 'CPF COM MOVIMENTO',
    ]);

    $resp = actingAs($user)
        ->get("/app/score-fiscal/participante/{$part->id}")
        ->assertOk()
        ->assertSee('CPF: 123.456.789-01')
        ->assertSee('Risco de Crédito (CPF)')
        ->assertSee('Risco de crédito não avaliado')
        ->assertSee('Certidões e situação cadastral de CNPJ não se aplicam')
        ->assertDontSee('Atualizar via Consulta');

    expect($resp->getContent())->not->toContain('/app/consulta/painel?participantes='.$part->id);
});

it('ignora score fiscal legado de CPF e mantém a pessoa física como não avaliada', function () {
    $user = User::factory()->create();
    $cpf = Participante::create([
        'user_id' => $user->id,
        'documento' => '12345678901',
        'razao_social' => 'CPF COM SCORE LEGADO',
    ]);
    \App\Models\ParticipanteScore::create([
        'participante_id' => $cpf->id,
        'user_id' => $user->id,
        'score_total' => 50,
        'classificacao' => 'medio',
        'ultima_consulta_em' => now(),
    ]);

    $resp = actingAs($user)->get('/app/score-fiscal?cliente_id=todos')->assertOk();

    $resp->assertSee('CPF COM SCORE LEGADO')
        ->assertSee('Pessoas físicas — risco de crédito');

    $detalhe = actingAs($user)->get("/app/score-fiscal/participante/{$cpf->id}")->assertOk();
    $detalhe->assertSee('Risco de crédito não avaliado')->assertDontSee('Médio Risco');
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

it('origem NULL com notas EFD vinculadas deriva o tipo da importação e o card explica o cálculo', function () {
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

    $creditoEsperado = 'R$ '.number_format(1000 * (float) config('reforma.aliquota_referencia'), 2, ',', '.');
    actingAs($user)
        ->get("/app/score-fiscal/participante/{$part->id}")
        ->assertOk()
        ->assertSee('EFD ICMS/IPI')
        ->assertSee('Como é calculado')
        ->assertSee('Crédito potencial')
        // MEI: fator 0 → 100% do potencial, usando a alíquota configurada.
        ->assertSee($creditoEsperado)
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

it('card de risco crítico mostra o motivo ao lado do score', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id,
        'documento' => '11222333000181',
        'razao_social' => 'CRITICA LTDA',
    ]);

    \App\Models\ParticipanteScore::create([
        'participante_id' => $part->id,
        'user_id' => $user->id,
        'score_cadastral' => 100,
        'score_total' => 100,
        'classificacao' => 'critico',
        'ultima_consulta_em' => now(),
        'dados_consultados' => ['situacao_cadastral' => 'BAIXADA'],
    ]);

    actingAs($user)
        ->get('/app/score-fiscal?cliente_id=todos')
        ->assertOk()
        ->assertSee('CNPJs em Risco Crítico')
        ->assertSee('Score:')
        ->assertSee('Motivo:')
        ->assertSee('Situação cadastral: BAIXADA');
});

it('dashboard colore o score pela classificação persistida e não só pela faixa numérica', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id,
        'documento' => '11222333000181',
        'razao_social' => 'PISO ALTO LTDA',
    ]);

    \App\Models\ParticipanteScore::create([
        'participante_id' => $part->id,
        'user_id' => $user->id,
        'score_cadastral' => 0,
        'score_cnd_estadual' => 70,
        'score_total' => 15,
        'classificacao' => 'alto',
        'ultima_consulta_em' => now(),
    ]);

    $html = actingAs($user)
        ->get('/app/score-fiscal?cliente_id=todos')
        ->assertOk()
        ->getContent();

    expect($html)->toContain('style="color: #ea580c">15</span>')
        ->not->toContain('style="color: #047857">15</span>');
});

it('barra de filtros expansível aplica tipo, crédito e faixa de score', function () {
    $user = User::factory()->create();
    $cliente = \App\Models\Cliente::create([
        'user_id' => $user->id,
        'documento' => '99888777000166',
        'razao_social' => 'CLIENTE FILTRADO LTDA',
        'tipo_pessoa' => 'PJ',
    ]);
    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => '11222333000181',
        'razao_social' => 'PARTICIPANTE FORA LTDA',
    ]);

    \App\Models\ParticipanteScore::create([
        'cliente_id' => $cliente->id,
        'user_id' => $user->id,
        'score_total' => 30,
        'score_credito_reforma' => 50,
        'classificacao' => 'medio',
        'ultima_consulta_em' => now(),
    ]);
    \App\Models\ParticipanteScore::create([
        'participante_id' => $participante->id,
        'user_id' => $user->id,
        'score_total' => 30,
        'score_credito_reforma' => 50,
        'classificacao' => 'medio',
        'ultima_consulta_em' => now(),
    ]);

    actingAs($user)
        ->get('/app/score-fiscal?cliente_id=todos&status=consultados&tipo=cliente&credito=parcial&score_min=20&score_max=40')
        ->assertOk()
        ->assertSee('data-mobile-filters', false)
        ->assertSee('Crédito IBS/CBS')
        ->assertSee('value="20"', false)
        ->assertSee('value="40"', false)
        ->assertSee('CLIENTE FILTRADO LTDA')
        ->assertDontSee('PARTICIPANTE FORA LTDA');
});

it('cliente consultado também tem ver detalhes no Score Fiscal', function () {
    $user = User::factory()->create();
    $cliente = \App\Models\Cliente::create([
        'user_id' => $user->id,
        'documento' => '99888777000166',
        'razao_social' => 'CLIENTE DETALHE LTDA',
        'tipo_pessoa' => 'PJ',
        'uf' => 'SP',
    ]);

    \App\Models\ParticipanteScore::create([
        'cliente_id' => $cliente->id,
        'user_id' => $user->id,
        'score_total' => 0,
        'classificacao' => 'baixo',
        'ultima_consulta_em' => now(),
    ]);

    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => MonitoramentoPlano::porCodigo('licitacao')->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 10,
        'tab_id' => (string) Str::uuid(),
        'processado_em' => now(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'cliente_id' => $cliente->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'situacao_cadastral' => 'ATIVA',
            'regime_tributario' => 'Simples Nacional',
            'razao_social' => 'CLIENTE DETALHE LTDA',
            'cnd_federal' => ['status' => 'Negativa'],
        ],
        'consultado_em' => now(),
    ]);

    actingAs($user)
        ->get('/app/score-fiscal?cliente_id=todos')
        ->assertOk()
        ->assertSee('CLIENTE DETALHE LTDA')
        ->assertSee('data-detalhe-url="/app/score-fiscal/cliente/'.$cliente->id.'/detalhe"', false);

    $resp = actingAs($user)->getJson("/app/score-fiscal/cliente/{$cliente->id}/detalhe")->assertOk();

    expect($resp->json('html'))->toContain('CLIENTE DETALHE LTDA')
        ->toContain('Dados cadastrais')
        ->toContain('Situação cadastral: ATIVA')
        ->toContain('Regime tributário: Simples Nacional');
});

it('detalhe cadastral mostra regime não consultado quando o plano não trouxe o dado', function () {
    $html = view('autenticado.consulta.partials.detalhe-blocos', [
        'resumo' => null,
        'certidoes' => [],
        'cabecalho' => ['razao' => 'SEM REGIME LTDA', 'documento' => '11222333000181', 'situacao' => 'ATIVA'],
        'blocos' => [[
            'chave' => 'cadastro',
            'titulo' => 'Dados cadastrais',
            'badge' => ['label' => 'Ativa', 'hex' => '#047857'],
            'itens' => [
                ['label' => 'Situação cadastral', 'valor' => 'ATIVA', 'tooltip' => null],
                ['label' => 'Porte', 'valor' => 'DEMAIS', 'tooltip' => null],
            ],
            'listas' => [],
            'mensagem' => null,
        ]],
    ])->render();

    expect($html)->toContain('Situação cadastral: ATIVA')
        ->toContain('Regime tributário: Não consultado');
});

it('ver detalhes do cliente mostra dados cadastrais mesmo sem resultado de consulta direto', function () {
    $user = User::factory()->create();
    $cliente = \App\Models\Cliente::create([
        'user_id' => $user->id,
        'documento' => '99888777000166',
        'razao_social' => 'CLIENTE SEM RESULTADO LTDA',
        'tipo_pessoa' => 'PJ',
        'uf' => 'SP',
        'situacao_cadastral' => 'ATIVA',
        'regime_tributario' => 'Lucro Real',
    ]);

    \App\Models\ParticipanteScore::create([
        'cliente_id' => $cliente->id,
        'user_id' => $user->id,
        'score_total' => 15,
        'classificacao' => 'alto',
        'ultima_consulta_em' => now(),
    ]);

    actingAs($user)
        ->get('/app/score-fiscal?cliente_id=todos')
        ->assertOk()
        ->assertSee('data-detalhe-url="/app/score-fiscal/cliente/'.$cliente->id.'/detalhe"', false);

    $resp = actingAs($user)->getJson("/app/score-fiscal/cliente/{$cliente->id}/detalhe")->assertOk();

    expect($resp->json('html'))->toContain('CLIENTE SEM RESULTADO LTDA')
        ->toContain('Dados cadastrais')
        ->toContain('Situação cadastral: ATIVA')
        ->toContain('Regime tributário: Lucro Real')
        ->not->toContain('Sem consulta de certidões para este CNPJ');
});

it('detalhe inline usa badges compactos com tooltip', function () {
    $html = view('autenticado.consulta.partials.detalhe-blocos', [
        'resumo' => null,
        'certidoes' => [[
            'sigla' => 'FED',
            'glyph' => '✓',
            'titulo' => 'CND Federal',
            'label' => 'Positiva com efeitos de negativa',
            'hex' => '#047857',
            'descricao' => 'Certidão regular por efeitos de negativa.',
        ]],
        'blocos' => [[
            'chave' => 'cnd_federal',
            'titulo' => 'CND Federal',
            'badge' => ['label' => 'Positiva com efeitos de negativa', 'hex' => '#047857'],
            'itens' => [],
            'listas' => [],
            'mensagem' => 'Certidão regular por efeitos de negativa.',
        ]],
    ])->render();

    expect($html)->toContain('cert-tip hidden')
        ->toContain('title="CND Federal · Positiva com efeitos de negativa')
        ->toContain('P.E.N.');
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
