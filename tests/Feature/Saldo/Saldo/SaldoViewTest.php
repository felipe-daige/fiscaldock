<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('mostra o pacote avulso como "Volume" (sem colidir com o tier Enterprise)', function () {
    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/saldo')->assertOk()->getContent();

    expect($html)->toContain('Volume');
    // O card de oferta não deve mais exibir o nome "Enterprise" (que agora é tier de assinatura).
    expect($html)->not->toContain('>Enterprise<');
});

it('usa a validade do trial do config (não hardcoda 30 dias)', function () {
    config(['trial.validade_dias' => 60]);

    $user = User::factory()->create();
    actingAs($user);

    $html = get('/app/saldo')->assertOk()->getContent();

    expect($html)->toContain('expira em 60 dias');
    expect($html)->not->toContain('expira em 30 dias');
});
