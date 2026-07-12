<?php

use App\Support\Dinheiro;

it('formata reais BR', function () {
    // NBSP entre "R$" e o número é contrato (valor monetário não quebra linha).
    expect(Dinheiro::brl(3))->toBe("R\$\u{A0}3,00");
    expect(Dinheiro::brl(1234.5))->toBe("R\$\u{A0}1.234,50");
    expect(Dinheiro::brl(0))->toBe("R\$\u{A0}0,00");
});
