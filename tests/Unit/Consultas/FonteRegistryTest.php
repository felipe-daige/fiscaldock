<?php

use App\Services\Consultas\Fontes\CadastroFonte;
use App\Services\Consultas\FonteRegistry;

it('resolve fonte por chave', function () {
    $reg = new FonteRegistry([new CadastroFonte()]);
    expect($reg->get('cadastro'))->toBeInstanceOf(CadastroFonte::class);
    expect($reg->get('inexistente'))->toBeNull();
});

it('diz se cobre todas as chaves de um plano', function () {
    $reg = new FonteRegistry([new CadastroFonte()]);
    expect($reg->cobre(['cadastro']))->toBeTrue();
    expect($reg->cobre(['cadastro', 'cnd_federal']))->toBeFalse();
    expect($reg->cobre([]))->toBeFalse();
});

it('devolve as fontes de uma lista de chaves (só as conhecidas)', function () {
    $reg = new FonteRegistry([new CadastroFonte()]);
    $fontes = $reg->fontesDe(['cadastro', 'cnd_federal']);
    expect($fontes)->toHaveCount(1);
    expect($fontes[0]->chave())->toBe('cadastro');
});
