<?php

namespace App\Notifications;

use App\Models\Account;
use App\Models\User;
use App\Support\Mail\Blocos;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Account $account,
        private readonly User $inviter,
        private readonly string $token,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return Blocos::comEtiqueta(new MailMessage, 'Convite de equipe')
            ->subject('Você foi convidado para a conta '.$this->account->nome)
            ->greeting('Olá!')
            ->line($this->inviter->name.' convidou você para trabalhar na conta '.$this->account->nome.' dentro da FiscalDock.')
            ->line('Você terá um login individual. O dono controla os módulos disponíveis e a conta compartilha o mesmo saldo de consultas.')
            ->action('Aceitar convite', route('equipe.convite.aceitar', ['token' => $this->token]))
            ->line('O convite vale por 7 dias e só pode ser utilizado uma vez.');
    }
}
