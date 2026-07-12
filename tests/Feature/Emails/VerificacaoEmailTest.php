<?php

use App\Models\User;
use App\Notifications\BoasVindasNotification;
use App\Notifications\ConfirmarTrocaEmailNotification;
use App\Notifications\VerifyEmailQueued;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
});

// ---------------------------------------------------------------- Fase 4 — boas-vindas

it('signup dispara boas-vindas e verificacao de e-mail', function () {
    $resp = $this->postJson('/criar-conta', [
        'nome' => 'Ana',
        'sobrenome' => 'Silva',
        'email' => 'ana@exemplo.com.br',
        'telefone' => '67999990000',
        'senha' => 'Senha#Forte2026',
        'senha_confirmation' => 'Senha#Forte2026',
        'empresa' => 'Exemplo Ltda',
        'cargo' => 'Contador',
        'documento' => '11222333000181',
        'faturamento' => 'ate_1m',
        'desafio_principal' => 'compliance',
        'terms_aceitos' => '1',
    ]);

    // signup detecta AJAX só por X-Requested-With; via JSON puro ele redireciona.
    $resp->assertRedirect('/app/dashboard');

    $user = User::where('email', 'ana@exemplo.com.br')->firstOrFail();

    Notification::assertSentTo($user, BoasVindasNotification::class, function ($n) {
        // Valor sempre em R$ — "crédito" não existe no produto.
        expect($n->saldoTrial)->toBeGreaterThan(0);
        expect($n->validadeDias)->toBe((int) config('trial.validade_dias'));

        return true;
    });
    Notification::assertSentTo($user, VerifyEmailQueued::class);
});

it('boas-vindas e verificacao sao enfileiradas, nao sincronas', function () {
    expect(new BoasVindasNotification(12.0, 60))->toBeInstanceOf(ShouldQueue::class);
    expect(new VerifyEmailQueued)->toBeInstanceOf(ShouldQueue::class);
    expect(new ConfirmarTrocaEmailNotification(1, 'Ana', 'x@y.com'))->toBeInstanceOf(ShouldQueue::class);
});

// ---------------------------------------------------------- Fase 3 — verificar e-mail

it('link assinado de verificacao marca o e-mail como verificado', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect('/app/perfil');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('link de verificacao aberto DESLOGADO cai no /login com aviso (nao some o flash)', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    // Sem actingAs: link aberto em outro aparelho/navegador. Aplica a verificação e
    // manda pro /login (que exibe session('status')), não pro /app/perfil autenticado.
    $this->get($url)->assertRedirect('/login')->assertSessionHas('status');
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('link de verificacao adulterado e rejeitado', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1('outro@email.com'),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect('/app/perfil')->assertSessionHas('error');
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('link de verificacao sem assinatura valida da 403', function () {
    $user = User::factory()->unverified()->create();

    $this->get("/email/verificar/{$user->id}/".sha1($user->email))->assertForbidden();
});

it('verification.notice (F7 preventivo) existe e nao da 500', function () {
    // Deslogado → login.
    $this->get('/email/verificar')->assertRedirect('/login');

    // Logado → perfil.
    $this->actingAs(User::factory()->create())->get('/email/verificar')->assertRedirect('/app/perfil');
});

it('reenviar verificacao dispara VerifyEmail e recusa quem ja verificou', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->postJson('/app/perfil/email/reenviar')
        ->assertOk()->assertJson(['success' => true]);
    Notification::assertSentTo($user, VerifyEmailQueued::class);

    $verificado = User::factory()->create(); // factory já vem verificado
    $this->actingAs($verificado)->postJson('/app/perfil/email/reenviar')
        ->assertStatus(422)->assertJson(['success' => false]);
});

// ------------------------------------------------------------ Fase 3 — trocar e-mail

it('trocar e-mail nao aplica nada ate confirmar; e-mail antigo continua valendo', function () {
    $user = User::factory()->create(['email' => 'antigo@exemplo.com', 'password' => 'Senha#Forte2026']);

    $this->actingAs($user)->patchJson('/app/perfil/email', [
        'email' => 'novo@exemplo.com',
        'current_password' => 'Senha#Forte2026',
    ])->assertOk()->assertJson(['success' => true]);

    $user->refresh();
    expect($user->email)->toBe('antigo@exemplo.com');
    expect($user->pending_email)->toBe('novo@exemplo.com');

    Notification::assertSentOnDemand(
        ConfirmarTrocaEmailNotification::class,
        fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === 'novo@exemplo.com'
            && $n->novoEmail === 'novo@exemplo.com'
    );
});

it('trocar e-mail exige senha atual correta', function () {
    $user = User::factory()->create(['email' => 'antigo@exemplo.com', 'password' => 'Senha#Forte2026']);

    $this->actingAs($user)->patchJson('/app/perfil/email', [
        'email' => 'novo@exemplo.com',
        'current_password' => 'errada',
    ])->assertStatus(422);

    expect($user->fresh()->pending_email)->toBeNull();
});

it('trocar e-mail recusa endereco ja usado por outra conta', function () {
    User::factory()->create(['email' => 'tomado@exemplo.com']);
    $user = User::factory()->create(['email' => 'antigo@exemplo.com', 'password' => 'Senha#Forte2026']);

    $this->actingAs($user)->patchJson('/app/perfil/email', [
        'email' => 'tomado@exemplo.com',
        'current_password' => 'Senha#Forte2026',
    ])->assertStatus(422);
});

it('confirmar troca aplica o novo e-mail e marca verificado', function () {
    $user = User::factory()->unverified()->create([
        'email' => 'antigo@exemplo.com',
        'pending_email' => 'novo@exemplo.com',
    ]);

    $url = URL::temporarySignedRoute('perfil.email.confirmar', now()->addMinutes(60), [
        'user' => $user->id,
        'hash' => sha1('novo@exemplo.com'),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect('/app/perfil')->assertSessionHas('success');

    $user->refresh();
    expect($user->email)->toBe('novo@exemplo.com');
    expect($user->pending_email)->toBeNull();
    expect($user->hasVerifiedEmail())->toBeTrue();
});

it('link velho e rejeitado depois de um pedido de troca mais novo', function () {
    $user = User::factory()->create(['email' => 'antigo@exemplo.com', 'pending_email' => 'primeiro@exemplo.com']);

    $urlVelho = URL::temporarySignedRoute('perfil.email.confirmar', now()->addMinutes(60), [
        'user' => $user->id,
        'hash' => sha1('primeiro@exemplo.com'),
    ]);

    // Pedido novo substitui o pendente.
    $user->update(['pending_email' => 'segundo@exemplo.com']);

    $this->actingAs($user)->get($urlVelho)->assertRedirect('/app/perfil')->assertSessionHas('error');
    expect($user->fresh()->email)->toBe('antigo@exemplo.com');
});

it('e-mail tomado por outra conta no meio-tempo rejeita a confirmacao', function () {
    $user = User::factory()->create(['email' => 'antigo@exemplo.com', 'pending_email' => 'disputado@exemplo.com']);

    $url = URL::temporarySignedRoute('perfil.email.confirmar', now()->addMinutes(60), [
        'user' => $user->id,
        'hash' => sha1('disputado@exemplo.com'),
    ]);

    User::factory()->create(['email' => 'disputado@exemplo.com']);

    $this->actingAs($user)->get($url)->assertRedirect('/app/perfil')->assertSessionHas('error');

    $user->refresh();
    expect($user->email)->toBe('antigo@exemplo.com');
    expect($user->pending_email)->toBeNull();
});

it('cancelar troca limpa o pedido pendente', function () {
    $user = User::factory()->create(['pending_email' => 'novo@exemplo.com']);

    $this->actingAs($user)->deleteJson('/app/perfil/email')->assertOk()->assertJson(['success' => true]);

    expect($user->fresh()->pending_email)->toBeNull();
});
