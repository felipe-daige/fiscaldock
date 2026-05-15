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
        ->and($obrigacao['detalhe']['obrigacao']['dias'])->toBeLessThan(0);
});
