<?php

use App\Services\Consultas\ClassificadorCodigo;
use App\Services\Consultas\Providers\InfoSimplesProvider;
use Illuminate\Support\Facades\Http;

it('allowlist de CNPJ não bloqueia consulta por chave_acesso (clearance)', function () {
    config()->set('consultas.infosimples_teste_cnpjs', ['11111111111111']);
    config()->set('consultas.providers.infosimples.token', 'tok');

    Http::fake(['api.infosimples.com/*' => Http::response(['code' => 200, 'data' => [[]]], 200)]);

    $provider = new InfoSimplesProvider(new ClassificadorCodigo);
    $resp = $provider->consultar('receita-federal/nfe', ['chave_acesso' => str_repeat('5', 44)]);

    expect($resp->status)->toBe('sucesso');
    Http::assertSent(fn ($req) => str_contains($req->url(), 'receita-federal/nfe'));
});

it('allowlist de CNPJ continua bloqueando CNPJ fora da lista', function () {
    config()->set('consultas.infosimples_teste_cnpjs', ['11111111111111']);

    Http::fake();
    $provider = new InfoSimplesProvider(new ClassificadorCodigo);
    $resp = $provider->consultar('receita-federal/pgfn', ['cnpj' => '99999999999999']);

    expect($resp->status)->toBe('nao_aplicavel');
    Http::assertNothingSent();
});
