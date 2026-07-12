<?php

use App\Services\PricingCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('apresenta o trial real separado da recarga paga', function () {
    get('/precos')
        ->assertOk()
        ->assertSee('Comece com', false)
        ->assertSee("R$\u{A0}20,00 grátis")
        ->assertSee('Recarga mínima de R$&nbsp;100', false)
        ->assertDontSee('R$&nbsp;100 para ativar o primeiro saldo', false);
});

it('apresenta planos, consultas CNPJ e clearance com os catálogos atuais', function () {
    get('/precos')
        ->assertOk()
        ->assertSee('Plano Essencial')
        ->assertSee('Plano Profissional')
        ->assertSee('Plano Escritório')
        ->assertSee('Validação')
        ->assertSee('Licitação')
        ->assertSee('Compliance')
        ->assertSee('Clearance Básico')
        ->assertSee('Busca avulsa');

    expect(collect(app(PricingCatalogService::class)->getComplianceSources())->pluck('status')->unique()->all())
        ->toBe(['ativo']);
});
