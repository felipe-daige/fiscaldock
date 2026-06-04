<?php

use App\Models\User;
use App\Services\BiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('separa frete (CT-e modelo 57) das compras e expoe a recolher liquido', function () {
    $user = User::factory()->create();
    $userId = $user->id;
    $clienteId = \DB::table('clientes')->insertGetId([
        'user_id' => $userId, 'documento' => '00000000000191', 'razao_social' => 'Empresa Teste',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $imp = \DB::table('efd_importacoes')->insertGetId([
        'user_id' => $userId, 'cliente_id' => $clienteId, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    // entrada NF-e (mercadoria) 1000 + entrada CT-e (frete) 50
    \DB::table('efd_notas')->insert([
        ['user_id' => $userId, 'cliente_id' => $clienteId, 'importacao_id' => $imp, 'origem_arquivo' => 'fiscal', 'modelo' => '55', 'tipo_operacao' => 'entrada', 'cancelada' => false, 'valor_total' => 1000, 'data_emissao' => '2024-01-10', 'numero' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => $userId, 'cliente_id' => $clienteId, 'importacao_id' => $imp, 'origem_arquivo' => 'fiscal', 'modelo' => '57', 'tipo_operacao' => 'entrada', 'cancelada' => false, 'valor_total' => 50, 'data_emissao' => '2024-01-10', 'numero' => 2, 'created_at' => now(), 'updated_at' => now()],
    ]);

    // apurações: ICMS a recolher 100, PIS 10, COFINS 46 -> a recolher total = 156
    \DB::table('efd_apuracoes_icms')->insert(['importacao_id' => $imp, 'user_id' => $userId, 'cliente_id' => $clienteId, 'periodo_inicio' => '2024-01-01', 'periodo_fim' => '2024-01-31', 'icms_a_recolher' => 100, 'st_icms_recolher' => 0, 'created_at' => now(), 'updated_at' => now()]);
    $impC = \DB::table('efd_importacoes')->insertGetId(['user_id' => $userId, 'cliente_id' => $clienteId, 'tipo_efd' => 'EFD PIS/COFINS', 'status' => 'concluido', 'created_at' => now(), 'updated_at' => now()]);
    \DB::table('efd_apuracoes_contribuicoes')->insert(['importacao_id' => $impC, 'user_id' => $userId, 'cliente_id' => $clienteId, 'pis_total_recolher' => 10, 'cofins_total_recolher' => 46, 'created_at' => now(), 'updated_at' => now()]);

    $g = app(BiService::class)->getResumoGeral($userId);

    expect($g['total_frete'])->toBe(50.0);
    expect($g['total_compras_mercadoria'])->toBe(1000.0);
    expect($g['total_compras'])->toBe(1050.0);
    expect($g['total_a_recolher'])->toBe(156.0);
});
