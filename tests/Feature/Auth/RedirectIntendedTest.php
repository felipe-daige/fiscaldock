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

test('resolveIntendedPath rejeita host externo', function () {
    makeLoginUser();

    $response = $this->withSession(['url.intended' => 'https://evil.com/app/x'])
        ->postJson('/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk()->assertJson(['redirect' => '/app/dashboard']);
});

test('resolveIntendedPath rejeita path fora de /app/', function () {
    makeLoginUser();

    $response = $this->withSession(['url.intended' => url('/inicio')])
        ->postJson('/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk()->assertJson(['redirect' => '/app/dashboard']);
});

test('resolveIntendedPath rejeita path traversal', function () {
    makeLoginUser();

    $response = $this->withSession(['url.intended' => url('/app/../admin')])
        ->postJson('/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk()->assertJson(['redirect' => '/app/dashboard']);
});

test('resolveIntendedPath preserva query string', function () {
    makeLoginUser();

    $response = $this->withSession(['url.intended' => url('/app/clearance/notas?status=ok&page=2')])
        ->postJson('/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk()->assertJson(['redirect' => '/app/clearance/notas?status=ok&page=2']);
});
