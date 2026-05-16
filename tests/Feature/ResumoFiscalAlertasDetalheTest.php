<?php

use App\Models\Cliente;
use App\Models\EfdApuracaoIcms;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\EfdNotaItem;
use App\Models\Participante;
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

function rfFazerNotaIcmsSaida(User $user, Cliente $cliente, EfdImportacao $importacao, array $overrides = []): EfdNota
{
    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => $overrides['participante_doc'] ?? '12345678000199',
        'razao_social' => $overrides['participante_razao'] ?? 'FORNECEDOR LTDA',
    ]);

    $nota = EfdNota::create(array_merge([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $importacao->id,
        'participante_id' => $participante->id,
        'origem_arquivo' => 'fiscal',
        'tipo_operacao' => 'saida',
        'numero' => '1234',
        'serie' => '1',
        'modelo' => '55',
        'data_emissao' => '2026-04-15',
        'valor_total' => 10000.00,
    ], $overrides));

    EfdNotaItem::create([
        'efd_nota_id' => $nota->id,
        'user_id' => $user->id,
        'numero_item' => 1,
        'codigo_item' => 'ITEM001',
        'descricao' => 'Item de teste',
        'valor_total' => 10000.00,
        'valor_icms' => $overrides['valor_icms'] ?? 1800.00,
        'valor_pis' => 165.00,
        'valor_cofins' => 760.00,
    ]);

    return $nota;
}

it('alerta de retencoes nao compensadas traz tipo e detalhe.breakdown + detalhe.retencoes', function () {
    $user = User::factory()->create();
    $cliente = rfFazerClienteProprio($user);
    $importacao = rfFazerImportacaoFiscal($user, $cliente);

    // Necessário: EfdApuracaoContribuicao::scopePeriodo filtra pelo período via JOIN com EfdNota.data_emissao.
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

it('alerta de divergencia ICMS debito traz tipo + breakdown + notas contribuintes', function () {
    $user = User::factory()->create();
    $cliente = rfFazerClienteProprio($user);
    $importacao = rfFazerImportacaoFiscal($user, $cliente);

    // Apuração declara 1000 de débito
    EfdApuracaoIcms::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $importacao->id,
        'periodo_inicio' => '2026-04-01',
        'periodo_fim' => '2026-04-30',
        'icms_tot_debitos' => 1000.00,
        'icms_tot_creditos' => 0,
        'icms_obrigacoes' => [],
    ]);

    // Notas somam 1500 → divergência de 50%
    rfFazerNotaIcmsSaida($user, $cliente, $importacao, ['numero' => '1', 'valor_icms' => 800.00, 'participante_doc' => '11111111000191']);
    rfFazerNotaIcmsSaida($user, $cliente, $importacao, ['numero' => '2', 'valor_icms' => 700.00, 'participante_doc' => '22222222000191']);

    $data = rfService()->getAlertasFiscaisData($user->id, $cliente->id, '2026-04');

    $alerta = collect($data['alertas'])->firstWhere('tipo', 'icms_debito');

    expect($alerta)->not->toBeNull()
        ->and($alerta['detalhe']['breakdown']['declarado'])->toBe(1000.00)
        ->and($alerta['detalhe']['breakdown']['soma_notas'])->toBe(1500.00)
        ->and($alerta['detalhe']['notas_total'])->toBe(2)
        ->and(count($alerta['detalhe']['notas']))->toBe(2)
        ->and($alerta['detalhe']['notas'][0]['valor_contribuicao'])->toBe(800.00)
        ->and($alerta['detalhe']['notas'][0]['participante'])->toBe('FORNECEDOR LTDA');
});

it('cap em 25 notas; notas_total reflete o total real', function () {
    $user = User::factory()->create();
    $cliente = rfFazerClienteProprio($user);
    $importacao = rfFazerImportacaoFiscal($user, $cliente);

    EfdApuracaoIcms::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $importacao->id,
        'periodo_inicio' => '2026-04-01',
        'periodo_fim' => '2026-04-30',
        'icms_tot_debitos' => 100.00,
        'icms_tot_creditos' => 0,
        'icms_obrigacoes' => [],
    ]);

    for ($i = 1; $i <= 30; $i++) {
        rfFazerNotaIcmsSaida($user, $cliente, $importacao, [
            'numero' => (string) $i,
            'valor_icms' => 10.00 + $i,
            'participante_doc' => str_pad((string) $i, 14, '0', STR_PAD_LEFT),
        ]);
    }

    $data = rfService()->getAlertasFiscaisData($user->id, $cliente->id, '2026-04');
    $alerta = collect($data['alertas'])->firstWhere('tipo', 'icms_debito');

    expect($alerta['detalhe']['notas_total'])->toBe(30)
        ->and(count($alerta['detalhe']['notas']))->toBe(25)
        ->and($alerta['detalhe']['notas'][0]['valor_contribuicao'])->toBe(40.00);
});

it('alertas de divergencia PIS e COFINS trazem detalhe com notas', function () {
    $user = User::factory()->create();
    $cliente = rfFazerClienteProprio($user);
    $importacao = rfFazerImportacaoFiscal($user, $cliente);

    \App\Models\EfdApuracaoContribuicao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $importacao->id,
        'periodo_inicio' => '2026-04-01',
        'periodo_fim' => '2026-04-30',
        'pis_total_recolher' => 100.00,
        'cofins_total_recolher' => 400.00,
        'pis_retencao_nc' => 0,
        'pis_retencao_cum' => 0,
        'cofins_retencao_nc' => 0,
        'cofins_retencao_cum' => 0,
    ]);

    // Itens somam PIS 330 e COFINS 1520 (165*2 e 760*2 dos defaults do helper) → divergências > 5%
    rfFazerNotaIcmsSaida($user, $cliente, $importacao, ['numero' => '10', 'participante_doc' => '11111111000100']);
    rfFazerNotaIcmsSaida($user, $cliente, $importacao, ['numero' => '11', 'participante_doc' => '22222222000100']);

    $data = rfService()->getAlertasFiscaisData($user->id, $cliente->id, '2026-04');

    $pis = collect($data['alertas'])->firstWhere('tipo', 'pis');
    $cofins = collect($data['alertas'])->firstWhere('tipo', 'cofins');

    expect($pis)->not->toBeNull()
        ->and($pis['detalhe']['breakdown']['rotulo_declarado'])->toBe('Apuração M200')
        ->and($pis['detalhe']['notas_total'])->toBe(2)
        ->and($cofins)->not->toBeNull()
        ->and($cofins['detalhe']['breakdown']['rotulo_declarado'])->toBe('Apuração M600')
        ->and($cofins['detalhe']['notas_total'])->toBe(2);
});

it('sem dados, retorna alertas vazio sem regressao', function () {
    $user = User::factory()->create();
    $cliente = rfFazerClienteProprio($user);

    $data = rfService()->getAlertasFiscaisData($user->id, $cliente->id, '2026-04');

    expect($data['alertas'])->toBe([])
        ->and($data['resumo']['total'])->toBe(0);
});
