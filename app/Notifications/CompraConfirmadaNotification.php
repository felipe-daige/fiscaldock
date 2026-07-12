<?php

namespace App\Notifications;

use App\Support\Mail\Blocos;
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
        $mail = Blocos::comEtiqueta(new MailMessage, 'Pagamento aprovado', Blocos::VERDE);

        return $mail
            ->subject('Pagamento confirmado · '.Blocos::brl($this->valor).' em saldo liberado')
            ->greeting('Seu saldo já está na conta.')
            ->line('O pagamento foi aprovado e o saldo **entrou na hora** — você não precisa esperar compensação para voltar a consultar.')
            ->line(Blocos::hero(
                Blocos::brl($this->valor),
                'Saldo liberado',
                'Não expira · vale para qualquer consulta',
                Blocos::VERDE
            ))
            ->line(Blocos::ficha([
                'Pacote' => $this->pacote,
                'Valor pago' => Blocos::brl($this->valor),
                'Forma' => 'Mercado Pago',
                'Data' => now()->format('d/m/Y \à\s H:i'),
                'Referência' => $this->mpPaymentId,
            ], 'Comprovante'))
            ->action('Ver meu saldo', url('/app/saldo'))
            ->line('O saldo serve para tudo: consulta de CNPJ, certidões, clearance de notas e monitoramento contínuo.')
            ->salutation('Guarde este e-mail — ele é o seu comprovante.');
    }
}
