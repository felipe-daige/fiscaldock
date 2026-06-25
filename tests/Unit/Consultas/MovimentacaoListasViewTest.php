<?php

use Illuminate\Support\Facades\Blade;

function renderListas(array $prod, array $cfop): string
{
    return Blade::render(
        '@include("autenticado.monitoramento._movimentacao-listas", ["top_produtos" => $p, "top_cfops" => $c])',
        ['p' => $prod, 'c' => $cfop]
    );
}

it('renderiza produtos e CFOPs com valores', function () {
    $html = renderListas(
        [['cod_item' => 'AGUA', 'descricao' => 'AGUA MINERAL', 'ncm' => '22011000', 'valor' => 800.0, 'qtd' => 2]],
        [['cfop' => 1102, 'descricao' => '1102 — Compra', 'valor' => 1000.0, 'qtd' => 3]],
    );
    expect($html)->toContain('Principais produtos')
        ->toContain('AGUA MINERAL')
        ->toContain('1102')
        ->toContain('800,00');
});

it('degrada com listas vazias (sem acervo)', function () {
    $html = renderListas([], []);
    expect($html)->toContain('Sem produtos no acervo.')
        ->toContain('Sem CFOPs no acervo.');
});
