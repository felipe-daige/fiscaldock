<?php

use App\Services\Admin\ComercialParametroService;
use App\Services\PricingCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sem override, os valores são idênticos aos atuais (garantia anti-regressão de preço)', function () {
    $pricing = new PricingCatalogService;

    expect($pricing->creditUnitPrice())->toBe(0.20);
    expect($pricing->getMinimumDeposit())->toBe(100.00);
});

it('override de credit_unit_price é lido pelo PricingCatalogService', function () {
    (new ComercialParametroService)->definir('credit_unit_price', 0.25, null);

    expect((new PricingCatalogService)->creditUnitPrice())->toBe(0.25);
});

it('override de minimum_deposit é lido pelo PricingCatalogService', function () {
    (new ComercialParametroService)->definir('minimum_deposit', 80.00, null);

    expect((new PricingCatalogService)->getMinimumDeposit())->toBe(80.00);
});

it('override de preço por plano é preenchido em reais e convertido para cobrança interna', function () {
    (new ComercialParametroService)->definir('preco_compliance', 6.00, null);

    $plano = \App\Models\MonitoramentoPlano::where('codigo', 'compliance')->firstOrFail();
    $pricing = new PricingCatalogService;

    expect($pricing->getProductPriceByPlan($plano))->toBe(6.00);
    expect($pricing->getProductCreditsByPlan($plano, \App\Models\User::factory()->create()))->toBe(30);
});

it('landing pricing lê override de preço por plano', function () {
    (new ComercialParametroService)->definir('preco_compliance', 6.00, null);

    $produto = collect((new PricingCatalogService)->getLandingPricingData()['products'])
        ->firstWhere('slug', 'compliance');

    expect($produto['price'])->toBe(6.00);
    expect($produto['price_label'])->toBe('R$ 6,00/consulta');
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
