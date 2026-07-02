<?php

use App\Services\Bi\Export\CatalogoItensXlsxBuilder;
use App\Support\Reports\XlsxReport;
use OpenSpout\Reader\XLSX\Reader;

uses(Tests\TestCase::class);

beforeEach(function () {
    if (! XlsxReport::disponivel()) {
        $this->markTestSkipped('OpenSpout não instalado (rebuild pendente).');
    }
});

it('gera o xlsx do catálogo com números reais e linha de totais', function () {
    $itens = collect([
        [
            'codigo_item' => 'P-0142', 'descricao' => 'CIMENTO CP-II 50KG', 'fontes' => 'efd',
            'ncm' => '25232910', 'cfops' => '5102', 'csts' => '000',
            'quantidade' => 100.5, 'ocorrencias' => 12, 'aliquota_media' => 18.0,
            'valor_total' => 2800.0, 'tem_catalogo' => true, 'catalogo' => ['descr_item' => 'CIMENTO 50KG'],
        ],
        [
            'codigo_item' => 'P-0001', 'descricao' => 'AREIA', 'fontes' => 'xml',
            'ncm' => null, 'cfops' => null, 'csts' => null,
            'quantidade' => 10.0, 'ocorrencias' => 2, 'aliquota_media' => null,
            'valor_total' => 200.0, 'tem_catalogo' => false, 'catalogo' => null,
        ],
    ]);

    $path = storage_path('framework/testing/cat_'.uniqid().'.xlsx');
    app(CatalogoItensXlsxBuilder::class)->gerarArquivo($itens, [['rotulo' => 'Fonte', 'valor' => 'EFD']], $path);

    $reader = new Reader();
    $reader->open($path);
    $rows = [];
    foreach ($reader->getSheetIterator() as $sheet) {
        foreach ($sheet->getRowIterator() as $row) {
            $rows[] = $row->toArray();
        }
        break;
    }
    $reader->close();
    @unlink($path);

    // título, subtítulo (filtros), header, 2 itens, totais
    expect($rows[2][0])->toBe('Código');
    expect($rows[3])->toEqual(['P-0142', 'CIMENTO CP-II 50KG', 'efd', '25232910', '5102', '000', 100.5, 12, 18.0, 2800.0, 'CIMENTO 50KG']);
    // alíquota nula vira '—' (sem quebrar a coluna numérica com zero falso)
    expect($rows[4][8])->toBe('—');
    expect($rows[4][10])->toBe('Sem catálogo');
    // totais somados
    expect($rows[5][0])->toBe('Total');
    expect($rows[5][6])->toEqual(110.5);
    expect($rows[5][7])->toEqual(14);
    expect($rows[5][9])->toEqual(3000.0);
});
