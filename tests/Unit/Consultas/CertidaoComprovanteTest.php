<?php

use App\Services\Consultas\Fontes\CndFederalFonte;
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

it('comprovante fica null quando nao ha receipt', function () {
    $raw = ['code' => 200, 'data' => [['tipo' => 'Negativa']]];

    $out = (new CndFederalFonte)->normalizar($raw, 'sucesso');

    expect($out['cnd_federal']['comprovante'])->toBeNull();
});
