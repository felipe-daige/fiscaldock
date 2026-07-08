<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use App\Services\Catalogo\Export\CatalogoReportBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Exportações do Catálogo de Produtos (PDF/XLSX/CSV-ZIP). Os 3 formatos leem o mesmo
 * `CatalogoReportBuilder`, que lê `CatalogoDadosService` — a mesma fonte da tela.
 * Ver `docs/catalogo/exportacoes.md`.
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->forceFill([
        'trial_used' => true, 'trial_expires_at' => now()->addDays(30), 'trial_credits_remaining' => 50,
    ])->save();

    $this->cliente = DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'EMPRESA TESTE', 'documento' => '00000000000100',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->imp = EfdImportacao::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente,
        'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'i.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);

    $cat = fn (string $cod, string $tipo, ?string $ncm, ?float $aliq) => DB::table('efd_catalogo_itens')->insert([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente, 'importacao_id' => $this->imp->id,
        'cod_item' => $cod, 'descr_item' => "Item {$cod}", 'tipo_item' => $tipo, 'cod_ncm' => $ncm,
        'aliq_icms' => $aliq, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $cat('CAFE', '00', '09011110', 18.0);   // tem movimentação, alíquota bate
    $cat('ACUCAR', '00', '17019900', 18.0); // divergente (nota vem 12%)
    $cat('SEMNCM', '00', null, null);       // mercadoria sem NCM → ncm_faltando

    // Movimentação: nota não cancelada com itens casando o catálogo.
    $nota = EfdNota::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente, 'importacao_id' => $this->imp->id,
        'numero' => 1, 'serie' => '1', 'valor_desconto' => 0, 'cancelada' => false, 'origem_arquivo' => 'fiscal',
        'modelo' => '55', 'tipo_operacao' => 'saida', 'valor_total' => 1000, 'data_emissao' => '2024-01-10',
        'chave_acesso' => str_pad('A', 44, '0', STR_PAD_LEFT),
    ]);
    // Nota cancelada: NÃO deve movimentar (P4).
    $cancelada = EfdNota::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente, 'importacao_id' => $this->imp->id,
        'numero' => 2, 'serie' => '1', 'valor_desconto' => 0, 'cancelada' => true, 'origem_arquivo' => 'fiscal',
        'modelo' => '55', 'tipo_operacao' => 'saida', 'valor_total' => 9999, 'data_emissao' => '2024-01-11',
        'chave_acesso' => str_pad('B', 44, '0', STR_PAD_LEFT),
    ]);

    $item = fn (EfdNota $n, int $ni, string $cod, int $cfop, string $cst, float $aliq, float $valor) => DB::table('efd_notas_itens')->insert([
        'efd_nota_id' => $n->id, 'user_id' => $this->user->id, 'numero_item' => $ni, 'codigo_item' => $cod,
        'quantidade' => 1, 'valor_total' => $valor, 'cfop' => $cfop, 'cst_icms' => $cst,
        'aliquota_icms' => $aliq, 'valor_icms' => 0, 'valor_pis' => 0, 'valor_cofins' => 0,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $item($nota, 1, 'CAFE', 5102, '00', 18.0, 600);
    $item($nota, 2, 'ACUCAR', 5405, '60', 12.0, 400); // 12 ≠ 18 catálogo → divergente
    $item($cancelada, 1, 'CAFE', 5102, '00', 18.0, 9999); // cancelada, ignorada
});

function catExportUrl(string $rota): string
{
    return "/app/catalogo/{$rota}";
}

function catCorpo($resp): string
{
    $base = $resp->baseResponse;
    if ($base instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
        return (string) file_get_contents($base->getFile()->getPathname());
    }
    if ($base instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
        return $resp->streamedContent();
    }

    return (string) $resp->getContent();
}

it('baixa o PDF do catálogo', function () {
    $resp = actingAs($this->user)->get(catExportUrl('exportar-pdf'));
    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('application/pdf');
    expect(substr(catCorpo($resp), 0, 4))->toBe('%PDF');
});

it('baixa o XLSX com uma aba por seção', function () {
    $resp = actingAs($this->user)->get(catExportUrl('exportar-xlsx'));
    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('spreadsheetml');
    expect(substr(catCorpo($resp), 0, 2))->toBe('PK');
});

it('baixa o ZIP com um CSV por seção', function () {
    $resp = actingAs($this->user)->get(catExportUrl('exportar-csv-zip'));
    $resp->assertOk();

    $tmp = tempnam(sys_get_temp_dir(), 'catzt');
    file_put_contents($tmp, catCorpo($resp));

    $zip = new ZipArchive;
    expect($zip->open($tmp))->toBeTrue();
    $nomes = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $nomes[] = $zip->getNameIndex($i);
    }
    $zip->close();
    unlink($tmp);

    expect($nomes)->toContain('01-resumo.csv')
        ->and($nomes)->toContain('02-itens.csv')
        ->and($nomes)->toContain('03-cfops.csv')
        ->and($nomes)->toContain('04-csts.csv')
        ->and($nomes)->toContain('05-drift.csv');
});

it('anexa o cookie bi_download quando recebe download_token', function () {
    $resp = actingAs($this->user)->get(catExportUrl('exportar-pdf').'?download_token=abc123');
    $resp->assertOk();
    $nomes = array_map(fn ($c) => $c->getName(), $resp->headers->getCookies());
    expect($nomes)->toContain('bi_download');
});

it('relatório bate com os KPIs da tela e marca item divergente', function () {
    $relatorio = app(CatalogoReportBuilder::class)->montar($this->user->id, []);

    expect($relatorio['kpis']['total_produtos'])->toBe(3);
    expect($relatorio['kpis']['com_movimentacao'])->toBe(2);      // CAFE + ACUCAR
    expect($relatorio['kpis']['valor_movimentado'])->toBe(1000.0); // cancelada fora (P4)
    expect($relatorio['kpis']['ncm_faltando'])->toBe(1);          // SEMNCM
    expect($relatorio['kpis']['aliq_divergente'])->toBe(1);       // ACUCAR

    // Seção itens: ACUCAR marcado Divergente, CAFE OK, SEMNCM Sem movimentação.
    $itens = collect($relatorio['secoes']['itens']['linhas'])->keyBy(0);
    expect($itens['ACUCAR'][8])->toBe('Divergente');
    expect($itens['CAFE'][8])->toBe('OK');
    expect($itens['SEMNCM'][8])->toBe('Sem movimentação');
});

it('respeita o filtro de tipo_item', function () {
    // Só há tipo 00 na massa → filtrar por 07 zera a tabela de itens.
    $relatorio = app(CatalogoReportBuilder::class)->montar($this->user->id, ['tipo_item' => '07']);
    expect($relatorio['secoes']['itens']['linhas'])->toBe([]);
});

it('escopa movimentação por cliente (cod_item colidindo entre clientes)', function () {
    // Segundo cliente do MESMO usuário, com um item de cod_item que colide com o cliente 1.
    $cliente2 = DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'OUTRA EMPRESA', 'documento' => '00000000000200',
        'is_empresa_propria' => false, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $imp2 = EfdImportacao::create([
        'user_id' => $this->user->id, 'cliente_id' => $cliente2,
        'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'i2.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);
    DB::table('efd_catalogo_itens')->insert([
        'user_id' => $this->user->id, 'cliente_id' => $cliente2, 'importacao_id' => $imp2->id,
        'cod_item' => 'CAFE', 'descr_item' => 'Café do cliente 2', 'tipo_item' => '00',
        'cod_ncm' => '09011110', 'aliq_icms' => 18.0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    // Movimentação alta no cliente 2 pro MESMO cod_item CAFE — não pode contar no recorte do cliente 1.
    $notaC2 = EfdNota::create([
        'user_id' => $this->user->id, 'cliente_id' => $cliente2, 'importacao_id' => $imp2->id,
        'numero' => 10, 'serie' => '1', 'valor_desconto' => 0, 'cancelada' => false, 'origem_arquivo' => 'fiscal',
        'modelo' => '55', 'tipo_operacao' => 'saida', 'valor_total' => 5000, 'data_emissao' => '2024-02-10',
        'chave_acesso' => str_pad('C', 44, '0', STR_PAD_LEFT),
    ]);
    DB::table('efd_notas_itens')->insert([
        'efd_nota_id' => $notaC2->id, 'user_id' => $this->user->id, 'numero_item' => 1, 'codigo_item' => 'CAFE',
        'quantidade' => 1, 'valor_total' => 5000, 'cfop' => 5102, 'cst_icms' => '00',
        'aliquota_icms' => 18.0, 'valor_icms' => 0, 'valor_pis' => 0, 'valor_cofins' => 0,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    // Filtrando cliente 1: CAFE movimenta só os 600 do cliente 1 (não os 5000 do cliente 2).
    $rel1 = app(CatalogoReportBuilder::class)->montar($this->user->id, ['cliente_id' => $this->cliente]);
    $itens1 = collect($rel1['secoes']['itens']['linhas'])->keyBy(0);
    expect($itens1['CAFE'][7])->toBe(600.0)          // valor movimentado escopado
        ->and($itens1['CAFE'][6])->toBe(1);          // 1 movimentação, não 2
    expect($rel1['kpis']['valor_movimentado'])->toBe(1000.0); // 600 CAFE + 400 ACUCAR, sem 5000

    // Sem filtro de cliente: soma os dois (mesmo cod_item, ambos clientes do usuário).
    $relTodos = app(CatalogoReportBuilder::class)->montar($this->user->id, []);
    expect($relTodos['kpis']['valor_movimentado'])->toBe(6000.0); // 1000 + 5000
});

it('não vaza catálogo de outro usuário', function () {
    $outro = User::factory()->create();
    $outro->forceFill(['trial_used' => true, 'trial_expires_at' => now()->addDays(30), 'trial_credits_remaining' => 50])->save();

    $relatorio = app(CatalogoReportBuilder::class)->montar($outro->id, []);
    expect($relatorio['kpis']['total_produtos'])->toBe(0);
    expect($relatorio['secoes']['itens']['linhas'])->toBe([]);
});

it('renderiza a view do PDF com as seções', function () {
    $relatorio = app(CatalogoReportBuilder::class)->montar($this->user->id, []);
    $html = view('reports.catalogo', ['relatorio' => $relatorio])->render();

    expect($html)->toContain('Catálogo de itens')
        ->and($html)->toContain('Top 10 CFOPs')
        ->and($html)->toContain('CAFE')
        ->and($html)->toContain('Divergente');
});
