<?php

use App\BI\Queries\VolumePorBlocoQuery;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

afterEach(function () {
    $this->user->forceDelete();
});

it('retorna estrutura com blocos A, C, D', function () {
    $filtros = [
        'user_id' => $this->user->id,
        'data_inicio_iso' => now()->startOfMonth()->format('Y-m-d'),
        'data_fim_iso' => now()->endOfMonth()->format('Y-m-d'),
    ];

    $resultado = (new VolumePorBlocoQuery($filtros))->execute();

    expect($resultado)->toHaveKeys(['A', 'C', 'D']);
    expect($resultado['A'])->toHaveKeys(['valor', 'notas']);
    expect($resultado['C'])->toHaveKeys(['valor', 'notas']);
    expect($resultado['D'])->toHaveKeys(['valor', 'notas']);
});

it('retorna zeros quando não há notas', function () {
    $filtros = [
        'user_id' => $this->user->id,
        'data_inicio_iso' => now()->startOfMonth()->format('Y-m-d'),
        'data_fim_iso' => now()->endOfMonth()->format('Y-m-d'),
    ];

    $resultado = (new VolumePorBlocoQuery($filtros))->execute();

    expect((float) $resultado['A']['valor'])->toBe(0.0);
    expect((int) $resultado['A']['notas'])->toBe(0);
    expect((float) $resultado['C']['valor'])->toBe(0.0);
    expect((float) $resultado['D']['valor'])->toBe(0.0);
});
