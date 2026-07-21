<?php

use App\Services\Efd\Sped\ContextWalker;
use App\Services\Efd\Sped\SpedParser;

/**
 * Golden test do tokenizer L1 contra o arquivo SPED REAL UTIDA jan/2026 — o mesmo
 * que expôs o bug do merge n8n (NFC-e sem COD_PART droppada: 1 de 1433 sobreviveu).
 * Critério de aceite da fase F1 (motor-laravel.md §10.9).
 *
 * O arquivo é dado fiscal real de cliente (gitignored em /tests/Fixtures/sped/).
 * Ausente → o teste é pulado; presente (host de dev) → roda e trava a contagem.
 */
if (! function_exists('spedUtidaJan')) {
    function spedUtidaJan(): string
    {
        $path = __DIR__.'/../../Fixtures/sped/UTIDA-jan2026-somente-dados.txt';
        if (! is_file($path)) {
            test()->markTestSkipped("Fixture SPED real (gitignored) ausente: {$path}");
        }

        return (string) file_get_contents($path);
    }
}

it('tokeniza exatamente 1433 C100 (não 1 — a regressão do n8n)', function () {
    $c100 = 0;
    foreach ((new SpedParser)->stream(spedUtidaJan()) as $rec) {
        if ($rec->reg === 'C100') {
            $c100++;
        }
    }

    expect($c100)->toBe(1433);
});

it('reproduz a contagem dos registros-chave do arquivo real', function () {
    $tally = [];
    foreach ((new SpedParser)->stream(spedUtidaJan()) as $rec) {
        $tally[$rec->reg] = ($tally[$rec->reg] ?? 0) + 1;
    }

    expect($tally['C100'] ?? 0)->toBe(1433)
        ->and($tally['C190'] ?? 0)->toBe(2167)
        ->and($tally['C170'] ?? 0)->toBe(1)
        ->and($tally['0150'] ?? 0)->toBe(1)
        ->and($tally['0200'] ?? 0)->toBe(1);
});

it('mantém as NFC-e (modelo 65) com COD_PART vazio — nunca dropa', function () {
    $nfceSemParte = 0;
    foreach ((new SpedParser)->stream(spedUtidaJan()) as $rec) {
        if ($rec->reg === 'C100' && $rec->campo(5) === '65' && $rec->campo(4) === '') {
            $nfceSemParte++;
        }
    }

    // Exatamente a classe de linha que o merge do n8n descartava (COD_PART vazio).
    expect($nfceSemParte)->toBeGreaterThan(0);
});

it('ContextWalker dá pai C100 (chave de 44) a toda C170/C190 do arquivo real', function () {
    $filhos = 0;
    foreach ((new ContextWalker)->walk((new SpedParser)->stream(spedUtidaJan())) as [$rec, $pai]) {
        if (in_array($rec->reg, ['C170', 'C190'], true)) {
            $filhos++;
            expect($pai)->not->toBeNull()
                ->and($pai->reg)->toBe('C100')
                ->and(strlen((string) $pai->chave))->toBe(44);
        }
    }

    // 2167 C190 + 1 C170, todos parenteados a um C100.
    expect($filhos)->toBe(2168);
});
