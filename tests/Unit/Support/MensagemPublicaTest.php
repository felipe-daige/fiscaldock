<?php

use App\Support\MensagemPublica;

it('remove a referência parentética ao provedor terceirizado', function () {
    expect(MensagemPublica::neutralizar('CND Municipal não disponível para DOURADOS/MS no provedor (InfoSimples).'))
        ->toBe('CND Municipal não disponível para DOURADOS/MS no provedor.');

    expect(MensagemPublica::neutralizar('CND Estadual não disponível para a UF MS no provedor (InfoSimples).'))
        ->toBe('CND Estadual não disponível para a UF MS no provedor.');
});

it('remove "via InfoSimples"', function () {
    expect(MensagemPublica::neutralizar('Dados consultados via InfoSimples com sucesso.'))
        ->toBe('Dados consultados com sucesso.');
});

it('substitui menção solta por "provedor"', function () {
    expect(MensagemPublica::neutralizar('Falha no InfoSimples ao consultar.'))
        ->toBe('Falha no provedor ao consultar.');
});

it('é case-insensitive e não deixa espaços duplos', function () {
    expect(MensagemPublica::neutralizar('Erro (infosimples)  ao  processar.'))
        ->toBe('Erro ao processar.');
});

it('passa adiante mensagens sem o provedor e trata null', function () {
    expect(MensagemPublica::neutralizar('CND Federal indisponível no momento.'))
        ->toBe('CND Federal indisponível no momento.');
    expect(MensagemPublica::neutralizar(null))->toBeNull();
    expect(MensagemPublica::neutralizar(''))->toBe('');
});
