<?php

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Services\Consultas\FonteRegistry;
use App\Services\Consultas\Persistencia\PersistenciaCnpj;
use App\Services\Consultas\RetryConsultaService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

/** Grava o mapa _fontes_erro direto num ConsultaResultado (participante). */
function gravarFontesErro(int $loteId, int $participanteId, array $erros): void
{
    $linha = ConsultaResultado::firstOrNew([
        'consulta_lote_id' => $loteId,
        'participante_id' => $participanteId,
    ]);
    $dados = $linha->resultado_dados ?? [];
    $dados['_fontes_erro'] = $erros;
    $linha->resultado_dados = $dados;
    $linha->status = $linha->status ?: 'erro';
    $linha->save();
}

function custoFonte(string $chave): int
{
    return app(FonteRegistry::class)->get($chave)->custoCreditos();
}

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

// ---------------------------------------------------------------------------
// Task 2 — RetryConsultaService: pendentesRetry + precificar
// ---------------------------------------------------------------------------

it('lista só fontes retry com tentativas 0 como elegíveis e precifica 50% off', function () {
    config()->set('consultas.retry.desconto_pct', 50);
    config()->set('consultas.retry.max_por_fonte', 1);
    [$loteId, $participanteId] = montarLoteParticipante();

    gravarFontesErro($loteId, $participanteId, [
        'cnd_federal' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 600, 'tentativas' => 0],
        'cndt'        => ['origem' => 'integracao', 'status' => 'fatal', 'codigo' => 602, 'tentativas' => 0],
        'crf_fgts'    => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 600, 'tentativas' => 1],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry(ConsultaLote::find($loteId));

    expect(collect($out['elegiveis'])->pluck('fonte')->all())->toBe(['cnd_federal']);
    expect(collect($out['inelegiveis'])->pluck('fonte')->sort()->values()->all())->toBe(['cndt', 'crf_fgts']);
    expect($out['elegiveis'][0]['preco_creditos'])->toBe((int) ceil(custoFonte('cnd_federal') * 0.5));
    expect($out['total_preco_creditos'])->toBe((int) ceil(custoFonte('cnd_federal') * 0.5));
});

it('marca o motivo dos inelegíveis (fatal / esgotado)', function () {
    config()->set('consultas.retry.max_por_fonte', 1);
    [$loteId, $participanteId] = montarLoteParticipante();
    gravarFontesErro($loteId, $participanteId, [
        'cndt'     => ['origem' => 'integracao', 'status' => 'fatal', 'codigo' => 602, 'tentativas' => 0],
        'crf_fgts' => ['origem' => 'integracao', 'status' => 'retry', 'codigo' => 600, 'tentativas' => 1],
    ]);

    $out = app(RetryConsultaService::class)->pendentesRetry(ConsultaLote::find($loteId));
    $motivos = collect($out['inelegiveis'])->pluck('motivo', 'fonte');
    expect($motivos['cndt'])->toBe('fatal');
    expect($motivos['crf_fgts'])->toBe('esgotado');
});

it('precifica somando ceil por fonte', function () {
    config()->set('consultas.retry.desconto_pct', 50);
    $r = app(RetryConsultaService::class)->precificar([
        ['custo_creditos' => 5], ['custo_creditos' => 3], // ceil(2.5)+ceil(1.5)=3+2=5
    ]);
    expect($r['creditos'])->toBe(5);
});
