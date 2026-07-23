<?php

use App\Support\Cpf;

test('Cpf::valido aceita CPF com DV correto e rejeita invalido/repetido/mascara', function () {
    expect(Cpf::valido('111.444.777-35'))->toBeTrue()
        ->and(Cpf::valido('52998224725'))->toBeTrue()
        ->and(Cpf::valido('111.444.777-00'))->toBeFalse()   // DV errado
        ->and(Cpf::valido('111.111.111-11'))->toBeFalse()   // todos iguais
        ->and(Cpf::valido('123'))->toBeFalse()               // curto
        ->and(Cpf::valido(''))->toBeFalse()
        ->and(Cpf::valido(null))->toBeFalse();
});

test('Cpf::digitos e formatar', function () {
    expect(Cpf::digitos('529.982.247-25'))->toBe('52998224725')
        ->and(Cpf::formatar('52998224725'))->toBe('529.982.247-25')
        ->and(Cpf::formatar('123'))->toBe('123'); // não formata se != 11 dígitos
});
