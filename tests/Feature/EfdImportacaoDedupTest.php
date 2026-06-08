<?php

use App\Models\EfdImportacao;
use App\Models\User;
use App\Services\Efd\EfdImportacaoDuplicidadeService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

function spedPisCofinsValido(): string
{
    return "|0000|006|0|||01062024|30062024|HIDRATOP|97551165000193|MS|5003702||00|9|\r\n".
        "|A100|0|0|F|00||1|1|CHV|01062024|01062024|1000.00|9|0|1000.00|6.5|1000.00|30|0|0|0|\r\n".
        "|9999|3|\r\n";
}

function prepararUploadEfd(): void
{
    config()->set('importacao.efd_manutencao.ativa', false);
    config()->set('services.webhook.importacao_efd_contribuicoes_url', 'http://n8n.test/hook');
}

it('detecta arquivo identico por hash', function () {
    $user = User::factory()->create();
    EfdImportacao::create([
        'user_id' => $user->id,
        'tipo_efd' => 'EFD PIS/COFINS',
        'periodo_inicio' => '2024-06-01',
        'periodo_fim' => '2024-06-30',
        'arquivo_hash' => str_repeat('a', 64),
        'status' => 'concluido',
    ]);

    $r = app(EfdImportacaoDuplicidadeService::class)->verificar(
        $user->id, null, 'EFD PIS/COFINS', '2024-06-01', '2024-06-30', str_repeat('a', 64)
    );

    expect($r['caso'])->toBe('identico');
    expect($r['importacao'])->not->toBeNull();
});

it('detecta mesmo periodo com hash diferente', function () {
    $user = User::factory()->create();
    EfdImportacao::create([
        'user_id' => $user->id,
        'tipo_efd' => 'EFD PIS/COFINS',
        'periodo_inicio' => '2024-06-01',
        'periodo_fim' => '2024-06-30',
        'arquivo_hash' => str_repeat('a', 64),
        'status' => 'concluido',
    ]);

    $r = app(EfdImportacaoDuplicidadeService::class)->verificar(
        $user->id, null, 'EFD PIS/COFINS', '2024-06-01', '2024-06-30', str_repeat('b', 64)
    );

    expect($r['caso'])->toBe('periodo');
});

it('nao detecta conflito para periodo diferente', function () {
    $user = User::factory()->create();
    EfdImportacao::create([
        'user_id' => $user->id,
        'tipo_efd' => 'EFD PIS/COFINS',
        'periodo_inicio' => '2024-06-01',
        'periodo_fim' => '2024-06-30',
        'arquivo_hash' => str_repeat('a', 64),
        'status' => 'concluido',
    ]);

    $r = app(EfdImportacaoDuplicidadeService::class)->verificar(
        $user->id, null, 'EFD PIS/COFINS', '2024-07-01', '2024-07-31', str_repeat('b', 64)
    );

    expect($r['caso'])->toBeNull();
});

it('ignora importacoes com status erro', function () {
    $user = User::factory()->create();
    EfdImportacao::create([
        'user_id' => $user->id,
        'tipo_efd' => 'EFD PIS/COFINS',
        'periodo_inicio' => '2024-06-01',
        'periodo_fim' => '2024-06-30',
        'arquivo_hash' => str_repeat('a', 64),
        'status' => 'erro',
    ]);

    $r = app(EfdImportacaoDuplicidadeService::class)->verificar(
        $user->id, null, 'EFD PIS/COFINS', '2024-06-01', '2024-06-30', str_repeat('a', 64)
    );

    expect($r['caso'])->toBeNull();
});

it('409 identico quando o mesmo arquivo ja foi importado', function () {
    Http::fake();
    prepararUploadEfd();
    $user = User::factory()->create();
    $conteudo = spedPisCofinsValido();
    EfdImportacao::create([
        'user_id' => $user->id, 'tipo_efd' => 'EFD PIS/COFINS',
        'periodo_inicio' => '2024-06-01', 'periodo_fim' => '2024-06-30',
        'arquivo_hash' => hash('sha256', $conteudo), 'status' => 'concluido',
    ]);

    $resp = $this->actingAs($user)->postJson('/app/importacao/efd/importar-txt', [
        'arquivo' => UploadedFile::fake()->createWithContent('sped.txt', $conteudo),
        'tipo_efd' => 'EFD PIS/COFINS',
    ]);

    $resp->assertStatus(409)->assertJson(['caso' => 'identico']);
});

it('409 periodo quando mesmo periodo com conteudo diferente', function () {
    Http::fake();
    prepararUploadEfd();
    $user = User::factory()->create();
    EfdImportacao::create([
        'user_id' => $user->id, 'tipo_efd' => 'EFD PIS/COFINS',
        'periodo_inicio' => '2024-06-01', 'periodo_fim' => '2024-06-30',
        'arquivo_hash' => str_repeat('a', 64), 'status' => 'concluido',
    ]);

    $resp = $this->actingAs($user)->postJson('/app/importacao/efd/importar-txt', [
        'arquivo' => UploadedFile::fake()->createWithContent('sped.txt', spedPisCofinsValido()),
        'tipo_efd' => 'EFD PIS/COFINS',
    ]);

    $resp->assertStatus(409)->assertJson(['caso' => 'periodo']);
});

it('substituir apaga a importacao anterior e cria nova', function () {
    Http::fake(['*' => Http::response(['ok' => true], 200)]);
    prepararUploadEfd();
    $user = User::factory()->create();
    $antiga = EfdImportacao::create([
        'user_id' => $user->id, 'tipo_efd' => 'EFD PIS/COFINS',
        'periodo_inicio' => '2024-06-01', 'periodo_fim' => '2024-06-30',
        'arquivo_hash' => str_repeat('a', 64), 'status' => 'concluido',
    ]);

    $resp = $this->actingAs($user)->postJson('/app/importacao/efd/importar-txt', [
        'arquivo' => UploadedFile::fake()->createWithContent('sped.txt', spedPisCofinsValido()),
        'tipo_efd' => 'EFD PIS/COFINS',
        'substituir' => true,
    ]);

    $resp->assertOk()->assertJson(['success' => true]);
    expect(EfdImportacao::find($antiga->id))->toBeNull();
    expect(EfdImportacao::where('user_id', $user->id)->count())->toBe(1);
});

it('substituir nao apaga importacao de outro usuario', function () {
    Http::fake(['*' => Http::response(['ok' => true], 200)]);
    prepararUploadEfd();
    $dono = User::factory()->create();
    $intruso = User::factory()->create();
    $alheia = EfdImportacao::create([
        'user_id' => $dono->id, 'tipo_efd' => 'EFD PIS/COFINS',
        'periodo_inicio' => '2024-06-01', 'periodo_fim' => '2024-06-30',
        'arquivo_hash' => hash('sha256', spedPisCofinsValido()), 'status' => 'concluido',
    ]);

    $this->actingAs($intruso)->postJson('/app/importacao/efd/importar-txt', [
        'arquivo' => UploadedFile::fake()->createWithContent('sped.txt', spedPisCofinsValido()),
        'tipo_efd' => 'EFD PIS/COFINS',
        'substituir' => true,
    ])->assertOk();

    expect(EfdImportacao::find($alheia->id))->not->toBeNull();
});

it('upload sem conflito segue normalmente e grava identidade', function () {
    Http::fake(['*' => Http::response(['ok' => true], 200)]);
    prepararUploadEfd();
    $user = User::factory()->create();

    $resp = $this->actingAs($user)->postJson('/app/importacao/efd/importar-txt', [
        'arquivo' => UploadedFile::fake()->createWithContent('sped.txt', spedPisCofinsValido()),
        'tipo_efd' => 'EFD PIS/COFINS',
    ]);

    $resp->assertOk()->assertJson(['success' => true]);
    $imp = EfdImportacao::where('user_id', $user->id)->first();
    expect($imp->periodo_inicio->format('Y-m-d'))->toBe('2024-06-01');
    expect($imp->cnpj)->toBe('97551165000193');
});
