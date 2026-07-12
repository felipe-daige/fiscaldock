<?php

use App\Models\Alerta;
use App\Models\User;
use App\Notifications\AlertaImediatoNotification;
use App\Notifications\ResumoSemanalNotification;
use App\Services\AlertaCentralService;
use Illuminate\Support\Facades\Notification;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
});

/**
 * Dispara o gate real (privado) pelo caminho público de monitoramento — que passa
 * pelo mesmo choke point `upsertAlerta` usado por `recalcular`.
 */
function registrarAlerta(User $user, string $severidade, string $categoria = 'monitoramento', array $extra = []): Alerta
{
    $service = app(AlertaCentralService::class);

    if ($categoria === 'monitoramento') {
        return $service->registrarAlertaMonitoramento(array_merge([
            'user_id' => $user->id,
            'tipo' => 'cnpj_situacao_irregular',
            'severidade' => $severidade,
            'titulo' => 'CNPJ ficou irregular',
            'descricao' => 'A situação cadastral mudou para SUSPENSA.',
        ], $extra));
    }

    // Categorias operacionais não têm entrada pública direta — cria e reprocessa
    // pelo mesmo gate via registrarAlertaMonitoramento seria mentira; então usa o
    // hash + upsert do próprio serviço por reflexão controlada.
    throw new InvalidArgumentException('categoria não suportada no helper');
}

// --------------------------------------------------------------- alerta imediato

it('alerta alta dispara e-mail imediato e grava notificado_em', function () {
    $user = User::factory()->create(['alertas_monitoramento' => true]);

    $alerta = registrarAlerta($user, 'alta');

    Notification::assertSentTo($user, AlertaImediatoNotification::class,
        fn ($n) => $n->alerta->id === $alerta->id);

    expect($alerta->fresh()->notificado_em)->not->toBeNull();
});

it('alerta baixa nunca dispara e-mail imediato', function () {
    $user = User::factory()->create(['alertas_monitoramento' => true]);

    $alerta = registrarAlerta($user, 'baixa');

    Notification::assertNothingSent();
    expect($alerta->fresh()->notificado_em)->toBeNull();
});

it('toggle desligado nao dispara e-mail', function () {
    $user = User::factory()->create(['alertas_monitoramento' => false]);

    $alerta = registrarAlerta($user, 'alta');

    Notification::assertNothingSent();
    expect($alerta->fresh()->notificado_em)->toBeNull();
});

it('toggle de monitoramento e o que gateia alerta de monitoramento (nao o operacional)', function () {
    $user = User::factory()->create([
        'alertas_monitoramento' => false,
        'alertas_operacionais' => true,
    ]);

    registrarAlerta($user, 'alta');

    Notification::assertNothingSent();
});

it('re-detectar o mesmo alerta nao reenvia e-mail (notificado_em e a guarda)', function () {
    $user = User::factory()->create(['alertas_monitoramento' => true]);

    registrarAlerta($user, 'alta');
    registrarAlerta($user, 'alta'); // upsert do mesmo hash — o que alertas:recalcular faz todo dia
    registrarAlerta($user, 'alta');

    Notification::assertSentToTimes($user, AlertaImediatoNotification::class, 1);
});

// --------------------------------------------------------------- resumo semanal

function criarAlerta(User $user, string $severidade, float $valorRisco = 0, ?string $createdAt = null): Alerta
{
    $alerta = Alerta::create([
        'user_id' => $user->id,
        'hash' => hash('sha256', uniqid('', true)),
        'tipo' => 'situacao_irregular',
        'categoria' => 'compliance',
        'severidade' => $severidade,
        'titulo' => 'Alerta de teste ('.$severidade.')',
        'descricao' => 'Descrição de teste.',
        'valor_risco' => $valorRisco,
        'status' => 'ativo',
        // Já nasce notificado pra não poluir o assert de e-mail imediato deste bloco.
        'notificado_em' => now(),
    ]);

    if ($createdAt) {
        $alerta->forceFill(['created_at' => $createdAt])->saveQuietly();
    }

    return $alerta;
}

it('resumo semanal nao vai pra quem nao teve alerta nem atividade', function () {
    User::factory()->create(['resumo_periodico' => true]);

    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();

    Notification::assertNothingSent();
});

it('resumo semanal respeita o toggle resumo_periodico', function () {
    $user = User::factory()->create(['resumo_periodico' => false]);
    criarAlerta($user, 'alta');

    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();

    Notification::assertNotSentTo($user, ResumoSemanalNotification::class);
});

it('resumo semanal sai com os numeros do periodo', function () {
    $user = User::factory()->create(['resumo_periodico' => true]);

    criarAlerta($user, 'alta', 5000);
    criarAlerta($user, 'media');
    criarAlerta($user, 'baixa');
    // Fora da janela de 7 dias — não conta.
    criarAlerta($user, 'alta', 0, now()->subDays(20)->toDateTimeString());

    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();

    Notification::assertSentTo($user, ResumoSemanalNotification::class, function ($n) {
        expect($n->resumo['por_severidade'])->toBe(['alta' => 1, 'media' => 1, 'baixa' => 1]);
        expect($n->resumo['destaques'][0]['severidade'])->toBe('alta');
        expect($n->resumo['vazio'])->toBeFalse();

        return true;
    });
});
