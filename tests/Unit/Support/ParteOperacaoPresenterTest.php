<?php

use App\Models\Cliente;
use App\Models\Participante;
use App\Support\DesignSystem\ParteOperacaoPresenter;

it('entrega o mesmo contrato completo para cliente e participante', function () {
    $cliente = new Cliente([
        'tipo_pessoa' => 'PJ',
        'documento' => '11222333000181',
        'razao_social' => 'Cliente Fiscal',
        'uf' => 'MS',
        'municipio' => 'Campo Grande',
        'cep' => '79002000',
    ]);
    $cliente->id = 10;
    $participante = new Participante([
        'documento' => '44555666000102',
        'razao_social' => 'Participante Fiscal',
        'uf' => 'SP',
        'municipio' => 'São Paulo',
    ]);
    $participante->id = 20;

    $cardCliente = ParteOperacaoPresenter::card($cliente, 'Cliente');
    $cardParticipante = ParteOperacaoPresenter::card($participante, 'Participante');

    expect($cardCliente['href'])->toBe('/app/cliente/10')
        ->and($cardParticipante['href'])->toBe('/app/participante/20')
        ->and($cardCliente['campos'])->toHaveCount(9)
        ->and($cardParticipante['campos'])->toHaveCount(9)
        ->and(array_column($cardCliente['campos'], 'label'))
        ->toBe(array_column($cardParticipante['campos'], 'label'))
        ->and(data_get($cardCliente, 'campos.3.valor'))->toBe('79002-000');
});

it('mantem quatro celulas no compacto mesmo sem perfil cadastral', function () {
    $card = ParteOperacaoPresenter::card(
        null,
        'Destinatário',
        fallback: [
            'nome' => 'Parte somente no XML',
            'documento' => '97551165000193',
            'uf' => 'MS',
        ],
        modo: ParteOperacaoPresenter::MODO_COMPACTO,
        papel: 'Participante',
        papelHex: '#6b7280',
    );

    expect($card['href'])->toBeNull()
        ->and($card['nome'])->toBe('Parte somente no XML')
        ->and($card['campos'])->toHaveCount(4)
        ->and(data_get($card, 'campos.0.valor'))->toBe('97.551.165/0001-93')
        ->and(data_get($card, 'campos.2.valor'))->toBe('MS');
});
