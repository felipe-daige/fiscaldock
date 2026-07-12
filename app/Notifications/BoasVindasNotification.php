<?php

namespace App\Notifications;

use App\Support\Mail\Blocos;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Boas-vindas ao novo usuário (signup com trial).
 * Disparada de AuthController::createTrialAccount DEPOIS do DB::commit().
 * Valores comerciais sempre em R$.
 */
class BoasVindasNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public float $saldoTrial,
        public int $validadeDias,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = Blocos::comEtiqueta(new MailMessage, 'Conta ativada');

        return $mail
            ->subject('Sua conta FiscalDock está pronta')
            ->greeting('Bem-vindo, '.$notifiable->name.'.')
            ->line('Sua conta está no ar. Para você testar com dado real, já creditamos um saldo de boas-vindas — **sem cartão e sem cobrança automática** no fim do período.')
            ->line(Blocos::hero(
                Blocos::brl($this->saldoTrial),
                'Saldo de boas-vindas',
                'Válido por '.$this->validadeDias.' dias · vale para qualquer consulta'
            ))
            ->line('## Por onde começar')
            ->line(Blocos::passos([
                ['Consulte um CNPJ', 'Um cliente ou fornecedor. Sai o Score Fiscal com certidões, situação cadastral e regime tributário.'],
                ['Importe um SPED (EFD)', 'O BI Fiscal abre com faturamento, carga tributária e as divergências entre o que foi apurado e o que foi escriturado.'],
                ['Ligue o monitoramento', 'Seus CNPJs críticos passam a ser reconsultados sozinhos — você é avisado quando a situação vira.'],
            ]))
            ->action('Abrir meu painel', url('/app/dashboard'))
            ->line('Não sabe qual consulta usar no seu caso? Responda este e-mail — quem lê é gente, não robô.');
    }
}
