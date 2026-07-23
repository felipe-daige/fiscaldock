<?php

use App\Support\Efd\ModeloDocumento;

it('classifica modelo em bucket de 4 vias', function () {
    expect(ModeloDocumento::bucket('55'))->toBe('notas_mercadorias');
    expect(ModeloDocumento::bucket('65'))->toBe('notas_consumidor');  // NFC-e separada
    expect(ModeloDocumento::bucket('57'))->toBe('notas_transportes');
    expect(ModeloDocumento::bucket('67'))->toBe('notas_transportes'); // CT-e OS
    expect(ModeloDocumento::bucket('00'))->toBe('notas_servicos');
    expect(ModeloDocumento::bucket('01'))->toBe('notas_mercadorias'); // avulsa
    expect(ModeloDocumento::bucket(''))->toBe('notas_mercadorias');
    expect(ModeloDocumento::bucket(null))->toBe('notas_mercadorias');
});

it('bucketAgrupado dobra consumidor em mercadorias (strip de 3 vias)', function () {
    expect(ModeloDocumento::bucketAgrupado('65'))->toBe('notas_mercadorias');
    expect(ModeloDocumento::bucketAgrupado('55'))->toBe('notas_mercadorias');
    expect(ModeloDocumento::bucketAgrupado('67'))->toBe('notas_transportes');
    expect(ModeloDocumento::bucketAgrupado('00'))->toBe('notas_servicos');
});

it('SQL CASE bate com bucketAgrupado (67 em transportes, não mercadorias)', function () {
    $sql = ModeloDocumento::sqlBlocoAgrupado('modelo');
    expect($sql)->toContain("'57','67'");        // CT-e OS incluso em transportes
    expect($sql)->toContain("modelo = '00'");     // serviço
    expect($sql)->toContain('notas_mercadorias'); // else
});
