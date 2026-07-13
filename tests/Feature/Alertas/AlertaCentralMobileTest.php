<?php

it('mantém os contratos responsivos da Central de Alertas', function () {
    $html = view('autenticado.alertas.central', [
        'clientes' => collect(),
        'resumo' => [
            'total_ativos' => 7,
            'por_severidade' => ['alta' => 4, 'media' => 2, 'baixa' => 1],
        ],
    ])->render();

    expect($html)
        ->toContain('alertas-header-actions')
        ->toContain('alertas-header-action')
        ->toContain('Deslize para ver mais categorias')
        ->toContain('id="alertas-filtros-toggle"')
        ->toContain('aria-controls="alertas-filtros-conteudo"')
        ->toContain('data-mobile-filters-native')
        ->toContain('alertas-card-actions')
        ->toContain('alerta-action-label')
        ->toContain('alertas-exposicao-grid')
        ->toContain('#alertas-paginacao .alerta-page-btn')
        ->toContain('alertas-bulk-actions')
        ->toContain('padding-bottom: max(0.75rem, env(safe-area-inset-bottom))')
        ->toContain('@media (max-width: 359px)');
});

it('mantém controles mobile com áreas de toque e sem rótulos ambíguos', function () {
    $html = view('autenticado.alertas.central', [
        'clientes' => collect(),
        'resumo' => [],
    ])->render();

    expect($html)
        ->toContain('min-height: 44px')
        ->toContain('min-height: 42px')
        ->toContain("ids.length === 1 ? 'selecionado' : 'selecionados'")
        ->toContain('<span class="sm:hidden">Atualizar</span>')
        ->not->toContain('selecionado(s)');
});
