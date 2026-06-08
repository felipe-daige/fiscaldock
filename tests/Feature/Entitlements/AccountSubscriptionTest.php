<?php

use App\Models\AccountSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed(SubscriptionPlanSeeder::class));

it('relaciona user com a assinatura e o plano', function () {
    $user = User::factory()->create();
    $plano = SubscriptionPlan::where('codigo', 'profissional')->first();

    AccountSubscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $plano->id,
        'status' => 'ativa',
        'ciclo' => 'mensal',
    ]);

    expect($user->fresh()->subscription->plan->codigo)->toBe('profissional');
});

it('sem assinatura, a relação subscription é null', function () {
    $user = User::factory()->create();
    expect($user->subscription)->toBeNull();
});
