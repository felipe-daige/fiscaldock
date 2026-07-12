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

    // Preços e créditos inclusos canônicos (CFO). Preço vem hardcoded na view
    // como "R$&nbsp;{n}"; saldo/mês vem de Dinheiro::brl (NBSP real).
    expect($html)->toContain('R$&nbsp;99');
    expect($html)->toContain('R$&nbsp;299');
    expect($html)->toContain('R$&nbsp;799');
    expect($html)->toContain(\App\Support\Dinheiro::brl(60).' em saldo/mês');
    expect($html)->toContain(\App\Support\Dinheiro::brl(220).' em saldo/mês');
    expect($html)->toContain(\App\Support\Dinheiro::brl(600).' em saldo/mês');
});

it('marca o Free como plano atual de quem não tem assinatura e oferece Assinar nos pagos', function () {
    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/planos')->assertOk()->getContent();

    expect($html)->toContain('Plano atual');     // Free é o atual
    expect($html)->toContain('data-assinar');    // botão real de assinar nos pagos (Fase 4)
    expect($html)->toContain('Falar com vendas'); // enterprise
    expect($html)->not->toContain('Assinar — em breve'); // placeholder antigo removido
});

it('renderiza os tiers mesmo se a tabela estiver vazia (fallback resiliente)', function () {
    \App\Models\SubscriptionPlan::query()->delete(); // simula seed ausente em prod

    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/planos')->assertOk()->getContent();

    foreach (['Essencial', 'Profissional', 'Escritório'] as $nome) {
        expect($html)->toContain($nome);
    }
    expect($html)->toContain(\App\Support\Dinheiro::brl(60).' em saldo/mês');
});

it('expõe os limites de carteira por tier (clientes/CNPJs)', function () {
    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/planos')->assertOk()->getContent();

    expect($html)->toContain('15 clientes monitorados');  // essencial
    expect($html)->toContain('40 CNPJs monitorados');      // profissional
    expect($html)->toContain('Clientes ilimitados');       // enterprise
});
