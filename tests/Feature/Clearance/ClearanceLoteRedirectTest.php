<?php

use App\Models\ConsultaLote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('lote de clearance (plano_id null) em /app/consulta/lote redireciona pro resultado do clearance', function () {
    $user = User::factory()->create();
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1, 'creditos_cobrados' => 3, 'tab_id' => 'tab-r', 'processado_em' => now(),
    ]);

    actingAs($user)
        ->get("/app/consulta/lote/{$lote->id}")
        ->assertRedirect(route('app.clearance.notas.resultado', ['consultaLoteId' => $lote->id]));
});
