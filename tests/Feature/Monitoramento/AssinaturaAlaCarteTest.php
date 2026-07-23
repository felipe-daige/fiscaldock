<?php

use App\Actions\Monitoramento\DispararConsultaMonitoramento;
use App\Models\ConsultaLote;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

/**
 * F5 (migração por-fonte, backward-compat): monitoramento à la carte convive com a escada
 * legada. Assinatura à la carte guarda `fontes` (plano_id null); custo/etapas/estorno vêm do
 * catálogo. `analise_fiscal` (R$1, disponível no test env) é a fonte paga usada aqui.
 */
function participanteDe(User $user, string $doc = '11222333000181'): Participante
{
    return Participante::create([
        'user_id' => $user->id, 'documento' => $doc, 'uf' => 'SP', 'razao_social' => 'ACME',
    ]);
}

it('custoCiclo à la carte lê o total precificado do catálogo, não o plano', function () {
    $user = User::factory()->create();
    $p = participanteDe($user);
    $a = MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => $p->id, 'plano_id' => null,
        'fontes' => ['analise_fiscal'], 'status' => 'ativo', 'frequencia_dias' => 30,
        'proxima_execucao_em' => now(),
    ]);

    expect($a->usaAlaCarte())->toBeTrue()
        ->and($a->custoCiclo())->toBe(1.0);
});

it('disparo à la carte cria lote SEM plano e COM fontes_selecionadas, cobra o total do catálogo', function () {
    Bus::fake();
    $user = User::factory()->create(['credits' => 100]);
    $p = participanteDe($user);
    $a = MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => $p->id, 'plano_id' => null,
        'fontes' => ['analise_fiscal'], 'status' => 'ativo', 'frequencia_dias' => 30,
        'proxima_execucao_em' => now(),
    ]);

    $consulta = app(DispararConsultaMonitoramento::class)->execute($a);

    expect($consulta->status)->toBe('pendente')
        ->and($consulta->consulta_lote_id)->not->toBeNull();

    $lote = ConsultaLote::find($consulta->consulta_lote_id);
    expect($lote->plano_id)->toBeNull()
        ->and($lote->fontes_selecionadas)->toBe(['analise_fiscal'])
        ->and((float) $lote->creditos_cobrados)->toBe(1.0);
    Bus::assertBatchCount(1);
});

it('disparo legado (plano) segue com plano_id no lote e fontes null — backward-compat', function () {
    Bus::fake();
    $user = User::factory()->create(['credits' => 100]);
    $plano = MonitoramentoPlano::porCodigo('licitacao');
    $p = participanteDe($user);
    $a = MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => $p->id, 'plano_id' => $plano->id,
        'status' => 'ativo', 'frequencia_dias' => 30, 'proxima_execucao_em' => now(),
    ]);

    $consulta = app(DispararConsultaMonitoramento::class)->execute($a);

    $lote = ConsultaLote::find($consulta->consulta_lote_id);
    expect($lote->plano_id)->toBe($plano->id)
        ->and($lote->fontes_selecionadas)->toBeNull();
    Bus::assertBatchCount(1);
});

it('store aceita fontes[] e cria assinatura à la carte (plano_id null)', function () {
    $user = User::factory()->trialAtivo()->create();
    $p = participanteDe($user);

    actingAs($user)->post(route('app.monitoramento.assinatura.criar'), [
        'participante_id' => $p->id, 'fontes' => ['analise_fiscal'], 'frequencia' => 'mensal',
    ])->assertSuccessful();

    assertDatabaseHas('monitoramento_assinaturas', [
        'participante_id' => $p->id, 'user_id' => $user->id, 'plano_id' => null,
    ]);
    $a = MonitoramentoAssinatura::where('participante_id', $p->id)->first();
    expect($a->fontes)->toBe(['analise_fiscal']);
});

it('store rejeita seleção à la carte só-grátis (422) — não vira monitor gratuito sem teto', function () {
    $user = User::factory()->trialAtivo()->create();
    $p = participanteDe($user);

    actingAs($user)->post(route('app.monitoramento.assinatura.criar'), [
        'participante_id' => $p->id, 'fontes' => ['cadastro'], 'frequencia' => 'mensal',
    ])->assertStatus(422);

    expect(MonitoramentoAssinatura::where('participante_id', $p->id)->exists())->toBeFalse();
});
