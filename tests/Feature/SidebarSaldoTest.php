<?php

use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('sidebar mostra o saldo em R$ ao lado do perfil', function () {
    $u = User::factory()->create(['credits' => 42]);
    $resp = $this->actingAs($u)->get('/app/dashboard');
    $resp->assertOk();
    $resp->assertSee('data-sidebar-saldo', false);
    // 42 créditos × R$ 0,20 — a UI fala só em R$ (créditos são unidade interna do ledger)
    $resp->assertSee('R$ 8,40');
});
