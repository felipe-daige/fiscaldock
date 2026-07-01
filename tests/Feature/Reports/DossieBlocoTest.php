<?php

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use App\Services\Participantes\DossieParticipanteBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('o bloco reusavel renderiza as mesmas secoes do dossie', function () {
    $user = User::factory()->trialAtivo()->create();
    $p = Participante::create(['user_id' => $user->id, 'documento' => '07863768000138', 'razao_social' => 'ACME BLOCO LTDA', 'uf' => 'SP', 'crt' => '3']);
    $plano = MonitoramentoPlano::ativos()->first() ?? MonitoramentoPlano::create([
        'nome' => 'Gratuito', 'codigo' => 'gratuito', 'ativo' => true, 'creditos_por_consulta' => 0, 'consultas_incluidas' => [], 'etapas' => [],
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => $plano->id, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 0, 'tab_id' => 'tab-'.uniqid(), 'processado_em' => now(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $p->id, 'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['razao_social' => 'ACME BLOCO LTDA', 'situacao_cadastral' => 'ATIVA', 'cnd_federal' => ['status' => 'NEGATIVA']],
        'consultado_em' => now(),
    ]);
    criarNotaEfd($user, $p, 'saida', '2026-01-10', 500);

    $dados = app(DossieParticipanteBuilder::class)->montar($p);
    $html = view('reports.dossie._bloco', $dados)->render();

    expect($html)->toContain('ACME BLOCO LTDA')
        ->and($html)->toContain('Movimentações')
        ->and($html)->toContain('Regularidade')
        ->and($html)->toContain('Infográficos')
        ->and($html)->toContain('Detalhamento');
});
