<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Services\Dashboard\DashboardDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('monta o cockpit completo com periodo normalizado', function () {
    $user = User::factory()->create();

    $dados = app(DashboardDataService::class)->cockpit($user->id, $user, null, 99);

    expect($dados)->toHaveKeys(['kpis', 'triagem', 'tendencia', 'top_fornecedores', 'risco_distribuicao', 'meta'])
        ->and($dados['meta']['periodo'])->toBe(6) // 99 -> default 6
        ->and($dados['tendencia'])->toHaveKeys(['meses', 'saida_valor', 'saida_qtd', 'entrada_valor', 'entrada_qtd'])
        ->and($dados['triagem'])->toBeArray()
        ->and($dados['top_fornecedores'])->toBeArray()
        ->and($dados['risco_distribuicao'])->toBeArray()
        ->and($dados['meta']['dados_desatualizados'])->toBeFalse()
        ->and($dados['meta']['ancorado'])->toBeFalse();
});

it('tendencia alinha entrada e saida no mesmo eixo de meses', function () {
    $user = User::factory()->create();

    $t = app(DashboardDataService::class)->cockpit($user->id, $user, null, 6)['tendencia'];

    // 6 meses no eixo, e as 4 séries têm o mesmo comprimento do eixo
    expect($t['meses'])->toHaveCount(6)
        ->and($t['saida_valor'])->toHaveCount(6)
        ->and($t['entrada_valor'])->toHaveCount(6)
        ->and($t['saida_qtd'])->toHaveCount(6)
        ->and($t['entrada_qtd'])->toHaveCount(6);
});

it('ancora o cockpit na ultima competencia com dados quando a janela atual vem vazia', function () {
    $user = User::factory()->create();
    $clienteId = DB::table('clientes')->insertGetId([
        'user_id' => $user->id,
        'razao_social' => 'EMPRESA TESTE',
        'documento' => '00000000000100',
        'is_empresa_propria' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $participante = Participante::create([
        'user_id' => $user->id,
        'cliente_id' => $clienteId,
        'documento' => '11111111000111',
        'razao_social' => 'FORNECEDOR TESTE',
    ]);
    $importacao = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $clienteId,
        'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'teste.txt',
        'status' => 'concluido',
        'iniciado_em' => now()->subDays(10),
    ]);

    EfdNota::create([
        'user_id' => $user->id,
        'cliente_id' => $clienteId,
        'participante_id' => $participante->id,
        'importacao_id' => $importacao->id,
        'chave_acesso' => str_pad('A', 44, '0', STR_PAD_LEFT),
        'modelo' => '55',
        'numero' => 1,
        'serie' => '1',
        'data_emissao' => '2024-01-15',
        'tipo_operacao' => 'saida',
        'valor_total' => 1000,
        'valor_desconto' => 0,
        'origem_arquivo' => 'fiscal',
        'cancelada' => false,
    ]);
    EfdNota::create([
        'user_id' => $user->id,
        'cliente_id' => $clienteId,
        'participante_id' => $participante->id,
        'importacao_id' => $importacao->id,
        'chave_acesso' => str_pad('B', 44, '0', STR_PAD_LEFT),
        'modelo' => '55',
        'numero' => 2,
        'serie' => '1',
        'data_emissao' => '2024-02-10',
        'tipo_operacao' => 'saida',
        'valor_total' => 500,
        'valor_desconto' => 0,
        'origem_arquivo' => 'fiscal',
        'cancelada' => false,
    ]);

    // Fornecedor = quem te vende (ENTRADA). As saídas acima alimentam a tendência,
    // não o ranking de fornecedores.
    EfdNota::create([
        'user_id' => $user->id,
        'cliente_id' => $clienteId,
        'participante_id' => $participante->id,
        'importacao_id' => $importacao->id,
        'chave_acesso' => str_pad('C', 44, '0', STR_PAD_LEFT),
        'modelo' => '55',
        'numero' => 3,
        'serie' => '1',
        'data_emissao' => '2024-01-20',
        'tipo_operacao' => 'entrada',
        'valor_total' => 700,
        'valor_desconto' => 0,
        'origem_arquivo' => 'fiscal',
        'cancelada' => false,
    ]);

    $dados = app(DashboardDataService::class)->cockpit($user->id, $user, null, 6);

    expect($dados['meta']['referencia'])->toBe('2024-02-10')
        ->and($dados['meta']['janela_inicio'])->toBe('2023-09-01')
        ->and($dados['meta']['janela_fim'])->toBe('2024-02-29')
        ->and($dados['meta']['dados_desatualizados'])->toBeTrue()
        ->and($dados['meta']['ancorado'])->toBeTrue()
        ->and($dados['tendencia']['saida_valor'])->toContain(1000.0, 500.0)
        ->and(array_sum($dados['tendencia']['saida_valor']))->toBe(1500.0)
        ->and($dados['top_fornecedores'])->not->toBeEmpty()
        ->and($dados['top_fornecedores'][0]['total'])->toBe(700.0);
});
