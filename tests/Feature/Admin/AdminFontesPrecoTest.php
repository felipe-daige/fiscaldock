<?php

use App\Models\FontePreco;
use App\Models\User;
use App\Services\Advocacia\CatalogoFontesAvulsas;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
});

it('override de preco no banco (fonte_precos) vence o default do config', function () {
    // Sem linha: cai no default R$ 1,00.
    expect(app(CatalogoFontesAvulsas::class)->precoDe('cnd_federal'))->toBe(1.00);

    FontePreco::create(['chave' => 'cnd_federal', 'preco' => 2.50, 'ativo' => true]);

    // Instância nova (memo por request): override do banco manda.
    expect(app(CatalogoFontesAvulsas::class)->precoDe('cnd_federal'))->toBe(2.50);
});

it('fonte desativada no admin (ativo=false) some do catalogo de selecao', function () {
    $catalogo = app(CatalogoFontesAvulsas::class);
    expect($catalogo->chavesDisponiveis())->toContain('cnd_federal');

    FontePreco::create(['chave' => 'cnd_federal', 'preco' => 1.00, 'ativo' => false]);

    expect(app(CatalogoFontesAvulsas::class)->chavesDisponiveis())->not->toContain('cnd_federal');
});

it('admin salva preco e desativa fonte; nao-admin bloqueado', function () {
    $comum = User::factory()->create(['is_admin' => false]);
    $this->actingAs($comum)->get('/app/admin/fontes')->assertStatus(403);

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->get('/app/admin/fontes')->assertOk()->assertSee('Preço das Consultas');

    $this->actingAs($admin)->post('/app/admin/fontes', [
        'precos' => ['cnd_federal' => '3.00', 'cndt' => ''],
        'ativos' => ['cnd_federal' => '1'], // cndt SEM checkbox = desativada
    ])->assertRedirect(route('app.admin.fontes.index'));

    // cnd_federal: preco custom + ativa.
    $fed = FontePreco::where('chave', 'cnd_federal')->first();
    expect((float) $fed->preco)->toBe(3.00)->and($fed->ativo)->toBeTrue();

    // cndt: sem preco custom mas desativada → materializa com preco efetivo + ativo=false.
    $cndt = FontePreco::where('chave', 'cndt')->first();
    expect($cndt)->not->toBeNull()->and($cndt->ativo)->toBeFalse();
});
