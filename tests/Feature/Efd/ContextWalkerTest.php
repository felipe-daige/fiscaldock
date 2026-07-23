<?php

use App\Services\Efd\Sped\ContextWalker;
use App\Services\Efd\Sped\SpedParser;

/** @return array<int, array{0: string, 1: ?\App\Services\Efd\Sped\Contexto}> */
if (! function_exists('spedWalk')) {
    function spedWalk(string $conteudo): array
    {
        $out = [];
        foreach ((new ContextWalker)->walk((new SpedParser)->stream($conteudo)) as [$rec, $pai]) {
            $out[] = [$rec->reg, $pai];
        }

        return $out;
    }
}

it('C100 vira pai dos filhos; bloco 0/E encerra o contexto', function () {
    $conteudo = implode("\n", [
        '|0150|FOR1|Fornecedor||11222333000181|',
        '|C100|0|1|FOR1|55|00|1|123|CHAVE_A|31012026|31012026|10,00|',
        '|C190|00|5102|18|100|',
        '|C170|1|COD|desc|',
        '|C100|1|0||65|00|2|456|CHAVE_B|08012026|08012026|50,00|', // NFC-e sem COD_PART
        '|C190|00|5405|0|50|',
        '|E100|01012026|31012026|',
        '|E110|100|0|',
    ]);

    $resumo = array_map(fn ($p) => [$p[0], $p[1]?->chave], spedWalk($conteudo));

    expect($resumo)->toBe([
        ['0150', null],
        ['C100', null],        // o próprio documento não tem pai
        ['C190', 'CHAVE_A'],
        ['C170', 'CHAVE_A'],
        ['C100', null],
        ['C190', 'CHAVE_B'],   // herda do 2º C100, não do 1º
        ['E100', null],        // bloco E encerrou o contexto
        ['E110', null],
    ]);
});

it('monta o Contexto do C100 com os índices SPED corretos', function () {
    $conteudo = "|C100|0|1|FOR1|55|00|3|123|CHAVE_A|31012026|31012026|7,20|\n|C190|00|5102|18|100|";

    $pai = null;
    foreach (spedWalk($conteudo) as [$reg, $ctx]) {
        if ($reg === 'C190') {
            $pai = $ctx;
        }
    }

    expect($pai->reg)->toBe('C100')
        ->and($pai->tipoOperacao)->toBe('0') // $p[2] IND_OPER
        ->and($pai->modelo)->toBe('55')       // $p[5] COD_MOD
        ->and($pai->serie)->toBe('3')         // $p[7] SER
        ->and($pai->numero)->toBe('123')      // $p[8] NUM_DOC
        ->and($pai->chave)->toBe('CHAVE_A');  // $p[9] CHV_NFE
});

it('monta o Contexto do D100 (CT-e) com CHV_CTE em $p[10]', function () {
    // D100 $p[2]=IND_OPER, [5]=COD_MOD, [7]=SER, [9]=NUM_DOC, [10]=CHV_CTE
    $conteudo = "|D100|0|1|TR1|57|00|1|0|999|CHAVE_CTE|01022026|01022026|200,00|\n|D190|00|5353|12|100|";

    $pai = null;
    foreach (spedWalk($conteudo) as [$reg, $ctx]) {
        if ($reg === 'D190') {
            $pai = $ctx;
        }
    }

    expect($pai->reg)->toBe('D100')
        ->and($pai->modelo)->toBe('57')
        ->and($pai->serie)->toBe('1')
        ->and($pai->numero)->toBe('999')
        ->and($pai->chave)->toBe('CHAVE_CTE');
});

it('A100 (NFS-e sem chave) vira pai do A170 por identidade lógica', function () {
    // A100 $p[2]=IND_OPER, [4]=COD_PART, [6]=SER, [8]=NUM_DOC, [9]=CHV (vazia). modelo fixo '00'.
    $conteudo = "|A100|1|0|FOR7|00|1|0|500||01022026|01022026|1000,00|\n|A170|1|SERV|Consultoria|1000,00|";

    $pai = null;
    foreach (spedWalk($conteudo) as [$reg, $ctx]) {
        if ($reg === 'A170') {
            $pai = $ctx;
        }
    }

    expect($pai->reg)->toBe('A100')
        ->and($pai->modelo)->toBe('00')
        ->and($pai->numero)->toBe('500')
        ->and($pai->serie)->toBe('1')
        ->and($pai->codPart)->toBe('FOR7')  // linkagem sem chave usa cod_part
        ->and($pai->chave)->toBe('');        // NFS-e sem chave de acesso
});

it('preserva o pai C100 através de registros intermediários (C110/C113); não orfana filhos', function () {
    // C110 (infCpl) e C113 aparecem ENTRE o C100 e o C170/C190 em toda NF-e B2B real.
    // Antes do fix, qualquer registro não-listado zerava o pai e os filhos viravam órfãos.
    $conteudo = implode("\n", [
        '|C100|0|1|FOR1|55|00|1|123|CHAVE_A|31012026|31012026|100,00|',
        '|C110|10|Informacao complementar qualquer|',
        '|C113|55|...|',
        '|C170|1|COD|desc|',
        '|C190|00|5102|18|100|',
        '|C001|0|', // fronteira de bloco: encerra o pai
        '|C190|00|9999|0|0|', // órfão de verdade, após a fronteira
    ]);

    $resumo = array_map(fn ($p) => [$p[0], $p[1]?->chave], spedWalk($conteudo));

    expect($resumo)->toBe([
        ['C100', null],
        ['C110', null],       // intermediário: emitido sem pai, mas NÃO zera o contexto
        ['C113', null],
        ['C170', 'CHAVE_A'],  // ainda acha o pai
        ['C190', 'CHAVE_A'],  // idem
        ['C001', null],       // fronteira de bloco encerra
        ['C190', null],       // agora sim órfão
    ]);
});

it('filho órfão (SPED malformado) sai com contexto null, sem quebrar', function () {
    $pares = spedWalk('|C190|00|5102|18|100|'); // C190 sem C100 antes

    expect($pares)->toHaveCount(1)
        ->and($pares[0][0])->toBe('C190')
        ->and($pares[0][1])->toBeNull();
});
