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

it('município requer_im: sem IM no perfil → não aplicável (pula a chamada billable)', function () {
    $f = new CndMunicipalFonte;
    $rp = ['uf' => 'SP', 'municipio' => 'RIBEIRAO PRETO', 'cnpj' => '56786908000127'];

    // Sem IM → não consulta (evita 606 billable), com motivo acionável.
    expect($f->aplicavelPara($rp))->toBeFalse();
    expect($f->motivoIndisponivel($rp))->toContain('inscrição municipal');

    // Com IM → volta a ser aplicável.
    $rpComIm = $rp + ['inscricao_municipal' => '990141600'];
    expect($f->aplicavelPara($rpComIm))->toBeTrue();
});

it('params envia inscricao_municipal SÓ em município requer_im', function () {
    $f = new CndMunicipalFonte;
    $im = '990141600';

    // requer_im (Ribeirão Preto) com IM → manda.
    $rp = $f->params(['uf' => 'SP', 'municipio' => 'RIBEIRAO PRETO', 'cnpj' => '56786908000127', 'inscricao_municipal' => $im]);
    expect($rp['inscricao_municipal'])->toBe($im);

    // NÃO requer_im (Goiânia roda por CNPJ) mesmo com IM no alvo → NÃO manda (evita 607).
    $go = $f->params(['uf' => 'GO', 'municipio' => 'GOIANIA', 'cnpj' => '56786908000127', 'inscricao_municipal' => $im]);
    expect($go)->not->toHaveKey('inscricao_municipal');
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

it('code 606 (prefeitura exige inscrição municipal) vira INDISPONIVEL, não erro', function () {
    // Prefeitura coberta pelo InfoSimples mas cujo endpoint pede inscricao_municipal (que não
    // temos) → code 606. Deve degradar pra INDISPONIVEL com motivo honesto, e NÃO devolver
    // blob vazio (que viraria _fontes_erro/erro vermelho). Repro: Ribeirão Preto/SP, lote 258.
    $out = (new CndMunicipalFonte)->normalizar([
        'code' => 606,
        'code_message' => "Parâmetros obrigatórios não foram enviados. O parâmetro 'inscricao_municipal' é obrigatório.",
    ], 'fatal');

    expect($out)->not->toBeEmpty();
    expect($out['cnd_municipal']['status'])->toBe('INDISPONIVEL');
    expect($out['cnd_municipal']['mensagem'])->toContain('inscrição municipal');
    expect($out['consultas_realizadas'])->toContain('cnd_municipal');
});

it('fatal que não é 606 continua sem bloco (blob vazio → _fontes_erro)', function () {
    // Outros fatais (601/607/…) seguem o comportamento base: nada persistido, marcado como
    // erro de integração pelo job. Só o 606 municipal é reinterpretado como cobertura.
    $out = (new CndMunicipalFonte)->normalizar(['code' => 607, 'code_message' => 'x'], 'fatal');
    expect($out)->toBe([]);
});

it('colhe a inscrição municipal da resposta da CND (para gravar no perfil)', function () {
    $f = new CndMunicipalFonte;

    // Dialeto direto
    $a = $f->normalizar(['data' => [[
        'tipo' => 'Negativa', 'uf' => 'GO', 'municipio' => 'Goiânia',
        'inscricao_municipal' => '123456-7',
    ]]], 'sucesso')['cnd_municipal'];
    expect($a['inscricao_municipal'])->toBe('123456-7');

    // Dialeto normalizado
    $b = $f->normalizar(['data' => [[
        'tipo' => 'Negativa', 'normalizado_inscricao_municipal' => '990141600',
    ]]], 'sucesso')['cnd_municipal'];
    expect($b['inscricao_municipal'])->toBe('990141600');

    // Ausente → null (não quebra)
    $c = $f->normalizar(['data' => [['tipo' => 'Negativa']]], 'sucesso')['cnd_municipal'];
    expect($c['inscricao_municipal'])->toBeNull();

    // Fallback genérico `inscricao` NÃO é colhido (poderia ser IE/protocolo e envenenar o perfil).
    $d = $f->normalizar(['data' => [['tipo' => 'Negativa', 'inscricao' => 'AMBIGUO-999']]], 'sucesso')['cnd_municipal'];
    expect($d['inscricao_municipal'])->toBeNull();
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
