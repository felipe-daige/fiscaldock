<?php

use App\Models\ConsultaLote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

// Os testes de despacho (webhook n8n) foram removidos no cutover de 2026-06-07: o clearance em
// lote roda 100% no Laravel. Cobertura do despacho/estorno: ClearanceLoteServiceTest,
// ClearanceValidarLaravelTest e FecharClearanceLoteServiceTest. Aqui ficam os testes do
// endpoint de RESULTADO lendo os snapshots persistidos em nfe_consultas.

function bulkDispatchUser(int $credits = 100): User
{
    return User::factory()->create(['credits' => $credits]);
}

it('resultado de notas ajax informa quando o resumo final está pronto', function () {
    $user = bulkDispatchUser(credits: 100);
    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'cliente_id' => null,
        'plano_id' => null,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 3,
        'tab_id' => 'tab-clearance-ajax-pronto',
        'processado_em' => now(),
    ]);

    \Illuminate\Support\Facades\DB::table('nfe_consultas')->insert([
        'user_id' => $user->id,
        'consulta_lote_id' => $lote->id,
        'chave_acesso' => '35240413305697000150550000000404041953940992',
        'tipo_documento' => 'NFE',
        'modelo' => '55',
        'numero' => '40404',
        'serie' => 1,
        'status' => 'AUTORIZADA',
        'valor_total' => 1000,
        'consultado_em' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($user)
        ->getJson("/app/clearance/notas/resultado/{$lote->id}?tipo_validacao=basico")
        ->assertOk()
        ->assertJsonPath('status_lote', ConsultaLote::STATUS_FINALIZADO)
        ->assertJsonPath('total_resultados', 1)
        ->assertJsonPath('resultado_pronto', true)
        ->assertJsonPath('resumo.total', 1);
});

it('resultado de notas ajax mantém resultado_pronto falso sem snapshots persistidos', function () {
    $user = bulkDispatchUser(credits: 100);
    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'cliente_id' => null,
        'plano_id' => null,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 3,
        'tab_id' => 'tab-clearance-ajax-aguardando',
        'processado_em' => now(),
    ]);

    actingAs($user)
        ->getJson("/app/clearance/notas/resultado/{$lote->id}?tipo_validacao=basico")
        ->assertOk()
        ->assertJsonPath('status_lote', ConsultaLote::STATUS_FINALIZADO)
        ->assertJsonPath('total_resultados', 0)
        ->assertJsonPath('resultado_pronto', false);
});
