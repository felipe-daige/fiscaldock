<?php

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
    DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'documento' => '00000000000191', 'razao_social' => 'E',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $rel = app(BiExportService::class)->relatorioCompleto($user->id, null, null, null);

    $parse = fn (string $brl) => (float) str_replace(',', '.', str_replace('.', '', $brl));
    expect($parse($rel['kpis']['saldo_liquido']))
        ->toBe(round($parse($rel['kpis']['faturamento']) - $parse($rel['kpis']['aquisicoes']), 2))
        ->and($rel)->toHaveKey('cobertura_consulta')
        ->and($rel)->toHaveKey('a_recolher_brl');
});
