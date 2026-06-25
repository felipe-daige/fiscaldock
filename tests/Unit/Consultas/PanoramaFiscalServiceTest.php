<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use App\Models\User;
use App\Services\Consultas\PanoramaFiscalService;
use Illuminate\Support\Facades\DB;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function notaFiscalPanorama(array $attrs): void
{
    $userId   = $attrs['user_id'];
    $clienteId = $attrs['cliente_id'];

    if (! isset($attrs['importacao_id'])) {
        $imp = EfdImportacao::create([
            'user_id'     => $userId,
            'cliente_id'  => $clienteId,
            'tipo_efd'    => 'EFD ICMS/IPI',
            'filename'    => 'test.txt',
            'status'      => 'concluido',
            'iniciado_em' => now(),
        ]);
        $attrs['importacao_id'] = $imp->id;
    }

    DB::table('efd_notas')->insert(array_merge([
        'origem_arquivo' => 'fiscal',
        'cancelada'      => false,
        'modelo'         => '55',
        'numero'         => random_int(1, 9999999),
        'created_at'     => now(),
        'updated_at'     => now(),
    ], $attrs));
}

it('monta panorama de participante com pct somando ~100 e serie', function () {
    $user    = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'documento' => '99888777000166', 'razao_social' => 'Minha Empresa']);
    $part    = Participante::create(['user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'ACME LTDA']);
    $base    = ['user_id' => $user->id, 'cliente_id' => $cliente->id, 'participante_id' => $part->id];

    notaFiscalPanorama($base + ['tipo_operacao' => 'entrada', 'valor_total' => 300, 'data_emissao' => '2026-01-10']);
    notaFiscalPanorama($base + ['tipo_operacao' => 'entrada', 'valor_total' => 100, 'data_emissao' => '2026-02-10']);

    $p = app(PanoramaFiscalService::class)->para($user->id, 'participante', $part->id);

    expect($p)->not->toBeNull()
        ->and($p['escopo'])->toBe('participante')
        ->and($p['kpis']['total_comprado'])->toBe(400.0)
        ->and($p['serie_mensal'])->toHaveCount(2)
        ->and(round(collect($p['concentracao'])->sum('pct')))->toBe(100.0)
        ->and($p['saude']['score'])->toBeNull();
});

it('inclui score quando existe ParticipanteScore', function () {
    $user    = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'documento' => '99888777000166', 'razao_social' => 'MINHA EMPRESA']);
    $part    = Participante::create(['user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'ACME LTDA']);
    notaFiscalPanorama([
        'user_id'       => $user->id,
        'cliente_id'    => $cliente->id,
        'participante_id' => $part->id,
        'tipo_operacao' => 'entrada',
        'valor_total'   => 100,
        'data_emissao'  => '2026-01-10',
    ]);
    ParticipanteScore::create([
        'user_id'          => $user->id,
        'participante_id'  => $part->id,
        'score_total'      => 82,
        'classificacao'    => 'Baixo risco',
        'ultima_consulta_em' => now(),
    ]);

    $p = app(PanoramaFiscalService::class)->para($user->id, 'participante', $part->id);

    expect($p['saude']['score'])->toBe(82)
        ->and($p['saude']['classificacao'])->toBe('Baixo risco');
});

it('retorna null sem movimentacao', function () {
    $user = User::factory()->create();
    $part = Participante::create(['user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'ACME LTDA']);
    expect(app(PanoramaFiscalService::class)->para($user->id, 'participante', $part->id))->toBeNull();
});

it('rejeita escopo invalido', function () {
    $user = User::factory()->create();
    expect(fn () => app(PanoramaFiscalService::class)->para($user->id, 'foo', 1))
        ->toThrow(InvalidArgumentException::class);
});
