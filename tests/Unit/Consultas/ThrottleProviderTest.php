<?php

use App\Services\Consultas\ThrottleProvider;

it('não bloqueia provider sem rate limit (minhareceita)', function () {
    $t = new ThrottleProvider();
    $inicio = microtime(true);
    $t->aguardar('minhareceita');
    $t->aguardar('minhareceita');
    expect(microtime(true) - $inicio)->toBeLessThan(0.5);
});

it('respeita a janela mínima no infosimples', function () {
    config()->set('consultas.providers.infosimples.rate_limit_por_segundo', 5); // janela 0,2s
    $t = new ThrottleProvider();
    $inicio = microtime(true);
    $t->aguardar('infosimples');
    $t->aguardar('infosimples');
    expect(microtime(true) - $inicio)->toBeGreaterThanOrEqual(0.18);
});
