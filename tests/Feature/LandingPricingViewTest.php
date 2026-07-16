<?php

use App\Services\PricingCatalogService;
use Database\Seeders\SubscriptionPlanSeeder;
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
        ->assertSee('10 GB para arquivos e comprovantes')
        ->assertSee('Validação')
        ->assertSee('Licitação')
        ->assertSee('Compliance')
        // Clearance tem preço ÚNICO por documento desde 2026-07-13: os dois cards são pontos de
        // entrada (acervo × chave), não tiers. Não existe mais "Básico" (que implicava um "Full").
        ->assertSee('Clearance do acervo')
        ->assertSee('Busca avulsa')
        ->assertDontSee('Clearance Básico')
        ->assertSee('mesmo preço por documento');

    expect(collect(app(PricingCatalogService::class)->getComplianceSources())->pluck('status')->unique()->all())
        ->toBe(['ativo']);
});

it('mantém todos os cards de assinatura na matriz comercial aprovada', function () {
    $this->seed(SubscriptionPlanSeeder::class);

    $html = get('/precos')->assertOk()->getContent();

    foreach ([
        'Essencial' => ['99', '990', '35,00', '2 acessos individuais incluídos'],
        'Profissional' => ['299', '2.990', '80,00', '3 acessos individuais incluídos'],
        'Escritório' => ['799', '7.990', '200,00', '10 acessos individuais incluídos'],
    ] as $nome => [$mensal, $anual, $saldo, $acessos]) {
        expect($html)->toContain($nome)
            ->toContain('data-monthly="'.$mensal.'.00"')
            ->toContain('data-annual-note="R$ '.$anual.' cobrados ao ano"')
            ->toContain("R$\u{A0}{$saldo} de saldo por mês")
            ->toContain($acessos);
    }

    expect($html)->toContain('Assento extra por R$ 39/mês via atendimento')
        ->toContain('1 acesso individual incluído')
        ->toContain('grid-template-columns: repeat(4, minmax(0, 1fr))')
        ->not->toContain('Certificado digital A1 disponível')
        ->not->toContain('R$&nbsp;249')
        ->not->toContain('R$&nbsp;599')
        ->not->toContain('Plano Enterprise');

    expect(substr_count($html, 'PDF executivo'))->toBe(4);
    expect($html)->toContain('PDF executivo com marca d’água');
    expect(substr_count($html, 'PDF executivo sem marca d’água'))->toBe(3);
});
