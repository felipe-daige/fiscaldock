<?php

namespace App\Notifications;

use App\Models\Alerta;
use App\Support\Mail\Blocos;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Digest de vários alertas detectados numa MESMA execução de recalcular (F3 do
 * hardening). Sem ele, importar um SPED com N fornecedores irregulares dispararia
 * N e-mails no mesmo minuto. 1 alerta = AlertaImediatoNotification (rico); 2+ = este.
 *
 * @property Alerta[] $alertas
 */
class AlertaDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var array<int, array{titulo: string, severidade: string, valor_risco: float}> */
    private array $itens;

    private int $altas;

    private int $medias;

    private float $exposicao;

    /** @param array<int, Alerta> $alertas */
    public function __construct(array $alertas)
    {
        $ordem = ['alta' => 3, 'media' => 2, 'baixa' => 1];

        // Ordena por gravidade e valor — o topo é o que o contador ataca primeiro.
        usort($alertas, fn (Alerta $a, Alerta $b) => [$ordem[$b->severidade] ?? 0, (float) $b->valor_risco]
            <=> [$ordem[$a->severidade] ?? 0, (float) $a->valor_risco]);

        $this->altas = count(array_filter($alertas, fn ($a) => $a->severidade === 'alta'));
        $this->medias = count(array_filter($alertas, fn ($a) => $a->severidade === 'media'));
        $this->exposicao = array_sum(array_map(fn ($a) => (float) $a->valor_risco, $alertas));

        $this->itens = array_map(fn (Alerta $a) => [
            'titulo' => $a->titulo,
            'severidade' => $a->severidade,
            'valor_risco' => (float) $a->valor_risco,
        ], $alertas);
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $total = count($this->itens);

        $mail = Blocos::comEtiqueta(
            new MailMessage,
            'Novos alertas',
            $this->altas > 0 ? Blocos::VERMELHO : Blocos::AMBAR
        );

        $mail->subject($total.' novos alertas na sua conta'
            .($this->altas > 0 ? ' ('.$this->altas.' de risco alto)' : ''))
            ->greeting('Detectamos '.$total.' novos alertas')
            ->line('A última varredura da sua carteira encontrou '.$total.' pontos que pedem atenção: '
                .'**'.$this->altas.'** de risco alto e **'.$this->medias.'** de risco médio.');

        if ($this->exposicao > 0) {
            $mail->line(Blocos::hero(
                Blocos::brl($this->exposicao),
                'Exposição somada',
                'Em notas escrituradas com contrapartes irregulares',
                Blocos::VERMELHO
            ));
        }

        // Mostra os 8 mais graves no corpo; o resto fica na central (evita e-mail gigante).
        $mostrados = array_slice($this->itens, 0, 8);
        $mail->line('**Priorize de cima para baixo:**');
        $mail->line(Blocos::listaAlertas($mostrados));

        $resto = $total - count($mostrados);
        if ($resto > 0) {
            $mail->line('E mais **'.$resto.'** '.($resto === 1 ? 'alerta' : 'alertas').' na central.');
        }

        return $mail
            ->action('Abrir central de alertas', url('/app/alertas'))
            ->line('Resolveu algum? Marque na central — ele sai do painel e para de pesar no seu score. '
                .'Para desligar estes avisos, vá em Configurações › Notificações.');
    }
}
