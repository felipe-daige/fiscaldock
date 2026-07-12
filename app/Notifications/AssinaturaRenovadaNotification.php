<?php

namespace App\Notifications;

use App\Support\Mail\Blocos;
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
        $dados = [
            'Plano' => $this->plano,
            'Valor cobrado' => Blocos::brl($this->valor),
            'Data' => now()->format('d/m/Y \à\s H:i'),
        ];

        if ($this->proximaRenovacao !== null) {
            $dados['Próxima renovação'] = $this->proximaRenovacao;
        }

        $mail = Blocos::comEtiqueta(new MailMessage, 'Assinatura renovada', Blocos::VERDE);

        return $mail
            ->subject('Assinatura renovada · '.$this->plano)
            ->greeting('Sua assinatura foi renovada.')
            ->line('A cobrança do plano **'.$this->plano.'** foi aprovada e o saldo do ciclo já entrou na conta. Nada muda no seu acesso.')
            ->line(Blocos::ficha($dados, 'Comprovante'))
            ->action('Ver minha assinatura', url('/app/plano'))
            ->line('Saldo não usado do ciclo anterior é acumulado, não perdido. Você pode trocar de plano ou cancelar a qualquer momento, sem multa.')
            ->salutation('Guarde este e-mail — ele é o comprovante da cobrança.');
    }
}
