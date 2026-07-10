<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\NfeConsulta;
use App\Models\User;
use App\Services\Clearance\Export\ClearanceDashboardReportBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Exportações do Painel de Clearance DF-e (PDF/XLSX/CSV-ZIP).
 *
 * Contrato: os 3 formatos leem o MESMO payload (`ClearanceDashboardReportBuilder`), que lê
 * `ValidacaoContabilService::dadosPainelClearance` — a mesma fonte SEFAZ da tela. Valor por
 * status, exposição bloqueante e cobertura por cliente não existem na tela nem nos outros
 * exports; se divergirem, quebra aqui.
 */
function seedAcervoClearance(User $user): Cliente
{
    $cliente = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Cliente Alpha ME',
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
    ]);

    $chaveAutorizada = str_pad('11', 44, '0', STR_PAD_LEFT);
    $chaveCancelada = str_pad('22', 44, '0', STR_PAD_LEFT);
    $chavePendente = str_pad('33', 44, '0', STR_PAD_LEFT);

    $mk = fn (string $chave, float $valor) => EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id,
        'chave_acesso' => $chave, 'modelo' => '55', 'numero' => (int) substr($chave, -4), 'serie' => '0',
        'data_emissao' => '2026-01-15', 'tipo_operacao' => 'entrada', 'valor_total' => $valor,
        'valor_desconto' => 0, 'origem_arquivo' => 'fiscal', 'metadados' => [],
    ]);

    $mk($chaveAutorizada, 1000.00);
    $mk($chaveCancelada, 5000.00);
    $mk($chavePendente, 300.00);   // sem snapshot → pendente/backlog

    NfeConsulta::create([
        'user_id' => $user->id, 'chave_acesso' => $chaveAutorizada, 'tipo_documento' => 'NFE',
        'modelo' => '55', 'status' => 'AUTORIZADA', 'consultado_em' => now(),
    ]);
    NfeConsulta::create([
        'user_id' => $user->id, 'chave_acesso' => $chaveCancelada, 'tipo_documento' => 'NFE',
        'modelo' => '55', 'status' => 'CANCELADA', 'consultado_em' => now(),
    ]);

    return $cliente;
}

it('agrega valor, exposição bloqueante e cobertura no payload do relatório', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    seedAcervoClearance($user);

    $relatorio = app(ClearanceDashboardReportBuilder::class)->montar($user->id);
    $r = $relatorio['resumo'];

    expect($r['total_notas'])->toBe(3)
        ->and($r['verificadas'])->toBe(2)
        ->and($r['pendentes'])->toBe(1)
        ->and($r['notas_bloqueantes'])->toBe(1)
        ->and($r['valor_bloqueante'])->toBe(5000.0)
        ->and($r['valor_pendente'])->toBe(300.0);

    // Backlog = pendentes × custo básico (3 créditos).
    expect($relatorio['backlog']['notas'])->toBe(1)
        ->and($relatorio['backlog']['custo_creditos'])->toBe(3);

    // A nota cancelada de R$ 5.000 aparece na exposição.
    $exposicao = $relatorio['secoes']['exposicao-bloqueante']['linhas'];
    expect($exposicao)->toHaveCount(1)
        ->and($exposicao[0][6])->toBe(5000.0);
});

it('gera o PDF do painel para o dono (200 application/pdf)', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    seedAcervoClearance($user);

    $response = actingAs($user)->get('/app/clearance/dashboard/exportar-pdf');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
    expect(substr((string) $response->getContent(), 0, 4))->toBe('%PDF');
});

it('gera o XLSX do painel para o dono (200 spreadsheet)', function () {
    if (! \App\Support\Reports\XlsxReport::disponivel()) {
        $this->markTestSkipped('openspout indisponível neste ambiente');
    }
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    seedAcervoClearance($user);

    $response = actingAs($user)->get('/app/clearance/dashboard/exportar-xlsx');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('spreadsheetml.sheet');
});

it('gera o CSV-ZIP do painel para o dono (200 zip)', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    seedAcervoClearance($user);

    $response = actingAs($user)->get('/app/clearance/dashboard/exportar-csv-zip');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('zip');
});

it('Free puro: PDF do dashboard liberado com marca d\'água (sem trial/plano)', function () {
    $user = User::factory()->create(); // sem trialAtivo → Free
    seedAcervoClearance($user);

    // PDF universal (marca d'água aplicada no layout); CSV/XLSX é que seguem gated.
    $resp = actingAs($user)->get('/app/clearance/dashboard/exportar-pdf');
    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('pdf');
});
