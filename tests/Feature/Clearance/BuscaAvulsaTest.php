<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\NfeConsulta;
use App\Models\User;
use App\Services\ValidacaoContabilService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function baUser(): User
{
    return User::factory()->create(['credits' => 1000]);
}

function baCliente(User $u): Cliente
{
    return Cliente::create([
        'user_id' => $u->id, 'is_empresa_propria' => true, 'tipo_pessoa' => 'PJ',
        'documento' => '00000000000191', 'razao_social' => 'Propria',
    ]);
}

// chave NF-e modelo 55 com dígito verificador (módulo 11) válido
function baChaveNfe(): string
{
    $base = '3524041330569700015055000000040404195394099'; // 43 dígitos
    $peso = 2;
    $soma = 0;
    for ($i = strlen($base) - 1; $i >= 0; $i--) {
        $soma += ((int) $base[$i]) * $peso;
        $peso = $peso === 9 ? 2 : $peso + 1;
    }
    $resto = $soma % 11;
    $dv = ($resto === 0 || $resto === 1) ? 0 : 11 - $resto;

    return $base.$dv;
}

it('flag off → 503, sem cobrar nem despachar', function () {
    config()->set('clearance.busca_avulsa.habilitada', false);
    Bus::fake();
    $user = baUser();
    $cli = baCliente($user);

    actingAs($user)->postJson('/app/clearance/buscar/consultar', [
        'tipo_documento' => 'nfe', 'chave_acesso' => baChaveNfe(), 'cliente_id' => $cli->id, 'tab_id' => 'tab-ba',
    ])->assertStatus(503);

    expect(ConsultaLote::count())->toBe(0);
    Bus::assertNothingBatched();
});

it('flag on, chave nova → debita o custo do documento e despacha batch com cliente_id', function () {
    config()->set('clearance.busca_avulsa.habilitada', true);
    Bus::fake();
    Http::fake();
    $user = baUser();
    $cli = baCliente($user);
    $saldo = $user->credits;

    actingAs($user)->postJson('/app/clearance/buscar/consultar', [
        'tipo_documento' => 'nfe', 'chave_acesso' => baChaveNfe(), 'cliente_id' => $cli->id, 'tab_id' => 'tab-ba',
    ])->assertOk()->assertJsonPath('success', true);

    expect($user->fresh()->credits)->toBe($saldo - ValidacaoContabilService::CUSTO_DOCUMENTO);
    expect(ConsultaLote::latest('id')->first()?->resultado_resumo['fluxo_origem'] ?? null)->toBe('avulsa');
    Bus::assertBatched(fn ($b) => collect($b->jobs)->every(fn ($j) => $j->clienteId === $cli->id));
});

it('chave com snapshot e sem reconsultar → não cobra', function () {
    config()->set('clearance.busca_avulsa.habilitada', true);
    Bus::fake();
    $user = baUser();
    $cli = baCliente($user);
    $chave = baChaveNfe();
    NfeConsulta::create(['user_id' => $user->id, 'chave_acesso' => $chave, 'tipo_documento' => 'NFE', 'modelo' => '55', 'status' => 'AUTORIZADA']);
    $saldo = $user->credits;

    actingAs($user)->postJson('/app/clearance/buscar/consultar', [
        'tipo_documento' => 'nfe', 'chave_acesso' => $chave, 'cliente_id' => $cli->id, 'tab_id' => 'tab-ba',
    ])->assertOk();

    expect($user->fresh()->credits)->toBe($saldo);
    Bus::assertNothingBatched();
});

it('chave com snapshot + reconsultar=true → cobra e despacha', function () {
    config()->set('clearance.busca_avulsa.habilitada', true);
    Bus::fake();
    Http::fake();
    $user = baUser();
    $cli = baCliente($user);
    $chave = baChaveNfe();
    NfeConsulta::create(['user_id' => $user->id, 'chave_acesso' => $chave, 'tipo_documento' => 'NFE', 'modelo' => '55', 'status' => 'AUTORIZADA']);
    $saldo = $user->credits;

    actingAs($user)->postJson('/app/clearance/buscar/consultar', [
        'tipo_documento' => 'nfe', 'chave_acesso' => $chave, 'cliente_id' => $cli->id, 'tab_id' => 'tab-ba', 'reconsultar' => true,
    ])->assertOk();

    expect($user->fresh()->credits)->toBe($saldo - ValidacaoContabilService::CUSTO_DOCUMENTO);
    Bus::assertBatched(fn ($b) => count($b->jobs) === 1);
});

it('view buscar mostra banner em desenvolvimento quando flag off', function () {
    config()->set('clearance.busca_avulsa.habilitada', false);
    $user = baUser();
    baCliente($user);

    actingAs($user)->get('/app/clearance/buscar')
        ->assertOk()
        ->assertSee('em desenvolvimento', false);
});
