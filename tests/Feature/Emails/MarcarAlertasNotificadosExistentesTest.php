<?php

use App\Models\Alerta;
use App\Models\User;
use App\Notifications\AlertaImediatoNotification;
use App\Services\AlertaCentralService;
use Illuminate\Support\Facades\Notification;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

function alertaAtivo(User $user, string $severidade, ?string $notificadoEm = null): Alerta
{
    return Alerta::create([
        'user_id' => $user->id,
        'hash' => hash('sha256', uniqid('', true)),
        'tipo' => 'situacao_irregular',
        'categoria' => 'compliance',
        'severidade' => $severidade,
        'titulo' => 'Alerta '.$severidade,
        'descricao' => 'x',
        'status' => 'ativo',
        'notificado_em' => $notificadoEm,
    ]);
}

it('marca alertas ativos sem notificado_em e preserva os já notificados/resolvidos', function () {
    $user = User::factory()->create();

    $semNotificar = alertaAtivo($user, 'alta');
    $jaNotificado = alertaAtivo($user, 'alta', now()->subDay()->toDateTimeString());
    $resolvido = Alerta::create([
        'user_id' => $user->id,
        'hash' => hash('sha256', uniqid('', true)),
        'tipo' => 'situacao_irregular',
        'categoria' => 'compliance',
        'severidade' => 'alta',
        'titulo' => 'resolvido',
        'descricao' => 'x',
        'status' => 'resolvido',
        'notificado_em' => null,
    ]);

    $this->artisan('alertas:marcar-notificados-existentes')->assertSuccessful();

    expect($semNotificar->fresh()->notificado_em)->not->toBeNull();
    // Não sobrescreve quem já tinha notificado_em (mantém a marca original).
    expect($jaNotificado->fresh()->notificado_em->toDateString())->toBe(now()->subDay()->toDateString());
    // Resolvido não é ativo → intocado (continua nulo).
    expect($resolvido->fresh()->notificado_em)->toBeNull();
});

it('dry-run não grava nada', function () {
    $user = User::factory()->create();
    $a = alertaAtivo($user, 'media');

    $this->artisan('alertas:marcar-notificados-existentes --dry-run')->assertSuccessful();

    expect($a->fresh()->notificado_em)->toBeNull();
});

it('é idempotente: 2ª execução não faz nada', function () {
    $user = User::factory()->create();
    alertaAtivo($user, 'alta');

    $this->artisan('alertas:marcar-notificados-existentes')->assertSuccessful();
    $this->artisan('alertas:marcar-notificados-existentes')
        ->expectsOutputToContain('Nenhum alerta ativo sem notificado_em')
        ->assertSuccessful();
});

it('depois do backfill, recalcular NÃO reenvia e-mail dos alertas antigos', function () {
    Notification::fake();
    $user = User::factory()->create(['alertas_monitoramento' => true]);

    // Alerta antigo (pré-gate): ativo, alta, sem notificado_em.
    $antigo = alertaAtivo($user, 'alta');

    // Backfill roda antes do ligamento.
    $this->artisan('alertas:marcar-notificados-existentes')->assertSuccessful();

    // Re-detectar o mesmo alerta (o que recalcular faz) não deve reenviar.
    app(AlertaCentralService::class)->registrarAlertaMonitoramento([
        'user_id' => $user->id,
        'tipo' => 'cnpj_situacao_irregular',
        'severidade' => 'alta',
        'titulo' => 'x',
        'descricao' => 'y',
    ]);
    // O antigo continua marcado; um alerta NOVO (hash diferente) ainda notifica.
    expect($antigo->fresh()->notificado_em)->not->toBeNull();
    Notification::assertSentToTimes($user, AlertaImediatoNotification::class, 1); // só o novo
});
