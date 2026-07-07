<?php

use App\Services\Consultas\Fontes\CndEstadualFonte;
use App\Services\Consultas\Fontes\SintegraFonte;

it('CND Estadual: metadados + uf no param + sucesso/611', function () {
    $f = new CndEstadualFonte();
    expect($f->chave())->toBe('cnd_estadual');
    expect($f->slug())->toBe('sefaz/certidao-debitos');
    expect($f->params(['cnpj' => '19131243000197', 'uf' => 'sp'])['uf'])->toBe('SP');

    $ok = $f->normalizar(['data' => [['tipo' => 'Negativa', 'uf' => 'SP', 'validade_data' => '01/12/2026']]], 'sucesso');
    expect($ok['cnd_estadual']['status'])->toBe('Negativa');
    expect($ok['cnd_estadual']['uf'])->toBe('SP');

    expect($f->normalizar(['code' => 611], 'indeterminado')['cnd_estadual']['status'])->toBe('INDETERMINADO');
});

it('certidão sem campo tipo deriva status de conseguiu_emitir (CNDT/Estadual)', function () {
    // resposta real de CNDT/Estadual não traz `tipo` — deriva: emitiu negativa = Negativa
    $f = new CndEstadualFonte();
    expect($f->normalizar(['data' => [['conseguiu_emitir_certidao_negativa' => true, 'mensagem' => 'CERTIDÃO NEGATIVA']]], 'sucesso')['cnd_estadual']['status'])->toBe('Negativa');
    expect($f->normalizar(['data' => [['conseguiu_emitir_certidao_negativa' => false]]], 'sucesso')['cnd_estadual']['status'])->toBe('Positiva');
    // se vier `tipo`, usa ele
    expect($f->normalizar(['data' => [['tipo' => 'Positiva com efeitos de negativa']]], 'sucesso')['cnd_estadual']['status'])->toBe('Positiva com efeitos de negativa');
});

it('CND Estadual: cobertura por UF (aplicavelPara) + INDISPONIVEL fora da cobertura', function () {
    config()->set('consultas.cnd_estadual.ufs_cobertas', ['SP', 'RJ']);
    $f = new CndEstadualFonte();

    expect($f->aplicavelPara(['uf' => 'SP']))->toBeTrue();
    expect($f->aplicavelPara(['uf' => 'sp']))->toBeTrue(); // normaliza maiúsculas
    expect($f->aplicavelPara(['uf' => 'AC']))->toBeFalse(); // fora da cobertura
    expect($f->aplicavelPara(['uf' => '']))->toBeFalse();   // sem UF
    expect($f->aplicavelPara([]))->toBeFalse();

    $out = $f->normalizar([], 'nao_aplicavel');
    expect($out['cnd_estadual']['status'])->toBe('INDISPONIVEL');
});

it('SINTEGRA: cadastral (IE/situação)', function () {
    $f = new SintegraFonte();
    expect($f->chave())->toBe('sintegra');
    expect($f->slug())->toBe('sintegra/unificada');

    $ok = $f->normalizar(['data' => [[
        'uf' => 'SP', 'inscricao_estadual' => '111.111.111.111', 'situacao' => 'Habilitado',
    ]]], 'sucesso');
    expect($ok['sintegra']['inscricao_estadual'])->toBe('111.111.111.111');
    expect($ok['sintegra']['situacao'])->toBe('Habilitado');
    expect($ok['consultas_realizadas'])->toContain('sintegra');
});
