<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function clearanceCardSeedDivergencia(User $user): void
{
    $cli = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'EMPRESA', 'documento' => '00000000000100',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $imp = DB::table('efd_importacoes')->insertGetId([
        'user_id' => $user->id, 'cliente_id' => $cli, 'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'c.txt', 'status' => 'concluido', 'iniciado_em' => now(), 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('efd_catalogo_itens')->insert([
        'user_id' => $user->id, 'cliente_id' => $cli, 'importacao_id' => $imp, 'cod_item' => 'DIV',
        'descr_item' => 'X', 'tipo_item' => '00', 'cod_ncm' => '11112222', 'aliq_icms' => 18, 'unid_inv' => 'UN',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $nid = DB::table('xml_notas')->insertGetId([
        'user_id' => $user->id, 'cliente_id' => $cli, 'chave_acesso' => str_pad('D', 44, '0', STR_PAD_LEFT),
        'tipo_documento' => 'NFE', 'numero_documento' => '1', 'serie' => '1', 'data_emissao' => '2024-01-10',
        'tipo_nota' => 1, 'modelo' => '55', 'emit_documento' => '00000000000100', 'dest_documento' => '99999999000191',
        'valor_total' => 10.0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('xml_notas_itens')->insert([
        'xml_nota_id' => $nid, 'user_id' => $user->id, 'numero_item' => 1, 'codigo_item' => 'DIV',
        'descricao' => 'item', 'quantidade' => 1, 'valor_total' => 10.0, 'cfop' => 5102, 'aliquota_icms' => 18,
        'ncm' => '99998888', 'created_at' => now(), 'updated_at' => now(),
    ]);
}

it('mostra o card Catálogo × Documentos quando há sinal', function () {
    $user = User::factory()->create();
    clearanceCardSeedDivergencia($user);

    actingAs($user)->get('/app/clearance/alertas')
        ->assertOk()
        ->assertSee('Catálogo × Documentos')
        ->assertSee('Itens com NCM a revisar (documento × cadastro)'); // corpo do card renderizado
});

it('esconde o card quando não há sinal', function () {
    $user = User::factory()->create();

    actingAs($user)->get('/app/clearance/alertas')
        ->assertOk()
        ->assertDontSee('Catálogo × Documentos');
});
