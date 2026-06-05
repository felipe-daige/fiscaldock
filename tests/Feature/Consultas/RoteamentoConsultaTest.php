<?php

use App\Jobs\ProcessarConsultaJob;
use App\Models\MonitoramentoPlano;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('plano Gratuito (coberto pelo Registry) despacha batch Laravel e não chama n8n', function () {
    $this->seed(\Database\Seeders\MonitoramentoPlanoSeeder::class);
    $gratuito = MonitoramentoPlano::where('codigo', 'gratuito')->firstOrFail();

    Bus::fake();
    Http::fake(); // qualquer chamada a webhook n8n falharia a asserção abaixo

    $user = User::factory()->create();
    $participanteId = DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'documento' => '19131243000197', 'razao_social' => 'PART',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($user)->postJson('/app/consulta/nova/executar', [
        'participante_ids' => [$participanteId],
        'plano_id' => $gratuito->id,
        'tab_id' => 'tab-test',
    ])->assertOk()->assertJson(['success' => true]);

    Bus::assertBatched(fn ($batch) => collect($batch->jobs)->first() instanceof ProcessarConsultaJob);
    Http::assertNothingSent();
});

it('aceita escopo cliente (cliente_ids) no plano Gratuito → batch Laravel', function () {
    $this->seed(\Database\Seeders\MonitoramentoPlanoSeeder::class);
    $gratuito = MonitoramentoPlano::where('codigo', 'gratuito')->firstOrFail();

    Bus::fake();
    Http::fake();

    $user = User::factory()->create();
    $clienteId = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'EMPRESA PROPRIA', 'documento' => '19131243000197',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($user)->postJson('/app/consulta/nova/executar', [
        'cliente_ids' => [$clienteId],
        'plano_id' => $gratuito->id,
        'tab_id' => 'tab-test',
    ])->assertOk()->assertJson(['success' => true]);

    Bus::assertBatched(fn ($batch) => $batch->jobs->first()->alvoTipo === 'cliente');
    Http::assertNothingSent();
});

it('seleção combinada: participantes + clientes geram jobs com alvoTipo correto', function () {
    $this->seed(\Database\Seeders\MonitoramentoPlanoSeeder::class);
    $gratuito = MonitoramentoPlano::where('codigo', 'gratuito')->firstOrFail();

    Bus::fake();
    Http::fake();

    $user = User::factory()->create();
    // 1 participante (contraparte)
    $participanteId = DB::table('participantes')->insertGetId([
        'user_id' => $user->id, 'documento' => '19131243000197', 'razao_social' => 'PART',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    // 2 clientes (CNPJ próprio)
    $c1 = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'CLI 1', 'documento' => '11111111111111',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $c2 = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'CLI 2', 'documento' => '22222222222222',
        'is_empresa_propria' => false, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($user)->postJson('/app/consulta/nova/executar', [
        'participante_ids' => [$participanteId],
        'cliente_ids' => [$c1, $c2],
        'plano_id' => $gratuito->id,
        'tab_id' => 'tab-test',
    ])->assertOk()->assertJson(['success' => true]);

    Bus::assertBatched(function ($batch) {
        $jobs = collect($batch->jobs);

        return $jobs->count() === 3
            && $jobs->where('alvoTipo', 'participante')->count() === 1
            && $jobs->where('alvoTipo', 'cliente')->count() === 2;
    });
});

it('rejeita escopo cliente quando o plano não migrou (n8n não suporta)', function () {
    $this->seed(\Database\Seeders\MonitoramentoPlanoSeeder::class);
    // licitacao tem cnd_federal (InfoSimples) → gate desligado → roteia n8n → cliente proibido
    config()->set('consultas.infosimples_ativo', false);
    $licitacao = MonitoramentoPlano::where('codigo', 'licitacao')->firstOrFail();

    $user = User::factory()->create();
    app(App\Services\CreditService::class)->add($user, 100, 'manual_add');
    $clienteId = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'EMP', 'documento' => '19131243000197',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($user)->postJson('/app/consulta/nova/executar', [
        'cliente_ids' => [$clienteId],
        'plano_id' => $licitacao->id,
        'tab_id' => 'tab-test',
    ])->assertStatus(422);
});
