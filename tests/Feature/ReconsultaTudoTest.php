<?php

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function rtPlano(string $codigo = 'licitacao'): MonitoramentoPlano
{
    return MonitoramentoPlano::porCodigo($codigo) ?? MonitoramentoPlano::firstOrFail();
}

function rtLote(User $user, array $overrides = []): ConsultaLote
{
    return ConsultaLote::create(array_merge([
        'user_id' => $user->id,
        'plano_id' => rtPlano()->id,
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'tab-rt-1',
    ], $overrides));
}

function rtParticipante(User $user, string $doc = '11222333000181'): Participante
{
    return Participante::create([
        'user_id' => $user->id,
        'documento' => $doc,
        'razao_social' => 'Alvo RT',
        'uf' => 'SP',
        'crt' => '3',
    ]);
}

function rtResultado(ConsultaLote $lote, Participante $p): ConsultaResultado
{
    return ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $p->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'situacao_cadastral' => 'ATIVA',
            'cnd_federal' => ['status' => 'Negativa'],
        ],
        'consultado_em' => now(),
    ]);
}

it('lote concluído expõe botão/modal de reconsulta total com planos ativos', function () {
    $user = User::factory()->create();
    $lote = rtLote($user);
    $p = rtParticipante($user);
    rtResultado($lote, $p);
    $lote->participantes()->attach([$p->id]);

    $resp = actingAs($user)->get(route('app.consulta.lote.show', ['id' => $lote->id]));

    $resp->assertOk();
    $resp->assertSee('modal-reconsulta-'.$lote->id, false);
    $resp->assertSee('Consultar novamente', false);
    // seletor traz planos ativos com value=id e o plano original selecionado
    $resp->assertSee('value="'.$lote->plano_id.'" selected', false);
});

it('lote processando NÃO expõe reconsulta total', function () {
    $user = User::factory()->create();
    $lote = rtLote($user, ['status' => ConsultaLote::STATUS_PROCESSANDO]);
    $p = rtParticipante($user);
    rtResultado($lote, $p);
    $lote->participantes()->attach([$p->id]);

    $resp = actingAs($user)->get(route('app.consulta.lote.show', ['id' => $lote->id]));

    $resp->assertOk();
    $resp->assertDontSee('modal-reconsulta-'.$lote->id, false);
});

it('alvo deletado após o lote fica fora da reconsulta total', function () {
    $user = User::factory()->create();
    $lote = rtLote($user, ['total_participantes' => 2]);
    $p1 = rtParticipante($user, '11222333000181');
    $p2 = rtParticipante($user, '11444777000161');
    rtResultado($lote, $p1);
    rtResultado($lote, $p2);
    $lote->participantes()->attach([$p1->id, $p2->id]);

    $p2->delete();

    $resp = actingAs($user)->get(route('app.consulta.lote.show', ['id' => $lote->id]));

    $resp->assertOk();
    // JSON serializado no modal só contém o alvo vivo
    $resp->assertSee('"participante_ids":['.$p1->id.']', false);
});

it('POST executar com os alvos do lote cria lote NOVO e preserva o original', function () {
    \Illuminate\Support\Facades\Bus::fake();

    $user = User::factory()->create();
    $plano = rtPlano('gratuito'); // sem débito de créditos no teste
    $lote = rtLote($user, ['plano_id' => $plano->id]);
    $p = rtParticipante($user);
    rtResultado($lote, $p);
    $lote->participantes()->attach([$p->id]);

    $resp = actingAs($user)->postJson(route('app.consulta.nova.executar'), [
        'participante_ids' => [$p->id],
        'cliente_ids' => [],
        'plano_id' => $plano->id,
        'tab_id' => (string) \Illuminate\Support\Str::uuid(),
    ]);

    $resp->assertOk()->assertJson(['success' => true]);

    $novoId = $resp->json('consulta_lote_id');
    expect($novoId)->not->toBe($lote->id);
    expect(ConsultaLote::find($lote->id)->status)->toBe(ConsultaLote::STATUS_CONCLUIDO);
    expect(ConsultaLote::find($novoId)->plano_id)->toBe($plano->id);
});
