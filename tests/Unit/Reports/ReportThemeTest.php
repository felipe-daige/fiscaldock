<?php

use App\Support\Reports\ReportTheme;

uses(Tests\TestCase::class);

it('mapeia status operacionais/cadastrais fiel ao comportamento atual', function () {
    expect(ReportTheme::statusHex('ATIVA'))->toBe('#047857');
    expect(ReportTheme::statusHex('negativa'))->toBe('#047857');
    expect(ReportTheme::statusHex('SUSPENSA'))->toBe('#b45309');
    expect(ReportTheme::statusHex('BAIXADA'))->toBe('#dc2626');
    expect(ReportTheme::statusHex('POSITIVA'))->toBe('#dc2626');
    expect(ReportTheme::statusHex(''))->toBe('#9ca3af');
    expect(ReportTheme::statusHex(null))->toBe('#9ca3af');
});

it('delega status desconhecidos ao CertidaoBadge', function () {
    // "Positiva com efeitos de negativa" = REGULAR pela semântica de certidão
    expect(ReportTheme::statusHex('Positiva com efeitos de negativa'))->toBe('#047857');
});

it('colore a classificacao de risco', function () {
    expect(ReportTheme::riscoHex('baixo'))->toBe('#047857');
    expect(ReportTheme::riscoHex('medio'))->toBe('#b45309');
    expect(ReportTheme::riscoHex('alto'))->toBe('#ea580c');
    expect(ReportTheme::riscoHex('critico'))->toBe('#dc2626');
    expect(ReportTheme::riscoHex('qualquer'))->toBe('#9ca3af');
});

it('colore regime tributario como dado cadastral informativo', function () {
    expect(ReportTheme::regimeHex('Simples Nacional'))->toBe('#0f766e');
    expect(ReportTheme::regimeHex('Lucro Presumido'))->toBe('#b45309');
    expect(ReportTheme::regimeHex('Lucro Real'))->toBe('#374151');
    expect(ReportTheme::regimeHex('Não consultado'))->toBe('#9ca3af');
});

it('logoBase64 devolve data-uri quando o arquivo existe', function () {
    $b64 = ReportTheme::logoBase64();
    expect($b64)->toBeString();
    expect($b64)->toStartWith('data:image/png;base64,');
});
