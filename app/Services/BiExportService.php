<?php

namespace App\Services;

use App\Services\Consultas\Fiscal\TopMovimentacaoQuery;
use App\Support\CsvExport;

class BiExportService
{
    public function __construct(
        protected BiService $bi,
        protected TopMovimentacaoQuery $topMov,
    ) {}

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
     * Relatório completo do BI (KPIs + cobertura + seções) — fonte única
     * consumida pelo PDF executivo e pelo XLSX. Reusa dataset() e os getters do BI.
     * Mode-aware: $cli === null ⇒ portfólio (inclui riscos + score); else ⇒ cliente.
     */
    public function relatorioCompleto(int $userId, ?string $ini, ?string $fim, ?int $cli): array
    {
        $resumo = $this->bi->getResumoGeral($userId, $cli, $ini, $fim);
        $efd = $this->bi->getKpisEfd($userId, $ini, $fim);
        $modo = $cli ? 'cliente' : 'portfolio';

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

        // Seções enriquecidas (P+C)
        $secoes['participantes'] = $this->datasetParticipantes($userId, $ini, $fim, $cli);
        $secoes['top-notas'] = $this->datasetTopNotas($userId, $ini, $fim, $cli);
        $secoes['catalogo'] = $this->datasetCatalogo($userId, $cli);
        $secoes['uf'] = $this->datasetUf($userId, $ini, $fim, $cli);
        $secoes['devolucoes'] = $this->datasetDevolucoes($userId, $ini, $fim, $cli);

        $ordem = ['faturamento', 'tributos', 'apuracao-notas', 'cfop', 'participantes', 'top-notas', 'catalogo', 'uf', 'devolucoes'];
        $scoreCarteira = null;

        // Seções user-wide — só portfólio (sem fonte cliente-scoped)
        if ($cli === null) {
            $secoes['riscos-notas'] = $this->datasetRiscoNotas($userId, $ini, $fim);
            $secoes['riscos-fornecedores'] = $this->datasetRiscoFornecedores($userId, $ini, $fim);
            $ordem[] = 'riscos-notas';
            $ordem[] = 'riscos-fornecedores';
            $ordem[] = 'score-carteira';
            $sc = $this->bi->getScoreCarteira($userId);
            $scoreCarteira = [
                'percentual_regular' => (float) ($sc['percentual_regular'] ?? 0),
                'irregulares' => (int) ($sc['irregulares'] ?? 0),
                'participantes_ativos' => (int) ($sc['participantes_ativos'] ?? 0),
                'percentual_em_risco' => (float) ($sc['percentual_em_risco'] ?? 0),
                'valor_total_em_risco_brl' => $this->brl((float) ($sc['valor_total_em_risco'] ?? 0)),
            ];
        }

        return [
            'modo' => $modo,
            'ordem_secoes' => $ordem,
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
            'score_carteira' => $scoreCarteira,
        ];
    }

    private function datasetParticipantes(int $userId, ?string $ini, ?string $fim, ?int $cli): array
    {
        $colunas = ['CNPJ/CPF', 'Razão social', 'Regime', 'Situação', 'Volume', 'Qtd notas', 'Ticket médio'];

        if ($cli === null) {
            $linhas = array_map(fn ($r) => [
                $r['cnpj_cpf'], $r['razao_social'], $r['regime'] ?: '—',
                ($r['irregular'] ? '⚠ ' : '').($r['situacao'] ?: '—'),
                $this->brl($r['total_valor']), $r['total_notas'], $this->brl($r['ticket_medio']),
            ], $this->bi->getRankingParticipantes($userId, 'S', $ini, $fim, 10));

            return ['titulo' => 'Top participantes (carteira)', 'colunas' => $colunas, 'linhas' => $linhas];
        }

        // Modo cliente: contrapartes daquele cliente (clientes/saída + fornecedores/entrada)
        $clientes = $this->bi->getTopClientes($userId, 10, $ini, $fim, $cli);
        $forn = $this->bi->getTopFornecedores($userId, 10, $ini, $fim, $cli);
        $colsC = ['Papel', 'CNPJ/CPF', 'Razão social', 'Volume', 'Qtd notas'];
        $linhas = [];
        foreach ($clientes as $r) {
            $linhas[] = ['Cliente', $r['cnpj'], $r['razao_social'] ?: '—', $this->brl($r['total']), $r['qtd_notas']];
        }
        foreach ($forn as $r) {
            $linhas[] = ['Fornecedor', $r['cnpj'], $r['razao_social'] ?: '—', $this->brl($r['total']), $r['qtd_notas']];
        }

        return ['titulo' => 'Principais contrapartes', 'colunas' => $colsC, 'linhas' => $linhas];
    }

    private function datasetTopNotas(int $userId, ?string $ini, ?string $fim, ?int $cli): array
    {
        $colunas = ['Data', 'Chave', 'Participante', 'Tipo', 'Valor'];
        $linhas = array_map(fn ($r) => [
            $r['data_emissao'] ?: '—',
            $r['chave'] ?: '—',
            ($r['razao_social'] ?: '—').($r['cnpj_cpf'] ? ' ('.$r['cnpj_cpf'].')' : ''),
            $r['tipo'] === 'E' ? 'Entrada' : 'Saída',
            $this->brl($r['valor']),
        ], $this->bi->getTopNotas($userId, $ini, $fim, $cli, 15));

        return ['titulo' => 'Principais notas', 'colunas' => $colunas, 'linhas' => $linhas];
    }

    private function datasetCatalogo(int $userId, ?int $cli): array
    {
        $colunas = ['Cód item', 'Descrição', 'NCM', 'Valor movimentado', 'Qtd'];

        $itens = $cli === null
            ? $this->topMov->produtosPorUsuario($userId, 15)
            : ($this->topMov->produtos($userId, 'cliente_id', [$cli], 15)[$cli] ?? []);

        $linhas = array_map(fn ($r) => [
            $r['cod_item'], $r['descricao'], $r['ncm'] ?: '—', $this->brl($r['valor']), $r['qtd'],
        ], $itens);

        return ['titulo' => 'Top itens do catálogo (acumulado)', 'colunas' => $colunas, 'linhas' => $linhas];
    }

    private function datasetUf(int $userId, ?string $ini, ?string $fim, ?int $cli): array
    {
        $colunas = ['UF', 'Faturamento', 'Qtd notas'];
        $linhas = array_map(fn ($r) => [
            $r['uf'] ?: '—', $this->brl($r['total']), $r['qtd_notas'],
        ], $this->bi->getFaturamentoPorUf($userId, $ini, $fim, $cli));

        return ['titulo' => 'Faturamento por UF', 'colunas' => $colunas, 'linhas' => $linhas];
    }

    private function datasetDevolucoes(int $userId, ?string $ini, ?string $fim, ?int $cli): array
    {
        $colunas = ['Mês', 'Valor devoluções', 'Qtd'];
        $linhas = array_map(fn ($r) => [
            $r['mes_formatado'] ?: '—', $this->brl($r['valor_devolucoes']), $r['qtd_devolucoes'],
        ], $this->bi->getDevolucoes($userId, $ini, $fim, $cli));

        return ['titulo' => 'Devoluções', 'colunas' => $colunas, 'linhas' => $linhas];
    }

    private function datasetRiscoNotas(int $userId, ?string $ini, ?string $fim): array
    {
        $colunas = ['Data', 'CNPJ/CPF', 'Razão social', 'Situação', 'Tipo', 'Valor'];
        $linhas = array_map(fn ($r) => [
            $r['data_emissao'] ?: '—', $r['cnpj_cpf'], $r['razao_social'] ?: '—',
            $r['situacao'] ?: '—', $r['tipo_nota'] === 'E' ? 'Entrada' : 'Saída', $this->brl($r['vl_doc']),
        ], array_slice($this->bi->getNotasEmRisco($userId, $ini, $fim), 0, 20));

        return ['titulo' => 'Notas em risco (participante irregular)', 'colunas' => $colunas, 'linhas' => $linhas];
    }

    private function datasetRiscoFornecedores(int $userId, ?string $ini, ?string $fim): array
    {
        $colunas = ['CNPJ/CPF', 'Razão social', 'Situação', 'Qtd notas', 'Valor em risco'];
        $linhas = array_map(fn ($r) => [
            $r['cnpj_cpf'], $r['razao_social'] ?: '—', $r['situacao'] ?: '—',
            $r['total_notas'], $this->brl($r['valor_em_risco']),
        ], array_slice($this->bi->getFornecedoresIrregulares($userId, $ini, $fim), 0, 20));

        return ['titulo' => 'Fornecedores irregulares', 'colunas' => $colunas, 'linhas' => $linhas];
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
