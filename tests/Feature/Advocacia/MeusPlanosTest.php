<?php

use App\Models\ConsultaKit;
use App\Models\User;
use App\Services\Advocacia\CatalogoFontesAvulsas;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
});

it('salva preset pessoal com user_id e preco = soma (sem desconto)', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/app/consulta/meus-planos', [
        'nome' => 'Diligência padrão',
        'fontes' => ['cnd_federal', 'cndt'],
    ])->assertOk()->assertJson([
        'success' => true,
        'preset' => ['nome' => 'Diligência padrão', 'preco_total' => 2.00],
    ]);

    $preset = ConsultaKit::where('nome', 'Diligência padrão')->first();
    expect($preset)->not->toBeNull()
        ->and((int) $preset->user_id)->toBe($user->id)
        ->and((float) $preset->desconto_percentual)->toBe(0.0)
        ->and($preset->fontes)->toBe(['cnd_federal', 'cndt']);
});

it('preset e visivel so pro dono e aparece no catalogo::presets', function () {
    $dono = User::factory()->create();
    $outro = User::factory()->create();

    ConsultaKit::create([
        'user_id' => $dono->id, 'nome' => 'Meu', 'slug' => 'meu-u'.$dono->id,
        'fontes' => ['cnd_federal'], 'desconto_percentual' => 0, 'ativo' => true, 'ordem' => 0,
    ]);

    $catalogo = app(CatalogoFontesAvulsas::class);
    expect($catalogo->presets($dono->id))->toHaveCount(1)
        ->and($catalogo->presets($outro->id))->toHaveCount(0);
});

it('exclui preset proprio; nao exclui de outro usuario (404)', function () {
    $dono = User::factory()->create();
    $outro = User::factory()->create();
    $preset = ConsultaKit::create([
        'user_id' => $dono->id, 'nome' => 'X', 'slug' => 'x-u'.$dono->id,
        'fontes' => ['cndt'], 'desconto_percentual' => 0, 'ativo' => true, 'ordem' => 0,
    ]);

    $this->actingAs($outro)->postJson("/app/consulta/meus-planos/{$preset->id}/excluir")->assertNotFound();
    expect(ConsultaKit::find($preset->id))->not->toBeNull();

    $this->actingAs($dono)->postJson("/app/consulta/meus-planos/{$preset->id}/excluir")->assertOk();
    expect(ConsultaKit::find($preset->id))->toBeNull();
});

it('recusa preset com fonte invalida (422)', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->postJson('/app/consulta/meus-planos', [
        'nome' => 'Ruim', 'fontes' => ['fonte_que_nao_existe'],
    ])->assertStatus(422);
});
