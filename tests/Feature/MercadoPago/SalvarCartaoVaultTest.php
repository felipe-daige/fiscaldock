<?php

use App\Actions\MercadoPago\SalvarCartaoVault;
use App\Models\RecargaAutomatica;
use App\Models\User;
use Illuminate\Support\Facades\Http;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(fn () => config([
    'services.mercadopago.access_token' => 'TEST-token',
    'services.mercadopago.base_url' => 'https://api.mercadopago.com',
    'services.mercadopago.preapproval_teto_centavos' => 400000,
]));

function fakeVaultOk(): void
{
    Http::fake([
        'api.mercadopago.com/v1/customers/search*' => Http::response(['results' => []], 200),
        'api.mercadopago.com/v1/customers' => Http::response(['id' => 'CUS-1'], 201),
        'api.mercadopago.com/v1/customers/CUS-1/cards' => Http::response(['id' => 'CARD-1'], 201),
    ]);
}

it('salva cartão, grava refs e marca gatilho=saldo ativa', function () {
    fakeVaultOk();
    $user = User::factory()->create();

    $r = (new SalvarCartaoVault)->execute($user, 'tok-abc', 'business', 50);

    expect($r->gatilho)->toBe('saldo');
    expect($r->status)->toBe('ativa');
    expect($r->limite_creditos)->toBe(50);
    expect($r->mp_customer_id)->toBe('CUS-1');
    expect($r->mp_card_id)->toBe('CARD-1');
    expect($r->creditos)->toBe(1000); // business
    expect($r->mp_preapproval_id)->toBeNull();
});

it('reusa customer existente (não cria outro)', function () {
    Http::fake([
        'api.mercadopago.com/v1/customers/search*' => Http::response(['results' => [['id' => 'CUS-X']]], 200),
        'api.mercadopago.com/v1/customers/CUS-X/cards' => Http::response(['id' => 'CARD-X'], 201),
    ]);
    $user = User::factory()->create();
    $r = (new SalvarCartaoVault)->execute($user, 'tok', 'business', 50);
    expect($r->mp_customer_id)->toBe('CUS-X');
    Http::assertNotSent(fn ($req) => $req->method() === 'POST' && $req->url() === 'https://api.mercadopago.com/v1/customers');
});

it('recusa quando o pacote não supera o limite (loop)', function () {
    fakeVaultOk();
    $user = User::factory()->create();
    // business = 1000 cr; limite 1000 não é < 1000 → inválido.
    expect(fn () => (new SalvarCartaoVault)->execute($user, 'tok', 'business', 1000))
        ->toThrow(RuntimeException::class);
    expect(RecargaAutomatica::count())->toBe(0);
});

it('recusa pacote inválido', function () {
    $user = User::factory()->create();
    Http::fake();
    expect(fn () => (new SalvarCartaoVault)->execute($user, 'tok', 'inexistente', 50))
        ->toThrow(RuntimeException::class);
});
