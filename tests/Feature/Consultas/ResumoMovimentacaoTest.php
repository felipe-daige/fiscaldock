<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\Participante;
use App\Models\User;
use App\Services\Consultas\ParticipanteFiscalResumoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function inserirNotaFiscalResumo(User $user, Participante $p, string $tipoOperacao, float $valor): void
{
    $cliente = Cliente::firstOrCreate(
        ['user_id' => $user->id, 'documento' => '00000000000191'],
        ['razao_social' => 'Empresa Teste']
    );
    $imp = EfdImportacao::firstOrCreate(
        ['user_id' => $user->id, 'tipo_efd' => 'EFD ICMS/IPI'],
        []
    );

    DB::table('efd_notas')->insert([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $imp->id,
        'participante_id' => $p->id,
        'modelo' => '55',
        'numero' => random_int(1, 9999999),
        'tipo_operacao' => $tipoOperacao,
        'origem_arquivo' => 'fiscal',
        'valor_total' => $valor,
        'cancelada' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('agrega papel, valor e qtd por participante numa chamada', function () {
    $user = User::factory()->create();
    $p = Participante::create([
        'user_id' => $user->id,
        'documento' => '11444777000161',
        'razao_social' => 'Contraparte Ambos',
    ]);

    inserirNotaFiscalResumo($user, $p, 'entrada', 100.0);
    inserirNotaFiscalResumo($user, $p, 'saida', 50.0);

    $resumo = app(ParticipanteFiscalResumoService::class)->resumoMovimentacao($user->id);

    expect($resumo[$p->id]['papel'])->toBe('ambos');
    expect($resumo[$p->id]['valor'])->toBe(150.0);
    expect($resumo[$p->id]['qtd'])->toBe(2);
});

it('classifica papel fornecedor quando so ha entradas', function () {
    $user = User::factory()->create();
    $p = Participante::create([
        'user_id' => $user->id,
        'documento' => '11444777000242',
        'razao_social' => 'So Fornecedor',
    ]);

    inserirNotaFiscalResumo($user, $p, 'entrada', 200.0);

    $resumo = app(ParticipanteFiscalResumoService::class)->resumoMovimentacao($user->id);

    expect($resumo[$p->id]['papel'])->toBe('fornecedor');
    expect($resumo[$p->id]['qtd'])->toBe(1);
});
