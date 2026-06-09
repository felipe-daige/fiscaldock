<?php

use App\Models\Cliente;
use App\Models\Participante;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use App\Services\Xml\NfeXmlParser;
use App\Services\Xml\XmlNotaImporter;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function parsedFixture(): array
{
    $xml = file_get_contents(base_path('tests/Fixtures/nfe/50240197551165000193550010000248021000214750-nfe.xml'));
    return (new NfeXmlParser())->parse($xml);
}

function novaImportacaoXml(User $u, ?int $clienteId = null): XmlImportacao
{
    return XmlImportacao::create([
        'user_id' => $u->id, 'cliente_id' => $clienteId,
        'tipo_documento' => 'NFE', 'modo_envio' => 'zip',
        'status' => 'processando', 'iniciado_em' => now(),
    ]);
}

it('importa nota nova, cria itens e classifica saída quando o dono é o emitente', function () {
    $user = User::factory()->create();
    $imp  = novaImportacaoXml($user);
    $parsed = parsedFixture(); // emit=97551165000193

    $status = app(XmlNotaImporter::class)->importar($parsed, '97551165000193', $imp);

    expect($status)->toBe('novo');
    $nota = XmlNota::where('user_id', $user->id)->first();
    expect($nota->tipo_nota)->toBe(XmlNota::TIPO_SAIDA);
    expect($nota->itens()->count())->toBe(7);
    expect($nota->emit_participante_id)->not->toBeNull();
    expect($nota->dest_participante_id)->not->toBeNull();
});

it('classifica entrada quando o dono é o destinatário', function () {
    $user = User::factory()->create();
    $imp  = novaImportacaoXml($user);

    app(XmlNotaImporter::class)->importar(parsedFixture(), '44373108000600', $imp);

    expect(XmlNota::where('user_id', $user->id)->first()->tipo_nota)->toBe(XmlNota::TIPO_ENTRADA);
});

it('faz dedup por chave: segunda importação retorna duplicado e não duplica itens', function () {
    $user = User::factory()->create();
    $imp1 = novaImportacaoXml($user);
    app(XmlNotaImporter::class)->importar(parsedFixture(), '97551165000193', $imp1);

    $imp2 = novaImportacaoXml($user);
    $status = app(XmlNotaImporter::class)->importar(parsedFixture(), '97551165000193', $imp2);

    expect($status)->toBe('duplicado');
    expect(XmlNota::where('user_id', $user->id)->count())->toBe(1);
    expect(XmlNota::where('user_id', $user->id)->first()->itens()->count())->toBe(7);
});

it('backfilla protNFe quando a nota existia sem protocolo', function () {
    $user = User::factory()->create();
    $imp1 = novaImportacaoXml($user);
    $semProt = parsedFixture();
    $semProt['header']['protocolo_autorizacao'] = null;
    $semProt['header']['status_autorizacao'] = null;
    app(XmlNotaImporter::class)->importar($semProt, '97551165000193', $imp1);

    $imp2 = novaImportacaoXml($user);
    $status = app(XmlNotaImporter::class)->importar(parsedFixture(), '97551165000193', $imp2);

    expect($status)->toBe('duplicado_atualizado');
    expect(XmlNota::where('user_id', $user->id)->first()->status_autorizacao)->toBe('100');
});

it('marca sem_dono quando o owner não aparece em nenhum lado', function () {
    $user = User::factory()->create();
    $imp  = novaImportacaoXml($user);

    $status = app(XmlNotaImporter::class)->importar(parsedFixture(), '99999999999999', $imp);

    expect($status)->toBe('sem_dono');
    $nota = XmlNota::where('user_id', $user->id)->first();
    expect($nota->payload['_dono_ausente'])->toBeTrue();
});

it('liga emit_cliente_id quando o documento casa com cliente existente', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id, 'documento' => '97551165000193',
        'razao_social' => 'HIDRATOP', 'is_empresa_propria' => true,
    ]);
    $imp = novaImportacaoXml($user, $cliente->id);

    app(XmlNotaImporter::class)->importar(parsedFixture(), '97551165000193', $imp);

    expect(XmlNota::where('user_id', $user->id)->first()->emit_cliente_id)->toBe($cliente->id);
});

// --- Modo AUTO (ownerDoc nulo): infere o dono pelo cliente que casa ---

it('auto: empresa própria no emitente classifica como saída', function () {
    $user = User::factory()->create();
    Cliente::create(['user_id' => $user->id, 'documento' => '97551165000193', 'razao_social' => 'HIDRATOP', 'is_empresa_propria' => true]);
    $imp = novaImportacaoXml($user);

    $status = app(XmlNotaImporter::class)->importar(parsedFixture(), null, $imp);

    expect($status)->toBe('novo');
    expect(XmlNota::where('user_id', $user->id)->first()->tipo_nota)->toBe(XmlNota::TIPO_SAIDA);
});

it('auto: empresa própria no destinatário classifica como entrada', function () {
    $user = User::factory()->create();
    Cliente::create(['user_id' => $user->id, 'documento' => '44373108000600', 'razao_social' => 'COCAL', 'is_empresa_propria' => true]);
    $imp = novaImportacaoXml($user);

    app(XmlNotaImporter::class)->importar(parsedFixture(), null, $imp);

    expect(XmlNota::where('user_id', $user->id)->first()->tipo_nota)->toBe(XmlNota::TIPO_ENTRADA);
});

it('auto: cliente comum (não própria) só no emitente classifica como saída', function () {
    $user = User::factory()->create();
    Cliente::create(['user_id' => $user->id, 'documento' => '97551165000193', 'razao_social' => 'HIDRATOP', 'is_empresa_propria' => false]);
    $imp = novaImportacaoXml($user);

    app(XmlNotaImporter::class)->importar(parsedFixture(), null, $imp);

    expect(XmlNota::where('user_id', $user->id)->first()->tipo_nota)->toBe(XmlNota::TIPO_SAIDA);
});

it('auto: nenhum lado cadastrado marca sem_dono e flag _dono_ausente', function () {
    $user = User::factory()->create();
    $imp = novaImportacaoXml($user);

    $status = app(XmlNotaImporter::class)->importar(parsedFixture(), null, $imp);

    expect($status)->toBe('sem_dono');
    expect(XmlNota::where('user_id', $user->id)->first()->payload['_dono_ausente'])->toBeTrue();
});

it('auto: empresa própria vence quando os dois lados são clientes', function () {
    $user = User::factory()->create();
    Cliente::create(['user_id' => $user->id, 'documento' => '97551165000193', 'razao_social' => 'HIDRATOP', 'is_empresa_propria' => false]);
    Cliente::create(['user_id' => $user->id, 'documento' => '44373108000600', 'razao_social' => 'COCAL', 'is_empresa_propria' => true]);
    $imp = novaImportacaoXml($user);

    // dest (COCAL) é a empresa própria → entrada
    app(XmlNotaImporter::class)->importar(parsedFixture(), null, $imp);

    expect(XmlNota::where('user_id', $user->id)->first()->tipo_nota)->toBe(XmlNota::TIPO_ENTRADA);
});
