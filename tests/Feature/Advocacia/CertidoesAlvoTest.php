<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('devolve html vazio (sem consulta anterior) sem estourar', function () {
    $user = User::factory()->create();
    $pid = DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'documento' => '19131243000197', 'razao_social' => 'PART',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($user)->getJson("/app/consulta/alvo/participante/{$pid}/certidoes")
        ->assertOk()
        ->assertJson(['success' => true, 'tem_consulta' => false]);
});

it('nao vaza alvo de outro usuario (404)', function () {
    $dono = User::factory()->create();
    $outro = User::factory()->create();
    $pid = DB::table('participantes')->insertGetId([
        'user_id' => $dono->id, 'documento' => '19131243000197', 'razao_social' => 'P',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($outro)->getJson("/app/consulta/alvo/participante/{$pid}/certidoes")->assertNotFound();
});

it('rejeita tipo de alvo invalido na rota', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->getJson('/app/consulta/alvo/fornecedor/1/certidoes')->assertNotFound();
});
