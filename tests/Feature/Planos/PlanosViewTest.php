<?php

use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SubscriptionPlanSeeder::class);
});

it('renderiza os 4 tiers ativos com preços, saldo e assentos aprovados', function () {
    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/planos')->assertOk()->getContent();

    // Nomes dos tiers
    foreach (['Free', 'Essencial', 'Profissional', 'Escritório'] as $nome) {
        expect($html)->toContain($nome);
    }
    expect($html)->not->toContain('Enterprise');

    // Preços e saldo incluso canônicos (CFO). Preço vem hardcoded na view
    // como "R$&nbsp;{n}"; saldo/mês vem de Dinheiro::brl (NBSP real).
    expect($html)->toContain('R$&nbsp;99');
    expect($html)->toContain('R$&nbsp;299');
    expect($html)->toContain('R$&nbsp;799');
    expect($html)->toContain(\App\Support\Dinheiro::brl(35).' em saldo/mês');
    expect($html)->toContain(\App\Support\Dinheiro::brl(80).' em saldo/mês');
    expect($html)->toContain(\App\Support\Dinheiro::brl(200).' em saldo/mês');
});

it('marca o Free como plano atual de quem não tem assinatura e oferece Assinar nos pagos', function () {
    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/planos')->assertOk()->getContent();

    expect($html)->toContain('Plano atual');     // Free é o atual
    expect($html)->toContain('data-assinar');    // botão real de assinar nos pagos (Fase 4)
    expect($html)->not->toContain('Falar com vendas');
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
    expect($html)->toContain(\App\Support\Dinheiro::brl(35).' em saldo/mês');
});

it('expõe uso pago sem teto e a proteção do gratuito', function () {
    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/planos')->assertOk()->getContent();

    expect($html)->toContain('Clientes e participantes sem limite de cadastro');
    expect($html)->toContain('1 CNPJ no monitoramento cadastral gratuito');
    expect($html)->toContain('Monitoramentos pagos sem teto comercial');
    expect($html)->toContain('1 acesso individual incluído');
    expect($html)->toContain('2 acessos individuais incluídos');
    expect($html)->toContain('3 acessos individuais incluídos');
    expect($html)->toContain('10 acessos individuais incluídos');
    expect($html)->toContain('Assento extra por R$ 39/mês via atendimento');
    expect(substr_count($html, 'PDF executivo'))->toBe(4);
    expect($html)->toContain('PDF executivo com marca d’água');
    expect(substr_count($html, 'PDF executivo sem marca d’água'))->toBe(3);
    expect($html)->not->toContain('Certificado digital A1 em qualquer plano');
    expect($html)->toContain('xl:grid-cols-4');
});

it('expõe o armazenamento incluído em cada tier', function () {
    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/planos')->assertOk()->getContent();

    expect($html)->toContain('250 MB de armazenamento');
    expect($html)->toContain('2 GB de armazenamento');
    expect($html)->toContain('10 GB de armazenamento');
    expect($html)->toContain('50 GB de armazenamento');
    expect($html)->not->toContain('200 GB de armazenamento');
});
