<?php

use App\Jobs\ProcessarConsultaJob;
use App\Models\ConsultaResultado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    config()->set('consultas.comprovantes.arquivar', true);
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'token-teste');
    config()->set('consultas.retry.auto.max_tentativas', 0);
});

it('ProcessarConsultaJob persiste o arquivo dentro do bloco da fonte', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    $comprovante = 'https://arquivos.example/cnd.pdf';
    Http::fake([
        'api.infosimples.com/*' => Http::response([
            'code' => 200,
            'data' => [['tipo' => 'Negativa', 'site_receipt' => $comprovante]],
        ]),
        'arquivos.example/*' => Http::response('%PDF', 200, ['Content-Type' => 'application/pdf']),
    ]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId,
        alvoTipo: 'participante',
        alvoId: $participanteId,
        userId: $userId,
        tabId: 'tab-comprovante',
        consultasIncluidas: ['cnd_federal'],
        alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando consulta', 'Certidões Federais'],
    );

    $resultado = ConsultaResultado::where('consulta_lote_id', $loteId)->firstOrFail();
    $bloco = $resultado->resultado_dados['cnd_federal'];
    expect($bloco['comprovante'])->toBe($comprovante)
        ->and($bloco['comprovante_arquivo'])->toStartWith("comprovantes/{$userId}/")
        ->and($bloco['comprovante_arquivado_em'])->not->toBeEmpty();
    Storage::disk('local')->assertExists($bloco['comprovante_arquivo']);
});

it('falha ao baixar não derruba nem apaga o resultado da consulta', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();
    $comprovante = 'https://arquivos.example/cnd.pdf';
    Http::fake([
        'api.infosimples.com/*' => Http::response([
            'code' => 200,
            'data' => [['tipo' => 'Negativa', 'site_receipt' => $comprovante]],
        ]),
        'arquivos.example/*' => Http::response('erro', 500),
    ]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId,
        alvoTipo: 'participante',
        alvoId: $participanteId,
        userId: $userId,
        tabId: 'tab-comprovante-falha',
        consultasIncluidas: ['cnd_federal'],
        alvo: ['cnpj' => '19131243000197'],
        etapas: ['Preparando consulta', 'Certidões Federais'],
    );

    $resultado = ConsultaResultado::where('consulta_lote_id', $loteId)->firstOrFail();
    expect($resultado->status)->toBe(ConsultaResultado::STATUS_SUCESSO)
        ->and($resultado->resultado_dados['cnd_federal']['comprovante'])->toBe($comprovante)
        ->and($resultado->resultado_dados['cnd_federal'])->not->toHaveKey('comprovante_arquivo');
});
