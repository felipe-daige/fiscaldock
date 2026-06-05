<?php

use App\Services\Consultas\Fontes\ProcessosFonte;
use App\Services\Consultas\Fontes\ProtestosFonte;

it('Protestos: metadados + sem/com protesto', function () {
    $f = new ProtestosFonte();
    expect($f->chave())->toBe('protestos');
    expect($f->slug())->toBe('ieptb/protestos');
    expect($f->fornece())->toBe(['protestos']);

    expect($f->normalizar(['data' => []], 'sucesso')['protestos']['possui_protesto'])->toBeFalse();
    $com = $f->normalizar(['data' => [['cartorio' => 'X'], ['cartorio' => 'Y']]], 'sucesso');
    expect($com['protestos']['possui_protesto'])->toBeTrue();
    expect($com['protestos']['total_protestos'])->toBe(2);
});

it('Processos: metadados + sem/com processo', function () {
    $f = new ProcessosFonte();
    expect($f->chave())->toBe('processos');
    expect($f->slug())->toBe('tribunal/trt/processo');

    expect($f->normalizar(['data' => []], 'sucesso')['processos']['possui_processo'])->toBeFalse();
    $com = $f->normalizar(['data' => [['numero' => '123']]], 'sucesso');
    expect($com['processos']['possui_processo'])->toBeTrue();
    expect($com['processos']['total_processos'])->toBe(1);
    expect($com['consultas_realizadas'])->toContain('processos');
});
