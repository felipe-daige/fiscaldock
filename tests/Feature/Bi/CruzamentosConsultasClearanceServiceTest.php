<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Services\Bi\CruzamentosConsultasClearanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Cenário: 3 fornecedores consultados —
 *  A: CND Federal Positiva (irregular) com R$ 1.500 em compras
 *  B: sanção CEIS (cgu_cnc) com R$ 2.000 em compras
 *  C: CND Federal Negativa (regular, controle) com R$ 300 em compras
 */
function montarCenarioCruzamento(): array
{
    $user = User::factory()->create(['credits' => 100]);
    $cliente = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Escritorio ME',
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
    ]);

    $A = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'documento' => '11111111000111', 'razao_social' => 'Fornecedor A Irregular']);
    $B = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'documento' => '22222222000122', 'razao_social' => 'Fornecedor B Sancionado']);
    $C = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'documento' => '33333333000133', 'razao_social' => 'Fornecedor C Regular']);

    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 3, 'creditos_cobrados' => 30, 'tab_id' => 'tab-cruz', 'processado_em' => now(),
    ]);

    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $A->id, 'status' => 'sucesso',
        'resultado_dados' => ['cnd_federal' => ['status' => 'Positiva']], 'consultado_em' => now(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $B->id, 'status' => 'sucesso',
        'resultado_dados' => ['cgu_cnc' => ['possui_sancao' => true, 'bases_com_registro' => ['CEIS']]], 'consultado_em' => now(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $C->id, 'status' => 'sucesso',
        'resultado_dados' => ['cnd_federal' => ['status' => 'Negativa']], 'consultado_em' => now(),
    ]);

    foreach ([[$A, 40001, 1000.00], [$A, 40002, 500.00], [$B, 40003, 2000.00], [$C, 40004, 300.00]] as [$p, $num, $valor]) {
        EfdNota::create([
            'user_id' => $user->id, 'cliente_id' => $cliente->id, 'participante_id' => $p->id,
            'importacao_id' => $imp->id, 'chave_acesso' => '3524'.str_pad((string) $num, 40, '0', STR_PAD_LEFT),
            'modelo' => '55', 'numero' => $num, 'serie' => '0', 'data_emissao' => '2026-01-15',
            'tipo_operacao' => 'entrada', 'valor_total' => $valor, 'valor_desconto' => 0,
            'origem_arquivo' => 'fiscal', 'metadados' => [],
        ]);
    }

    return compact('user', 'A', 'B', 'C', 'lote');
}

it('cruza fornecedor irregular (CND positiva) × volume de compras', function () {
    $c = montarCenarioCruzamento();

    $linhas = (new CruzamentosConsultasClearanceService)->fornecedoresIrregularesComCompras($c['user']->id);

    expect($linhas)->toHaveCount(1);
    $a = $linhas->first();
    expect($a['participante_id'])->toBe($c['A']->id);
    expect($a['valor_comprado'])->toBe(1500.00);
    expect($a['qtd_notas'])->toBe(2);
    expect(implode(' ', $a['motivos']))->toContain('Federal');
});

it('cruza fornecedor sancionado (CEIS/CGU) × volume de compras', function () {
    $c = montarCenarioCruzamento();

    $linhas = (new CruzamentosConsultasClearanceService)->fornecedoresSancionadosComCompras($c['user']->id);

    expect($linhas)->toHaveCount(1);
    $b = $linhas->first();
    expect($b['participante_id'])->toBe($c['B']->id);
    expect($b['valor_comprado'])->toBe(2000.00);
    expect($b['bases'])->toContain('CEIS');
});

it('não inclui fornecedor regular nos cruzamentos de risco', function () {
    $c = montarCenarioCruzamento();
    $service = new CruzamentosConsultasClearanceService;

    $irregulares = $service->fornecedoresIrregularesComCompras($c['user']->id);
    $sancionados = $service->fornecedoresSancionadosComCompras($c['user']->id);

    expect($irregulares->pluck('participante_id'))->not->toContain($c['C']->id);
    expect($sancionados->pluck('participante_id'))->not->toContain($c['C']->id);
});

it('resume os KPIs dos cruzamentos', function () {
    $c = montarCenarioCruzamento();

    $resumo = (new CruzamentosConsultasClearanceService)->resumo($c['user']->id);

    expect($resumo['irregulares_qtd'])->toBe(1);
    expect($resumo['irregulares_valor'])->toBe(1500.00);
    expect($resumo['sancionados_qtd'])->toBe(1);
    expect($resumo['sancionados_valor'])->toBe(2000.00);
});

it('isola por usuário (não vaza fornecedor de outro)', function () {
    $c = montarCenarioCruzamento();
    $outro = User::factory()->create(['credits' => 100]);

    $linhas = (new CruzamentosConsultasClearanceService)->fornecedoresIrregularesComCompras($outro->id);

    expect($linhas)->toHaveCount(0);
});
