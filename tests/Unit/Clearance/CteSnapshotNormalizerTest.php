<?php

use App\Services\Clearance\Sefaz\CteSnapshotNormalizer;

function cteFixture(string $nome): array
{
    return json_decode(file_get_contents(__DIR__."/../../Fixtures/Clearance/{$nome}.json"), true);
}

$chaveCte = '51240146970030000474570010000016901000685610';

it('200 CT-e: status AUTORIZADA, colunas CT-e normalizadas, persistível', function () use ($chaveCte) {
    $s = (new CteSnapshotNormalizer)->normalizar(cteFixture('cte_200'), 'sucesso', $chaveCte, true);

    expect($s->tipoDocumento)->toBe('CTE');
    expect($s->tabela())->toBe('cte_consultas');
    expect($s->status)->toBe('AUTORIZADA');
    expect($s->colunas['valor_prestacao'])->toBe(1500.50);
    expect($s->colunas['valor_carga'])->toBe(80000.00);
    expect($s->colunas['cfop'])->toBe('6352');
    expect($s->colunas['modal'])->toBe('Rodoviário');
    expect($s->colunas['tipo_servico'])->toBe('Normal');
    expect($s->colunas['uf_inicio'])->toBe('MG');
    expect($s->colunas['uf_fim'])->toBe('SP');
    expect($s->colunas['emit_cnpj'])->toBe('46970030000474');
    expect($s->colunas['tomador_cnpj'])->toBe('11111111000191');
    expect($s->colunas['remet_cnpj'])->toBe('22222222000191');
    expect($s->colunas['dest_cnpj'])->toBe('33333333000191');
    expect($s->colunas['expedidor_cnpj'])->toBe('44444444000191');
    expect($s->colunas['recebedor_cnpj'])->toBe('55555555000191');
    expect($s->colunas['nfes_referenciadas_count'])->toBe(1);
    expect($s->colunas['data_emissao'])->toBe('2024-03-15T09:12:00-03:00');
    expect($s->colunas['cte_completa'])->toBeFalse();
    expect($s->persistivel)->toBeTrue();
    expect($s->estornavel)->toBeFalse();
});

it('612 CT-e: NAO_ENCONTRADA, persistível, sem estorno', function () use ($chaveCte) {
    $s = (new CteSnapshotNormalizer)->normalizar(cteFixture('cte_612'), 'nao_encontrado', $chaveCte, true);

    expect($s->status)->toBe('NAO_ENCONTRADA');
    expect($s->tipoDocumento)->toBe('CTE');
    expect($s->persistivel)->toBeTrue();
    expect($s->estornavel)->toBeFalse();
});

it('fatal CT-e: ERRO_INTEGRACAO, não persistível, estorna', function () use ($chaveCte) {
    $s = (new CteSnapshotNormalizer)->normalizar(['code' => 601, 'header' => ['billable' => false]], 'fatal', $chaveCte, false);

    expect($s->status)->toBe('ERRO_INTEGRACAO');
    expect($s->persistivel)->toBeFalse();
    expect($s->estornavel)->toBeTrue();
});
