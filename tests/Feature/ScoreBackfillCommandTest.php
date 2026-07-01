<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function loteConcluido(User $user): ConsultaLote
{
    return ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => MonitoramentoPlano::porCodigo('licitacao')->id,
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 10,
        'tab_id' => (string) Str::uuid(),
    ]);
}

it('backfilla o score das consultas ja feitas, usando o resultado mais recente', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'ACME LTDA',
    ]);

    // Consulta ANTIGA: CND positiva (irregular)
    ConsultaResultado::create([
        'consulta_lote_id' => loteConcluido($user)->id,
        'participante_id' => $part->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['situacao_cadastral' => 'ATIVA', 'cnd_federal' => ['status' => 'Positiva']],
        'consultado_em' => now()->subDays(10),
    ]);
    // Consulta NOVA: CND negativa (regular) — deve prevalecer
    ConsultaResultado::create([
        'consulta_lote_id' => loteConcluido($user)->id,
        'participante_id' => $part->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['situacao_cadastral' => 'ATIVA', 'cnd_federal' => ['status' => 'Negativa'], 'cnd_estadual' => ['status' => 'Negativa']],
        'consultado_em' => now(),
    ]);

    $this->artisan('score:backfill')->assertSuccessful();

    $score = ParticipanteScore::where('participante_id', $part->id)->first();
    expect($score)->not->toBeNull();
    expect($score->score_cnd_federal)->toBe(0); // negativa = regular (resultado mais recente)
    expect($score->classificacao)->toBe('baixo');
});

it('backfilla tambem o score de clientes consultados', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id, 'documento' => '99888777000166', 'razao_social' => 'MINHA EMPRESA',
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => loteConcluido($user)->id,
        'cliente_id' => $cliente->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['situacao_cadastral' => 'ATIVA', 'cnd_federal' => ['status' => 'Negativa'], 'cnd_estadual' => ['status' => 'Negativa']],
    ]);

    $this->artisan('score:backfill')->assertSuccessful();

    $score = ParticipanteScore::where('cliente_id', $cliente->id)->first();
    expect($score)->not->toBeNull();
    expect($score->classificacao)->toBe('baixo');
});

it('e idempotente (rodar duas vezes nao duplica)', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000182', 'razao_social' => 'BETA LTDA',
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => loteConcluido($user)->id,
        'participante_id' => $part->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['situacao_cadastral' => 'ATIVA'],
    ]);

    $this->artisan('score:backfill')->assertSuccessful();
    $this->artisan('score:backfill')->assertSuccessful();

    expect(ParticipanteScore::where('participante_id', $part->id)->count())->toBe(1);
});
