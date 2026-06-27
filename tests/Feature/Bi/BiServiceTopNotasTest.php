<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use App\Services\BiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('retorna maiores notas por valor, respeitando cliente e ignorando canceladas', function () {
    $user = User::factory()->create();
    $cli = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'documento' => '00000000000191', 'razao_social' => 'Empresa',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $outro = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'documento' => '00000000000272', 'razao_social' => 'Outro',
        'is_empresa_propria' => false, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $part = DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'cliente_id' => $cli, 'razao_social' => 'ACME LTDA',
        'documento' => '11111111000111', 'origem_tipo' => 'MANUAL', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cli, 'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'x.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);

    $mk = fn (array $a) => EfdNota::create(array_merge([
        'user_id' => $user->id, 'cliente_id' => $cli, 'participante_id' => $part, 'importacao_id' => $imp->id,
        'numero' => random_int(1, 99999), 'serie' => '1', 'modelo' => '55',
        'valor_desconto' => 0, 'cancelada' => false, 'origem_arquivo' => 'fiscal',
        'tipo_operacao' => 'saida', 'data_emissao' => '2026-03-10',
    ], $a));

    $mk(['chave_acesso' => str_pad('1', 44, '0'), 'valor_total' => 100.00]);
    $mk(['chave_acesso' => str_pad('2', 44, '0'), 'valor_total' => 900.00]);
    $mk(['chave_acesso' => str_pad('3', 44, '0'), 'valor_total' => 5000.00, 'cancelada' => true]); // ignorada
    $mk(['chave_acesso' => str_pad('4', 44, '0'), 'valor_total' => 7000.00, 'cliente_id' => $outro]); // outro cliente
    $mk(['chave_acesso' => str_pad('5', 44, '0'), 'valor_total' => 500.00, 'tipo_operacao' => 'entrada']);

    $top = app(BiService::class)->getTopNotas($user->id, null, null, $cli, 15);

    expect($top)->toHaveCount(3)
        ->and($top[0]['valor'])->toBe(900.00)
        ->and($top[0]['razao_social'])->toBe('ACME LTDA')
        ->and($top[0]['tipo'])->toBe('S')
        ->and($top[0]['chave'])->toBe(str_pad('2', 44, '0'))
        ->and($top[0]['cnpj_cpf'])->toBe('11111111000111')
        ->and($top[0]['data_emissao'])->toBe('10/03/2026')
        ->and($top[1]['valor'])->toBe(500.00)
        ->and($top[1]['tipo'])->toBe('E')
        ->and($top[2]['valor'])->toBe(100.00);
});
