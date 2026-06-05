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
