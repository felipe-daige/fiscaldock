<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function trackingSignupPayload(array $overrides = []): array
{
    return array_merge([
        'nome' => 'Titular',
        'sobrenome' => 'Tracking',
        'email' => 'tracking-trial@example.com',
        'telefone' => '67911112222',
        'senha' => 'Xk9382mZqp01',
        'senha_confirmation' => 'Xk9382mZqp01',
        'empresa' => 'Empresa Tracking',
        'cargo' => 'Contador',
        'documento' => '11144477735',
        'faturamento' => 'ate-1m',
        'desafio_principal' => 'compliance',
        'terms_aceitos' => true,
    ], $overrides);
}

it('enfileira o evento trial_start na sessão após signup bem-sucedido', function () {
    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->postJson('/criar-conta', trackingSignupPayload())
        ->assertOk();

    expect(User::where('email', 'tracking-trial@example.com')->exists())->toBeTrue();

    expect(session('tracking_events'))->toBe([
        ['name' => 'trial_start'],
    ]);
});

it('não enfileira trial_start quando o signup falha', function () {
    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->postJson('/criar-conta', trackingSignupPayload(['email' => 'nao-e-email']))
        ->assertStatus(422);

    expect(session('tracking_events'))->toBeNull();
});

it('não renderiza nada de tracking com a config desligada', function () {
    config(['tracking.enabled' => false, 'tracking.meta_pixel_id' => '123']);

    expect(trim(view('partials.tracking-base')->render()))->toBe('');
});

it('não renderiza tracking quando ligado mas sem nenhum ID configurado', function () {
    config([
        'tracking.enabled' => true,
        'tracking.meta_pixel_id' => null,
        'tracking.ga4_id' => null,
        'tracking.google_ads_id' => null,
    ]);

    expect(trim(view('partials.tracking-base')->render()))->toBe('');
});

it('renderiza pixel e gtag com o gate de consentimento quando configurado', function () {
    config([
        'tracking.enabled' => true,
        'tracking.meta_pixel_id' => '1234567890',
        'tracking.ga4_id' => 'G-TESTE123',
        'tracking.google_ads_id' => 'AW-555',
        'tracking.ads_labels.trial_start' => 'labelTrial',
    ]);

    $html = view('partials.tracking-base')->render();

    expect($html)->toContain('1234567890')
        ->toContain('G-TESTE123')
        ->toContain('AW-555')
        ->toContain('labelTrial')
        // LGPD: nada carrega sem aceite explícito no banner de cookies.
        ->toContain('fd_cookie_consent');
});
