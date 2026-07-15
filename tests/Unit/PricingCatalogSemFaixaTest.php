<?php

use App\Models\MonitoramentoPlano;
use App\Models\User;
use App\Services\PricingCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('preco do plano sem faixa', function () {
    $user = User::factory()->create();
    $plano = MonitoramentoPlano::where('codigo', 'compliance')->first();
    expect(app(PricingCatalogService::class)->getProductPriceByPlan($plano))->toBe(5.0);
});

it('faixas removidas', function () {
    expect(method_exists(PricingCatalogService::class, 'getTiers'))->toBeFalse();
});

it('conversao credito->reais removida (ledger é em reais)', function () {
    expect(method_exists(PricingCatalogService::class, 'creditsToCurrency'))->toBeFalse();
    expect(method_exists(PricingCatalogService::class, 'currencyToCredits'))->toBeFalse();
});
