<?php

use App\Services\RiskScoreService;

beforeEach(function () {
    $this->svc = new RiskScoreService;
});

// ---------- subscores por categoria (shape ANINHADO real do resultado_dados) ----------

it('cadastral: ATIVA e regular (0), inapta e critico (100)', function () {
    expect($this->svc->calcularScores(['situacao_cadastral' => 'ATIVA'])['cadastral'])->toBe(0);
    expect($this->svc->calcularScores(['situacao_cadastral' => 'INAPTA'])['cadastral'])->toBe(100);
    expect($this->svc->calcularScores(['situacao_cadastral' => 'SUSPENSA'])['cadastral'])->toBe(50);
});

it('cadastral ausente e nao avaliado (null)', function () {
    expect($this->svc->calcularScores([])['cadastral'])->toBeNull();
});

it('cnd_federal negativa (shape aninhado) e regular (0)', function () {
    $scores = $this->svc->calcularScores(['cnd_federal' => ['status' => 'Negativa']]);
    expect($scores['cnd_federal'])->toBe(0);
});

it('cnd_federal positiva pura e irregular (penalidade 70)', function () {
    $scores = $this->svc->calcularScores(['cnd_federal' => ['status' => 'Positiva']]);
    expect($scores['cnd_federal'])->toBe(70);
});

it('cnd_federal INDISPONIVEL/indeterminada e nao avaliado (null), nunca irregular', function () {
    expect($this->svc->calcularScores(['cnd_federal' => ['status' => 'INDISPONIVEL']])['cnd_federal'])->toBeNull();
    expect($this->svc->calcularScores(['cnd_federal' => ['status' => 'Indeterminada']])['cnd_federal'])->toBeNull();
});

it('fgts REGULAR com conseguiu_emitir=false pontua como regular (0)', function () {
    // bug real: o FGTS volta status REGULAR + conseguiu_emitir=false; não pode virar null.
    $scores = $this->svc->calcularScores(['crf_fgts' => ['status' => 'REGULAR', 'conseguiu_emitir' => false]]);
    expect($scores['fgts'])->toBe(0);
});

it('esg e protestos foram removidos do calculo', function () {
    $scores = $this->svc->calcularScores(['situacao_cadastral' => 'ATIVA']);
    expect($scores)->not->toHaveKey('esg');
    expect($scores)->not->toHaveKey('protestos');
    expect(array_keys($this->svc->getPesos()))
        ->toBe(['cadastral', 'cnd_federal', 'cnd_estadual', 'fgts', 'trabalhista']);
});

// ---------- total com renormalizacao dinamica (so categorias presentes) ----------

it('total pondera apenas as categorias avaliadas, renormalizando os pesos', function () {
    // so cadastral=0 e cnd_federal=70 presentes; resto null -> fora do denominador
    // (0*0.15 + 70*0.20) / (0.15+0.20) = 14 / 0.35 = 40
    $scores = $this->svc->calcularScores([
        'situacao_cadastral' => 'ATIVA',
        'cnd_federal' => ['status' => 'Positiva'],
        'cnd_estadual' => ['status' => 'INDISPONIVEL'], // null, nao conta
    ]);

    expect($this->svc->calcularScoreTotal($scores))->toBe(40);
});

it('gratuito (so cadastral) usa apenas a categoria cadastral', function () {
    $scores = $this->svc->calcularScores(['situacao_cadastral' => 'ATIVA']);
    expect($this->svc->calcularScoreTotal($scores))->toBe(0);
});

it('nenhuma categoria avaliada => total null e classificacao nao_avaliado', function () {
    $scores = $this->svc->calcularScores([]);
    expect($this->svc->calcularScoreTotal($scores))->toBeNull();
    expect($this->svc->classificar(null))->toBe('nao_avaliado');
});

it('classifica faixas a partir do total', function () {
    expect($this->svc->classificar(0))->toBe('baixo');
    expect($this->svc->classificar(40))->toBe('medio');
    expect($this->svc->classificar(70))->toBe('alto');
    expect($this->svc->classificar(90))->toBe('critico');
});

// ---------- detalhar() / categoriaLabels() / hexSubscore() ----------

it('categoriaLabels cobre exatamente as chaves de getPesos', function () {
    $labels = \App\Services\RiskScoreService::categoriaLabels();
    expect(array_keys($labels))->toBe(array_keys($this->svc->getPesos()));
    expect($labels['cnd_federal'])->toBe('CND Federal');
});

it('hexSubscore mapeia faixas (replica closure do risk/show)', function () {
    expect(\App\Services\RiskScoreService::hexSubscore(null))->toBe('#9ca3af');
    expect(\App\Services\RiskScoreService::hexSubscore(0))->toBe('#047857');
    expect(\App\Services\RiskScoreService::hexSubscore(19))->toBe('#047857');
    expect(\App\Services\RiskScoreService::hexSubscore(20))->toBe('#b45309');
    expect(\App\Services\RiskScoreService::hexSubscore(50))->toBe('#ea580c');
    expect(\App\Services\RiskScoreService::hexSubscore(80))->toBe('#b91c1c');
    expect(\App\Services\RiskScoreService::hexSubscore(100))->toBe('#b91c1c');
});

it('detalhar produz linha por categoria com label, peso_pct, score, avaliado e hex', function () {
    $scores = $this->svc->calcularScores([
        'situacao_cadastral' => 'ATIVA',
        'cnd_federal' => ['status' => 'Positiva'], // irregular => 70
    ]);
    $det = $this->svc->detalhar($scores);

    expect(array_keys($det))->toBe(array_keys($this->svc->getPesos()));
    expect($det['cadastral'])->toMatchArray([
        'label' => 'Situação Cadastral', 'peso_pct' => 15, 'score' => 0, 'avaliado' => true, 'hex' => '#047857',
    ]);
    expect($det['cnd_federal'])->toMatchArray([
        'label' => 'CND Federal', 'peso_pct' => 20, 'score' => 70, 'avaliado' => true, 'hex' => '#ea580c',
    ]);
    // categorias não consultadas => null, não avaliado, hex neutro
    expect($det['fgts'])->toMatchArray(['score' => null, 'avaliado' => false, 'hex' => '#9ca3af']);
});

it('detalhar com scores vazio => 5 categorias todas não avaliadas', function () {
    $det = $this->svc->detalhar([]);
    expect($det)->toHaveCount(5);
    foreach ($det as $linha) {
        expect($linha['avaliado'])->toBeFalse();
        expect($linha['score'])->toBeNull();
        expect($linha['hex'])->toBe('#9ca3af');
    }
});
