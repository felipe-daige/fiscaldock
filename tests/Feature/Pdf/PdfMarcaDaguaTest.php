<?php

use App\Models\AccountSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(fn () => (new Database\Seeders\SubscriptionPlanSeeder)->run());

function assinarPlanoPdf(User $user, string $codigo): void
{
    $p = SubscriptionPlan::where('codigo', $codigo)->first();
    AccountSubscription::create([
        'user_id' => $user->id, 'subscription_plan_id' => $p->id,
        'status' => 'ativa', 'ciclo' => 'mensal',
    ]);
}

it('Free puro recebe marca d\'água no PDF', function () {
    actingAs(User::factory()->create()); // Free, sem export pago
    $html = view('reports.layout')->render();
    expect($html)->toContain('Plano gratuito'); // discriminador da marca d'água
});

it('plano pago (Essencial) NÃO recebe marca d\'água', function () {
    $u = User::factory()->create();
    assinarPlanoPdf($u, 'essencial'); // export=[csv] → export pago
    actingAs($u->fresh());
    $html = view('reports.layout')->render();
    expect($html)->not->toContain('Plano gratuito');
});

it('trial ativo NÃO recebe marca d\'água', function () {
    actingAs(User::factory()->trialAtivo()->create());
    $html = view('reports.layout')->render();
    expect($html)->not->toContain('Plano gratuito');
});

it('header executivo aparece só com pdf_executivo (Profissional), não no Essencial', function () {
    $ess = User::factory()->create();
    assinarPlanoPdf($ess, 'essencial'); // pdf_executivo=false
    actingAs($ess->fresh());
    expect(view('reports.layout')->render())->not->toContain('Relatório Executivo');

    $prof = User::factory()->create();
    assinarPlanoPdf($prof, 'profissional'); // pdf_executivo=true
    actingAs($prof->fresh());
    expect(view('reports.layout')->render())->toContain('Relatório Executivo');
});

it('controller pode forçar sem marca d\'água passando marcaDagua=false', function () {
    actingAs(User::factory()->create()); // Free
    $html = view('reports.layout', ['marcaDagua' => false])->render();
    expect($html)->not->toContain('Plano gratuito');
});
