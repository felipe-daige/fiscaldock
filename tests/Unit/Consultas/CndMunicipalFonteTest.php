<?php

use App\Services\Consultas\Fontes\CndMunicipalFonte;

it('normaliza nome de cidade (acento/espacos)', function () {
    expect(CndMunicipalFonte::normalizarCidade('RIO DE JANEIRO'))->toBe('rio-de-janeiro');
    expect(CndMunicipalFonte::normalizarCidade('São Paulo'))->toBe('sao-paulo');
    expect(CndMunicipalFonte::normalizarCidade('SAO LUIS'))->toBe('sao-luis');
    expect(CndMunicipalFonte::normalizarCidade(''))->toBe('');
});

it('resolve slug por UF+cidade do mapa e aplica só quando coberto', function () {
    $f = new CndMunicipalFonte;
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
    $out = (new CndMunicipalFonte)->normalizar([
        'data' => [['tipo' => 'Negativa', 'uf' => 'RJ', 'municipio' => 'Rio de Janeiro', 'validade_data' => '01/12/2026']],
    ], 'sucesso');
    expect($out['cnd_municipal']['status'])->toBe('Negativa');
    expect($out['consultas_realizadas'])->toContain('cnd_municipal');

    $ind = (new CndMunicipalFonte)->normalizar(['_motivo' => 'x'], 'nao_aplicavel');
    expect($ind['cnd_municipal']['status'])->toBe('INDISPONIVEL');
});

it('coalesce dialetos de campo por prefeitura (validado e2e 2026-07-11)', function () {
    $f = new CndMunicipalFonte;

    // Dialeto Chapadão do Sul/MS: data_emissao + validade + codigo_controle_certidao + endereco.uf
    $chapadao = $f->normalizar(['data' => [[
        'conseguiu_emitir_certidao_negativa' => true,
        'codigo_controle_certidao' => 'E91C58468E493404',
        'data_emissao' => '10/07/2026',
        'validade' => '09/08/2026',
        'endereco' => ['cidade' => 'CHAPADÃO DO SUL', 'uf' => 'MS'],
    ]]], 'sucesso')['cnd_municipal'];
    expect($chapadao['status'])->toBe('Negativa');
    expect($chapadao['certidao_codigo'])->toBe('E91C58468E493404');
    expect($chapadao['emissao_data'])->toBe('10/07/2026');
    expect($chapadao['data_validade'])->toBe('09/08/2026');
    expect($chapadao['uf'])->toBe('MS');
    expect($chapadao['municipio'])->toBe('CHAPADÃO DO SUL');

    // Dialeto Goiânia/GO: emissao_data + validade_data + numero_certidao (sem endereço)
    $goiania = $f->normalizar(['data' => [[
        'conseguiu_emitir_certidao_negativa' => false,
        'numero_certidao' => '1.675.240-9',
        'emissao_data' => '10/07/2026',
        'validade_data' => '07/10/2026',
    ]]], 'sucesso')['cnd_municipal'];
    expect($goiania['status'])->toBe('Positiva');
    expect($goiania['certidao_codigo'])->toBe('1.675.240-9');
    expect($goiania['emissao_data'])->toBe('10/07/2026');
    expect($goiania['data_validade'])->toBe('07/10/2026');
});
