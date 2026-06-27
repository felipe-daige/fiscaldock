<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use App\Services\Consultas\Fiscal\TopMovimentacaoQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('agrega top itens por cod_item somando todos os clientes do usuário', function () {
    $user = User::factory()->create();
    $mkCli = fn (string $doc) => DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'documento' => $doc, 'razao_social' => "Cli {$doc}",
        'is_empresa_propria' => false, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $cliA = $mkCli('00000000000191');
    $cliB = $mkCli('00000000000272');
    $imp = fn (int $cli) => EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cli, 'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'x.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ])->id;
    $impA = $imp($cliA);
    $impB = $imp($cliB);

    $nota = fn (int $cli, int $impId, string $chave) => EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cli, 'importacao_id' => $impId, 'numero' => random_int(1, 99999),
        'serie' => '1', 'modelo' => '55', 'valor_desconto' => 0, 'cancelada' => false,
        'origem_arquivo' => 'fiscal', 'tipo_operacao' => 'saida', 'data_emissao' => '2026-03-01',
        'chave_acesso' => str_pad($chave, 44, '0'), 'valor_total' => 0,
    ]);
    $item = fn (EfdNota $n, string $cod, float $v) => DB::table('efd_notas_itens')->insert([
        'user_id' => $user->id, 'efd_nota_id' => $n->id, 'numero_item' => 1,
        'codigo_item' => $cod, 'quantidade' => 1, 'valor_total' => $v, 'cfop' => 5102,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $item($nota($cliA, $impA, 'A1'), 'X1', 300.00);
    $item($nota($cliB, $impB, 'B1'), 'X1', 200.00); // mesmo cod, outro cliente → soma 500
    $item($nota($cliA, $impA, 'A2'), 'X2', 100.00);

    // Nota cancelada — deve ser EXCLUÍDA da agregação (X1 deve manter valor 500, qtd 2)
    $notaCancelada = EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliA, 'importacao_id' => $impA, 'numero' => random_int(100000, 199999),
        'serie' => '1', 'modelo' => '55', 'valor_desconto' => 0, 'cancelada' => true,
        'origem_arquivo' => 'fiscal', 'tipo_operacao' => 'saida', 'data_emissao' => '2026-03-01',
        'chave_acesso' => str_pad('A3', 44, '0'), 'valor_total' => 0,
    ]);
    $item($notaCancelada, 'X1', 999.00); // não deve entrar no total

    $top = app(TopMovimentacaoQuery::class)->produtosPorUsuario($user->id, 15);

    expect($top)->toHaveCount(2)
        ->and($top[0]['cod_item'])->toBe('X1')
        ->and($top[0]['valor'])->toBe(500.00)  // cancelada não soma
        ->and($top[0]['qtd'])->toBe(2)          // cancelada não conta
        ->and($top[1]['cod_item'])->toBe('X2');
});
