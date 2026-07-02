<?php

use App\Services\Bi\Export\BiCsvZipBuilder;

uses(Tests\TestCase::class);

function relatorioCsvZipFixture(): array
{
    return [
        'modo' => 'portfolio',
        'ordem_secoes' => ['faturamento', 'contrapartes', 'uf', 'riscos-fornecedores', 'score-carteira'],
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
            'contrapartes' => [
                'titulo' => 'Principais contrapartes', 'modo' => 'portfolio',
                'itens' => [[
                    'papel' => null, 'cnpj' => '00.000.000/0001-91', 'razao' => 'ACME LTDA',
                    'score_total' => 82, 'classificacao' => 'baixo',
                    'volume' => 1000.0, 'volume_brl' => '1.000,00', 'notas' => 2,
                    'ticket_brl' => '500,00', 'cfops' => ['5102'],
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
                'linhas' => [],
            ],
        ],
    ];
}

it('empacota resumo, cobertura e todas as seções na ordem do relatório', function () {
    $path = storage_path('framework/testing/bicsv_'.uniqid().'.zip');
    app(BiCsvZipBuilder::class)->gerarArquivo(relatorioCsvZipFixture(), $path);

    $zip = new ZipArchive;
    expect($zip->open($path))->toBeTrue();

    $nomes = [];
    $conteudos = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $nome = $zip->getNameIndex($i);
        $nomes[] = $nome;
        $conteudos[$nome] = $zip->getFromIndex($i);
    }
    $zip->close();
    @unlink($path);

    // score-carteira não vira CSV próprio (entra no resumo)
    expect($nomes)->toBe([
        '01-resumo.csv', '02-cobertura.csv', '03-faturamento.csv',
        '04-contrapartes.csv', '05-uf.csv', '06-riscos-fornecedores.csv',
    ]);

    // Coluna Valor 100% numérica: sem "R$ " prefixando (unidade no rótulo),
    // percentual com vírgula decimal — alinhamento consistente no Excel pt-BR
    expect($conteudos['01-resumo.csv'])
        ->toContain('"Faturamento (R$)";1.500,00')
        ->toContain('"Alíquota média (%)";8')
        ->toContain('"Score da carteira — regular (%)";80')
        ->toContain('"Score da carteira — valor em risco (R$)";250,00')
        ->not->toContain('R$ ');

    expect($conteudos['02-cobertura.csv'])->toContain('2024-02;FALTA;Sim');

    expect($conteudos['03-faturamento.csv'])
        ->toContain('Mês;Faturamento;"Qtd Notas"')
        ->toContain('jan/24;1.000,00;2');

    expect($conteudos['04-contrapartes.csv'])
        ->toContain('CNPJ/CPF;"Razão social";Classificação;Score;Volume;"Qtd notas";"Ticket médio";"Principais CFOPs"')
        ->toContain('00.000.000/0001-91;"ACME LTDA";baixo;82;1.000,00;2;500,00;5102');

    // Seção sem linhas ainda entra: header presente para o contador saber que existe
    expect($conteudos['06-riscos-fornecedores.csv'])->toContain('CNPJ/CPF');
});
