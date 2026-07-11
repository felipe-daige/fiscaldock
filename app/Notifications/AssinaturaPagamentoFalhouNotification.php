<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Dunning: cobrança recorrente de assinatura recusada/cancelada (preapproval MP).
 * A assinatura entra em inadimplência — usuário precisa atualizar o cartão.
 * Disparada de RegistrarCobrancaAssinatura após o commit.
 */
class AssinaturaPagamentoFalhouNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $plano,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Falha na cobrança da sua assinatura')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Não conseguimos processar a cobrança da sua assinatura do plano **'.$this->plano.'**.')
            ->line('Sua assinatura está com pagamento pendente. Atualize o cartão para evitar a suspensão do plano.')
            ->action('Atualizar pagamento', url('/app/plano'))
            ->line('Se o cartão já foi atualizado, desconsidere este aviso.');
    }
}
