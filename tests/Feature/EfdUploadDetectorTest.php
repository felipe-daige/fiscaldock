<?php

use App\Jobs\ProcessarEfdImportacaoJob;
use App\Models\EfdImportacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // A extração roda 100% no motor Laravel — o upload despacha o Job (sem n8n).
    Bus::fake();
});

function spedPisCofins(): string
{
    return "|0000|006|0|01022024|29022024|EMPRESA|12345678000100|MG|123|3106200|0|0|\r\n".
        "|A100|0|0|FORN|00||1|1|CHV|01022024|01022024|100|9|0|100|0.65|100|3|0|0|0|\r\n".
        "|9999|3|\r\n";
}

function spedIcmsIpi(): string
{
    return "|0000|016|0|01022024|29022024|EMPRESA|12345678000100|MG|123|3106200|0|A|0|0|\r\n".
        "|C100|0|0|FORN|55|00|1|123|CHAVE|01022024|01022024|100|9|0|100|0|100|0|0|0|0|0|0|0|0|0|0|0|0|0|\r\n".
        "|E100|01022024|29022024|\r\n".
        "|E110|0|0|0|0|0|0|0|0|0|0|0|\r\n".
        "|9999|5|\r\n";
}

it('mostra a tela de upload efd sem bloqueio de manutencao', function () {
    config()->set('importacao.efd_manutencao.ativa', true);

    $response = $this->get('/app/importacao/efd');

    $response->assertOk();
    $response->assertSee('Importação EFD');
    $response->assertSee('Upload do Arquivo');
    $response->assertDontSee('Módulo em manutenção');
    $response->assertDontSee('Upload temporariamente desativado');
});

it('mantem upload efd aberto mesmo se a config legada de manutencao estiver ativa', function () {
    config()->set('importacao.efd_manutencao.ativa', true);

    $file = UploadedFile::fake()->createWithContent('icms.txt', spedIcmsIpi());

    $response = $this->postJson('/app/importacao/efd/importar-txt', [
        'tipo_efd' => 'EFD ICMS/IPI',
        'arquivo' => $file,
    ]);

    $response->assertOk();
    expect(EfdImportacao::first()->tipo_efd)->toBe('EFD ICMS/IPI');
});

it('rejeita arquivo nao-SPED com 422', function () {
    $file = UploadedFile::fake()->createWithContent('lixo.txt', 'isso nao eh sped, eh um texto aleatorio qualquer.');

    $response = $this->postJson('/app/importacao/efd/importar-txt', [
        'tipo_efd' => 'EFD PIS/COFINS',
        'arquivo' => $file,
    ]);

    $response->assertStatus(422);
    expect($response->json('success'))->toBeFalse();
    expect($response->json('error'))->toContain('SPED');
    expect(EfdImportacao::count())->toBe(0);
    Bus::assertNotDispatched(ProcessarEfdImportacaoJob::class);
});

it('corrige tipo_efd silenciosamente quando arquivo divergir', function () {
    Log::spy();

    $file = UploadedFile::fake()->createWithContent('contrib.txt', spedPisCofins());

    $response = $this->postJson('/app/importacao/efd/importar-txt', [
        'tipo_efd' => 'EFD ICMS/IPI', // usuario escolheu errado
        'arquivo' => $file,
    ]);

    $response->assertOk();

    $importacao = EfdImportacao::first();
    expect($importacao->tipo_efd)->toBe('EFD PIS/COFINS'); // sobrescrito pelo detectado

    // Job despachado para ESTA importação (o driver correto é escolhido no Job pelo tipo).
    Bus::assertDispatched(
        ProcessarEfdImportacaoJob::class,
        fn (ProcessarEfdImportacaoJob $job) => $job->importacaoId === $importacao->id
    );

    // log de divergencia registrado
    Log::shouldHaveReceived('info')->withArgs(fn ($msg) => str_contains($msg, 'tipo_efd corrigido'))->atLeast()->once();
});

it('aceita upload quando tipo_efd bate com arquivo', function () {
    $file = UploadedFile::fake()->createWithContent('icms.txt', spedIcmsIpi());

    $response = $this->postJson('/app/importacao/efd/importar-txt', [
        'tipo_efd' => 'EFD ICMS/IPI',
        'arquivo' => $file,
    ]);

    $response->assertOk();
    expect(EfdImportacao::first()->tipo_efd)->toBe('EFD ICMS/IPI');
    Bus::assertDispatched(ProcessarEfdImportacaoJob::class);
});

it('aceita SPED de tipo desconhecido sem sobrescrever', function () {
    // SPED valido (0000+9999) mas sem discriminadores
    $sped = "|0000|999|0|01022024|29022024|EMPRESA|12345678000100|MG|123|3106200|0|0|\r\n".
        "|0001|0|\r\n".
        "|9999|2|\r\n";

    $file = UploadedFile::fake()->createWithContent('desconhecido.txt', $sped);

    $response = $this->postJson('/app/importacao/efd/importar-txt', [
        'tipo_efd' => 'EFD PIS/COFINS',
        'arquivo' => $file,
    ]);

    $response->assertOk();
    expect(EfdImportacao::first()->tipo_efd)->toBe('EFD PIS/COFINS');
});
