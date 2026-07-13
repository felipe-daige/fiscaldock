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
        $manual = (bool) $this->option('user');
        // Mensal só sai na 1ª segunda do mês (o comando roda toda segunda). Manual
        // (--user) ignora essa trava pra permitir teste.
        $primeiraSegundaDoMes = now()->day <= 7;

        $query = User::where('resumo_periodico', true)
            ->whereNull('anonimizado_em')
            ->whereNull('bloqueado_em')
            // Quem pediu exclusão de conta (LGPD) não deve mais receber e-mail, mesmo
            // antes de a anonimização rodar.
            ->whereNull('deletion_requested_at');

        if ($manual) {
            $query->where('id', (int) $this->option('user'));
        }

        $enviados = 0;
        $pulados = 0;

        foreach ($query->cursor() as $user) {
            $mensal = ($user->resumo_frequencia ?? 'semanal') === 'mensal';

            // Frequência: mensal pula fora da 1ª segunda; janela de 30 dias (vs 7 no semanal).
            if ($mensal && ! $primeiraSegundaDoMes && ! $manual) {
                $pulados++;

                continue;
            }

            $inicio = $fim->copy()->subDays($mensal ? 30 : 7);
            $resumo = $service->montar($user, $inicio, $fim);

            // Período sem alerta e sem atividade não vira e-mail (evita ruído).
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
