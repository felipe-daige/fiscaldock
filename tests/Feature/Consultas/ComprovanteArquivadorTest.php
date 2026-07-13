<?php

use App\Services\Consultas\ComprovanteArquivador;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    config()->set('consultas.comprovantes.arquivar', true);
});

it('arquiva o comprovante usando a extensão do content-type', function () {
    Http::fake([
        'arquivos.example/*' => Http::response('%PDF-1.7', 200, [
            'Content-Type' => 'application/pdf',
        ]),
    ]);

    $arquivo = app(ComprovanteArquivador::class)->arquivar(
        'https://arquivos.example/comprovante.bin',
        42,
    );

    expect($arquivo)->not->toBeNull();
    expect($arquivo['path'])->toStartWith('comprovantes/42/')
        ->and($arquivo['path'])->toEndWith('.pdf')
        ->and($arquivo['arquivado_em'])->not->toBeEmpty();
    Storage::disk('local')->assertExists($arquivo['path']);
});

it('retorna null sem lançar em HTTP 500', function () {
    Http::fake(['arquivos.example/*' => Http::response('erro', 500)]);

    expect(app(ComprovanteArquivador::class)->arquivar(
        'https://arquivos.example/comprovante.pdf',
        42,
    ))->toBeNull();
});

it('rejeita arquivo acima de 10 MB', function () {
    Http::fake([
        'arquivos.example/*' => Http::response('pequeno', 200, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => (string) (10 * 1024 * 1024 + 1),
        ]),
    ]);

    expect(app(ComprovanteArquivador::class)->arquivar(
        'https://arquivos.example/grande.pdf',
        42,
    ))->toBeNull();
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

it('respeita o kill-switch sem fazer chamada HTTP', function () {
    config()->set('consultas.comprovantes.arquivar', false);
    Http::fake();

    expect(app(ComprovanteArquivador::class)->arquivar(
        'https://arquivos.example/comprovante.pdf',
        42,
    ))->toBeNull();
    Http::assertNothingSent();
});

it('inclui o rótulo descritivo no nome do arquivo', function () {
    Http::fake([
        'arquivos.example/*' => Http::response('<html></html>', 200, [
            'Content-Type' => 'text/html',
        ]),
    ]);

    $arquivo = app(ComprovanteArquivador::class)->arquivar(
        'https://arquivos.example/comprovante',
        42,
        'CND Federal 04252011000110',
    );

    expect($arquivo)->not->toBeNull();
    expect(basename($arquivo['path']))->toStartWith('CND Federal 04252011000110__')
        ->and($arquivo['path'])->toEndWith('.html');
    Storage::disk('local')->assertExists($arquivo['path']);
});

it('sanitiza rótulo com caracteres perigosos sem quebrar o path', function () {
    Http::fake([
        'arquivos.example/*' => Http::response('%PDF-1.7', 200, [
            'Content-Type' => 'application/pdf',
        ]),
    ]);

    $arquivo = app(ComprovanteArquivador::class)->arquivar(
        'https://arquivos.example/comprovante.pdf',
        42,
        "../etc/rot__ulo\\perigoso <>:*?|\0",
    );

    expect($arquivo)->not->toBeNull();
    expect($arquivo['path'])->toStartWith('comprovantes/42/')
        ->and(basename($arquivo['path']))->not->toContain('..')
        ->and(substr_count($arquivo['path'], '/'))->toBe(4);
    Storage::disk('local')->assertExists($arquivo['path']);
});

it('monta rótulos canônicos por fonte e por documento fiscal', function () {
    expect(ComprovanteArquivador::rotuloFonte('cnd_federal', '04.252.011/0001-10'))
        ->toBe('CND Federal 04252011000110')
        ->and(ComprovanteArquivador::rotuloFonte('crf_fgts', null))->toBe('CRF FGTS')
        ->and(ComprovanteArquivador::rotuloFonte('fonte_nova', '123'))->toBe('Fonte Nova 123')
        ->and(ComprovanteArquivador::rotuloDocumento('NFE', 'CHAVE44', 'html'))->toBe('NF-e CHAVE44 - espelho')
        ->and(ComprovanteArquivador::rotuloDocumento('CTE', 'CHAVE44', 'xml'))->toBe('CT-e CHAVE44 - XML')
        ->and(ComprovanteArquivador::rotuloDocumento('NFE', 'CHAVE44', 'site_receipt'))->toBe('NF-e CHAVE44 - recibo');
});

it('extrai o rótulo de um path arquivado e ignora paths legados', function () {
    expect(ComprovanteArquivador::rotuloDePath('comprovantes/42/2026/07/CND Federal 04252011000110__01JABCDEF.html'))
        ->toBe('CND Federal 04252011000110')
        ->and(ComprovanteArquivador::rotuloDePath('comprovantes/42/2026/07/01JABCDEF.html'))->toBeNull();
});

it('extrai a expiração da URL assinada da InfoSimples', function () {
    $epoch = now()->addDays(7)->timestamp;
    $url = "https://host/infosimples-storage/assinatura/{$epoch}/salt/arquivo.pdf";

    expect(app(ComprovanteArquivador::class)->expiraEm($url))->toBe($epoch)
        ->and(app(ComprovanteArquivador::class)->expiraEm('https://host/arquivo.pdf'))->toBeNull();
});
