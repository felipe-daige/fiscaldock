<?php

namespace App\Services;

use App\Support\CsvExport;

class BiExportService
{
    public function __construct(protected BiService $bi) {}

    private function brl(float $v): string
    {
        return number_format($v, 2, ',', '.');
    }

    public function toCsv(array $colunas, array $linhas): string
    {
        return CsvExport::build($colunas, $linhas);
    }

    /**
     * Dataset tabular da aba (1 tabela canônica por aba). Reusa os getters do BI.
     */
    public function dataset(string $aba, int $userId, ?string $ini, ?string $fim, ?int $cli): array
    {
        return match ($aba) {
            'faturamento' => $this->fromMensal($this->bi->getFaturamentoPorPeriodo($userId, $ini, $fim, $cli),
                ['Mês', 'Faturamento', 'Qtd Notas'],
                fn ($r) => [$r['mes_formatado'], $this->brl($r['faturamento']), $r['qtd_notas']]),

            'tributos' => $this->fromMensal($this->bi->getCargaTributaria($userId, $ini, $fim, $cli),
                ['Mês', 'Faturamento', 'ICMS', 'PIS', 'COFINS', 'Total Tributos', 'Alíq. Efetiva %'],
                fn ($r) => [$r['mes_formatado'], $this->brl($r['faturamento']), $this->brl($r['icms']), $this->brl($r['pis']), $this->brl($r['cofins']), $this->brl($r['tributos_total']), $r['aliquota_efetiva']]),

            'apuracao-notas' => $this->datasetApuracao($userId, $ini, $fim, $cli),

            'cfop' => $this->datasetCfop($userId, $ini, $fim, $cli),

            default => ['colunas' => [], 'linhas' => []],
        };
    }

    private function fromMensal(array $rows, array $colunas, callable $map): array
    {
        return ['colunas' => $colunas, 'linhas' => array_map($map, $rows)];
    }

    private function datasetApuracao(int $userId, ?string $ini, ?string $fim, ?int $cli): array
    {
        $data = $this->bi->getApuracaoVsNotas($userId, $ini, $fim, $cli);
        $colunas = ['Mês', 'ICMS Declarado', 'ICMS Computado', 'PIS Declarado', 'PIS Computado', 'COFINS Declarado', 'COFINS Computado'];
        $linhas = array_map(fn ($m) => [
            $m['label'],
            $this->brl($m['icms']['declarado']), $this->brl($m['icms']['computado']),
            $this->brl($m['pis']['declarado']), $this->brl($m['pis']['computado']),
            $this->brl($m['cofins']['declarado']), $this->brl($m['cofins']['computado']),
        ], $data['mensal']);

        return ['colunas' => $colunas, 'linhas' => $linhas];
    }

    private function datasetCfop(int $userId, ?string $ini, ?string $fim, ?int $cli): array
    {
        $ranking = $this->bi->getCfopAnalitico($userId, $ini, $fim, $cli)['ranking'];
        $colunas = ['CFOP / Natureza', 'Tipo', 'Valor', 'Qtd Notas', 'Tributos', '% Total'];
        $linhas = array_map(fn ($r) => [
            $r['descricao'], $r['tipo'], $this->brl($r['valor']), $r['qtd'], $this->brl($r['tributos']), $r['percentual'],
        ], $ranking);

        return ['colunas' => $colunas, 'linhas' => $linhas];
    }

    /**
     * Relatório completo do BI (KPIs + cobertura + as 4 seções) — fonte única
     * consumida pelo PDF executivo e pelo XLSX. Reusa dataset() e os getters do BI.
     */
    public function relatorioCompleto(int $userId, ?string $ini, ?string $fim, ?int $cli): array
    {
        $resumo = $this->bi->getResumoGeral($userId, $cli, $ini, $fim);
        $efd = $this->bi->getKpisEfd($userId, $ini, $fim);

        $titulos = [
            'faturamento' => 'Faturamento mensal',
            'tributos' => 'Tributos por mês',
            'apuracao-notas' => 'Declarado × Computado',
            'cfop' => 'Ranking CFOP',
        ];
        $secoes = [];
        foreach ($titulos as $aba => $titulo) {
            $ds = $this->dataset($aba, $userId, $ini, $fim, $cli);
            $secoes[$aba] = ['titulo' => $titulo, 'colunas' => $ds['colunas'], 'linhas' => $ds['linhas']];
        }

        return [
            'periodo' => ['inicio' => $ini, 'fim' => $fim, 'cliente_id' => $cli],
            'kpis' => [
                'faturamento' => $this->brl((float) ($resumo['total_vendas'] ?? 0)),
                'aquisicoes' => $this->brl((float) ($resumo['total_compras'] ?? 0)),
                'tributos' => $this->brl((float) ($resumo['total_tributos'] ?? 0)),
                'saldo_liquido' => $this->brl((float) ($efd['saldo_liquido'] ?? 0)),
                'total_notas' => (int) ($resumo['total_notas'] ?? 0),
                'aliquota_media' => (float) ($resumo['aliquota_media'] ?? 0),
            ],
            'cobertura' => $this->bi->getCoberturaResumo($userId, $ini, $fim, $cli),
            'secoes' => $secoes,
        ];
    }

    /**
     * Converte linhas de uma seção mensal em itens do partial _bar-chart.
     * pct é relativo ao máximo da série (max=0 → pct=0). idxValorBrl aponta a
     * coluna cujo valor está formatado em BRL string ("1.234,56").
     */
    public function barChartItens(array $linhas, int $idxLabel, int $idxValorBrl, string $hex): array
    {
        $parse = fn (string $brl): float => (float) str_replace(',', '.', str_replace('.', '', $brl));
        $valores = array_map(fn ($l) => $parse((string) ($l[$idxValorBrl] ?? '0')), $linhas);
        $max = $valores ? max($valores) : 0.0;

        $itens = [];
        foreach ($linhas as $i => $l) {
            $itens[] = [
                'label' => (string) ($l[$idxLabel] ?? ''),
                'hex' => $hex,
                'pct' => $max > 0 ? (int) round($valores[$i] / $max * 100) : 0,
                'valor' => (string) ($l[$idxValorBrl] ?? ''),
            ];
        }

        return $itens;
    }
}
