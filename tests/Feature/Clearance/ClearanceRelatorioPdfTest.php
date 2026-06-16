<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\NfeConsulta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function seedLoteClearance(User $user): ConsultaLote
{
    $cliente = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Escritorio Contabil ME',
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
    ]);
    $chave = '35240413305697000150550000000404041953940992';

    EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id,
        'chave_acesso' => $chave, 'modelo' => '55', 'numero' => 40404, 'serie' => '0',
        'data_emissao' => '2026-01-15', 'tipo_operacao' => 'entrada', 'valor_total' => 1000.00,
        'valor_desconto' => 0, 'origem_arquivo' => 'fiscal', 'metadados' => [],
    ]);

    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 3, 'tab_id' => 'tab-pdf-http', 'processado_em' => now(),
    ]);

    NfeConsulta::create([
        'user_id' => $user->id, 'consulta_lote_id' => $lote->id, 'chave_acesso' => $chave,
        'tipo_documento' => 'NFE', 'modelo' => '55', 'status' => 'NAO_ENCONTRADA',
        'emit_nome' => 'Fornecedor Fria LTDA', 'emit_cnpj' => '13305697000150', 'consultado_em' => now(),
    ]);

    return $lote;
}

it('gera o PDF executivo do lote para o dono (200 application/pdf)', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    $lote = seedLoteClearance($user);

    $response = actingAs($user)->get("/app/clearance/notas/resultado/{$lote->id}/pdf");

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
    expect(substr((string) $response->getContent(), 0, 4))->toBe('%PDF');
});

it('nega o PDF de lote de outro usuário (404)', function () {
    $dono = User::factory()->trialAtivo()->create(['credits' => 100]);
    $lote = seedLoteClearance($dono);

    $intruso = User::factory()->trialAtivo()->create(['credits' => 100]);

    actingAs($intruso)
        ->get("/app/clearance/notas/resultado/{$lote->id}/pdf")
        ->assertNotFound();
});

it('404 quando o lote não tem resultados de clearance', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 0, 'creditos_cobrados' => 0, 'tab_id' => 'tab-vazio', 'processado_em' => now(),
    ]);

    actingAs($user)
        ->get("/app/clearance/notas/resultado/{$lote->id}/pdf")
        ->assertNotFound();
});

it('redireciona visitante não autenticado', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    $lote = seedLoteClearance($user);

    $this->get("/app/clearance/notas/resultado/{$lote->id}/pdf")
        ->assertRedirect();
});
