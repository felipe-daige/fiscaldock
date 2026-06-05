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
