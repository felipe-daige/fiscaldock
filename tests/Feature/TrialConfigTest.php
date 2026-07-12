<?php

it('trial R$20 de saldo e cap gratuito 3', function () {
    expect(config('trial.saldo_reais'))->toBe(20.0);
    expect(config('trial.validade_dias'))->toBe(60);
    expect(config('trial.limite_consultas_gratuito'))->toBe(3);
});
