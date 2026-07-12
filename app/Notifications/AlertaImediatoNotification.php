<?php

namespace App\Notifications;

use App\Models\Alerta;
use App\Support\Mail\Blocos;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Alerta operacional/monitoramento de severidade alta ou média, no momento em que
 * é detectado. Gate + idempotência (`alertas.notificado_em`) ficam no
 * AlertaCentralService::notificarSeRelevante — aqui só a peça de e-mail.
 */
class AlertaImediatoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** Traduz a categoria interna no "de onde veio isso" que o usuário entende. */
    private const ORIGEM = [
        'monitoramento' => 'Monitoramento contínuo',
        'compliance' => 'Compliance de CNPJ',
        'notas_fiscais' => 'Acervo de notas fiscais',
        'importacao' => 'Importação de arquivos',
    ];

    public function __construct(public Alerta $alerta) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $critico = $this->alerta->severidade === 'alta';

        $dados = [
            'Origem' => self::ORIGEM[$this->alerta->categoria] ?? ucfirst((string) $this->alerta->categoria),
            'Detectado em' => $this->alerta->created_at?->format('d/m/Y \à\s H:i') ?? now()->format('d/m/Y \à\s H:i'),
        ];

        if ($this->alerta->total_afetados > 0) {
            $dados['Registros afetados'] = (string) $this->alerta->total_afetados;
        }

        // O valor NÃO entra na ficha: quando existe, ele é o hero (repetir seria ruído).

        $mail = Blocos::comEtiqueta(
            new MailMessage,
            $critico ? 'Risco alto detectado' : 'Ponto de atenção',
            $critico ? Blocos::VERMELHO : Blocos::AMBAR
        );

        $mail->subject(($critico ? '[Crítico] ' : '[Atenção] ').$this->alerta->titulo)
            ->greeting($this->alerta->titulo)
            ->line(Blocos::chipSeveridade($this->alerta->severidade))
            ->line($this->alerta->descricao);

        // O valor exposto é a headline quando existe — é o número que faz o contador agir.
        if ((float) $this->alerta->valor_risco > 0) {
            $mail->line(Blocos::hero(
                Blocos::brl((float) $this->alerta->valor_risco),
                'Valor exposto',
                'Em notas escrituradas com esta contraparte',
                Blocos::VERMELHO
            ));
        }

        $mail->line(Blocos::ficha($dados, 'Ficha do alerta'));

        if ($critico) {
            $mail->line(Blocos::destaque(
                '<strong style="color: #111827;">Por que não deixar pra depois</strong><br>'
                .'Certidão positiva trava licitação e crédito. Nota de fornecedor irregular é candidata '
                .'a glosa — o crédito tomado pode ser exigido de volta, com multa.',
                Blocos::VERMELHO
            ));
        }

        return $mail
            ->action('Ver o alerta e o que fazer', url('/app/alertas/'.$this->alerta->id))
            ->line('Já tratou? Marque como resolvido na central — ele sai do painel e para de pesar no seu score.');
    }
}
