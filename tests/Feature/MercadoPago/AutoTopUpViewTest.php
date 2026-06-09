<?php

use App\Models\RecargaAutomatica;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('mostra o opt-in de auto top-up por saldo com input de limite e rota', function () {
    config(['services.mercadopago.public_key' => 'TEST-PK', 'services.mercadopago.auto_topup.habilitado' => true]);
    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/creditos')->assertOk()->getContent();

    expect($html)->toContain('Auto top-up por saldo');
    expect($html)->toContain('recarga-saldo-limite');     // input do limite
    expect($html)->toContain('/app/recarga-automatica/saldo');
});

it('mostra banner de inadimplente quando a recarga por saldo está pausada', function () {
    config(['services.mercadopago.auto_topup.habilitado' => true]);
    $user = User::factory()->create();
    RecargaAutomatica::create([
        'user_id' => $user->id, 'gatilho' => 'saldo', 'limite_creditos' => 50,
        'pacote' => 'business', 'creditos' => 1000, 'valor' => 200, 'status' => 'inadimplente',
        'mp_customer_id' => 'CUS', 'mp_card_id' => 'CARD',
    ]);
    actingAs($user);

    $html = get('/app/creditos')->assertOk()->getContent();
    expect($html)->toContain('recarga pausada');
});
