<?php

namespace App\Notifications;

use App\Support\Mail\Blocos;
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
        $mail = Blocos::comEtiqueta(new MailMessage, 'Ação necessária', Blocos::VERMELHO);

        return $mail
            ->error()
            ->subject('Ação necessária: cobrança recusada · '.$this->plano)
            ->greeting('Não conseguimos cobrar sua assinatura.')
            ->line('A cobrança do plano **'.$this->plano.'** foi recusada pelo emissor do cartão. Isso costuma ser cartão vencido, limite indisponível ou bloqueio do banco para cobrança recorrente.')
            ->line(Blocos::destaque(
                '<strong style="color: #111827;">O que acontece agora</strong><br>'
                .'Sua assinatura está <strong>pendente</strong>, não cancelada. O acesso continua por ora, mas sem '
                .'a regularização o plano é suspenso — e o monitoramento contínuo dos seus CNPJs para de rodar.',
                Blocos::VERMELHO
            ))
            ->action('Atualizar forma de pagamento', url('/app/plano'))
            ->line('Se você já atualizou o cartão, ignore este aviso — a nova tentativa acontece automaticamente.');
    }
}
