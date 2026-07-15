<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\EfdNotaItem;
use App\Models\NfeConsulta;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use App\Models\XmlNotaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Resultado do lote de clearance exibe os itens negociados de cada documento,
 * resolvidos por chave contra o acervo (xml_notas preferido; EFD com fallback gêmea).
 */
function resultadoItensBase(User $user): Cliente
{
    return Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Empresa Propria',
    ]);
}

it('mostra os itens do acervo XML no resultado do lote', function () {
    $user = User::factory()->create(['credits' => 100]);
    $cliente = resultadoItensBase($user);
    $chave = str_repeat('4', 44);

    $importacao = XmlImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'status' => 'concluido', 'tipo_documento' => 'NFE',
    ]);
    $nota = XmlNota::create([
        'user_id' => $user->id, 'importacao_xml_id' => $importacao->id, 'cliente_id' => $cliente->id,
        'chave_acesso' => $chave, 'tipo_documento' => 'NFE', 'numero_documento' => 44001, 'serie' => 1,
        'data_emissao' => '2026-02-01 10:00:00', 'valor_total' => 500.00, 'tipo_nota' => XmlNota::TIPO_SAIDA,
        'emit_cliente_id' => $cliente->id, 'emit_documento' => $cliente->documento,
        'emit_razao_social' => $cliente->razao_social,
        'dest_documento' => '97551165000193', 'dest_razao_social' => 'Comprador Resultado',
    ]);
    XmlNotaItem::create([
        'xml_nota_id' => $nota->id, 'user_id' => $user->id, 'numero_item' => 1,
        'codigo_item' => 'SKU-RES', 'descricao' => 'Martelete Rompedor 800W',
        'quantidade' => 1, 'unidade_medida' => 'UN', 'valor_unitario' => 500.00, 'valor_total' => 500.00,
        'cfop' => '5102', 'ncm' => '84672100',
    ]);

    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 1, 'tab_id' => 'tab-itens', 'processado_em' => now(),
    ]);
    NfeConsulta::create([
        'user_id' => $user->id, 'consulta_lote_id' => $lote->id, 'chave_acesso' => $chave,
        'tipo_documento' => 'NFE', 'modelo' => '55', 'numero' => '44001', 'serie' => '1',
        'status' => 'AUTORIZADA', 'valor_total' => 500.00, 'consultado_em' => now(),
    ]);

    actingAs($user)
        ->get("/app/clearance/notas/resultado/{$lote->id}")
        ->assertOk()
        ->assertSee('Itens da nota (1)')
        ->assertSee('Martelete Rompedor 800W')
        ->assertSee('SKU-RES');
});

it('resolve itens da gemea EFD contribuicoes no resultado do lote', function () {
    $user = User::factory()->create(['credits' => 100]);
    $cliente = resultadoItensBase($user);
    $chave = '35240413305697000150550000000404041953940992';

    $impFiscal = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
    ]);
    $impContrib = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD PIS/COFINS', 'status' => 'concluido',
    ]);
    $base = [
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'chave_acesso' => $chave, 'modelo' => '55',
        'numero' => 40404, 'serie' => '0', 'data_emissao' => '2026-01-15', 'tipo_operacao' => 'saida',
        'valor_total' => 320.00, 'valor_desconto' => 0, 'metadados' => [],
    ];
    EfdNota::create($base + ['importacao_id' => $impFiscal->id, 'origem_arquivo' => 'fiscal']);
    $gemea = EfdNota::create($base + ['importacao_id' => $impContrib->id, 'origem_arquivo' => 'contribuicoes']);
    EfdNotaItem::create([
        'efd_nota_id' => $gemea->id, 'user_id' => $user->id, 'numero_item' => 1,
        'codigo_item' => 'PROD-77', 'descricao' => 'Serra Circular 1400W',
        'quantidade' => 2, 'unidade_medida' => 'UN', 'valor_unitario' => 160.00, 'valor_total' => 320.00,
        'cfop' => '5102',
    ]);

    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 1, 'tab_id' => 'tab-gemea', 'processado_em' => now(),
    ]);
    NfeConsulta::create([
        'user_id' => $user->id, 'consulta_lote_id' => $lote->id, 'chave_acesso' => $chave,
        'tipo_documento' => 'NFE', 'modelo' => '55', 'numero' => '40404', 'serie' => '0',
        'status' => 'AUTORIZADA', 'valor_total' => 320.00, 'consultado_em' => now(),
    ]);

    actingAs($user)
        ->get("/app/clearance/notas/resultado/{$lote->id}")
        ->assertOk()
        ->assertSee('Serra Circular 1400W')
        ->assertSee('Itens via EFD Contribuições');
});
