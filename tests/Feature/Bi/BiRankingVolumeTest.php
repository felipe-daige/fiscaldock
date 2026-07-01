<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Services\BiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function semearRankingVolume(): array
{
    $user = User::factory()->create();
    $cliA = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'documento' => '00000000000191', 'razao_social' => 'Cliente A',
        'is_empresa_propria' => true, 'ativo' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $cliB = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'documento' => '00000000000272', 'razao_social' => 'Cliente B',
        'is_empresa_propria' => false, 'ativo' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $mkPart = fn (int $cli, string $doc, string $rz) => Participante::create([
        'user_id' => $user->id, 'cliente_id' => $cli, 'documento' => $doc, 'razao_social' => $rz,
        'origem_tipo' => 'MANUAL',
    ])->id;
    $p1 = $mkPart($cliA, '11111111000111', 'P1'); // volume 1000
    $p2 = $mkPart($cliA, '22222222000122', 'P2'); // volume 5000
    $p3 = $mkPart($cliB, '33333333000133', 'P3'); // volume 3000

    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliA, 'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'x.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);
    $seq = 0;
    $mkNota = function (int $cli, int $part, float $valor, string $tipo = 'saida') use ($user, $imp, &$seq) {
        $seq++;
        EfdNota::create([
            'user_id' => $user->id, 'cliente_id' => $cli, 'participante_id' => $part, 'importacao_id' => $imp->id,
            'numero' => $seq, 'serie' => '1', 'modelo' => '55', 'chave_acesso' => str_pad((string) $seq, 44, '0'),
            'valor_total' => $valor, 'valor_desconto' => 0, 'cancelada' => false, 'origem_arquivo' => 'fiscal',
            'tipo_operacao' => $tipo, 'data_emissao' => '2026-03-10',
        ]);
    };
    $mkNota($cliA, $p1, 1000);
    $mkNota($cliA, $p2, 5000);
    $mkNota($cliB, $p3, 3000);

    return compact('user', 'cliA', 'cliB', 'p1', 'p2', 'p3');
}

it('participantesPorVolume ordena por volume desc', function () {
    ['user' => $u, 'p1' => $p1, 'p2' => $p2, 'p3' => $p3] = semearRankingVolume();
    $ordem = app(BiService::class)->participantesPorVolume($u->id)->pluck('id')->all();
    expect($ordem)->toBe([$p2, $p3, $p1]); // 5000, 3000, 1000
});

it('participantesPorVolume respeita o limite', function () {
    ['user' => $u, 'p2' => $p2, 'p3' => $p3] = semearRankingVolume();
    $ordem = app(BiService::class)->participantesPorVolume($u->id, null, 2)->pluck('id')->all();
    expect($ordem)->toBe([$p2, $p3]);
});

it('participantesPorVolume filtra por cliente', function () {
    ['user' => $u, 'cliA' => $cliA, 'p1' => $p1, 'p2' => $p2] = semearRankingVolume();
    $ordem = app(BiService::class)->participantesPorVolume($u->id, $cliA)->pluck('id')->all();
    expect($ordem)->toBe([$p2, $p1]); // só cliente A, por volume
});

it('clientesPorVolume ordena clientes ativos por volume desc', function () {
    ['user' => $u, 'cliA' => $cliA, 'cliB' => $cliB] = semearRankingVolume();
    $ids = app(BiService::class)->clientesPorVolume($u->id)->pluck('id')->all();
    expect($ids)->toBe([$cliA, $cliB]); // A=6000 (p1+p2) > B=3000
});

it('clientesPorVolume respeita o limite', function () {
    ['user' => $u, 'cliA' => $cliA] = semearRankingVolume();
    $ids = app(BiService::class)->clientesPorVolume($u->id, 1)->pluck('id')->all();
    expect($ids)->toBe([$cliA]); // só o de maior volume
});
