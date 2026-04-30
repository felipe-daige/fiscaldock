<?php

use App\Models\User;
use App\Models\XmlNota;
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

function listagemCriarUser(array &$ids): User
{
    $user = User::factory()->create();
    $ids[] = $user->id;

    return $user;
}

function listagemCriarNota(int $userId, string $chave, int $numero, ?string $sev, ?int $count, ?string $situacaoSefaz): int
{
    // INSERT direto pra evitar que o XmlNotaSefazSyncObserver recalcule
    // (queremos controlar exatamente o snapshot persistido nos asserts).
    return DB::table('xml_notas')->insertGetId([
        'user_id' => $userId,
        'nfe_id' => $chave,
        'origem' => 'xml_upload',
        'tipo_documento' => 'NFE',
        'numero_nota' => $numero,
        'serie' => 1,
        'data_emissao' => '2026-04-12 10:00:00',
        'valor_total' => 100.00,
        'tipo_nota' => XmlNota::TIPO_SAIDA,
        'emit_cnpj' => '12345678000190',
        'dest_cnpj' => '98765432000110',
        'situacao_sefaz' => $situacaoSefaz,
        'verificado_sefaz_em' => $situacaoSefaz ? '2026-04-12 14:00:00' : null,
        'divergencia_severidade' => $sev,
        'divergencia_count' => $count,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('filtro divergencia=CRITICA retorna so notas criticas', function () use (&$testUserIds) {
    $user = listagemCriarUser($testUserIds);

    listagemCriarNota($user->id, '35202404123456789012555000001234567890123410', 10, XmlNota::DIVERGENCIA_CRITICA, 5, 'AUTORIZADA');
    listagemCriarNota($user->id, '35202404123456789012555000001234567890123411', 11, XmlNota::DIVERGENCIA_OK, 0, 'AUTORIZADA');
    listagemCriarNota($user->id, '35202404123456789012555000001234567890123412', 12, XmlNota::DIVERGENCIA_REVISAR, 2, 'AUTORIZADA');

    actingAs($user);
    $r = get('/app/clearance/notas?divergencia=CRITICA');

    $r->assertStatus(200);
    $r->assertSee('35202404123456789012555000001234567890123410');
    $r->assertDontSee('35202404123456789012555000001234567890123411');
    $r->assertDontSee('35202404123456789012555000001234567890123412');
});

it('filtro divergencia=COM_DIVERGENCIA retorna CRITICA + REVISAR', function () use (&$testUserIds) {
    $user = listagemCriarUser($testUserIds);

    listagemCriarNota($user->id, '35202404123456789012555000001234567890123420', 20, XmlNota::DIVERGENCIA_CRITICA, 5, 'AUTORIZADA');
    listagemCriarNota($user->id, '35202404123456789012555000001234567890123421', 21, XmlNota::DIVERGENCIA_OK, 0, 'AUTORIZADA');
    listagemCriarNota($user->id, '35202404123456789012555000001234567890123422', 22, XmlNota::DIVERGENCIA_REVISAR, 2, 'AUTORIZADA');

    actingAs($user);
    $r = get('/app/clearance/notas?divergencia=COM_DIVERGENCIA');

    $r->assertStatus(200);
    $body = $r->getContent();
    expect($body)->toContain('35202404123456789012555000001234567890123420');
    expect($body)->toContain('35202404123456789012555000001234567890123422');
    expect($body)->not->toContain('35202404123456789012555000001234567890123421');
});

it('filtro divergencia=SEM_SNAPSHOT retorna so notas sem situacao_sefaz', function () use (&$testUserIds) {
    $user = listagemCriarUser($testUserIds);

    listagemCriarNota($user->id, '35202404123456789012555000001234567890123430', 30, null, null, null);
    listagemCriarNota($user->id, '35202404123456789012555000001234567890123431', 31, XmlNota::DIVERGENCIA_OK, 0, 'AUTORIZADA');

    actingAs($user);
    $r = get('/app/clearance/notas?divergencia=SEM_SNAPSHOT');

    $r->assertStatus(200);
    $r->assertSee('35202404123456789012555000001234567890123430');
    $r->assertDontSee('35202404123456789012555000001234567890123431');
});

it('listagem renderiza coluna Divergencia com badge CRITICA', function () use (&$testUserIds) {
    $user = listagemCriarUser($testUserIds);

    listagemCriarNota($user->id, '35202404123456789012555000001234567890123440', 40, XmlNota::DIVERGENCIA_CRITICA, 7, 'AUTORIZADA');

    actingAs($user);
    $r = get('/app/clearance/notas');

    $r->assertStatus(200);
    $r->assertSee('Divergência');
    $r->assertSee('Crítica'); // badge
});
