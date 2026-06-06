<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('busca de participantes é insensível a acento E maiúscula', function () {
    $user = User::factory()->create();
    // duas grafias do mesmo termo: com e sem acento
    foreach ([
        ['doc' => '11111111111111', 'nome' => 'COMÉRCIO DE PEÇAS LTDA'],
        ['doc' => '22222222222222', 'nome' => 'comercio de pecas me'],
    ] as $p) {
        DB::table('participantes')->insert([
            'user_id' => $user->id, 'documento' => $p['doc'], 'razao_social' => $p['nome'],
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    actingAs($user);

    // qualquer grafia acha os DOIS
    foreach (['comercio', 'COMÉRCIO', 'comércio', 'Comercio'] as $termo) {
        $resp = getJson('/app/consulta/nova/participantes?busca='.urlencode($termo))->assertOk();
        expect($resp->json('data'))->toHaveCount(2, "termo '{$termo}' deveria achar os 2");
    }
});
