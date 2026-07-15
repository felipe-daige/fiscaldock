<?php

use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('sidebar mostra o saldo em R$ ao lado do perfil', function () {
    $u = User::factory()->create(['credits' => 42]);
    $resp = $this->actingAs($u)->get('/app/dashboard');
    $resp->assertOk();
    $resp->assertSee('data-sidebar-saldo', false);
    // saldo em R$ direto (ledger é em reais)
    $resp->assertSee(\App\Support\Dinheiro::brl(42.00));
});
