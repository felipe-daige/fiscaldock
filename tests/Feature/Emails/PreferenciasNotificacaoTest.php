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

function alertaResumo(User $user, string $createdAt): void
{
    $a = Alerta::create([
        'user_id' => $user->id, 'hash' => hash('sha256', uniqid('', true)),
        'tipo' => 'situacao_irregular', 'categoria' => 'compliance', 'severidade' => 'alta',
        'titulo' => 'x', 'descricao' => 'y', 'status' => 'ativo', 'notificado_em' => now(),
    ]);
    $a->forceFill(['created_at' => $createdAt])->saveQuietly();
}

it('resumo MENSAL só é enviado na 1ª segunda do mês', function () {
    Notification::fake();
    $user = User::factory()->create(['resumo_periodico' => true, 'resumo_frequencia' => 'mensal']);
    alertaResumo($user, '2026-07-02 12:00:00'); // dentro da janela de 30d do dia 6

    // Dia 15 (não é 1ª semana) → pula.
    Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 7, 15));
    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();
    Notification::assertNotSentTo($user, ResumoSemanalNotification::class);

    // Dia 6 (1ª semana) → envia.
    Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 7, 6));
    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();
    Notification::assertSentTo($user, ResumoSemanalNotification::class);

    Carbon\Carbon::setTestNow();
});

it('resumo SEMANAL é enviado em qualquer segunda', function () {
    Notification::fake();
    $user = User::factory()->create(['resumo_periodico' => true, 'resumo_frequencia' => 'semanal']);
    alertaResumo($user, '2026-07-10 12:00:00'); // dentro da janela de 7d do dia 15

    Carbon\Carbon::setTestNow(Carbon\Carbon::create(2026, 7, 15));
    $this->artisan('alertas:enviar-resumo-semanal')->assertSuccessful();
    Notification::assertSentTo($user, ResumoSemanalNotification::class);

    Carbon\Carbon::setTestNow();
});
