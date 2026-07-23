<?php

use App\Services\Admin\ComercialParametroService;
use App\Services\PricingCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sem override, os valores são idênticos aos atuais (garantia anti-regressão de preço)', function () {
    $pricing = new PricingCatalogService;

    expect($pricing->getMinimumDeposit())->toBe(100.00);
});

it('credit_unit_price saiu do registro de parâmetros (ledger é em reais)', function () {
    expect(ComercialParametroService::DEFAULTS)->not->toHaveKey('credit_unit_price');
    expect(fn () => (new ComercialParametroService)->definir('credit_unit_price', 0.25, null))
        ->toThrow(InvalidArgumentException::class);
});

it('override de minimum_deposit é lido pelo PricingCatalogService', function () {
    (new ComercialParametroService)->definir('minimum_deposit', 80.00, null);

    expect((new PricingCatalogService)->getMinimumDeposit())->toBe(80.00);
});

it('preço per-plano legado saiu do registro comercial (migração à la carte)', function () {
    expect(ComercialParametroService::DEFAULTS)->not->toHaveKeys(['preco_validacao', 'preco_licitacao', 'preco_compliance']);
    expect(fn () => (new ComercialParametroService)->definir('preco_compliance', 6.00, null))
        ->toThrow(InvalidArgumentException::class);
});

it('preço de exibição legado do plano vale o custo do PlanoCatalog (sem override comercial)', function () {
    $plano = \App\Models\MonitoramentoPlano::where('codigo', 'compliance')->firstOrFail();
    $pricing = new PricingCatalogService;

    expect($pricing->getProductPriceByPlan($plano))->toBe(round((float) $plano->custo_creditos, 2));
});

it('faixas de volume removidas do PricingCatalogService', function () {
    $pricing = new PricingCatalogService;

    expect(method_exists($pricing, 'getTiers'))->toBeFalse();
    expect(method_exists($pricing, 'getTierForUser'))->toBeFalse();
    expect(method_exists($pricing, 'getTierForPaidCredits'))->toBeFalse();
    expect(method_exists($pricing, 'getNextTierForUser'))->toBeFalse();
    expect(method_exists($pricing, 'getTierProgressForUser'))->toBeFalse();
    expect(method_exists($pricing, 'getProductCreditsForUser'))->toBeFalse();
});
