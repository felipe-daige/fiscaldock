<?php

use App\Services\Consultas\Providers\MinhaReceitaProvider;
use Illuminate\Support\Facades\Http;

it('faz GET por CNPJ e classifica 200 como sucesso', function () {
    Http::fake([
        'minhareceita.org/*' => Http::response(['razao_social' => 'EMPRESA X', 'uf' => 'MS'], 200),
    ]);

    $resp = (new MinhaReceitaProvider)->consultar('', ['cnpj' => '00000000000191']);

    expect($resp->status)->toBe('sucesso');
    expect($resp->httpCode)->toBe(200);
    expect($resp->raw['razao_social'])->toBe('EMPRESA X');
    Http::assertSent(fn ($req) => str_contains($req->url(), '00000000000191'));
});

it('classifica 404 como nao_encontrado', function () {
    Http::fake(['minhareceita.org/*' => Http::response(['message' => 'não encontrado'], 404)]);
    $resp = (new MinhaReceitaProvider)->consultar('', ['cnpj' => '00000000000191']);
    expect($resp->status)->toBe('nao_encontrado');
});

it('classifica 5xx como retry', function () {
    Http::fake(['minhareceita.org/*' => Http::response('erro', 500)]);
    $resp = (new MinhaReceitaProvider)->consultar('', ['cnpj' => '00000000000191']);
    expect($resp->status)->toBe('retry');
});
