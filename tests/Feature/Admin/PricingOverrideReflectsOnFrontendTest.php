<?php

use App\Models\FontePreco;
use App\Models\User;
use App\Services\Admin\ComercialParametroService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

/**
 * Regressão: os overrides do painel admin comercial
 * precisam REFLETIR nas telas, não só no backend. Antes, copy hardcoded ("R$ 50", "R$ 0,20")
 * ignorava o override. Ver bug 2026-06-22.
 *
 * Views da landing e do plano atualizadas na Task UI (pós Task 4 que mata faixas de volume).
 */
beforeEach(function () {
    (new ComercialParametroService)->definir('minimum_deposit', 80.00, null);
    (new ComercialParametroService)->definir('preco_compliance', 6.00, null);
});

it('a landing /precos reflete o depósito mínimo e o preço de produto do override', function () {
    get('/precos')
        ->assertOk()
        ->assertSee('Recarga mínima de R$&nbsp;80', false)
        ->assertSee('R$&nbsp;6,00', false)
        ->assertDontSee('R$&nbsp;5,00', false);
});

/**
 * A escada de planos saiu de /app/consulta/painel na migração escada→à la carte (2026-07-22): a
 * tela virou o seletor por FONTE, e o preço que ela mostra vem de `fonte_precos` (admin), não do
 * `preco_compliance` do painel comercial. O teste antigo assertava "R$ 6,00"/data-custo=6 nesta
 * mesma URL e passou a falhar por premissa morta — reescrito para o preço que a tela hoje exibe.
 * O override comercial segue coberto na landing /precos (teste acima).
 */
it('a tela /app/consulta/painel reflete o preço por fonte do override do admin', function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');

    FontePreco::create(['chave' => 'cnd_federal', 'preco' => 2.50, 'ativo' => true]);

    actingAs(User::factory()->create())
        ->get('/app/consulta/painel')
        ->assertOk()
        ->assertSee('data-preco="2.5"', false)
        ->assertSee("R$\u{A0}2,50");
});

it('a tela /app/planos mostra o saldo incluso em R$ (ledger é em reais)', function () {
    actingAs(User::factory()->create())
        ->get('/app/planos')
        ->assertOk()
        ->assertSee("R$\u{A0}35,00 em saldo/mês");  // essencial: R$ 35 inclusos, sem conversão
});
