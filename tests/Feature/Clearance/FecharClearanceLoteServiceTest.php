<?php

use App\Models\ConsultaLote;
use App\Models\User;
use App\Services\Clearance\FecharClearanceLoteService;
use App\Services\SaldoService;
use Illuminate\Support\Facades\Cache;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

function loteFechar(User $u): ConsultaLote
{
    return ConsultaLote::create([
        'user_id' => $u->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_PROCESSANDO,
        'total_participantes' => 2, 'creditos_cobrados' => 6, 'tab_id' => 'tab-f',
    ]);
}

it('soma o estorno por doc, credita e fecha o lote', function () {
    $user = User::factory()->create(['credits' => 100]);
    $lote = loteFechar($user);
    $chaveA = str_repeat('5', 44);
    $chaveB = str_repeat('6', 44);
    Cache::put("clearance_lote_chaves:{$lote->id}", [$chaveA, $chaveB], 86400);
    Cache::put("clearance_estorno:{$lote->id}:{$chaveA}", 3, 86400); // A falhou
    Cache::put("clearance_estorno:{$lote->id}:{$chaveB}", 0, 86400); // B ok

    $saldoAntes = app(SaldoService::class)->getBalance($user);
    app(FecharClearanceLoteService::class)->fechar($lote->id);

    $lote->refresh();
    expect($lote->status)->toBe(ConsultaLote::STATUS_CONCLUIDO);
    expect($lote->processado_em)->not->toBeNull();
    expect($lote->resultado_resumo['engine'])->toBe('laravel-clearance');
    expect(app(SaldoService::class)->getBalance($user->fresh()))->toBe($saldoAntes + 3);
    // pull limpa as keys (idempotência)
    expect(Cache::get("clearance_lote_chaves:{$lote->id}"))->toBeNull();
    // terminal escrito no cache de progresso → SSE fecha sem F4 manual
    $prog = Cache::get("progresso:{$user->id}:tab-f");
    expect($prog['status'])->toBe(ConsultaLote::STATUS_FINALIZADO);
    expect($prog['progresso'])->toBe(100);
});

it('sem docs falhos: nenhum saldo adicionado', function () {
    $user = User::factory()->create(['credits' => 100]);
    $lote = loteFechar($user);
    $chave = str_repeat('5', 44);
    Cache::put("clearance_lote_chaves:{$lote->id}", [$chave], 86400);
    Cache::put("clearance_estorno:{$lote->id}:{$chave}", 0, 86400);

    $saldoAntes = app(SaldoService::class)->getBalance($user);
    app(FecharClearanceLoteService::class)->fechar($lote->id);

    expect(app(SaldoService::class)->getBalance($user->fresh()))->toBe($saldoAntes);
    expect($lote->fresh()->status)->toBe(ConsultaLote::STATUS_CONCLUIDO);
});
