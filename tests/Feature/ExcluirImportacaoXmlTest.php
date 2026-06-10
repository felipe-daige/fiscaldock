<?php

use App\Models\Participante;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use App\Models\XmlNotaItem;
use App\Services\Xml\ExcluirImportacaoXmlService;
use App\Services\Xml\NfeXmlParser;
use App\Services\Xml\XmlNotaImporter;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function seedImportacaoXml(int $userId, string $fixture = '50240197551165000193550010000248021000214750-nfe.xml'): XmlImportacao
{
    $imp = XmlImportacao::create([
        'user_id' => $userId, 'tipo_documento' => 'NFE', 'modo_envio' => 'xml',
        'status' => 'concluido', 'iniciado_em' => now(),
    ]);
    $xml = file_get_contents(base_path('tests/Fixtures/nfe/'.$fixture));
    app(XmlNotaImporter::class)->importar(app(NfeXmlParser::class)->parse($xml), '', $imp);

    return $imp;
}

it('preview conta notas, itens e participantes órfãos', function () {
    $user = User::factory()->create();
    $imp = seedImportacaoXml($user->id);

    $preview = app(ExcluirImportacaoXmlService::class)->preview($imp);

    expect($preview['notas'])->toBe(1);
    expect($preview['itens'])->toBe(7);
    expect($preview['participantes']['candidatos'])->toBe(2);   // emit + dest
    expect($preview['participantes']['orfaos'])->toBe(2);       // nenhum compartilhado
    expect($preview['participantes']['compartilhados'])->toBe(0);
});

it('execute sem excluir participantes apaga notas/itens/importação e preserva participantes', function () {
    $user = User::factory()->create();
    $imp = seedImportacaoXml($user->id);

    $resultado = app(ExcluirImportacaoXmlService::class)->execute($imp, false);

    expect(XmlNota::where('user_id', $user->id)->count())->toBe(0);
    expect(XmlNotaItem::where('user_id', $user->id)->count())->toBe(0);
    expect(XmlImportacao::find($imp->id))->toBeNull();
    expect(Participante::where('user_id', $user->id)->count())->toBe(2);
    expect($resultado['participantes_excluidos'])->toBe(0);
    expect($resultado['participantes_preservados'])->toBe(2);
});

it('execute com excluir participantes remove os órfãos', function () {
    $user = User::factory()->create();
    $imp = seedImportacaoXml($user->id);

    $resultado = app(ExcluirImportacaoXmlService::class)->execute($imp, true);

    expect(Participante::where('user_id', $user->id)->count())->toBe(0);
    expect($resultado['participantes_excluidos'])->toBe(2);
});

it('preserva participante compartilhado com outra importação XML', function () {
    $user = User::factory()->create();
    // Todas as fixtures têm o mesmo emitente (97551165000193) → o participante emit é
    // compartilhado entre as duas importações.
    $imp = seedImportacaoXml($user->id, '50240197551165000193550010000248021000214750-nfe.xml');
    seedImportacaoXml($user->id, '50240197551165000193550010000248001000214739-nfe.xml');

    $emit = Participante::where('user_id', $user->id)->where('documento', '97551165000193')->firstOrFail();

    app(ExcluirImportacaoXmlService::class)->execute($imp, true);

    // O emitente, citado pela 2ª importação, sobrevive; o destinatário órfão da 1ª é apagado.
    expect(Participante::find($emit->id))->not->toBeNull();
});

it('endpoint destroy exclui e responde sucesso', function () {
    $user = User::factory()->create();
    $imp = seedImportacaoXml($user->id);

    $this->actingAs($user)
        ->deleteJson("/app/importacao/xml/{$imp->id}", ['excluir_participantes' => true])
        ->assertOk()->assertJson(['success' => true]);

    expect(XmlImportacao::find($imp->id))->toBeNull();
});

it('endpoint destroy bloqueia importação em processamento (409)', function () {
    $user = User::factory()->create();
    $imp = XmlImportacao::create(['user_id' => $user->id, 'tipo_documento' => 'NFE', 'modo_envio' => 'xml', 'status' => 'processando', 'iniciado_em' => now()]);

    $this->actingAs($user)
        ->deleteJson("/app/importacao/xml/{$imp->id}")
        ->assertStatus(409);

    expect(XmlImportacao::find($imp->id))->not->toBeNull();
});

it('não permite excluir importação de outro usuário', function () {
    $user = User::factory()->create();
    $outro = User::factory()->create();
    $imp = seedImportacaoXml($outro->id);

    $this->actingAs($user)
        ->deleteJson("/app/importacao/xml/{$imp->id}")
        ->assertNotFound();

    expect(XmlImportacao::find($imp->id))->not->toBeNull();
});
