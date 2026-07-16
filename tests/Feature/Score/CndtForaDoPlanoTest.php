<?php

use App\Support\Monitoramento\PlanoCatalog;

it('nenhum plano inclui cndt nas consultas e o custo em saldo NÃO muda', function () {
    $planos = PlanoCatalog::definitions();

    // custos esperados (anti-regressão de PREÇO — devem permanecer idênticos)
    $custoEsperado = [
        'gratuito' => 0.0,
        'validacao' => 3.0,
        'licitacao' => 4.0,
        'compliance' => 5.0,
        'due_diligence' => 7.0,
    ];

    foreach ($planos as $p) {
        $incluidas = (array) ($p['consultas_incluidas'] ?? []);
        expect($incluidas)->not->toContain('cndt');

        if (isset($custoEsperado[$p['codigo']])) {
            expect((float) $p['custo_creditos'])->toBe($custoEsperado[$p['codigo']]);
        }
    }
});

it('FonteRegistry não conhece mais a chave cndt', function () {
    $registry = app(\App\Services\Consultas\FonteRegistry::class);

    expect($registry->get('cndt'))->toBeNull();
});
