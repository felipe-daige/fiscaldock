<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('renderiza a tela vazia de consultas x EFD', function () {
    $user = User::factory()->create();

    actingAs($user)->get('/app/bi/consultas-efd')
        ->assertOk()
        ->assertSee('Consultas CNPJ × EFD', false)
        ->assertSee('Nenhum participante consultado com movimentação EFD', false);
});

it('renderiza participante consultado com movimentacao EFD', function () {
    $user = User::factory()->create();
    $cliente = consultasEfdCliente($user);
    $plano = consultasEfdPlano();
    $importacao = consultasEfdImportacao($user, $cliente);
    $participante = consultasEfdParticipante($user, $cliente, '66666666000191', 'Fornecedor Consultado');

    consultasEfdResultado(consultasEfdLote($user, $plano), $participante, [
        'regime_tributario' => 'Lucro Presumido',
        'situacao_cadastral' => 'ATIVA',
    ]);
    consultasEfdNota($user, $cliente, $importacao, $participante, 'entrada', 777.5);

    actingAs($user)->get('/app/bi/consultas-efd')
        ->assertOk()
        ->assertSee('Participantes consultados com impacto fiscal', false)
        ->assertSee('Fornecedor Consultado')
        ->assertSee('Lucro Presumido')
        ->assertSee('777,50');
});

it('renderiza drill-down do participante na célula principal', function () {
    $user = User::factory()->create();
    $cliente = consultasEfdCliente($user);
    $plano = consultasEfdPlano();
    $importacao = consultasEfdImportacao($user, $cliente);
    $participante = consultasEfdParticipante($user, $cliente, '12121212000121', 'Drilldown Fornecedor');

    consultasEfdResultado(consultasEfdLote($user, $plano), $participante, [
        'regime_tributario' => 'Lucro Presumido',
        'situacao_cadastral' => 'ATIVA',
    ]);
    consultasEfdNota($user, $cliente, $importacao, $participante, 'entrada', 100);

    actingAs($user)->get('/app/bi/consultas-efd')
        ->assertOk()
        ->assertSee('href="'.route('app.participante', $participante->id).'"', false)
        ->assertSee('data-link', false);
});
