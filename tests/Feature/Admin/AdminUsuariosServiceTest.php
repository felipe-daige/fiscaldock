<?php

use App\Models\User;
use App\Services\Admin\AdminUsuariosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('lista usuários com contagens derivadas e busca', function () {
    $a = User::factory()->create(['name' => 'Alpha', 'email' => 'alpha@x.com', 'empresa' => 'AlphaCorp', 'credits' => 10]);
    User::factory()->create(['name' => 'Beta', 'email' => 'beta@x.com']);

    DB::table('consulta_lotes')->insert(['user_id' => $a->id, 'status' => 'finalizado', 'total_participantes' => 1, 'creditos_cobrados' => 1, 'tab_id' => 't', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('efd_importacoes')->insert(['user_id' => $a->id, 'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'f', 'status' => 'concluido', 'iniciado_em' => now(), 'created_at' => now(), 'updated_at' => now()]);

    $svc = app(AdminUsuariosService::class);

    $todos = $svc->lista([], 20, 1);
    expect($todos->total())->toBe(2);
    $alpha = collect($todos->items())->firstWhere('id', $a->id);
    expect((int) $alpha->qtd_consultas)->toBe(1);
    expect((int) $alpha->qtd_importacoes)->toBe(1);

    $busca = $svc->lista(['q' => 'AlphaCorp'], 20, 1);
    expect($busca->total())->toBe(1);
    expect($busca->items()[0]->id)->toBe($a->id);
});

it('kpis do usuário somam consumo e pagamentos', function () {
    $u = User::factory()->create();
    DB::table('credit_transactions')->insert(['user_id' => $u->id, 'amount' => -7, 'balance_after' => 0, 'type' => 'consulta_lote', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('mercado_pago_payments')->insert(['user_id' => $u->id, 'status' => 'approved', 'valor' => 50.0, 'creditos' => 0, 'pacote' => 'licitacao', 'idempotency_key' => 'k-50', 'created_at' => now(), 'updated_at' => now()]);

    $k = app(AdminUsuariosService::class)->kpis($u->id);
    expect($k['creditos_consumidos'])->toBe(7.0);
    expect($k['total_pago'])->toBe(50.0);
});

it('detalhe consolida conta, financeiro e uso do usuário', function () {
    $u = User::factory()->trialAtivo()->create(['credits' => 25]);

    DB::table('clientes')->insert([
        'user_id' => $u->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000190',
        'razao_social' => 'Cliente A',
        'ativo' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('participantes')->insert([
        'user_id' => $u->id,
        'cnpj' => '11111111000191',
        'razao_social' => 'Fornecedor A',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('consulta_lotes')->insert([
        'user_id' => $u->id,
        'status' => 'concluido',
        'total_participantes' => 2,
        'creditos_cobrados' => 3,
        'tab_id' => 'det',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('efd_importacoes')->insert([
        'user_id' => $u->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'efd.txt',
        'status' => 'erro',
        'iniciado_em' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('xml_importacoes')->insert([
        'user_id' => $u->id,
        'tipo_documento' => 'NFE',
        'filename' => 'nota.xml',
        'status' => 'concluido',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('nfe_consultas')->insert([
        'user_id' => $u->id,
        'chave_acesso' => str_repeat('1', 44),
        'status' => 'autorizada',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('credit_transactions')->insert([
        ['user_id' => $u->id, 'amount' => 30, 'balance_after' => 55, 'type' => 'purchase', 'description' => 'compra', 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => $u->id, 'amount' => -5, 'balance_after' => 50, 'type' => 'consulta_lote', 'description' => 'uso', 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('mercado_pago_payments')->insert([
        'user_id' => $u->id,
        'status' => 'approved',
        'valor' => 30.0,
        'creditos' => 30,
        'pacote' => 'avulso',
        'idempotency_key' => 'detalhe-admin',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $detalhe = app(AdminUsuariosService::class)->detalhe($u->fresh());

    expect($detalhe['conta']['trial_ativo'])->toBeTrue();
    expect($detalhe['conta']['tem_compra_confirmada'])->toBeTrue();
    expect($detalhe['financeiro']['total_pago'])->toBe(30.0);
    expect($detalhe['financeiro']['creditos_consumidos'])->toBe(5.0);
    expect($detalhe['uso']['clientes_total'])->toBe(1);
    expect($detalhe['uso']['participantes_total'])->toBe(1);
    expect($detalhe['uso']['consultas_por_status']['concluido'])->toBe(1);
    expect($detalhe['uso']['importacoes_por_status'])->toMatchArray(['concluido' => 1, 'erro' => 1]);
    expect($detalhe['uso']['clearance_por_status']['autorizada'])->toBe(1);
});

it('ultimaSessao retorna a sessão mais recente do usuário', function () {
    $u = User::factory()->create();
    DB::table('sessions')->insert([
        ['id' => 's1', 'user_id' => $u->id, 'ip_address' => '1.1.1.1', 'user_agent' => 'UA', 'payload' => '', 'last_activity' => now()->subHour()->timestamp],
        ['id' => 's2', 'user_id' => $u->id, 'ip_address' => '2.2.2.2', 'user_agent' => 'UA', 'payload' => '', 'last_activity' => now()->timestamp],
    ]);

    $s = app(AdminUsuariosService::class)->ultimaSessao($u->id);
    expect($s->ip_address)->toBe('2.2.2.2');
});

it('timeline lista atividades do usuário ordenadas desc e cobre os tipos', function () {
    $u = User::factory()->create();
    DB::table('consulta_lotes')->insert(['user_id' => $u->id, 'status' => 'finalizado', 'total_participantes' => 3, 'creditos_cobrados' => 1, 'tab_id' => 't', 'created_at' => now()->subDays(2), 'updated_at' => now()]);
    DB::table('mercado_pago_payments')->insert(['user_id' => $u->id, 'status' => 'approved', 'valor' => 20.0, 'creditos' => 0, 'pacote' => 'licitacao', 'idempotency_key' => 'k-tl', 'created_at' => now(), 'updated_at' => now()]);

    $tl = app(AdminUsuariosService::class)->timeline($u->id);

    expect($tl)->toHaveCount(2);
    expect($tl->first()['tipo'])->toBe('pagamento');   // mais recente primeiro
    expect($tl->pluck('tipo')->all())->toContain('consulta');
});
