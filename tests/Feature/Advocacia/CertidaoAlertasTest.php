<?php

use App\Models\Alerta;
use App\Models\Certidao;
use App\Models\User;
use App\Services\AlertaCentralService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

function certidaoJudicial(User $user, int $pid, string $tipo, int $diasParaVencer): Certidao
{
    return Certidao::create([
        'user_id' => $user->id, 'participante_id' => $pid, 'alvo_tipo' => 'participante',
        'alvo_documento' => '19131243000197', 'tipo' => $tipo, 'status' => 'Negativa',
        'orgao' => 'Superior Tribunal de Justiça (STJ)',
        'emitida_em' => now()->subDays(80), 'valida_ate' => now()->addDays($diasParaVencer)->startOfDay(),
        'validade_origem' => 'resposta',
    ]);
}

function participanteDe(User $user): int
{
    return DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'documento' => '19131243000197', 'razao_social' => 'ALVO SA',
        'uf' => 'SP', 'created_at' => now(), 'updated_at' => now(),
    ]);
}

it('certidao judicial vencendo em 10 dias gera alerta certidao_vencendo com reemitir_url (faixa 15)', function () {
    Notification::fake();
    $user = User::factory()->create();
    $pid = participanteDe($user);
    certidaoJudicial($user, $pid, 'certidao_stj', 10);

    app(AlertaCentralService::class)->recalcular($user->id);

    $alerta = Alerta::where('user_id', $user->id)->where('tipo', 'certidao_vencendo')->first();
    expect($alerta)->not->toBeNull()
        ->and($alerta->severidade)->toBe('media')
        ->and($alerta->vence_em->format('Y-m-d'))->toBe(now()->addDays(10)->format('Y-m-d'))
        ->and($alerta->titulo)->toContain('Certidão')
        ->and($alerta->titulo)->toContain('ALVO SA')
        ->and($alerta->detalhes['reemitir_url'])->toContain('/app/consulta/fontes')
        ->and($alerta->detalhes['reemitir_url'])->toContain('fonte=certidao_stj')
        ->and($alerta->detalhes['reemitir_url'])->toContain('documento=19131243000197')
        ->and($alerta->detalhes['certidoes'][0]['label'])->toBe('Certidão STJ');
});

it('cruzar de faixa cria alerta novo (novo e-mail) e resolve o da faixa anterior', function () {
    Notification::fake();
    $user = User::factory()->create();
    $pid = participanteDe($user);
    $certidao = certidaoJudicial($user, $pid, 'certidao_stj', 12);

    $service = app(AlertaCentralService::class);
    $service->recalcular($user->id);
    $alertaFaixa15 = Alerta::where('user_id', $user->id)->where('tipo', 'certidao_vencendo')->where('status', 'ativo')->first();
    expect($alertaFaixa15->severidade)->toBe('media');

    // Certidão agora a 5 dias do vencimento: entra na faixa 7 (alta).
    $certidao->update(['valida_ate' => now()->addDays(5)->startOfDay()]);
    $service->recalcular($user->id);

    $ativos = Alerta::where('user_id', $user->id)->where('tipo', 'certidao_vencendo')->where('status', 'ativo')->get();
    expect($ativos)->toHaveCount(1)
        ->and($ativos->first()->id)->not->toBe($alertaFaixa15->id)
        ->and($ativos->first()->severidade)->toBe('alta')
        ->and($alertaFaixa15->fresh()->status)->toBe('resolvido');
});

it('certidao renovada sai da janela e o alerta auto-resolve', function () {
    Notification::fake();
    $user = User::factory()->create();
    $pid = participanteDe($user);
    $certidao = certidaoJudicial($user, $pid, 'certidao_stj', 3);

    $service = app(AlertaCentralService::class);
    $service->recalcular($user->id);
    expect(Alerta::where('user_id', $user->id)->where('tipo', 'certidao_vencendo')->where('status', 'ativo')->count())->toBe(1);

    // Re-emissão: validade nova bem fora da janela.
    $certidao->update(['valida_ate' => now()->addDays(90)->startOfDay()]);
    $service->recalcular($user->id);

    expect(Alerta::where('user_id', $user->id)->where('tipo', 'certidao_vencendo')->where('status', 'ativo')->count())->toBe(0);
});

it('tipos fiscais do CERTIDOES_MAP ficam FORA do detector do registro (sem alerta duplicado)', function () {
    Notification::fake();
    $user = User::factory()->create();
    $pid = participanteDe($user);
    certidaoJudicial($user, $pid, 'cnd_federal', 5); // fiscal: coberta pelo 3c via participante_scores

    app(AlertaCentralService::class)->recalcular($user->id);

    expect(Alerta::where('user_id', $user->id)->where('tipo', 'certidao_vencendo')->count())->toBe(0);
});

it('certidao vencida entra na faixa vencida com severidade alta', function () {
    Notification::fake();
    $user = User::factory()->create();
    $pid = participanteDe($user);
    certidaoJudicial($user, $pid, 'ceat_trt', -3);

    app(AlertaCentralService::class)->recalcular($user->id);

    $alerta = Alerta::where('user_id', $user->id)->where('tipo', 'certidao_vencendo')->first();
    expect($alerta->severidade)->toBe('alta')
        ->and($alerta->titulo)->toContain('venceu em')
        ->and($alerta->detalhes['certidoes'][0]['vencida'])->toBeTrue();
});
