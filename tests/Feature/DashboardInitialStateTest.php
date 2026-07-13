<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renderiza o dashboard com o estado inicial do cockpit', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/app/dashboard')
        ->assertOk()
        ->assertViewHas('cockpit')
        ->assertViewHas('dashboardPrefs')
        ->assertViewHas('clientesOpcoes')
        ->assertSee('data-fornecedores-ranking', false)
        ->assertSee('Compras no período')
        ->assertSee('data-dashboard-stale', false)
        ->assertSee('Base fiscal desatualizada')
        ->assertSee('Importar EFD atualizada');
});
