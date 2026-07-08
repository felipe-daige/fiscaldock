<?php

use App\Support\Documento;

it('detecta CPF por 11 dígitos, formatado ou cru', function () {
    expect(Documento::ehCpf('12345678909'))->toBeTrue();
    expect(Documento::ehCpf('123.456.789-09'))->toBeTrue();
    expect(Documento::ehCpf('11222333000181'))->toBeFalse();
    expect(Documento::ehCpf('11.222.333/0001-81'))->toBeFalse();
    expect(Documento::ehCpf(null))->toBeFalse();
    expect(Documento::ehCpf(''))->toBeFalse();
});

it('rotula CPF como pessoa física no lugar de "não consultado"', function () {
    expect(Documento::rotuloSemConsulta('123.456.789-09'))->toBe('CPF (pessoa física)');
    expect(Documento::rotuloSemConsulta('11222333000181'))->toBe('não consultado');
    expect(Documento::rotuloSemConsulta('11222333000181', 'nunca consultado'))->toBe('nunca consultado');
    // CPF ignora o padrão custom — é sempre pessoa física.
    expect(Documento::rotuloSemConsulta('12345678909', 'nunca consultado'))->toBe('CPF (pessoa física)');
});
