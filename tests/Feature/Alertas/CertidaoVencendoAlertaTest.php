<?php

use App\Models\Alerta;
use App\Models\ParticipanteScore;
use App\Models\User;
use App\Services\AlertaCentralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * Alerta `certidao_vencendo`: certidão REGULAR cuja data_validade já venceu ou vence
 * em ≤30 dias. Avisa antes de virar problema (renovar a tempo). Popula vence_em.
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->cliente = DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'MINHA EMPRESA', 'documento' => '00000000000100',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $this->svc = app(AlertaCentralService::class);

    $this->mkParticipante = fn (string $razao, string $doc) => DB::table('participantes')->insertGetId([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente, 'razao_social' => $razao, 'documento' => $doc,
        'origem_tipo' => 'MANUAL', 'created_at' => now(), 'updated_at' => now(),
    ]);

    // Score com certidão FGTS regular (subscore 0) e a validade que o teste definir.
    $this->scoreComValidade = function (int $pid, string $validade) {
        ParticipanteScore::create([
            'user_id' => $this->user->id,
            'participante_id' => $pid,
            'score_cnd_federal' => 0,
            'score_fgts' => 0,
            'dados_consultados' => ['crf_fgts' => ['status' => 'REGULAR', 'data_validade' => $validade]],
        ]);
    };
});

it('cria alerta quando a certidão regular vence em ≤30 dias, com vence_em', function () {
    $pid = ($this->mkParticipante)('FORNECEDOR A', '11111111000111');
    $venc = now()->addDays(10);
    ($this->scoreComValidade)($pid, $venc->format('d/m/Y'));

    $this->svc->recalcular($this->user->id);

    $alerta = Alerta::where('tipo', 'certidao_vencendo')->where('participante_id', $pid)->first();
    expect($alerta)->not->toBeNull()
        ->and($alerta->severidade)->toBe('media')      // 10 dias > 7 → média
        ->and($alerta->vence_em->toDateString())->toBe($venc->toDateString())
        ->and($alerta->cliente_id)->toBe($this->cliente)
        ->and($alerta->descricao)->toContain('FGTS');
});

it('severidade alta quando vence em ≤7 dias', function () {
    $pid = ($this->mkParticipante)('FORNECEDOR B', '22222222000122');
    ($this->scoreComValidade)($pid, now()->addDays(3)->format('d/m/Y'));

    $this->svc->recalcular($this->user->id);

    expect(Alerta::where('tipo', 'certidao_vencendo')->where('participante_id', $pid)->value('severidade'))->toBe('alta');
});

it('certidão já vencida gera alerta alta', function () {
    $pid = ($this->mkParticipante)('FORNECEDOR C', '33333333000133');
    ($this->scoreComValidade)($pid, now()->subDays(5)->format('d/m/Y'));

    $this->svc->recalcular($this->user->id);

    $alerta = Alerta::where('tipo', 'certidao_vencendo')->where('participante_id', $pid)->first();
    expect($alerta->severidade)->toBe('alta')
        ->and($alerta->descricao)->toContain('venceu');
});

it('NÃO cria alerta quando a certidão vence além de 30 dias', function () {
    $pid = ($this->mkParticipante)('FORNECEDOR D', '44444444000144');
    ($this->scoreComValidade)($pid, now()->addDays(90)->format('d/m/Y'));

    $this->svc->recalcular($this->user->id);

    expect(Alerta::where('tipo', 'certidao_vencendo')->where('participante_id', $pid)->count())->toBe(0);
});

it('certidão POSITIVA não vira certidao_vencendo (é certidao_positiva)', function () {
    $pid = ($this->mkParticipante)('FORNECEDOR E', '55555555000155');
    // FGTS positiva (subscore > 0) mesmo com validade próxima → não é "vencendo".
    ParticipanteScore::create([
        'user_id' => $this->user->id,
        'participante_id' => $pid,
        'score_fgts' => 50,
        'dados_consultados' => ['crf_fgts' => ['status' => 'IRREGULAR', 'data_validade' => now()->addDays(5)->format('d/m/Y')]],
    ]);

    $this->svc->recalcular($this->user->id);

    expect(Alerta::where('tipo', 'certidao_vencendo')->where('participante_id', $pid)->count())->toBe(0);
});

it('resolve o alerta quando a certidão é renovada (validade longe) no recalcular', function () {
    $pid = ($this->mkParticipante)('FORNECEDOR F', '66666666000166');
    $score = ParticipanteScore::create([
        'user_id' => $this->user->id, 'participante_id' => $pid, 'score_fgts' => 0,
        'dados_consultados' => ['crf_fgts' => ['status' => 'REGULAR', 'data_validade' => now()->addDays(5)->format('d/m/Y')]],
    ]);

    $this->svc->recalcular($this->user->id);
    expect(Alerta::where('tipo', 'certidao_vencendo')->where('status', 'ativo')->count())->toBe(1);

    // Renovada: nova validade daqui a 6 meses → detector não acha → auto-resolve.
    $score->update(['dados_consultados' => ['crf_fgts' => ['status' => 'REGULAR', 'data_validade' => now()->addMonths(6)->format('d/m/Y')]]]);
    $this->svc->recalcular($this->user->id);

    expect(Alerta::where('tipo', 'certidao_vencendo')->first()->status)->toBe('resolvido');
});
