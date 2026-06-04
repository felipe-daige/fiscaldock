<?php

use App\Models\User;
use App\Services\BiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function semearNota(int $userId, int $clienteId, int $imp, string $origem, string $data, float $valor, int $numero): void
{
    \DB::table('efd_notas')->insert([
        'user_id' => $userId, 'cliente_id' => $clienteId, 'importacao_id' => $imp,
        'origem_arquivo' => $origem, 'modelo' => '55', 'tipo_operacao' => 'saida', 'cancelada' => false,
        'valor_total' => $valor, 'data_emissao' => $data, 'numero' => $numero,
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

function semearContexto(): array
{
    $user = User::factory()->create();
    $clienteId = \DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'documento' => '00000000000191', 'razao_social' => 'Empresa Teste',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $imp = \DB::table('efd_importacoes')->insertGetId([
        'user_id' => $user->id, 'cliente_id' => $clienteId, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    return [$user->id, $clienteId, $imp];
}

it('getResumoGeral respeita o filtro de periodo (F1)', function () {
    [$userId, $cli, $imp] = semearContexto();
    semearNota($userId, $cli, $imp, 'fiscal', '2024-01-10', 1000, 1);
    semearNota($userId, $cli, $imp, 'fiscal', '2024-02-10', 500, 2);

    $svc = app(BiService::class);
    expect($svc->getResumoGeral($userId, null, '2024-01-01', '2024-01-31')['total_vendas'])->toBe(1000.0);
    expect($svc->getResumoGeral($userId)['total_vendas'])->toBe(1500.0);
});

it('getGapImportacoes detecta mes sem origem, por competencia e ancorado ao range (F2)', function () {
    [$userId, $cli, $imp] = semearContexto();
    semearNota($userId, $cli, $imp, 'fiscal', '2024-01-10', 100, 1); // jan
    semearNota($userId, $cli, $imp, 'fiscal', '2024-03-10', 100, 2); // mar (fev vazio)

    $gap = collect(app(BiService::class)->getGapImportacoes($userId))->keyBy('mes');

    expect($gap)->toHaveCount(3);              // jan, fev, mar (range MIN..MAX)
    expect($gap['2024-01']['gap'])->toBeFalse();
    expect($gap['2024-02']['gap'])->toBeTrue(); // mês sem nenhuma importação
    expect($gap['2024-03']['gap'])->toBeFalse();
    expect((bool) $gap['2024-01']['tem_fiscal'])->toBeTrue();
});
