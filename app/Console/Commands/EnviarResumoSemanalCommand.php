<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\ResumoSemanalNotification;
use App\Services\Alertas\ResumoSemanalService;
use Illuminate\Console\Command;

class EnviarResumoSemanalCommand extends Command
{
    protected $signature = 'alertas:enviar-resumo-semanal
                            {--user= : ID de um usuário específico}
                            {--force : Reenvia ignorando a cadência, com a janela nominal e sem mover a âncora (manual/teste)}
                            {--dry-run : Só mostra quem receberia, sem enviar}';

    protected $description = 'Envia o resumo periódico de alertas e atividade (agendado: toda segunda 08:00; mensal sai 1x/mês)';

    public function handle(ResumoSemanalService $service): int
    {
        $fim = now();
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $query = User::where('resumo_periodico', true)
            ->whereNull('anonimizado_em')
            ->whereNull('bloqueado_em')
            // Quem pediu exclusão de conta (LGPD) não deve mais receber e-mail, mesmo
            // antes de a anonimização rodar.
            ->whereNull('deletion_requested_at');

        if ($this->option('user')) {
            $query->where('id', (int) $this->option('user'));
        }

        $enviados = 0;
        $pulados = 0;

        foreach ($query->cursor() as $user) {
            $mensal = ($user->resumo_frequencia ?? 'semanal') === 'mensal';
            $ultimo = $user->ultimo_resumo_em;

            // Cadência guardada por `ultimo_resumo_em`, não pela data do cron: rodar o
            // comando 2x no mesmo período não reenvia, e mudar o schedule não muda a
            // frequência prometida ao usuário. Mensal = no máximo 1x por mês civil;
            // semanal = no máximo 1x a cada 6 dias (folga p/ jitter do agendador).
            if (! $force && $ultimo !== null) {
                $dentroDoPeriodo = $mensal
                    ? $ultimo->greaterThanOrEqualTo($fim->copy()->startOfMonth())
                    : $ultimo->greaterThan($fim->copy()->subDays(6));

                if ($dentroDoPeriodo) {
                    $pulados++;

                    continue;
                }
            }

            // Janela ANCORADA no último envio — sem buraco nem sobreposição. (Janela fixa
            // de 30 dias perdia até 5 dias de alertas quando o intervalo entre 1as segundas
            // era 35 dias.) No 1º envio — e no --force, que é reenvio manual/teste — cai na
            // janela nominal (a âncora deixaria a janela vazia num reenvio imediato).
            $inicio = ($force || $ultimo === null)
                ? $fim->copy()->subDays($mensal ? 30 : 7)
                : $ultimo;

            $resumo = $service->montar($user, $inicio, $fim);

            // Período sem alerta e sem atividade não vira e-mail (evita ruído). NÃO marca
            // `ultimo_resumo_em`: a janela segue acumulando até haver o que contar.
            if ($resumo['vazio']) {
                $pulados++;

                continue;
            }

            if ($dryRun) {
                $this->line("[dry-run] {$user->email} ({$user->resumo_frequencia}) — alertas: ".array_sum($resumo['por_severidade'])
                    .", consultas: {$resumo['consultas']}, clearance: {$resumo['clearance']}, importações: {$resumo['importacoes']}");
                $enviados++;

                continue;
            }

            $user->notify(new ResumoSemanalNotification($resumo));

            // Move a âncora só no envio agendado: --dry-run e --force são escapes
            // manuais/de teste e NÃO podem suprimir o envio real do ciclo.
            if (! $force) {
                $user->forceFill(['ultimo_resumo_em' => $fim])->saveQuietly();
            }
            $enviados++;
        }

        $this->info("Resumo periódico: {$enviados} enviado(s), {$pulados} pulado(s) (fora da cadência ou período vazio).");

        return self::SUCCESS;
    }
}
