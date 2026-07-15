<?php

use App\Models\Cliente;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed(SubscriptionPlanSeeder::class));

function freeUserComPropria(): User
{
    $user = User::factory()->create();
    Cliente::create([
        'user_id' => $user->id,
        'documento' => '10000000000191',
        'tipo_pessoa' => 'PJ',
        'razao_social' => 'Empresa Propria',
        'is_empresa_propria' => true,
        'ativo' => true,
    ]);

    return $user;
}

function payloadCliente(string $doc): array
{
    return ['tipo_pessoa' => 'PJ', 'documento' => $doc, 'razao_social' => 'Nova Empresa'];
}

it('Free cadastra o +1 cliente além da própria', function () {
    $user = freeUserComPropria();

    $this->actingAs($user)
        ->postJson('/app/cliente/novo', payloadCliente('22222222000191'))
        ->assertStatus(201);

    expect(Cliente::where('user_id', $user->id)->where('is_empresa_propria', false)->count())->toBe(1);
});

it('Free pode cadastrar um 2º cliente sem bloqueio comercial', function () {
    $user = freeUserComPropria();
    Cliente::create([
        'user_id' => $user->id, 'documento' => '22222222000191', 'tipo_pessoa' => 'PJ',
        'razao_social' => 'A', 'is_empresa_propria' => false, 'ativo' => true,
    ]);

    $this->actingAs($user)
        ->postJson('/app/cliente/novo', payloadCliente('33333333000191'))
        ->assertStatus(201);

    expect(Cliente::where('user_id', $user->id)->where('documento', '33333333000191')->exists())->toBeTrue();
});

it('não cria uma segunda empresa própria ao forjar is_empresa_propria=true', function () {
    $user = freeUserComPropria();
    // usa o +1 (cap cheio: própria + 1)
    Cliente::create([
        'user_id' => $user->id, 'documento' => '22222222000191', 'tipo_pessoa' => 'PJ',
        'razao_social' => 'A', 'is_empresa_propria' => false, 'ativo' => true,
    ]);

    // tenta um 3º forjando "própria" pra escapar do cap
    $this->actingAs($user)
        ->postJson('/app/cliente/novo', array_merge(payloadCliente('33333333000191'), ['is_empresa_propria' => true]))
        ->assertStatus(201);

    expect(Cliente::where('user_id', $user->id)->where('documento', '33333333000191')->exists())->toBeTrue();
    expect(Cliente::where('user_id', $user->id)->where('is_empresa_propria', true)->count())->toBe(1);
});

it('is_empresa_propria=true vira cliente normal quando já existe própria (cap com folga)', function () {
    $user = freeUserComPropria();

    $this->actingAs($user)
        ->postJson('/app/cliente/novo', array_merge(payloadCliente('22222222000191'), ['is_empresa_propria' => true]))
        ->assertStatus(201);

    expect(Cliente::where('user_id', $user->id)->where('is_empresa_propria', true)->count())->toBe(1);
    expect(Cliente::where('user_id', $user->id)->where('documento', '22222222000191')
        ->where('is_empresa_propria', false)->exists())->toBeTrue();
});

it('CRUD de clientes ignora tentativa de criar a empresa propria', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/app/cliente/novo', array_merge(payloadCliente('22222222000191'), ['is_empresa_propria' => true]))
        ->assertStatus(201);

    expect(Cliente::where('user_id', $user->id)->where('documento', '22222222000191')->value('is_empresa_propria'))
        ->toBeFalse();
});

it('CRUD de clientes nao promove nem rebaixa a empresa propria na edicao', function () {
    $user = freeUserComPropria();
    $empresa = $user->empresaPropria();
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'documento' => '22222222000191',
        'tipo_pessoa' => 'PJ',
        'razao_social' => 'Cliente Comum',
        'is_empresa_propria' => false,
        'ativo' => true,
    ]);

    $this->actingAs($user)
        ->putJson('/app/cliente/'.$cliente->id, [
            'documento' => $cliente->documento,
            'razao_social' => $cliente->razao_social,
            'is_empresa_propria' => true,
        ])
        ->assertOk();

    $this->putJson('/app/cliente/'.$empresa->id, [
        'documento' => $empresa->documento,
        'razao_social' => $empresa->razao_social,
        'is_empresa_propria' => false,
    ])->assertOk();

    expect($cliente->fresh()->is_empresa_propria)->toBeFalse()
        ->and($empresa->fresh()->is_empresa_propria)->toBeTrue();
});

it('formulario de clientes nao exibe controle de empresa propria', function () {
    $user = freeUserComPropria();

    $response = $this->actingAs($user)->get('/app/cliente/novo');

    $response->assertOk();
    $response->assertDontSee('id="btn-empresa-propria"', false);
    $response->assertDontSee('Esta é minha empresa');
});

it('trial ativo cadastra clientes sem cap', function () {
    $user = User::factory()->trialAtivo()->create();
    Cliente::create([
        'user_id' => $user->id, 'documento' => '10000000000191', 'tipo_pessoa' => 'PJ',
        'razao_social' => 'Propria', 'is_empresa_propria' => true, 'ativo' => true,
    ]);

    foreach (['22222222000191', '33333333000191', '44444444000191'] as $doc) {
        $this->actingAs($user)->postJson('/app/cliente/novo', payloadCliente($doc))->assertStatus(201);
    }

    expect(Cliente::where('user_id', $user->id)->where('is_empresa_propria', false)->count())->toBe(3);
});
