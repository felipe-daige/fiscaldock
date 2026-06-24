<?php

use App\Models\ConsultaResultado;
use App\Services\Consultas\Persistencia\PersistenciaCnpj;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Task 1 — _fontes_erro enriquecido (objeto + tentativas + retrocompat)
// ---------------------------------------------------------------------------

it('grava _fontes_erro como objeto com status/codigo/tentativas', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    $svc = app(PersistenciaCnpj::class);

    $svc->marcarErroFonte($loteId, 'participante', $participanteId, 'cnd_federal', 'integracao', 'retry', 600);

    $row = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($row->resultado_dados['_fontes_erro']['cnd_federal'])->toMatchArray([
        'origem' => 'integracao', 'status' => 'retry', 'codigo' => 600, 'tentativas' => 0,
    ]);
});

it('preserva tentativas numa re-falha da mesma fonte', function () {
    [$loteId, $participanteId] = montarLoteParticipante();
    $svc = app(PersistenciaCnpj::class);

    $svc->marcarErroFonte($loteId, 'participante', $participanteId, 'cnd_federal', 'integracao', 'retry', 600);
    $svc->incrementarTentativaFonte($loteId, 'participante', $participanteId, 'cnd_federal');
    $svc->marcarErroFonte($loteId, 'participante', $participanteId, 'cnd_federal', 'integracao', 'retry', 600);

    $row = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($row->resultado_dados['_fontes_erro']['cnd_federal']['tentativas'])->toBe(1);
});

it('normaliza entrada string legada como retry/tentativas-0', function () {
    $svc = app(PersistenciaCnpj::class);

    expect($svc->normalizarFontesErro(['cnd_federal' => 'integracao']))->toMatchArray([
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => null, 'tentativas' => 0],
    ]);
    expect($svc->normalizarFontesErro(['x' => 'interno'])['x']['status'])->toBeNull();
});
