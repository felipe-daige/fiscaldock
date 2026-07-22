<?php

use App\Jobs\ProcessarEfdImportacaoJob;
use App\Models\EfdImportacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/**
 * Cutover do motor de extração EFD (F4): a flag `EFD_MOTOR`/`EFD_MOTOR_FISCAL` roteia o
 * upload pro Job Laravel em vez do webhook n8n. Default n8n — merge não muda produção.
 * PIS/COFINS nunca roteia (sem driver até F5).
 */
if (! function_exists('spedIcmsCutover')) {
    function spedIcmsCutover(): string
    {
        return "|0000|016|0|01022024|29022024|EMPRESA|12345678000100|MG|123|3106200|0|A|0|0|\r\n".
            "|C100|0|0|FORN|55|00|1|123|CHAVE|01022024|01022024|100|9|0|100|0|100|0|0|0|0|0|0|0|0|0|0|0|0|0|\r\n".
            "|9999|3|\r\n";
    }
}

if (! function_exists('spedPisCutover')) {
    function spedPisCutover(): string
    {
        return "|0000|006|0|01022024|29022024|EMPRESA|12345678000100|MG|123|3106200|0|0|\r\n".
            "|A100|0|0|FORN|00||1|1|CHV|01022024|01022024|100|9|0|100|0.65|100|3|0|0|0|\r\n".
            "|9999|3|\r\n";
    }
}

function uploadEfd(string $tipo, string $conteudo, ?string $tabId = null): \Illuminate\Testing\TestResponse
{
    $file = UploadedFile::fake()->createWithContent('sped.txt', $conteudo);

    return test()->postJson('/app/importacao/efd/importar-txt', array_filter([
        'tipo_efd' => $tipo,
        'arquivo' => $file,
        'tab_id' => $tabId,
    ]));
}

beforeEach(function () {
    config([
        'services.webhook.importacao_efd_fiscal_url' => 'https://n8n.example.com/icms',
        'services.webhook.importacao_efd_contribuicoes_url' => 'https://n8n.example.com/contrib',
    ]);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    Http::fake(['*' => Http::response(['ok' => true], 200)]);
});

it('default (n8n): não despacha o Job, chama o webhook fiscal', function () {
    Bus::fake();

    $resp = uploadEfd('EFD ICMS/IPI', spedIcmsCutover());

    $resp->assertOk();
    expect($resp->json('motor'))->toBeNull();
    Bus::assertNotDispatched(ProcessarEfdImportacaoJob::class);
    Http::assertSent(fn ($req) => str_contains($req->url(), 'icms'));
});

it('EFD_MOTOR_FISCAL=laravel: despacha o Job com tab, não chama n8n, motor=laravel', function () {
    config(['efd.motor_fiscal' => 'laravel']);
    Bus::fake();

    $resp = uploadEfd('EFD ICMS/IPI', spedIcmsCutover(), 'tab-1');

    $resp->assertOk();
    expect($resp->json('motor'))->toBe('laravel');
    expect($resp->json('success'))->toBeTrue();

    $impId = EfdImportacao::first()->id;
    Bus::assertDispatched(
        ProcessarEfdImportacaoJob::class,
        fn (ProcessarEfdImportacaoJob $job) => $job->importacaoId === $impId && $job->tabId === 'tab-1'
    );
    Http::assertNothingSent();
});

it('EFD_MOTOR_FISCAL=laravel é granular: não afeta PIS/COFINS, que segue n8n', function () {
    config(['efd.motor_fiscal' => 'laravel']);
    Bus::fake();

    $resp = uploadEfd('EFD PIS/COFINS', spedPisCutover());

    $resp->assertOk();
    expect($resp->json('motor'))->toBeNull();
    Bus::assertNotDispatched(ProcessarEfdImportacaoJob::class);
    Http::assertSent(fn ($req) => str_contains($req->url(), 'contrib'));
});

it('EFD_MOTOR_CONTRIB=laravel: despacha o Job pro PIS/COFINS, não afeta fiscal', function () {
    config(['efd.motor_contrib' => 'laravel']);
    Bus::fake();

    $resp = uploadEfd('EFD PIS/COFINS', spedPisCutover(), 'tab-c');
    $resp->assertOk();
    expect($resp->json('motor'))->toBe('laravel');
    $impId = EfdImportacao::first()->id;
    Bus::assertDispatched(
        ProcessarEfdImportacaoJob::class,
        fn (ProcessarEfdImportacaoJob $job) => $job->importacaoId === $impId && $job->tabId === 'tab-c'
    );

    // fiscal sob motor_contrib segue n8n
    $resp2 = uploadEfd('EFD ICMS/IPI', spedIcmsCutover());
    $resp2->assertOk();
    expect($resp2->json('motor'))->toBeNull();
    Http::assertSent(fn ($req) => str_contains($req->url(), 'icms'));
});

it('EFD_MOTOR=laravel (global) roteia AMBOS os tipos pro Laravel', function () {
    config(['efd.motor' => 'laravel']);
    Bus::fake();

    uploadEfd('EFD ICMS/IPI', spedIcmsCutover())->assertOk();
    uploadEfd('EFD PIS/COFINS', spedPisCutover())->assertOk();

    Bus::assertDispatchedTimes(ProcessarEfdImportacaoJob::class, 2);
    Http::assertNothingSent();
});

it('motor Laravel dispensa webhook: sem URL fiscal ainda processa (sem 503)', function () {
    config([
        'services.webhook.importacao_efd_fiscal_url' => null,
        'efd.motor_fiscal' => 'laravel',
    ]);
    Bus::fake();

    $resp = uploadEfd('EFD ICMS/IPI', spedIcmsCutover());

    $resp->assertOk();
    expect($resp->json('motor'))->toBe('laravel');
    Bus::assertDispatched(ProcessarEfdImportacaoJob::class);
});
