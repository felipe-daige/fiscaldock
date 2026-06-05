<?php

use App\Services\Consultas\Dto\RespostaProvider;
use App\Services\Consultas\Dto\ResultadoFonte;

it('RespostaProvider carrega status, código e raw', function () {
    $r = new RespostaProvider('sucesso', 200, ['razao_social' => 'X'], null);
    expect($r->status)->toBe('sucesso');
    expect($r->httpCode)->toBe(200);
    expect($r->raw['razao_social'])->toBe('X');
    expect($r->mensagem)->toBeNull();
});

it('ResultadoFonte carrega chave, dados e status', function () {
    $r = new ResultadoFonte('cadastro', ['razao_social' => 'X'], 'sucesso', 0, null);
    expect($r->chave)->toBe('cadastro');
    expect($r->dados['razao_social'])->toBe('X');
    expect($r->status)->toBe('sucesso');
    expect($r->custoCreditos)->toBe(0);
    expect($r->ehFalhaEstornavel())->toBeFalse();
});

it('ResultadoFonte marca falha estornável em fatal', function () {
    $r = new ResultadoFonte('cnd_federal', [], 'fatal', 4, 'erro');
    expect($r->ehFalhaEstornavel())->toBeTrue();
});
