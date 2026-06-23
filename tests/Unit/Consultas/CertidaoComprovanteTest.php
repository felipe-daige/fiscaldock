<?php

use App\Services\Consultas\Fontes\CguCncFonte;
use App\Services\Consultas\Fontes\CndFederalFonte;
use App\Services\Consultas\Fontes\CnjImprobidadeFonte;
use App\Services\Consultas\Fontes\SintegraFonte;

it('captura comprovante (site_receipt em data[0]) na CND Federal', function () {
    $raw = ['code' => 200, 'data' => [['tipo' => 'Negativa', 'site_receipt' => 'https://ex.com/cnd.pdf']]];

    $out = (new CndFederalFonte)->normalizar($raw, 'sucesso');

    expect($out['cnd_federal']['comprovante'])->toBe('https://ex.com/cnd.pdf');
});

it('captura comprovante (site_receipts top-level) na CND Federal', function () {
    $raw = ['code' => 200, 'data' => [['tipo' => 'Negativa']], 'site_receipts' => ['https://ex.com/top.pdf']];

    $out = (new CndFederalFonte)->normalizar($raw, 'sucesso');

    expect($out['cnd_federal']['comprovante'])->toBe('https://ex.com/top.pdf');
});

it('captura comprovante no SINTEGRA', function () {
    $raw = ['code' => 200, 'data' => [['uf' => 'SP', 'situacao' => 'Habilitado', 'site_receipt' => 'https://ex.com/sint.pdf']]];

    $out = (new SintegraFonte)->normalizar($raw, 'sucesso');

    expect($out['sintegra']['comprovante'])->toBe('https://ex.com/sint.pdf');
});

it('captura comprovante (site_receipts top-level) na CGU CNC', function () {
    $raw = ['code' => 200, 'data' => [['conseguiu_emitir_certidao_negativa' => true]], 'site_receipts' => ['https://ex.com/cgu.pdf']];

    $out = (new CguCncFonte)->normalizar($raw, 'sucesso');

    expect($out['cgu_cnc']['comprovante'])->toBe('https://ex.com/cgu.pdf');
});

it('captura comprovante (site_receipts top-level) na CNJ Improbidade', function () {
    $raw = ['code' => 200, 'data' => [['certidao_negativa' => true]], 'site_receipts' => ['https://ex.com/cnj.pdf']];

    $out = (new CnjImprobidadeFonte)->normalizar($raw, 'sucesso');

    expect($out['cnj_improbidade']['comprovante'])->toBe('https://ex.com/cnj.pdf');
});

it('comprovante fica null quando nao ha receipt', function () {
    $raw = ['code' => 200, 'data' => [['tipo' => 'Negativa']]];

    $out = (new CndFederalFonte)->normalizar($raw, 'sucesso');

    expect($out['cnd_federal']['comprovante'])->toBeNull();
});
