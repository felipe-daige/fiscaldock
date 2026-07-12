<?php

use App\Models\ConsultaLote;
use App\Models\SaldoTransacao;
use App\Models\MonitoramentoPlano;
use App\Models\User;
use App\Services\PricingCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function criarLoteGratuito(User $user, int $qtd, string $status = ConsultaLote::STATUS_CONCLUIDO): void
{
    $plano = MonitoramentoPlano::where('codigo', 'gratuito')->first();
    ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => $status,
        'total_participantes' => $qtd,
        'creditos_cobrados' => 0,
        'tab_id' => (string) Str::uuid(),
    ]);
}

it('bloqueia 4a gratuita sem compra', function () {
    $user = User::factory()->create();
    criarLoteGratuito($user, 3);

    expect(app(PricingCatalogService::class)->gratuitoCapStatus($user, 1)['bloqueado'])->toBeTrue();
});

it('libera gratuito apos compra', function () {
    $user = User::factory()->create();
    SaldoTransacao::create(['user_id' => $user->id, 'type' => 'purchase', 'amount' => 500, 'balance_after' => 500]);
    criarLoteGratuito($user, 1);
    criarLoteGratuito($user, 1);
    criarLoteGratuito($user, 1);
    criarLoteGratuito($user, 1);
    criarLoteGratuito($user, 1);

    expect(app(PricingCatalogService::class)->gratuitoCapStatus($user, 1)['bloqueado'])->toBeFalse();
});

it('retorna limite correto sem compra', function () {
    $user = User::factory()->create();

    $status = app(PricingCatalogService::class)->gratuitoCapStatus($user, 0);

    expect($status['limite'])->toBe(3)
        ->and($status['usados'])->toBe(0)
        ->and($status['restantes'])->toBe(3)
        ->and($status['bloqueado'])->toBeFalse();
});

it('contabiliza participantes consumidos corretamente', function () {
    $user = User::factory()->create();
    criarLoteGratuito($user, 2);

    $status = app(PricingCatalogService::class)->gratuitoCapStatus($user, 0);

    expect($status['usados'])->toBe(2)
        ->and($status['restantes'])->toBe(1)
        ->and($status['bloqueado'])->toBeFalse();
});

it('lotes de erro nao contam no cap gratuito', function () {
    $user = User::factory()->create();
    criarLoteGratuito($user, 10, ConsultaLote::STATUS_ERRO);

    $status = app(PricingCatalogService::class)->gratuitoCapStatus($user, 0);

    expect($status['usados'])->toBe(0)
        ->and($status['bloqueado'])->toBeFalse();
});
