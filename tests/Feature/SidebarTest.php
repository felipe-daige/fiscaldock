<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Sidebar: garante que a navegação de Monitoramento existe e que os marcadores
 * "Novo" + a ocultação da busca avulsa desabilitada não regridam.
 */
it('sidebar expõe a navegação de Monitoramento e pílulas Novo', function () {
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)->toContain('Monitoramento')
        ->toContain('/app/monitoramento/clientes')
        ->toContain('/app/monitoramento/grupos')
        ->toContain('Novo'); // pílula de item recém-lançado
});

it('sidebar leva direto às listagens e mantém o cadastro nos cabeçalhos', function () {
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)
        ->toContain('href="/app/clientes" data-link data-sidebar-link')
        ->toContain('href="/app/participantes" data-link data-sidebar-link')
        ->not->toContain('href="/app/cliente/novo"')
        ->not->toContain('href="/app/participante/novo"');

    actingAs($user)->get('/app/clientes')
        ->assertOk()
        ->assertSee('href="/app/cliente/novo"', false)
        ->assertSee('Novo Cliente');

    actingAs($user)->get('/app/participantes')
        ->assertOk()
        ->assertSee('href="/app/participante/novo"', false)
        ->assertSee('Novo Participante');
});

it('sidebar esconde Buscar Notas quando a busca avulsa está desabilitada', function () {
    config()->set('clearance.busca_avulsa.habilitada', false);
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)->not->toContain('/app/clearance/buscar');
});

it('sidebar mostra Buscar Notas quando a busca avulsa está habilitada', function () {
    config()->set('clearance.busca_avulsa.habilitada', true);
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)->toContain('/app/clearance/buscar');
});

it('sidebar leva direto a nova consulta e mantém historico/planos no cabeçalho da tela', function () {
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)
        ->toContain('href="/app/consulta/nova" data-link data-sidebar-link')
        ->not->toContain('href="/app/consulta/historico" data-link data-sidebar-link')
        ->not->toContain('href="/app/consulta/planos" data-link data-sidebar-link');

    actingAs($user)->get('/app/consulta/nova')
        ->assertOk()
        ->assertSee('href="/app/consulta/historico"', false)
        ->assertSee('href="/app/consulta/planos"', false);
});
