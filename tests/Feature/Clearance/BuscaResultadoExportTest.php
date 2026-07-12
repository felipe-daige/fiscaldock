<?php

use App\Models\ConsultaLote;
use App\Models\NfeConsulta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Export do resultado da busca avulsa (1 documento) em PDF/XLSX/CSV.
 * Mesma fonte da tela (formatarResultadoConsultaDfe); gates de plano nas rotas:
 * PDF `:export` (universal — Free sai com marca d'água), XLSX `:export,excel`,
 * CSV `:export,csv` (trial libera tudo).
 */
function breSeed(User $user): ConsultaLote
{
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 14, 'tab_id' => 'tab-bre', 'processado_em' => now(),
    ]);

    NfeConsulta::create([
        'user_id' => $user->id, 'consulta_lote_id' => $lote->id,
        'chave_acesso' => '50240197551165000193550010000248001000214739',
        'tipo_documento' => 'NFE', 'modelo' => '55', 'numero' => '24800', 'serie' => 1,
        'status' => 'CANCELADA', 'valor_total' => 51.11,
        'natureza_operacao' => 'VENDA DE MERCADORIA', 'tipo_operacao' => 'SAÍDA',
        'emit_nome' => 'HIDRATOP COMERCIO', 'emit_cnpj' => '97551165000193',
        'dest_nome' => 'CLIENTE FINAL LTDA', 'dest_cnpj' => '13305697000150',
        'consulta_sem_certificado' => true, 'data_emissao' => '2025-08-11',
        'eventos' => [
            ['evento' => 'Autorização de Uso', 'protocolo' => '150240008274469', 'data_autorizacao' => '11/08/2025 às 08:55:28-04:00'],
            ['evento' => 'Cancelamento pelo emitente', 'protocolo' => '150240008999999', 'data_autorizacao' => '13/08/2025 às 08:36:57-04:00'],
        ],
        'produtos' => [['descricao' => 'BOMBA HIDRAULICA', 'ncm' => '84137080', 'cfop' => '5102', 'quantidade' => '1', 'valor' => '51,11']],
        'consultado_em' => now(),
    ]);

    return $lote;
}

it('gera o PDF do resultado da busca avulsa (200 pdf)', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    $lote = breSeed($user);

    $resp = actingAs($user)->get("/app/clearance/buscar/resultado/{$lote->id}/pdf");

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('application/pdf');
});

it('gera o XLSX do resultado da busca avulsa (200 spreadsheet)', function () {
    if (! \App\Support\Reports\XlsxReport::disponivel()) {
        $this->markTestSkipped('openspout indisponível neste ambiente');
    }
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    $lote = breSeed($user);

    $resp = actingAs($user)->get("/app/clearance/buscar/resultado/{$lote->id}/xlsx");

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('spreadsheetml.sheet');
});

it('gera o CSV com documento, eventos em ordem cronológica e produtos', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    $lote = breSeed($user);

    $resp = actingAs($user)->get("/app/clearance/buscar/resultado/{$lote->id}/csv");

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('text/csv');

    $csv = $resp->streamedContent();
    expect($csv)->toContain('50240197551165000193550010000248001000214739')
        ->toContain('CANCELADA')
        ->toContain('Consulta pública (sem certificado)')
        ->toContain('Eventos na SEFAZ')
        ->toContain('150240008274469')
        ->toContain('BOMBA HIDRAULICA');

    // Ordem cronológica na seção de eventos (autorização antes do cancelamento)
    expect(strpos($csv, '150240008274469'))->toBeLessThan(strpos($csv, '150240008999999'));
});

it('Free puro: PDF liberado (marca d\'água), CSV e XLSX bloqueados (403)', function () {
    $user = User::factory()->create(); // sem trial/plano → Free
    $lote = breSeed($user);

    actingAs($user)->get("/app/clearance/buscar/resultado/{$lote->id}/pdf")->assertOk();
    actingAs($user)->get("/app/clearance/buscar/resultado/{$lote->id}/csv")->assertForbidden();
    actingAs($user)->get("/app/clearance/buscar/resultado/{$lote->id}/xlsx")->assertForbidden();
});

it('não exporta lote de outro usuário (404) nem lote sem snapshot persistido (404)', function () {
    $dono = User::factory()->trialAtivo()->create(['credits' => 100]);
    $outro = User::factory()->trialAtivo()->create(['credits' => 100]);
    $lote = breSeed($dono);

    actingAs($outro)->get("/app/clearance/buscar/resultado/{$lote->id}/pdf")->assertNotFound();

    $vazio = ConsultaLote::create([
        'user_id' => $dono->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 14, 'tab_id' => 'tab-bre2', 'processado_em' => now(),
    ]);

    actingAs($dono)->get("/app/clearance/buscar/resultado/{$vazio->id}/csv")->assertNotFound();
});

it('tela de resultado exibe o menu de exportação quando o resultado está pronto', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    $lote = breSeed($user);

    actingAs($user)->get('/app/clearance/buscar/resultado/'.$lote->id.'?tipo_documento=nfe')
        ->assertOk()
        ->assertSee('Exportar documento')
        ->assertSee("/app/clearance/buscar/resultado/{$lote->id}/pdf", false)
        ->assertSee("/app/clearance/buscar/resultado/{$lote->id}/xlsx", false)
        ->assertSee("/app/clearance/buscar/resultado/{$lote->id}/csv", false);
});
