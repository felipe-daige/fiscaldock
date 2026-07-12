<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\TestCase;

uses(TestCase::class);

test('e-mail de redefinição de senha usa cópia em pt-BR e link correto', function () {
    $user = User::factory()->make(['email' => 'maria@example.com', 'name' => 'Maria']);

    $notification = new ResetPassword('token-abc-123');
    $mail = $notification->toMail($user);

    expect($mail->subject)->toBe('Redefinir a senha da sua conta');
    expect($mail->greeting)->toBe('Olá, Maria.');
    expect(collect($mail->introLines)->implode(' '))->toContain('redefinir a senha');
    expect($mail->actionUrl)->toContain('/redefinir-senha/token-abc-123');
    expect($mail->actionUrl)->toContain('email=maria%40example.com');
});

test('e-mail de redefinição de senha renderizado não vaza texto padrão em inglês', function () {
    $user = User::factory()->make(['email' => 'maria@example.com', 'name' => 'Maria']);

    $html = (string) (new ResetPassword('token-abc-123'))->toMail($user)->render();

    expect($html)->not->toContain('Regards,')
        ->not->toContain('having trouble clicking')
        ->not->toContain('All rights reserved')
        // Marcadores pt-BR hardcoded (independem de config('app.name'), que no ambiente
        // de teste não é "FiscalDock"): tagline do rodapé + subcopy do botão.
        ->toContain('Inteligência fiscal e tributária')
        ->toContain('não funcionar');
});
