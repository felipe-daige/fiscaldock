<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Recibo de recarga automática por saldo baixo aprovada (Mercado Pago, tipo=auto_topup).
 * Disparada de ProcessarPagamentoMercadoPago após o commit. Valor sempre em R$.
 */
class RecargaAutomaticaConfirmadaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $pacote,
        public float $valor,
        public string $mpPaymentId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $valorFmt = 'R$ '.number_format($this->valor, 2, ',', '.');

        return (new MailMessage)
            ->subject('Recarga automática concluída')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Sua recarga automática por saldo baixo foi processada e o saldo já está disponível.')
            ->line('**Pacote:** '.$this->pacote)
            ->line('**Valor cobrado:** '.$valorFmt)
            ->action('Ver meu saldo', url('/app/creditos'))
            ->line('Você pode ajustar ou desativar a recarga automática a qualquer momento.')
            ->salutation('Referência do pagamento: '.$this->mpPaymentId);
    }
}
