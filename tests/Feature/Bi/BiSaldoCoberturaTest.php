<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use App\Services\BiExportService;
use App\Services\BiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('coberturaConsultaParticipantes conta sem_consulta (situacao null) e sem_uf', function () {
    $user = User::factory()->create();
    $cli = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'documento' => '00000000000191', 'razao_social' => 'E',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $mk = fn (array $a) => DB::table('participantes')->insert(array_merge([
        'user_id' => $user->id, 'cliente_id' => $cli, 'razao_social' => 'P', 'documento' => (string) random_int(1, 9e9),
        'origem_tipo' => 'MANUAL', 'created_at' => now(), 'updated_at' => now(),
    ], $a));
    $mk(['situacao_cadastral' => '02', 'uf' => 'SP']);     // consultado, com uf
    $mk(['situacao_cadastral' => null, 'uf' => null]);     // sem consulta, sem uf
    $mk(['situacao_cadastral' => null, 'uf' => null]);     // sem consulta, sem uf

    $cob = app(BiService::class)->coberturaConsultaParticipantes($user->id);
    expect($cob['total'])->toBe(3)
        ->and($cob['sem_consulta'])->toBe(2)
        ->and($cob['consultados'])->toBe(1)
        ->and($cob['sem_uf'])->toBe(2);
});

it('saldo_liquido do relatório reconcilia com vendas − aquisições exibidos', function () {
    $user = User::factory()->create();
    $cli = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'documento' => '00000000000191', 'razao_social' => 'E',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cli, 'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'x.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);
    $mkNota = fn (array $a) => EfdNota::create(array_merge([
        'user_id' => $user->id, 'cliente_id' => $cli, 'importacao_id' => $imp->id,
        'numero' => random_int(1, 99999), 'serie' => '1', 'modelo' => '55', 'valor_desconto' => 0,
        'cancelada' => false, 'origem_arquivo' => 'fiscal', 'data_emissao' => '2026-03-15',
    ], $a));
    $c190 = fn (EfdNota $n, int $cfop, float $val) => DB::table('efd_notas_consolidados')->insert([
        'efd_nota_id' => $n->id, 'user_id' => $user->id, 'cfop' => $cfop, 'cst_icms' => '00', 'aliquota_icms' => 18,
        'valor_operacao' => $val, 'valor_bc_icms' => 0, 'valor_icms' => 0, 'valor_bc_icms_st' => 0,
        'valor_icms_st' => 0, 'valor_reducao_bc' => 0, 'valor_ipi' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    // dados REAIS não-zero (CFOPs não-devolução): saída 5102 R$1000 + entrada 1102 R$400.
    $c190($mkNota(['tipo_operacao' => 'saida', 'valor_total' => 1000, 'chave_acesso' => str_pad('A', 44, '0')]), 5102, 1000.00);
    $c190($mkNota(['tipo_operacao' => 'entrada', 'valor_total' => 400, 'chave_acesso' => str_pad('B', 44, '0')]), 1102, 400.00);

    $rel = app(BiExportService::class)->relatorioCompleto($user->id, null, null, null);

    $parse = fn (string $brl) => (float) str_replace(',', '.', str_replace('.', '', $brl));
    $fat = $parse($rel['kpis']['faturamento']);
    $aq = $parse($rel['kpis']['aquisicoes']);
    // prova a fórmula sobre número real (não 0==0): faturamento > 0 e saldo = fat − aquis.
    expect($fat)->toBeGreaterThan(0)
        ->and($parse($rel['kpis']['saldo_liquido']))->toBe(round($fat - $aq, 2))
        ->and($rel)->toHaveKey('cobertura_consulta')
        ->and($rel)->toHaveKey('a_recolher_brl');
});
