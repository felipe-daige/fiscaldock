<?php

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('dry-run lista os pedidos de exclusão sem alterar nada', function () {
    $user = User::factory()->create([
        'email' => 'quer-sair@example.com',
        'deletion_requested_at' => now()->subDays(40),
    ]);

    $this->artisan('lgpd:processar-exclusoes')
        ->expectsOutputToContain('quer-sair@example.com')
        ->assertExitCode(0);

    $fresh = $user->fresh();
    expect($fresh->anonimizado_em)->toBeNull();
    expect($fresh->email)->toBe('quer-sair@example.com');
});

it('--force anonimiza a PII do titular e marca anonimizado_em', function () {
    $user = User::factory()->create([
        'name' => 'Fulano',
        'email' => 'quer-sair@example.com',
        'telefone' => '67999990000',
        'cnpj' => '11222333000181',
        'deletion_requested_at' => now()->subDays(40),
    ]);

    $this->artisan('lgpd:processar-exclusoes --force')->assertExitCode(0);

    $fresh = $user->fresh();
    expect($fresh->anonimizado_em)->not->toBeNull();
    expect($fresh->email)->not->toBe('quer-sair@example.com');
    expect($fresh->name)->not->toBe('Fulano');
    expect($fresh->telefone)->not->toBe('67999990000');
    expect($fresh->cnpj)->toBeNull();
});

it('--force preserva os dados fiscais (clientes) do titular', function () {
    $user = User::factory()->create([
        'deletion_requested_at' => now()->subDays(40),
    ]);
    Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '11222333000181',
        'nome' => 'Cliente Fiscal',
        'razao_social' => 'Cliente Fiscal LTDA',
        'is_empresa_propria' => false,
    ]);

    $this->artisan('lgpd:processar-exclusoes --force')->assertExitCode(0);

    expect(Cliente::where('user_id', $user->id)->count())->toBe(1);
});

it('--force anonimiza a PII da empresa própria do titular (fase 2.3)', function () {
    $user = User::factory()->create([
        'deletion_requested_at' => now()->subDays(40),
    ]);
    $empresa = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '11222333000181',
        'nome' => 'Minha Empresa',
        'razao_social' => 'Minha Empresa LTDA',
        'nome_fantasia' => 'MinhaEmp',
        'email' => 'contato@minhaempresa.com',
        'telefone' => '6733334444',
        'endereco' => 'Rua Real',
        'cep' => '79000000',
        'qsa' => [['nome' => 'Sócio Fulano', 'cpf' => '12345678900']],
        'is_empresa_propria' => true,
    ]);

    $this->artisan('lgpd:processar-exclusoes --force')->assertExitCode(0);

    $fresh = $empresa->fresh();
    expect($fresh)->not->toBeNull();
    expect($fresh->documento)->not->toBe('11222333000181');
    expect($fresh->razao_social)->not->toBe('Minha Empresa LTDA');
    expect($fresh->nome_fantasia)->toBeNull();
    expect($fresh->email)->toBeNull();
    expect($fresh->telefone)->toBeNull();
    expect($fresh->endereco)->toBeNull();
    expect($fresh->cep)->toBeNull();
    expect($fresh->qsa)->toBeNull();
});

it('--force NÃO toca na PII da carteira administrada (retenção fiscal de terceiros)', function () {
    $user = User::factory()->create([
        'deletion_requested_at' => now()->subDays(40),
    ]);
    $administrado = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '11222333000181',
        'nome' => 'Cliente do Contador',
        'razao_social' => 'Cliente do Contador LTDA',
        'email' => 'fiscal@cliente.com',
        'is_empresa_propria' => false,
    ]);

    $this->artisan('lgpd:processar-exclusoes --force')->assertExitCode(0);

    $fresh = $administrado->fresh();
    expect($fresh->documento)->toBe('11222333000181');
    expect($fresh->razao_social)->toBe('Cliente do Contador LTDA');
    expect($fresh->email)->toBe('fiscal@cliente.com');
});

it('respeita --apos-dias e ignora pedidos recentes', function () {
    $user = User::factory()->create([
        'deletion_requested_at' => now()->subDays(2),
    ]);

    $this->artisan('lgpd:processar-exclusoes --force --apos-dias=30')->assertExitCode(0);

    expect($user->fresh()->anonimizado_em)->toBeNull();
});

it('não reprocessa quem já foi anonimizado', function () {
    $user = User::factory()->create([
        'email' => 'anon-99@anonimizado.invalid',
        'deletion_requested_at' => now()->subDays(40),
        'anonimizado_em' => now()->subDay(),
    ]);

    $this->artisan('lgpd:processar-exclusoes --force')->assertExitCode(0);

    // anonimizado_em não muda (não reprocessa)
    expect($user->fresh()->email)->toBe('anon-99@anonimizado.invalid');
});

it('ignora quem não pediu exclusão', function () {
    $user = User::factory()->create(['deletion_requested_at' => null]);

    $this->artisan('lgpd:processar-exclusoes --force')->assertExitCode(0);

    expect($user->fresh()->anonimizado_em)->toBeNull();
});
