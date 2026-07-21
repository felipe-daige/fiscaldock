<?php

use App\Services\Consultas\FonteRegistry;
use App\Services\Consultas\Fontes\Advocacia\CeatTrtFonte;
use App\Services\Consultas\Fontes\Advocacia\CertidaoStjFonte;
use App\Services\Consultas\Fontes\Advocacia\ProtestosFonte;

beforeEach(function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
});

test('as 11 fontes advocacia estao registradas e prontas', function () {
    $registry = app(FonteRegistry::class);
    $chaves = ['certidao_stj', 'certidao_trf', 'ceat_trt', 'certidao_mpt', 'certidao_mpf',
        'certidao_tcu', 'improbidade', 'ceis', 'cnep', 'protestos', 'falencias'];

    foreach ($chaves as $chave) {
        $fonte = $registry->get($chave);
        expect($fonte)->not->toBeNull("fonte {$chave} ausente do registry")
            ->and($fonte->pronta())->toBeTrue()
            ->and($fonte->provider())->toBe('infosimples'); // herda o throttle 1 req/s do job
    }
});

test('catalogo avulso agora expoe 17 fontes em 4 grupos', function () {
    $grupos = app(\App\Services\Advocacia\CatalogoFontesAvulsas::class)->grupos();

    expect(array_keys($grupos))->toBe(['judicial', 'integridade', 'passivo', 'fiscal'])
        ->and(count($grupos['judicial']['fontes']))->toBe(5)
        ->and(count($grupos['integridade']['fontes']))->toBe(4)
        ->and(count($grupos['passivo']['fontes']))->toBe(2)
        ->and(count($grupos['fiscal']['fontes']))->toBe(6);

    // Todas a R$ 1,00 default.
    foreach ($grupos as $g) {
        foreach ($g['fontes'] as $f) {
            expect($f['preco'])->toBe(1.00);
        }
    }
});

test('etapas dinamicas incluem os grupos novos em ordem canonica', function () {
    $catalogo = app(\App\Services\Advocacia\CatalogoFontesAvulsas::class);

    $etapas = $catalogo->etapasDe(['certidao_stj', 'protestos', 'ceis', 'cnd_federal']);

    expect(array_column($etapas, 'chave'))->toBe([
        'inicializacao', 'cadastrais', 'certidoes_federais', 'certidoes_judiciais', 'integridade', 'passivo',
    ])->and(array_column($etapas, 'numero'))->toBe([1, 2, 3, 4, 5, 6]);
});

test('certidao STJ normaliza sucesso negativa e 611 indeterminado', function () {
    $fonte = new CertidaoStjFonte;

    $ok = $fonte->normalizar(['data' => [[
        'tipo' => 'Negativa', 'numero_certidao' => 'STJ-123', 'emissao_data' => '21/07/2026',
        'site_receipt' => 'https://infosimples/receipt.pdf',
    ]]], 'sucesso');
    expect($ok['certidao_stj']['status'])->toBe('Negativa')
        ->and($ok['certidao_stj']['certidao_codigo'])->toBe('STJ-123')
        ->and($ok['certidao_stj']['comprovante'])->toBe('https://infosimples/receipt.pdf')
        ->and($ok['consultas_realizadas'])->toBe(['certidao_stj']);

    $ind = $fonte->normalizar(['code_message' => 'dados incompletos'], 'indeterminado');
    expect($ind['certidao_stj']['status'])->toBe('INDETERMINADO');

    expect($fonte->normalizar(['code' => 605], 'retry'))->toBe([]);
});

test('CEAT resolve o TRT pela UF, exige nome e manda nome+cnpj nos params', function () {
    $fonte = new CeatTrtFonte;

    expect($fonte->slugPara(['uf' => 'RJ']))->toBe('tribunal/trt1/ceat')
        ->and($fonte->slugPara(['uf' => 'sp']))->toBe('tribunal/trt2/ceat')
        ->and($fonte->slugPara(['uf' => 'MS']))->toBe('tribunal/trt24/ceat')
        ->and($fonte->slugPara(['uf' => 'AP']))->toBe('tribunal/trt8/ceat')
        ->and($fonte->aplicavelPara(['uf' => 'MG', 'razao_social' => 'ACME LTDA']))->toBeTrue()
        // `nome` é obrigatório no endpoint (606 billable sem ele — smoke lote 260): sem razão
        // social a fonte fica INDISPONIVEL sem chamar nem cobrar.
        ->and($fonte->aplicavelPara(['uf' => 'MG']))->toBeFalse()
        ->and($fonte->aplicavelPara(['uf' => '', 'razao_social' => 'ACME']))->toBeFalse()
        ->and($fonte->aplicavelPara([]))->toBeFalse();

    expect($fonte->params(['cnpj' => '19.131.243/0001-97', 'razao_social' => 'ACME LTDA']))
        ->toBe(['cnpj' => '19131243000197', 'nome' => 'ACME LTDA']);

    // Sem UF/nome o job persiste INDISPONIVEL com o motivo — sem chamada nem cobrança.
    $bloco = $fonte->normalizar(['_motivo' => $fonte->motivoIndisponivel([])], 'nao_aplicavel');
    expect($bloco['ceat_trt']['status'])->toBe('INDISPONIVEL');
});

test('CEAT normaliza o contrato real (nada consta, processos do CNPJ, expedicao)', function () {
    $bloco = (new CeatTrtFonte)->normalizar(['data' => [[
        'conseguiu_emitir_certidao_negativa' => false,
        'nada_consta' => false,
        'numero_certidao' => '7654321',
        'normalizado_expedicao_datahora' => '20/07/2026 14:30:00',
        'total_processos' => 6,
        'processos_encontrados_cpf_cnpj' => ['quantidade' => 2, 'lista_processos' => ['111', '222']],
        'processos_encontrados_nome' => ['quantidade' => 4, 'lista_processos' => ['a', 'b', 'c', 'd']],
    ]]], 'sucesso');

    expect($bloco['ceat_trt']['status'])->toBe('Positiva')
        ->and($bloco['ceat_trt']['certidao_codigo'])->toBe('7654321')
        ->and($bloco['ceat_trt']['emissao_data'])->toBe('20/07/2026')
        ->and($bloco['ceat_trt']['processos_cnpj_quantidade'])->toBe(2)
        ->and($bloco['ceat_trt']['processos_cnpj'])->toBe(['111', '222'])
        ->and($bloco['ceat_trt']['total_processos'])->toBe(6);
});

test('MPT exige UF, manda uf nos params e deriva status de nada_consta + procedimentos', function () {
    $fonte = new \App\Services\Consultas\Fontes\Advocacia\CertidaoMptFonte;

    expect($fonte->aplicavelPara(['uf' => 'SP']))->toBeTrue()
        ->and($fonte->aplicavelPara(['uf' => '']))->toBeFalse()
        ->and($fonte->params(['cnpj' => '19131243000197', 'uf' => 'sp']))
        ->toBe(['cnpj' => '19131243000197', 'uf' => 'SP']);

    $bloco = $fonte->normalizar(['data' => [[
        'nada_consta' => false,
        'titulo' => 'CERTIDÃO DE FEITOS PARA FINS GERAIS',
        'codigo' => '111',
        'procedimentos' => [
            ['ano_autuacao' => '2024', 'classe' => 'PAJ', 'numero' => '123', 'normalizado_numero' => '0123', 'situacao' => 'ATIVO', 'partes_polo_passivo' => ['X']],
        ],
    ]]], 'sucesso');

    expect($bloco['certidao_mpt']['status'])->toBe('Positiva')
        ->and($bloco['certidao_mpt']['total_procedimentos'])->toBe(1)
        ->and($bloco['certidao_mpt']['procedimentos'][0])->toBe(['ano_autuacao' => '2024', 'classe' => 'PAJ', 'numero' => '123', 'situacao' => 'ATIVO']);

    $negativa = $fonte->normalizar(['data' => [['nada_consta' => true, 'procedimentos' => []]]], 'sucesso');
    expect($negativa['certidao_mpt']['status'])->toBe('Negativa');
});

test('TRF unificada manda tipo+email e deriva status da conjuncao dos TRFs', function () {
    $fonte = new \App\Services\Consultas\Fontes\Advocacia\CertidaoTrfFonte;

    config()->set('advocacia.email_solicitante', 'consultas@fiscaldock.com.br');
    expect($fonte->params(['cnpj' => '19.131.243/0001-97']))
        ->toBe(['cnpj' => '19131243000197', 'tipo' => '1', 'email' => 'consultas@fiscaldock.com.br']);

    // Todos os TRFs conseguiram emitir negativa → Negativa (regular).
    $neg = $fonte->normalizar(['data' => [[
        'conseguiu_emitir' => true, 'emitiu_pdf' => true,
        'detalhes_certidao' => [
            'numero_certidao' => 'TRF-123', 'codigo_validacao' => 'ABC',
            'normalizado_data_hora_emissao' => '25/11/2022 16:09:17',
            'tribunais' => [
                'trf1' => ['conseguiu_emitir_certidao_negativa' => true],
                'trf2' => ['conseguiu_emitir_certidao_negativa' => true],
                'trf3' => ['conseguiu_emitir_certidao_negativa' => true],
            ],
        ],
    ]]], 'sucesso');
    expect($neg['certidao_trf']['status'])->toBe('Negativa')
        ->and($neg['certidao_trf']['certidao_codigo'])->toBe('TRF-123')
        ->and($neg['certidao_trf']['emissao_data'])->toBe('25/11/2022')
        ->and($neg['certidao_trf']['tribunais_com_feitos'])->toBe([]);

    // Um TRF sem negativa (constam feitos) → Positiva, apontando qual.
    $pos = $fonte->normalizar(['data' => [[
        'detalhes_certidao' => ['tribunais' => [
            'trf1' => ['conseguiu_emitir_certidao_negativa' => true],
            'trf3' => ['conseguiu_emitir_certidao_negativa' => false],
        ]],
    ]]], 'sucesso');
    expect($pos['certidao_trf']['status'])->toBe('Positiva')
        ->and($pos['certidao_trf']['tribunais_com_feitos'])->toBe(['TRF3']);
});

test('fonte de lista: nada consta vira Negativa, registros viram Positiva com resumo', function () {
    $fonte = new ProtestosFonte;

    $nada = $fonte->normalizar(['data' => [['cartorios' => []]]], 'sucesso');
    expect($nada['protestos']['status'])->toBe('Negativa')
        ->and($nada['protestos']['nada_consta'])->toBeTrue()
        ->and($nada['protestos']['total_registros'])->toBe(0);

    $com = $fonte->normalizar(['data' => [[
        'quantidade_titulos' => 3,
        'cartorios' => [
            ['cartorio' => '1º Tabelionato', 'cidade' => 'Campo Grande', 'uf' => 'MS', 'quantidade_titulos' => 3, 'endereco_ignorado' => 'x'],
        ],
    ]]], 'sucesso');
    expect($com['protestos']['status'])->toBe('Positiva')
        ->and($com['protestos']['total_titulos'])->toBe(3)
        ->and($com['protestos']['registros'][0])->toBe(['cartorio' => '1º Tabelionato', 'cidade' => 'Campo Grande', 'uf' => 'MS', 'quantidade_titulos' => 3])
        ->and($com['protestos']['mensagem'])->toContain('3 título(s)');
});

test('fonte de lista trata 612 nao_encontrado como nada consta (Negativa)', function () {
    // Visto no smoke prod (lote 260): CEIS/CNEP devolvem 612 p/ CNPJ sem sanção — isso é
    // resposta boa ("nada consta"), não neutro.
    $fonte = new \App\Services\Consultas\Fontes\Advocacia\CeisFonte;

    $bloco = $fonte->normalizar(['code' => 612, 'code_message' => 'sem dados'], 'nao_encontrado');
    expect($bloco['ceis']['status'])->toBe('Negativa')
        ->and($bloco['ceis']['nada_consta'])->toBeTrue()
        ->and($bloco['ceis']['total_registros'])->toBe(0);

    // Certidão comum mantém o comportamento neutro original no 612.
    $stj = (new CertidaoStjFonte)->normalizar(['code' => 612], 'nao_encontrado');
    expect($stj['certidao_stj']['status'])->toBe('NAO_ENCONTRADA');
});

test('presenter classifica bloco de lista Positiva como atencao no strip', function () {
    $user = \App\Models\User::factory()->create();
    $resultado = new \App\Models\ConsultaResultado([
        'resultado_dados' => [
            'protestos' => ['status' => 'Positiva', 'total_registros' => 2, 'mensagem' => 'Constam 2 registro(s).'],
            'certidao_stj' => ['status' => 'Negativa'],
        ],
    ]);

    $strip = app(\App\Services\Consultas\ResultadoDetalhePresenter::class)
        ->certidoes($resultado, ['protestos', 'certidao_stj']);

    $porChave = collect($strip)->keyBy('chave');
    expect($porChave['protestos']['estado'])->toBe('atencao')
        ->and($porChave['certidao_stj']['estado'])->toBe('regular')
        ->and($porChave['certidao_stj']['sigla'])->toBe('STJ');
});
