<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('lista usuários para o admin e filtra por busca', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    User::factory()->create(['name' => 'Zezinho', 'email' => 'ze@x.com', 'empresa' => 'ZeLtda']);

    $html = actingAs($admin)->get('/app/admin/usuarios')->assertOk()->getContent();
    expect($html)->toContain('Zezinho');

    actingAs($admin)->get('/app/admin/usuarios?q=ZeLtda')->assertOk()->assertSee('Zezinho');
});

it('mostra o detalhe de um usuário com KPIs', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $alvo = User::factory()->create(['name' => 'Fulano', 'email' => 'fulano@x.com']);

    actingAs($admin)->get('/app/admin/usuarios/'.$alvo->id)
        ->assertOk()
        ->assertSee('Fulano')
        ->assertSee('Conta e acesso')
        ->assertSee('Uso do produto')
        ->assertSee('Financeiro')
        ->assertSee('Atividade recente');
});

it('detalhe de usuário inexistente dá 404', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    actingAs($admin)->get('/app/admin/usuarios/99999')->assertNotFound();
});

it('não-admin não acessa a lista', function () {
    $u = User::factory()->create(['is_admin' => false]);
    actingAs($u)->get('/app/admin/usuarios')->assertStatus(403);
});
