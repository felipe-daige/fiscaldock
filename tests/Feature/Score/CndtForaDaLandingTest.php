<?php

it('nenhuma ref de cndt/trabalhista na landing, catálogo comercial e blog', function () {
    $alvos = [
        base_path('app/Services/PricingCatalogService.php'),
        base_path('app/Http/Controllers/Landing/LandingPageController.php'),
        base_path('app/Support/Landing/BlogPostCatalog.php'),
        base_path('public/js/inicio.js'),
        base_path('resources/views/landing_page/layouts/public.blade.php'),
        base_path('resources/views/landing_page/llms.blade.php'),
        base_path('resources/views/landing_page/paginas/inicio.blade.php'),
        base_path('resources/views/landing_page/paginas/duvidas.blade.php'),
        base_path('resources/views/landing_page/paginas/precos.blade.php'),
        base_path('resources/views/landing_page/solucoes/index.blade.php'),
    ];

    foreach ($alvos as $f) {
        $conteudo = file_get_contents($f);
        expect(preg_match('/cndt|trabalhista/i', $conteudo))->toBe(0, "cndt/trabalhista ainda em {$f}");
    }
});

it('remoção do CNDT não mexeu em nenhum preço do catálogo comercial', function () {
    $svc = app(\App\Services\PricingCatalogService::class);

    // fontes de compliance sem cndt
    $slugs = array_column($svc->getComplianceSources(), 'slug');
    expect($slugs)->not->toContain('cndt');

    // preço dos produtos de consulta idêntico (anti-regressão de preço, sem override admin)
    foreach (['validacao' => 3.0, 'licitacao' => 4.0, 'compliance' => 5.0] as $codigo => $custo) {
        $plano = \App\Models\MonitoramentoPlano::where('codigo', $codigo)->first();
        if ($plano) {
            expect((float) $plano->custo_creditos)->toBe($custo);
        }
    }

    // catálogo público não expõe mais cndt em nenhum plano
    foreach ($svc->getProductCatalog() as $produto) {
        expect((array) $produto['consultas_incluidas'])->not->toContain('cndt');
    }
});
