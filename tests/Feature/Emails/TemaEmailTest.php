<?php

use App\Models\Alerta;
use App\Models\User;
use App\Notifications\AlertaDigestNotification;
use App\Notifications\AlertaImediatoNotification;
use App\Notifications\AssinaturaPagamentoFalhouNotification;
use App\Notifications\AssinaturaRenovadaNotification;
use App\Notifications\BoasVindasNotification;
use App\Notifications\CompraConfirmadaNotification;
use App\Notifications\ConfirmarTrocaEmailNotification;
use App\Notifications\RecargaAutomaticaConfirmadaNotification;
use App\Notifications\ResetPasswordQueued;
use App\Notifications\ResumoSemanalNotification;
use App\Notifications\VerifyEmailQueued;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Array transport: notifyNow monta o MIME final (com CID + tema inlinado) sem entregar.
    config(['mail.default' => 'array']);
});

/** Renderiza a notification pro MIME final e devolve subject/html/anexos. */
function renderTema(User $user, $notification): array
{
    $user->notifyNow($notification);

    $msg = app('mailer')->getSymfonyTransport()->messages()->last()->getOriginalMessage();

    return [
        'subject' => $msg->getSubject(),
        'html' => $msg->getHtmlBody(),
        'anexos' => count($msg->getAttachments()),
    ];
}

function alertaTema(string $severidade = 'alta', float $valor = 184320.50): Alerta
{
    $a = new Alerta([
        'titulo' => 'Fornecedor irregular com 12 notas',
        'descricao' => 'RAIZEN COMBUSTIVEIS S.A. está com situação SUSPENSA.',
        'severidade' => $severidade,
        'categoria' => 'compliance',
        'total_afetados' => 12,
        'valor_risco' => $valor,
    ]);
    $a->id = 123;
    $a->created_at = now();

    return $a;
}

function resumoTema(): array
{
    return [
        'periodo_inicio' => now()->subDays(7),
        'periodo_fim' => now(),
        'por_severidade' => ['alta' => 2, 'media' => 1, 'baixa' => 3],
        'destaques' => [
            ['id' => 1, 'titulo' => 'Fornecedor irregular', 'severidade' => 'alta', 'valor_risco' => 5000.0],
        ],
        'consultas' => 10, 'clearance' => 5, 'importacoes' => 2,
    ];
}

dataset('notifications', [
    'boas-vindas' => [fn () => new BoasVindasNotification(12.0, 60)],
    'verificar-email' => [fn () => new VerifyEmailQueued],
    'trocar-email' => [fn () => new ConfirmarTrocaEmailNotification(999999, 'Felipe', 'novo@exemplo.com')],
    'reset-senha' => [fn () => new ResetPasswordQueued('token-demo-1234567890')],
    'alerta-imediato' => [fn () => new AlertaImediatoNotification(alertaTema())],
    'alerta-digest' => [fn () => new AlertaDigestNotification([alertaTema('alta'), alertaTema('media', 4200.0)])],
    'resumo-semanal' => [fn () => new ResumoSemanalNotification(resumoTema())],
    'compra' => [fn () => new CompraConfirmadaNotification('Pacote R$ 200', 200.0, 'PAY-1')],
    'recarga' => [fn () => new RecargaAutomaticaConfirmadaNotification('Pacote R$ 100', 100.0, 'PAY-2')],
    'assinatura-renovada' => [fn () => new AssinaturaRenovadaNotification('Profissional', 149.0, '12/08/2026')],
    'assinatura-falhou' => [fn () => new AssinaturaPagamentoFalhouNotification('Profissional')],
]);

it('todo e-mail carrega a marca FiscalDock (navy + acento dourado)', function (Closure $fazer) {
    $r = renderTema(User::factory()->create(), $fazer());

    // Navy da marca (header + fio de acento).
    expect($r['html'])->toContain('#102c4d');       // navy do header
    expect($r['html'])->toContain('#d19a2e');        // dourado (fio + "Dock" no rodapé)
    // Wordmark no header e assinatura no rodapé.
    expect($r['html'])->toContain('Fiscal');
    expect($r['html'])->toContain('Inteligência fiscal e tributária');
})->with('notifications');

it('todo e-mail embute a logo por CID (1 anexo inline, sem host externo)', function (Closure $fazer) {
    $r = renderTema(User::factory()->create(), $fazer());

    expect($r['html'])->toMatch('/src="cid:[^"]+"/');
    expect($r['anexos'])->toBe(1);
})->with('notifications');

it('todo e-mail trava tema claro no dark mode (only light + gradiente iOS)', function (Closure $fazer) {
    $r = renderTema(User::factory()->create(), $fazer());

    expect($r['html'])->toContain('color-scheme: only light');
    // Fundos saturados travados por gradiente (o que segura o navy no iPhone).
    expect($r['html'])->toContain('linear-gradient');
})->with('notifications');

it('todo e-mail tem assunto e não vaza a cópia em inglês do Laravel', function (Closure $fazer) {
    $r = renderTema(User::factory()->create(), $fazer());

    expect($r['subject'])->not->toBe('');
    foreach (['Whoops!', 'Regards,', 'Hello!', 'Verify Email Address', 'Reset Password', "If you're having trouble"] as $ingles) {
        expect($r['html'])->not->toContain($ingles);
    }
})->with('notifications');

it('o Mailable de recarga pausada usa o mesmo tema de marca', function () {
    $user = User::factory()->create();

    \Illuminate\Support\Facades\Mail::to($user->email)
        ->sendNow(new \App\Mail\RecargaAutomaticaPausada($user, 'cartão recusado'));

    $msg = app('mailer')->getSymfonyTransport()->messages()->last()->getOriginalMessage();
    $html = $msg->getHtmlBody();

    expect($html)->toContain('#102c4d');
    expect($html)->toContain('#d19a2e');
    expect($html)->toMatch('/src="cid:[^"]+"/');
    expect(count($msg->getAttachments()))->toBe(1);
    expect($html)->toContain('color-scheme: only light');
});
