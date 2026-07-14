<?php

use App\Services\Dossie\DossieXlsxBuilder;
use App\Support\Reports\XlsxReport;
use OpenSpout\Reader\XLSX\Reader;

uses(Tests\TestCase::class);

beforeEach(function () {
    if (! XlsxReport::disponivel()) {
        $this->markTestSkipped('OpenSpout não instalado (rebuild pendente).');
    }
});

function dossieDadosFixture(): array
{
    return [
        'gerado_em' => '02/07/2026 10:00',
        'consulta' => [
            'tem' => true,
            'blocos' => [[
                'titulo' => 'CND Federal',
                'badge' => ['label' => 'Regular', 'hex' => '#047857'],
                'itens' => [['label' => 'Validade', 'valor' => '01/01/2027']],
            ]],
        ],
        'score' => ['score_total' => 82, 'classificacao' => 'baixo', 'detalhamento' => []],
        'movimentacao' => [
            'kpis' => [
                'total_notas' => 12, 'valor_movimentado' => 3500.0,
                'entradas_qtd' => 5, 'entradas_valor' => 1500.0,
                'saidas_qtd' => 7, 'saidas_valor' => 2000.0,
                'periodo_inicio' => '2026-01', 'periodo_fim' => '2026-06',
            ],
            'por_competencia' => [
                ['competencia' => '2026-01', 'entrada' => 1000.0, 'saida' => 800.0],
                ['competencia' => '2026-02', 'entrada' => 500.0, 'saida' => 1200.0],
            ],
            'por_cfop' => [['cfop' => '5102', 'qtd' => 8, 'valor' => 2000.0]],
            'por_cst' => [['cst' => '000', 'qtd' => 8, 'valor' => 2000.0]],
            'impostos' => ['icms' => 240.0, 'pis' => 33.0, 'cofins' => 152.0, 'aliquota_icms_media' => 12.0],
        ],
        'top_produtos' => [['cod_item' => 'P-1', 'descricao' => 'CIMENTO', 'valor' => 900.0, 'qtd' => 30.0]],
        'top_cfops' => [['cfop' => '5102', 'descricao' => 'Venda de mercadoria', 'valor' => 2000.0, 'qtd' => 8]],
    ];
}

function lerWorkbookDossie(string $path): array
{
    $reader = new Reader;
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

    return $sheets;
}

it('gera o workbook do dossiê com identificação, certidões e movimentação numérica', function () {
    $dono = (object) [
        'razao_social' => 'ACME LTDA', 'documento' => '00.000.000/0001-91',
        'situacao_cadastral' => 'ATIVA', 'uf' => 'SP',
    ];

    $path = storage_path('framework/testing/dsx_'.uniqid().'.xlsx');
    app(DossieXlsxBuilder::class)->gerarArquivo(dossieDadosFixture(), $dono, $path);
    $sheets = lerWorkbookDossie($path);
    @unlink($path);

    expect(array_keys($sheets))->toBe(['Resumo', 'Certidões', 'Por Competência', 'CFOP', 'CST', 'Top Produtos', 'Top CFOPs']);

    $resumo = $sheets['Resumo'];
    expect($resumo[3])->toEqual(['Razão social', 'ACME LTDA']);
    expect($resumo[4])->toEqual(['CNPJ', '00.000.000/0001-91']);
    expect($resumo[7])->toEqual(['Score fiscal', 82]);
    expect($resumo[10])->toEqual(['Valor movimentado', 3500.0]);

    expect($sheets['Certidões'][2])->toEqual(['CND Federal', 'Regular', 'Validade: 01/01/2027']);

    // Competência: entradas/saídas numéricas + totais
    $comp = $sheets['Por Competência'];
    expect($comp[2])->toEqual(['2026-01', 1000.0, 800.0]);
    expect($comp[4])->toEqual(['Total', 1500.0, 2000.0]);

    expect($sheets['CFOP'][2])->toEqual(['5102', 8, 2000.0]);
    expect($sheets['Top Produtos'][2])->toEqual(['P-1', 'CIMENTO', 900.0, 30.0]);
});

it('documento de 11 dígitos vira CPF e nunca recebe score fiscal sintético', function () {
    $dono = (object) [
        'razao_social' => 'FULANO DE TAL', 'documento' => '123.456.789-09',
        'situacao_cadastral' => null, 'uf' => null,
    ];
    $dados = dossieDadosFixture();
    // Mesmo que um payload legado traga faixa fiscal, CPF deve permanecer não avaliado.
    $dados['score'] = ['score_total' => 50, 'classificacao' => 'medio'];
    $dados['consulta'] = ['tem' => false, 'blocos' => []];

    $path = storage_path('framework/testing/dsx_'.uniqid().'.xlsx');
    app(DossieXlsxBuilder::class)->gerarArquivo($dados, $dono, $path);
    $sheets = lerWorkbookDossie($path);
    @unlink($path);

    $resumo = $sheets['Resumo'];
    expect($resumo[4][0])->toBe('CPF');
    expect($resumo[7])->toEqual(['Risco de crédito (CPF)', 'Não avaliado']);
    expect($resumo[8])->toEqual(['Classificação de risco', 'não avaliado']);
    expect($sheets['Certidões'][2][0])->toContain('Certidões de CNPJ não se aplicam a CPF');
});
