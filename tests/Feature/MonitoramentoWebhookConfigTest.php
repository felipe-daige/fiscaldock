<?php

it('expõe as urls de webhook do monitoramento', function () {
    config()->set('services.webhook.monitoramento_cnpj_participante_url', 'https://exemplo/participante');
    config()->set('services.webhook.monitoramento_cnpj_cliente_url', 'https://exemplo/cliente');

    expect(config('services.webhook.monitoramento_cnpj_participante_url'))->toBe('https://exemplo/participante');
    expect(config('services.webhook.monitoramento_cnpj_cliente_url'))->toBe('https://exemplo/cliente');
});

it('tem as chaves declaradas no config/services.php', function () {
    expect(array_key_exists('monitoramento_cnpj_participante_url', config('services.webhook')))->toBeTrue();
    expect(array_key_exists('monitoramento_cnpj_cliente_url', config('services.webhook')))->toBeTrue();
});
