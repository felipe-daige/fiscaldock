<?php

use App\Models\Alerta;
use App\Models\User;
use App\Notifications\AlertaImediatoNotification;
use App\Notifications\ResumoSemanalNotification;
use App\Services\AlertaCentralService;
use Illuminate\Support\Facades\Notification;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

// -------------------------------------------------- página /app/configuracoes

it('a pagina de configuracoes renderiza (nao 404) e mostra os controles', function () {
    $this->actingAs(User::factory()->create())
        ->get('/app/configuracoes')
        ->assertOk()
        ->assertSee('Alertas por e-mail', false)
        ->assertSee('data-campo="alertas_severidade_minima"', false)
        ->assertSee('data-campo="resumo_frequencia"', false);
});

it('os toggles são checkbox REAL (acessível por teclado), não span com onclick', function () {
    $user = User::factory()->create(['alertas_operacionais' => true, 'resumo_periodico' => false]);

    $this->actingAs($user)->get('/app/configuracoes')->assertOk()
        // <input type=checkbox> dentro do <label> → Tab+Espaço funcionam e o label
        // inteiro fica clicável (um <span> com onclick não dá nenhum dos dois).
        ->assertSee('type="checkbox" class="config-toggle-input sr-only" data-campo="alertas_operacionais" checked', false)
        // Desligado não vem com `checked`.
        ->assertSee('data-campo="resumo_periodico"', false)
        ->assertDontSee('data-campo="resumo_periodico" checked', false);
});

it('salva a severidade mínima (enum)', function () {
    $user = User::factory()->create(['alertas_severidade_minima' => 'media']);

    $this->actingAs($user)->patchJson('/app/configuracoes/notificacoes', [
        'campo' => 'alertas_severidade_minima', 'valor' => 'alta',
    ])->assertOk()->assertJson(['success' => true, 'valor' => 'alta']);

    expect($user->fresh()->alertas_severidade_minima)->toBe('alta');
});

it('rejeita valor de enum fora da lista', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patchJson('/app/configuracoes/notificacoes', [
        'campo' => 'resumo_frequencia', 'valor' => 'diario',
    ])->assertStatus(422);
});

it('input malformado dá 422, não 500 (campo como array derrubava o endpoint)', function () {
    $user = User::factory()->create();

    // `(string)` sobre array emitia "Array to string conversion" → ErrorException → 500.
    $this->actingAs($user)->patchJson('/app/configuracoes/notificacoes', [
        'campo' => ['alertas_severidade_minima'], 'valor' => 'alta',
    ])->assertStatus(422);

    $this->actingAs($user)->patchJson('/app/configuracoes/notificacoes', [
        'campo' => ['x' => 'y'], 'valor' => true,
    ])->assertStatus(422);
});

it('os toggles booleanos continuam funcionando', function () {
    $user = User::factory()->create(['alertas_operacionais' => true]);

    $this->actingAs($user)->patchJson('/app/configuracoes/notificacoes', [
        'campo' => 'alertas_operacionais', 'valor' => false,
    ])->assertOk();

    expect($user->fresh()->alertas_operacionais)->toBeFalse();
});

// -------------------------------------------------- gate respeita severidade mínima

it('severidade mínima "alta" NÃO manda e-mail imediato de alerta média', function () {
    Notification::fake();
    $user = User::factory()->create(['alertas_monitoramento' => true, 'alertas_severidade_minima' => 'alta']);

    app(AlertaCentralService::class)->registrarAlertaMonitoramento([
        'user_id' => $user->id, 'tipo' => 'cnpj_situacao_irregular',
        'severidade' => 'media', 'titulo' => 'x', 'descricao' => 'y',
    ]);

    Notification::assertNotSentTo($user, AlertaImediatoNotification::class);
});

it('severidade mínima "alta" ainda manda e-mail de alerta alta', function () {
    Notification::fake();
    $user = User::factory()->create(['alertas_monitoramento' => true, 'alertas_severidade_minima' => 'alta']);

    app(AlertaCentralService::class)->registrarAlertaMonitoramento([
        'user_id' => $user->id, 'tipo' => 'cnpj_situacao_irregular',
        'severidade' => 'alta', 'titulo' => 'x', 'descricao' => 'y',
    ]);

    Notification::assertSentTo($user, AlertaImediatoNotification::class);
});

it('default "media" manda e-mail imediato de alerta média', function () {
    Notification::fake();
    $user = User::factory()->create(['alertas_monitoramento' => true]); // default media

    app(AlertaCentralService::class)->registrarAlertaMonitoramento([
        'user_id' => $user->id, 'tipo' => 'cnpj_situacao_irregular',
        'severidade' => 'media', 'titulo' => 'x', 'descricao' => 'y',
    ]);

    Notification::assertSentTo($user, AlertaImediatoNotification::class);
});

// -------------------------------------------------- resumo respeita frequência
//
// Cadência guardada por `users.ultimo_resumo_em` (não pela data do cron): rodar o
// comando 2x no mesmo período não reenvia, e a janela do próximo resumo começa onde
// o último terminou (janela fixa de 30d perdia alertas quando o intervalo entre 1as
// segundas era 35 dias).

// Relógio congelado vaza pros testes seguintes se um assert falhar antes do reset —
// afterEach garante o destravamento mesmo em falha.
afterEach(fn () => Carbon\Carbon::setTestNow());

function alertaResumo(User $user, string $createdAt): void
{
    $a = Alerta::create([
        'user_id' => $user->id, 'hash' => hash('sha256', uniqid('', true)),
        'tipo' => 'situacao_irregular', 'categoria' => 'compliance', 'severidade' => 'alta',
        'titulo' => 'x', 'descricao' => 'y', 'status' => 'ativo', 'notificado_em' => now(),
    ]);
    $a->forceFill(['created_at' => $createdAt])->saveQuietly();
}

it('resumo MENSAL sai 1x por mês civil (2º run no mesmo mês não reenvia)', function () {
    Notification::fake();
    Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 7, 6));

    $user = User::factory()->create(['resumo_periodico' => true, 'resumo_frequencia' => 'mensal']);
    alertaResumo($user, '2026-06-20 12:00:00');

    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();
    Notification::assertSentToTimes($user, ResumoSemanalNotification::class, 1);
    expect($user->fresh()->ultimo_resumo_em)->not->toBeNull();

    // Mesmo mês, semana seguinte (o cron roda toda segunda) → NÃO reenvia.
    Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 7, 13));
    alertaResumo($user, '2026-07-10 12:00:00');
    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();
    Notification::assertSentToTimes($user, ResumoSemanalNotification::class, 1);

    // Mês seguinte → reenvia.
    Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 8, 3));
    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();
    Notification::assertSentToTimes($user, ResumoSemanalNotification::class, 2);
});

it('a janela do mensal é ancorada no último envio — não perde alertas no gap de 35 dias', function () {
    Notification::fake();

    // 1ª segunda de março/2026 = 02/03. Envio inicial fixa a âncora.
    Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 3, 2));
    $user = User::factory()->create(['resumo_periodico' => true, 'resumo_frequencia' => 'mensal']);
    alertaResumo($user, '2026-02-20 12:00:00');
    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();

    // Alerta em 04/03 — cai no buraco da janela fixa de 30 dias (06/04 − 30d = 07/03).
    alertaResumo($user, '2026-03-04 12:00:00');

    // 1ª segunda de abril/2026 = 06/04 → gap de 35 dias desde 02/03.
    Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 4, 6));
    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();

    // O alerta de 04/03 TEM que estar no resumo de abril (âncora = 02/03, não 07/03).
    Notification::assertSentTo($user, ResumoSemanalNotification::class, function ($n) {
        return array_sum($n->resumo['por_severidade']) === 1;
    });
});

it('resumo SEMANAL não reenvia se já saiu há menos de 6 dias', function () {
    Notification::fake();
    Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 7, 6));

    $user = User::factory()->create(['resumo_periodico' => true, 'resumo_frequencia' => 'semanal']);
    alertaResumo($user, '2026-07-02 12:00:00');

    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();
    Notification::assertSentToTimes($user, ResumoSemanalNotification::class, 1);

    // Run manual no dia seguinte → dentro da cadência, não reenvia.
    Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 7, 7));
    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();
    Notification::assertSentToTimes($user, ResumoSemanalNotification::class, 1);

    // Semana seguinte → reenvia.
    Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 7, 13));
    alertaResumo($user, '2026-07-11 12:00:00');
    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();
    Notification::assertSentToTimes($user, ResumoSemanalNotification::class, 2);
});

it('--force reenvia (janela nominal) e NÃO move a âncora do ciclo real', function () {
    Notification::fake();
    Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 7, 6));

    $user = User::factory()->create(['resumo_periodico' => true, 'resumo_frequencia' => 'mensal']);
    alertaResumo($user, '2026-07-02 12:00:00');

    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();
    $ancora = $user->fresh()->ultimo_resumo_em;

    // Reenvio manual: usa a janela nominal (a âncora deixaria a janela vazia).
    $this->artisan('alertas:enviar-resumo-semanal', ['--force' => true])->assertSuccessful();
    Notification::assertSentToTimes($user, ResumoSemanalNotification::class, 2);

    // Âncora intocada — um teste manual não pode suprimir o envio real do ciclo.
    expect($user->fresh()->ultimo_resumo_em->eq($ancora))->toBeTrue();
});

it('período vazio não move a âncora (o próximo resumo ainda cobre o intervalo todo)', function () {
    Notification::fake();
    Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 7, 6));

    $user = User::factory()->create(['resumo_periodico' => true, 'resumo_frequencia' => 'semanal']);

    // Sem alerta e sem atividade → não envia e não marca.
    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();
    Notification::assertNothingSent();
    expect($user->fresh()->ultimo_resumo_em)->toBeNull();
});
