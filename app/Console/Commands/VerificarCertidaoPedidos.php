<?php

namespace App\Console\Commands;

use App\Jobs\VerificarCertidaoPedidoJob;
use App\Models\CertidaoPedido;
use Illuminate\Console\Command;

/**
 * Sweep dos pedidos de certidão de 2 etapas cuja verificação venceu (docs/advocacia/
 * consultas-certidoes.md fase 4). Rede de segurança do follow-up: o VerificarCertidaoPedidoJob
 * é agendado por delay na criação, mas se o worker reiniciar (job atrasado perdido) ou o delay
 * passar, o scheduler garante que o pedido volte a ser conferido.
 */
class VerificarCertidaoPedidos extends Command
{
    protected $signature = 'certidoes:verificar-pedidos';

    protected $description = 'Reagenda a conferência (etapa 2) dos pedidos de certidão pendentes cuja verificação venceu';

    public function handle(): int
    {
        $pedidos = CertidaoPedido::vencidos()->limit(200)->get();

        foreach ($pedidos as $pedido) {
            VerificarCertidaoPedidoJob::dispatch($pedido->id);
        }

        $this->info("Pedidos de certidão reenfileirados: {$pedidos->count()}");

        return self::SUCCESS;
    }
}
