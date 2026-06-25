<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\Participante;
use App\Models\User;
use App\Services\Consultas\Fiscal\TopMovimentacaoQuery;
use Illuminate\Support\Facades\DB;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function criarNotaFiscal(array $attrs): void
{
    DB::table('efd_notas')->insert(array_merge([
        'origem_arquivo' => 'fiscal',
        'cancelada' => false,
        'modelo' => '55',
        'numero' => random_int(1, 9999999),
        'created_at' => now(),
        'updated_at' => now(),
    ], $attrs));
}

it('agrega entradas e saidas por mes ignorando cancelada e data nula', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'documento' => '99888777000166', 'razao_social' => 'MINHA EMPRESA']);
    $part = Participante::create(['user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'ACME LTDA']);
    $imp = EfdImportacao::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'test.txt', 'status' => 'concluido', 'iniciado_em' => now()]);

    $base = ['user_id' => $user->id, 'cliente_id' => $cliente->id, 'participante_id' => $part->id, 'importacao_id' => $imp->id];

    criarNotaFiscal($base + ['tipo_operacao' => 'entrada', 'valor_total' => 100, 'data_emissao' => '2026-01-10']);
    criarNotaFiscal($base + ['tipo_operacao' => 'entrada', 'valor_total' => 50,  'data_emissao' => '2026-01-20']);
    criarNotaFiscal($base + ['tipo_operacao' => 'saida',   'valor_total' => 200, 'data_emissao' => '2026-02-05']);
    // cancelada e data nula: ignoradas
    criarNotaFiscal($base + ['tipo_operacao' => 'saida', 'valor_total' => 999, 'data_emissao' => '2026-02-06', 'cancelada' => true]);
    criarNotaFiscal($base + ['tipo_operacao' => 'entrada', 'valor_total' => 999, 'data_emissao' => null]);

    $serie = app(TopMovimentacaoQuery::class)
        ->serieMensal($user->id, 'participante_id', [$part->id], 24);

    expect($serie[$part->id])->toBe([
        ['mes' => '2026-01', 'entradas' => 150.0, 'saidas' => 0.0],
        ['mes' => '2026-02', 'entradas' => 0.0, 'saidas' => 200.0],
    ]);
});

it('rejeita coluna de escopo invalida', function () {
    $user = User::factory()->create();
    expect(fn () => app(TopMovimentacaoQuery::class)->serieMensal($user->id, 'foo', [1]))
        ->toThrow(InvalidArgumentException::class);
});
