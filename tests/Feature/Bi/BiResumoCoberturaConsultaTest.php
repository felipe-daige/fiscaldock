<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('/app/bi/resumo inclui cobertura_consulta (não-consultados + sem-uf)', function () {
    $user = User::factory()->create();
    $cli = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'documento' => '00000000000191', 'razao_social' => 'E',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('participantes')->insert([
        'user_id' => $user->id, 'cliente_id' => $cli, 'razao_social' => 'P', 'documento' => '11111111000111',
        'origem_tipo' => 'MANUAL', 'situacao_cadastral' => null, 'uf' => null,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $resp = $this->actingAs($user)->getJson('/app/bi/resumo');
    $resp->assertOk()
        ->assertJsonStructure(['cobertura_consulta' => ['total', 'sem_consulta', 'sem_uf']]);
    expect($resp->json('cobertura_consulta.sem_consulta'))->toBe(1);
});
