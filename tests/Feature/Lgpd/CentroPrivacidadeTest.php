<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('renderiza o centro de privacidade para o usuário autenticado', function () {
    $user = User::factory()->create(['email' => 'contador@exemplo.com', 'credits' => 50]);

    actingAs($user)
        ->get('/app/privacidade')
        ->assertOk()
        ->assertSee('Privacidade')
        ->assertSee('contador@exemplo.com');
});

it('redireciona visitante não autenticado', function () {
    $this->get('/app/privacidade')->assertRedirect();
});

it('o link para a política pública abre em nova aba e não é interceptado pelo SPA', function () {
    // Regressão: link com data-link para /privacidade (rota pública, fora de /app/) jogava o
    // usuário na landing e bugava o histórico do SPA no botão voltar. Deve abrir em aba nova.
    $user = User::factory()->create();

    $html = actingAs($user)->get('/app/privacidade')->assertOk()->getContent();

    // casa SÓ a URL pública exata (route('privacidade')), não o link de sidebar /app/privacidade
    $url = preg_quote(route('privacidade'), '/');

    // a âncora que aponta para a política pública precisa ter target="_blank" e NÃO ter data-link
    expect($html)->toMatch('/<a[^>]*href="'.$url.'"[^>]*target="_blank"/');
    expect($html)->not->toMatch('/<a[^>]*href="'.$url.'"[^>]*data-link/');
});

it('revoga o consentimento de marketing', function () {
    $user = User::factory()->create(['marketing_opt_in' => true, 'marketing_opt_in_at' => now()]);

    actingAs($user)->post('/app/privacidade/marketing/revogar')->assertRedirect();

    expect($user->fresh()->marketing_opt_in)->toBeFalse();
});

it('exporta os dados do titular em JSON (DSAR)', function () {
    $user = User::factory()->create(['email' => 'titular@exemplo.com', 'empresa' => 'Escritorio XYZ']);

    $response = actingAs($user)->get('/app/privacidade/exportar');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/json');
    expect($response->headers->get('content-disposition'))->toContain('attachment');
    $response->assertJsonPath('perfil.email', 'titular@exemplo.com')
        ->assertJsonPath('perfil.empresa', 'Escritorio XYZ');
});

it('registra o pedido de exclusão de conta (flag, sem apagar)', function () {
    $user = User::factory()->create();

    actingAs($user)->post('/app/privacidade/exclusao')->assertRedirect();

    $fresh = $user->fresh();
    expect($fresh->deletion_requested_at)->not->toBeNull();
    // não apaga a conta — o registro continua existente
    expect(User::find($user->id))->not->toBeNull();
});

it('cancela o pedido de exclusão de conta', function () {
    $user = User::factory()->create(['deletion_requested_at' => now()]);

    actingAs($user)->post('/app/privacidade/exclusao/cancelar')->assertRedirect();

    expect($user->fresh()->deletion_requested_at)->toBeNull();
});

it('não duplica o pedido de exclusão já existente', function () {
    $momento = now()->subDay();
    $user = User::factory()->create(['deletion_requested_at' => $momento]);

    actingAs($user)->post('/app/privacidade/exclusao')->assertRedirect();

    // mantém o timestamp original do primeiro pedido
    expect($user->fresh()->deletion_requested_at->timestamp)->toBe($momento->timestamp);
});
