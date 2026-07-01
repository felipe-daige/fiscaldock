<?php

use App\Models\IntegracaoStatus;
use App\Models\User;
use Database\Seeders\IntegracaoStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function adminIntegUser(): User
{
    return User::factory()->create(['is_admin' => true]);
}

it('bloqueia não-admin no índice de integrações', function () {
    actingAs(User::factory()->create(['is_admin' => false]))
        ->get('/app/admin/integracoes')->assertStatus(403);
});

it('admin abre o índice', function () {
    (new IntegracaoStatusSeeder())->run();
    actingAs(adminIntegUser())->get('/app/admin/integracoes')->assertOk();
});

it('admin atualiza status e mensagem e grava atualizado_por', function () {
    (new IntegracaoStatusSeeder())->run();
    $a = adminIntegUser();
    $cnd = IntegracaoStatus::where('chave', 'cnd_federal')->first();

    actingAs($a)->put(route('app.admin.integracoes.update', $cnd), [
        'status' => 'fora', 'mensagem' => 'Receita instável',
    ])->assertRedirect();

    $cnd->refresh();
    expect($cnd->status)->toBe('fora');
    expect($cnd->mensagem)->toBe('Receita instável');
    expect($cnd->atualizado_por)->toBe($a->id);
});

it('rejeita status inválido', function () {
    (new IntegracaoStatusSeeder())->run();
    $cnd = IntegracaoStatus::where('chave', 'cnd_federal')->first();
    actingAs(adminIntegUser())->put(route('app.admin.integracoes.update', $cnd), [
        'status' => 'explodiu',
    ])->assertSessionHasErrors('status');
});
