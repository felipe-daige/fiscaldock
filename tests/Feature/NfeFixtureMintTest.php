<?php

use App\Services\Xml\NfeXmlParser;
use Tests\Fixtures\NfeFixtureMint;

it('gera NF-e parseável com emit/dest/chave trocados', function () {
    $xml = NfeFixtureMint::make('11111111000191', '22222222000191', '50240111111111000191550010000999990000000001', 'CLIENTE A LTDA', 'COMPRADOR X LTDA');

    $parsed = app(NfeXmlParser::class)->parse($xml);

    expect($parsed['header']['emit_documento'])->toBe('11111111000191');
    expect($parsed['header']['dest_documento'])->toBe('22222222000191');
    expect($parsed['header']['chave_acesso'])->toBe('50240111111111000191550010000999990000000001');
    expect($parsed['header']['emit_razao_social'])->toBe('CLIENTE A LTDA');
});

it('gera chaves distintas para notas distintas', function () {
    $a = app(NfeXmlParser::class)->parse(NfeFixtureMint::make('11111111000191', '22222222000191', '50240111111111000191550010000999990000000001'));
    $b = app(NfeXmlParser::class)->parse(NfeFixtureMint::make('33333333000191', '44444444000191', '50240133333333000191550010000999990000000002'));

    expect($a['header']['chave_acesso'])->not->toBe($b['header']['chave_acesso']);
    expect($a['header']['emit_documento'])->not->toBe($b['header']['emit_documento']);
});
