<?php

use App\Services\Clearance\ExposicaoFiscalService;

it('calcula multa de ofício de 75% sobre a base (art. 44 Lei 9.430/96)', function () {
    $service = new ExposicaoFiscalService;

    expect($service->multa(1000.00))->toBe(750.00);
    expect($service->multa(0.0))->toBe(0.0);
    expect($service->multa(1234.56))->toBe(925.92); // round(1234.56*0.75, 2)
});

it('calcula a decadência como emissão + 5 anos (art. 173 CTN)', function () {
    $service = new ExposicaoFiscalService;

    $decadencia = $service->decadencia('2026-01-15');

    expect($decadencia)->not->toBeNull();
    expect($decadencia->format('Y-m-d'))->toBe('2031-01-15');
});

it('decadência é null-safe quando não há data de emissão', function () {
    $service = new ExposicaoFiscalService;

    expect($service->decadencia(null))->toBeNull();
    expect($service->decadencia(''))->toBeNull();
});

it('monta o pacote de exposição: base + multa = total', function () {
    $service = new ExposicaoFiscalService;

    $pacote = $service->montar(1000.00, '2026-01-15');

    expect($pacote['base'])->toBe(1000.00);
    expect($pacote['multa'])->toBe(750.00);
    expect($pacote['total'])->toBe(1750.00);
    expect($pacote['decadencia']->format('Y-m-d'))->toBe('2031-01-15');
    expect($pacote['decadencia_label'])->toBe('15/01/2031');
});

it('monta exposição com decadência indefinida quando a emissão é nula', function () {
    $service = new ExposicaoFiscalService;

    $pacote = $service->montar(200.00, null);

    expect($pacote['total'])->toBe(350.00);
    expect($pacote['decadencia'])->toBeNull();
    expect($pacote['decadencia_label'])->toBe('—');
});
