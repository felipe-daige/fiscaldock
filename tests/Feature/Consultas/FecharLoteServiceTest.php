<?php

use App\Models\ConsultaLote;
use App\Models\User;
use App\Services\Consultas\Dto\ResultadoFonte;
use App\Services\Consultas\FecharLoteService;
use App\Services\Consultas\Persistencia\PersistenciaCnpj;
use App\Services\SaldoService;
use Illuminate\Support\Facades\Cache;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('fecha o lote como concluido e grava resumo', function () {
    [$loteId] = montarLoteParticipante();

    app(FecharLoteService::class)->fechar($loteId, resumo: ['ok' => 1]);

    $lote = ConsultaLote::find($loteId);
    expect($lote->status)->toBe('concluido');
    expect($lote->resultado_resumo['ok'])->toBe(1);
    expect($lote->processado_em)->not->toBeNull();
});

it('estorna a soma do estorno por alvo (cache) das fontes que falharam', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    // precisa existir uma linha consulta_resultados p/ o alvo ser somado
    app(PersistenciaCnpj::class)->gravar($loteId, 'participante', $participanteId, new ResultadoFonte('cnd_federal', [], 'fatal', 4, 'erro'));
    Cache::put("consulta_estorno:{$loteId}:participante:{$participanteId}", 4, 600);

    $user = User::find($userId);
    $saldoAntes = app(SaldoService::class)->getBalance($user);

    app(FecharLoteService::class)->fechar($loteId, resumo: []);

    expect(app(SaldoService::class)->getBalance($user->refresh()))->toBe($saldoAntes + 4);
});

it('não estorna quando não houve falha estornável', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    app(PersistenciaCnpj::class)->gravar($loteId, 'participante', $participanteId, new ResultadoFonte('cadastro', ['razao_social' => 'X'], 'sucesso', 0));

    $user = User::find($userId);
    $saldoAntes = app(SaldoService::class)->getBalance($user);

    app(FecharLoteService::class)->fechar($loteId, resumo: []);

    expect(app(SaldoService::class)->getBalance($user->refresh()))->toBe($saldoAntes);
});
