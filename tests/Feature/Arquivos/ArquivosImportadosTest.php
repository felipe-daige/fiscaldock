<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Services\Arquivos\ArquivoUsuarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    (new Database\Seeders\SubscriptionPlanSeeder)->run();

    $this->criarEfd = fn (User $user, string $conteudo = 'SPED-BRUTO', array $extra = []) => EfdImportacao::create(array_merge([
        'user_id' => $user->id,
        'tipo_efd' => 'EFD PIS/COFINS',
        'filename' => 'sped-jan.txt',
        'status' => 'concluido',
        'iniciado_em' => now(),
        'arquivo_base64' => base64_encode($conteudo),
    ], $extra));

    $this->criarXml = fn (User $user, array $extra = []) => XmlImportacao::create(array_merge([
        'user_id' => $user->id,
        'tipo_documento' => 'NFE',
        'filename' => 'notas-fevereiro.zip',
        'modo_envio' => 'zip',
        'total_arquivos' => 1,
        'total_xmls' => 3,
        'tamanho_total_bytes' => 4096,
        'status' => 'concluido',
        'iniciado_em' => now(),
    ], $extra));

    $this->quotaDe1Mb = function () {
        $free = SubscriptionPlan::where('codigo', 'free')->firstOrFail();
        $free->capabilities = array_merge($free->capabilities ?? [], ['armazenamento_mb' => 1]);
        $free->save();
    };
});

it('lista importações EFD e XML com peso, dono e link de origem', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'nome' => 'Empresa Importada Ltda',
        'documento' => '04252011000110',
    ]);
    $efd = ($this->criarEfd)($user, str_repeat('X', 3000), ['cliente_id' => $cliente->id]);
    $xml = ($this->criarXml)($user, ['cliente_id' => $cliente->id]);

    $arquivos = app(ArquivoUsuarioService::class)->listar($user)->keyBy('path');

    $itemEfd = $arquivos["importacao/efd/{$efd->id}"];
    expect($itemEfd['origem'])->toBe('importacao')
        ->and($itemEfd['nome'])->toBe('sped-jan.txt')
        ->and($itemEfd['baixavel'])->toBeTrue()
        ->and($itemEfd['previewavel'])->toBeFalse()
        ->and($itemEfd['pode_excluir'])->toBeFalse()
        ->and($itemEfd['tamanho_bytes'])->toBe(3000)
        ->and($itemEfd['extensao'])->toBe('TXT')
        ->and($itemEfd['historico_url'])->toBe(route('app.importacao.efd.detalhes', $efd->id))
        ->and($itemEfd['dono_documento'])->toBe('04.252.011/0001-10')
        ->and($itemEfd['dono_nome'])->toBe('Empresa Importada Ltda');

    $itemXml = $arquivos["importacao/xml/{$xml->id}"];
    expect($itemXml['baixavel'])->toBeFalse()
        ->and($itemXml['tamanho_bytes'])->toBe(4096)
        ->and($itemXml['historico_url'])->toBe(route('app.importacao.xml.detalhes', $xml->id))
        ->and($itemXml['mime_type'])->toContain('3 nota(s)');

    $this->actingAs($user)
        ->get(route('app.arquivos.index', ['origem' => 'importacao']))
        ->assertOk()
        ->assertSee('sped-jan.txt')
        ->assertSee('notas-fevereiro.zip')
        ->assertSee('Importação')
        ->assertSee('2 resultado(s)');
});

it('baixa o SPED original da importação EFD e nega download/preview do lote XML', function () {
    $user = User::factory()->create();
    $conteudo = "|0000|006|0|01022024|29022024|EMPRESA|12345678000100|\r\n|9999|2|\r\n";
    $efd = ($this->criarEfd)($user, $conteudo);
    $xml = ($this->criarXml)($user);
    $service = app(ArquivoUsuarioService::class);

    $resposta = $this->actingAs($user)
        ->get(route('app.arquivos.download', $service->identificador("importacao/efd/{$efd->id}")))
        ->assertOk()
        ->assertDownload('sped-jan.txt');
    expect($resposta->streamedContent())->toBe($conteudo);

    $this->actingAs($user)
        ->get(route('app.arquivos.download', $service->identificador("importacao/xml/{$xml->id}")))
        ->assertNotFound();

    $this->actingAs($user)
        ->get(route('app.arquivos.preview', $service->identificador("importacao/efd/{$efd->id}")))
        ->assertNotFound();
});

it('não expõe importação de outro usuário', function () {
    $dono = User::factory()->create();
    $intruso = User::factory()->create();
    $efd = ($this->criarEfd)($dono);
    $service = app(ArquivoUsuarioService::class);

    expect($service->listar($intruso))->toBeEmpty();

    $this->actingAs($intruso)
        ->get(route('app.arquivos.download', $service->identificador("importacao/efd/{$efd->id}")))
        ->assertNotFound();
});

it('quota soma o peso das importações e bloqueia upload manual quando cheia', function () {
    ($this->quotaDe1Mb)();
    $user = User::factory()->create();
    ($this->criarEfd)($user, str_repeat('A', 1200 * 1024));

    $resumo = app(ArquivoUsuarioService::class)->resumo($user);
    expect($resumo['usado_bytes'])->toBeGreaterThan(1024 * 1024)
        ->and($resumo['total_importados'])->toBe(1);

    $this->actingAs($user)
        ->post(route('app.arquivos.store'), [
            'arquivos' => [UploadedFile::fake()->create('doc.pdf', 20, 'application/pdf')],
        ])
        ->assertSessionHasErrors('arquivos');
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

it('bloqueia importação EFD nova quando a quota está cheia', function () {
    config([
        'services.webhook.importacao_efd_fiscal_url' => 'https://n8n.example.com/icms',
        'services.webhook.importacao_efd_contribuicoes_url' => 'https://n8n.example.com/contrib',
    ]);
    Http::fake(['*' => Http::response(['ok' => true], 200)]);
    ($this->quotaDe1Mb)();
    $user = User::factory()->create();
    ($this->criarEfd)($user, str_repeat('A', 1200 * 1024));

    $sped = "|0000|006|0|01032024|31032024|EMPRESA|12345678000100|MG|123|3106200|0|0|\r\n|9999|2|\r\n";
    $this->actingAs($user)
        ->postJson('/app/importacao/efd/importar-txt', [
            'arquivo' => UploadedFile::fake()->createWithContent('sped-marco.txt', $sped),
            'tipo_efd' => 'EFD PIS/COFINS',
        ])
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error', fn ($erro) => str_contains((string) $erro, 'espaço'));

    Http::assertNothingSent();
    expect(EfdImportacao::where('user_id', $user->id)->count())->toBe(1);
});

it('bloqueia importação XML nova quando a quota está cheia', function () {
    ($this->quotaDe1Mb)();
    $user = User::factory()->create();
    ($this->criarEfd)($user, str_repeat('A', 1200 * 1024));

    $this->actingAs($user)
        ->postJson(route('app.importacao.xml.importar'), [
            'tipo_documento' => 'NFE',
            'modo_envio' => 'xml',
            'decidir_depois' => true,
            'tab_id' => 'tab-quota',
            'arquivos' => [[
                'nome' => 'nota.xml',
                'tipo' => 'text/xml',
                'conteudo_base64' => base64_encode('<?xml version="1.0"?><nfe/>'),
            ]],
        ])
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error', fn ($erro) => str_contains((string) $erro, 'espaço'));

    expect(XmlImportacao::count())->toBe(0);
});
