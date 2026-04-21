<?php

use App\Models\CreditTransaction;
use App\Models\User;
use Database\Seeders\MonitoramentoPlanoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

function assertPlanosEmBreve(TestResponse $response): void
{
    $response
        ->assertOk()
        ->assertSee('Planos de Consulta')
        ->assertSee('Usar plano')
        ->assertDontSee('Comprar créditos')
        ->assertDontSee('Disponível após a primeira recarga de créditos.');

    $content = $response->getContent();

    expect(substr_count($content, 'class="text-xs text-gray-400 whitespace-nowrap">Em breve</span>'))->toBe(4);
    expect(substr_count($content, 'Usar plano</a>'))->toBe(2);
    expect(substr_count($content, 'hover:underline whitespace-nowrap">Usar plano</a>'))->toBe(2);
}

it('mantem compliance e due diligence como em breve sem compra anterior', function () {
    $this->seed(MonitoramentoPlanoSeeder::class);

    $user = User::factory()->create(['credits' => 100]);

    $response = $this->actingAs($user)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get('/app/consulta/planos');

    assertPlanosEmBreve($response);
});

it('mantem compliance e due diligence como em breve apos primeira compra', function () {
    $this->seed(MonitoramentoPlanoSeeder::class);

    $user = User::factory()->create(['credits' => 100]);

    CreditTransaction::create([
        'user_id' => $user->id,
        'amount' => 100,
        'balance_after' => 100,
        'type' => 'purchase',
        'description' => 'Primeira compra de créditos',
    ]);

    $response = $this->actingAs($user)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get('/app/consulta/planos');

    assertPlanosEmBreve($response);
});
