<?php

use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SubscriptionPlanSeeder::class);
});

it('renderiza os 5 tiers do seeder com preços e créditos inclusos da doc CFO', function () {
    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/planos')->assertOk()->getContent();

    // Nomes dos tiers
    foreach (['Free', 'Essencial', 'Profissional', 'Escritório', 'Enterprise'] as $nome) {
        expect($html)->toContain($nome);
    }

    // Preços e créditos inclusos canônicos (CFO)
    expect($html)->toContain('R$ 99');
    expect($html)->toContain('R$ 299');
    expect($html)->toContain('R$ 799');
    expect($html)->toContain('300 créditos inclusos/mês');
    expect($html)->toContain('1.100 créditos inclusos/mês');
    expect($html)->toContain('3.000 créditos inclusos/mês');
});

it('marca o Free como plano atual de quem não tem assinatura e mostra Assinar em breve nos pagos', function () {
    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/planos')->assertOk()->getContent();

    expect($html)->toContain('Plano atual');        // Free é o atual
    expect($html)->toContain('Assinar — em breve');  // pagos ainda sem preapproval
    expect($html)->toContain('Falar com vendas');    // enterprise
});

it('renderiza os tiers mesmo se a tabela estiver vazia (fallback resiliente)', function () {
    \App\Models\SubscriptionPlan::query()->delete(); // simula seed ausente em prod

    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/planos')->assertOk()->getContent();

    foreach (['Essencial', 'Profissional', 'Escritório'] as $nome) {
        expect($html)->toContain($nome);
    }
    expect($html)->toContain('300 créditos inclusos/mês');
});

it('expõe os limites de carteira por tier (clientes/CNPJs)', function () {
    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/planos')->assertOk()->getContent();

    expect($html)->toContain('15 clientes monitorados');  // essencial
    expect($html)->toContain('40 CNPJs monitorados');      // profissional
    expect($html)->toContain('Clientes ilimitados');       // enterprise
});
