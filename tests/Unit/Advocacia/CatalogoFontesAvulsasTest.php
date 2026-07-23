<?php

use App\Services\Advocacia\CatalogoFontesAvulsas;

beforeEach(function () {
    // Fontes InfoSimples só ficam prontas() com o gate ligado + token.
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
});

test('grupos fiscal lista cadastro (gratis) + analise fiscal + 6 certidoes', function () {
    $catalogo = app(CatalogoFontesAvulsas::class);

    $grupos = $catalogo->grupos();

    expect($grupos)->toHaveKey('fiscal');
    $chaves = array_column($grupos['fiscal']['fontes'], 'chave');
    expect($chaves)->toBe(['cadastro', 'analise_fiscal', 'cnd_federal', 'cnd_estadual', 'cnd_municipal', 'crf_fgts', 'cndt', 'sintegra']);

    // Cadastro é grátis; análise fiscal e as certidões, R$ 1,00.
    $precos = array_column($grupos['fiscal']['fontes'], 'preco', 'chave');
    expect($precos['cadastro'])->toBe(0.0)
        ->and($precos['analise_fiscal'])->toBe(1.00)
        ->and($precos['cnd_federal'])->toBe(1.00);
});

test('override de preco por fonte vence o default', function () {
    config()->set('advocacia.precos.sintegra', 2.50);
    $catalogo = app(CatalogoFontesAvulsas::class);

    expect($catalogo->precoDe('sintegra'))->toBe(2.50)
        ->and($catalogo->precoDe('cnd_federal'))->toBe(1.00)
        ->and($catalogo->precoSelecao(['sintegra', 'cnd_federal', 'cnd_federal']))->toBe(3.50);
});

test('fonte infosimples nao pronta some; analise_fiscal (minhareceita) permanece', function () {
    config()->set('consultas.infosimples_ativo', false);
    $catalogo = app(CatalogoFontesAvulsas::class);

    // Todas as fontes InfoSimples somem; cadastro (grátis) + Análise Fiscal (derivada) ficam.
    expect($catalogo->chavesDisponiveis())->toBe(['cadastro', 'analise_fiscal'])
        ->and(array_keys($catalogo->grupos()))->toBe(['fiscal']);
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
