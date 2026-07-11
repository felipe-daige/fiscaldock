<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Recibo de compra avulsa de saldo aprovada (Mercado Pago, tipo=purchase).
 * Disparada de ProcessarPagamentoMercadoPago após o commit. Valor sempre em R$.
 */
class CompraConfirmadaNotification extends Notification implements ShouldQueue
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
            ->subject('Pagamento confirmado — saldo liberado')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Recebemos seu pagamento e o saldo já está disponível na sua conta.')
            ->line('**Pacote:** '.$this->pacote)
            ->line('**Valor pago:** '.$valorFmt)
            ->action('Ver meu saldo', url('/app/creditos'))
            ->line('Obrigado por usar o FiscalDock.')
            ->salutation('Referência do pagamento: '.$this->mpPaymentId);
    }
}
