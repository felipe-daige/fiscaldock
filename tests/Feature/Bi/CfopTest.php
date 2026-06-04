<?php

use App\Support\Cfop;

it('resolve descricao de cfop comum pelo mapa', function () {
    expect(Cfop::descricao('5102'))->toContain('Venda de mercadoria adquirida');
});

it('cai no fallback de familia para cfop nao mapeado', function () {
    // 5999 não está no mapa top-usados → família "Saída estadual"
    expect(Cfop::descricao('5999'))->toContain('Saída');
});

it('classifica entrada x saida pelo primeiro digito', function () {
    expect(Cfop::tipoOperacao('1102'))->toBe('entrada');
    expect(Cfop::tipoOperacao('6108'))->toBe('saida');
});
