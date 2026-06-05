<?php

use App\Services\Consultas\Providers\InfoSimplesProvider;
use Illuminate\Support\Facades\Http;

it('classifica pelo code do corpo (não pelo HTTP) — 200 sucesso', function () {
    Http::fake(['api.infosimples.com/*' => Http::response(['code' => 200, 'code_message' => 'ok', 'data' => [['tipo' => 'Negativa']]], 200)]);

    $resp = app(InfoSimplesProvider::class)->consultar('receita-federal/pgfn', ['cnpj' => '19131243000197']);

    expect($resp->status)->toBe('sucesso');
    expect($resp->httpCode)->toBe(200);
    expect($resp->raw['data'][0]['tipo'])->toBe('Negativa');
    Http::assertSent(fn ($req) => str_contains($req->url(), 'receita-federal/pgfn'));
});

it('611 vira indeterminado e preserva a mensagem (errors)', function () {
    Http::fake(['api.infosimples.com/*' => Http::response([
        'code' => 611, 'code_message' => 'Não foi possível emitir.', 'errors' => ['dados insuficientes'], 'data' => [],
    ], 200)]);

    $resp = app(InfoSimplesProvider::class)->consultar('receita-federal/pgfn', ['cnpj' => '19131243000197']);

    expect($resp->status)->toBe('indeterminado');
    expect($resp->httpCode)->toBe(611);
    expect($resp->mensagem)->toContain('dados insuficientes');
});

it('601 (auth) vira fatal', function () {
    Http::fake(['api.infosimples.com/*' => Http::response(['code' => 601, 'code_message' => 'token inválido', 'errors' => []], 200)]);

    $resp = app(InfoSimplesProvider::class)->consultar('receita-federal/pgfn', ['cnpj' => '19131243000197']);

    expect($resp->status)->toBe('fatal');
    expect($resp->httpCode)->toBe(601);
});
