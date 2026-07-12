<?php

use App\Support\CndFederal;

it('marca INDETERMINADO pelo status', function () {
    $r = CndFederal::analisar(['status' => 'INDETERMINADO', 'mensagem' => 'Sem dados.']);
    expect($r['indeterminado'])->toBeTrue();
    expect($r['label'])->toBe('Indeterminada');
    expect($r['hex'])->toBe('#b45309');
});

it('marca INDETERMINADO quando conseguiu_emitir e false sem status', function () {
    $r = CndFederal::analisar(['conseguiu_emitir' => false, 'mensagem' => 'Falhou.']);
    expect($r['indeterminado'])->toBeTrue();
});

it('normaliza espaco duplo e espaco antes de pontuacao no motivo', function () {
    $r = CndFederal::analisar([
        'status' => 'INDETERMINADO',
        'mensagem' => 'Inscrição no CNPJ 72.983.711/0001-34 Inapta  - Omissão de declarações.',
    ]);
    expect($r['motivo'])->toBe('Inscrição no CNPJ 72.983.711/0001-34 Inapta - Omissão de declarações.');
});

it('usa errors[0] como fallback quando nao ha mensagem', function () {
    $r = CndFederal::analisar([
        'status' => 'INDETERMINADO',
        'errors' => ['Certidão não emitida pela internet.'],
    ]);
    expect($r['motivo'])->toBe('Certidão não emitida pela internet.');
});

it('retorna indeterminado=false para status regular', function () {
    $r = CndFederal::analisar(['status' => 'NEGATIVA']);
    expect($r['indeterminado'])->toBeFalse();
    expect($r['label'])->toBeNull();
    expect($r['motivo'])->toBeNull();
});

it('status definitivo prevalece sobre conseguiu_emitir=false', function () {
    // FGTS volta REGULAR mas com conseguiu_emitir=false — o status definitivo manda,
    // não é indeterminado (senão o FGTS regular não pontuaria no score).
    expect(CndFederal::analisar(['status' => 'REGULAR', 'conseguiu_emitir' => false])['indeterminado'])->toBeFalse();
    expect(CndFederal::analisar(['status' => 'NEGATIVA', 'conseguiu_emitir' => false])['indeterminado'])->toBeFalse();
});

it('certidao Positiva EMITIDA (nº/data presentes) e conclusiva, nao indeterminada', function () {
    expect(CndFederal::analisar([
        'status' => 'Positiva', 'conseguiu_emitir' => false,
        'certidao_codigo' => '123456', 'emissao_data' => '02/07/2026',
    ])['indeterminado'])->toBeFalse();

    expect(CndFederal::analisar([
        'status' => 'Positiva', 'conseguiu_emitir' => false, 'emissao_data' => '02/07/2026',
    ])['indeterminado'])->toBeFalse();
});

it('SEFAZ sem emissao (Positiva derivada, sem nº/data) vira indeterminada', function () {
    // Caso real (CND Estadual MS/SP): fonte não emite pela internet, manda "procure a
    // Agência Fazendária" — sem documento emitido não há pendência comprovada.
    $r = CndFederal::analisar([
        'uf' => null, 'status' => 'Positiva', 'conseguiu_emitir' => false,
        'certidao_codigo' => null, 'emissao_data' => null,
        'mensagem' => 'Não foi possível a emissão da sua Certidão Negativa.',
    ]);
    expect($r['indeterminado'])->toBeTrue();
    expect($r['motivo'])->toBe('Não foi possível a emissão da sua Certidão Negativa.');
});

it('trata null e string sem quebrar', function () {
    expect(CndFederal::analisar(null)['indeterminado'])->toBeFalse();
    expect(CndFederal::analisar('regular')['indeterminado'])->toBeFalse();
});
