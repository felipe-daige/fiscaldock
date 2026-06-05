<?php

use App\Services\Consultas\ClassificadorCodigo;

it('classifica cada grupo de código InfoSimples', function () {
    $c = new ClassificadorCodigo();
    expect($c->classificar(200))->toBe('sucesso');
    expect($c->classificar(201))->toBe('sucesso');
    expect($c->classificar(612))->toBe('nao_encontrado');
    expect($c->classificar(611))->toBe('erro_participante');
    expect($c->classificar(620))->toBe('erro_participante');
    expect($c->classificar(613))->toBe('retry');
    expect($c->classificar(601))->toBe('fatal');
    expect($c->classificar(622))->toBe('fatal');
});

it('trata código desconhecido como fatal (conservador)', function () {
    expect((new ClassificadorCodigo())->classificar(999))->toBe('fatal');
});
