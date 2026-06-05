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

it('guard de teste: CNPJ fora da allowlist NÃO chama o provedor (sem cobrança)', function () {
    config()->set('consultas.infosimples_teste_cnpjs', ['11111111111111']);
    Http::fake(); // qualquer chamada falha a asserção

    $resp = app(InfoSimplesProvider::class)->consultar('receita-federal/pgfn', ['cnpj' => '99999999999999']);

    expect($resp->status)->toBe('nao_aplicavel');
    expect($resp->mensagem)->toContain('allowlist');
    Http::assertNothingSent();
});

it('guard de teste: CNPJ na allowlist é consultado normalmente', function () {
    config()->set('consultas.infosimples_teste_cnpjs', ['11111111111111']);
    Http::fake(['api.infosimples.com/*' => Http::response(['code' => 200, 'data' => [['tipo' => 'Negativa']]], 200)]);

    $resp = app(InfoSimplesProvider::class)->consultar('receita-federal/pgfn', ['cnpj' => '11.111.111/1111-11']);

    expect($resp->status)->toBe('sucesso');
    Http::assertSent(fn ($req) => str_contains($req->url(), 'receita-federal/pgfn'));
});

it('allowlist vazia = todos liberados (produção normal)', function () {
    config()->set('consultas.infosimples_teste_cnpjs', []);
    Http::fake(['api.infosimples.com/*' => Http::response(['code' => 200, 'data' => []], 200)]);

    $resp = app(InfoSimplesProvider::class)->consultar('receita-federal/pgfn', ['cnpj' => '99999999999999']);
    expect($resp->status)->toBe('sucesso');
});

it('601 (auth) vira fatal', function () {
    Http::fake(['api.infosimples.com/*' => Http::response(['code' => 601, 'code_message' => 'token inválido', 'errors' => []], 200)]);

    $resp = app(InfoSimplesProvider::class)->consultar('receita-federal/pgfn', ['cnpj' => '19131243000197']);

    expect($resp->status)->toBe('fatal');
    expect($resp->httpCode)->toBe(601);
});
