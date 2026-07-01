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
    (new IntegracaoStatusSeeder())->run();
    IntegracaoStatus::where('chave', 'cnd_federal')->update(['status' => 'fora', 'mensagem' => 'Receita instável']);

    $html = actingAs(User::factory()->create(['is_admin' => false]))
        ->get('/app/status')->assertOk()->getContent();

    expect($html)->toContain('CND Federal (Receita/PGFN)');
    expect($html)->toContain('Fora do ar');
    expect($html)->toContain('Receita instável');
});
