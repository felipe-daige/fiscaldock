<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\NfeConsulta;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function cpChave(): string
{
    return '50240197551165000193550010000248001000214739';
}

function cpSnapshot(User $u, ?int $clienteId = null): NfeConsulta
{
    return NfeConsulta::create([
        'user_id' => $u->id, 'cliente_id' => $clienteId, 'chave_acesso' => cpChave(),
        'tipo_documento' => 'NFE', 'modelo' => '55', 'status' => 'AUTORIZADA',
        'emit_cnpj' => '97551165000193', 'emit_nome' => 'HIDRATOP COMERCIO',
        'emit_uf' => 'MS', 'emit_municipio' => 'CAMPO GRANDE', 'emit_ie' => '283657896',
        'dest_cnpj' => '13305697000150', 'dest_nome' => 'CLIENTE FINAL LTDA',
        'dest_uf' => 'SP', 'dest_municipio' => 'SAO PAULO',
        'consultado_em' => now(),
    ]);
}

it('classificar lado emit cria cliente com dados da SEFAZ, reassocia snapshot e remove participante auto-criado', function () {
    $user = User::factory()->create();
    $snap = cpSnapshot($user);

    // Simula o auto-cadastro que o job fez pros dois lados
    foreach ([['97551165000193', 'HIDRATOP COMERCIO'], ['13305697000150', 'CLIENTE FINAL LTDA']] as [$doc, $nome]) {
        Participante::create([
            'user_id' => $user->id, 'documento' => $doc, 'tipo_documento' => 'PJ',
            'razao_social' => $nome, 'origem_tipo' => 'NFE',
            'origem_ref' => ['fonte' => 'clearance_snapshot', 'chave_acesso' => cpChave()],
        ]);
    }

    $resp = actingAs($user)->postJson('/app/clearance/buscar/classificar-partes', [
        'chave_acesso' => cpChave(), 'lado' => 'emit',
    ])->assertOk()->assertJsonPath('success', true);

    $cliente = Cliente::where('user_id', $user->id)->where('documento', '97551165000193')->first();
    expect($cliente)->not->toBeNull()
        ->and($cliente->razao_social)->toBe('HIDRATOP COMERCIO')
        ->and($cliente->uf)->toBe('MS')
        ->and($cliente->inscricao_estadual)->toBe('283657896')
        ->and($resp->json('cliente_id'))->toBe($cliente->id);

    // Snapshot reassociado
    expect($snap->fresh()->cliente_id)->toBe($cliente->id);

    // Participante do CNPJ que virou cliente foi removido; o outro lado permanece
    expect(Participante::where('user_id', $user->id)->where('documento', '97551165000193')->exists())->toBeFalse()
        ->and(Participante::where('user_id', $user->id)->where('documento', '13305697000150')->exists())->toBeTrue();
});

it('não remove participante manual com o mesmo CNPJ (só o auto-criado pelo clearance)', function () {
    $user = User::factory()->create();
    cpSnapshot($user);
    Participante::create([
        'user_id' => $user->id, 'documento' => '97551165000193', 'tipo_documento' => 'PJ',
        'razao_social' => 'MANUAL', 'origem_tipo' => 'MANUAL',
    ]);

    actingAs($user)->postJson('/app/clearance/buscar/classificar-partes', [
        'chave_acesso' => cpChave(), 'lado' => 'emit',
    ])->assertOk();

    expect(Participante::where('user_id', $user->id)->where('documento', '97551165000193')->exists())->toBeTrue();
});

it('reusa cliente existente com o CNPJ em vez de duplicar', function () {
    $user = User::factory()->create();
    cpSnapshot($user);
    $existente = Cliente::create([
        'user_id' => $user->id, 'tipo_pessoa' => 'PJ',
        'documento' => '97551165000193', 'razao_social' => 'JA EXISTIA',
    ]);

    actingAs($user)->postJson('/app/clearance/buscar/classificar-partes', [
        'chave_acesso' => cpChave(), 'lado' => 'emit',
    ])->assertOk()->assertJsonPath('cliente_id', $existente->id);

    expect(Cliente::where('user_id', $user->id)->where('documento', '97551165000193')->count())->toBe(1);
});

it('404 para chave sem snapshot e 422 para lado sem CNPJ', function () {
    $user = User::factory()->create();

    actingAs($user)->postJson('/app/clearance/buscar/classificar-partes', [
        'chave_acesso' => cpChave(), 'lado' => 'emit',
    ])->assertNotFound();

    $snap = cpSnapshot($user);
    $snap->update(['dest_cnpj' => null]);

    actingAs($user)->postJson('/app/clearance/buscar/classificar-partes', [
        'chave_acesso' => cpChave(), 'lado' => 'dest',
    ])->assertStatus(422);
});

it('não vaza snapshot de outro usuário', function () {
    $dono = User::factory()->create();
    $outro = User::factory()->create();
    cpSnapshot($dono);

    actingAs($outro)->postJson('/app/clearance/buscar/classificar-partes', [
        'chave_acesso' => cpChave(), 'lado' => 'emit',
    ])->assertNotFound();
});

it('lado mascarado identificado no acervo vira cliente com o CNPJ real do participante', function () {
    $user = User::factory()->create();
    cpSnapshot($user)->update(['dest_cnpj' => '00000958000105', 'dest_nome' => 'RAIZ***']);
    Participante::create([
        'user_id' => $user->id, 'documento' => '09538958000105', 'tipo_documento' => 'PJ',
        'razao_social' => 'RAIZEN AGRICOLA CAARAPO LTDA', 'origem_tipo' => 'EFD',
    ]);

    actingAs($user)->postJson('/app/clearance/buscar/classificar-partes', [
        'chave_acesso' => cpChave(), 'lado' => 'dest',
    ])->assertOk()->assertJsonPath('success', true);

    // Cliente criado com o CNPJ REAL, não com o mascarado
    expect(Cliente::where('user_id', $user->id)->where('documento', '09538958000105')->exists())->toBeTrue()
        ->and(Cliente::where('user_id', $user->id)->where('documento', '00000958000105')->exists())->toBeFalse();
});

it('lado mascarado NÃO identificado no acervo retorna 422 e não cria cliente lixo', function () {
    $user = User::factory()->create();
    cpSnapshot($user)->update(['dest_cnpj' => '00000958000105', 'dest_nome' => 'RAIZ***']);

    actingAs($user)->postJson('/app/clearance/buscar/classificar-partes', [
        'chave_acesso' => cpChave(), 'lado' => 'dest',
    ])->assertStatus(422);

    expect(Cliente::where('user_id', $user->id)->where('documento', '00000958000105')->exists())->toBeFalse();
});

it('resultado exibe dados reais do participante identificado e trava o lado mascarado sem match', function () {
    $user = User::factory()->create();
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 14, 'tab_id' => 'tab-cpm', 'processado_em' => now(),
    ]);
    cpSnapshot($user)->update([
        'consulta_lote_id' => $lote->id,
        'dest_cnpj' => '00000958000105', 'dest_nome' => 'RAIZ***',
    ]);
    Participante::create([
        'user_id' => $user->id, 'documento' => '09538958000105', 'tipo_documento' => 'PJ',
        'razao_social' => 'RAIZEN AGRICOLA CAARAPO LTDA', 'origem_tipo' => 'EFD',
    ]);

    // Identificado: mostra razão social + CNPJ reais e badge do acervo
    actingAs($user)->get('/app/clearance/buscar/resultado/'.$lote->id.'?tipo_documento=nfe')
        ->assertOk()
        ->assertSee('RAIZEN AGRICOLA CAARAPO LTDA')
        ->assertSee('09.538.958/0001-05')
        ->assertSee('Identificado no seu acervo');

    // Sem match no acervo: lado mascarado fica inelegível pra virar cliente
    Participante::where('user_id', $user->id)->delete();

    actingAs($user)->get('/app/clearance/buscar/resultado/'.$lote->id.'?tipo_documento=nfe')
        ->assertOk()
        ->assertSee('CNPJ mascarado pela SEFAZ');
});

it('bloco de classificação some quando o lado mascarado já é cliente do usuário', function () {
    $user = User::factory()->create();
    Cliente::create([
        'user_id' => $user->id, 'tipo_pessoa' => 'PJ',
        'documento' => '09538958000105', 'razao_social' => 'RAIZEN AGRICOLA CAARAPO LTDA',
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 14, 'tab_id' => 'tab-cpm2', 'processado_em' => now(),
    ]);
    cpSnapshot($user)->update([
        'consulta_lote_id' => $lote->id,
        'dest_cnpj' => '00000958000105', 'dest_nome' => 'RAIZ***',
    ]);

    actingAs($user)->get('/app/clearance/buscar/resultado/'.$lote->id.'?tipo_documento=nfe')
        ->assertOk()
        ->assertDontSee('Organize sua carteira');
});

it('consulta completa (certificado A1): dados sem máscara não passam pelo resolver — fluxo normal', function () {
    $user = User::factory()->create();
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 14, 'tab_id' => 'tab-cpa1', 'processado_em' => now(),
    ]);
    // Snapshot como virá com A1: consulta_sem_certificado=false, dest completo (DV válido)
    cpSnapshot($user)->update([
        'consulta_lote_id' => $lote->id,
        'consulta_sem_certificado' => false,
        'dest_cnpj' => '09538958000105', 'dest_nome' => 'RAIZEN AGRICOLA CAARAPO LTDA',
    ]);
    // Mesmo com participante homônimo no acervo, nada é "identificado" — o dado já é completo
    Participante::create([
        'user_id' => $user->id, 'documento' => '09538958000105', 'tipo_documento' => 'PJ',
        'razao_social' => 'RAIZEN AGRICOLA CAARAPO LTDA', 'origem_tipo' => 'EFD',
    ]);

    actingAs($user)->get('/app/clearance/buscar/resultado/'.$lote->id.'?tipo_documento=nfe')
        ->assertOk()
        ->assertSee('RAIZEN AGRICOLA CAARAPO LTDA')
        ->assertDontSee('Identificado no seu acervo')
        ->assertDontSee('CNPJ mascarado pela SEFAZ')
        // Bloco de classificação normal, com os dois lados selecionáveis
        ->assertSee('Organize sua carteira')
        ->assertSee('Este é meu cliente');

    // E o lado dest pode virar cliente com o próprio CNPJ da SEFAZ
    actingAs($user)->postJson('/app/clearance/buscar/classificar-partes', [
        'chave_acesso' => cpChave(), 'lado' => 'dest',
    ])->assertOk();

    expect(Cliente::where('user_id', $user->id)->where('documento', '09538958000105')->exists())->toBeTrue();
});

it('resultado da busca exibe o bloco de classificação quando nenhum CNPJ é cliente', function () {
    $user = User::factory()->create();
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 14, 'tab_id' => 'tab-cp', 'processado_em' => now(),
    ]);
    cpSnapshot($user)->update(['consulta_lote_id' => $lote->id]);

    actingAs($user)->get('/app/clearance/buscar/resultado/'.$lote->id.'?tipo_documento=nfe')
        ->assertOk()
        ->assertSee('Organize sua carteira')
        ->assertSee('Este é meu cliente')
        ->assertSee('97.551.165/0001-93');
});

it('resultado NÃO exibe o bloco quando um dos CNPJs já é cliente', function () {
    $user = User::factory()->create();
    Cliente::create([
        'user_id' => $user->id, 'tipo_pessoa' => 'PJ',
        'documento' => '97551165000193', 'razao_social' => 'MEU CLIENTE',
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 14, 'tab_id' => 'tab-cp2', 'processado_em' => now(),
    ]);
    cpSnapshot($user)->update(['consulta_lote_id' => $lote->id]);

    actingAs($user)->get('/app/clearance/buscar/resultado/'.$lote->id.'?tipo_documento=nfe')
        ->assertOk()
        ->assertDontSee('Organize sua carteira');
});
