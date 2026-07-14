<?php

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use App\Services\Participantes\DossieParticipanteBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->builder = app(DossieParticipanteBuilder::class);
    $this->user = User::factory()->create();
    $this->p = Participante::create(['user_id' => $this->user->id, 'documento' => '07863768000138', 'razao_social' => 'ACME LTDA', 'uf' => 'SP', 'crt' => '3']);
});

it('monta payload com blocos esperados mesmo sem consulta nem movimentacao', function () {
    $d = $this->builder->montar($this->p);

    expect($d['participante']->id)->toBe($this->p->id)
        ->and($d['consulta']['tem'])->toBeFalse()
        ->and($d['movimentacao']['kpis']['total_notas'])->toBe(0)
        ->and($d['score'])->toHaveKeys(['score_total', 'classificacao', 'scores'])
        ->and($d)->toHaveKey('gerado_em');
});

it('CPF sem fonte de crédito fica não avaliado e nunca recebe fallback médio', function () {
    $cpf = Participante::create([
        'user_id' => $this->user->id,
        'documento' => '12345678901',
        'razao_social' => 'PESSOA FISICA',
    ]);

    $d = $this->builder->montar($cpf);

    expect($d['score'])
        ->toMatchArray([
            'tipo' => 'credito_cpf',
            'score_total' => null,
            'classificacao' => 'nao_avaliado',
            'avaliado' => false,
        ])
        ->and($d['consulta']['aplicavel'])->toBeFalse()
        ->and($d['consulta']['tem'])->toBeFalse();
});

it('inclui dados da ultima consulta sucesso quando existe', function () {
    $plano = MonitoramentoPlano::ativos()->first();
    if (! $plano) {
        $plano = MonitoramentoPlano::create([
            'nome' => 'Gratuito',
            'codigo' => 'gratuito',
            'ativo' => true,
            'creditos_por_consulta' => 0,
            'consultas_incluidas' => [],
            'etapas' => [],
        ]);
    }

    $lote = ConsultaLote::create([
        'user_id' => $this->user->id, 'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_FINALIZADO, 'total_participantes' => 1, 'creditos_cobrados' => 0,
        'tab_id' => 'tab-'.uniqid(), 'processado_em' => now(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $this->p->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['razao_social' => 'ACME LTDA', 'situacao_cadastral' => 'ATIVA', 'cnd_federal' => ['status' => 'NEGATIVA']],
        'consultado_em' => now(),
    ]);

    $d = $this->builder->montar($this->p);

    expect($d['consulta']['tem'])->toBeTrue()
        ->and($d['consulta']['blocos'])->not->toBeEmpty()
        ->and($d['score'])->toHaveKeys(['score_total', 'classificacao', 'scores'])
        ->and($d['consulta']['consultado_em'])->not->toBeNull();
});

it('score inclui detalhamento por categoria (label+peso+avaliado)', function () {
    $plano = MonitoramentoPlano::ativos()->first() ?? MonitoramentoPlano::create([
        'nome' => 'Gratuito', 'codigo' => 'gratuito', 'ativo' => true,
        'creditos_por_consulta' => 0, 'consultas_incluidas' => [], 'etapas' => [],
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $this->user->id, 'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_FINALIZADO, 'total_participantes' => 1, 'creditos_cobrados' => 0,
        'tab_id' => 'tab-det-'.uniqid(), 'processado_em' => now(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => $this->p->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['situacao_cadastral' => 'ATIVA', 'cnd_federal' => ['status' => 'NEGATIVA']],
        'consultado_em' => now(),
    ]);

    $d = $this->builder->montar($this->p);

    expect($d['score'])->toHaveKey('detalhamento');
    expect(array_keys($d['score']['detalhamento']))
        ->toBe(array_keys(app(\App\Services\RiskScoreService::class)->getPesos()));
    expect($d['score']['detalhamento']['cadastral']['avaliado'])->toBeTrue();
    expect($d['score']['detalhamento']['cadastral']['label'])->toBe('Situação Cadastral');
});

it('inclui top_produtos e top_cfops do acervo EFD do participante', function () {
    // vazio sem acervo
    $vazio = $this->builder->montar($this->p);
    expect($vazio['top_produtos'])->toBe([]);
    expect($vazio['top_cfops'])->toBe([]);

    // semeia acervo: 1 nota fiscal entrada + item + C190
    $cliente = \Illuminate\Support\Facades\DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'EMP', 'documento' => '00000000000100',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $imp = \App\Models\EfdImportacao::create(['user_id' => $this->user->id, 'cliente_id' => $cliente, 'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'f.txt', 'status' => 'concluido', 'iniciado_em' => now()]);
    $nota = \App\Models\EfdNota::create([
        'user_id' => $this->user->id, 'cliente_id' => $cliente, 'participante_id' => $this->p->id,
        'importacao_id' => $imp->id, 'numero' => '1', 'serie' => '1', 'modelo' => '55',
        'origem_arquivo' => 'fiscal', 'tipo_operacao' => 'entrada', 'valor_total' => 1000,
        'valor_desconto' => 0, 'cancelada' => false, 'data_emissao' => '2024-05-01',
    ]);
    \Illuminate\Support\Facades\DB::table('efd_notas_itens')->insert([
        'efd_nota_id' => $nota->id, 'user_id' => $this->user->id, 'numero_item' => 1,
        'codigo_item' => 'AGUA', 'descricao' => 'AGUA MINERAL', 'quantidade' => 1,
        'unidade_medida' => 'UN', 'valor_unitario' => 1000, 'valor_total' => 1000,
        'cfop' => 1102, 'cst_icms' => '00', 'aliquota_icms' => 18, 'created_at' => now(), 'updated_at' => now(),
    ]);
    \Illuminate\Support\Facades\DB::table('efd_notas_consolidados')->insert([
        'efd_nota_id' => $nota->id, 'user_id' => $this->user->id, 'cfop' => 1102, 'cst_icms' => '00',
        'aliquota_icms' => 18, 'valor_operacao' => 1000, 'valor_bc_icms' => 1000, 'valor_icms' => 180,
        'valor_bc_icms_st' => 0, 'valor_icms_st' => 0, 'valor_reducao_bc' => 0, 'valor_ipi' => 0,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $d = $this->builder->montar($this->p);
    expect($d['top_produtos'][0]['cod_item'])->toBe('AGUA');
    expect($d['top_cfops'][0]['cfop'])->toBe(1102);
});
