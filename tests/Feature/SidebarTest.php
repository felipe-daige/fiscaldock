<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Sidebar: Monitoramento é um item único que leva ao painel (2026-07-04). A antiga
 * submenu (Painel/Clientes/Histórico) foi consolidada — histórico e trava vivem no painel.
 */
it('sidebar expõe Monitoramento como item único apontando pro painel', function () {
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)
        ->toContain('Monitoramento')
        ->toContain('href="/app/monitoramento/painel" data-link data-sidebar-link')
        ->not->toContain('/app/monitoramento/clientes')   // submenu removido
        ->not->toContain('/app/monitoramento/historico');  // alcançado por botão no painel
});

it('sidebar leva direto às listagens e mantém o cadastro nos cabeçalhos', function () {
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)
        ->toContain('href="/app/clientes" data-link data-sidebar-link')
        ->toContain('href="/app/participantes" data-link data-sidebar-link')
        ->not->toContain('href="/app/cliente/novo"')
        ->not->toContain('href="/app/participante/novo"');

    actingAs($user)->get('/app/clientes')
        ->assertOk()
        ->assertSee('href="/app/cliente/novo"', false)
        ->assertSee('Novo Cliente');

    actingAs($user)->get('/app/participantes')
        ->assertOk()
        ->assertSee('href="/app/participante/novo"', false)
        ->assertSee('Novo Participante');
});

it('sidebar esconde Buscar Notas quando a busca avulsa está desabilitada', function () {
    config()->set('clearance.busca_avulsa.habilitada', false);
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)->not->toContain('/app/clearance/buscar');
});

it('sidebar mostra Buscar Notas quando a busca avulsa está habilitada', function () {
    config()->set('clearance.busca_avulsa.habilitada', true);
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)->toContain('/app/clearance/buscar');
});

it('sidebar leva direto a nova consulta e mantém historico/planos no cabeçalho da tela', function () {
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)
        ->toContain('href="/app/consulta/painel" data-link data-sidebar-link')
        ->not->toContain('href="/app/consulta/historico" data-link data-sidebar-link')
        ->not->toContain('href="/app/consulta/planos" data-link data-sidebar-link');

    actingAs($user)->get('/app/consulta/painel')
        ->assertOk()
        ->assertSee('href="/app/consulta/historico"', false)
        ->assertSee('href="/app/consulta/planos"', false);
});

it('esconde o pill quando pill-until já passou', function () {
    $vencido = \Illuminate\Support\Facades\Blade::render(
        '<x-sidebar.item href="/x" pill="Novo" pill-until="2020-01-01">X</x-sidebar.item>'
    );
    $vigente = \Illuminate\Support\Facades\Blade::render(
        '<x-sidebar.item href="/x" pill="Novo" pill-until="2999-01-01">X</x-sidebar.item>'
    );
    $grupoVencido = \Illuminate\Support\Facades\Blade::render(
        '<x-sidebar.group-item href="/x" pill="Novo" pill-until="2020-01-01">X</x-sidebar.group-item>'
    );

    expect($vencido)->not->toContain('Novo')
        ->and($vigente)->toContain('Novo')
        ->and($grupoVencido)->not->toContain('Novo');
});

it('sidebar não marca mais Importação XML como novo', function () {
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    // O item XML continua, mas sem pill "Novo" (live desde 2026-06)
    expect($html)->toContain('href="/app/importacao/xml"');
    $trechoXml = substr($html, strpos($html, 'href="/app/importacao/xml"'), 400);
    expect($trechoXml)->not->toContain('Novo');
});

it('sidebar traz os elementos de scroll fade no nav', function () {
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)->toContain('sidebar__nav-wrap')
        ->toContain('sidebar__nav-fade--top')
        ->toContain('sidebar__nav-fade--bottom');
});

it('sidebar funde Clearance como grupo dentro de INTELIGÊNCIA', function () {
    $user = User::factory()->trialAtivo()->create();

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)
        // seção própria morreu
        ->not->toContain('<div class="sidebar__section-title">CLEARANCE NF-e</div>')
        // virou grupo expansível (título do grupo)
        ->toContain('Clearance NF-e')
        // destinos agora são group-items
        ->toContain('href="/app/clearance/dashboard"')
        ->toContain('href="/app/clearance/notas"');

    // /app/clearance/dashboard aparece como group-item, não como item de seção
    $trecho = substr($html, strpos($html, 'href="/app/clearance/dashboard"') - 200, 400);
    expect($trecho)->toContain('data-sidebar-group-item');
});

it('menu do usuário agrupa em blocos Conta/Financeiro e esconde Admin de não-admin', function () {
    $user = User::factory()->trialAtivo()->create(['is_admin' => false]);

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)
        ->toContain('sidebar__user-menu-heading')
        ->toContain('>Conta</div>')
        ->toContain('>Financeiro</div>')
        ->not->toContain('>Admin</div>');
});

it('menu do usuário mostra bloco Admin para admin', function () {
    $user = User::factory()->trialAtivo()->create(['is_admin' => true]);

    $html = actingAs($user)->get('/app/dashboard')->assertOk()->getContent();

    expect($html)->toContain('>Admin</div>')
        ->toContain('href="/app/admin"');
});
