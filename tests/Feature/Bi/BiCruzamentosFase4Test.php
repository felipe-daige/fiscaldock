<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Services\Bi\CruzamentosEfdInternosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Fase 4 do BI cruzamentos: ICMS-ST × regime do fornecedor e estoque H010 × movimentação.
 * Massa sintética — a base real não tem movimento ST nem bloco H extraído; o código é
 * data-ready e degrada pra empty-state (coberto no teste de página).
 */
function f4Base(User $user): array
{
    $cliente = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Comercio ME',
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI',
        'status' => 'concluido', 'periodo_inicio' => '2026-01-01', 'periodo_fim' => '2026-01-31',
    ]);

    return [$cliente, $imp];
}

function f4NotaFiscal(User $user, Cliente $cliente, EfdImportacao $imp, array $ov = []): EfdNota
{
    return EfdNota::create(array_merge([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id,
        'chave_acesso' => '3526'.str_pad((string) random_int(1, 999999), 40, '0', STR_PAD_LEFT),
        'modelo' => '55', 'numero' => random_int(1, 99999), 'serie' => '1', 'data_emissao' => '2026-01-15',
        'tipo_operacao' => 'entrada', 'valor_total' => 1000, 'valor_desconto' => 0,
        'origem_arquivo' => 'fiscal', 'cancelada' => false, 'metadados' => [],
    ], $ov));
}

function f4Consolidado(User $user, EfdNota $nota, array $ov = []): void
{
    DB::table('efd_notas_consolidados')->insert(array_merge([
        'efd_nota_id' => $nota->id, 'user_id' => $user->id,
        'cst_icms' => '010', 'cfop' => 1403, 'aliquota_icms' => 18,
        'valor_operacao' => 1000, 'valor_bc_icms' => 0, 'valor_icms' => 0,
        'valor_bc_icms_st' => 500, 'valor_icms_st' => 90,
        'created_at' => now(), 'updated_at' => now(),
    ], $ov));
}

function f4EstoqueItem(User $user, Cliente $cliente, EfdImportacao $imp, array $ov = []): void
{
    DB::table('efd_estoque')->insert(array_merge([
        'importacao_id' => $imp->id, 'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'dt_inventario' => '2025-12-31', 'motivo_inventario' => '01',
        'cod_item' => 'PROD1', 'unid' => 'UN', 'qtd' => 10, 'vl_unit' => 5, 'vl_item' => 50,
        'ind_prop' => '0', 'created_at' => now(), 'updated_at' => now(),
    ], $ov));
}

function f4ItemDeNota(User $user, EfdNota $nota, array $ov = []): void
{
    DB::table('efd_notas_itens')->insert(array_merge([
        'efd_nota_id' => $nota->id, 'user_id' => $user->id, 'numero_item' => 1, 'codigo_item' => 'PROD1',
        'quantidade' => 1, 'valor_total' => 100, 'cfop' => 5102,
        'valor_icms' => 0, 'valor_pis' => 0, 'valor_cofins' => 0,
        'created_at' => now(), 'updated_at' => now(),
    ], $ov));
}

// ── ICMS-ST × regime ─────────────────────────────────────────────────────────

it('agrupa ST das entradas por fornecedor com regime tributário', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp] = f4Base($user);

    $fornecedor = Participante::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'documento' => '11111111000111', 'razao_social' => 'Atacado ST SA',
        'regime_tributario' => 'Simples Nacional',
    ]);

    $n1 = f4NotaFiscal($user, $cliente, $imp, ['participante_id' => $fornecedor->id]);
    $n2 = f4NotaFiscal($user, $cliente, $imp, ['participante_id' => $fornecedor->id]);
    f4Consolidado($user, $n1);
    f4Consolidado($user, $n2, ['valor_bc_icms_st' => 300, 'valor_icms_st' => 54]);

    $r = app(CruzamentosEfdInternosService::class)->icmsStRegime($user->id);

    expect($r['fornecedores'])->toHaveCount(1)
        ->and($r['fornecedores'][0]['razao_social'])->toBe('Atacado ST SA')
        ->and($r['fornecedores'][0]['regime'])->toBe('Simples Nacional')
        ->and($r['fornecedores'][0]['qtd_notas'])->toBe(2)
        ->and($r['fornecedores'][0]['bc_st'])->toBe(800.00)
        ->and($r['fornecedores'][0]['valor_st'])->toBe(144.00);
});

it('ST ignora saída, cancelada e consolidado sem ST; soma E210 no contexto', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp] = f4Base($user);

    $entrada = f4NotaFiscal($user, $cliente, $imp);
    f4Consolidado($user, $entrada);

    $saida = f4NotaFiscal($user, $cliente, $imp, ['tipo_operacao' => 'saida']);
    f4Consolidado($user, $saida); // saída: fora

    $cancelada = f4NotaFiscal($user, $cliente, $imp, ['cancelada' => true]);
    f4Consolidado($user, $cancelada); // cancelada: fora

    $semSt = f4NotaFiscal($user, $cliente, $imp);
    f4Consolidado($user, $semSt, ['valor_bc_icms_st' => 0, 'valor_icms_st' => 0]); // sem ST: fora

    DB::table('efd_apuracoes_icms')->insert([
        'importacao_id' => $imp->id, 'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'periodo_inicio' => '2026-01-01', 'periodo_fim' => '2026-01-31',
        'st_icms_recolher' => 250.50,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $r = app(CruzamentosEfdInternosService::class)->icmsStRegime($user->id);

    expect($r['fornecedores'])->toHaveCount(1)
        ->and($r['fornecedores'][0]['valor_st'])->toBe(90.00)
        ->and($r['fornecedores'][0]['razao_social'])->toBe('Sem identificação')
        ->and($r['fornecedores'][0]['regime'])->toBeNull()
        ->and($r['e210_st_recolher'])->toBe(250.50);
});

it('ST respeita filtro de período/cliente e não vaza entre usuários', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp] = f4Base($user);
    f4Consolidado($user, f4NotaFiscal($user, $cliente, $imp, ['data_emissao' => '2026-01-15']));
    f4Consolidado($user, f4NotaFiscal($user, $cliente, $imp, ['data_emissao' => '2026-03-10']));

    $svc = app(CruzamentosEfdInternosService::class);

    expect($svc->icmsStRegime($user->id)['fornecedores'][0]['qtd_notas'])->toBe(2)
        ->and($svc->icmsStRegime($user->id, ['data_inicio' => '2026-03-01', 'data_fim' => '2026-03-31'])['fornecedores'][0]['qtd_notas'])->toBe(1)
        ->and($svc->icmsStRegime($user->id, ['cliente_id' => $cliente->id + 999])['fornecedores'])->toHaveCount(0);

    $outro = User::factory()->trialAtivo()->create(['credits' => 100]);
    expect($svc->icmsStRegime($outro->id)['fornecedores'])->toHaveCount(0);
});

// ── Estoque H010 × movimentação ──────────────────────────────────────────────

it('cruza itens do inventário com movimentação 12m e flagga item sem giro', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp] = f4Base($user);

    f4EstoqueItem($user, $cliente, $imp); // PROD1, 50
    f4EstoqueItem($user, $cliente, $imp, ['cod_item' => 'PROD2', 'vl_item' => 200]); // sem giro
    f4EstoqueItem($user, $cliente, $imp, ['cod_item' => 'TERC1', 'ind_prop' => '2', 'vl_item' => 999]); // de terceiros: fora

    // Movimentação de PROD1 dentro da janela (inventário 2025-12-31)
    $entrada = f4NotaFiscal($user, $cliente, $imp, ['data_emissao' => '2025-06-10']);
    f4ItemDeNota($user, $entrada, ['valor_total' => 300]);
    $saida = f4NotaFiscal($user, $cliente, $imp, ['tipo_operacao' => 'saida', 'data_emissao' => '2025-11-20']);
    f4ItemDeNota($user, $saida, ['valor_total' => 120]);
    // Fora da janela (mais de 12m antes) e depois do inventário: não contam
    $antiga = f4NotaFiscal($user, $cliente, $imp, ['data_emissao' => '2024-06-01']);
    f4ItemDeNota($user, $antiga, ['valor_total' => 999]);
    $posterior = f4NotaFiscal($user, $cliente, $imp, ['data_emissao' => '2026-02-01']);
    f4ItemDeNota($user, $posterior, ['valor_total' => 888]);
    // Origem contribuições: fora (movimentação lê só fiscal/C170)
    $contrib = f4NotaFiscal($user, $cliente, $imp, ['origem_arquivo' => 'contribuicoes', 'data_emissao' => '2025-10-01']);
    f4ItemDeNota($user, $contrib, ['valor_total' => 777]);

    $r = app(CruzamentosEfdInternosService::class)->estoqueVsMovimentacao($user->id);

    expect($r['itens'])->toHaveCount(2)
        ->and($r['itens_total'])->toBe(2);

    $porItem = collect($r['itens'])->keyBy('cod_item');
    expect($porItem['PROD1']['mov_entradas'])->toBe(300.00)
        ->and($porItem['PROD1']['mov_saidas'])->toBe(120.00)
        ->and($porItem['PROD1']['sem_movimentacao'])->toBeFalse()
        ->and($porItem['PROD2']['sem_movimentacao'])->toBeTrue()
        ->and($r['parados_qtd'])->toBe(1)
        ->and($r['parados_valor'])->toBe(200.00);
});

it('estoque usa o inventário mais recente por cliente e junta descrição do catálogo', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp] = f4Base($user);

    f4EstoqueItem($user, $cliente, $imp, ['dt_inventario' => '2024-12-31', 'vl_item' => 111]); // antigo: fora
    f4EstoqueItem($user, $cliente, $imp, ['dt_inventario' => '2025-12-31', 'vl_item' => 50]);

    DB::table('efd_catalogo_itens')->insert([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id,
        'cod_item' => 'PROD1', 'descr_item' => 'Produto Um', 'tipo_item' => '00',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $r = app(CruzamentosEfdInternosService::class)->estoqueVsMovimentacao($user->id);

    expect($r['itens'])->toHaveCount(1)
        ->and($r['itens'][0]['vl_item'])->toBe(50.00)
        ->and($r['itens'][0]['dt_inventario'])->toBe('2025-12-31')
        ->and($r['itens'][0]['descricao'])->toBe('Produto Um');
});

it('estoque respeita filtros e não vaza entre usuários', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp] = f4Base($user);
    f4EstoqueItem($user, $cliente, $imp);

    $svc = app(CruzamentosEfdInternosService::class);

    expect($svc->estoqueVsMovimentacao($user->id)['itens'])->toHaveCount(1)
        ->and($svc->estoqueVsMovimentacao($user->id, ['data_inicio' => '2026-01-01'])['itens'])->toHaveCount(0)
        ->and($svc->estoqueVsMovimentacao($user->id, ['cliente_id' => $cliente->id + 999])['itens'])->toHaveCount(0);

    $outro = User::factory()->trialAtivo()->create(['credits' => 100]);
    expect($svc->estoqueVsMovimentacao($outro->id)['itens'])->toHaveCount(0);
});

// ── Página ───────────────────────────────────────────────────────────────────

it('página renderiza as seções fase 4 com dado e com empty-state', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);

    // Sem dado: empty-states das duas seções
    actingAs($user)->get('/app/bi/cruzamentos')
        ->assertOk()
        ->assertSee('ICMS-ST nas compras × regime do fornecedor')
        ->assertSee('Nenhuma compra com ICMS-ST destacado')
        ->assertSee('Estoque declarado (H010) × movimentação do item')
        ->assertSee('Nenhum inventário (bloco H) importado');

    [$cliente, $imp] = f4Base($user);
    $fornecedor = Participante::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'documento' => '11111111000111', 'razao_social' => 'Atacado ST SA',
        'regime_tributario' => 'Lucro Real',
    ]);
    f4Consolidado($user, f4NotaFiscal($user, $cliente, $imp, ['participante_id' => $fornecedor->id]));
    f4EstoqueItem($user, $cliente, $imp, ['cod_item' => 'PROD9', 'vl_item' => 350]);

    actingAs($user)->get('/app/bi/cruzamentos')
        ->assertOk()
        ->assertSee('Atacado ST SA')
        ->assertSee('Lucro Real')
        ->assertSee('PROD9')
        ->assertSee('Sem giro 12m');
});
