<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Recibo de cobrança recorrente aprovada de uma assinatura (preapproval MP).
 * Disparada de RegistrarCobrancaAssinatura após o commit. Valor sempre em R$.
 */
class AssinaturaRenovadaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $plano,
        public float $valor,
        public ?string $proximaRenovacao = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $valorFmt = 'R$ '.number_format($this->valor, 2, ',', '.');

        $msg = (new MailMessage)
            ->subject('Assinatura renovada — '.$this->plano)
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Sua assinatura do plano **'.$this->plano.'** foi renovada com sucesso.')
            ->line('**Valor cobrado:** '.$valorFmt);

        if ($this->proximaRenovacao !== null) {
            $msg->line('**Próxima renovação:** '.$this->proximaRenovacao);
        }

        return $msg
            ->action('Ver minha assinatura', url('/app/plano'))
            ->line('Obrigado por continuar com o FiscalDock.');
    }
}
