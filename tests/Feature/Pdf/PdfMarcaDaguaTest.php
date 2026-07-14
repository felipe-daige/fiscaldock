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

it('trial recebe PDF limpo para experimentar a entrega paga', function () {
    actingAs(User::factory()->trialAtivo()->create());
    $html = view('reports.layout')->render();
    expect($html)->not->toContain('Plano gratuito');
});

it('header executivo aparece em todos os planos', function () {
    actingAs(User::factory()->create());
    expect(view('reports.layout')->render())->toContain('Relatório Executivo');

    $ess = User::factory()->create();
    assinarPlanoPdf($ess, 'essencial');
    actingAs($ess->fresh());
    expect(view('reports.layout')->render())->toContain('Relatório Executivo');

    $prof = User::factory()->create();
    assinarPlanoPdf($prof, 'profissional');
    actingAs($prof->fresh());
    expect(view('reports.layout')->render())->toContain('Relatório Executivo');
});

it('controller não consegue retirar a marca d\'água do Free', function () {
    actingAs(User::factory()->create()); // Free
    $html = view('reports.layout', ['marcaDagua' => false])->render();
    expect($html)->toContain('Plano gratuito');
});

it('todo PDF gerado pela aplicação usa o layout com a política global de marca d\'água', function () {
    $views = collect(\Illuminate\Support\Facades\File::allFiles(app_path()))
        ->flatMap(function (\SplFileInfo $file) {
            preg_match_all("/PdfReport::render\\('([^']+)'/", $file->getContents(), $matches);

            return $matches[1] ?? [];
        })
        ->unique()
        ->values();

    expect($views)->not->toBeEmpty();

    foreach ($views as $view) {
        $path = resource_path('views/'.str_replace('.', '/', $view).'.blade.php');
        expect($path)->toBeFile()
            ->and(file_get_contents($path))->toContain("@extends('reports.layout')");
    }
});
