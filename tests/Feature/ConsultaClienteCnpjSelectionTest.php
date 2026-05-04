<?php

use App\Models\Cliente;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('seleciona apenas o participante equivalente ao documento do cliente', function () {
    $user = User::factory()->create();

    $cliente = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000199',
        'razao_social' => 'Cliente Alvo Ltda',
        'ativo' => true,
    ]);

    $participanteEquivalente = Participante::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'documento' => '12345678000199',
        'razao_social' => 'Cliente Alvo Ltda',
        'origem_tipo' => 'MANUAL',
    ]);

    Participante::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'documento' => '98765432000155',
        'razao_social' => 'Fornecedor Vinculado 1',
        'origem_tipo' => 'NFE',
    ]);

    Participante::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'documento' => '11222333000144',
        'razao_social' => 'Fornecedor Vinculado 2',
        'origem_tipo' => 'SPED_EFD_FISCAL',
    ]);

    actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->postJson('/app/consulta/nova/participantes-por-clientes', [
            '_token' => 'test-token',
            'cliente_ids' => [$cliente->id],
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'ids' => [$participanteEquivalente->id],
        ]);
});

it('cria participante espelho quando o cliente pj nao tem equivalente previo', function () {
    $user = User::factory()->create();

    $cliente = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '44556677000188',
        'razao_social' => 'Cliente Sem Equivalente Ltda',
        'nome' => 'Cliente Sem Equivalente',
        'uf' => 'SP',
        'telefone' => '1133334444',
        'ativo' => true,
    ]);

    Participante::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'documento' => '99887766000122',
        'razao_social' => 'Fornecedor Vinculado',
        'origem_tipo' => 'NFE',
    ]);

    actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->postJson('/app/consulta/nova/participantes-por-clientes', [
            '_token' => 'test-token',
            'cliente_ids' => [$cliente->id],
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    $participanteEspelho = Participante::where('user_id', $user->id)
        ->where('documento', '44556677000188')
        ->first();

    expect($participanteEspelho)->not->toBeNull();
    expect($participanteEspelho->cliente_id)->toBe($cliente->id);
    expect($participanteEspelho->razao_social)->toBe('Cliente Sem Equivalente Ltda');
    expect($participanteEspelho->uf)->toBe('SP');
    expect($participanteEspelho->telefone)->toBe('1133334444');
    expect($participanteEspelho->origem_tipo)->toBe('MANUAL');
});

it('vincula participante equivalente existente ao cliente correto', function () {
    $user = User::factory()->create();

    $cliente = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '55443322000111',
        'razao_social' => 'Cliente para Vincular Ltda',
        'ativo' => true,
    ]);

    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => '55443322000111',
        'razao_social' => 'Cliente para Vincular Ltda',
        'origem_tipo' => 'MANUAL',
    ]);

    actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->postJson('/app/consulta/nova/participantes-por-clientes', [
            '_token' => 'test-token',
            'cliente_ids' => [$cliente->id],
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'ids' => [$participante->id],
        ]);

    expect($participante->fresh()->cliente_id)->toBe($cliente->id);
});

it('nao cria participante para cliente pf', function () {
    $user = User::factory()->create();

    $cliente = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PF',
        'documento' => '12345678901',
        'razao_social' => 'Cliente Pessoa Fisica',
        'ativo' => true,
    ]);

    actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->postJson('/app/consulta/nova/participantes-por-clientes', [
            '_token' => 'test-token',
            'cliente_ids' => [$cliente->id],
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'ids' => [],
        ]);

    expect(Participante::where('user_id', $user->id)->count())->toBe(0);
});
