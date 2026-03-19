<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redireciona para login quando não autenticado', function () {
    $response = $this->get('/app/bi');
    $response->assertRedirect('/login');
});

it('exibe a página BI para usuário autenticado', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/app/bi');
    $response->assertStatus(200);
    $response->assertViewHas('periodoAtivo');
    $response->assertViewHas('filtros');
});

it('resolve periodo mes_atual', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/app/bi?periodo=mes_atual');
    $response->assertViewHas('periodoAtivo', 'mes_atual');
    $filtros = $response->viewData('filtros');
    expect($filtros['data_inicio'])->toBe(now()->startOfMonth()->format('d/m/Y'));
});

it('resolve periodo mes_anterior', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/app/bi?periodo=mes_anterior');
    $response->assertViewHas('periodoAtivo', 'mes_anterior');
    $filtros = $response->viewData('filtros');
    expect($filtros['data_inicio'])->toBe(now()->subMonth()->startOfMonth()->format('d/m/Y'));
});

it('resolve periodo personalizado com datas', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)
        ->get('/app/bi?periodo=personalizado&data_inicio=2026-01-01&data_fim=2026-01-31');
    $response->assertViewHas('periodoAtivo', 'personalizado');
    $filtros = $response->viewData('filtros');
    expect($filtros['data_inicio'])->toBe('01/01/2026');
    expect($filtros['data_fim'])->toBe('31/01/2026');
});

it('resolve periodo ano_atual', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/app/bi?periodo=ano_atual');
    $response->assertViewHas('periodoAtivo', 'ano_atual');
    $filtros = $response->viewData('filtros');
    expect($filtros['data_inicio'])->toBe(now()->startOfYear()->format('d/m/Y'));
});

it('resolve periodo trimestre_atual', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/app/bi?periodo=trimestre_atual');
    $response->assertViewHas('periodoAtivo', 'trimestre_atual');
    $filtros = $response->viewData('filtros');
    expect($filtros['data_inicio'])->toBe(now()->firstOfQuarter()->format('d/m/Y'));
    expect($filtros['data_fim'])->toBe(now()->lastOfQuarter()->format('d/m/Y'));
});

it('resolve periodo semestre_atual com início e fim corretos', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/app/bi?periodo=semestre_atual');
    $response->assertViewHas('periodoAtivo', 'semestre_atual');
    $filtros = $response->viewData('filtros');
    $mes = (int) now()->format('n');
    $expectedInicio = $mes <= 6
        ? now()->startOfYear()->format('d/m/Y')
        : now()->month(7)->startOfMonth()->format('d/m/Y');
    expect($filtros['data_inicio'])->toBe($expectedInicio);
});
