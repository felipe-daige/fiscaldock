<?php

namespace App\Console\Commands;

use App\Actions\Monitoramento\DispararConsultaMonitoramento;
use App\Models\MonitoramentoAssinatura;
use App\Services\CreditService;
use App\Support\Monitoramento\MonitoramentoNotifier;
use Illuminate\Console\Command;

class ExecutarMonitoramentoPendentesCommand extends Command
{
    protected $signature = 'monitoramento:executar-pendentes';

    protected $description = 'Dispara as consultas recorrentes de monitoramento contínuo vencidas e os retries de erro';

    public function handle(
        DispararConsultaMonitoramento $disparar,
        CreditService $creditService,
        MonitoramentoNotifier $notifier,
    ): int {
        $this->executarVencidas($disparar, $creditService, $notifier);

        return self::SUCCESS;
    }

    private function executarVencidas(
        DispararConsultaMonitoramento $disparar,
        CreditService $creditService,
        MonitoramentoNotifier $notifier,
    ): void {
        foreach (MonitoramentoAssinatura::pendentesExecucao() as $assinatura) {
            $custo = (int) ($assinatura->plano->custo_creditos ?? 0);

            if (! $creditService->hasEnough($assinatura->user, $custo)) {
                $assinatura->pausar();
                $notifier->assinaturaPausadaSemSaldo($assinatura);
                $this->warn("Assinatura #{$assinatura->id} pausada — saldo insuficiente.");

                continue;
            }

            $disparar->execute($assinatura);
            $assinatura->agendarProximaExecucao();
            $this->info("Assinatura #{$assinatura->id} disparada.");
        }
    }
}
