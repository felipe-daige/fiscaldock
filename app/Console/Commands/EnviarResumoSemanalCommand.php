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
                            {--dry-run : Só mostra quem receberia, sem enviar}';

    protected $description = 'Envia o resumo semanal de alertas e atividade (agendado: segunda 08:00)';

    public function handle(ResumoSemanalService $service): int
    {
        $fim = now();
        $inicio = $fim->copy()->subDays(7);

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
            $resumo = $service->montar($user, $inicio, $fim);

            // Semana sem alerta e sem atividade não vira e-mail (evita ruído).
            if ($resumo['vazio']) {
                $pulados++;

                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("[dry-run] {$user->email} — alertas: ".array_sum($resumo['por_severidade'])
                    .", consultas: {$resumo['consultas']}, clearance: {$resumo['clearance']}, importações: {$resumo['importacoes']}");
                $enviados++;

                continue;
            }

            $user->notify(new ResumoSemanalNotification($resumo));
            $enviados++;
        }

        $this->info("Resumo semanal: {$enviados} enviado(s), {$pulados} pulado(s) por período vazio.");

        return self::SUCCESS;
    }
}
