<?php

use App\Models\Cliente;
use App\Models\EfdCatalogoItem;
use App\Models\EfdImportacao;
use App\Models\User;
use App\Services\Catalogo\CatalogoComparacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function comparacaoCatalogo(User $user, string $codItem, array $overrides = []): EfdCatalogoItem
{
    $cliente = Cliente::firstOrCreate(
        ['user_id' => $user->id, 'documento' => '00000000000191'],
        ['tipo_pessoa' => 'PJ', 'razao_social' => 'Empresa', 'is_empresa_propria' => true]
    );
    $imp = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'status' => 'concluido',
    ]);

    return EfdCatalogoItem::create(array_merge([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $imp->id,
        'cod_item' => $codItem,
        'descr_item' => "Catálogo {$codItem}",
        'tipo_item' => '00',
        'cod_ncm' => '84713012',
        'aliq_icms' => 18.00,
        'unid_inv' => 'UN',
    ], $overrides));
}

beforeEach(function () {
    $this->service = app(CatalogoComparacaoService::class);
});

it('item sem cadastro tem cadastro null e zero divergências', function () {
    $user = User::factory()->create();
    $itens = collect([(object) [
        'id' => 1,
        'codigo_item' => 'NÃO-EXISTE',
        'ncm' => '84713012',
        'unidade_medida' => 'UN',
        'aliquota_icms' => 18.0,
    ]]);

    $resultado = $this->service->indexarComparacaoPorItem($user->id, $itens);

    expect($resultado[1]['cadastro'])->toBeNull()
        ->and($resultado[1]['divergencias'])->toBe([]);
});

it('item idêntico ao catálogo retorna divergências vazias', function () {
    $user = User::factory()->create();
    comparacaoCatalogo($user, 'PROD-1');

    $itens = collect([(object) [
        'id' => 10,
        'codigo_item' => 'PROD-1',
        'ncm' => '84713012',
        'unidade_medida' => 'UN',
        'aliquota_icms' => 18.0,
    ]]);

    $resultado = $this->service->indexarComparacaoPorItem($user->id, $itens);

    expect($resultado[10]['cadastro'])->not->toBeNull()
        ->and($resultado[10]['cadastro']['cod_ncm'])->toBe('84713012')
        ->and($resultado[10]['divergencias'])->toBe([]);
});

it('detecta divergência de NCM', function () {
    $user = User::factory()->create();
    comparacaoCatalogo($user, 'PROD-NCM', ['cod_ncm' => '84713012']);

    $itens = collect([(object) [
        'id' => 1,
        'codigo_item' => 'PROD-NCM',
        'ncm' => '94054210',
        'unidade_medida' => 'UN',
        'aliquota_icms' => 18.0,
    ]]);

    $resultado = $this->service->indexarComparacaoPorItem($user->id, $itens);

    expect($resultado[1]['divergencias'])->toContain('ncm');
});

it('detecta divergência de unidade', function () {
    $user = User::factory()->create();
    comparacaoCatalogo($user, 'PROD-U', ['unid_inv' => 'UN']);

    $itens = collect([(object) [
        'id' => 1,
        'codigo_item' => 'PROD-U',
        'ncm' => '84713012',
        'unidade_medida' => 'PC',
        'aliquota_icms' => 18.0,
    ]]);

    expect($this->service->indexarComparacaoPorItem($user->id, $itens)[1]['divergencias'])
        ->toContain('unidade');
});

it('alíquota dentro da tolerância (0,3pp) NÃO marca divergência', function () {
    $user = User::factory()->create();
    comparacaoCatalogo($user, 'PROD-AL1', ['aliq_icms' => 18.00]);

    $itens = collect([(object) [
        'id' => 1,
        'codigo_item' => 'PROD-AL1',
        'ncm' => '84713012',
        'unidade_medida' => 'UN',
        'aliquota_icms' => 18.30,
    ]]);

    expect($this->service->indexarComparacaoPorItem($user->id, $itens)[1]['divergencias'])
        ->not->toContain('aliquota');
});

it('alíquota fora da tolerância (0,8pp) marca divergência', function () {
    $user = User::factory()->create();
    comparacaoCatalogo($user, 'PROD-AL2', ['aliq_icms' => 18.00]);

    $itens = collect([(object) [
        'id' => 1,
        'codigo_item' => 'PROD-AL2',
        'ncm' => '84713012',
        'unidade_medida' => 'UN',
        'aliquota_icms' => 18.80,
    ]]);

    expect($this->service->indexarComparacaoPorItem($user->id, $itens)[1]['divergencias'])
        ->toContain('aliquota');
});

it('múltiplas divergências no mesmo item são todas listadas', function () {
    $user = User::factory()->create();
    comparacaoCatalogo($user, 'PROD-MULT', ['cod_ncm' => '84713012', 'unid_inv' => 'UN', 'aliq_icms' => 18.00]);

    $itens = collect([(object) [
        'id' => 1,
        'codigo_item' => 'PROD-MULT',
        'ncm' => '94054210',
        'unidade_medida' => 'PC',
        'aliquota_icms' => 4.00,
    ]]);

    $divergencias = $this->service->indexarComparacaoPorItem($user->id, $itens)[1]['divergencias'];

    expect($divergencias)->toContain('ncm', 'unidade', 'aliquota');
});

it('catálogo sem aliq_icms cadastrada não compara alíquota', function () {
    $user = User::factory()->create();
    comparacaoCatalogo($user, 'PROD-NULL', ['aliq_icms' => null]);

    $itens = collect([(object) [
        'id' => 1,
        'codigo_item' => 'PROD-NULL',
        'ncm' => '84713012',
        'unidade_medida' => 'UN',
        'aliquota_icms' => 4.00,
    ]]);

    expect($this->service->indexarComparacaoPorItem($user->id, $itens)[1]['divergencias'])
        ->not->toContain('aliquota');
});

it('isolamento por user_id (catálogo de outro usuário não vaza)', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    comparacaoCatalogo($alice, 'COMPARTILHADO', ['cod_ncm' => '84713012']);

    $itens = collect([(object) [
        'id' => 1,
        'codigo_item' => 'COMPARTILHADO',
        'ncm' => '94054210',
        'unidade_medida' => 'UN',
        'aliquota_icms' => 18.0,
    ]]);

    // Bob não tem cadastro desse cod_item — cadastro null
    expect($this->service->indexarComparacaoPorItem($bob->id, $itens)[1]['cadastro'])->toBeNull();
    // Alice tem
    expect($this->service->indexarComparacaoPorItem($alice->id, $itens)[1]['cadastro'])->not->toBeNull();
});

it('quando o usuário tem o mesmo cod_item em clientes diferentes, usa o registro mais recente', function () {
    $user = User::factory()->create();

    // unique (cliente_id, cod_item) impede 2 versões dentro do mesmo cliente.
    // A regra "versão mais recente" cobre o caso de múltiplos clientes do mesmo usuário
    // com o mesmo código (cenário realista: empresa principal + filial).
    $clienteA = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '11111111000111',
        'razao_social' => 'Cliente A',
        'is_empresa_propria' => false,
    ]);
    $clienteB = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '22222222000222',
        'razao_social' => 'Cliente B',
        'is_empresa_propria' => false,
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $clienteA->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'status' => 'concluido',
    ]);

    // Cadastro mais antigo em cliente A
    EfdCatalogoItem::create([
        'user_id' => $user->id,
        'cliente_id' => $clienteA->id,
        'importacao_id' => $imp->id,
        'cod_item' => 'PROD-VER',
        'descr_item' => 'Versão antiga',
        'tipo_item' => '00',
        'cod_ncm' => '84713012',
        'unid_inv' => 'UN',
    ]);
    // Cadastro mais recente em cliente B (id maior — vence)
    EfdCatalogoItem::create([
        'user_id' => $user->id,
        'cliente_id' => $clienteB->id,
        'importacao_id' => $imp->id,
        'cod_item' => 'PROD-VER',
        'descr_item' => 'Versão nova',
        'tipo_item' => '00',
        'cod_ncm' => '94054210',
        'unid_inv' => 'UN',
    ]);

    $itens = collect([(object) [
        'id' => 1,
        'codigo_item' => 'PROD-VER',
        'ncm' => '94054210',
        'unidade_medida' => 'UN',
        'aliquota_icms' => 18.0,
    ]]);

    $resultado = $this->service->indexarComparacaoPorItem($user->id, $itens);

    expect($resultado[1]['cadastro']['cod_ncm'])->toBe('94054210')
        ->and($resultado[1]['divergencias'])->not->toContain('ncm');
});
