<?php

// tests/Feature/Admin/BloqueioEnforcementTest.php
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

it('usuário bloqueado é deslogado ao acessar /app', function () {
    $u = User::factory()->create(['bloqueado_em' => now()]);

    actingAs($u)->get('/app/dashboard')->assertRedirect(route('login'));
    expect(auth()->check())->toBeFalse();
});

it('usuário não bloqueado acessa normalmente', function () {
    $u = User::factory()->create(['bloqueado_em' => null]);
    actingAs($u)->get('/app/dashboard')->assertOk();
});

it('login de usuário bloqueado é rejeitado', function () {
    $u = User::factory()->create([
        'email' => 'bloq@ex.com', 'password' => bcrypt('senha12345'), 'bloqueado_em' => now(),
    ]);

    post('/login', ['email' => 'bloq@ex.com', 'password' => 'senha12345'])
        ->assertSessionHasErrors('email');
    expect(auth()->check())->toBeFalse();
});

it('login ajax de usuário bloqueado retorna instrução de suporte por WhatsApp', function () {
    User::factory()->create([
        'email' => 'bloq-ajax@ex.com', 'password' => bcrypt('senha12345'), 'bloqueado_em' => now(),
    ]);

    post('/login', ['email' => 'bloq-ajax@ex.com', 'password' => 'senha12345'], [
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ])
        ->assertStatus(403)
        ->assertJson([
            'success' => false,
            'support' => true,
            'support_label' => 'Falar no WhatsApp',
        ])
        ->assertJsonPath('support_url', config('support.whatsapp_url'));

    expect(auth()->check())->toBeFalse();
});
