<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * Harness `efd:motor-shadow` (F4): valida o motor Laravel contra o SPED real e reporta o
 * veredito do oráculo. Prova a garantia central: dry-run NÃO persiste (reverte a
 * transação); só `--commit` grava. Pula se a fixture (gitignored) estiver ausente.
 */
function fixtureUtidaShadow(): string
{
    $path = __DIR__.'/../../Fixtures/sped/UTIDA-jan2026-somente-dados.txt';
    if (! is_file($path)) {
        test()->markTestSkipped("Fixture SPED real (gitignored) ausente: {$path}");
    }

    return $path;
}

function userClienteShadow(): array
{
    $user = User::factory()->create();
    $clienteId = DB::table('clientes')->insertGetId([
        'user_id' => $user->id,
        'razao_social' => 'UTIDA',
        'documento' => '10440482000154',
        'is_empresa_propria' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [$user, $clienteId];
}

it('dry-run aprova o UTIDA e NÃO persiste (reverte a transação)', function () {
    $path = fixtureUtidaShadow();
    [$user, $clienteId] = userClienteShadow();

    $this->artisan('efd:motor-shadow', [
        'arquivo' => $path,
        '--user' => $user->id,
        '--cliente' => $clienteId,
    ])->assertExitCode(0);

    // Oráculo aprovou (exit 0), mas nada ficou no banco — dry-run reverte.
    expect(DB::table('efd_notas')->count())->toBe(0);
    expect(DB::table('efd_importacoes')->count())->toBe(0);
    expect(DB::table('efd_notas_consolidados')->count())->toBe(0);
});

it('dry-run aprova o UTIDA fevereiro (2º mês real) pelo oráculo completo', function () {
    $path = __DIR__.'/../../Fixtures/sped/UTIDA-fev2026-somente-dados.txt';
    if (! is_file($path)) {
        test()->markTestSkipped("Fixture fev (gitignored) ausente: {$path}");
    }
    [$user, $clienteId] = userClienteShadow();

    // exit 0 ⟺ status concluído + integridade.ok + 0 divergência ERRO no auditar().
    // É o gate de aceite F4 rodado contra dado fiscal real — o 2º mês, além do jan.
    $this->artisan('efd:motor-shadow', [
        'arquivo' => $path,
        '--user' => $user->id,
        '--cliente' => $clienteId,
    ])->assertExitCode(0);

    expect(DB::table('efd_notas')->count())->toBe(0); // dry-run reverte
});

it('--commit persiste as 1433 notas do UTIDA', function () {
    $path = fixtureUtidaShadow();
    [$user, $clienteId] = userClienteShadow();

    $this->artisan('efd:motor-shadow', [
        'arquivo' => $path,
        '--user' => $user->id,
        '--cliente' => $clienteId,
        '--commit' => true,
    ])->assertExitCode(0);

    expect(DB::table('efd_notas')->count())->toBe(1433);
    expect(DB::table('efd_importacoes')->where('status', 'concluido')->count())->toBe(1);
});
