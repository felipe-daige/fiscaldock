<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use App\Models\XmlNota;
use App\Services\BiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('Dashboard de Notas: saldo do KPI == getResumoGeral.saldo_liquido (consistência cross-surface)', function () {
    $user = User::factory()->create();
    $cli = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'documento' => '00000000000191', 'razao_social' => 'E',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cli, 'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'x.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);
    $mk = fn (array $a) => EfdNota::create(array_merge([
        'user_id' => $user->id, 'cliente_id' => $cli, 'importacao_id' => $imp->id,
        'numero' => random_int(1, 99999), 'serie' => '1', 'modelo' => '55', 'valor_desconto' => 0,
        'cancelada' => false, 'origem_arquivo' => 'fiscal', 'data_emissao' => '2026-03-10',
    ], $a));
    $c190 = fn (EfdNota $n, int $cfop, float $v) => DB::table('efd_notas_consolidados')->insert([
        'efd_nota_id' => $n->id, 'user_id' => $user->id, 'cfop' => $cfop, 'cst_icms' => '00', 'aliquota_icms' => 18,
        'valor_operacao' => $v, 'valor_bc_icms' => 0, 'valor_icms' => 0, 'valor_bc_icms_st' => 0,
        'valor_icms_st' => 0, 'valor_reducao_bc' => 0, 'valor_ipi' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $c190($mk(['tipo_operacao' => 'saida', 'valor_total' => 1000, 'chave_acesso' => str_pad('A', 44, '0')]), 5102, 1000.00);
    $c190($mk(['tipo_operacao' => 'entrada', 'valor_total' => 400, 'chave_acesso' => str_pad('B', 44, '0')]), 1102, 400.00);

    // XML venda: invisível pra query EFD-only do controller, mas getResumoGeral (XML+EFD)
    // a soma — força a divergência que o KPI canônico deve resolver.
    XmlNota::create([
        'user_id' => $user->id, 'cliente_id' => $cli, 'chave_acesso' => str_repeat('9', 44),
        'tipo_documento' => 'NFE', 'numero_documento' => 9001, 'serie' => 1,
        'data_emissao' => '2026-03-20', 'natureza_operacao' => 'VENDA', 'valor_total' => 250.00,
        'tipo_nota' => 1, 'finalidade' => 1,
        'emit_documento' => '12345678000199', 'emit_razao_social' => 'Empresa', 'emit_uf' => 'SP',
        'dest_documento' => '98765432000188', 'dest_razao_social' => 'Cliente', 'dest_uf' => 'SP',
    ]);

    // Controller EFD-only: 1000 − 400 = 600. getResumoGeral (XML+EFD): (1000+250) − 400 = 850.
    $saldoCanonico = app(BiService::class)->getResumoGeral($user->id)['saldo_liquido'];

    // endpoint de dados dos KPIs = visaoGeral (getJson já manda X-Requested-With)
    $resp = $this->actingAs($user)->getJson('/app/notas/dashboard/visao-geral');
    $resp->assertOk();
    expect((float) $resp->json('kpis.saldo'))->toBe(round($saldoCanonico, 2));
});
