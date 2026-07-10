<?php

use App\Support\PdfReport;

it('logoDataUri devolve data-uri png base64', function () {
    expect(PdfReport::logoDataUri())->toStartWith('data:image/png;base64,');
});

function pdfReportMediaBox(string $bytes): array
{
    preg_match('/\/MediaBox\s*\[\s*0(?:\.0+)?\s+0(?:\.0+)?\s+([0-9.]+)\s+([0-9.]+)\s*\]/', $bytes, $m);

    return [(float) ($m[1] ?? 0), (float) ($m[2] ?? 0)];
}

it('render devolve PDF A4 retrato e gera bytes %PDF', function () {
    $pdf = PdfReport::render('reports.layout');
    $bytes = $pdf->output();
    [$largura, $altura] = pdfReportMediaBox($bytes);

    expect($pdf)->toBeInstanceOf(\Barryvdh\DomPDF\PDF::class);
    expect(substr($bytes, 0, 4))->toBe('%PDF');
    expect($largura)->toBeGreaterThan(590.0)
        ->toBeLessThan(600.0);
    expect($altura)->toBeGreaterThan(835.0)
        ->toBeLessThan(850.0);
    expect($largura)->toBeLessThan($altura);
});

it('ignora orientacao legada e mantem A4 retrato', function () {
    $bytes = PdfReport::render('reports.layout', [], 'landscape')->output();
    [$largura, $altura] = pdfReportMediaBox($bytes);

    expect($largura)->toBeLessThan($altura);
});

it('hashDocumento e deterministico e curto por identificadores', function () {
    $a = PdfReport::hashDocumento('lote', 13, 1700000000);
    $b = PdfReport::hashDocumento('lote', 13, 1700000000);
    $c = PdfReport::hashDocumento('lote', 14, 1700000000);
    expect($a)->toBe($b)                 // mesmo doc => mesmo hash
        ->not->toBe($c)                  // doc diferente => hash diferente
        ->toMatch('/^[0-9A-F]{12}$/');   // 12 hex maiusculo
});

it('emissor e o dominio da marca', function () {
    expect(PdfReport::emissor())->toBe('fiscaldock.com.br');
});
