<?php

use App\Models\CreditTransaction;
use App\Models\User;
use App\Services\PricingCatalogService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('a rota /app/faixa-comercial renderiza e a antiga /app/plano não existe', function () {
    $user = User::factory()->create();
    actingAs($user);

    get('/app/faixa-comercial')->assertOk();
    get('/app/plano')->assertNotFound();
});

it('a página explica a faixa e mostra a matriz de custo por faixa', function () {
    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/faixa-comercial')->assertOk()->getContent();

    expect($html)->toContain('Faixa Comercial');
    expect($html)->toContain('Custo por consulta em cada faixa'); // matriz comparativa
    expect($html)->toContain('Como funciona a faixa comercial');
    expect($html)->toContain('sua faixa'); // coluna da faixa atual destacada
});

it('a faixa dá desconto REAL: faixa Z consome menos créditos que a Base', function () {
    $catalog = new PricingCatalogService;

    $base = User::factory()->create();
    // Faixa Z = 20.000+ créditos pagos acumulados.
    $z = User::factory()->create();
    CreditTransaction::create([
        'user_id' => $z->id, 'amount' => 20000, 'balance_after' => 20000, 'type' => 'purchase',
    ]);

    foreach (['validacao' => [5, 4], 'licitacao' => [10, 8], 'compliance' => [18, 14], 'due_diligence' => [35, 28]] as $produto => [$esperadoBase, $esperadoZ]) {
        expect($catalog->getProductCreditsForUser($produto, $base))->toBe($esperadoBase);
        expect($catalog->getProductCreditsForUser($produto, $z))->toBe($esperadoZ);
        expect($catalog->getProductCreditsForUser($produto, $z))
            ->toBeLessThan($catalog->getProductCreditsForUser($produto, $base));
    }
});
