<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function usuarioParaLogin(): User
{
    return User::factory()->create([
        'email' => 'volta@example.com',
        'password' => Hash::make('senhaCorreta123'),
    ]);
}

// ── Caso 1: deslogado acessa rota protegida (GET de página inteira) ──────────

test('deslogado em rota protegida é mandado para o login', function () {
    $this->get('/app/perfil')->assertRedirect('/login');
});

test('após login (web) volta para a rota protegida que tentou acessar', function () {
    usuarioParaLogin();

    // GET na rota protegida guarda a url.intended na sessão
    $this->get('/app/perfil')->assertRedirect('/login');

    $response = $this->post('/login', [
        'email' => 'volta@example.com',
        'password' => 'senhaCorreta123',
    ]);

    $response->assertRedirect('/app/perfil');
});

test('após login (ajax) o campo redirect aponta para a rota protegida', function () {
    usuarioParaLogin();

    $this->get('/app/perfil')->assertRedirect('/login');

    $response = $this->post('/login', [
        'email' => 'volta@example.com',
        'password' => 'senhaCorreta123',
    ], ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk();
    expect($response->json('redirect'))->toEndWith('/app/perfil');
});

test('login sem destino guardado cai no dashboard (fallback)', function () {
    usuarioParaLogin();

    $response = $this->post('/login', [
        'email' => 'volta@example.com',
        'password' => 'senhaCorreta123',
    ]);

    $response->assertRedirect('/app/dashboard');
});

// ── Caso 2: sessão expira no meio (AJAX) → /login?redirect=<pagina> ───────────

test('login com ?redirect válido guarda o destino e volta após autenticar', function () {
    usuarioParaLogin();

    $this->get('/login?redirect='.urlencode('/app/clientes'));

    $response = $this->post('/login', [
        'email' => 'volta@example.com',
        'password' => 'senhaCorreta123',
    ]);

    $response->assertRedirect('/app/clientes');
});

test('login com ?redirect preservando querystring volta com a querystring', function () {
    usuarioParaLogin();

    $this->get('/login?redirect='.urlencode('/app/clientes?pagina=3'));

    $response = $this->post('/login', [
        'email' => 'volta@example.com',
        'password' => 'senhaCorreta123',
    ]);

    $response->assertRedirect('/app/clientes?pagina=3');
});

// ── Caso 2: segurança contra open redirect ──────────────────────────────────

test('?redirect para host externo é ignorado (cai no dashboard)', function () {
    usuarioParaLogin();

    $this->get('/login?redirect='.urlencode('https://evil.com/phish'));

    $response = $this->post('/login', [
        'email' => 'volta@example.com',
        'password' => 'senhaCorreta123',
    ]);

    $response->assertRedirect('/app/dashboard');
});

test('?redirect protocol-relative (//evil) é ignorado', function () {
    usuarioParaLogin();

    $this->get('/login?redirect='.urlencode('//evil.com/phish'));

    $response = $this->post('/login', [
        'email' => 'volta@example.com',
        'password' => 'senhaCorreta123',
    ]);

    $response->assertRedirect('/app/dashboard');
});

test('?redirect para caminho fora de /app é ignorado', function () {
    usuarioParaLogin();

    $this->get('/login?redirect='.urlencode('/logout'));

    $response = $this->post('/login', [
        'email' => 'volta@example.com',
        'password' => 'senhaCorreta123',
    ]);

    $response->assertRedirect('/app/dashboard');
});
