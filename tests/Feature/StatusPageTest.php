<?php

use App\Models\IntegracaoStatus;
use App\Models\User;
use Database\Seeders\IntegracaoStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('redireciona visitante para login', function () {
    $this->get('/app/status')->assertRedirect(route('login'));
});

it('usuário autenticado não-admin vê a página de status', function () {
    (new IntegracaoStatusSeeder)->run();
    IntegracaoStatus::where('chave', 'cnd_federal')->update(['status' => 'fora', 'mensagem' => 'Receita instável']);

    $html = actingAs(User::factory()->create(['is_admin' => false]))
        ->get('/app/status')->assertOk()->getContent();

    expect($html)->toContain('CND Federal (Receita/PGFN)');
    expect($html)->toContain('Fora do ar');
    expect($html)->toContain('Receita instável');
});

it('banner geral verde quando tudo operacional', function () {
    (new IntegracaoStatusSeeder)->run();

    $html = actingAs(User::factory()->create())
        ->get('/app/status')->assertOk()->getContent();

    expect($html)->toContain('Todos os sistemas operacionais');
    expect($html)->toContain('#047857');
    expect($html)->not->toContain('com problema');
});

it('banner geral reporta problemas com a cor do pior status', function () {
    (new IntegracaoStatusSeeder)->run();
    IntegracaoStatus::where('chave', 'cnd_federal')->update(['status' => 'fora']);
    IntegracaoStatus::where('chave', 'sintegra')->update(['status' => 'degradado']);

    $html = actingAs(User::factory()->create())
        ->get('/app/status')->assertOk()->getContent();

    expect($html)->toContain('2 serviços com problema');
    expect($html)->not->toContain('Todos os sistemas operacionais');
    // pior status = fora → banner vermelho
    expect($html)->toContain('background-color: #dc2626');
});

it('banner geral singulariza 1 serviço com problema', function () {
    (new IntegracaoStatusSeeder)->run();
    IntegracaoStatus::where('chave', 'sintegra')->update(['status' => 'manutencao']);

    $html = actingAs(User::factory()->create())
        ->get('/app/status')->assertOk()->getContent();

    expect($html)->toContain('1 serviço com problema');
});
