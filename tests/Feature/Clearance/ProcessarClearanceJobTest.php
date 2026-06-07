<?php

use App\Jobs\ProcessarClearanceJob;
use App\Models\ConsultaLote;
use App\Models\NfeConsulta;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config()->set('consultas.providers.infosimples.token', 'tok');
    config()->set('consultas.providers.infosimples.rate_limit_por_segundo', 0);
});

function loteParaClearance(int $userId): ConsultaLote
{
    return ConsultaLote::create([
        'user_id' => $userId, 'plano_id' => null, 'status' => ConsultaLote::STATUS_PROCESSANDO,
        'total_participantes' => 1, 'creditos_cobrados' => 3, 'tab_id' => 'tab-c',
    ]);
}

function chaveNfeJob(): string
{
    return substr_replace(str_repeat('5', 44), '55', 20, 2);
}

it('sucesso: persiste snapshot e não acumula estorno', function () {
    $user = User::factory()->create();
    $lote = loteParaClearance($user->id);
    $chave = chaveNfeJob();
    Http::fake(['api.infosimples.com/*' => Http::response(json_decode(file_get_contents(base_path('tests/Fixtures/Clearance/nfe_200_autorizada.json')), true), 200)]);

    ProcessarClearanceJob::dispatchSync(
        loteId: $lote->id, chave: $chave, tipoDocumento: 'nfe', userId: $user->id, tabId: 'tab-c',
        clienteId: null, custoCreditos: 3, indice: 1, total: 1,
    );

    expect(NfeConsulta::where('user_id', $user->id)->where('chave_acesso', $chave)->exists())->toBeTrue();
    expect((int) Cache::get("clearance_estorno:{$lote->id}:{$chave}", 0))->toBe(0);
    expect(Cache::get("progresso:{$user->id}:tab-c"))->not->toBeNull();
});

it('erro de parâmetro: NÃO persiste e acumula estorno = custoCreditos', function () {
    $user = User::factory()->create();
    $lote = loteParaClearance($user->id);
    $chave = chaveNfeJob();
    Http::fake(['api.infosimples.com/*' => Http::response(['code' => 608, 'header' => ['billable' => false], 'errors' => ['x']], 200)]);

    ProcessarClearanceJob::dispatchSync(
        loteId: $lote->id, chave: $chave, tipoDocumento: 'nfe', userId: $user->id, tabId: 'tab-c',
        clienteId: null, custoCreditos: 3, indice: 1, total: 1,
    );

    expect(NfeConsulta::where('user_id', $user->id)->where('chave_acesso', $chave)->exists())->toBeFalse();
    expect((int) Cache::get("clearance_estorno:{$lote->id}:{$chave}", 0))->toBe(3);
});

it('idempotência: chave já persistida no lote não re-consulta', function () {
    $user = User::factory()->create();
    $lote = loteParaClearance($user->id);
    $chave = chaveNfeJob();
    NfeConsulta::create(['user_id' => $user->id, 'chave_acesso' => $chave, 'status' => 'AUTORIZADA', 'tipo_documento' => 'NFE', 'consulta_lote_id' => $lote->id]);
    Http::fake();

    ProcessarClearanceJob::dispatchSync(
        loteId: $lote->id, chave: $chave, tipoDocumento: 'nfe', userId: $user->id, tabId: 'tab-c',
        clienteId: null, custoCreditos: 3, indice: 1, total: 1,
    );

    Http::assertNothingSent();
});

it('herda cliente_id da nota no snapshot', function () {
    $user = User::factory()->create();
    $lote = loteParaClearance($user->id);
    $clienteId = \Illuminate\Support\Facades\DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'CLIENTE X', 'documento' => '11111111000191',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $chave = chaveNfeJob();
    Http::fake(['api.infosimples.com/*' => Http::response(json_decode(file_get_contents(base_path('tests/Fixtures/Clearance/nfe_200_autorizada.json')), true), 200)]);

    ProcessarClearanceJob::dispatchSync(
        loteId: $lote->id, chave: $chave, tipoDocumento: 'nfe', userId: $user->id, tabId: 'tab-c',
        clienteId: $clienteId, custoCreditos: 3, indice: 1, total: 1,
    );

    expect(NfeConsulta::where('user_id', $user->id)->where('chave_acesso', $chave)->value('cliente_id'))->toBe($clienteId);
});
