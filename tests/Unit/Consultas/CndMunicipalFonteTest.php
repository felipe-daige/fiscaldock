<?php

use App\Services\Consultas\Fontes\CndMunicipalFonte;

it('normaliza nome de cidade (acento/espacos)', function () {
    expect(CndMunicipalFonte::normalizarCidade('RIO DE JANEIRO'))->toBe('rio-de-janeiro');
    expect(CndMunicipalFonte::normalizarCidade('São Paulo'))->toBe('sao-paulo');
    expect(CndMunicipalFonte::normalizarCidade('SAO LUIS'))->toBe('sao-luis');
    expect(CndMunicipalFonte::normalizarCidade(''))->toBe('');
});

it('resolve slug por UF+cidade do mapa e aplica só quando coberto', function () {
    $f = new CndMunicipalFonte();
    expect($f->chave())->toBe('cnd_municipal');
    expect($f->fornece())->toBe(['cnd_municipal']);

    // coberto (mapa): Rio de Janeiro → pref/rj/rio-janeiro/cnd (slug ≠ nome!)
    $rj = ['uf' => 'RJ', 'municipio' => 'RIO DE JANEIRO', 'cnpj' => '19131243000197'];
    expect($f->aplicavelPara($rj))->toBeTrue();
    expect($f->slugPara($rj))->toBe('pref/rj/rio-janeiro/cnd');

    // não coberto: cidade fora do mapa
    $x = ['uf' => 'SP', 'municipio' => 'ITAQUAQUECETUBA', 'cnpj' => '19131243000197'];
    expect($f->aplicavelPara($x))->toBeFalse();
    expect($f->motivoIndisponivel($x))->toContain('ITAQUAQUECETUBA');

    // sem município
    expect($f->aplicavelPara(['uf' => 'SP', 'cnpj' => '1']))->toBeFalse();
});

it('normaliza sucesso da CND Municipal', function () {
    $out = (new CndMunicipalFonte())->normalizar([
        'data' => [['tipo' => 'Negativa', 'uf' => 'RJ', 'municipio' => 'Rio de Janeiro', 'validade_data' => '01/12/2026']],
    ], 'sucesso');
    expect($out['cnd_municipal']['status'])->toBe('Negativa');
    expect($out['consultas_realizadas'])->toContain('cnd_municipal');

    $ind = (new CndMunicipalFonte())->normalizar(['_motivo' => 'x'], 'nao_aplicavel');
    expect($ind['cnd_municipal']['status'])->toBe('INDISPONIVEL');
});
