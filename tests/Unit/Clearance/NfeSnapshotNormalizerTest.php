<?php

use App\Services\Clearance\Sefaz\NfeSnapshotNormalizer;

function nfeFixture(string $nome): array
{
    return json_decode(file_get_contents(__DIR__."/../../Fixtures/Clearance/{$nome}.json"), true);
}

$chave = '50240243648971004576550010001117211468024730';

it('200 autorizada: status AUTORIZADA, valor e data normalizados, persistível, não estorna', function () use ($chave) {
    $s = (new NfeSnapshotNormalizer)->normalizar(nfeFixture('nfe_200_autorizada'), 'sucesso', $chave, true);

    expect($s->status)->toBe('AUTORIZADA');
    expect($s->tipoDocumento)->toBe('NFE');
    expect($s->colunas['valor_total'])->toBe(51.11);
    expect($s->colunas['data_emissao'])->toBe('2024-02-29T14:03:39-04:00');
    expect($s->colunas['emit_cnpj'])->toBe('43648971004576');
    expect($s->colunas['dest_cnpj'])->toBe('00000165000193');
    expect($s->persistivel)->toBeTrue();
    expect($s->estornavel)->toBeFalse();
});

it('200 cancelada via evento vence situação defasada', function () use ($chave) {
    $s = (new NfeSnapshotNormalizer)->normalizar(nfeFixture('nfe_200_cancelada'), 'sucesso', $chave, true);
    expect($s->status)->toBe('CANCELADA');
    expect($s->persistivel)->toBeTrue();
});

it('611 indeterminado: persistível, sem estorno, preserva errors', function () use ($chave) {
    $s = (new NfeSnapshotNormalizer)->normalizar(nfeFixture('nfe_611'), 'indeterminado', $chave, true);
    expect($s->status)->toBe('INDETERMINADO');
    expect($s->persistivel)->toBeTrue();
    expect($s->estornavel)->toBeFalse();
    expect($s->errorMessage)->toContain('Receita');
});

it('612 não encontrada: persistível (nota fria), sem estorno', function () use ($chave) {
    $s = (new NfeSnapshotNormalizer)->normalizar(nfeFixture('nfe_612'), 'nao_encontrado', $chave, true);
    expect($s->status)->toBe('NAO_ENCONTRADA');
    expect($s->persistivel)->toBeTrue();
    expect($s->estornavel)->toBeFalse();
});

it('608 erro de parâmetro: NÃO persistível, estorna', function () use ($chave) {
    $s = (new NfeSnapshotNormalizer)->normalizar(nfeFixture('nfe_608'), 'erro_participante', $chave, false);
    expect($s->status)->toBe('ERRO_PARAMETRO');
    expect($s->persistivel)->toBeFalse();
    expect($s->estornavel)->toBeTrue();
});

it('timeout estorna só quando não billable', function () use ($chave) {
    $billable = (new NfeSnapshotNormalizer)->normalizar(['code' => 613, 'header' => ['billable' => true]], 'retry', $chave, true);
    $naoBillable = (new NfeSnapshotNormalizer)->normalizar(['code' => 613, 'header' => ['billable' => false]], 'retry', $chave, false);
    expect($billable->status)->toBe('TIMEOUT');
    expect($billable->estornavel)->toBeFalse();
    expect($naoBillable->estornavel)->toBeTrue();
    expect($billable->persistivel)->toBeFalse();
});

it('fatal: ERRO_INTEGRACAO, não persistível, estorna', function () use ($chave) {
    $s = (new NfeSnapshotNormalizer)->normalizar(['code' => 601, 'header' => ['billable' => false]], 'fatal', $chave, false);
    expect($s->status)->toBe('ERRO_INTEGRACAO');
    expect($s->persistivel)->toBeFalse();
    expect($s->estornavel)->toBeTrue();
});
