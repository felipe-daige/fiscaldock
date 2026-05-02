<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\EfdNotaItem;
use App\Models\User;
use App\Models\XmlNota;
use App\Models\XmlNotaItem;
use App\Services\Catalogo\NotaItemUnificadoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function unifClientePropria(User $user, string $documento = '00000000000191'): Cliente
{
    return Cliente::firstOrCreate(
        ['user_id' => $user->id, 'documento' => $documento],
        ['tipo_pessoa' => 'PJ', 'razao_social' => 'Empresa Própria', 'is_empresa_propria' => true]
    );
}

function unifEfdImportacao(User $user, Cliente $cliente): EfdImportacao
{
    return EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'status' => 'concluido',
    ]);
}

function unifEfdNota(User $user, Cliente $cliente, EfdImportacao $imp, string $chave, array $overrides = []): EfdNota
{
    return EfdNota::create(array_merge([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $imp->id,
        'chave_acesso' => $chave,
        'modelo' => '55',
        'numero' => (int) substr($chave, -8),
        'serie' => '1',
        'data_emissao' => '2026-04-15',
        'tipo_operacao' => 'entrada',
        'valor_total' => 1000.00,
        'valor_desconto' => 0,
        'metadados' => [],
    ], $overrides));
}

function unifEfdItem(EfdNota $nota, array $overrides = []): EfdNotaItem
{
    return EfdNotaItem::create(array_merge([
        'efd_nota_id' => $nota->id,
        'user_id' => $nota->user_id,
        'numero_item' => 1,
        'codigo_item' => 'PROD-001',
        'descricao' => 'Produto comum',
        'quantidade' => 2,
        'unidade_medida' => 'UN',
        'valor_unitario' => 500.00,
        'valor_total' => 1000.00,
        'cfop' => 5102,
        'cst_icms' => '00',
        'aliquota_icms' => 18.00,
        'valor_icms' => 180.00,
    ], $overrides));
}

function unifXmlNota(User $user, string $chave, array $overrides = []): XmlNota
{
    return XmlNota::create(array_merge([
        'user_id' => $user->id,
        'nfe_id' => $chave,
        'tipo_documento' => 'NFE',
        'tipo_nota' => XmlNota::TIPO_ENTRADA,
        'origem' => 'xml_upload',
        'numero_nota' => (int) substr($chave, -8),
        'serie' => 1,
        'data_emissao' => '2026-04-15',
        'valor_total' => 1500.00,
        'emit_cnpj' => '13305697000150',
        'dest_cnpj' => '00000000000191',
        'payload' => [],
    ], $overrides));
}

function unifXmlItem(XmlNota $nota, array $overrides = []): XmlNotaItem
{
    return XmlNotaItem::create(array_merge([
        'xml_nota_id' => $nota->id,
        'user_id' => $nota->user_id,
        'numero_item' => 1,
        'codigo_item' => 'PROD-001',
        'descricao' => 'Produto comum',
        'cfop' => '5102',
        'quantidade' => 3,
        'valor_total' => 600.00,
        'cst_icms' => '00',
        'aliquota_icms' => 18.00,
    ], $overrides));
}

beforeEach(function () {
    $this->service = new NotaItemUnificadoService;
});

it('quando item está só em XML, conta como xml na agregação', function () {
    $user = User::factory()->create();
    $nota = unifXmlNota($user, '35240413305697000150550000000404041953940001');
    unifXmlItem($nota, ['quantidade' => 5, 'valor_total' => 250.00]);

    $agregado = $this->service->agregadoPorItem($user->id);

    expect($agregado)->toHaveCount(1);
    expect($agregado->first()['origens'])->toBe(['xml']);
    expect((float) $agregado->first()['quantidade_total'])->toBe(5.0);
    expect((float) $agregado->first()['valor_total'])->toBe(250.0);
});

it('quando item está só em EFD, conta como efd na agregação', function () {
    $user = User::factory()->create();
    $cliente = unifClientePropria($user);
    $imp = unifEfdImportacao($user, $cliente);
    $nota = unifEfdNota($user, $cliente, $imp, '35240413305697000150550000000404041953940002');
    unifEfdItem($nota, ['quantidade' => 7, 'valor_total' => 350.00]);

    $agregado = $this->service->agregadoPorItem($user->id);

    expect($agregado)->toHaveCount(1);
    expect($agregado->first()['origens'])->toBe(['efd']);
    expect((float) $agregado->first()['quantidade_total'])->toBe(7.0);
});

it('dedup: quando mesma chave existe em XML e EFD, conta só EFD', function () {
    $user = User::factory()->create();
    $cliente = unifClientePropria($user);
    $imp = unifEfdImportacao($user, $cliente);
    $chave = '35240413305697000150550000000404041953940003';

    // Mesma nota, duas fontes
    $efd = unifEfdNota($user, $cliente, $imp, $chave);
    unifEfdItem($efd, ['codigo_item' => 'PROD-DEDUP', 'quantidade' => 10, 'valor_total' => 1000.00]);

    $xml = unifXmlNota($user, $chave);
    unifXmlItem($xml, ['codigo_item' => 'PROD-DEDUP', 'quantidade' => 10, 'valor_total' => 1000.00]);

    $agregado = $this->service->agregadoPorItem($user->id);

    expect($agregado)->toHaveCount(1);
    expect($agregado->first()['origens'])->toBe(['efd']); // só EFD, não [efd,xml]
    expect((float) $agregado->first()['quantidade_total'])->toBe(10.0); // não 20
    expect((float) $agregado->first()['valor_total'])->toBe(1000.0);
    expect($agregado->first()['notas'])->toBe(1);
});

it('mistura XML-only com EFD: ambos contam', function () {
    $user = User::factory()->create();
    $cliente = unifClientePropria($user);
    $imp = unifEfdImportacao($user, $cliente);

    $efd = unifEfdNota($user, $cliente, $imp, '35240413305697000150550000000404041953940004');
    unifEfdItem($efd, ['codigo_item' => 'X', 'quantidade' => 2, 'valor_total' => 200.00]);

    // XML com chave diferente (não está na EFD)
    $xml = unifXmlNota($user, '35240413305697000150550000000404041953940005');
    unifXmlItem($xml, ['codigo_item' => 'X', 'quantidade' => 3, 'valor_total' => 300.00]);

    $agregado = $this->service->agregadoPorItem($user->id);

    expect($agregado)->toHaveCount(1);
    expect($agregado->first()['origens'])->toContain('efd', 'xml');
    expect((float) $agregado->first()['quantidade_total'])->toBe(5.0); // 2+3
    expect((float) $agregado->first()['valor_total'])->toBe(500.0);
    expect($agregado->first()['notas'])->toBe(2);
});

it('agrupa múltiplos códigos de item separadamente', function () {
    $user = User::factory()->create();
    $nota = unifXmlNota($user, '35240413305697000150550000000404041953940006');
    unifXmlItem($nota, ['numero_item' => 1, 'codigo_item' => 'A', 'valor_total' => 100.00]);
    unifXmlItem($nota, ['numero_item' => 2, 'codigo_item' => 'B', 'valor_total' => 200.00]);
    unifXmlItem($nota, ['numero_item' => 3, 'codigo_item' => 'A', 'valor_total' => 50.00]);

    $agregado = $this->service->agregadoPorItem($user->id);

    expect($agregado)->toHaveCount(2);
    $a = $agregado->firstWhere('codigo_item', 'A');
    $b = $agregado->firstWhere('codigo_item', 'B');
    expect((float) $a['valor_total'])->toBe(150.0);
    expect((float) $b['valor_total'])->toBe(200.0);
});

it('filtro de período aplica nos dois lados', function () {
    $user = User::factory()->create();
    $cliente = unifClientePropria($user);
    $imp = unifEfdImportacao($user, $cliente);

    // Dentro do período
    $efdIn = unifEfdNota($user, $cliente, $imp, '35240413305697000150550000000404041953940007', ['data_emissao' => '2026-04-15']);
    unifEfdItem($efdIn);

    // Fora do período
    $efdOut = unifEfdNota($user, $cliente, $imp, '35240413305697000150550000000404041953940008', ['data_emissao' => '2026-03-01']);
    unifEfdItem($efdOut, ['codigo_item' => 'PROD-MARCO']);

    $xmlIn = unifXmlNota($user, '35240413305697000150550000000404041953940009', ['data_emissao' => '2026-04-20']);
    unifXmlItem($xmlIn, ['codigo_item' => 'PROD-XML']);

    $xmlOut = unifXmlNota($user, '35240413305697000150550000000404041953940010', ['data_emissao' => '2026-05-15']);
    unifXmlItem($xmlOut, ['codigo_item' => 'PROD-MAIO']);

    $agregado = $this->service->agregadoPorItem($user->id, [
        'data_inicio' => '2026-04-01',
        'data_fim' => '2026-04-30',
    ]);

    $codigos = $agregado->pluck('codigo_item')->all();
    expect($codigos)->toContain('PROD-001', 'PROD-XML')
        ->not->toContain('PROD-MARCO')
        ->not->toContain('PROD-MAIO');
});

it('isola por user_id (não vaza item de outro usuário)', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $notaAlice = unifXmlNota($alice, '35240413305697000150550000000404041953940011');
    unifXmlItem($notaAlice, ['codigo_item' => 'ALICE-1']);

    $notaBob = unifXmlNota($bob, '35240413305697000150550000000404041953940012');
    unifXmlItem($notaBob, ['codigo_item' => 'BOB-1']);

    $agregadoAlice = $this->service->agregadoPorItem($alice->id);
    expect($agregadoAlice->pluck('codigo_item')->all())->toBe(['ALICE-1']);

    $agregadoBob = $this->service->agregadoPorItem($bob->id);
    expect($agregadoBob->pluck('codigo_item')->all())->toBe(['BOB-1']);
});

it('ncm vem do efd_catalogo_itens via cliente_id+cod_item', function () {
    $user = User::factory()->create();
    $cliente = unifClientePropria($user);
    $imp = unifEfdImportacao($user, $cliente);

    DB::table('efd_catalogo_itens')->insert([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $imp->id,
        'cod_item' => 'PROD-CAT',
        'descr_item' => 'Item com NCM cadastrado',
        'tipo_item' => '00',
        'cod_ncm' => '84713012',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $nota = unifEfdNota($user, $cliente, $imp, '35240413305697000150550000000404041953940013');
    unifEfdItem($nota, ['codigo_item' => 'PROD-CAT']);

    $agregado = $this->service->agregadoPorItem($user->id);

    expect($agregado->first()['ncms'])->toBe(['84713012']);
});

it('aliquota_icms_media é ponderada por valor_total', function () {
    $user = User::factory()->create();
    $nota = unifXmlNota($user, '35240413305697000150550000000404041953940014');
    // 100 a 12% + 100 a 18% = média simples 15%, mas ponderada também 15%
    unifXmlItem($nota, ['numero_item' => 1, 'codigo_item' => 'P', 'aliquota_icms' => 12.00, 'valor_total' => 100.00]);
    unifXmlItem($nota, ['numero_item' => 2, 'codigo_item' => 'P', 'aliquota_icms' => 18.00, 'valor_total' => 100.00]);
    // 200 a 4% — puxa pra baixo
    unifXmlItem($nota, ['numero_item' => 3, 'codigo_item' => 'P', 'aliquota_icms' => 4.00, 'valor_total' => 200.00]);

    $agregado = $this->service->agregadoPorItem($user->id);

    // (12*100 + 18*100 + 4*200) / 400 = (1200+1800+800)/400 = 3800/400 = 9.5
    expect((float) $agregado->first()['aliquota_icms_media'])->toBe(9.5);
});

it('itensUnificados retorna linhas cruas com origem identificável', function () {
    $user = User::factory()->create();
    $cliente = unifClientePropria($user);
    $imp = unifEfdImportacao($user, $cliente);

    $efd = unifEfdNota($user, $cliente, $imp, '35240413305697000150550000000404041953940015');
    unifEfdItem($efd);

    $xml = unifXmlNota($user, '35240413305697000150550000000404041953940016');
    unifXmlItem($xml);

    $linhas = $this->service->itensUnificados($user->id);

    expect($linhas)->toHaveCount(2);
    expect($linhas->pluck('origem')->all())->toContain('efd', 'xml');
});
