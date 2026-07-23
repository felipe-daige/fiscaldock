<?php

use App\Services\Consultas\FonteRegistry;
use App\Services\Consultas\Fontes\Advocacia\BcbValoresReceberFonte;
use App\Services\Consultas\Fontes\Advocacia\CeisFonte;
use App\Services\Consultas\Fontes\Advocacia\CertidaoMpfFonte;
use App\Services\Consultas\Fontes\Advocacia\CertidaoStjFonte;
use App\Services\Consultas\Fontes\Advocacia\CertidaoTrfFonte;
use App\Services\Consultas\Fontes\Advocacia\CnepFonte;
use App\Services\Consultas\Fontes\Advocacia\IbamaAutuacoesFonte;
use App\Services\Consultas\Fontes\Advocacia\IbamaDebitosFonte;
use App\Services\Consultas\Fontes\Advocacia\IbamaEmbargosFonte;
use App\Services\Consultas\Fontes\Advocacia\IbamaRegularidadeFonte;
use App\Services\Consultas\Fontes\Advocacia\InpiMarcasTitularFonte;
use App\Services\Consultas\Fontes\Advocacia\PgfnDevedoresFonte;
use App\Services\Consultas\Fontes\Advocacia\TcuCniInabilitadoFonte;
use App\Services\Consultas\Fontes\Advocacia\TcuCniInidoneoFonte;
use App\Services\Consultas\Fontes\Advocacia\TcuCnpFonte;
use App\Services\Consultas\Fontes\CndFederalFonte;
use App\Services\Consultas\Fontes\CndtFonte;

beforeEach(function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
    config()->set('advocacia.fontes_pf_liberadas', []);
    config()->set('advocacia.fontes_publicas_liberadas', []);
});

test('ampliacoes CPF permanecem bloqueadas ate o smoke autorizado', function () {
    $fontes = [
        new CndFederalFonte,
        new CndtFonte,
        new CertidaoStjFonte,
        new CertidaoTrfFonte,
        new CertidaoMpfFonte,
        new CeisFonte,
        new CnepFonte,
    ];

    foreach ($fontes as $fonte) {
        expect($fonte->aceitaPessoa())->toBe(['PJ']);
    }

    config()->set('advocacia.fontes_pf_liberadas', array_map(
        fn ($fonte) => $fonte->chave(),
        $fontes,
    ));

    foreach ($fontes as $fonte) {
        expect($fonte->aceitaPessoa())->toBe(['PF', 'PJ']);
        $params = $fonte->params([
            'tipo_pessoa' => 'PF',
            'documento' => '529.982.247-25',
            'birthdate' => '1980-05-10',
        ]);
        expect($params['cpf'] ?? null)->toBe('52998224725')
            ->and($params)->not->toHaveKey('cnpj');
    }
});

test('CND federal por CPF exige nascimento e nao aplica regra de matriz', function () {
    config()->set('advocacia.fontes_pf_liberadas', ['cnd_federal']);
    $fonte = new CndFederalFonte;

    expect($fonte->aplicavelPara([
        'tipo_pessoa' => 'PF',
        'documento' => '52998224725',
    ]))->toBeFalse()
        ->and($fonte->aplicavelPara([
            'tipo_pessoa' => 'PF',
            'documento' => '52998224725',
            'birthdate' => '1980-05-10',
        ]))->toBeTrue()
        ->and($fonte->params([
            'tipo_pessoa' => 'PF',
            'documento' => '52998224725',
            'birthdate' => '1980-05-10',
        ]))->toBe([
            'cpf' => '52998224725',
            'preferencia_emissao' => '2via',
            'birthdate' => '1980-05-10',
        ]);
});

test('novas fontes publicas ficam registradas mas nao prontas antes da validacao', function () {
    $registry = app(FonteRegistry::class);
    $chaves = [
        'pgfn_devedores',
        'tcu_cnp',
        'tcu_cni_inidoneo',
        'tcu_cni_inabilitado',
        'bcb_valores_receber',
        'inpi_marcas_titular',
        'ibama_embargos',
        'ibama_debitos',
        'ibama_regularidade',
        'ibama_autuacoes',
    ];

    foreach ($chaves as $chave) {
        expect($registry->get($chave))->not->toBeNull()
            ->and($registry->get($chave)->pronta())->toBeFalse();
    }
    expect($registry->get('tcu_cni_inabilitado')->aceitaPessoa())->toBe(['PF']);
    foreach (array_diff($chaves, ['tcu_cni_inabilitado']) as $chave) {
        expect($registry->get($chave)->aceitaPessoa())->toBe(['PF', 'PJ']);
    }

    config()->set('advocacia.fontes_publicas_liberadas', $chaves);

    foreach ($chaves as $chave) {
        expect($registry->get($chave)->pronta())->toBeTrue();
    }
});

test('TCU CNI separa inidoneo de inabilitado e preserva processos', function () {
    $inidoneo = new TcuCniInidoneoFonte;
    $inabilitado = new TcuCniInabilitadoFonte;

    expect($inidoneo->params([
        'tipo_pessoa' => 'PJ',
        'documento' => '19.131.243/0001-97',
    ]))->toBe(['cnpj' => '19131243000197', 'tipo_relacao' => 1])
        ->and($inabilitado->params([
            'tipo_pessoa' => 'PF',
            'documento' => '529.982.247-25',
        ]))->toBe(['cpf' => '52998224725', 'tipo_relacao' => 2])
        ->and($inabilitado->aceitaPessoa())->toBe(['PF']);

    $resultado = $inidoneo->normalizar([
        'data' => [[
            'codigo_controle' => 'CNI-123',
            'conseguiu_emitir_certidao_negativa' => false,
            'nome' => 'EMPRESA EXEMPLO',
            'titulo' => 'CERTIDÃO POSITIVA DE INIDÔNEOS',
            'processos' => [[
                'processo' => '1111111-11.1111.1.11.1111',
                'acordao' => '111/2026',
                'entrada_cadastro' => '01/01/2026',
                'saida_cadastro' => null,
            ]],
            'site_receipt' => 'https://example.test/cni.pdf',
        ]],
    ], 'sucesso');

    expect($resultado['tcu_cni_inidoneo']['status'])->toBe('Positiva')
        ->and($resultado['tcu_cni_inidoneo']['certidao_codigo'])->toBe('CNI-123')
        ->and($resultado['tcu_cni_inidoneo']['total_processos'])->toBe(1)
        ->and($resultado['tcu_cni_inidoneo']['comprovante'])->toBe('https://example.test/cni.pdf');
});

test('BCB valores a receber exige o complemento correto de PF e PJ', function () {
    $fonte = new BcbValoresReceberFonte;

    expect($fonte->aplicavelPara([
        'tipo_pessoa' => 'PF',
        'documento' => '52998224725',
    ]))->toBeFalse()
        ->and($fonte->params([
            'tipo_pessoa' => 'PF',
            'documento' => '52998224725',
            'birthdate' => '1980-05-10',
        ]))->toBe(['cpf' => '52998224725', 'data_nascimento' => '1980-05-10'])
        ->and($fonte->params([
            'tipo_pessoa' => 'PJ',
            'documento' => '19131243000197',
            'data_inicio_atividade' => '2014-09-02',
        ]))->toBe(['cnpj' => '19131243000197', 'data_abertura_empresa' => '2014-09-02']);

    $positivo = $fonte->normalizar([
        'data' => [[
            'mensagem' => 'Há valores a receber.',
            'possui_valores_receber' => true,
            'site_receipt' => 'https://example.test/bcb.html',
        ]],
        'site_receipts' => [],
    ]);
    $ausente = $fonte->normalizar(['code_message' => 'Sem dados na origem.'], 'nao_encontrado');

    expect($positivo['bcb_valores_receber']['status'])->toBe('Positiva')
        ->and($positivo['bcb_valores_receber']['possui_valores_receber'])->toBeTrue()
        ->and($positivo['bcb_valores_receber']['comprovante'])->toBe('https://example.test/bcb.html')
        ->and($ausente['bcb_valores_receber']['status'])->toBe('NAO_ENCONTRADO');
});

test('IBAMA autuacoes exige ano e INPI limita a consulta a primeira pagina', function () {
    $ibama = new IbamaAutuacoesFonte;
    $inpi = new InpiMarcasTitularFonte;

    expect($ibama->aplicavelPara([
        'tipo_pessoa' => 'PF',
        'documento' => '52998224725',
    ]))->toBeFalse()
        ->and($ibama->params([
            'tipo_pessoa' => 'PF',
            'documento' => '52998224725',
            'ano' => 2024,
        ]))->toBe(['cpf' => '52998224725', 'ano' => 2024])
        ->and($inpi->params([
            'tipo_pessoa' => 'PJ',
            'documento' => '19131243000197',
        ]))->toBe(['cnpj' => '19131243000197', 'pagina' => 1]);

    $autuacoes = $ibama->normalizar(['data' => [[
        'infracoes' => [[
            'numero' => '1',
            'tipo' => 'Flora',
            'valor_multa' => '135.000,00',
            'segredo' => 'não persistir',
        ]],
        'total_infracoes' => '3',
        'valor_infracoes' => '135.000,00',
        'normalizado_valor_infracoes' => 135000.0,
    ]]]);
    $marcas = $inpi->normalizar(['data' => [[
        'processos' => [[
            'numero' => '123',
            'marca' => 'FiscalDock',
            'situacao' => 'Registro em vigor',
            'campo_extra' => 'não persistir',
        ]],
        'processos_total' => 8,
        'total_paginas' => 3,
        'pagina_atual' => 1,
    ]]]);

    expect($autuacoes['ibama_autuacoes']['status'])->toBe('Positiva')
        ->and($autuacoes['ibama_autuacoes']['total_registros'])->toBe(3)
        ->and($autuacoes['ibama_autuacoes']['registros'][0])->not->toHaveKey('segredo')
        ->and($marcas['inpi_marcas_titular']['total_registros'])->toBe(8)
        ->and($marcas['inpi_marcas_titular']['tem_mais_paginas'])->toBeTrue()
        ->and($marcas['inpi_marcas_titular']['registros'][0])->not->toHaveKey('campo_extra');
});

test('lista de devedores PGFN normaliza divida e ausencia de registros', function () {
    $fonte = new PgfnDevedoresFonte;

    expect($fonte->params([
        'tipo_pessoa' => 'PF',
        'documento' => '529.982.247-25',
    ]))->toBe(['cpf' => '52998224725']);

    $positiva = $fonte->normalizar(['data' => [[
        'nome' => 'MARIA DA SILVA',
        'total_divida' => 'R$ 1.234,56',
        'naturezas_debitos' => [
            ['descricao' => 'Tributário', 'total' => 'R$ 1.234,56', 'debitos' => 2],
        ],
    ]]], 'sucesso');

    expect($positiva['pgfn_devedores']['status'])->toBe('Positiva')
        ->and($positiva['pgfn_devedores']['total_registros'])->toBe(1)
        ->and($positiva['pgfn_devedores']['total_divida'])->toBe('R$ 1.234,56');

    $negativa = $fonte->normalizar([], 'nao_encontrado');
    expect($negativa['pgfn_devedores']['status'])->toBe('Negativa')
        ->and($negativa['pgfn_devedores']['total_registros'])->toBe(0);
});

test('certidoes TCU e IBAMA usam CPF ou CNPJ e normalizam status', function () {
    $tcu = new TcuCnpFonte;
    $embargos = new IbamaEmbargosFonte;
    $debitos = new IbamaDebitosFonte;
    $regularidade = new IbamaRegularidadeFonte;

    expect($tcu->params([
        'tipo_pessoa' => 'PJ',
        'documento' => '19.131.243/0001-97',
    ]))->toBe(['cnpj' => '19131243000197'])
        ->and($embargos->params([
            'tipo_pessoa' => 'PF',
            'documento' => '52998224725',
        ]))->toBe(['cpf' => '52998224725'])
        ->and($debitos->aplicavelPara([
            'tipo_pessoa' => 'PF',
            'documento' => '52998224725',
        ]))->toBeFalse()
        ->and($debitos->params([
            'tipo_pessoa' => 'PF',
            'documento' => '52998224725',
            'nome' => 'Maria da Silva',
        ]))->toBe(['cpf' => '52998224725', 'nome' => 'Maria da Silva'])
        ->and($regularidade->params([
            'tipo_pessoa' => 'PJ',
            'documento' => '19.131.243/0001-97',
        ]))->toBe(['cnpj' => '19131243000197']);

    $tcuNegativa = $tcu->normalizar(['data' => [[
        'conseguiu_emitir_certidao_negativa' => true,
        'codigo_controle' => 'TCU-123',
    ]]], 'sucesso');
    $embargosPositiva = $embargos->normalizar(['data' => [[
        'conseguiu_emitir_certidao_negativa' => false,
        'numero' => 'IBAMA-1',
    ]]], 'sucesso');
    $regularidadeNegativa = $regularidade->normalizar(['data' => [[
        'registro' => 'CR-1',
        'emissao_data' => '23/07/2026',
        'validade_data' => '23/10/2026',
        'razao_social' => 'ACME LTDA',
        'categorias' => [['categoria' => '1', 'detalhe' => 'Atividade potencialmente poluidora']],
    ]]], 'sucesso');

    expect($tcuNegativa['tcu_cnp']['status'])->toBe('Negativa')
        ->and($tcuNegativa['tcu_cnp']['certidao_codigo'])->toBe('TCU-123')
        ->and($embargosPositiva['ibama_embargos']['status'])->toBe('Positiva')
        ->and($regularidadeNegativa['ibama_regularidade']['status'])->toBe('Regular')
        ->and($regularidadeNegativa['ibama_regularidade']['certidao_codigo'])->toBe('CR-1')
        ->and($regularidadeNegativa['ibama_regularidade']['categorias'])->toHaveCount(1);
});
