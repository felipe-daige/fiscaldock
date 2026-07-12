<?php

namespace App\Console\Commands;

use App\Models\Alerta;
use Illuminate\Console\Command;

/**
 * Backfill de segurança para o LIGAMENTO do e-mail de alerta imediato (F1 do
 * hardening de e-mails). Roda 1× em prod ANTES de `alertas:recalcular` bater com
 * Postmark ligado.
 *
 * O gate `AlertaCentralService::notificarSeRelevante` dispara e-mail para todo
 * alerta ativo alta/média com `notificado_em IS NULL`. Alertas que já existiam
 * antes do gate têm `notificado_em` nulo — sem este backfill, o primeiro
 * `recalcular` os trataria como "novos" e mandaria um e-mail retroativo de cada
 * um (flood de coisa velha + queima de reputação Postmark).
 *
 * Marca `notificado_em = now()` em TODOS os alertas ativos ainda não notificados
 * (qualquer severidade — os de baixa nunca notificam de qualquer forma, mas
 * marcá-los é inócuo e deixa o estado consistente). Idempotente: rodar de novo
 * não faz nada (só pega os que ainda estão nulos).
 */
class MarcarAlertasNotificadosExistentes extends Command
{
    protected $signature = 'alertas:marcar-notificados-existentes {--dry-run : Só conta, não grava}';

    protected $description = 'Backfill: marca alertas ativos existentes como já notificados (evita flood de e-mail no 1º recalcular com Postmark)';

    public function handle(): int
    {
        $query = Alerta::where('status', 'ativo')->whereNull('notificado_em');

        $total = (int) $query->count();

        // Recorte que de fato dispararia e-mail imediato — o número que importa.
        $notificaveis = (int) (clone $query)->whereIn('severidade', ['alta', 'media'])->count();

        if ($total === 0) {
            $this->info('Nenhum alerta ativo sem notificado_em. Nada a fazer.');

            return self::SUCCESS;
        }

        $this->line("Alertas ativos sem notificado_em: {$total} (dos quais {$notificaveis} alta/média disparariam e-mail).");

        if ($this->option('dry-run')) {
            $this->warn('[dry-run] Nada gravado.');

            return self::SUCCESS;
        }

        // Update em massa: não passa pelo observer (auditoria) de propósito — é
        // backfill de estado, não uma mudança de negócio no alerta.
        $afetados = $query->update(['notificado_em' => now()]);

        $this->info("Marcados {$afetados} alerta(s) como já notificados. O próximo recalcular só enviará e-mail dos NOVOS.");

        return self::SUCCESS;
    }
}
