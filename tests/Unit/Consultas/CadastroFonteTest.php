<?php

use App\Services\Consultas\Fontes\CadastroFonte;

it('normaliza o raw da minhareceita para o shape achatado de prod', function () {
    $raw = json_decode(file_get_contents(base_path('tests/Fixtures/Consultas/minhareceita-19131243000197.json')), true);

    $out = (new CadastroFonte())->normalizar($raw);

    foreach (['razao_social', 'situacao_cadastral', 'situacao_cadastral_codigo', 'endereco', 'qsa', 'cnaes', 'simples_nacional', 'mei', 'consultas_realizadas'] as $k) {
        expect($out)->toHaveKey($k);
    }
    expect($out['razao_social'])->toBe('OPEN KNOWLEDGE BRASIL');
    expect($out['situacao_cadastral'])->toBe('ATIVA');
    expect($out['situacao_cadastral_codigo'])->toBe(2);
    expect($out['endereco'])->toHaveKeys(['uf', 'cep', 'municipio', 'logradouro', 'numero', 'bairro']);
    expect($out['endereco']['uf'])->toBe('SP');
    expect($out['qsa'])->toBeArray()->not->toBeEmpty();
    expect($out['qsa'][0])->toHaveKeys(['nome', 'cpf_cnpj', 'data_entrada', 'qualificacao']);
    expect($out['cnaes'][0]['principal'])->toBeTrue();
    expect($out['consultas_realizadas'])->toContain('situacao_cadastral');
});

it('expõe chave/provider/custo da fonte cadastro', function () {
    $f = new CadastroFonte();
    expect($f->chave())->toBe('cadastro');
    expect($f->provider())->toBe('minhareceita');
    expect($f->custoCreditos())->toBe(0);
    expect($f->params(['cnpj' => '19.131.243/0001-97'])['cnpj'])->toBe('19131243000197');
});
