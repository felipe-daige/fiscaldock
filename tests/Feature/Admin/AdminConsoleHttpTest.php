<?php

use App\Models\AdminPendencia;
use App\Models\IntegracaoStatus;
use App\Models\User;
use App\Services\Admin\AdminArmazenamentoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('bloqueia visitante no admin (redirect login)', function () {
    $this->get('/app/admin')->assertRedirect(route('login'));
});

it('bloqueia usuário não-admin com 403', function () {
    $u = User::factory()->create(['is_admin' => false]);
    actingAs($u)->get('/app/admin')->assertStatus(403);
});

it('admin vê o dashboard de analytics', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    AdminPendencia::create([
        'titulo' => 'Pendência vencida',
        'status' => AdminPendencia::STATUS_ABERTA,
        'criado_por' => $admin->id,
        'lembrar_em' => now()->subDay(),
    ]);
    IntegracaoStatus::create([
        'chave' => 'teste_dashboard',
        'nome' => 'Integração Teste',
        'grupo' => IntegracaoStatus::GRUPO_PLATAFORMA,
        'ordem' => 1,
        'status' => IntegracaoStatus::STATUS_DEGRADADO,
    ]);
    $this->mock(AdminArmazenamentoService::class)
        ->shouldReceive('medirDisco')
        ->once()
        ->andReturn([
            'disponivel' => true,
            'total_formatado' => '100 GB',
            'usado_formatado' => '60 GB',
            'livre_formatado' => '40 GB',
            'percentual' => 60.0,
            'status' => 'saudavel',
            'status_label' => 'Saudável',
            'status_cor' => '#047857',
        ]);

    $resposta = actingAs($admin)->get('/app/admin')->assertOk();
    $html = $resposta->getContent();

    expect($html)->toContain('Visão Geral')
        ->toContain('Resumo do negócio')
        ->toContain('Operação agora')
        ->toContain('2 ponto(s) de atenção')
        ->toContain('60 GB de 100 GB')
        ->toContain('Comercial e conversão')
        ->toContain('Uso do produto')
        ->toContain(route('app.admin.armazenamento.index'))
        ->toContain(route('app.admin.pendencias.index'))
        ->toContain(route('app.admin.integracoes.index'))
        ->not->toContain('apexcharts.min.js');
});

it('entrega somente o partial da visão geral para navegação SPA', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    actingAs($admin)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get('/app/admin')
        ->assertOk()
        ->assertSee('Operação agora')
        ->assertDontSee('<!DOCTYPE html>', false);
});
