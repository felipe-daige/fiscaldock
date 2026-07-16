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
    expect($essencial->creditos_inclusos)->toBe(35.0);
    expect($essencial->faixa_slug)->toBe('base');
    expect($essencial->limite_cnpjs_monitorados)->toBeNull();
    expect($essencial->assentos_inclusos)->toBe(2);

    $profissional = SubscriptionPlan::where('codigo', 'profissional')->first();
    expect($profissional->preco_mensal_centavos)->toBe(29900);
    expect($profissional->faixa_slug)->toBe('x');
    expect($profissional->creditos_inclusos)->toBe(80.0);
    expect($profissional->assentos_inclusos)->toBe(3);

    $escritorio = SubscriptionPlan::where('codigo', 'escritorio')->first();
    expect($escritorio->preco_mensal_centavos)->toBe(79900);
    expect($escritorio->faixa_slug)->toBe('y');
    expect($escritorio->creditos_inclusos)->toBe(200.0);
    expect($escritorio->assentos_inclusos)->toBe(10);

    expect(SubscriptionPlan::where('codigo', 'enterprise')->first()->is_active)->toBeFalse();
});

it('expõe capabilities como array tipado', function () {
    $free = SubscriptionPlan::where('codigo', 'free')->first();
    expect($free->capabilities['clearance_lote'])->toBeFalse();
    expect($free->capabilities['retencao_meses'])->toBe(6);
    expect($free->capabilities['pdf_executivo'])->toBeTrue();

    foreach (['essencial', 'profissional', 'escritorio'] as $codigo) {
        expect(SubscriptionPlan::where('codigo', $codigo)->first()->capabilities['pdf_executivo'])->toBeTrue();
    }

    expect(SubscriptionPlan::where('codigo', 'profissional')->first()->capabilities['export'])
        ->toBe(['csv', 'excel']);
});

it('reseeda de forma idempotente (updateOrCreate)', function () {
    $this->seed(SubscriptionPlanSeeder::class);
    expect(SubscriptionPlan::count())->toBe(5);
});
