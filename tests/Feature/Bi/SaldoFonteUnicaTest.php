<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use App\Services\BiExportService;
use App\Services\BiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function semearSaldoFonteUnica(int $userId, int $cli): void
{
    $imp = EfdImportacao::create([
        'user_id' => $userId, 'cliente_id' => $cli, 'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'x.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);
    $mk = fn (array $a) => EfdNota::create(array_merge([
        'user_id' => $userId, 'cliente_id' => $cli, 'importacao_id' => $imp->id,
        'numero' => random_int(1, 99999), 'serie' => '1', 'modelo' => '55', 'valor_desconto' => 0,
        'cancelada' => false, 'origem_arquivo' => 'fiscal', 'data_emissao' => '2026-03-10',
    ], $a));
    $c190 = fn (EfdNota $n, int $cfop, float $v) => DB::table('efd_notas_consolidados')->insert([
        'efd_nota_id' => $n->id, 'user_id' => $userId, 'cfop' => $cfop, 'cst_icms' => '00', 'aliquota_icms' => 18,
        'valor_operacao' => $v, 'valor_bc_icms' => 0, 'valor_icms' => 0, 'valor_bc_icms_st' => 0,
        'valor_icms_st' => 0, 'valor_reducao_bc' => 0, 'valor_ipi' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $c190($mk(['tipo_operacao' => 'saida', 'valor_total' => 1000, 'chave_acesso' => str_pad('A', 44, '0')]), 5102, 1000.00);
    $c190($mk(['tipo_operacao' => 'entrada', 'valor_total' => 400, 'chave_acesso' => str_pad('B', 44, '0')]), 1102, 400.00);
}

it('getResumoGeral expõe saldo_liquido = vendas − compras', function () {
    $user = User::factory()->create();
    $cli = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'documento' => '00000000000191', 'razao_social' => 'E',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    semearSaldoFonteUnica($user->id, $cli);

    $r = app(BiService::class)->getResumoGeral($user->id);
    expect($r['saldo_liquido'])->toBe(round($r['total_vendas'] - $r['total_compras'], 2))
        ->and($r['saldo_liquido'])->toBeGreaterThan(0);
});

it('PDF (relatorioCompleto) consome o saldo da fonte única', function () {
    $user = User::factory()->create();
    $cli = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'documento' => '00000000000191', 'razao_social' => 'E',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    semearSaldoFonteUnica($user->id, $cli);

    $resumo = app(BiService::class)->getResumoGeral($user->id);
    $rel = app(BiExportService::class)->relatorioCompleto($user->id, null, null, null);
    $parse = fn (string $brl) => (float) str_replace(',', '.', str_replace('.', '', $brl));
    expect($parse($rel['kpis']['saldo_liquido']))->toBe(round($resumo['saldo_liquido'], 2));
});
