<?php

// tests/Feature/Admin/ImpersonacaoBannerTest.php
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('mostra o banner durante impersonação', function () {
    $alvo = User::factory()->create(['name' => 'Cliente Teste']);

    actingAs($alvo)->withSession(['impersonator_id' => 999])
        ->get('/app/dashboard')->assertOk()->assertSee('Voltar ao admin');
});

it('esconde o banner fora da impersonação', function () {
    $alvo = User::factory()->create(['name' => 'Cliente Teste']);

    actingAs($alvo)->get('/app/dashboard')->assertOk()->assertDontSee('Voltar ao admin');
});
