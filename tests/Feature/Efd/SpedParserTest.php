<?php

use App\Services\Efd\Sped\SpedParser;

it('quebra por \r\n, \r e \n, pulando linha vazia e não-pipe', function () {
    $conteudo = "|0000|LF|\n"          // \n
        ."|0001|CRLF|\r\n"             // \r\n
        ."|0002|CR|\r"                 // \r
        ."\n"                          // linha vazia
        ."lixo sem pipe\n"            // não começa com '|'
        .'|0003|fim|';

    $regs = [];
    foreach ((new SpedParser)->stream($conteudo) as $rec) {
        $regs[] = $rec->reg;
    }

    expect($regs)->toBe(['0000', '0001', '0002', '0003']);
});

it('para no |9999| e não parseia a assinatura binária seguinte', function () {
    // Assinatura PKCS#7 real começa com DER "\x30\x82…"; simulamos bytes altos.
    $assinatura = "\x30\x82\x04\x12".str_repeat("\xDE\xAD\xBE\xEF", 8);
    $conteudo = "|C100|dado|\n|9999|42|\n".$assinatura."\n|NAO|deve aparecer|";

    $regs = [];
    foreach ((new SpedParser)->stream($conteudo) as $rec) {
        $regs[] = $rec->reg;
    }

    // O |9999| entra; nada depois dele (nem o |NAO| textual) deve aparecer.
    expect($regs)->toBe(['C100', '9999']);
});

it('campo() faz trim, preserva vazio e devolve null quando ausente', function () {
    // |C170|  abc  ||  → campos: [1]=C170 [2]='  abc  ' [3]='' [4]=''
    $rec = iterator_to_array((new SpedParser)->stream('|C170|  abc  ||'))[0];

    expect($rec->campo(1))->toBe('C170')  // REG
        ->and($rec->campo(2))->toBe('abc') // trim
        ->and($rec->campo(3))->toBe('')    // presente-vazio ≠ null
        ->and($rec->campo(4))->toBe('')
        ->and($rec->campo(99))->toBeNull(); // ausente
});

it('não converte cod_part de zeros à esquerda (fica string literal)', function () {
    // Gotcha 10.10: cod_part STRING, nunca parseInt (019075128507 ≠ 19075128507).
    $rec = iterator_to_array((new SpedParser)->stream('|C100|0|1|019075128507|55|'))[0];

    expect($rec->campo(4))->toBe('019075128507');
});
