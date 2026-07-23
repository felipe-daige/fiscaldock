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
    $this->actingAs($admin)->get('/app/admin/fontes')
        ->assertOk()
        ->assertSee('Catálogo de Consultas')
        ->assertSee('Dívida Ativa — Lista de Devedores PGFN')
        ->assertSee('Em manutenção')
        ->assertSee('GOV.BR, A1 ou conta externa');

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

it('rejeita preco abaixo do custo do provedor (guarda de margem)', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    config()->set('consultas.fontes.cnd_federal', 0.40);

    // R$ 0,00 é o caso mais grave: além do prejuízo por consulta, custo zero derruba o gate de
    // saldo (`hasEnough($user, 0)` é sempre true) e a fonte PAGA vira ilimitada e gratuita.
    $this->actingAs($admin)->post('/app/admin/fontes', [
        'precos' => ['cnd_federal' => '0'],
        'ativos' => ['cnd_federal' => '1'],
    ])->assertSessionHasErrors('precos');

    expect(FontePreco::where('chave', 'cnd_federal')->exists())->toBeFalse();

    // Igual ao custo passa (margem zero é decisão comercial, prejuízo não).
    $this->actingAs($admin)->post('/app/admin/fontes', [
        'precos' => ['cnd_federal' => '0.40'],
        'ativos' => ['cnd_federal' => '1'],
    ])->assertRedirect(route('app.admin.fontes.index'));

    expect((float) FontePreco::where('chave', 'cnd_federal')->first()->preco)->toBe(0.40);

    // Fonte de custo ZERO (cadastro/minhareceita) segue podendo ser R$ 0,00.
    $this->actingAs($admin)->post('/app/admin/fontes', [
        'precos' => ['cadastro' => '0', 'cnd_federal' => '1.00'],
        'ativos' => ['cadastro' => '1', 'cnd_federal' => '1'],
    ])->assertRedirect(route('app.admin.fontes.index'));
});

it('admin precifica e oculta fonte futura sem torna-la executavel', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->post('/app/admin/fontes', [
        'precos' => ['pgfn_devedores' => '2.90'],
        // pgfn ausente em ativos => publicada=false
        'ativos' => ['cadastro' => '1'],
    ])->assertRedirect(route('app.admin.fontes.index'));

    $linha = FontePreco::where('chave', 'pgfn_devedores')->first();
    expect($linha)->not->toBeNull()
        ->and((float) $linha->preco)->toBe(2.90)
        ->and($linha->ativo)->toBeFalse()
        ->and(app(CatalogoFontesAvulsas::class)->chavesDisponiveis())
        ->not->toContain('pgfn_devedores');
});
