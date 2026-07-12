<?php

namespace App\Notifications;

use App\Support\Mail\Blocos;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Resumo semanal da conta (segunda 08:00). Só sai pra quem tem `resumo_periodico`
 * ligado E teve alerta ou atividade no período — ver ResumoSemanalService::montar.
 */
class ResumoSemanalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param array<string, mixed> $resumo */
    public function __construct(public array $resumo) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r = $this->resumo;
        $periodo = $r['periodo_inicio']->format('d/m').' a '.$r['periodo_fim']->format('d/m/Y');
        $sev = $r['por_severidade'];
        $totalAlertas = $sev['alta'] + $sev['media'] + $sev['baixa'];

        $exposicao = array_sum(array_column($r['destaques'], 'valor_risco'));

        $mail = Blocos::comEtiqueta(new MailMessage, 'Resumo semanal · '.$periodo);

        $mail->subject('Resumo da semana · '.$periodo)
            ->greeting($totalAlertas > 0
                ? $totalAlertas.' '.($totalAlertas === 1 ? 'alerta novo' : 'alertas novos').' na sua carteira'
                : 'Semana limpa na sua carteira');

        // Exposição primeiro: é o número que decide se ele abre o painel hoje ou não.
        if ($exposicao > 0) {
            $mail->line(Blocos::hero(
                Blocos::brl($exposicao),
                'Exposição a glosa',
                'Notas escrituradas com contrapartes irregulares',
                Blocos::VERMELHO
            ));
        }

        if ($totalAlertas > 0) {
            $mail->line('## Prioridades da semana');
            $mail->line('Ordenados por gravidade e valor. Resolva de cima para baixo.');
            $mail->line(Blocos::listaAlertas($r['destaques']));

            $resto = $totalAlertas - count($r['destaques']);

            if ($resto > 0) {
                $mail->line('E mais **'.$resto.'** '.($resto === 1 ? 'alerta' : 'alertas')
                    .' de menor gravidade na central.');
            }
        } else {
            $mail->line(Blocos::destaque(
                '<strong style="color: #047857;">Nenhum alerta novo nesta semana.</strong><br>'
                .'Certidões em dia, nenhuma contraparte virou irregular e sem divergência nova no acervo.',
                Blocos::VERDE
            ));
        }

        // Placar por último: é o "o que você levou", não o que pede ação.
        $mail->line('## O que a plataforma processou');
        $mail->line(Blocos::placar([
            'CNPJs consultados' => $r['consultas'],
            'Documentos na SEFAZ' => $r['clearance'],
            'Importações' => $r['importacoes'],
        ]));

        return $mail
            ->action('Abrir central de alertas', url('/app/alertas'))
            ->line('Este resumo chega toda segunda. Para desligar, vá em Configurações › Notificações.');
    }
}
