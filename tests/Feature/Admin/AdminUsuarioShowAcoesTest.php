<?php
// tests/Feature/Admin/AdminUsuarioShowAcoesTest.php
use App\Models\AdminActionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('show mostra os cards de ação e a trilha do alvo', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $alvo = User::factory()->create();
    AdminActionLog::create([
        'admin_user_id' => $admin->id, 'target_user_id' => $alvo->id,
        'acao' => 'creditar', 'motivo' => 'log do alvo', 'created_at' => now(),
    ]);

    actingAs($admin)->get("/app/admin/usuarios/{$alvo->id}")
        ->assertOk()
        ->assertSee('Ações administrativas')
        ->assertSee('Trilha administrativa')
        ->assertSee('log do alvo');
});
