<?php

use App\Support\Cfop;

it('config efd.cfops_devolucao tem a lista CONFAZ e fora_faturamento a inclui', function () {
    $devol = config('efd.cfops_devolucao');
    $fora = config('efd.cfops_fora_faturamento');

    // amostra dos códigos reais da HIDRATOP + ST-variants
    foreach ([1411, 2202, 5411, 6202, 1201, 5202] as $c) {
        expect($devol)->toContain($c)
            ->and($fora)->toContain($c); // devolução não compõe faturamento
    }
    // fora_faturamento mantém os não-receita antigos
    expect($fora)->toContain(1915)->and($fora)->toContain(6916);
});

it('Cfop::descricao resolve os ST-variants de devolução (antes caíam em familia)', function () {
    expect(Cfop::descricao('1411'))->toContain('Devolução')
        ->and(Cfop::descricao('5410'))->toContain('Devolução')
        ->and(Cfop::descricao('6411'))->toContain('Devolução');
});
