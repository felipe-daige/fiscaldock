<?php

use App\Support\Cfop;

it('resolve descricao de cfop comum pelo mapa', function () {
    expect(Cfop::descricao('5102'))->toContain('Venda de mercadoria adquirida');
});

it('cai no fallback de familia para cfop nao mapeado', function () {
    // 5999 não está no mapa top-usados → família "Saída estadual"
    expect(Cfop::descricao('5999'))->toContain('Saída');
});

it('classifica entrada x saida pelo primeiro digito', function () {
    expect(Cfop::tipoOperacao('1102'))->toBe('entrada');
    expect(Cfop::tipoOperacao('6108'))->toBe('saida');
});

it('soma valor e icms do C190 por cfop (nao dos itens fiscais)', function () {
    $user = \App\Models\User::factory()->create();
    $userId = $user->id;
    $clienteId = \DB::table('clientes')->insertGetId([
        'user_id' => $userId, 'documento' => substr(str_replace('.', '', microtime(true)) . $userId, 0, 14), 'razao_social' => 'Empresa Teste',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $importacaoId = \DB::table('efd_importacoes')->insertGetId([
        'user_id' => $userId, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $nota = \DB::table('efd_notas')->insertGetId(['user_id' => $userId, 'cliente_id' => $clienteId, 'importacao_id' => $importacaoId, 'origem_arquivo' => 'fiscal', 'modelo' => '55', 'tipo_operacao' => 'saida', 'cancelada' => false, 'valor_total' => 1000, 'data_emissao' => '2024-03-10', 'numero' => 1, 'created_at' => now(), 'updated_at' => now()]);
    \DB::table('efd_notas_consolidados')->insert(['efd_nota_id' => $nota, 'user_id' => $userId, 'cst_icms' => '000', 'cfop' => '5102', 'valor_operacao' => 1000, 'valor_icms' => 120, 'created_at' => now(), 'updated_at' => now()]);

    $rows = collect(app(\App\Services\EfdAgregadorService::class)->cfopRanking($userId, null, null, null));
    $r = $rows->firstWhere('cfop', '5102');

    expect((float) $r['valor'])->toBe(1000.0);
    expect((float) $r['icms'])->toBe(120.0);
    expect($r['tipo'])->toBe('saida');
});
