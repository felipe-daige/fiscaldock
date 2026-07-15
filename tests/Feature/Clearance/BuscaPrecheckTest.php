<?php

use App\Models\Cliente;
use App\Models\NfeConsulta;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use App\Services\ValidacaoContabilService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function bpUser(): User
{
    return User::factory()->create(['credits' => 1000]);
}

function bpCliente(User $u): Cliente
{
    return Cliente::create([
        'user_id' => $u->id, 'is_empresa_propria' => true, 'tipo_pessoa' => 'PJ',
        'documento' => '00000000000191', 'razao_social' => 'Propria',
    ]);
}

// chave NF-e modelo 55 com dígito verificador (módulo 11) válido
function bpChaveNfe(): string
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

function bpNotaXml(User $u, Cliente $c, string $chave): XmlNota
{
    $imp = XmlImportacao::create([
        'user_id' => $u->id, 'cliente_id' => $c->id, 'status' => 'concluido', 'tipo_documento' => 'NFE',
    ]);

    return XmlNota::create([
        'user_id' => $u->id, 'importacao_xml_id' => $imp->id, 'cliente_id' => $c->id,
        'chave_acesso' => $chave,
        'tipo_documento' => 'NFE', 'numero_documento' => 222, 'serie' => 1,
        'data_emissao' => '2026-01-20 10:00:00', 'valor_total' => 500.00, 'tipo_nota' => XmlNota::TIPO_SAIDA,
        'emit_documento' => '00000000000191', 'emit_razao_social' => 'Empresa Propria',
        'dest_documento' => '13305697000150', 'dest_razao_social' => 'Cliente',
        'payload' => [],
    ]);
}

it('flag off → 503', function () {
    config()->set('clearance.busca_avulsa.habilitada', false);
    $user = bpUser();

    actingAs($user)->postJson('/app/clearance/buscar/precheck', [
        'chave_acesso' => bpChaveNfe(),
    ])->assertStatus(503);
});

it('chave com menos de 44 dígitos → 422', function () {
    config()->set('clearance.busca_avulsa.habilitada', true);
    $user = bpUser();

    actingAs($user)->postJson('/app/clearance/buscar/precheck', [
        'chave_acesso' => '123',
    ])->assertStatus(422);
});

it('chave nova (sem acervo nem snapshot) → no_acervo=false, snapshot=null', function () {
    config()->set('clearance.busca_avulsa.habilitada', true);
    $user = bpUser();

    actingAs($user)->postJson('/app/clearance/buscar/precheck', [
        'chave_acesso' => bpChaveNfe(),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('no_acervo', false)
        ->assertJsonPath('snapshot', null)
        ->assertJsonPath('custo_avulsa', fn ($v) => (float) $v === ValidacaoContabilService::CUSTO_DOCUMENTO);
});

it('chave no acervo XML → no_acervo=true com nota, urls e custos', function () {
    config()->set('clearance.busca_avulsa.habilitada', true);
    $user = bpUser();
    $cli = bpCliente($user);
    $chave = bpChaveNfe();
    $nota = bpNotaXml($user, $cli, $chave);

    $resp = actingAs($user)->postJson('/app/clearance/buscar/precheck', [
        'chave_acesso' => $chave,
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('no_acervo', true)
        ->assertJsonPath('origem', 'xml')
        ->assertJsonPath('nota_id', $nota->id)
        ->assertJsonPath('custo_clearance', fn ($v) => (float) $v === ValidacaoContabilService::CUSTO_DOCUMENTO)
        ->assertJsonPath('custo_avulsa', fn ($v) => (float) $v === ValidacaoContabilService::CUSTO_DOCUMENTO)
        ->assertJsonPath('nota.tipo_documento', 'NFE');

    expect($resp->json('listagem_url'))->toContain('/app/notas')->toContain($chave);
    expect($resp->json('detalhe_url'))->toContain('/app/notas/xml/'.$nota->id);
});

it('chave só com snapshot → no_acervo=false com dados do snapshot', function () {
    config()->set('clearance.busca_avulsa.habilitada', true);
    $user = bpUser();
    $chave = bpChaveNfe();
    NfeConsulta::create([
        'user_id' => $user->id, 'chave_acesso' => $chave,
        'status' => 'AUTORIZADA', 'consultado_em' => '2026-07-01 12:00:00',
    ]);

    actingAs($user)->postJson('/app/clearance/buscar/precheck', [
        'chave_acesso' => $chave,
    ])->assertOk()
        ->assertJsonPath('no_acervo', false)
        ->assertJsonPath('snapshot.status', 'AUTORIZADA');
});

it('não vaza acervo de outro usuário', function () {
    config()->set('clearance.busca_avulsa.habilitada', true);
    $dono = bpUser();
    $outro = bpUser();
    $chave = bpChaveNfe();
    bpNotaXml($dono, bpCliente($dono), $chave);

    actingAs($outro)->postJson('/app/clearance/buscar/precheck', [
        'chave_acesso' => $chave,
    ])->assertOk()->assertJsonPath('no_acervo', false);
});
