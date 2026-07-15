<?php

use App\Services\Consultas\Fontes\CndFederalFonte;

it('expõe metadados da fonte CND Federal', function () {
    $f = new CndFederalFonte;
    expect($f->chave())->toBe('cnd_federal');
    expect($f->provider())->toBe('infosimples');
    expect($f->slug())->toBe('receita-federal/pgfn');
    expect($f->fornece())->toBe(['cnd_federal']);
    expect($f->custoCreditos())->toBeGreaterThan(0);
    expect($f->params(['cnpj' => '19.131.243/0001-97'])['cnpj'])->toBe('19131243000197');
    expect($f->params([])['preferencia_emissao'])->toBe('2via');
});

it('consulta pela matriz quando o alvo é filial (PGFN só emite pela matriz)', function () {
    $f = new CndFederalFonte;

    // Filiais reais que retornaram 620 ("A certidão deve ser emitida para o CNPJ da matriz").
    expect($f->params(['cnpj' => '07903169001768'])['cnpj'])->toBe('07903169000109');
    expect($f->params(['cnpj' => '15.527.906/0035-85'])['cnpj'])->toBe('15527906000136');

    // Matriz e documento não-CNPJ (CPF) passam intactos.
    expect($f->params(['cnpj' => '19131243000197'])['cnpj'])->toBe('19131243000197');
    expect($f->params(['cnpj' => '52998224725'])['cnpj'])->toBe('52998224725');
});

it('normaliza sucesso mapeando tipo→status e campos da certidão', function () {
    $raw = ['code' => 200, 'data' => [[
        'tipo' => 'Positiva com efeitos de negativa',
        'certidao_codigo' => '11AA.111A.1AA1.1A11',
        'emissao_data' => '01/06/2026',
        'validade_data' => '28/11/2026',
        'conseguiu_emitir_certidao_negativa' => true,
        'debitos_pgfn' => false,
        'debitos_rfb' => true,
        'mensagem' => 'CERTIDÃO ...',
        'situacao' => 'Válida Prorrogada até 28/11/2026',
    ]]];

    $out = (new CndFederalFonte)->normalizar($raw, 'sucesso');

    expect($out['cnd_federal']['status'])->toBe('Positiva com efeitos de negativa');
    expect($out['cnd_federal']['data_validade'])->toBe('28/11/2026');
    expect($out['cnd_federal']['conseguiu_emitir'])->toBeTrue();
    expect($out['cnd_federal']['debitos_rfb'])->toBeTrue();
    expect($out['consultas_realizadas'])->toContain('cnd_federal');
});

it('611 → INDETERMINADO preservando a mensagem (nunca irregular)', function () {
    $raw = ['code' => 611, 'code_message' => 'Não foi possível emitir.', 'errors' => ['dados insuficientes']];

    $out = (new CndFederalFonte)->normalizar($raw, 'indeterminado');

    expect($out['cnd_federal']['status'])->toBe('INDETERMINADO');
    expect($out['cnd_federal']['mensagem'])->toContain('dados insuficientes');
});

it('612 → NAO_ENCONTRADA; falha técnica não persiste bloco', function () {
    $semRegistro = (new CndFederalFonte)->normalizar(['code' => 612, 'code_message' => 'sem registro'], 'nao_encontrado');
    expect($semRegistro['cnd_federal']['status'])->toBe('NAO_ENCONTRADA');

    expect((new CndFederalFonte)->normalizar(['code' => 601], 'fatal'))->toBe([]);
    expect((new CndFederalFonte)->normalizar(['code' => 605], 'retry'))->toBe([]);
});
