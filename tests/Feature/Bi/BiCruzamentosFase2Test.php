<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use App\Services\Bi\CruzamentosConsultasClearanceService;
use App\Services\RiskScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Fase 2 do BI cruzamentos: compras via XML (EFD vence na chave duplicada),
 * filtro de período e drill-down por documento.
 */
function f2Seed(User $user): array
{
    $cliente = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Escritorio ME',
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
    ]);
    $forn = Participante::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'documento' => '11111111000111', 'razao_social' => 'Fornecedor Devedor SA',
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 10, 'tab_id' => 'tab-f2', 'processado_em' => now(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $forn->id, 'status' => 'sucesso',
        'resultado_dados' => ['cnd_federal' => ['status' => 'Positiva']], 'consultado_em' => now(),
    ]);
    app(RiskScoreService::class)->atualizarScore($forn, ['cnd_federal' => ['status' => 'Positiva']]);

    return [$cliente, $imp, $forn];
}

function f2EfdNota(User $user, Cliente $cliente, EfdImportacao $imp, Participante $forn, array $ov = []): EfdNota
{
    return EfdNota::create(array_merge([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'participante_id' => $forn->id,
        'importacao_id' => $imp->id, 'chave_acesso' => '35240000000000000000000000000000000000040001',
        'modelo' => '55', 'numero' => 40001, 'serie' => '0', 'data_emissao' => '2026-01-15',
        'tipo_operacao' => 'entrada', 'valor_total' => 1000.00, 'valor_desconto' => 0,
        'origem_arquivo' => 'fiscal', 'metadados' => [],
    ], $ov));
}

function f2XmlNota(User $user, Cliente $cliente, array $ov = []): XmlNota
{
    $imp = XmlImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'status' => 'concluido', 'tipo_documento' => 'NFE',
    ]);

    return XmlNota::create(array_merge([
        'user_id' => $user->id, 'importacao_xml_id' => $imp->id, 'cliente_id' => $cliente->id,
        'chave_acesso' => '35240413305697000150550000000404041953940993',
        'tipo_documento' => 'NFE', 'numero_documento' => 777, 'serie' => 1,
        'data_emissao' => '2026-02-10 10:00:00', 'valor_total' => 500.00,
        'tipo_nota' => XmlNota::TIPO_ENTRADA, 'finalidade' => 1,
        'emit_documento' => '11111111000111', 'emit_razao_social' => 'Fornecedor Devedor SA',
        'dest_documento' => '00000000000191', 'dest_razao_social' => 'Escritorio ME',
    ], $ov));
}

it('soma compras XML (entrada) com as compras EFD do fornecedor irregular', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp, $forn] = f2Seed($user);
    f2EfdNota($user, $cliente, $imp, $forn);
    f2XmlNota($user, $cliente);

    $linhas = app(CruzamentosConsultasClearanceService::class)
        ->fornecedoresIrregularesComCompras($user->id);

    expect($linhas)->toHaveCount(1)
        ->and($linhas[0]['valor_comprado'])->toBe(1500.00)
        ->and($linhas[0]['qtd_notas'])->toBe(2);
});

it('XML com chave que já existe no EFD não conta duas vezes (EFD vence)', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp, $forn] = f2Seed($user);
    $chave = '35240000000000000000000000000000000000040001';
    f2EfdNota($user, $cliente, $imp, $forn, ['chave_acesso' => $chave]);
    f2XmlNota($user, $cliente, ['chave_acesso' => $chave]);

    $linhas = app(CruzamentosConsultasClearanceService::class)
        ->fornecedoresIrregularesComCompras($user->id);

    expect($linhas[0]['valor_comprado'])->toBe(1000.00)
        ->and($linhas[0]['qtd_notas'])->toBe(1);
});

it('XML de devolução e nota EFD cancelada ficam fora das compras', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp, $forn] = f2Seed($user);
    f2EfdNota($user, $cliente, $imp, $forn);
    f2EfdNota($user, $cliente, $imp, $forn, [
        'chave_acesso' => '35240000000000000000000000000000000000040002', 'numero' => 40002,
        'valor_total' => 9999.00, 'cancelada' => true,
    ]);
    f2XmlNota($user, $cliente, ['finalidade' => XmlNota::FINALIDADE_DEVOLUCAO, 'valor_total' => 8888.00]);

    $linhas = app(CruzamentosConsultasClearanceService::class)
        ->fornecedoresIrregularesComCompras($user->id);

    expect($linhas[0]['valor_comprado'])->toBe(1000.00)
        ->and($linhas[0]['qtd_notas'])->toBe(1);
});

it('filtro de período limita compras EFD e XML pela data de emissão', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp, $forn] = f2Seed($user);
    f2EfdNota($user, $cliente, $imp, $forn); // 2026-01-15
    f2XmlNota($user, $cliente); // 2026-02-10

    $service = app(CruzamentosConsultasClearanceService::class);

    $soFevereiro = $service->fornecedoresIrregularesComCompras($user->id, [
        'data_inicio' => '2026-02-01', 'data_fim' => '2026-02-28',
    ]);
    expect($soFevereiro[0]['valor_comprado'])->toBe(500.00)
        ->and($soFevereiro[0]['qtd_notas'])->toBe(1);

    $foraDoPeriodo = $service->fornecedoresIrregularesComCompras($user->id, [
        'data_inicio' => '2025-01-01', 'data_fim' => '2025-12-31',
    ]);
    expect($foraDoPeriodo)->toHaveCount(0);
});

it('página aceita filtro de período e mostra só o valor do intervalo', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp, $forn] = f2Seed($user);
    f2EfdNota($user, $cliente, $imp, $forn);
    f2XmlNota($user, $cliente);

    actingAs($user)
        ->get('/app/bi/cruzamentos?data_inicio=2026-01-01&data_fim=2026-01-31')
        ->assertOk()
        ->assertSee('Fornecedor Devedor SA')
        ->assertSee('1.000,00');
});

it('drill-down devolve os documentos EFD + XML do fornecedor em JSON', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp, $forn] = f2Seed($user);
    f2EfdNota($user, $cliente, $imp, $forn);
    f2XmlNota($user, $cliente);

    $resp = actingAs($user)
        ->getJson('/app/bi/cruzamentos/fornecedor/'.$forn->id.'/notas')
        ->assertOk();

    $notas = collect($resp->json('notas'));
    expect($notas)->toHaveCount(2)
        ->and($notas->pluck('origem')->sort()->values()->all())->toBe(['EFD', 'XML'])
        ->and((float) $notas->firstWhere('origem', 'EFD')['valor'])->toBe(1000.0)
        ->and((float) $notas->firstWhere('origem', 'XML')['valor'])->toBe(500.0)
        // ordenado por emissão desc: XML (fev) antes do EFD (jan)
        ->and($notas[0]['origem'])->toBe('XML');
});

it('drill-down respeita o filtro de período', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente, $imp, $forn] = f2Seed($user);
    f2EfdNota($user, $cliente, $imp, $forn);
    f2XmlNota($user, $cliente);

    $resp = actingAs($user)
        ->getJson('/app/bi/cruzamentos/fornecedor/'.$forn->id.'/notas?data_inicio=2026-02-01&data_fim=2026-02-28')
        ->assertOk();

    expect(collect($resp->json('notas'))->pluck('origem')->all())->toBe(['XML']);
});

it('drill-down 404 para participante de outro usuário e 403 sem BI completo', function () {
    $dono = User::factory()->trialAtivo()->create(['credits' => 100]);
    [, , $forn] = f2Seed($dono);

    $outro = User::factory()->trialAtivo()->create(['credits' => 100]);
    actingAs($outro)
        ->getJson('/app/bi/cruzamentos/fornecedor/'.$forn->id.'/notas')
        ->assertNotFound();

    $free = User::factory()->create(['credits' => 0]);
    actingAs($free)
        ->getJson('/app/bi/cruzamentos/fornecedor/'.$forn->id.'/notas')
        ->assertForbidden();
});

it('notas canceladas na SEFAZ respeitam filtro de período e cliente', function () {
    $user = User::factory()->trialAtivo()->create(['credits' => 100]);
    [$cliente] = f2Seed($user);

    \Illuminate\Support\Facades\DB::table('nfe_consultas')->insert([
        [
            'user_id' => $user->id, 'cliente_id' => $cliente->id,
            'chave_acesso' => '35240100000000000000550000000000010000000001',
            'tipo_documento' => 'NFE', 'modelo' => '55', 'status' => 'CANCELADA',
            'emit_cnpj' => '11111111000111', 'emit_nome' => 'Fornecedor Devedor SA',
            'valor_total' => 300, 'data_emissao' => '2026-01-20', 'consultado_em' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ],
        [
            'user_id' => $user->id, 'cliente_id' => $cliente->id,
            'chave_acesso' => '35240200000000000000550000000000020000000002',
            'tipo_documento' => 'NFE', 'modelo' => '55', 'status' => 'CANCELADA',
            'emit_cnpj' => '22222222000122', 'emit_nome' => 'Outro Emitente',
            'valor_total' => 400, 'data_emissao' => '2026-02-20', 'consultado_em' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ],
    ]);

    $service = app(CruzamentosConsultasClearanceService::class);

    expect($service->notasCanceladasComEmitente($user->id))->toHaveCount(2)
        ->and($service->notasCanceladasComEmitente($user->id, [
            'data_inicio' => '2026-01-01', 'data_fim' => '2026-01-31',
        ]))->toHaveCount(1)
        ->and($service->notasCanceladasComEmitente($user->id, [
            'cliente_id' => $cliente->id + 999,
        ]))->toHaveCount(0);
});
