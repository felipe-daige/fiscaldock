<?php

use App\Models\Cliente;
use App\Models\EfdApuracaoIcms;
use App\Models\EfdImportacao;
use App\Models\User;
use App\Services\ResumoFiscalService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function rfService(): ResumoFiscalService
{
    return app(ResumoFiscalService::class);
}

function rfFazerClienteProprio(User $user): Cliente
{
    return Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '00000000000191',
        'razao_social' => 'Empresa Propria LTDA',
        'is_empresa_propria' => true,
        'ativo' => true,
    ]);
}

function rfFazerImportacaoFiscal(User $user, Cliente $cliente): EfdImportacao
{
    return EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'efd-fiscal.txt',
        'status' => 'concluido',
    ]);
}

it('alerta de obrigacao ICMS vencida traz tipo e detalhe.obrigacao', function () {
    $user = User::factory()->create();
    $cliente = rfFazerClienteProprio($user);
    $importacao = rfFazerImportacaoFiscal($user, $cliente);

    EfdApuracaoIcms::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $importacao->id,
        'periodo_inicio' => '2026-04-01',
        'periodo_fim' => '2026-04-30',
        'icms_tot_debitos' => 0,
        'icms_tot_creditos' => 0,
        'icms_obrigacoes' => ['items' => [[
            'cod_or' => '000',
            'desc_or' => 'ICMS Próprio',
            'vl_or' => 5000.00,
            'dt_vcto' => now()->subDays(15)->format('Y-m-d'),
        ]]],
    ]);

    $data = rfService()->getAlertasFiscaisData($user->id, $cliente->id, '2026-04');

    $obrigacao = collect($data['alertas'])->firstWhere('tipo', 'obrigacao_vencida');

    expect($obrigacao)->not->toBeNull()
        ->and($obrigacao['detalhe']['obrigacao']['codigo'])->toBe('000')
        ->and($obrigacao['detalhe']['obrigacao']['descricao'])->toBe('ICMS Próprio')
        ->and($obrigacao['detalhe']['obrigacao']['valor_obrigacao'])->toBe(5000.00)
        ->and($obrigacao['detalhe']['obrigacao']['dias'])->toBeLessThan(0)
        ->and(abs($obrigacao['detalhe']['obrigacao']['dias']))->toBeGreaterThanOrEqual(14);
});

it('alerta de retencoes nao compensadas traz tipo e detalhe.breakdown + detalhe.retencoes', function () {
    $user = User::factory()->create();
    $cliente = rfFazerClienteProprio($user);
    $importacao = rfFazerImportacaoFiscal($user, $cliente);

    // EfdApuracaoContribuicao.scopePeriodo filtra por importacao_id de EfdNota com data_emissao no período
    \App\Models\EfdNota::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $importacao->id,
        'modelo' => '55',
        'numero' => 1,
        'data_emissao' => '2026-04-15',
        'tipo_operacao' => 'saida',
        'valor_total' => 0,
        'origem_arquivo' => 'contribuicoes',
    ]);

    \App\Models\EfdApuracaoContribuicao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $importacao->id,
        'pis_total_recolher' => 0,
        'cofins_total_recolher' => 0,
        'pis_retencao_nc' => 0,
        'pis_retencao_cum' => 0,
        'cofins_retencao_nc' => 0,
        'cofins_retencao_cum' => 0,
    ]);

    \App\Models\EfdRetencaoFonte::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $importacao->id,
        'natureza' => '01',
        'data_retencao' => '2026-04-10',
        'base_calculo' => 1000.00,
        'valor_total' => 300.00,
        'cod_receita' => '5979',
        'natureza_receita' => '01',
        'cnpj' => '00000000000191',
        'valor_pis' => 100.00,
        'valor_cofins' => 200.00,
    ]);

    $data = rfService()->getAlertasFiscaisData($user->id, $cliente->id, '2026-04');

    $alerta = collect($data['alertas'])->firstWhere('tipo', 'retencoes');

    expect($alerta)->not->toBeNull()
        ->and($alerta['detalhe']['breakdown']['total_retido'])->toBe(300.00)
        ->and($alerta['detalhe']['breakdown']['deduzido_apuracao'])->toBe(0.0)
        ->and($alerta['detalhe']['breakdown']['nao_compensado'])->toBe(300.00)
        ->and($alerta['detalhe']['retencoes'])->toBeArray()
        ->and(count($alerta['detalhe']['retencoes']))->toBeGreaterThan(0);
});
