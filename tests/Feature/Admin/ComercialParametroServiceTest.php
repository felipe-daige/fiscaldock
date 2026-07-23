<?php

use App\Services\Admin\ComercialParametroService;
use App\Services\PricingCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sem faixas, sem peg legado e sem preço per-plano (migração à la carte)', function () {
    expect(ComercialParametroService::DEFAULTS)->not->toHaveKeys(['faixa_x_min', 'faixa_y_min', 'faixa_z_min']);
    expect(ComercialParametroService::DEFAULTS)->not->toHaveKey('credit_unit_price');
    expect(ComercialParametroService::DEFAULTS)->not->toHaveKeys(['preco_validacao', 'preco_licitacao', 'preco_compliance']);
    expect(ComercialParametroService::DEFAULTS)->toHaveKey('minimum_deposit');
});

it('retorna o default passado quando a chave não está no registro', function () {
    $service = new ComercialParametroService;

    expect($service->valor('preco_validacao', 3.00))->toBe(3.00);
});

it('os defaults do registro batem com as constantes do PricingCatalogService (anti-drift)', function () {
    $defaults = ComercialParametroService::DEFAULTS;

    expect($defaults['minimum_deposit']['default'])->toBe(PricingCatalogService::MINIMUM_DEPOSIT);
    expect($defaults)->not->toHaveKey('faixa_x_min');
    expect($defaults)->not->toHaveKey('faixa_y_min');
    expect($defaults)->not->toHaveKey('faixa_z_min');
    expect($defaults)->not->toHaveKey('preco_validacao');
    expect($defaults)->not->toHaveKey('preco_licitacao');
    expect($defaults)->not->toHaveKey('preco_compliance');
});

it('persiste e lê um override com tipagem correta', function () {
    $service = new ComercialParametroService;

    $service->definir('minimum_deposit', 120.50, null);

    expect($service->valor('minimum_deposit'))->toBe(120.50);
    $this->assertDatabaseHas('comercial_parametros', ['chave' => 'minimum_deposit', 'valor' => '120.5']);
});

it('faz cast monetario para parâmetros de preço', function () {
    $service = new ComercialParametroService;

    $service->definir('minimum_deposit', '4.50', null);

    expect($service->valor('minimum_deposit'))->toBe(4.50);
});

it('resetar remove o override e volta ao default', function () {
    $service = new ComercialParametroService;
    $service->definir('minimum_deposit', 90.00, null);

    $service->resetar('minimum_deposit');

    expect($service->valor('minimum_deposit'))->toBe(100.00);
    $this->assertDatabaseMissing('comercial_parametros', ['chave' => 'minimum_deposit']);
});

it('rejeita chave desconhecida (não deixa criar parâmetro fora do registro)', function () {
    $service = new ComercialParametroService;

    expect(fn () => $service->definir('chave_inexistente', 1, null))
        ->toThrow(InvalidArgumentException::class);
});

it('efetivos() expõe default, override e valor efetivo por parâmetro', function () {
    $service = new ComercialParametroService;
    $service->definir('minimum_deposit', 80.00, null);

    $efetivos = $service->efetivos();

    expect($efetivos['minimum_deposit']['default'])->toBe(100.00);
    expect($efetivos['minimum_deposit']['override'])->toBe(80.00);
    expect($efetivos['minimum_deposit']['efetivo'])->toBe(80.00);
    expect($efetivos)->not->toHaveKey('credit_unit_price');
    expect($efetivos)->not->toHaveKey('preco_compliance');
    expect($efetivos)->not->toHaveKey('faixa_x_min');
});
