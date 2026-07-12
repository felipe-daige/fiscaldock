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
 *
 * Copia os campos do Alerta no construtor (como o AlertaDigestNotification): o payload
 * da fila fica enxuto e imune a mudança/exclusão do alerta antes de o worker rodar —
 * não serializa o model Eloquent inteiro (com `detalhes` jsonb).
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

    public int $alertaId;

    private string $titulo;

    private string $descricao;

    private string $severidade;

    private string $categoria;

    private int $totalAfetados;

    private float $valorRisco;

    private string $detectadoEm;

    public function __construct(Alerta $alerta)
    {
        $this->alertaId = (int) $alerta->id;
        $this->titulo = (string) $alerta->titulo;
        $this->descricao = (string) $alerta->descricao;
        $this->severidade = (string) $alerta->severidade;
        $this->categoria = (string) $alerta->categoria;
        $this->totalAfetados = (int) $alerta->total_afetados;
        $this->valorRisco = (float) $alerta->valor_risco;
        $this->detectadoEm = ($alerta->created_at ?? now())->format('d/m/Y \à\s H:i');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $critico = $this->severidade === 'alta';

        $dados = [
            'Origem' => self::ORIGEM[$this->categoria] ?? ucfirst($this->categoria),
            'Detectado em' => $this->detectadoEm,
        ];

        if ($this->totalAfetados > 0) {
            $dados['Registros afetados'] = (string) $this->totalAfetados;
        }

        // O valor NÃO entra na ficha: quando existe, ele é o hero (repetir seria ruído).

        $mail = Blocos::comEtiqueta(
            new MailMessage,
            $critico ? 'Risco alto detectado' : 'Ponto de atenção',
            $critico ? Blocos::VERMELHO : Blocos::AMBAR
        );

        $mail->subject(($critico ? '[Crítico] ' : '[Atenção] ').$this->titulo)
            ->greeting($this->titulo)
            ->line(Blocos::chipSeveridade($this->severidade))
            ->line($this->descricao);

        // O valor exposto é a headline quando existe — é o número que faz o contador agir.
        if ($this->valorRisco > 0) {
            $mail->line(Blocos::hero(
                Blocos::brl($this->valorRisco),
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
            ->action('Ver o alerta e o que fazer', url('/app/alertas/'.$this->alertaId))
            ->line('Já tratou? Marque como resolvido na central — ele sai do painel e para de pesar no seu score.');
    }
}
