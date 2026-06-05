<?php

use App\Models\ConsultaLote;
use App\Models\User;
use App\Services\Consultas\FecharLoteService;
use App\Services\CreditService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('fecha o lote como concluido e grava resumo', function () {
    [$loteId] = montarLoteParticipante();

    app(FecharLoteService::class)->fechar($loteId, creditosFalhos: 0, resumo: ['ok' => 1]);

    $lote = ConsultaLote::find($loteId);
    expect($lote->status)->toBe('concluido');
    expect($lote->resultado_resumo['ok'])->toBe(1);
    expect($lote->processado_em)->not->toBeNull();
});

it('estorna exatamente os créditos das fontes que falharam', function () {
    [$loteId, , $userId] = montarLoteParticipante();
    $user = User::find($userId);
    $saldoAntes = app(CreditService::class)->getBalance($user);

    app(FecharLoteService::class)->fechar($loteId, creditosFalhos: 4, resumo: []);

    expect(app(CreditService::class)->getBalance($user->refresh()))->toBe($saldoAntes + 4);
});
