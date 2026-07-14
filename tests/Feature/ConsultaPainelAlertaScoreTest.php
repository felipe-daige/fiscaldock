<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use App\Services\RiskScoreService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function planoPainelAlerta(): MonitoramentoPlano
{
    return MonitoramentoPlano::ativos()->first() ?? MonitoramentoPlano::create([
        'nome' => 'Gratuito', 'codigo' => 'gratuito', 'ativo' => true,
        'creditos_por_consulta' => 0, 'consultas_incluidas' => [], 'etapas' => [],
    ]);
}

function participantePainelAlerta(User $user, string $documento = '11111111000111'): Participante
{
    return Participante::create([
        'user_id' => $user->id, 'documento' => $documento,
        'razao_social' => 'FORNECEDOR '.$documento, 'uf' => 'SP',
    ]);
}

function resultadoPainelAlerta(User $user, Participante $part, array $dados): ConsultaResultado
{
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => planoPainelAlerta()->id, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 0, 'tab_id' => 'tab-'.uniqid(), 'processado_em' => now(),
    ]);
    $lote->participantes()->attach([$part->id]);

    return ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $part->id,
        'status' => ConsultaResultado::STATUS_SUCESSO, 'consultado_em' => now(),
        'resultado_dados' => $dados,
    ]);
}

function alertaDoPainel($testCase, User $user, Participante $part): array
{
    $resp = $testCase->actingAs($user)->getJson('/app/consulta/nova/participantes');
    $resp->assertOk();
    $linha = collect($resp->json('data'))->firstWhere('id', $part->id);
    expect($linha)->not->toBeNull();

    return $linha;
}

it('cadastral-only nunca ganha check verde: classifica inconclusivo e pede mais consultas', function () {
    $user = User::factory()->create();
    $part = participantePainelAlerta($user);
    $dados = ['situacao_cadastral' => 'ATIVA', 'consultas_realizadas' => ['situacao_cadastral']];
    resultadoPainelAlerta($user, $part, $dados);
    app(RiskScoreService::class)->atualizarScore($part, $dados);

    $linha = alertaDoPainel($this, $user, $part);

    expect($linha['alerta_nivel'])->toBe('inconclusivo')
        ->and($linha['alerta_label'])->toBe('Risco Não Conclusivo')
        ->and($linha['alerta_detalhe'])->toContain('situação cadastral')
        ->and($linha['alerta_detalhe'])->toContain('Consulte')
        ->and($linha['alerta_motivo_curto'])->toBeNull();
});

it('CND Estadual positiva puxa o ícone para alto risco mesmo com federal negativa (regressão do check verde)', function () {
    $user = User::factory()->create();
    $part = participantePainelAlerta($user);
    $dados = [
        'situacao_cadastral' => 'ATIVA',
        'cnd_federal' => ['status' => 'Negativa', 'certidao_codigo' => 'X1'],
        'cnd_estadual' => ['status' => 'Positiva', 'certidao_codigo' => 'X2'],
        'crf_fgts' => ['status' => 'Regular', 'certidao_codigo' => 'X3'],
        'cndt' => ['status' => 'Negativa', 'certidao_codigo' => 'X4'],
    ];
    resultadoPainelAlerta($user, $part, $dados);
    app(RiskScoreService::class)->atualizarScore($part, $dados);

    $linha = alertaDoPainel($this, $user, $part);

    expect($linha['alerta_nivel'])->toBe('alto')
        ->and($linha['alerta_motivo_curto'])->toContain('CND Estadual positiva')
        ->and($linha['alerta_hex'])->toBe('#ea580c');
});

it('situação BAIXADA classifica crítico com o motivo visível', function () {
    $user = User::factory()->create();
    $part = participantePainelAlerta($user);
    $dados = ['situacao_cadastral' => 'BAIXADA', 'consultas_realizadas' => ['situacao_cadastral']];
    resultadoPainelAlerta($user, $part, $dados);
    app(RiskScoreService::class)->atualizarScore($part, $dados);

    $linha = alertaDoPainel($this, $user, $part);

    expect($linha['alerta_nivel'])->toBe('critico')
        ->and($linha['alerta_label'])->toBe('Risco Crítico')
        ->and($linha['alerta_motivo_curto'])->toContain('BAIXADA')
        ->and($linha['alerta_hex'])->toBe('#dc2626');
});

it('data_validade d/m/Y futura não marca CND como vencida (regressão Carbon m/d/Y)', function () {
    $user = User::factory()->create();
    $part = participantePainelAlerta($user);
    // 05/08 = 5 de agosto. Carbon::parse lia como 8 de maio (m/d/Y) e marcava vencida.
    $dados = [
        'situacao_cadastral' => 'ATIVA',
        'cnd_federal' => ['status' => 'Negativa', 'certidao_codigo' => 'X1', 'data_validade' => '05/08/2099'],
        'cnd_estadual' => ['status' => 'Negativa', 'certidao_codigo' => 'X2'],
        'cndt' => ['status' => 'Negativa', 'certidao_codigo' => 'X3'],
    ];
    resultadoPainelAlerta($user, $part, $dados);
    app(RiskScoreService::class)->atualizarScore($part, $dados);

    $linha = alertaDoPainel($this, $user, $part);

    expect($linha['alerta_nivel'])->toBe('baixo')
        ->and($linha['alerta_detalhe'])->not->toContain('vencida')
        ->and($linha['cnd_federal_meta'])->toContain('Validade: 05/08/2099');
});

it('mantém a CND válida durante todo o dia de vencimento', function () {
    Carbon::setTestNow('2026-07-14 18:30:00');

    try {
        $user = User::factory()->create();
        $part = participantePainelAlerta($user);
        $dados = [
            'situacao_cadastral' => 'ATIVA',
            'cnd_federal' => ['status' => 'Negativa', 'certidao_codigo' => 'X1', 'data_validade' => '14/07/2026'],
            'cnd_estadual' => ['status' => 'Negativa', 'certidao_codigo' => 'X2'],
            'cndt' => ['status' => 'Negativa', 'certidao_codigo' => 'X3'],
        ];
        resultadoPainelAlerta($user, $part, $dados);
        app(RiskScoreService::class)->atualizarScore($part, $dados);

        $linha = alertaDoPainel($this, $user, $part);

        expect($linha['cnd_federal_meta'])->toBe('Vence hoje')
            ->and($linha['alerta_detalhe'])->not->toContain('vencida');
    } finally {
        Carbon::setTestNow();
    }
});

it('CND vencida vira aviso de reconsulta sem mudar a classificação', function () {
    $user = User::factory()->create();
    $part = participantePainelAlerta($user);
    $dados = [
        'situacao_cadastral' => 'ATIVA',
        'cnd_federal' => ['status' => 'Negativa', 'certidao_codigo' => 'X1', 'data_validade' => '05/01/2020'],
        'cnd_estadual' => ['status' => 'Negativa', 'certidao_codigo' => 'X2'],
        'cndt' => ['status' => 'Negativa', 'certidao_codigo' => 'X3'],
    ];
    resultadoPainelAlerta($user, $part, $dados);
    app(RiskScoreService::class)->atualizarScore($part, $dados);

    $linha = alertaDoPainel($this, $user, $part);

    expect($linha['alerta_nivel'])->toBe('baixo')
        ->and($linha['alerta_detalhe'])->toContain('vencida em 05/01/2020');
});

it('participante sem consulta segue como nunca consultado', function () {
    $user = User::factory()->create();
    $part = participantePainelAlerta($user);

    $linha = alertaDoPainel($this, $user, $part);

    expect($linha['alerta_nivel'])->toBe('nunca_consultado')
        ->and($linha['alerta_label'])->toBe('Nunca consultado');
});

it('sem linha de score o alerta é computado do último resultado (fallback legado)', function () {
    $user = User::factory()->create();
    $part = participantePainelAlerta($user);
    resultadoPainelAlerta($user, $part, [
        'situacao_cadastral' => 'ATIVA',
        'consultas_realizadas' => ['situacao_cadastral'],
    ]);

    $linha = alertaDoPainel($this, $user, $part);

    expect($linha['alerta_nivel'])->toBe('inconclusivo');
});

it('consulta parcial posterior não rebaixa o alerta: merge do score preserva certidões anteriores', function () {
    $user = User::factory()->create();
    $part = participantePainelAlerta($user);
    $service = app(RiskScoreService::class);

    // 1ª consulta completa (compliance)
    $completa = [
        'situacao_cadastral' => 'ATIVA',
        'cnd_federal' => ['status' => 'Negativa', 'certidao_codigo' => 'X1'],
        'cnd_estadual' => ['status' => 'Negativa', 'certidao_codigo' => 'X2'],
        'cndt' => ['status' => 'Negativa', 'certidao_codigo' => 'X3'],
        'consultas_realizadas' => ['situacao_cadastral', 'cnd_federal', 'cnd_estadual', 'cndt'],
    ];
    resultadoPainelAlerta($user, $part, $completa);
    $service->atualizarScore($part, $completa);

    // 2ª consulta só cadastral — é o ÚLTIMO resultado, mas o merge mantém as certidões
    $parcial = ['situacao_cadastral' => 'ATIVA', 'consultas_realizadas' => ['situacao_cadastral']];
    resultadoPainelAlerta($user, $part, $parcial);
    $service->atualizarScore($part, $parcial);

    $linha = alertaDoPainel($this, $user, $part);

    expect($linha['alerta_nivel'])->toBe('baixo')
        ->and($linha['cnd_federal_status_label'])->toBe('Negativa');
});

it('aba clientes herda o risco do participante equivalente pelo documento', function () {
    $user = User::factory()->create();
    $doc = '33333333000133';
    $cliente = Cliente::create([
        'user_id' => $user->id, 'razao_social' => 'EMPRESA CLIENTE', 'documento' => $doc,
        'tipo_pessoa' => 'PJ', 'ativo' => true,
    ]);
    $part = participantePainelAlerta($user, $doc);
    $dados = ['situacao_cadastral' => 'INAPTA', 'consultas_realizadas' => ['situacao_cadastral']];
    resultadoPainelAlerta($user, $part, $dados);
    app(RiskScoreService::class)->atualizarScore($part, $dados);

    $resp = $this->actingAs($user)->getJson('/app/consulta/nova/clientes');
    $resp->assertOk();
    $linha = collect($resp->json('data'))->firstWhere('id', $cliente->id);

    expect($linha)->not->toBeNull()
        ->and($linha['alerta_nivel'])->toBe('critico')
        ->and($linha['alerta_motivo_curto'])->toContain('INAPTA');
});
