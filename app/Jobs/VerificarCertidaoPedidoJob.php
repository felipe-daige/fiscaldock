<?php

namespace App\Jobs;

use App\Models\CertidaoPedido;
use App\Services\Consultas\CertidaoPedidoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Follow-up da etapa 2 de um pedido de certidão (docs/advocacia/consultas-certidoes.md fase 4).
 * Despachado UNICAMENTE pelo sweep `certidoes:verificar-pedidos` (everyMinute) pros pedidos
 * vencidos. NÃO usa ShouldBeUnique: a defesa contra conferência paga em duplicidade é o CLAIM
 * ATÔMICO dentro de `verificar()` (UPDATE condicional em proxima_verificacao_em) — o unique-lock
 * de fila descartava silenciosamente os re-agendamentos disparados de dentro de outro job.
 */
class VerificarCertidaoPedidoJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $pedidoId) {}

    public function handle(CertidaoPedidoService $service): void
    {
        $pedido = CertidaoPedido::find($this->pedidoId);
        if (! $pedido || ! $pedido->estaAberto()) {
            return; // já concluído/falhou por outra execução — idempotente
        }

        $service->verificar($pedido);
    }
}
