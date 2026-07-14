<?php

use App\Models\Participante;
use App\Services\Risk\RiscoCreditoCpfService;

uses(Tests\TestCase::class);

it('mantém CPF não avaliado sem converter movimentação fiscal em score de crédito', function () {
    $participante = new Participante(['documento' => '123.456.789-01']);

    $avaliacao = app(RiscoCreditoCpfService::class)->avaliar($participante, [
        'total_notas' => 23,
        'valor_movimentado' => 187857.06,
        'periodo_inicio' => '2024-01',
        'periodo_fim' => '2024-07',
    ]);

    expect($avaliacao)
        ->toMatchArray([
            'tipo' => 'credito_cpf',
            'score_total' => null,
            'classificacao' => 'nao_avaliado',
            'avaliado' => false,
        ])
        ->and($avaliacao['evidencia_comercial']['total_notas'])->toBe(23)
        ->and($avaliacao['evidencia_comercial']['valor_movimentado'])->toBe(187857.06)
        ->and($avaliacao['mensagem'])->toContain('não é possível atribuir uma faixa de risco real');
});
