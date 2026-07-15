<?php

use App\Services\Bi\Export\BiXlsxBuilder;
use App\Support\Reports\XlsxReport;
use OpenSpout\Reader\XLSX\Reader;

uses(Tests\TestCase::class);

beforeEach(function () {
    if (! XlsxReport::disponivel()) {
        $this->markTestSkipped('OpenSpout não instalado (rebuild pendente).');
    }
});

function relatorioBiFixture(): array
{
    return [
        'modo' => 'portfolio',
        'ordem_secoes' => ['faturamento', 'tributos', 'contrapartes', 'uf', 'riscos-fornecedores', 'score-carteira'],
        'periodo' => ['inicio' => '2024-01-01', 'fim' => '2024-02-29', 'cliente_id' => null],
        'kpis' => [
            'faturamento' => '1.500,00', 'aquisicoes' => '300,00', 'tributos' => '120,00',
            'saldo_liquido' => '1.200,00', 'total_notas' => 3, 'aliquota_media' => 8.0,
        ],
        'a_recolher_brl' => '90,00',
        'cobertura' => [
            'parcial' => true,
            'meses_sem_fiscal' => [['mes' => '2024-02', 'label' => 'fev/24']],
            'meses_sem_contrib' => [], 'meses_gap_total' => [],
        ],
        'cobertura_consulta' => ['total' => 5, 'sem_consulta' => 2, 'sem_uf' => 0],
        'score_carteira' => [
            'percentual_regular' => 80.0, 'irregulares' => 1, 'participantes_ativos' => 5,
            'percentual_em_risco' => 10.0, 'valor_total_em_risco_brl' => '250,00',
        ],
        'secoes' => [
            'faturamento' => [
                'titulo' => 'Faturamento mensal',
                'colunas' => ['Mês', 'Faturamento', 'Qtd Notas'],
                'linhas' => [['jan/24', '1.000,00', 2], ['fev/24', '500,00', 1]],
            ],
            'tributos' => [
                'titulo' => 'Tributos por mês',
                'colunas' => ['Mês', 'Faturamento', 'ICMS', 'PIS', 'COFINS', 'Total Tributos', 'Alíq. Efetiva %'],
                'linhas' => [],
            ],
            'contrapartes' => [
                'titulo' => 'Principais contrapartes', 'modo' => 'portfolio',
                'itens' => [[
                    'papel' => null, 'cnpj' => '00.000.000/0001-91', 'razao' => 'ACME LTDA',
                    'score_total' => 82, 'classificacao' => 'baixo',
                    'volume' => 1000.0, 'volume_brl' => '1.000,00', 'notas' => 2,
                    'ticket_brl' => '500,00', 'cfops' => ['5102', '6102'],
                ]],
            ],
            'uf' => [
                'titulo' => 'Faturamento por UF',
                'colunas' => ['UF', 'Faturamento', 'Qtd notas'],
                'linhas' => [['SP', '1.500,00', 3]],
            ],
            'riscos-fornecedores' => [
                'titulo' => 'Fornecedores irregulares',
                'colunas' => ['CNPJ/CPF', 'Razão social', 'Situação', 'Qtd notas', 'Valor em risco'],
                'linhas' => [['11.111.111/0001-11', 'FALHA SA', 'BAIXADA', 4, '2.000,00']],
            ],
        ],
    ];
}

function lerWorkbook(string $path): array
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

it('gera o workbook completo na ordem do relatório com números reais', function () {
    $path = storage_path('framework/testing/bixb_'.uniqid().'.xlsx');
    app(BiXlsxBuilder::class)->gerarArquivo(relatorioBiFixture(), $path);

    expect(is_file($path))->toBeTrue();
    $sheets = lerWorkbook($path);
    @unlink($path);

    // Abas na ordem do relatório (score-carteira entra no Resumo, não vira aba)
    expect(array_keys($sheets))->toBe(['Resumo', 'Cobertura', 'Faturamento', 'Tributos', 'Contrapartes', 'UF', 'Riscos - Fornecedores']);

    // Resumo: KPIs como número real (somável), score da carteira e alerta de consulta
    $resumo = $sheets['Resumo'];
    expect($resumo[2])->toBe(['Indicador', 'Valor']);
    expect($resumo[3])->toEqual(['Faturamento', 1500.0]);
    expect($resumo[6])->toEqual(['A recolher (apurado)', 90.0]);
    expect($resumo[9])->toEqual(['Alíquota média', 8.0]);
    expect($resumo[10])->toEqual(['Score da carteira — % regular', 80.0]);
    expect($resumo[11])->toEqual(['Score da carteira — irregulares', 1]);
    expect($resumo[12])->toEqual(['Score da carteira — participantes ativos', 5]);
    expect($resumo[15])->toEqual(['Participantes nunca consultados', 2]);

    // Faturamento: valores numéricos + linha de totais somada
    $fat = $sheets['Faturamento'];
    expect($fat[1])->toBe(['Mês', 'Faturamento', 'Qtd Notas']);
    expect($fat[2])->toEqual(['jan/24', 1000.0, 2]);
    expect($fat[4])->toEqual(['Total', 1500.0, 3]);

    // Seção sem linhas ganha aviso, não aba vazia
    expect($sheets['Tributos'][2][0])->toBe('Sem dados no período.');

    // Cobertura: mês sem EFD ICMS/IPI marcado como FALTA
    expect($sheets['Cobertura'][2])->toBe(['2024-02', 'FALTA', 'Sim']);

    // Contrapartes tabularizada (portfólio: sem Papel, com Ticket médio)
    $ctr = $sheets['Contrapartes'];
    expect($ctr[1])->toBe(['CNPJ/CPF', 'Razão social', 'Classificação', 'Score', 'Volume', 'Qtd notas', 'Ticket médio', 'Principais CFOPs']);
    expect($ctr[2])->toEqual(['00.000.000/0001-91', 'ACME LTDA', 'baixo', 82, 1000.0, 2, 500.0, '5102 · 6102']);

    // Riscos: valor em risco numérico, sem linha de totais (lista top-N)
    $rf = $sheets['Riscos - Fornecedores'];
    expect($rf[2])->toEqual(['11.111.111/0001-11', 'FALHA SA', 'BAIXADA', 4, 2000.0]);
    expect($rf)->toHaveCount(3);
});

it('em modo cliente a aba Contrapartes traz Papel e omite Ticket médio', function () {
    $rel = relatorioBiFixture();
    $rel['modo'] = 'cliente';
    $rel['periodo']['cliente_id'] = 7;
    $rel['ordem_secoes'] = ['contrapartes'];
    $rel['score_carteira'] = null;
    $rel['secoes']['contrapartes']['modo'] = 'cliente';
    $rel['secoes']['contrapartes']['itens'][0]['papel'] = 'Fornecedor';
    $rel['secoes']['contrapartes']['itens'][0]['ticket_brl'] = null;

    $path = storage_path('framework/testing/bixb_'.uniqid().'.xlsx');
    app(BiXlsxBuilder::class)->gerarArquivo($rel, $path);
    $sheets = lerWorkbook($path);
    @unlink($path);

    $ctr = $sheets['Contrapartes'];
    expect($ctr[1])->toBe(['Papel', 'CNPJ/CPF', 'Razão social', 'Classificação', 'Score', 'Volume', 'Qtd notas', 'Principais CFOPs']);
    expect($ctr[2])->toEqual(['Fornecedor', '00.000.000/0001-91', 'ACME LTDA', 'baixo', 82, 1000.0, 2, '5102 · 6102']);
});
