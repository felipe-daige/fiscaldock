<?php

use App\Models\User;
use App\Models\XmlNota;
use App\Services\Clearance\Comparacao\DivergenciaIndicadoresService;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

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
    DB::purge('pgsql');
    DB::reconnect('pgsql');
});

afterEach(function () use (&$testUserIds) {
    if (! empty($testUserIds)) {
        User::whereIn('id', $testUserIds)->delete();
    }
});

function indicadoresCriarUser(array &$ids): User
{
    $user = User::factory()->create();
    $ids[] = $user->id;

    return $user;
}

function indicadoresCriarNota(int $userId, string $chave, ?string $sev, ?int $count, float $valor, string $emitCnpj, ?string $emitRazao, ?string $situacaoSefaz): int
{
    return DB::table('xml_notas')->insertGetId([
        'user_id' => $userId,
        'nfe_id' => $chave,
        'origem' => 'xml_upload',
        'tipo_documento' => 'NFE',
        'numero_nota' => mt_rand(1, 99999),
        'serie' => 1,
        'data_emissao' => '2026-04-12 10:00:00',
        'valor_total' => $valor,
        'tipo_nota' => XmlNota::TIPO_SAIDA,
        'emit_cnpj' => $emitCnpj,
        'emit_razao_social' => $emitRazao,
        'dest_cnpj' => '98765432000110',
        'situacao_sefaz' => $situacaoSefaz,
        'verificado_sefaz_em' => $situacaoSefaz ? '2026-04-12 14:00:00' : null,
        'divergencia_severidade' => $sev,
        'divergencia_count' => $count,
        'comparado_em' => $sev ? now() : null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('resumo agrega contagens e valor exposto das notas criticas', function () use (&$testUserIds) {
    $user = indicadoresCriarUser($testUserIds);

    indicadoresCriarNota($user->id, '35202404123456789012555000001234567890123500', XmlNota::DIVERGENCIA_OK, 0, 100.00, '11111111000111', 'Emitente A', 'AUTORIZADA');
    indicadoresCriarNota($user->id, '35202404123456789012555000001234567890123501', XmlNota::DIVERGENCIA_REVISAR, 2, 200.00, '11111111000111', 'Emitente A', 'AUTORIZADA');
    indicadoresCriarNota($user->id, '35202404123456789012555000001234567890123502', XmlNota::DIVERGENCIA_CRITICA, 5, 1000.00, '22222222000122', 'Emitente B', 'AUTORIZADA');
    indicadoresCriarNota($user->id, '35202404123456789012555000001234567890123503', XmlNota::DIVERGENCIA_CRITICA, 3, 500.00, '22222222000122', 'Emitente B', 'AUTORIZADA');
    indicadoresCriarNota($user->id, '35202404123456789012555000001234567890123504', null, null, 50.00, '33333333000133', 'Emitente C', null); // sem snapshot

    $service = app(DivergenciaIndicadoresService::class);
    $resumo = $service->resumo($user->id);

    expect($resumo['total_geral'])->toBe(5);
    expect($resumo['total_com_snapshot'])->toBe(4);
    expect($resumo['sem_snapshot'])->toBe(1);
    expect($resumo['ok'])->toBe(1);
    expect($resumo['revisar'])->toBe(1);
    expect($resumo['critica'])->toBe(2);
    expect((float) $resumo['valor_exposto'])->toBe(1500.00);
});

it('topEmitentes ranqueia por numero de divergencias', function () use (&$testUserIds) {
    $user = indicadoresCriarUser($testUserIds);

    indicadoresCriarNota($user->id, '35202404123456789012555000001234567890123510', XmlNota::DIVERGENCIA_CRITICA, 5, 1000.00, '22222222000122', 'Emitente B', 'AUTORIZADA');
    indicadoresCriarNota($user->id, '35202404123456789012555000001234567890123511', XmlNota::DIVERGENCIA_CRITICA, 3, 500.00, '22222222000122', 'Emitente B', 'AUTORIZADA');
    indicadoresCriarNota($user->id, '35202404123456789012555000001234567890123512', XmlNota::DIVERGENCIA_REVISAR, 1, 200.00, '11111111000111', 'Emitente A', 'AUTORIZADA');
    indicadoresCriarNota($user->id, '35202404123456789012555000001234567890123513', XmlNota::DIVERGENCIA_OK, 0, 100.00, '11111111000111', 'Emitente A', 'AUTORIZADA');

    $top = app(DivergenciaIndicadoresService::class)->topEmitentes($user->id, 5);

    expect($top)->toHaveCount(2);
    expect($top[0]['cnpj'])->toBe('22222222000122');
    expect($top[0]['divergencias'])->toBe(2);
    expect($top[0]['criticas'])->toBe(2);
    expect((float) $top[0]['valor_exposto'])->toBe(1500.00);
    expect($top[1]['cnpj'])->toBe('11111111000111');
    expect($top[1]['divergencias'])->toBe(1);
});

it('dashboard mostra bloco de divergencia com contagens', function () use (&$testUserIds) {
    $user = indicadoresCriarUser($testUserIds);

    indicadoresCriarNota($user->id, '35202404123456789012555000001234567890123520', XmlNota::DIVERGENCIA_CRITICA, 5, 1000.00, '22222222000122', 'Emitente B', 'AUTORIZADA');

    actingAs($user);
    $r = get('/app/clearance/dashboard');

    $r->assertStatus(200);
    $r->assertSee('Declarado vs SEFAZ');
    $r->assertSee('Crítica');
    $r->assertSee('Top emitentes com divergência');
});

it('alertas mostra notas criticas mais recentes', function () use (&$testUserIds) {
    $user = indicadoresCriarUser($testUserIds);

    indicadoresCriarNota($user->id, '35202404123456789012555000001234567890123530', XmlNota::DIVERGENCIA_CRITICA, 7, 999.00, '22222222000122', 'Emitente Crítico', 'AUTORIZADA');

    actingAs($user);
    $r = get('/app/clearance/alertas');

    $r->assertStatus(200);
    $r->assertSee('Divergências declarado vs SEFAZ');
    $r->assertSee('Emitente Crítico');
    $body = $r->getContent();
    expect($body)->toContain('35202404123456789012555000001234567890123530');
});
