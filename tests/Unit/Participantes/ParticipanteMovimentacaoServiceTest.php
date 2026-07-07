<?php

use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Services\Participantes\ParticipanteMovimentacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->svc = app(ParticipanteMovimentacaoService::class);
    $this->user = User::factory()->create();
    $this->p = Participante::create(['user_id' => $this->user->id, 'documento' => '07863768000138', 'razao_social' => 'ACME LTDA', 'uf' => 'SP', 'crt' => '3']);
});

it('kpis somam entradas e saidas e ignoram canceladas', function () {
    criarNotaEfd($this->user, $this->p, 'entrada', '2026-01-10', 100);
    criarNotaEfd($this->user, $this->p, 'entrada', '2026-02-10', 50);
    criarNotaEfd($this->user, $this->p, 'saida', '2026-02-15', 200);
    criarNotaEfd($this->user, $this->p, 'saida', '2026-03-01', 9999, cancelada: true); // ignorada

    $k = $this->svc->kpis($this->p);

    expect($k['total_notas'])->toBe(3)
        ->and($k['entradas_qtd'])->toBe(2)
        ->and($k['entradas_valor'])->toBe(150.0)
        ->and($k['saidas_qtd'])->toBe(1)
        ->and($k['saidas_valor'])->toBe(200.0)
        ->and($k['valor_movimentado'])->toBe(350.0)
        ->and($k['periodo_inicio'])->toBe('2026-01')
        ->and($k['periodo_fim'])->toBe('2026-02');
});

it('porCompetencia pivota entrada/saida e ignora data nula', function () {
    criarNotaEfd($this->user, $this->p, 'entrada', '2026-01-10', 100);
    criarNotaEfd($this->user, $this->p, 'saida', '2026-01-20', 70);
    $n = criarNotaEfd($this->user, $this->p, 'entrada', '2026-02-01', 40);
    EfdNota::where('id', $n->id)->update(['data_emissao' => null]); // não deve aparecer

    $comp = $this->svc->porCompetencia($this->p);

    expect($comp)->toHaveCount(1)
        ->and($comp[0])->toBe(['competencia' => '2026-01', 'entrada' => 100.0, 'saida' => 70.0]);
});

it('porCfop/porCst/impostos leem C190 (ICMS) + itens contribuições (PIS/COFINS)', function () {
    // CFOP/CST/ICMS vêm do consolidado C190 (fonte canônica), não do item-level.
    $n1 = criarNotaEfd($this->user, $this->p, 'saida', '2026-01-10', 30);
    $n2 = criarNotaEfd($this->user, $this->p, 'saida', '2026-01-11', 90);
    criarConsolidadoEfd($n1, ['cfop' => '5102', 'cst_icms' => '00', 'valor_operacao' => 30, 'valor_icms' => 3, 'aliquota_icms' => 10]);
    criarConsolidadoEfd($n2, ['cfop' => '6102', 'cst_icms' => '00', 'valor_operacao' => 90, 'valor_icms' => 9, 'aliquota_icms' => 10]);

    // PIS/COFINS vêm dos itens da EFD de contribuições (gêmea da mesma NF-e).
    $nc = \App\Models\EfdNota::create([
        'user_id' => $this->user->id, 'cliente_id' => $n1->cliente_id, 'importacao_id' => $n1->importacao_id,
        'participante_id' => $this->p->id, 'modelo' => '55', 'numero' => '900001', 'origem_arquivo' => 'contribuicoes',
        'tipo_operacao' => 'saida', 'valor_total' => 120, 'data_emissao' => '2026-01-10', 'cancelada' => false,
    ]);
    criarItemEfd($nc, ['numero_item' => 1, 'cfop' => '5102', 'valor_total' => 30, 'valor_pis' => 1, 'valor_cofins' => 2]);
    criarItemEfd($nc, ['numero_item' => 2, 'cfop' => '6102', 'valor_total' => 90, 'valor_pis' => 1, 'valor_cofins' => 2]);

    $cfop = $this->svc->porCfop($this->p);
    expect($cfop[0]['cfop'])->toBe('6102')->and($cfop[0]['valor'])->toBe(90.0);
    expect($cfop[1]['cfop'])->toBe('5102');

    $cst = $this->svc->porCst($this->p);
    expect($cst)->toHaveCount(1)
        ->and($cst[0])->toMatchArray(['cst' => '00', 'qtd' => 2, 'valor' => 120.0]);

    $imp = $this->svc->impostos($this->p);
    expect($imp['icms'])->toBe(12.0)->and($imp['pis'])->toBe(2.0)->and($imp['cofins'])->toBe(4.0)
        ->and($imp['aliquota_icms_media'])->toBe(10.0);
});
