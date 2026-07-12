<?php

use App\Models\User;
use App\Services\Admin\AdminAcaoService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

// F2 — email_verified_at não pode ser marcado por mass-assignment (privilégio).

it('User::create com email_verified_at no array NÃO verifica (guardado)', function () {
    $user = User::create([
        'name' => 'Fulano',
        'sobrenome' => 'Silva',
        'email' => 'fulano@exemplo.com',
        'telefone' => '11999990000',
        'password' => 'Senha#Forte2026',
        'email_verified_at' => now(), // tentativa de mass-assign — deve ser ignorada
    ]);

    expect($user->fresh()->email_verified_at)->toBeNull();
    expect($user->hasVerifiedEmail())->toBeFalse();
});

it('fill() com email_verified_at de um request não escala privilégio', function () {
    $user = User::factory()->unverified()->create();

    // Simula endpoint que faça ->fill($request->all()) com o campo injetado.
    $user->fill(['name' => 'Novo Nome', 'email_verified_at' => now()])->save();

    expect($user->fresh()->name)->toBe('Novo Nome');
    expect($user->fresh()->email_verified_at)->toBeNull();
});

it('markEmailAsVerified (método explícito) continua verificando', function () {
    $user = User::factory()->unverified()->create();

    $user->markEmailAsVerified();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('admin criando usuário com toggle verificado ligado ainda marca verificado', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $novo = app(AdminAcaoService::class)->criarUsuario($admin, [
        'name' => 'Cliente',
        'sobrenome' => 'Novo',
        'email' => 'cliente@exemplo.com',
        'telefone' => '11988887777',
        'password' => 'Senha#Forte2026',
        'email_verified' => true,
    ], 'teste');

    expect($novo->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('admin criando usuário com toggle desligado NÃO marca verificado', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $novo = app(AdminAcaoService::class)->criarUsuario($admin, [
        'name' => 'Cliente',
        'sobrenome' => 'Dois',
        'email' => 'cliente2@exemplo.com',
        'telefone' => '11988886666',
        'password' => 'Senha#Forte2026',
        'email_verified' => false,
    ], 'teste');

    expect($novo->fresh()->email_verified_at)->toBeNull();
});
