<?php

use App\Http\Middleware\RequiresEntitlement;
use App\Models\AccountSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SubscriptionPlanSeeder::class);

    // Rota-sonda só pra exercitar o middleware isoladamente — o gate NÃO é
    // cabeado a uma feature ao vivo nesta fundação (isso é Fase 5, quando
    // assinaturas forem atribuíveis via billing).
    Route::middleware(RequiresEntitlement::class.':clearance_lote')
        ->get('/_test/entitlement-probe', fn () => response()->json(['ok' => true]));
});

it('bloqueia com 403 quando o plano não tem a capability (Free)', function () {
    $user = User::factory()->create();

    actingAs($user)->getJson('/_test/entitlement-probe')->assertStatus(403);
});

it('libera quando o plano tem a capability (Essencial)', function () {
    $user = User::factory()->create();
    $plano = SubscriptionPlan::where('codigo', 'essencial')->first();
    AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $plano->id,
        'status' => 'ativa', 'ciclo' => 'mensal',
    ]);

    actingAs($user)->getJson('/_test/entitlement-probe')->assertOk();
});
