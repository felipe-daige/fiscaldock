<?php

it('renderiza detalhamento web com headline, categorias, peso e legenda', function () {
    $det = [
        'cadastral' => ['label' => 'Situação Cadastral', 'peso_pct' => 15, 'score' => 0, 'avaliado' => true, 'hex' => '#047857'],
        'cnd_federal' => ['label' => 'CND Federal', 'peso_pct' => 20, 'score' => 70, 'avaliado' => true, 'hex' => '#dc2626'],
        'fgts' => ['label' => 'FGTS/CRF', 'peso_pct' => 10, 'score' => null, 'avaliado' => false, 'hex' => '#9ca3af'],
    ];
    $html = view('autenticado.partials._score-detalhamento', [
        'detalhamento' => $det, 'scoreTotal' => 14, 'classificacao' => 'baixo', 'comHeadline' => true,
    ])->render();

    expect($html)->toContain('14')                       // headline
        ->and($html)->toContain('CND Federal')
        ->and($html)->toContain('Peso efetivo: 20,0%')
        ->and($html)->toContain('background-color: #dc2626')
        ->and($html)->toContain('Regular')               // cadastral score 0 = regular (não fica barra vazia)
        ->and($html)->toContain('width: 100%')           // regular preenche 100% (verde), não 0%
        ->and($html)->toContain('Não avaliado')          // fgts null
        ->and($html)->toContain('Legenda');               // seção "Categorias em breve" removida com ESG/Protestos
});

it('irregular preenche a barra PELO RISCO: baixada (100) = barra cheia, suspensa (50) = metade', function () {
    // Regressão: fórmula antiga (100 − score) deixava o pior caso (baixada, score 100)
    // com barra VAZIA — lia-se como "sem dado".
    $det = [
        'cadastral' => ['label' => 'Situação Cadastral', 'peso_pct' => 15, 'score' => 100, 'avaliado' => true, 'hex' => '#b91c1c'],
        'cnd_federal' => ['label' => 'CND Federal', 'peso_pct' => 20, 'score' => 50, 'avaliado' => true, 'hex' => '#dc2626'],
    ];
    $html = view('autenticado.partials._score-detalhamento', [
        'detalhamento' => $det, 'scoreTotal' => 100, 'classificacao' => 'critico', 'comHeadline' => true,
    ])->render();

    expect($html)->toContain('width: 100%; background-color: #b91c1c')  // baixada: cheia vermelha
        ->and($html)->toContain('width: 50%; background-color: #dc2626') // meia barra vermelha
        ->and($html)->not->toContain('width: 0%');                       // nunca barra vazia em avaliado
});

it('score 15 com piso alto usa vermelho e explica pesos efetivos e contribuição', function () {
    $service = app(\App\Services\RiskScoreService::class);
    $scores = [
        'cadastral' => 0,
        'cnd_federal' => 0,
        'cnd_estadual' => 70,
        'fgts' => 0,
        'trabalhista' => 0,
    ];

    $html = view('autenticado.partials._score-detalhamento', [
        'detalhamento' => $service->detalhar($scores),
        'scoreTotal' => $service->calcularScoreTotal($scores),
        'classificacao' => $service->classificarComCobertura($scores),
        'comHeadline' => true,
    ])->render();

    expect($html)->toContain('style="color: #dc2626">15')
        ->and($html)->toContain('background-color: #dc2626">Alto Risco')
        ->and($html)->toContain('width: 15%; background-color: #dc2626')
        ->and($html)->toContain('Classificação elevada por irregularidade conhecida')
        ->and($html)->toContain('CND Estadual impõe o piso de Alto Risco')
        ->and($html)->toContain('Peso efetivo: 21,4%')
        ->and($html)->toContain('base 15,0%')
        ->and($html)->toContain('15,0 pt');
});

it('sem headline (risk/show) não exibe o número grande do total', function () {
    $det = ['cadastral' => ['label' => 'Situação Cadastral', 'peso_pct' => 15, 'score' => 0, 'avaliado' => true, 'hex' => '#047857']];
    $html = view('autenticado.partials._score-detalhamento', [
        'detalhamento' => $det, 'scoreTotal' => 0, 'classificacao' => 'baixo', 'comHeadline' => false,
    ])->render();
    expect($html)->not->toContain('score-headline-total');
});

it('empty-state quando não há nada avaliado e total null', function () {
    $html = view('autenticado.partials._score-detalhamento', [
        'detalhamento' => [], 'scoreTotal' => null, 'classificacao' => 'nao_avaliado', 'comHeadline' => true,
    ])->render();
    expect($html)->toContain('Score não calculado');
});
