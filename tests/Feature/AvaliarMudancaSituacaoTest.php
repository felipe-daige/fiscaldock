<?php

use App\Actions\Monitoramento\AvaliarMudancaSituacao;
use App\Models\Alerta;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoConsulta;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use Database\Seeders\MonitoramentoPlanoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(MonitoramentoPlanoSeeder::class);
    $this->plano = MonitoramentoPlano::query()->first();
    $this->user = User::factory()->create();
    $this->participante = Participante::create([
        'user_id' => $this->user->id, 'documento' => '11222333000181',
        'tipo_documento' => 'PJ', 'razao_social' => 'Fornecedor X',
    ]);
    $this->assinatura = MonitoramentoAssinatura::create([
        'user_id' => $this->user->id, 'participante_id' => $this->participante->id,
        'plano_id' => $this->plano->id, 'status' => 'ativo', 'frequencia_dias' => 30,
    ]);
});

function consultaSucesso(array $attrs, $ctx): MonitoramentoConsulta
{
    return MonitoramentoConsulta::create(array_merge([
        'user_id' => $ctx->user->id, 'participante_id' => $ctx->participante->id,
        'plano_id' => $ctx->plano->id, 'assinatura_id' => $ctx->assinatura->id,
        'tipo' => 'assinatura', 'status' => 'sucesso', 'creditos_cobrados' => 5,
        'executado_em' => now(),
    ], $attrs));
}

it('não gera alerta no baseline (sem consulta anterior)', function () {
    $consulta = consultaSucesso(['situacao_geral' => 'regular'], $this);

    app(AvaliarMudancaSituacao::class)->execute($consulta);

    expect(Alerta::count())->toBe(0);
});

it('gera alerta de piora quando a situação regride', function () {
    consultaSucesso(['situacao_geral' => 'regular'], $this);
    $nova = consultaSucesso(['situacao_geral' => 'irregular'], $this);

    app(AvaliarMudancaSituacao::class)->execute($nova);

    expect(Alerta::where('tipo', 'monitoramento_situacao_piorou')->count())->toBe(1);
});

it('gera alerta de melhora quando a situação avança', function () {
    consultaSucesso(['situacao_geral' => 'irregular'], $this);
    $nova = consultaSucesso(['situacao_geral' => 'regular'], $this);

    app(AvaliarMudancaSituacao::class)->execute($nova);

    expect(Alerta::where('tipo', 'monitoramento_situacao_melhorou')->count())->toBe(1);
});

it('gera alerta quando surgem pendências', function () {
    consultaSucesso(['situacao_geral' => 'regular', 'tem_pendencias' => false], $this);
    $nova = consultaSucesso(['situacao_geral' => 'regular', 'tem_pendencias' => true], $this);

    app(AvaliarMudancaSituacao::class)->execute($nova);

    expect(Alerta::where('tipo', 'monitoramento_pendencias_surgiram')->count())->toBe(1);
});

it('gera alerta de certidão vencendo quando proxima_validade está dentro de 30 dias', function () {
    $nova = consultaSucesso([
        'situacao_geral' => 'regular',
        'proxima_validade' => now()->addDays(10)->toDateString(),
    ], $this);

    app(AvaliarMudancaSituacao::class)->execute($nova);

    expect(Alerta::where('tipo', 'monitoramento_certidao_vencendo')->count())->toBe(1);
});
