<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Arquivos\ArquivoUsuarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    (new Database\Seeders\SubscriptionPlanSeeder)->run();
});

it('lista apenas uploads e comprovantes do usuário autenticado', function () {
    $user = User::factory()->create();
    $outro = User::factory()->create();
    Storage::disk('local')->put("arquivos/{$user->id}/2026/07/01ABC/relatorio.pdf", '%PDF-user');
    Storage::disk('local')->put("comprovantes/{$user->id}/2026/07/01DEF.pdf", '%PDF-comprovante');
    Storage::disk('local')->put("arquivos/{$outro->id}/2026/07/01XYZ/segredo.pdf", '%PDF-outro');

    $this->actingAs($user)
        ->get(route('app.arquivos.index'))
        ->assertOk()
        ->assertSee('Meus Arquivos')
        ->assertSee('relatorio.pdf')
        ->assertSee('Comprovante PDF')
        ->assertDontSee('segredo.pdf')
        ->assertSee('2 resultado(s)');
});

it('aceita upload privado e permite download pelo dono', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('app.arquivos.store'), [
            'arquivos' => [UploadedFile::fake()->create('relatório fiscal.pdf', 20, 'application/pdf')],
        ])
        ->assertRedirect(route('app.arquivos.index'))
        ->assertSessionHas('success');

    $path = collect(Storage::disk('local')->allFiles("arquivos/{$user->id}"))->sole();
    expect($path)->toContain("arquivos/{$user->id}/")
        ->and(basename($path))->toBe('relatório fiscal.pdf');

    $id = app(ArquivoUsuarioService::class)->identificador($path);
    $this->actingAs($user)
        ->get(route('app.arquivos.download', $id))
        ->assertOk()
        // Symfony translitera o fallback ASCII do Content-Disposition.
        ->assertDownload('relatorio fiscal.pdf');
});

it('bloqueia formato não permitido', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('app.arquivos.index'))
        ->post(route('app.arquivos.store'), [
            'arquivos' => [UploadedFile::fake()->create('programa.exe', 10, 'application/octet-stream')],
        ])
        ->assertRedirect(route('app.arquivos.index'))
        ->assertSessionHasErrors('arquivos.0');

    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

it('recusa o lote inteiro quando ultrapassa a quota do plano', function () {
    $free = SubscriptionPlan::where('codigo', 'free')->firstOrFail();
    $free->capabilities = array_merge($free->capabilities ?? [], ['armazenamento_mb' => 1]);
    $free->save();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('app.arquivos.store'), [
            'arquivos' => [UploadedFile::fake()->create('grande.pdf', 2 * 1024, 'application/pdf')],
        ])
        ->assertSessionHasErrors('arquivos');

    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

it('não permite baixar arquivo de outra conta mesmo com identificador válido', function () {
    $dono = User::factory()->create();
    $intruso = User::factory()->create();
    $path = "arquivos/{$dono->id}/2026/07/01ABC/documento.pdf";
    Storage::disk('local')->put($path, '%PDF');
    $id = app(ArquivoUsuarioService::class)->identificador($path);

    $this->actingAs($intruso)
        ->get(route('app.arquivos.download', $id))
        ->assertNotFound();
});

it('exclui upload manual e protege comprovante do sistema', function () {
    $user = User::factory()->create();
    $upload = "arquivos/{$user->id}/2026/07/01ABC/apagar.pdf";
    $comprovante = "comprovantes/{$user->id}/2026/07/01DEF.pdf";
    Storage::disk('local')->put($upload, '%PDF-upload');
    Storage::disk('local')->put($comprovante, '%PDF-comprovante');
    $service = app(ArquivoUsuarioService::class);

    $this->actingAs($user)
        ->delete(route('app.arquivos.destroy', $service->identificador($upload)))
        ->assertRedirect(route('app.arquivos.index'));
    Storage::disk('local')->assertMissing($upload);

    $this->actingAs($user)
        ->delete(route('app.arquivos.destroy', $service->identificador($comprovante)))
        ->assertForbidden();
    Storage::disk('local')->assertExists($comprovante);
});

it('nomeia comprovante pelo rótulo arquivado e formata o CNPJ', function () {
    $user = User::factory()->create();
    Storage::disk('local')->put(
        "comprovantes/{$user->id}/2026/07/CND Federal 04252011000110__01JABCDEF.html",
        '<html></html>',
    );
    Storage::disk('local')->put("comprovantes/{$user->id}/2026/07/01DEF.pdf", '%PDF-legado');

    $this->actingAs($user)
        ->get(route('app.arquivos.index'))
        ->assertOk()
        ->assertSee('CND Federal 04.252.011/0001-10')
        ->assertSee('Comprovante PDF');
});

it('baixa comprovante rotulado com nome amigável e extensão', function () {
    $user = User::factory()->create();
    $path = "comprovantes/{$user->id}/2026/07/CNDT 04252011000110__01JABCDEF.html";
    Storage::disk('local')->put($path, '<html></html>');
    $id = app(ArquivoUsuarioService::class)->identificador($path);

    $this->actingAs($user)
        ->get(route('app.arquivos.download', $id))
        ->assertOk()
        ->assertDownload('CNDT 04.252.011-0001-10.html');
});

it('serve preview de HTML sandboxed e de XML como texto plano', function () {
    $user = User::factory()->create();
    $html = "comprovantes/{$user->id}/2026/07/CND Federal 04252011000110__01JHTML.html";
    $xml = "comprovantes/{$user->id}/2026/07/NF-e CHAVE - XML__01JXML.xml";
    Storage::disk('local')->put($html, '<html><body>certidão</body></html>');
    Storage::disk('local')->put($xml, '<?xml version="1.0"?><nfe/>');
    $service = app(ArquivoUsuarioService::class);

    $resposta = $this->actingAs($user)
        ->get(route('app.arquivos.preview', $service->identificador($html)))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
        ->assertHeader('Content-Security-Policy', 'sandbox')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($resposta->streamedContent())->toContain('certidão');

    $this->actingAs($user)
        ->get(route('app.arquivos.preview', $service->identificador($xml)))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
});

it('nega preview de formato não visualizável e de arquivo alheio', function () {
    $dono = User::factory()->create();
    $intruso = User::factory()->create();
    $zip = "arquivos/{$dono->id}/2026/07/01JZIP/backup.zip";
    $pdf = "arquivos/{$dono->id}/2026/07/01JPDF/laudo.pdf";
    Storage::disk('local')->put($zip, 'PK');
    Storage::disk('local')->put($pdf, '%PDF-1.7');
    $service = app(ArquivoUsuarioService::class);

    $this->actingAs($dono)
        ->get(route('app.arquivos.preview', $service->identificador($zip)))
        ->assertNotFound();

    $this->actingAs($intruso)
        ->get(route('app.arquivos.preview', $service->identificador($pdf)))
        ->assertNotFound();

    $this->actingAs($dono)
        ->get(route('app.arquivos.preview', $service->identificador($pdf)))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

it('listagem oferece preview só para formatos visualizáveis', function () {
    $user = User::factory()->create();
    Storage::disk('local')->put("comprovantes/{$user->id}/2026/07/CNDT 04252011000110__01JV.html", '<html></html>');
    Storage::disk('local')->put("arquivos/{$user->id}/2026/07/01JZ/backup.zip", 'PK');

    $resposta = $this->actingAs($user)
        ->get(route('app.arquivos.index'))
        ->assertOk();

    $html = $resposta->getContent();
    expect(substr_count($html, 'data-preview-url'))->toBeGreaterThanOrEqual(1)
        ->and(str_contains($html, 'backup.zip'))->toBeTrue();
    // zip não ganha atributo de preview em nenhuma linha
    preg_match_all('/data-preview-url="([^"]+)"/', $html, $m);
    foreach ($m[1] as $url) {
        expect($url)->not->toContain('backup');
    }
});

it('linka comprovante ao histórico de origem quando existe referência', function () {
    $user = User::factory()->create();

    // Comprovante de consulta CNPJ → lote de consulta.
    $lote = \App\Models\ConsultaLote::create([
        'user_id' => $user->id,
        'status' => \App\Models\ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 2,
        'tab_id' => 'tab-hist',
    ]);
    $cliente = \App\Models\Cliente::create([
        'user_id' => $user->id,
        'nome' => 'Empresa Alvo Ltda',
        'documento' => '04252011000110',
    ]);
    $pathConsulta = "comprovantes/{$user->id}/2026/07/CND Federal 04252011000110__01JHIST1.pdf";
    Storage::disk('local')->put($pathConsulta, '%PDF');
    \App\Models\ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'cliente_id' => $cliente->id,
        'status' => \App\Models\ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['cnd_federal' => ['comprovante_arquivo' => $pathConsulta]],
    ]);

    // Comprovante de clearance (busca avulsa) → resultado da busca.
    $loteAvulsa = \App\Models\ConsultaLote::create([
        'user_id' => $user->id,
        'status' => \App\Models\ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 5,
        'tab_id' => 'tab-avulsa',
        'resultado_resumo' => ['fluxo_origem' => 'avulsa'],
    ]);
    $chave = str_repeat('3524', 11);
    $pathClearance = "comprovantes/{$user->id}/2026/07/NF-e {$chave} - espelho__01JHIST2.html";
    Storage::disk('local')->put($pathClearance, '<html></html>');
    \App\Models\NfeConsulta::create([
        'user_id' => $user->id,
        'chave_acesso' => $chave,
        'tipo_documento' => 'NFE',
        'status' => 'AUTORIZADA',
        'consulta_lote_id' => $loteAvulsa->id,
        'emit_cnpj' => '46970030000202',
        'emit_nome' => 'Transportadora Emitente SA',
        'consultado_em' => now(),
        'payload' => ['comprovantes_arquivos' => ['html' => $pathClearance]],
    ]);

    // Comprovante órfão → sem link.
    Storage::disk('local')->put("comprovantes/{$user->id}/2026/07/01JORFAO.html", '<html></html>');

    $service = app(ArquivoUsuarioService::class);
    $arquivos = $service->listar($user)->keyBy('path');

    expect($arquivos[$pathConsulta]['historico_url'])
        ->toBe(route('app.consulta.lote.show', $lote->id))
        ->and($arquivos[$pathConsulta]['dono_documento'])->toBe('04.252.011/0001-10')
        ->and($arquivos[$pathConsulta]['dono_nome'])->toBe('Empresa Alvo Ltda')
        ->and($arquivos[$pathClearance]['historico_url'])
        ->toContain(route('app.clearance.buscar.resultado', $loteAvulsa->id))
        ->and($arquivos[$pathClearance]['historico_url'])->toContain('chave_acesso='.$chave)
        ->and($arquivos[$pathClearance]['dono_documento'])->toBe('46.970.030/0002-02')
        ->and($arquivos[$pathClearance]['dono_nome'])->toBe('Transportadora Emitente SA')
        ->and($arquivos["comprovantes/{$user->id}/2026/07/01JORFAO.html"]['historico_url'])->toBeNull()
        ->and($arquivos["comprovantes/{$user->id}/2026/07/01JORFAO.html"]['dono_documento'])->toBeNull();

    $this->actingAs($user)
        ->get(route('app.arquivos.index'))
        ->assertOk()
        ->assertSee('data-link', false)
        ->assertSee(route('app.consulta.lote.show', $lote->id), false)
        ->assertSee('Empresa Alvo Ltda')
        ->assertSee('46.970.030/0002-02');
});

it('deriva o CNPJ do rótulo quando o comprovante não tem registro no banco', function () {
    $user = User::factory()->create();
    Storage::disk('local')->put(
        "comprovantes/{$user->id}/2026/07/CNDT 04252011000110__01JSOLTO.html",
        '<html></html>',
    );
    $chave = '35240746970030000202570010000708221001256858';
    Storage::disk('local')->put(
        "comprovantes/{$user->id}/2026/07/CT-e {$chave} - espelho__01JCHAVE.html",
        '<html></html>',
    );

    $arquivos = app(ArquivoUsuarioService::class)->listar($user)->keyBy('path');

    expect($arquivos["comprovantes/{$user->id}/2026/07/CNDT 04252011000110__01JSOLTO.html"]['dono_documento'])
        ->toBe('04.252.011/0001-10')
        // CNPJ do emitente embutido na chave de acesso (posições 7-20).
        ->and($arquivos["comprovantes/{$user->id}/2026/07/CT-e {$chave} - espelho__01JCHAVE.html"]['dono_documento'])
        ->toBe('46.970.030/0002-02');
});

it('entrega somente a view parcial para navegação SPA', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get(route('app.arquivos.index'))
        ->assertOk()
        ->assertSee('Meus Arquivos')
        ->assertDontSee('<!DOCTYPE html>', false);
});
