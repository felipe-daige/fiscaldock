<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('redireciona /app/monitoramento/clientes para o painel com 301', function () {
    $user = User::factory()->create();
    $r = $this->actingAs($user)->get('/app/monitoramento/clientes');
    $r->assertStatus(301)->assertRedirect('/app/monitoramento');
});

it('preserva query string no redirect', function () {
    $user = User::factory()->create();
    $r = $this->actingAs($user)->get('/app/monitoramento/clientes?tipo=cliente');
    $r->assertStatus(301);
    expect($r->headers->get('Location'))->toContain('tipo=cliente');
});
