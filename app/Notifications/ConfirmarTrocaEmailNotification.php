<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Confirmação de troca de e-mail — enviada ao e-mail NOVO (on-demand, o notifiable
 * ainda não é o e-mail da conta). Só ao clicar o link é que `pending_email` vira
 * `email`. O e-mail antigo continua válido pra login até lá.
 */
class ConfirmarTrocaEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $userId,
        public string $nome,
        public string $novoEmail,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'perfil.email.confirmar',
            now()->addMinutes(60),
            ['user' => $this->userId, 'hash' => sha1($this->novoEmail)],
        );

        $mail = \App\Support\Mail\Blocos::comEtiqueta(new MailMessage, 'Troca de e-mail');

        return $mail
            ->subject('Confirme seu novo e-mail de acesso')
            ->greeting('Olá, '.$this->nome.'.')
            ->line('Pediram para trocar o e-mail de acesso da conta FiscalDock para **'.$this->novoEmail.'** — este endereço.')
            ->line(\App\Support\Mail\Blocos::destaque(
                'Nada mudou ainda. A troca só vale <strong>depois</strong> que você confirmar por aqui: até lá, '
                .'o login continua sendo feito pelo e-mail antigo. É assim de propósito, para que um endereço '
                .'digitado errado nunca tranque você fora da conta.'
            ))
            ->action('Confirmar este e-mail', $url)
            ->line('O link vale por 60 minutos e só pode ser usado uma vez.')
            ->line('Se não foi você, ignore — sem a confirmação, nada muda na conta.');
    }
}
