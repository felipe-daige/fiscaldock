<?php

use App\Actions\Monitoramento\DispararConsultaMonitoramento;
use App\Jobs\ProcessarConsultaJob;
use App\Models\ConsultaLote;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\ParticipanteGrupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

function cgPlano(): MonitoramentoPlano
{
    return MonitoramentoPlano::porCodigo('licitacao') ?? MonitoramentoPlano::firstOrFail();
}

function cgAssinaturaGrupo(User $user, int $membros): MonitoramentoAssinatura
{
    $grupo = ParticipanteGrupo::create(['user_id' => $user->id, 'nome' => 'G1']);
    foreach (range(1, $membros) as $i) {
        $p = Participante::create([
            'user_id' => $user->id,
            'documento' => sprintf('112223330%03d81', $i),
            'razao_social' => "M{$i}",
            'uf' => 'SP',
        ]);
        $grupo->participantes()->attach($p->id);
    }

    return MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'grupo_id' => $grupo->id,
        'plano_id' => cgPlano()->id, 'status' => 'ativo', 'frequencia_dias' => 30,
    ]);
}

it('ciclo de grupo cria lote com N membros atuais e N jobs, deduct único N×plano', function () {
    Bus::fake();
    $user = User::factory()->create();
    app(\App\Services\CreditService::class)->add($user, 1000, type: 'manual_add', description: 'seed teste');
    $ass = cgAssinaturaGrupo($user, 2);

    $consulta = app(DispararConsultaMonitoramento::class)->execute($ass);

    $lote = ConsultaLote::find($consulta->consulta_lote_id);
    expect($lote)->not->toBeNull();
    expect((int) $lote->total_participantes)->toBe(2);
    expect($lote->participantes()->count())->toBe(2);
    expect((int) $consulta->creditos_cobrados)->toBe(2 * (int) cgPlano()->custo_creditos);
    expect((int) $consulta->grupo_id)->toBe((int) $ass->grupo_id);

    Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 2
        && $batch->jobs->every(fn ($j) => $j instanceof ProcessarConsultaJob));
});

it('grupo vazio: não cobra, não cria lote, marca ciclo concluído', function () {
    Bus::fake();
    $user = User::factory()->create();
    $grupo = ParticipanteGrupo::create(['user_id' => $user->id, 'nome' => 'Vazio']);
    $ass = MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'grupo_id' => $grupo->id,
        'plano_id' => cgPlano()->id, 'status' => 'ativo', 'frequencia_dias' => 30,
    ]);

    $consulta = app(DispararConsultaMonitoramento::class)->execute($ass);

    expect($consulta->consulta_lote_id)->toBeNull();
    expect((int) $consulta->creditos_cobrados)->toBe(0);
    expect($consulta->status)->toBe('sucesso');
    Bus::assertNothingBatched();
});
