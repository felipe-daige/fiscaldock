<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\NfeConsulta;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('veredito FRIA: nota declarada na EFD + NAO_ENCONTRADA na SEFAZ → crítica', function () {
    $user = User::factory()->create(['credits' => 100]);
    $cliente = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Empresa Propria',
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
    ]);
    $part = Participante::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'documento' => '13305697000150', 'razao_social' => 'Fornecedor',
    ]);

    $chave = '35240413305697000150550000000404041953940992';

    // Declarado na EFD com valor > 0.
    EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'participante_id' => $part->id,
        'importacao_id' => $imp->id, 'chave_acesso' => $chave, 'modelo' => '55', 'numero' => 40404,
        'serie' => '0', 'data_emissao' => '2026-01-15', 'tipo_operacao' => 'entrada',
        'valor_total' => 1000.00, 'valor_desconto' => 0, 'origem_arquivo' => 'fiscal', 'metadados' => [],
    ]);

    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 3, 'tab_id' => 'tab-fria', 'processado_em' => now(),
    ]);

    // SEFAZ não encontrou o documento (snapshot persistido pelo clearance).
    NfeConsulta::create([
        'user_id' => $user->id, 'consulta_lote_id' => $lote->id, 'chave_acesso' => $chave,
        'tipo_documento' => 'NFE', 'modelo' => '55', 'status' => 'NAO_ENCONTRADA', 'consultado_em' => now(),
    ]);

    actingAs($user)
        ->getJson("/app/clearance/notas/resultado/{$lote->id}")
        ->assertOk()
        ->assertJsonPath('resultado_pronto', true)
        ->assertJsonPath('total_resultados', 1)
        ->assertJsonPath('veredito.severidade', 'critica')
        ->assertJsonPath('kpis.existencia.nao_encontradas', 1);
});

it('veredito OK: AUTORIZADA com valor batendo o declarado → sem divergência', function () {
    $user = User::factory()->create(['credits' => 100]);
    $cliente = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Empresa Propria',
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
    ]);
    $chave = '35240413305697000150550000000404041953940992';

    EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id,
        'chave_acesso' => $chave, 'modelo' => '55', 'numero' => 40404, 'serie' => '0',
        'data_emissao' => '2026-01-15', 'tipo_operacao' => 'entrada', 'valor_total' => 1000.00,
        'valor_desconto' => 0, 'origem_arquivo' => 'fiscal', 'metadados' => [],
    ]);

    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 3, 'tab_id' => 'tab-ok', 'processado_em' => now(),
    ]);

    NfeConsulta::create([
        'user_id' => $user->id, 'consulta_lote_id' => $lote->id, 'chave_acesso' => $chave,
        'tipo_documento' => 'NFE', 'modelo' => '55', 'status' => 'AUTORIZADA', 'valor_total' => 1000.00,
        'consultado_em' => now(),
    ]);

    actingAs($user)
        ->getJson("/app/clearance/notas/resultado/{$lote->id}")
        ->assertOk()
        ->assertJsonPath('veredito.severidade', 'ok')
        ->assertJsonPath('kpis.existencia.encontradas', 1);
});
