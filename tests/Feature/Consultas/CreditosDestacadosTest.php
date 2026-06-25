<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use App\Services\Consultas\Fiscal\TopMovimentacaoQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/** Cria 1 fornecedor com entradas para um comprador de dado regime (crt). */
function destSetup(int $crt): array
{
    $user = User::factory()->create();
    $cliente = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'EMPRESA A', 'documento' => '00000000000100',
        'is_empresa_propria' => true, 'crt' => $crt, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $part = DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'cliente_id' => $cliente, 'razao_social' => 'FORN X',
        'documento' => '11111111000111', 'origem_tipo' => 'MANUAL', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $imp = EfdImportacao::create(['user_id' => $user->id, 'cliente_id' => $cliente, 'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'f.txt', 'status' => 'concluido', 'iniciado_em' => now()]);

    $nota = EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente, 'participante_id' => $part,
        'importacao_id' => $imp->id, 'numero' => 1, 'serie' => '1', 'modelo' => '55',
        'origem_arquivo' => 'fiscal', 'tipo_operacao' => 'entrada',
        'valor_total' => 1000, 'valor_desconto' => 0, 'cancelada' => false, 'data_emissao' => '2024-05-01',
    ]);

    // ICMS 180 + IPI 20 (consolidado); PIS 16,5 + COFINS 76 (itens) => destacado 292,5
    DB::table('efd_notas_consolidados')->insert([
        'efd_nota_id' => $nota->id, 'user_id' => $user->id, 'cfop' => 1102, 'cst_icms' => '00',
        'aliquota_icms' => 18, 'valor_operacao' => 1000, 'valor_bc_icms' => 1000, 'valor_icms' => 180,
        'valor_bc_icms_st' => 0, 'valor_icms_st' => 0, 'valor_reducao_bc' => 0, 'valor_ipi' => 20,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('efd_notas_itens')->insert([
        'efd_nota_id' => $nota->id, 'user_id' => $user->id, 'numero_item' => 1,
        'codigo_item' => 'X', 'descricao' => 'd', 'quantidade' => 1, 'unidade_medida' => 'UN',
        'valor_unitario' => 1000, 'valor_total' => 1000, 'cfop' => 1102, 'cst_icms' => '00',
        'aliquota_icms' => 18, 'valor_pis' => 16.5, 'valor_cofins' => 76,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    return compact('user', 'cliente', 'part');
}

it('soma ICMS+IPI+PIS+COFINS destacados das entradas quando o comprador é Regime Normal (crt=3)', function () {
    $d = destSetup(3);
    $r = app(TopMovimentacaoQuery::class)->creditosDestacados($d['user']->id, 'participante_id', [$d['part']]);

    expect($r)->toHaveKey($d['part']);
    expect($r[$d['part']])->toEqual(292.5); // 180 + 20 + 16,5 + 76
});

it('gate: comprador Simples (crt=1) não credita — fornecedor fica fora do retorno', function () {
    $d = destSetup(1);
    $r = app(TopMovimentacaoQuery::class)->creditosDestacados($d['user']->id, 'participante_id', [$d['part']]);

    expect($r)->toBe([]);
});

it('ids vazios retornam array vazio', function () {
    expect(app(TopMovimentacaoQuery::class)->creditosDestacados(1, 'participante_id', []))->toBe([]);
});

it('coluna inválida lança exceção', function () {
    app(TopMovimentacaoQuery::class)->creditosDestacados(1, 'razao_social', [1]);
})->throws(InvalidArgumentException::class);
