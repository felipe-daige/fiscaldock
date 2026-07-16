<?php

it('nenhuma ref ATIVA de cndt/trabalhista no resultado/PDF/views de consulta', function () {
    $alvos = [
        base_path('app/Services/Consultas/ResultadoDetalhePresenter.php'),
        base_path('app/Models/ConsultaResultado.php'),
        base_path('app/Services/ConsultaReportService.php'),
        base_path('app/Http/Controllers/Dashboard/ConsultaController.php'),
        base_path('resources/views/reports/consulta-lote/_cnpj.blade.php'),
        base_path('resources/views/autenticado/consulta/nova.blade.php'),
        base_path('resources/views/autenticado/monitoramento/historico.blade.php'),
        base_path('resources/views/autenticado/risk/index.blade.php'),
        base_path('public/js/consulta-lote.js'),
    ];

    foreach ($alvos as $f) {
        $conteudo = file_get_contents($f);
        expect(preg_match('/cndt|trabalhista/i', $conteudo))->toBe(0, "cndt/trabalhista ainda em {$f}");
    }
});
