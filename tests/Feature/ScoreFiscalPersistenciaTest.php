<?php

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use App\Models\User;
use App\Services\Consultas\FecharLoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function novoLote(User $user): ConsultaLote
{
    return ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => MonitoramentoPlano::porCodigo('due_diligence')->id,
        'status' => ConsultaLote::STATUS_PROCESSANDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 35,
        'tab_id' => (string) Str::uuid(),
    ]);
}

it('persiste o score do participante ao fechar o lote', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181', 'razao_social' => 'ACME LTDA',
    ]);
    $lote = novoLote($user);

    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $part->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'situacao_cadastral' => 'ATIVA',
            'cnd_federal' => ['status' => 'Negativa'],
            'cnd_estadual' => ['status' => 'Negativa'], // 2ª certidão → cobertura suficiente
            'cgu_cnc' => ['possui_sancao' => false],
        ],
    ]);

    app(FecharLoteService::class)->fechar($lote->id);

    $score = ParticipanteScore::where('participante_id', $part->id)->first();
    expect($score)->not->toBeNull();
    expect($score->score_cadastral)->toBe(0);
    expect($score->score_cnd_federal)->toBe(0);
    expect($score->score_compliance)->toBe(0);
    expect($score->score_total)->toBe(0);
    expect($score->classificacao)->toBe('baixo');
    // dívida: ESG/protestos sem fonte -> null
    expect($score->score_esg)->toBeNull();
    expect($score->score_protestos)->toBeNull();
});

it('classifica risco alto quando CND federal e estadual positivas', function () {
    // Compliance/sanções (CGU) saiu do score; certidão irregular maxa em 70 → 'alto'.
    // cadastral omitido de propósito p/ não diluir o total (renorm só sobre federal+estadual).
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000182', 'razao_social' => 'BAD LTDA',
    ]);
    $lote = novoLote($user);

    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $part->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'cnd_federal' => ['status' => 'Positiva'],
            'cnd_estadual' => ['status' => 'Positiva'],
        ],
    ]);

    app(FecharLoteService::class)->fechar($lote->id);

    $score = ParticipanteScore::where('participante_id', $part->id)->first();
    expect($score->classificacao)->toBe('alto');
});

it('cria score para resultado de cliente (empresa gerida/propria)', function () {
    $user = User::factory()->create();
    $cliente = \App\Models\Cliente::create([
        'user_id' => $user->id, 'documento' => '99888777000166', 'razao_social' => 'MINHA EMPRESA',
    ]);
    $lote = novoLote($user);

    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'cliente_id' => $cliente->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['situacao_cadastral' => 'ATIVA', 'cnd_federal' => ['status' => 'Negativa'], 'cnd_estadual' => ['status' => 'Negativa']],
    ]);

    app(FecharLoteService::class)->fechar($lote->id);

    $score = ParticipanteScore::where('cliente_id', $cliente->id)->first();
    expect($score)->not->toBeNull();
    expect($score->participante_id)->toBeNull();
    expect($score->classificacao)->toBe('baixo');
    expect($cliente->fresh()->score)->not->toBeNull();
});

it('persiste score_credito_reforma a partir do regime (MEI => 100)', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000184', 'razao_social' => 'MEI LTDA',
    ]);
    $lote = novoLote($user);

    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $part->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['situacao_cadastral' => 'ATIVA', 'mei' => true],
    ]);

    app(FecharLoteService::class)->fechar($lote->id);

    $score = ParticipanteScore::where('participante_id', $part->id)->first();
    expect($score->score_credito_reforma)->toBe(100);
});

it('score_credito_reforma usa o crt do proprio participante (Regime Normal => 0)', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000185', 'razao_social' => 'NORMAL SA', 'crt' => 3,
    ]);
    $lote = novoLote($user);

    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $part->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['situacao_cadastral' => 'ATIVA'],
    ]);

    app(FecharLoteService::class)->fechar($lote->id);

    $score = ParticipanteScore::where('participante_id', $part->id)->first();
    expect($score->score_credito_reforma)->toBe(0);
});

it('marca nao_avaliado quando nada e avaliavel', function () {
    $user = User::factory()->create();
    $part = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000183', 'razao_social' => 'SEM DADOS LTDA',
    ]);
    $lote = novoLote($user);

    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $part->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'cnd_federal' => ['status' => 'INDISPONIVEL'],
            'cndt' => ['status' => 'INDISPONIVEL'],
        ],
    ]);

    app(FecharLoteService::class)->fechar($lote->id);

    $score = ParticipanteScore::where('participante_id', $part->id)->first();
    expect($score)->not->toBeNull();
    expect($score->score_total)->toBeNull();
    expect($score->classificacao)->toBe('nao_avaliado');
});
