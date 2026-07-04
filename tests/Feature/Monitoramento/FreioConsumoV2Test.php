<?php

use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush(); // guards 1×/ciclo vivem no cache — isolar entre testes
});

function freioAssinatura(User $user, Participante $p, array $overrides = []): MonitoramentoAssinatura
{
    return MonitoramentoAssinatura::create(array_merge([
        'user_id' => $user->id, 'participante_id' => $p->id,
        'plano_id' => MonitoramentoPlano::porCodigo('licitacao')->id, // custo 10
        'status' => 'ativo', 'frequencia_dias' => 30, 'proxima_execucao_em' => now()->subDay(),
    ], $overrides));
}

function freioParticipante(User $user, string $doc = '11222333000181'): Participante
{
    return Participante::create(['user_id' => $user->id, 'documento' => $doc, 'razao_social' => 'ACME']);
}

it('pausar() registra o motivo e reativar() limpa', function () {
    $user = User::factory()->create();
    $a = freioAssinatura($user, freioParticipante($user));

    $a->pausar('saldo');
    expect($a->fresh()->status)->toBe('pausado')
        ->and($a->fresh()->pausada_motivo)->toBe('saldo');

    $a->reativar();
    expect($a->fresh()->status)->toBe('ativo')
        ->and($a->fresh()->pausada_motivo)->toBeNull();
});

it('pausar() sem argumento assume motivo manual', function () {
    $user = User::factory()->create();
    $a = freioAssinatura($user, freioParticipante($user));

    $a->pausar();
    expect($a->fresh()->pausada_motivo)->toBe('manual');
});
