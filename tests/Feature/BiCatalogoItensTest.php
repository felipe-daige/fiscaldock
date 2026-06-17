<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function seedBiUser(): array
{
    $user = User::factory()->create();
    $clienteId = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'EMPRESA', 'documento' => '00000000000100',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);

    return [$user, (int) $clienteId];
}

function biEfdItem(int $userId, int $clienteId, string $chave, string $codItem, float $valor): void
{
    $imp = EfdImportacao::create(['user_id' => $userId, 'cliente_id' => $clienteId, 'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'i.txt', 'status' => 'concluido', 'iniciado_em' => now()]);
    $nota = EfdNota::create([
        'user_id' => $userId, 'cliente_id' => $clienteId, 'importacao_id' => $imp->id, 'numero' => 1, 'serie' => '1',
        'data_emissao' => '2024-01-15', 'valor_desconto' => 0, 'cancelada' => false, 'chave_acesso' => $chave,
        'modelo' => '55', 'tipo_operacao' => 'saida', 'origem_arquivo' => 'fiscal', 'valor_total' => $valor,
    ]);
    DB::table('efd_notas_itens')->insert([
        'efd_nota_id' => $nota->id, 'user_id' => $userId, 'numero_item' => 1, 'codigo_item' => $codItem,
        'descricao' => 'item efd', 'quantidade' => 1, 'valor_total' => $valor, 'cfop' => 5102, 'aliquota_icms' => 18,
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

function biCatalogo(int $userId, int $clienteId, string $codItem, string $ncm = '99887766'): void
{
    $imp = EfdImportacao::create(['user_id' => $userId, 'cliente_id' => $clienteId, 'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'c.txt', 'status' => 'concluido', 'iniciado_em' => now()]);
    DB::table('efd_catalogo_itens')->insert([
        'user_id' => $userId, 'cliente_id' => $clienteId, 'importacao_id' => $imp->id, 'cod_item' => $codItem,
        'descr_item' => 'TEM CATALOGO', 'tipo_item' => '00', 'cod_ncm' => $ncm, 'aliq_icms' => 18, 'unid_inv' => 'UN',
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

it('exige autenticação', function () {
    $this->get('/app/bi/catalogo-itens')->assertRedirect(route('login'));
});

it('renderiza a tela com KPIs, itens e o alerta de item sem catálogo', function () {
    [$user, $clienteId] = seedBiUser();
    biCatalogo($user->id, $clienteId, 'COMCAT', '99887766');
    biEfdItem($user->id, $clienteId, str_pad('A', 44, '0', STR_PAD_LEFT), 'COMCAT', 100.0);
    biEfdItem($user->id, $clienteId, str_pad('C', 44, '0', STR_PAD_LEFT), 'SEMCAT', 40.0);

    $html = actingAs($user)->get('/app/bi/catalogo-itens')->assertOk()->getContent();

    expect($html)->toContain('COMCAT');
    expect($html)->toContain('SEMCAT');
    expect($html)->toContain('sem catálogo');
});

it('mostra colunas de procedência, NCM e os KPIs principais', function () {
    [$user, $clienteId] = seedBiUser();
    biCatalogo($user->id, $clienteId, 'P1', '12345678');
    biEfdItem($user->id, $clienteId, str_pad('A', 44, '0', STR_PAD_LEFT), 'P1', 100.0);

    $html = actingAs($user)->get('/app/bi/catalogo-itens')->assertOk()->getContent();

    expect($html)->toContain('Origem');           // cabeçalho da coluna procedência
    expect($html)->toContain('12345678');          // NCM (via catálogo)
    expect($html)->toContain('Valor movimentado'); // KPI
});

function biXmlItem(int $userId, int $clienteId, string $chave, string $codItem, float $valor, string $ncm): void
{
    $nid = DB::table('xml_notas')->insertGetId([
        'user_id' => $userId, 'cliente_id' => $clienteId, 'chave_acesso' => $chave, 'tipo_documento' => 'NFE',
        'numero_documento' => '1', 'serie' => '1', 'data_emissao' => '2024-02-10', 'tipo_nota' => 1, 'modelo' => '55',
        'emit_documento' => '00000000000100', 'dest_documento' => '99999999000191', 'valor_total' => $valor,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('xml_notas_itens')->insert([
        'xml_nota_id' => $nid, 'user_id' => $userId, 'numero_item' => 1, 'codigo_item' => $codItem,
        'descricao' => 'item xml', 'quantidade' => 1, 'valor_total' => $valor, 'cfop' => 5102, 'aliquota_icms' => 18,
        'ncm' => $ncm, 'created_at' => now(), 'updated_at' => now(),
    ]);
}

it('mostra o KPI e o painel de NCM a revisar quando o item XML diverge do catálogo', function () {
    [$user, $clienteId] = seedBiUser();
    biCatalogo($user->id, $clienteId, 'DIVITEM', '11112222');
    biXmlItem($user->id, $clienteId, str_pad('D', 44, '0', STR_PAD_LEFT), 'DIVITEM', 100.0, '99998888');

    $html = actingAs($user)->get('/app/bi/catalogo-itens')->assertOk()->getContent();

    expect($html)->toContain('NCM a revisar');   // KPI + título do painel
    expect($html)->toContain('DIVITEM');          // linha do painel
    expect($html)->toContain('99998888');         // NCM documento
    expect($html)->toContain('11112222');         // NCM cadastro
});

it('mostra a faixa de reconciliação quando há nota XML documentada', function () {
    [$user, $clienteId] = seedBiUser();
    $chave = str_pad('R', 44, '0', STR_PAD_LEFT);
    biEfdItem($user->id, $clienteId, $chave, 'P', 100.0);
    biXmlItem($user->id, $clienteId, $chave, 'P', 100.0, '12345678');

    $html = actingAs($user)->get('/app/bi/catalogo-itens')->assertOk()->getContent();

    expect($html)->toContain('Reconciliação documento');
    expect($html)->toContain('documentadas');
});

it('não mostra o painel de divergência quando não há divergência', function () {
    [$user, $clienteId] = seedBiUser();
    biCatalogo($user->id, $clienteId, 'OKITEM', '12345678');
    biEfdItem($user->id, $clienteId, str_pad('A', 44, '0', STR_PAD_LEFT), 'OKITEM', 50.0);

    $html = actingAs($user)->get('/app/bi/catalogo-itens')->assertOk()->getContent();

    expect($html)->not->toContain('NCM a revisar (documento');  // título do painel ausente
});
