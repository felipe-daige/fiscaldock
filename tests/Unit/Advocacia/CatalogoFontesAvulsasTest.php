<?php

use App\Services\Advocacia\CatalogoFontesAvulsas;

beforeEach(function () {
    // Fontes InfoSimples só ficam prontas() com o gate ligado + token.
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
});

test('grupos lista as 6 fontes fiscais com preco default de R$ 1,00', function () {
    $catalogo = app(CatalogoFontesAvulsas::class);

    $grupos = $catalogo->grupos();

    expect($grupos)->toHaveKey('fiscal');
    $chaves = array_column($grupos['fiscal']['fontes'], 'chave');
    expect($chaves)->toBe(['cnd_federal', 'cnd_estadual', 'cnd_municipal', 'crf_fgts', 'cndt', 'sintegra'])
        ->and(array_column($grupos['fiscal']['fontes'], 'preco'))->each->toBe(1.00);
});

test('override de preco por fonte vence o default', function () {
    config()->set('advocacia.precos.sintegra', 2.50);
    $catalogo = app(CatalogoFontesAvulsas::class);

    expect($catalogo->precoDe('sintegra'))->toBe(2.50)
        ->and($catalogo->precoDe('cnd_federal'))->toBe(1.00)
        ->and($catalogo->precoSelecao(['sintegra', 'cnd_federal', 'cnd_federal']))->toBe(3.50);
});

test('fonte nao pronta some do catalogo', function () {
    config()->set('consultas.infosimples_ativo', false);
    $catalogo = app(CatalogoFontesAvulsas::class);

    expect($catalogo->grupos())->toBe([])
        ->and($catalogo->chavesDisponiveis())->toBe([]);
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
