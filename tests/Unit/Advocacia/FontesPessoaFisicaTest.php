<?php

use App\Services\Consultas\Fontes\Advocacia\AntecedentesPfFonte;
use App\Services\Consultas\Fontes\Advocacia\CadastroPfFonte;
use App\Services\Consultas\Fontes\Advocacia\MandadoPrisaoFonte;
use App\Services\Consultas\Fontes\Advocacia\QuitacaoEleitoralFonte;

beforeEach(function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
    config()->set('advocacia.fontes_sensiveis.antecedentes_pf', false);
    config()->set('advocacia.fontes_sensiveis.mandado_prisao', false);
});

test('cadastro PF exige CPF e nascimento e normaliza identidade situacao e obito', function () {
    $fonte = new CadastroPfFonte;
    $alvo = ['tipo_pessoa' => 'PF', 'documento' => '529.982.247-25', 'birthdate' => '1980-05-10'];

    expect($fonte->aceitaPessoa())->toBe(['PF'])
        ->and($fonte->params($alvo))->toBe(['cpf' => '52998224725', 'birthdate' => '1980-05-10'])
        ->and($fonte->aplicavelPara($alvo))->toBeTrue()
        ->and($fonte->aplicavelPara(array_replace($alvo, ['birthdate' => '2099-01-01'])))->toBeFalse();

    $dados = $fonte->normalizar(['data' => [[
        'normalizado_cpf' => '52998224725',
        'nome' => 'MARIA DA SILVA',
        'situacao_cadastral' => 'REGULAR',
        'normalizado_data_nascimento' => '10/05/1980',
        'normalizado_ano_obito' => '2024',
    ]]], 'sucesso');

    expect($dados['cadastro_pf']['status'])->toBe('REGULAR')
        ->and($dados['cadastro_pf']['nome'])->toBe('MARIA DA SILVA')
        ->and($dados['cadastro_pf']['falecido'])->toBeTrue()
        ->and($dados['cadastro_pf']['ano_obito'])->toBe(2024)
        ->and($dados['consultas_realizadas'])->toBe(['cadastro_pf']);
});

test('cadastro PF vivo: ano_obito=0 nao marca falecido (smoke real pegou filled(0) true)', function () {
    // A Receita devolve ano_obito=0 para vivo (não null). `filled(0)` é true → o normalizer
    // marcava "falecido: true" num CPF vivo. Confirmado no smoke pago de 2026-07-23.
    $fonte = new CadastroPfFonte;

    $dados = $fonte->normalizar(['data' => [[
        'nome' => 'FELIPE VIVO',
        'situacao_cadastral' => 'REGULAR',
        'normalizado_ano_obito' => 0,
    ]]], 'sucesso');

    expect($dados['cadastro_pf']['falecido'])->toBeFalse()
        ->and($dados['cadastro_pf']['ano_obito'])->toBeNull();
});

test('quitacao eleitoral monta os parametros PF e traduz quite para certidao negativa', function () {
    $fonte = new QuitacaoEleitoralFonte;
    $alvo = [
        'documento' => '52998224725',
        'nome' => 'Maria da Silva',
        'birthdate' => '1980-05-10',
        'nome_mae' => 'Ana da Silva',
        'nome_pai' => 'José da Silva',
        'titulo_eleitoral' => '1234 5678 9012',
    ];

    expect($fonte->aceitaPessoa())->toBe(['PF'])
        ->and($fonte->aplicavelPara($alvo))->toBeTrue()
        ->and($fonte->params($alvo))->toBe([
            'cpf' => '52998224725',
            'birthdate' => '1980-05-10',
            'name' => 'Maria da Silva',
            'mother' => 'Ana da Silva',
            'father' => 'José da Silva',
            'titulo_eleitoral' => '123456789012',
        ]);

    $dados = $fonte->normalizar(['data' => [[
        'quite' => true,
        'autenticidade' => 'TSE-123',
        'uf' => 'MS',
        'municipio' => 'Dourados',
    ]]], 'sucesso');

    expect($dados['quitacao_eleitoral']['status'])->toBe('Negativa')
        ->and($dados['quitacao_eleitoral']['quite'])->toBeTrue()
        ->and($dados['quitacao_eleitoral']['certidao_codigo'])->toBe('TSE-123');
});

test('antecedentes PF fica gated e exige qualificacao completa do alvo', function () {
    $fonte = new AntecedentesPfFonte;
    $alvo = [
        'documento' => '52998224725',
        'nome' => 'Maria da Silva',
        'birthdate' => '1980-05-10',
        'nome_mae' => 'Ana da Silva',
        'nome_pai' => 'José da Silva',
        'uf_nascimento' => 'ms',
    ];

    expect($fonte->pronta())->toBeFalse()
        ->and($fonte->aplicavelPara($alvo))->toBeTrue()
        ->and($fonte->params($alvo)['uf_nascimento'])->toBe('MS');

    config()->set('advocacia.fontes_sensiveis.antecedentes_pf', true);
    expect($fonte->pronta())->toBeTrue();

    $dados = $fonte->normalizar(['data' => [[
        'conseguiu_emitir_certidao_negativa' => true,
        'certidao_codigo' => 'PF-123',
    ]]], 'sucesso');
    expect($dados['antecedentes_pf']['status'])->toBe('Negativa')
        ->and($dados['antecedentes_pf']['certidao_codigo'])->toBe('PF-123');
});

test('mandado de prisao fica gated e normaliza lista positiva ou nada consta', function () {
    $fonte = new MandadoPrisaoFonte;

    expect($fonte->pronta())->toBeFalse()
        ->and($fonte->aplicavelPara(['documento' => '52998224725']))->toBeTrue()
        ->and($fonte->params([
            'documento' => '52998224725',
            'nome' => 'Maria',
            'nome_mae' => 'Ana',
        ]))->toBe(['cpf' => '52998224725', 'nome' => 'Maria', 'nome_mae' => 'Ana']);

    config()->set('advocacia.fontes_sensiveis.mandado_prisao', true);
    expect($fonte->pronta())->toBeTrue();

    $positivo = $fonte->normalizar(['data' => [[
        'mandado' => '123',
        'processo' => '0001',
        'situacao' => 'Pendente de cumprimento',
        'campo_nao_persistido' => 'segredo',
    ]]], 'sucesso');
    expect($positivo['mandado_prisao']['status'])->toBe('Positiva')
        ->and($positivo['mandado_prisao']['total_registros'])->toBe(1)
        ->and($positivo['mandado_prisao']['registros'][0])->not->toHaveKey('campo_nao_persistido');

    $negativo = $fonte->normalizar([], 'nao_encontrado');
    expect($negativo['mandado_prisao']['status'])->toBe('Negativa')
        ->and($negativo['mandado_prisao']['nada_consta'])->toBeTrue();
});
