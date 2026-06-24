<?php

use App\Services\Consultas\Dto\ResultadoFonte;

it('classifica retry e fatal como falha estornável', function () {
    expect((new ResultadoFonte('cnd_federal', [], 'retry'))->ehFalhaEstornavel())->toBeTrue();
    expect((new ResultadoFonte('cnd_federal', [], 'fatal'))->ehFalhaEstornavel())->toBeTrue();
});

it('não estorna sucesso nem não-falhas', function () {
    foreach (['sucesso', 'nao_encontrado', 'indeterminado', 'erro_participante', 'nao_aplicavel'] as $s) {
        expect((new ResultadoFonte('cnd_federal', [], $s))->ehFalhaEstornavel())->toBeFalse();
    }
});
