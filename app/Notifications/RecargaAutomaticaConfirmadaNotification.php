<?php

namespace App\Notifications;

use App\Support\Mail\Blocos;
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
        $mail = Blocos::comEtiqueta(new MailMessage, 'Recarga automática', Blocos::VERDE);

        return $mail
            ->subject('Recarga automática · '.Blocos::brl($this->valor).' adicionados ao saldo')
            ->greeting('Recarregamos seu saldo automaticamente.')
            ->line('Cobramos o cartão cadastrado e o crédito já entrou. Suas consultas e o **monitoramento contínuo** seguem rodando sem interrupção.')
            ->line(Blocos::hero(
                Blocos::brl($this->valor),
                'Saldo adicionado',
                'Cobrado no cartão cadastrado',
                Blocos::VERDE
            ))
            ->line(Blocos::ficha([
                'Pacote' => $this->pacote,
                'Valor cobrado' => Blocos::brl($this->valor),
                'Forma' => 'Mercado Pago',
                'Data' => now()->format('d/m/Y \à\s H:i'),
                'Referência' => $this->mpPaymentId,
            ], 'Comprovante'))
            ->action('Ver meu saldo', url('/app/creditos'))
            ->line('Você define o gatilho e o valor do pacote — e desliga quando quiser. Sem essa configuração ativa, nada é cobrado.')
            ->salutation('Guarde este e-mail — ele é o seu comprovante.');
    }
}
