<?php

use App\Services\Clearance\Export\ClearanceXlsxBuilder;
use App\Support\Reports\XlsxReport;
use OpenSpout\Reader\XLSX\Reader;

uses(Tests\TestCase::class);

beforeEach(function () {
    if (! XlsxReport::disponivel()) {
        $this->markTestSkipped('OpenSpout não instalado (rebuild pendente).');
    }
});

function relatorioClearanceFixture(): array
{
    $doc = (object) [
        'severidade' => 'critica', 'tipo_documento' => 'NFE', 'numero' => '123', 'serie' => '1',
        'chave_acesso' => str_pad('9', 44, '9'), 'emit_nome' => 'ACME LTDA', 'emit_cnpj' => '00.000.000/0001-91',
        'declarado_valor' => 1000.0, 'valor_total' => 800.0, 'delta_valor' => 200.0,
        'decadencia_label' => '12/2030', 'exposicao_base' => 200.0, 'motivos' => ['Valor divergente'],
    ];
    $ok = (object) [
        'tipo_documento' => 'NFE', 'numero' => '124', 'serie' => '1',
        'emit_nome' => 'BOA SA', 'valor_total' => 500.0,
    ];

    return [
        'capa' => [
            'escritorio' => ['razao_social' => 'ESCRITORIO X', 'cnpj' => '11.111.111/0001-11'],
            'cliente_auditado' => ['razao_social' => 'CLIENTE Y'],
            'periodo' => ['label' => 'jan/2026 a mar/2026'],
            'lote_id' => 42, 'emitido_em_label' => '02/07/2026 10:00',
        ],
        'resumo' => [
            'veredito' => ['severidade' => 'critica', 'mensagem' => 'Divergências críticas encontradas.'],
            'total_documentos' => 2, 'total_divergencias' => 1, 'total_criticas' => 1,
            'total_revisar' => 0, 'sem_divergencia' => 1, 'ruido' => 0,
        ],
        'exposicao' => ['base' => 200.0, 'multa' => 150.0, 'total' => 350.0],
        'metodologia' => ['tolerancia_absoluta' => 0.10, 'tolerancia_percentual' => 0.5],
        'concentracao' => collect([[
            'emit_nome' => 'ACME LTDA', 'emit_cnpj' => '00.000.000/0001-91',
            'qtd' => 1, 'valor_exposto' => 200.0,
        ]]),
        'documentos' => collect([$doc]),
        'sem_divergencia' => collect([$ok]),
        'hash' => str_repeat('ab', 32),
    ];
}

it('gera o workbook do clearance com números reais e badge de severidade', function () {
    $path = storage_path('framework/testing/clr_'.uniqid().'.xlsx');
    app(ClearanceXlsxBuilder::class)->gerarArquivo(relatorioClearanceFixture(), $path);

    $reader = new Reader();
    $reader->open($path);
    $sheets = [];
    foreach ($reader->getSheetIterator() as $sheet) {
        $rows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $rows[] = $row->toArray();
        }
        $sheets[$sheet->getName()] = $rows;
    }
    $reader->close();
    @unlink($path);

    expect(array_keys($sheets))->toBe(['Resumo', 'Concentração de Risco', 'Divergências', 'Sem Divergência']);

    // Resumo: veredito + exposição numérica
    $resumo = $sheets['Resumo'];
    expect($resumo[3])->toEqual(['Escritório responsável', 'ESCRITORIO X']);
    expect($resumo[6])->toEqual(['Veredito', 'Crítica']);
    expect($resumo[13])->toEqual(['Exposição — crédito/imposto exposto', 200.0]);
    expect($resumo[15])->toEqual(['Exposição total estimada', 350.0]);

    // Concentração: valores numéricos
    expect($sheets['Concentração de Risco'][2])->toEqual(['ACME LTDA', '00.000.000/0001-91', 1, 200.0]);

    // Divergências: severidade + declarado/sefaz/delta numéricos
    $div = $sheets['Divergências'][2];
    expect($div[0])->toBe('Crítica');
    expect($div[5])->toEqual(1000.0);
    expect($div[6])->toEqual(800.0);
    expect($div[7])->toEqual(200.0);
    expect($div[10])->toBe('Valor divergente');

    // Sem divergência
    expect($sheets['Sem Divergência'][2])->toEqual(['NFE 124/1', 'BOA SA', 500.0]);
});
