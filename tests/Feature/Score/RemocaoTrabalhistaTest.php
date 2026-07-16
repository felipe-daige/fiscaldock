<?php

use App\Models\User;
use App\Services\RiskScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('score model não tem mais a categoria trabalhista (pesos, labels, calcularScores, detalhar)', function () {
    $svc = app(RiskScoreService::class);

    expect(RiskScoreService::categoriaLabels())->not->toHaveKey('trabalhista');

    // dados com cndt presente → NÃO deve virar subscore
    $scores = $svc->calcularScores(['cndt' => ['status' => 'IRREGULAR'], 'cnd_federal' => ['status' => 'REGULAR']]);
    expect($scores)->not->toHaveKey('trabalhista');

    // detalhar() não emite a linha trabalhista
    $det = $svc->detalhar($scores);
    expect($det)->not->toHaveKey('trabalhista');
});

it('calcularScoreTotal renormaliza sobre as 5 categorias (sem trabalhista)', function () {
    $svc = app(RiskScoreService::class);
    // só cadastral avaliada = 20 → total = 20 (renormaliza sobre a única avaliada)
    expect($svc->calcularScoreTotal(['cadastral' => 20]))->toBe(20);
    // duas iguais → média ponderada = 40 (pesos renormalizam entre elas)
    expect($svc->calcularScoreTotal(['cadastral' => 40, 'cnd_federal' => 40]))->toBe(40);
});

it('persiste score_trabalhista = null e o total renormalizado (backfill via atualizarScore)', function () {
    $user = User::factory()->create();
    $part = DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'P', 'documento' => '11111111000111',
        'origem_tipo' => 'MANUAL', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $p = App\Models\Participante::find($part);
    $svc = app(RiskScoreService::class);
    $svc->atualizarScore($p, ['cndt' => ['status' => 'IRREGULAR'], 'cnd_federal' => ['status' => 'REGULAR']]);

    $row = DB::table('participante_scores')->where('participante_id', $part)->first();
    expect($row->score_trabalhista)->toBeNull()
        ->and($row->score_total)->not->toBeNull();
});
