<?php

use App\Jobs\ProcessarEfdImportacaoJob;
use App\Models\EfdImportacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * E2E do motor Laravel (F3): roda o Job inteiro contra o SPED real da UTIDA (jan/2026)
 * e prova que o banco reproduz o arquivo — 1433 C100 (não 1, o bug UTIDA), a árvore
 * pai-filho ligada, a apuração agregada e o guardrail de integridade verde.
 *
 * O arquivo é dado fiscal real de cliente (gitignored em /tests/Fixtures/sped/); o teste
 * pula se ausente.
 */
if (! function_exists('spedUtidaJanFixture')) {
    function spedUtidaJanFixture(): string
    {
        $path = __DIR__.'/../../Fixtures/sped/UTIDA-jan2026-somente-dados.txt';
        if (! is_file($path)) {
            test()->markTestSkipped("Fixture SPED real (gitignored) ausente: {$path}");
        }

        return (string) file_get_contents($path);
    }
}

/** Cria user + cliente (empresa própria) + importação com o SPED retido em arquivo_base64. */
function criarImportacaoUtida(): array
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

    $imp = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $clienteId,
        'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'UTIDA-jan2026.txt',
        'arquivo_base64' => json_encode(spedUtidaJanFixture()),
        'status' => 'processando',
        'iniciado_em' => now()->subMinutes(2),
    ]);

    return [$user, $clienteId, $imp];
}

it('persiste o SPED UTIDA inteiro: 1433 notas, consolidados, item, apuração', function () {
    [$user, $clienteId, $imp] = criarImportacaoUtida();

    ProcessarEfdImportacaoJob::dispatchSync($imp->id, 'tab-e2e');

    $notas = DB::table('efd_notas')->where('importacao_id', $imp->id);

    // 1433 C100 — o número que o merge do n8n colapsava pra 1 (bug UTIDA).
    expect($notas->count())->toBe(1433);
    // 1432 NFC-e (modelo 65, COD_PART vazio) preservadas + 1 NF-e modelo 55.
    expect((clone $notas)->where('modelo', '65')->count())->toBe(1432);
    expect((clone $notas)->where('modelo', '55')->count())->toBe(1);
    // Toda nota fiscal tem chave de 44.
    expect((clone $notas)->whereRaw('length(chave_acesso) <> 44')->count())->toBe(0);
    // Nenhuma cancelada no arquivo.
    expect((clone $notas)->where('cancelada', true)->count())->toBe(0);

    // Consolidados (C190) e item (C170) ligados às notas.
    expect(DB::table('efd_notas_consolidados')->where('user_id', $user->id)->count())->toBe(2167);
    expect(DB::table('efd_notas_itens')->where('user_id', $user->id)->count())->toBe(1);

    // O único 0150 (getnet) virou participante desta importação.
    expect(DB::table('participantes')->where('importacao_efd_id', $imp->id)->count())->toBe(1);

    // Catálogo (0200) — 1 item.
    expect(DB::table('efd_catalogo_itens')->where('importacao_id', $imp->id)->count())->toBe(1);

    // Apuração ICMS (bloco E) agregada em 1 linha, com período.
    $apuracao = DB::table('efd_apuracoes_icms')->where('importacao_id', $imp->id)->first();
    expect($apuracao)->not->toBeNull();
    expect($apuracao->periodo_inicio)->not->toBeNull();
});

it('marca a importação concluída com integridade verde', function () {
    [$user, $clienteId, $imp] = criarImportacaoUtida();

    ProcessarEfdImportacaoJob::dispatchSync($imp->id, 'tab-e2e');

    $imp->refresh();
    expect($imp->status)->toBe('concluido');

    $resumo = $imp->resumo_final;
    // Guardrail: banco reproduz o SPED — nenhuma nota dropada.
    expect($resumo['integridade']['ok'])->toBeTrue();
    expect($resumo['integridade']['esperadas'])->toBe(1433);
    expect($resumo['integridade']['faltando'])->toBe(0);
    // Resumo bate com o persistido, com NF-e e NFC-e em blocos distintos (UTIDA = varejo:
    // 1 NF-e modelo 55 + 1432 NFC-e modelo 65).
    expect($resumo['blocos']['notas_mercadorias']['total_notas'])->toBe(1);
    expect($resumo['blocos']['notas_consumidor']['total_notas'])->toBe(1432);
    expect($resumo['estatisticas']['total_notas_processadas'])->toBe(1433);
});

it('emite progresso por bloco no cache do SSE', function () {
    [$user, $clienteId, $imp] = criarImportacaoUtida();

    ProcessarEfdImportacaoJob::dispatchSync($imp->id, 'tab-e2e');

    $bloco = Cache::get("efd_notas_progress:{$user->id}:tab-e2e:notas_mercadorias");
    expect($bloco['status'])->toBe('concluido');

    $principal = Cache::get("progresso:{$user->id}:tab-e2e");
    expect($principal['status'])->toBe('concluido');
    expect($principal['importacao_id'])->toBe($imp->id);
});

it('é idempotente: reprocessar não duplica notas/consolidados/apuração', function () {
    [$user, $clienteId, $imp] = criarImportacaoUtida();

    ProcessarEfdImportacaoJob::dispatchSync($imp->id, 'tab-e2e');
    ProcessarEfdImportacaoJob::dispatchSync($imp->id, 'tab-e2e');

    expect(DB::table('efd_notas')->where('importacao_id', $imp->id)->count())->toBe(1433);
    expect(DB::table('efd_notas_consolidados')->where('user_id', $user->id)->count())->toBe(2167);
    expect(DB::table('efd_notas_itens')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('efd_apuracoes_icms')->where('importacao_id', $imp->id)->count())->toBe(1);
});
