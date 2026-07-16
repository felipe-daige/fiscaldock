<?php

namespace App\Notifications;

use App\Support\Mail\Blocos;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Add-on recorrente (assento extra / espaço adicional) suspenso na renovação por saldo
 * insuficiente. O add-on foi zerado; nenhum dado é apagado (membros e arquivos permanecem,
 * só travam novos convites/uploads acima da nova capacidade). Recontratável a qualquer momento.
 */
class AddonSuspensoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $addon) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = Blocos::comEtiqueta(new MailMessage, 'Ação necessária', Blocos::VERMELHO);

        return $mail
            ->subject('Add-on suspenso por saldo insuficiente · '.$this->addon)
            ->greeting('Não conseguimos renovar seu '.$this->addon.'.')
            ->line('A renovação mensal do add-on **'.$this->addon.'** foi cancelada porque seu saldo não cobriu a cobrança.')
            ->line(Blocos::destaque(
                '<strong style="color: #111827;">Nada foi apagado.</strong><br>'
                .'Membros da equipe e arquivos continuam onde estão — apenas novos convites ou uploads acima da '
                .'capacidade atual ficam bloqueados até você recontratar o add-on.',
                Blocos::VERMELHO
            ))
            ->action('Adicionar saldo', url('/app/saldo'))
            ->line('Depois de recarregar, é só recontratar o add-on na tela correspondente.');
    }
}
