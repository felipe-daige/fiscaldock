<?php

use App\Services\Advocacia\CatalogoFontesAvulsas;

beforeEach(function () {
    // Fontes InfoSimples só ficam prontas() com o gate ligado + token.
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
    // As PF nativas ficam atrás do gate de smoke (params ainda não confirmados no painel);
    // os testes as liberam explicitamente para exercitar o pipeline.
    config()->set('advocacia.fontes_publicas_liberadas', ['cadastro_pf', 'quitacao_eleitoral']);
});

test('grupo fiscal lista fontes operacionais e futuras em manutencao', function () {
    $catalogo = app(CatalogoFontesAvulsas::class);

    $grupos = $catalogo->grupos();

    expect($grupos)->toHaveKey('fiscal');
    $chaves = array_column($grupos['fiscal']['fontes'], 'chave');
    expect($chaves)->toBe([
        'cadastro', 'analise_fiscal', 'simples_nacional', 'cnd_federal',
        'cnd_estadual', 'cnd_municipal', 'crf_fgts', 'cndt', 'sintegra',
        'receita_situacao_fiscal',
    ]);

    // Cadastro é grátis; análise fiscal e as certidões, R$ 1,00. Fontes futuras aparecem, mas
    // nunca entram em chavesDisponiveis/preço do carrinho.
    $precos = array_column($grupos['fiscal']['fontes'], 'preco', 'chave');
    $fontes = collect($grupos['fiscal']['fontes'])->keyBy('chave');
    expect($precos['cadastro'])->toBe(0.0)
        ->and($precos['analise_fiscal'])->toBe(1.00)
        ->and($precos['cnd_federal'])->toBe(1.00)
        ->and($fontes['simples_nacional']['selecionavel'])->toBeFalse()
        ->and($fontes['receita_situacao_fiscal']['requer_autenticacao'])->toBeTrue()
        ->and($catalogo->chavesDisponiveis())->not->toContain('simples_nacional');
});

test('override de preco por fonte vence o default', function () {
    config()->set('advocacia.precos.sintegra', 2.50);
    $catalogo = app(CatalogoFontesAvulsas::class);

    expect($catalogo->precoDe('sintegra'))->toBe(2.50)
        ->and($catalogo->precoDe('cnd_federal'))->toBe(1.00)
        ->and($catalogo->precoSelecao(['sintegra', 'cnd_federal', 'cnd_federal']))->toBe(3.50);
});

test('fonte infosimples nao pronta fica em manutencao e nao entra na selecao', function () {
    config()->set('consultas.infosimples_ativo', false);
    $catalogo = app(CatalogoFontesAvulsas::class);

    // Somente cadastro + análise ficam selecionáveis; a vitrine mantém as demais como manutenção.
    expect($catalogo->chavesDisponiveis())->toBe(['cadastro', 'analise_fiscal'])
        ->and(array_keys($catalogo->grupos()))->toBe([
            'pessoa_fisica', 'judicial', 'integridade', 'ambiental', 'patrimonio',
            'imoveis', 'processual', 'passivo', 'fiscal',
        ])
        ->and(collect($catalogo->grupos()['judicial']['fontes'])->every(
            fn (array $fonte) => $fonte['selecionavel'] === false
        ))->toBeTrue();
});

test('atributosDe inclui sempre o cadastro alem das fontes selecionadas', function () {
    $catalogo = app(CatalogoFontesAvulsas::class);

    $atributos = $catalogo->atributosDe(['cnd_federal']);

    // Atributos do cadastro (ex.: situacao_cadastral) + o da fonte paga selecionada.
    expect($atributos)->toContain('situacao_cadastral')
        ->and($atributos)->toContain('cnd_federal');

    // Round-trip: o registry deriva de volta cadastro + cnd_federal (o que o job consome).
    $fontes = app(\App\Services\Consultas\FonteRegistry::class)->fontesDe($atributos);
    $chaves = array_map(fn ($f) => $f->chave(), $fontes);
    sort($chaves);
    expect($chaves)->toBe(['cadastro', 'cnd_federal']);
});

test('etapasDe deriva o strip dinamicamente dos grupos das fontes', function () {
    $catalogo = app(CatalogoFontesAvulsas::class);

    // Só federais → 3 etapas (inicializacao, cadastrais, federais).
    $etapas = $catalogo->etapasDe(['cnd_federal', 'cndt']);
    expect(array_column($etapas, 'chave'))->toBe(['inicializacao', 'cadastrais', 'certidoes_federais'])
        ->and(array_column($etapas, 'numero'))->toBe([1, 2, 3]);

    // Estadual entra → 4 etapas, numeração contígua.
    $etapas = $catalogo->etapasDe(['cnd_federal', 'sintegra']);
    expect(array_column($etapas, 'chave'))->toBe(['inicializacao', 'cadastrais', 'certidoes_federais', 'certidoes_estaduais']);

    // Seleção vazia ainda tem inicializacao + cadastrais (cadastro sempre roda).
    expect(array_column($catalogo->etapasDe([]), 'chave'))->toBe(['inicializacao', 'cadastrais']);
});

test('filtra o catalogo e o pipeline pelo tipo de pessoa do alvo', function () {
    $catalogo = app(CatalogoFontesAvulsas::class);

    expect(array_keys($catalogo->grupos('PF')))->toBe([
        'pessoa_fisica', 'judicial', 'integridade', 'ambiental', 'patrimonio',
        'imoveis', 'processual', 'passivo', 'fiscal',
    ])
        ->and($catalogo->chavesDisponiveis('PF'))->toBe(['cadastro_pf', 'quitacao_eleitoral'])
        ->and(array_keys($catalogo->grupos('PJ')))->toBe([
            'judicial', 'integridade', 'ambiental', 'patrimonio',
            'imoveis', 'processual', 'passivo', 'fiscal',
        ])
        ->and($catalogo->chavesDisponiveis('PJ'))->not->toContain('cadastro_pf')
        ->and($catalogo->fontesIncompativeis(['cadastro_pf'], 'PJ'))->toBe(['cadastro_pf'])
        ->and($catalogo->fontesIncompativeis(['cadastro'], 'PF'))->toBe(['cadastro']);

    // PF não recebe o cadastro CNPJ grátis de forma implícita.
    expect($catalogo->atributosDe(['cadastro_pf'], 'PF'))->toBe(['cadastro_pf'])
        ->and(array_column($catalogo->etapasDe(['cadastro_pf'], 'PF'), 'chave'))
        ->toBe(['inicializacao', 'cadastrais'])
        ->and(array_column($catalogo->etapasDe(['quitacao_eleitoral'], 'PF'), 'chave'))
        ->toBe(['inicializacao', 'certidoes_judiciais']);
});

test('rotula explicitamente as fontes por documento aceito', function () {
    $catalogo = app(CatalogoFontesAvulsas::class);
    $grupos = $catalogo->grupos();
    $fontes = collect($grupos)->flatMap(fn (array $grupo) => $grupo['fontes'])->keyBy('chave');

    expect($fontes['cadastro_pf']['documentos_aceitos_label'])->toBe('CPF')
        ->and($fontes['cadastro']['documentos_aceitos_label'])->toBe('CNPJ')
        ->and($fontes['car_imovel']['documentos_aceitos_label'])->toBe('Número do CAR')
        ->and($catalogo->rotuloDocumentosAceitos(['PJ', 'PF']))->toBe('CPF e CNPJ');
});
