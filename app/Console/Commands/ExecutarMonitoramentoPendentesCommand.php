<?php

namespace App\Console\Commands;

use App\Actions\Monitoramento\DispararConsultaMonitoramento;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoConsulta;
use App\Models\User;
use App\Services\CreditService;
use App\Services\Entitlements\EntitlementService;
use App\Support\Monitoramento\MonitoramentoNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ExecutarMonitoramentoPendentesCommand extends Command
{
    protected $signature = 'monitoramento:executar-pendentes';

    protected $description = 'Dispara as consultas recorrentes de monitoramento contínuo vencidas e os retries de erro';

    /** @var array<int, int> user_id => nº de assinaturas adiadas pelo freio neste run */
    private array $adiadasPorUsuario = [];

    public function handle(
        DispararConsultaMonitoramento $disparar,
        CreditService $creditService,
        MonitoramentoNotifier $notifier,
        EntitlementService $entitlements,
    ): int {
        $this->adiadasPorUsuario = [];

        $this->executarVencidas($disparar, $creditService, $notifier, $entitlements);
        $this->executarRetries($disparar, $creditService, $notifier, $entitlements);
        $this->notificarFreio($notifier, $entitlements);

        return self::SUCCESS;
    }

    private function executarVencidas(
        DispararConsultaMonitoramento $disparar,
        CreditService $creditService,
        MonitoramentoNotifier $notifier,
        EntitlementService $entitlements,
    ): void {
        foreach (MonitoramentoAssinatura::pendentesExecucao() as $assinatura) {
            $custo = $assinatura->custoCiclo();

            // Freio §6.2 v2: ADIA (skip sem pausar) — a assinatura fica ativa e vencida;
            // cada run reavalia. Ciclo novo (consumo zera) ou cap maior => dispara sozinha.
            if ($entitlements->monitoramentoCapEstourado($assinatura->user, $custo)) {
                $this->adiadasPorUsuario[$assinatura->user_id] = ($this->adiadasPorUsuario[$assinatura->user_id] ?? 0) + 1;
                $this->line("Assinatura #{$assinatura->id} adiada — freio de consumo do ciclo.");

                continue;
            }

            if (! $creditService->hasEnough($assinatura->user, $custo)) {
                $assinatura->pausar('saldo');
                $notifier->assinaturaPausadaSemSaldo($assinatura);
                $this->warn("Assinatura #{$assinatura->id} pausada — saldo insuficiente.");

                continue;
            }

            $disparar->execute($assinatura);
            $assinatura->agendarProximaExecucao();
            $this->info("Assinatura #{$assinatura->id} disparada.");

            $this->alertarConsumoAlto($notifier, $entitlements, $assinatura->user);
        }
    }

    private function executarRetries(
        DispararConsultaMonitoramento $disparar,
        CreditService $creditService,
        MonitoramentoNotifier $notifier,
        EntitlementService $entitlements,
    ): void {
        $elegiveis = MonitoramentoConsulta::query()
            ->where('tipo', 'assinatura')
            ->where('status', 'erro')
            ->where('executado_em', '<=', now()->subDay())
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('monitoramento_consultas')
                    ->where('tipo', 'assinatura')
                    ->whereNotNull('assinatura_id')
                    ->groupBy('assinatura_id');
            })
            ->get();

        foreach ($elegiveis as $consultaErro) {
            $assinatura = $consultaErro->assinatura;

            if (! $assinatura || ! $assinatura->isAtiva()) {
                continue;
            }

            if ($consultaErro->retryCount() >= 3) {
                $assinatura->pausar('falhas');
                $notifier->assinaturaPausadaPorFalhas($assinatura);
                $this->warn("Assinatura #{$assinatura->id} pausada — 3 retries falhos.");

                continue;
            }

            $custo = $assinatura->custoCiclo();

            if ($entitlements->monitoramentoCapEstourado($assinatura->user, $custo)) {
                $this->adiadasPorUsuario[$assinatura->user_id] = ($this->adiadasPorUsuario[$assinatura->user_id] ?? 0) + 1;
                $this->line("Retry da assinatura #{$assinatura->id} adiado — freio de consumo do ciclo.");

                continue;
            }

            if (! $creditService->hasEnough($assinatura->user, $custo)) {
                $assinatura->pausar('saldo');
                $notifier->assinaturaPausadaSemSaldo($assinatura);
                $this->warn("Assinatura #{$assinatura->id} pausada no retry — saldo insuficiente.");

                continue;
            }

            $disparar->execute($assinatura, $consultaErro);
            $this->info("Retry da assinatura #{$assinatura->id} disparado.");

            $this->alertarConsumoAlto($notifier, $entitlements, $assinatura->user);
        }
    }

    /** Aviso 1×/ciclo ao cruzar 80% do cap (só com cap > 0). */
    private function alertarConsumoAlto(
        MonitoramentoNotifier $notifier,
        EntitlementService $entitlements,
        User $user,
    ): void {
        $cap = $entitlements->consumptionCap($user);

        if ($cap <= 0) {
            return;
        }

        $consumo = $entitlements->consumoMonitoramentoNoCiclo($user);

        if ($consumo < (int) ceil($cap * 0.8)) {
            return;
        }

        $ciclo = $entitlements->cicloInicioMonitoramento($user);

        if ($this->umaVezPorCiclo("monitor_consumo80:{$user->id}:{$ciclo->timestamp}", $entitlements, $user)) {
            $notifier->consumoProximoDoLimite($user, $consumo, $cap);
        }
    }

    /** Consolida as adiadas do run e avisa cada usuário 1×/ciclo. */
    private function notificarFreio(MonitoramentoNotifier $notifier, EntitlementService $entitlements): void
    {
        foreach ($this->adiadasPorUsuario as $userId => $adiadas) {
            $user = User::find($userId);

            if (! $user) {
                continue;
            }

            $ciclo = $entitlements->cicloInicioMonitoramento($user);

            if ($this->umaVezPorCiclo("monitor_freio:{$userId}:{$ciclo->timestamp}", $entitlements, $user)) {
                $notifier->freioAtuou($user, $adiadas, $entitlements->fimCicloMonitoramento($user));
            }
        }
    }

    /** Cache::add é atômico: true só na primeira vez da chave no ciclo. */
    private function umaVezPorCiclo(string $key, EntitlementService $entitlements, User $user): bool
    {
        $fim = $entitlements->fimCicloMonitoramento($user);

        // Âncora do ciclo pode estar >1 mês estagnada (assinatura sem grant recente):
        // fimCiclo já passou e o diff fica negativo. Cai no período nominal de 1 mês
        // pra manter a cadência 1×/ciclo em vez de reexpirar a cada run diário.
        $ttl = now()->lt($fim)
            ? (int) now()->diffInSeconds($fim, false)
            : 60 * 60 * 24 * 30;

        return Cache::add($key, true, max(60, $ttl));
    }
}
