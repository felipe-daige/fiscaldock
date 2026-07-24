<?php

use App\Models\ConsultaLote;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
    // Fonte sensível habilitada + PF liberada para o teste exercitar o guard.
    config()->set('advocacia.fontes_sensiveis', ['mandado_prisao' => true]);
    config()->set('advocacia.fontes_publicas_liberadas', ['mandado_prisao']);
});

function userComCpfPf(): array
{
    $user = User::factory()->create(['credits' => 10.0]);
    $pid = DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'documento' => '52998224725', 'tipo_documento' => 'PF',
        'razao_social' => 'FULANO', 'uf' => 'SP', 'created_at' => now(), 'updated_at' => now(),
    ]);

    return [$user, $pid];
}

it('bloqueia consulta sensivel sem declaracao de finalidade (422) e nao debita', function () {
    Bus::fake();
    [$user, $pid] = userComCpfPf();

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['mandado_prisao'],
        'tab_id' => 'tab-sensivel',
    ])->assertStatus(422)
        ->assertJson(['requer_finalidade_sensivel' => true]);

    expect((float) $user->fresh()->credits)->toBe(10.0);
    Bus::assertNothingBatched();
});

it('bloqueia finalidade curta demais', function () {
    Bus::fake();
    [$user, $pid] = userComCpfPf();

    $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['mandado_prisao'],
        'finalidade_sensivel' => 'curto',
        'tab_id' => 'tab-sensivel',
    ])->assertStatus(422)->assertJson(['requer_finalidade_sensivel' => true]);
});

it('permite consulta sensivel com finalidade e persiste a trilha de auditoria', function () {
    Bus::fake();
    [$user, $pid] = userComCpfPf();

    $resp = $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['mandado_prisao'],
        'finalidade_sensivel' => 'Investigação de parte adversa no processo 0001234-55.2026.8.26.0100',
        'tab_id' => 'tab-sensivel',
    ])->assertOk();

    $lote = ConsultaLote::findOrFail($resp->json('consulta_lote_id'));
    expect($lote->sensivel_finalidade)->toContain('0001234-55')
        ->and($lote->sensivel_base_legal)->toContain('art. 11')
        ->and($lote->sensivel_declarado_em)->not->toBeNull();
});

it('nao exige finalidade quando a selecao nao tem fonte sensivel', function () {
    Bus::fake();
    Http::fake();
    [$user, $pid] = userComCpfPf();
    config()->set('advocacia.fontes_publicas_liberadas', ['cadastro_pf']);

    $resp = $this->actingAs($user)->postJson('/app/consulta/nova/fontes/executar', [
        'participante_ids' => [$pid],
        'fontes' => ['cadastro_pf'],
        'dados_pf' => [['tipo' => 'participante', 'id' => $pid, 'birthdate' => '1980-05-10']],
        'tab_id' => 'tab-normal',
    ])->assertOk();

    $lote = ConsultaLote::findOrFail($resp->json('consulta_lote_id'));
    expect($lote->sensivel_finalidade)->toBeNull()
        ->and($lote->sensivel_declarado_em)->toBeNull();
});
