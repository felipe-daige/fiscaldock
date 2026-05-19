<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

function makeLoginUser(): User
{
    return User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
    ]);
}

test('login sem intended redireciona para /app/dashboard (AJAX)', function () {
    makeLoginUser();

    $response = postJson('/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
    ], ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'redirect' => '/app/dashboard',
        ]);
});

test('login sem intended redireciona para /app/dashboard (web)', function () {
    makeLoginUser();

    $response = post('/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/app/dashboard');
});

test('login lê url.intended da sessão e redireciona (AJAX)', function () {
    makeLoginUser();

    $intended = url('/app/clearance/notas');

    $response = $this->withSession(['url.intended' => $intended])
        ->postJson('/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'redirect' => '/app/clearance/notas',
        ]);
});

test('login lê url.intended da sessão e redireciona (web)', function () {
    makeLoginUser();

    $intended = url('/app/clearance/notas');

    $response = $this->withSession(['url.intended' => $intended])
        ->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

    $response->assertRedirect('/app/clearance/notas');
});
