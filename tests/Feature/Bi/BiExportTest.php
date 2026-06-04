<?php

use App\Services\BiExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('gera csv com BOM, separador ; e header da aba', function () {
    $svc = app(BiExportService::class);
    $csv = $svc->toCsv(['Mês', 'Valor'], [['01/2024', '1.000,00']]);

    expect(substr($csv, 0, 3))->toBe("\xEF\xBB\xBF");
    expect($csv)->toContain('Mês;Valor');
    expect($csv)->toContain('01/2024;1.000,00');
});

it('monta dataset da aba cfop', function () {
    $user = \App\Models\User::factory()->create();
    $userId = $user->id;
    $clienteId = \DB::table('clientes')->insertGetId([
        'user_id' => $userId, 'documento' => '00000000000191', 'razao_social' => 'Empresa Teste',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $impId = \DB::table('efd_importacoes')->insertGetId([
        'user_id' => $userId, 'cliente_id' => $clienteId, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $nota = \DB::table('efd_notas')->insertGetId(['user_id' => $userId, 'cliente_id' => $clienteId, 'importacao_id' => $impId, 'origem_arquivo' => 'fiscal', 'modelo' => '55', 'tipo_operacao' => 'saida', 'cancelada' => false, 'valor_total' => 1000, 'data_emissao' => '2024-03-10', 'numero' => 1, 'created_at' => now(), 'updated_at' => now()]);
    \DB::table('efd_notas_consolidados')->insert(['efd_nota_id' => $nota, 'user_id' => $userId, 'cst_icms' => '000', 'cfop' => '5102', 'valor_operacao' => 1000, 'valor_icms' => 120, 'created_at' => now(), 'updated_at' => now()]);

    $ds = app(BiExportService::class)->dataset('cfop', $userId, null, null, null);
    expect($ds['colunas'][0])->toBe('CFOP / Natureza');
    expect($ds['linhas'][0][0])->toContain('5102');
});
