<?php

use App\Models\User;
use App\Services\Dashboard\DashboardDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('monta os 3 KPIs do cockpit com o shape esperado', function () {
    $user = User::factory()->create(['credits' => 42]);

    $kpis = app(DashboardDataService::class)->getCockpitKpis($user->id, $user, null, null, null);

    expect($kpis)->toHaveKeys(['volume', 'saude', 'creditos'])
        ->and($kpis['volume'])->toHaveKeys(['notas', 'valor'])
        ->and($kpis['saude'])->toHaveKeys(['total', 'alertas_alta', 'risco'])
        ->and($kpis['saldo']['disponivel'])->toBe(42);
});
