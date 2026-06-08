<?php

use App\Models\SubscriptionPlan;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed(SubscriptionPlanSeeder::class));

it('seeda os 5 tiers com preços e créditos do spec', function () {
    expect(SubscriptionPlan::count())->toBe(5);

    $essencial = SubscriptionPlan::where('codigo', 'essencial')->first();
    expect($essencial->preco_mensal_centavos)->toBe(9900);
    expect($essencial->preco_anual_centavos)->toBe(99000);
    expect($essencial->creditos_inclusos)->toBe(300);
    expect($essencial->faixa_slug)->toBe('base');
    expect($essencial->limite_cnpjs_monitorados)->toBe(10);

    $profissional = SubscriptionPlan::where('codigo', 'profissional')->first();
    expect($profissional->faixa_slug)->toBe('x');
    expect($profissional->creditos_inclusos)->toBe(1100);

    $escritorio = SubscriptionPlan::where('codigo', 'escritorio')->first();
    expect($escritorio->preco_mensal_centavos)->toBe(79900);
    expect($escritorio->faixa_slug)->toBe('y');
    expect($escritorio->creditos_inclusos)->toBe(3000);
});

it('expõe capabilities como array tipado', function () {
    $free = SubscriptionPlan::where('codigo', 'free')->first();
    expect($free->capabilities['clearance_lote'])->toBeFalse();
    expect($free->capabilities['retencao_meses'])->toBe(6);

    $prof = SubscriptionPlan::where('codigo', 'profissional')->first();
    expect($prof->capabilities['export'])->toBe(['csv', 'excel']);
    expect($prof->capabilities['pdf_executivo'])->toBeTrue();
});

it('reseeda de forma idempotente (updateOrCreate)', function () {
    $this->seed(SubscriptionPlanSeeder::class);
    expect(SubscriptionPlan::count())->toBe(5);
});
