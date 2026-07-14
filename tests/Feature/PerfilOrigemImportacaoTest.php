<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('perfil do cliente oferece atalho para o resultado da importação de origem', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'documento' => '12345678000195',
        'tipo_pessoa' => 'PJ',
        'razao_social' => 'Cliente Importado',
        'ativo' => true,
    ]);
    $importacao = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'origem-cliente.txt',
    ]);

    $this->actingAs($user)
        ->get("/app/cliente/{$cliente->id}")
        ->assertOk()
        ->assertSee('Origem')
        ->assertSee('origem-cliente.txt')
        ->assertSee('Ver resultado da importação')
        ->assertSee("href=\"/app/importacao/efd/{$importacao->id}\"", false);
});

it('perfil do participante oferece o mesmo atalho na célula de origem', function () {
    $user = User::factory()->create();
    $importacao = EfdImportacao::create([
        'user_id' => $user->id,
        'tipo_efd' => 'EFD PIS/COFINS',
        'filename' => 'origem-participante.txt',
    ]);
    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => '12345678901',
        'razao_social' => 'Participante Importado',
        'importacao_efd_id' => $importacao->id,
        'latitude' => -15.0,
        'longitude' => -47.0,
    ]);

    $this->actingAs($user)
        ->get("/app/participante/{$participante->id}")
        ->assertOk()
        ->assertSee('origem-participante.txt')
        ->assertSee('Ver resultado da importação')
        ->assertSee('Resumo Operacional')
        ->assertSee('Valor utilizado')
        ->assertSee('data-sidebar-assinatura="false"', false)
        ->assertDontSee('Saldo disponível')
        ->assertDontSee('Adicionar Saldo')
        ->assertDontSee('>Estatísticas<', false)
        ->assertSee("href=\"/app/importacao/efd/{$importacao->id}\"", false);
});

it('perfil do participante reserva a lateral somente para a assinatura ativa', function () {
    $user = User::factory()->create();
    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => '11222333000181',
        'razao_social' => 'Participante Monitorado',
        'latitude' => -15.0,
        'longitude' => -47.0,
    ]);
    $plano = MonitoramentoPlano::ativos()->first() ?? MonitoramentoPlano::create([
        'codigo' => 'licitacao',
        'nome' => 'Licitação',
        'consultas_incluidas' => [],
        'etapas' => [],
        'custo_creditos' => 4,
        'is_active' => true,
    ]);

    MonitoramentoAssinatura::create([
        'user_id' => $user->id,
        'participante_id' => $participante->id,
        'plano_id' => $plano->id,
        'status' => 'ativo',
        'frequencia_dias' => 30,
    ]);

    $this->actingAs($user)
        ->get("/app/participante/{$participante->id}")
        ->assertOk()
        ->assertSee('data-sidebar-assinatura="true"', false)
        ->assertSee('Assinatura Ativa')
        ->assertSee('Resumo Operacional')
        ->assertDontSee('Saldo disponível')
        ->assertDontSee('>Estatísticas<', false);
});
