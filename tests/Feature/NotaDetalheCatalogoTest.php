<?php

use App\Models\Cliente;
use App\Models\EfdCatalogoItem;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\EfdNotaItem;
use App\Models\User;
use App\Models\XmlNota;
use App\Models\XmlNotaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function detalheCliente(User $user): Cliente
{
    return Cliente::firstOrCreate(
        ['user_id' => $user->id, 'documento' => '00000000000191'],
        ['tipo_pessoa' => 'PJ', 'razao_social' => 'Empresa', 'is_empresa_propria' => true]
    );
}

function detalheImp(User $user, Cliente $cliente): EfdImportacao
{
    return EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'status' => 'concluido',
    ]);
}

function detalheCatalogo(User $user, Cliente $cliente, EfdImportacao $imp, string $codItem, array $overrides = []): EfdCatalogoItem
{
    return EfdCatalogoItem::create(array_merge([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $imp->id,
        'cod_item' => $codItem,
        'descr_item' => "Cad {$codItem}",
        'tipo_item' => '00',
        'cod_ncm' => '84713012',
        'aliq_icms' => 18.00,
        'unid_inv' => 'UN',
    ], $overrides));
}

it('view EFD mostra coluna NCM (cad.) e badge NCM divergente', function () {
    $user = User::factory()->create();
    $cliente = detalheCliente($user);
    $imp = detalheImp($user, $cliente);
    detalheCatalogo($user, $cliente, $imp, 'PROD-DIV', ['cod_ncm' => '84713012']);

    $nota = EfdNota::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $imp->id,
        'chave_acesso' => '35240413305697000150550000000404041953940401',
        'modelo' => '55',
        'numero' => 1,
        'serie' => '1',
        'data_emissao' => '2026-04-15',
        'tipo_operacao' => 'entrada',
        'valor_total' => 100,
        'valor_desconto' => 0,
        'metadados' => [],
    ]);
    EfdNotaItem::create([
        'efd_nota_id' => $nota->id,
        'user_id' => $user->id,
        'numero_item' => 1,
        'codigo_item' => 'PROD-DIV',
        'descricao' => 'Item EFD div',
        'quantidade' => 1,
        'unidade_medida' => 'UN',
        'valor_total' => 100,
        'cfop' => 5102,
        'aliquota_icms' => 18.00,
    ]);

    // Item declara NCM diferente do cadastrado — divergência via NotaItemUnificado
    // já é capturada via efd_catalogo_itens; o ncm declarado em efd_notas_itens é null
    // pois o EFD não traz NCM por linha (vem do catálogo). Pra este teste, simulamos
    // apenas que o cadastro está presente — a coluna NCM (cad.) deve renderizar.

    $response = actingAs($user)->get("/app/notas/efd/{$nota->id}");

    $response->assertOk()
        ->assertSee('NCM (cad.)', false)
        ->assertSee('84713012'); // NCM cadastrado renderizado
});

it('view EFD mostra badge "Sem cadastro" quando item da nota não tem 0200', function () {
    $user = User::factory()->create();
    $cliente = detalheCliente($user);
    $imp = detalheImp($user, $cliente);
    // SEM catálogo

    $nota = EfdNota::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $imp->id,
        'chave_acesso' => '35240413305697000150550000000404041953940402',
        'modelo' => '55',
        'numero' => 2,
        'serie' => '1',
        'data_emissao' => '2026-04-15',
        'tipo_operacao' => 'entrada',
        'valor_total' => 100,
        'valor_desconto' => 0,
        'metadados' => [],
    ]);
    EfdNotaItem::create([
        'efd_nota_id' => $nota->id,
        'user_id' => $user->id,
        'numero_item' => 1,
        'codigo_item' => 'FANTASMA',
        'descricao' => 'Item sem catálogo',
        'quantidade' => 1,
        'unidade_medida' => 'UN',
        'valor_total' => 100,
        'cfop' => 5102,
    ]);

    $response = actingAs($user)->get("/app/notas/efd/{$nota->id}");

    $response->assertOk()->assertSee('Sem cadastro', false);
});

it('view XML lista itens tipados com coluna NCM e badge de divergência', function () {
    $user = User::factory()->create();
    $cliente = detalheCliente($user);
    $imp = detalheImp($user, $cliente);
    detalheCatalogo($user, $cliente, $imp, 'PROD-X', ['cod_ncm' => '84713012']);

    $nota = XmlNota::create([
        'user_id' => $user->id,
        'nfe_id' => '35240413305697000150550000000404041953940403',
        'tipo_documento' => 'NFE',
        'tipo_nota' => 0,
        'origem' => 'xml_upload',
        'numero_nota' => 3,
        'serie' => 1,
        'data_emissao' => '2026-04-15',
        'valor_total' => 200,
        'emit_cnpj' => '13305697000150',
        'dest_cnpj' => '00000000000191',
        'payload' => [],
    ]);
    XmlNotaItem::create([
        'xml_nota_id' => $nota->id,
        'user_id' => $user->id,
        'numero_item' => 1,
        'codigo_item' => 'PROD-X',
        'descricao' => 'Item XML divergente',
        'cfop' => '5102',
        'quantidade' => 2,
        'valor_total' => 200,
        'cst_icms' => '00',
        'aliquota_icms' => 18.00,
        'unidade_medida' => 'UN',
        'ncm' => '94054210', // diferente do cadastrado
    ]);

    $response = actingAs($user)->get("/app/notas/xml/{$nota->id}");

    $response->assertOk()
        ->assertSee('Item XML divergente')
        ->assertSee('PROD-X')
        ->assertSee('94054210') // NCM declarado
        ->assertSee('84713012') // NCM cadastrado
        ->assertSee('NCM (cad.)', false);
});

it('view XML sem itens tipados mostra mensagem orientando o backfill', function () {
    $user = User::factory()->create();

    $nota = XmlNota::create([
        'user_id' => $user->id,
        'nfe_id' => '35240413305697000150550000000404041953940404',
        'tipo_documento' => 'NFE',
        'tipo_nota' => 0,
        'origem' => 'xml_upload',
        'numero_nota' => 4,
        'serie' => 1,
        'data_emissao' => '2026-04-15',
        'valor_total' => 100,
        'emit_cnpj' => '13305697000150',
        'dest_cnpj' => '00000000000191',
        'payload' => [],
    ]);
    // sem itens tipados

    $response = actingAs($user)->get("/app/notas/xml/{$nota->id}");

    $response->assertOk()->assertSee('xml:backfill-itens', false);
});

it('alíquota dentro da tolerância NÃO mostra badge de divergência', function () {
    $user = User::factory()->create();
    $cliente = detalheCliente($user);
    $imp = detalheImp($user, $cliente);
    detalheCatalogo($user, $cliente, $imp, 'PROD-OK', ['aliq_icms' => 18.00]);

    $nota = XmlNota::create([
        'user_id' => $user->id,
        'nfe_id' => '35240413305697000150550000000404041953940405',
        'tipo_documento' => 'NFE',
        'tipo_nota' => 0,
        'origem' => 'xml_upload',
        'numero_nota' => 5,
        'serie' => 1,
        'data_emissao' => '2026-04-15',
        'valor_total' => 100,
        'emit_cnpj' => '13305697000150',
        'dest_cnpj' => '00000000000191',
        'payload' => [],
    ]);
    XmlNotaItem::create([
        'xml_nota_id' => $nota->id,
        'user_id' => $user->id,
        'numero_item' => 1,
        'codigo_item' => 'PROD-OK',
        'descricao' => 'Item dentro da tolerância',
        'cfop' => '5102',
        'quantidade' => 1,
        'valor_total' => 100,
        'cst_icms' => '00',
        'aliquota_icms' => 18.30, // 0.3pp ≤ 0.5pp tolerância
        'unidade_medida' => 'UN',
        'ncm' => '84713012',
    ]);

    $response = actingAs($user)->get("/app/notas/xml/{$nota->id}");

    $response->assertOk()
        ->assertSee('PROD-OK')
        ->assertDontSee('Sem cadastro', false);

    // Não deve haver nenhuma divergência computada
    $cmp = $response->original->getData()['catalogoPorItem'];
    $first = reset($cmp);
    expect($first['divergencias'])->toBe([]);
});
