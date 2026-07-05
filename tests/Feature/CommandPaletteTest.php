<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('emite o registry da palette no layout autenticado', function () {
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)
        ->toContain('window.paletteRegistry')
        ->toContain('\/app\/consulta\/painel'); // @json escapa as barras
});

it('não expõe destinos admin no registry de usuário comum', function () {
    $user = User::factory()->trialAtivo()->create(['is_admin' => false]);

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)->not->toContain('\/app\/admin\/usuarios');
});

it('expõe destinos admin no registry de admin', function () {
    $user = User::factory()->trialAtivo()->create(['is_admin' => true]);

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)->toContain('\/app\/admin\/usuarios');
});

it('respeita a flag da busca avulsa no registry', function () {
    config()->set('clearance.busca_avulsa.habilitada', false);
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)->not->toContain('\/app\/clearance\/buscar');
});
