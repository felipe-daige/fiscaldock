<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Services\SaldoService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

// listarConsultasDfePorLote usa sintaxe PostgreSQL — força pgsql (fiscaldock_test).
$testUserIds = [];

beforeEach(function () use (&$testUserIds) {
    $testUserIds = [];
    config([
        'database.default' => 'pgsql',
        'database.connections.pgsql.host' => env('DB_HOST', 'postgres'),
        'database.connections.pgsql.port' => env('DB_PORT', 5432),
        'database.connections.pgsql.database' => 'fiscaldock_test',
        'database.connections.pgsql.username' => env('DB_USERNAME', 'postgres'),
        'database.connections.pgsql.password' => env('DB_PASSWORD', 'fdpCjI5U7KvpBdWjVLzzAEs2q5NOeGRu'),
        'database.connections.pgsql.schema' => 'public',
    ]);
    config(['consultas.infosimples_ativo' => true]);
    DB::purge('pgsql');
    DB::reconnect('pgsql');
});

afterEach(function () use (&$testUserIds) {
    if (! empty($testUserIds)) {
        User::whereIn('id', $testUserIds)->delete();
    }
});

/** Monta lote CT-e finalizado com 1 contraparte SEM IE e 1 COM IE. Retorna [user, lote, semIeId]. */
function sintegraCenario(array &$ids): array
{
    $user = User::factory()->create();
    $ids[] = $user->id;

    $cliente = Cliente::create([
        'user_id' => $user->id, 'tipo_pessoa' => 'PJ',
        'documento' => str_pad((string) random_int(1, 99_999_999_999_999), 14, '0', STR_PAD_LEFT),
        'razao_social' => 'Empresa Propria', 'is_empresa_propria' => true,
    ]);

    $semIe = Participante::create([
        'user_id' => $user->id, 'documento' => '27371932000105', 'razao_social' => 'ROGERIO',
        'uf' => 'MS', 'tipo_documento' => 'PJ',
    ]);
    $comIe = Participante::create([
        'user_id' => $user->id, 'documento' => '46970030000202', 'razao_social' => 'PANTANAL',
        'uf' => 'MS', 'inscricao_estadual' => '284722995', 'tipo_documento' => 'PJ',
    ]);

    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'status' => 'finalizado', 'total_participantes' => 2,
        'creditos_cobrados' => 20, 'tab_id' => 'tab-sint', 'processado_em' => now(),
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI',
        'status' => 'finalizada', 'periodo_inicial' => '2024-02-01', 'periodo_final' => '2024-02-29',
    ]);

    $chaveSemIe = '35240227371932000105570010000111111000772218';
    $chaveComIe = '35240246970030000202570010000432381000772218';

    EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id,
        'participante_id' => $semIe->id, 'chave_acesso' => $chaveSemIe, 'modelo' => '57',
        'numero' => 1111, 'serie' => '1', 'data_emissao' => '2024-02-29', 'tipo_operacao' => 'entrada',
        'origem_arquivo' => 'fiscal', 'valor_total' => 100.00,
    ]);
    EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id,
        'participante_id' => $comIe->id, 'chave_acesso' => $chaveComIe, 'modelo' => '57',
        'numero' => 43238, 'serie' => '1', 'data_emissao' => '2024-02-29', 'tipo_operacao' => 'entrada',
        'origem_arquivo' => 'fiscal', 'valor_total' => 200.00,
    ]);

    foreach ([[$chaveSemIe, '27371932000105'], [$chaveComIe, '46970030000202']] as [$ch, $cnpj]) {
        DB::table('cte_consultas')->insert([
            'user_id' => $user->id, 'consulta_lote_id' => $lote->id, 'chave_acesso' => $ch,
            'tipo_documento' => 'CTE', 'modelo' => '57', 'serie' => 1, 'status' => 'AUTORIZADA',
            'valor_prestacao' => 100.00, 'emit_cnpj' => $cnpj, 'emit_nome' => 'X',
            'consultado_em' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    return [$user, $lote, $semIe->id];
}

it('preview conta só participantes sem IE do lote e calcula custo', function () use (&$testUserIds) {
    [$user, $lote, $semIeId] = sintegraCenario($testUserIds);

    actingAs($user)
        ->postJson('/app/clearance/sintegra/preview', ['lote_id' => $lote->id])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'total' => 1,
            // Response expõe R$ (rename saldo): 1 participante × 2 unidades = R$ 0,40.
            'valor_reais' => app(\App\Services\PricingCatalogService::class)->creditsToCurrency(2),
            'participante_ids' => [$semIeId],
        ]);
});

it('executar debita saldo e despacha batch sintegra-only', function () use (&$testUserIds) {
    Bus::fake();
    [$user, $lote, $semIeId] = sintegraCenario($testUserIds);
    app(SaldoService::class)->add($user, 100, 'manual_add');
    $saldoAntes = app(SaldoService::class)->getBalance($user);

    actingAs($user)
        ->postJson('/app/clearance/sintegra/executar', ['lote_id' => $lote->id, 'tab_id' => 'tab-sint'])
        ->assertOk()
        ->assertJson(['success' => true, 'total' => 1, 'valor_reais' => app(\App\Services\PricingCatalogService::class)->creditsToCurrency(2), 'participante_ids' => [$semIeId]]);

    expect(app(SaldoService::class)->getBalance($user))->toBe($saldoAntes - 2);
    Bus::assertBatched(fn ($batch) => str_starts_with($batch->name, 'sintegra-ie-') && $batch->jobs->count() === 1);
});

it('executar recusa quando saldo insuficiente', function () use (&$testUserIds) {
    Bus::fake();
    [$user, $lote] = sintegraCenario($testUserIds);
    // zera saldo
    $saldo = app(SaldoService::class)->getBalance($user);
    if ($saldo > 0) {
        app(SaldoService::class)->deduct($user, $saldo, 'manual_add');
    }

    actingAs($user)
        ->postJson('/app/clearance/sintegra/executar', ['lote_id' => $lote->id])
        ->assertStatus(402);
    Bus::assertNothingBatched();
});

it('status reporta prontos/pendentes por IE preenchida', function () use (&$testUserIds) {
    [$user, $lote, $semIeId] = sintegraCenario($testUserIds);

    actingAs($user)
        ->postJson('/app/clearance/sintegra/status', ['participante_ids' => [$semIeId]])
        ->assertOk()
        ->assertJson(['success' => true, 'total' => 1, 'prontos' => 0, 'pendentes' => 1]);

    Participante::where('id', $semIeId)->update(['inscricao_estadual' => '123456789']);

    actingAs($user)
        ->postJson('/app/clearance/sintegra/status', ['participante_ids' => [$semIeId]])
        ->assertOk()
        ->assertJson(['prontos' => 1, 'pendentes' => 0]);
});

it('preview de participante já com IE retorna total 0', function () use (&$testUserIds) {
    [$user, $lote, $semIeId] = sintegraCenario($testUserIds);
    Participante::where('id', $semIeId)->update(['inscricao_estadual' => '999']);

    actingAs($user)
        ->postJson('/app/clearance/sintegra/preview', ['lote_id' => $lote->id])
        ->assertOk()
        ->assertJson(['success' => true, 'total' => 0]);
});
