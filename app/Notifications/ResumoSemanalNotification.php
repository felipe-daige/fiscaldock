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
        $temAlertas = $totalAlertas > 0;

        $corEtiqueta = $sev['alta'] > 0
            ? Blocos::VERMELHO
            : ($sev['media'] > 0 ? Blocos::AMBAR : Blocos::VERDE);

        $mail = Blocos::comEtiqueta(
            new MailMessage,
            'Resumo semanal · '.$periodo,
            $corEtiqueta
        );

        $assunto = $temAlertas
            ? 'Resumo fiscal: '.$totalAlertas.' '.($totalAlertas === 1 ? 'alerta novo' : 'alertas novos')
            : 'Resumo fiscal: semana sem novos alertas';

        $mail->subject($assunto.' · '.$periodo)
            ->greeting('Sua semana fiscal, em um olhar')
            ->line(Blocos::panoramaSemanal(
                $totalAlertas,
                $sev['alta'],
                $sev['media'],
                $exposicao,
                $periodo
            ));

        if ($temAlertas) {
            $mail->line(Blocos::tituloSecao(
                '01',
                'Mapa de atenção',
                'A distribuição mostra o que pede ação agora e o que pode ser apenas acompanhado.'
            ));
            $mail->line(Blocos::severidades($sev));

            $mail->line(Blocos::tituloSecao(
                '02',
                'Prioridades para revisar',
                'Ordenadas por gravidade e exposição financeira, da mais sensível para a menos urgente.'
            ));
            $mail->line(Blocos::listaAlertas($r['destaques'], true));

            $resto = $totalAlertas - count($r['destaques']);
            $complemento = $resto > 0
                ? ' A central reúne mais '.$resto.' '.($resto === 1 ? 'ocorrência' : 'ocorrências')
                    .' além das cinco prioridades deste e-mail.'
                : '';

            if ($sev['alta'] > 0) {
                $mail->line(Blocos::aviso(
                    'Primeiro movimento recomendado',
                    'Revise os itens de risco alto antes de validar créditos, participar de licitações ou fechar o período.'
                        .$complemento,
                    'critico'
                ));
            } elseif ($sev['media'] > 0) {
                $mail->line(Blocos::aviso(
                    'Programe uma revisão nesta semana',
                    'Os pontos de risco médio ainda não são críticos, mas merecem acompanhamento para não evoluírem.'
                        .$complemento,
                    'atencao'
                ));
            } else {
                $mail->line(Blocos::aviso(
                    'Acompanhamento suficiente',
                    'As ocorrências deste período são informativas e podem entrar na sua rotina normal de conferência.'
                        .$complemento,
                    'info'
                ));
            }
        } else {
            $mail->line(Blocos::aviso(
                'Nenhuma ação corretiva nova',
                'O período terminou sem novas irregularidades, vencimentos ou divergências detectadas na carteira.',
                'sucesso'
            ));
        }

        $mail->line(Blocos::tituloSecao(
            $temAlertas ? '03' : '01',
            'Atividade processada',
            'Volume concluído pelo FiscalDock durante o período deste resumo.'
        ));
        $mail->line(Blocos::placar([
            'CNPJs consultados' => $r['consultas'],
            'Documentos na SEFAZ' => $r['clearance'],
            'Importações' => $r['importacoes'],
        ]));

        return $mail
            ->action(
                $temAlertas ? 'Revisar prioridades na central' : 'Abrir painel fiscal',
                $temAlertas ? url('/app/alertas') : url('/app/dashboard')
            )
            ->line(Blocos::preferenciasResumo(url('/app/configuracoes')));
    }
}
