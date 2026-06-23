<?php

it('trial 100 creditos e cap gratuito 3', function () {
    expect(config('trial.creditos'))->toBe(100);
    expect(config('trial.limite_consultas_gratuito'))->toBe(3);
});
