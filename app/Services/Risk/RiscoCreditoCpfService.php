<?php

namespace App\Services\Risk;

use App\Models\Cliente;
use App\Models\Participante;
use App\Support\Documento;
use InvalidArgumentException;

/**
 * Contrato do risco de crédito de pessoa física.
 *
 * O acervo fiscal observa operações, mas não contém comportamento de pagamento, renda ou
 * restrições financeiras. Até uma fonte própria de crédito ser integrada, o resultado correto
 * é explicitamente não avaliado — nunca uma faixa sintética derivada do volume das notas.
 */
final class RiscoCreditoCpfService
{
    public const MENSAGEM_NAO_AVALIADO = 'As notas fiscais comprovam relacionamento comercial, mas não informam atrasos, inadimplência, renda, restrições financeiras ou capacidade de pagamento. Sem uma fonte de crédito para CPF, não é possível atribuir uma faixa de risco real.';

    /**
     * @param  array<string, mixed>  $movimentacao
     * @return array<string, mixed>
     */
    public function avaliar(Participante|Cliente $participante, array $movimentacao = []): array
    {
        if (! Documento::ehCpf($participante->documento)) {
            throw new InvalidArgumentException('A avaliação de risco de crédito CPF exige um documento de 11 dígitos.');
        }

        return [
            'tipo' => 'credito_cpf',
            'titulo' => 'Risco de crédito',
            'scores' => [],
            'score_total' => null,
            'classificacao' => 'nao_avaliado',
            'detalhamento' => [],
            'avaliado' => false,
            'mensagem' => self::MENSAGEM_NAO_AVALIADO,
            'evidencia_comercial' => [
                'total_notas' => (int) ($movimentacao['total_notas'] ?? 0),
                'valor_movimentado' => (float) ($movimentacao['valor_movimentado'] ?? 0),
                'periodo_inicio' => $movimentacao['periodo_inicio'] ?? null,
                'periodo_fim' => $movimentacao['periodo_fim'] ?? null,
            ],
        ];
    }
}
