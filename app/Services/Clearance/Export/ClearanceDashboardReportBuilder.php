<?php

namespace App\Services\Clearance\Export;

use App\Services\PricingCatalogService;
use App\Services\ValidacaoContabilService;
use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Illuminate\Support\Carbon;

/**
 * Monta o "Panorama de Clearance DF-e" — payload único do PDF, XLSX e CSV/ZIP de
 * `/app/clearance/dashboard`.
 *
 * Escopo de valor (pedido do usuário, 2026-07-09): este relatório carrega SÓ o que a
 * tela e os demais exports NÃO entregam. A tela conta documentos por situação na
 * Receita; aqui a dimensão nova é R$: valor por status, exposição financeira das notas
 * bloqueantes escrituradas (canceladas/denegadas/inutilizadas) e cobertura de
 * verificação por cliente + backlog (quanto custa fechar o gap). O PDF executivo de lote
 * cobre UM lote; este cobre o acervo inteiro.
 *
 * Contrato de seção idêntico ao Raio-X (DashboardNotasReportBuilder):
 *   ['chave','titulo','colunas','linhas','formatos'?,'cores'?,'total'?,'nota'?]
 * `linhas` carrega NÚMERO REAL nas colunas numéricas (XLSX soma/pivota; CSV converte pra
 * vírgula; PDF formata na renderização) — nunca "R$ 1.234,56" como string.
 */
class ClearanceDashboardReportBuilder
{
    /** Linhas que cabem no PDF sem virar listagem (XLSX/CSV levam tudo). */
    public const LIMITE_PDF = 60;

    /** Custo de verificar 1 nota no clearance em lote (tier básico) — usado no backlog. */
    private const CUSTO_VERIFICACAO_TIER = 'basico';

    private const HEX_SITUACAO = [
        'AUTORIZADA' => '#047857',
        'CANCELADA' => '#dc2626',
        'DENEGADA' => '#991b1b',
        'INUTILIZADA' => '#374151',
        'NAO_ENCONTRADA' => '#d97706',
        'INDETERMINADO' => '#1d4ed8',
        'NAO_VERIFICADA' => '#9ca3af',
    ];

    private const LABEL_SITUACAO = [
        'AUTORIZADA' => 'Autorizada',
        'CANCELADA' => 'Cancelada',
        'DENEGADA' => 'Denegada',
        'INUTILIZADA' => 'Inutilizada',
        'NAO_ENCONTRADA' => 'Não encontrada',
        'INDETERMINADO' => 'Indeterminada',
        'NAO_VERIFICADA' => 'Não verificada',
    ];

    public function __construct(
        private ValidacaoContabilService $service,
        private PricingCatalogService $pricing,
    ) {}

    public function montar(int $userId): array
    {
        $kpis = $this->service->getKpisStatusReceita($userId);
        $dados = $this->service->dadosPainelClearance($userId);
        $resumo = $dados['resumo'];

        $custoUnit = ValidacaoContabilService::custoUnitarioPorTier(self::CUSTO_VERIFICACAO_TIER);
        $backlogCreditos = (int) $resumo['pendentes'] * $custoUnit;

        $secoes = [
            $this->secaoStatusValor($dados['status_valor'], $resumo),
            $this->secaoExposicao($dados['exposicao']),
            $this->secaoCobertura($dados['cobertura']),
        ];

        return [
            'titulo' => 'Panorama de Clearance DF-e',
            'gerado_em' => now(),
            'kpis' => $kpis,
            'resumo' => $resumo,
            'backlog' => [
                'notas' => (int) $resumo['pendentes'],
                'custo_creditos' => $backlogCreditos,
                'custo_reais' => $this->pricing->creditsToCurrency($backlogCreditos),
                'custo_unitario_creditos' => $custoUnit,
            ],
            'filtros' => ['Escopo' => 'Todo o acervo (XML + EFD deduplicado por chave)'],
            'secoes' => collect($secoes)->keyBy('chave')->all(),
            'ordem_secoes' => array_column($secoes, 'chave'),
        ];
    }

    // ── Seções ────────────────────────────────────────────────────────────────

    /**
     * Status na Receita × VALOR R$. A tela mostra só a contagem; a coluna de valor e o
     * "% do valor" (concentração financeira por status) não existem em lugar nenhum.
     */
    private function secaoStatusValor(array $statusValor, array $resumo): array
    {
        $totalValor = (float) $resumo['valor_total'] ?: 1.0;
        $totalQtd = (int) $resumo['total_notas'] ?: 1;

        $linhas = [];
        $cores = [];
        foreach ($statusValor as $i => $s) {
            $situacao = $s['situacao'];
            $linhas[] = [
                self::LABEL_SITUACAO[$situacao] ?? $situacao,
                (int) $s['quantidade'],
                round($s['quantidade'] / $totalQtd * 100, 1),
                (float) $s['valor'],
                round($s['valor'] / $totalValor * 100, 1),
            ];
            $cores[$i] = [0 => self::HEX_SITUACAO[$situacao] ?? ReportTheme::NEUTRO];
        }

        return [
            'chave' => 'status-valor',
            'titulo' => 'Status na Receita × valor movimentado',
            'nota' => 'Quanto do valor escriturado está em cada situação oficial. Bloqueantes (cancelada/denegada/inutilizada) = documento na contabilidade que a Receita não reconhece.',
            'colunas' => ['Situação', 'Notas', '% das notas', 'Valor movimentado', '% do valor'],
            'formatos' => [1 => XlsxReport::FMT_INT, 2 => XlsxReport::FMT_PCT, 3 => XlsxReport::FMT_BRL, 4 => XlsxReport::FMT_PCT],
            'linhas' => $linhas,
            'cores' => $cores,
            'total' => [
                'Total',
                (int) $resumo['total_notas'],
                100.0,
                (float) $resumo['valor_total'],
                100.0,
            ],
        ];
    }

    /**
     * Exposição financeira: cada nota bloqueante escriturada, com valor e contraparte.
     * Nenhum outro relatório lista, no acervo inteiro, o R$ escriturado que a Receita
     * invalidou. É a lista acionável de risco fiscal.
     */
    private function secaoExposicao(array $exposicao): array
    {
        $linhas = [];
        $cores = [];
        foreach ($exposicao as $i => $e) {
            $linhas[] = [
                self::LABEL_SITUACAO[$e['situacao']] ?? $e['situacao'],
                $e['origem'],
                $e['numero'],
                $e['chave'],
                $e['cliente'],
                $e['contraparte'],
                (float) $e['valor'],
                $this->dataHora($e['consultado_em']),
            ];
            $cores[$i] = [0 => self::HEX_SITUACAO[$e['situacao']] ?? ReportTheme::IRREGULAR];
        }

        $totalValor = array_sum(array_column($exposicao, 'valor'));

        return [
            'chave' => 'exposicao-bloqueante',
            'titulo' => 'Exposição — notas bloqueantes escrituradas',
            'nota' => 'Notas canceladas, denegadas ou inutilizadas na Receita que continuam no acervo (XML/EFD). Revisão fiscal imediata: cada linha é crédito/débito indevido em risco.',
            'colunas' => ['Situação', 'Origem', 'Número', 'Chave de acesso', 'Cliente', 'Contraparte', 'Valor', 'Verificado em'],
            'formatos' => [6 => XlsxReport::FMT_BRL],
            'linhas' => $linhas,
            'cores' => $cores,
            'total' => ['Total', '', '', '', '', '', (float) $totalValor, ''],
        ];
    }

    /**
     * Cobertura de verificação por cliente: quanto do acervo de cada cliente já foi
     * consultado, o valor ainda pendente e a exposição bloqueante. A tela só tem o total
     * global — o recorte por cliente (onde priorizar) é exclusivo daqui.
     */
    private function secaoCobertura(array $cobertura): array
    {
        $linhas = collect($cobertura)->map(fn ($c) => [
            $c['cliente'],
            (int) $c['total'],
            (int) $c['verificadas'],
            (int) $c['pendentes'],
            (float) $c['cobertura_pct'],
            (float) $c['valor_pendente'],
            (int) $c['bloqueantes'],
            (float) $c['valor_bloqueante'],
        ])->all();

        return [
            'chave' => 'cobertura-cliente',
            'titulo' => 'Cobertura de verificação por cliente',
            'nota' => 'Cobertura = notas verificadas ÷ total do cliente. "Valor pendente" = R$ ainda não confirmado na Receita. Priorize quem tem mais bloqueante e menor cobertura.',
            'colunas' => ['Cliente', 'Notas', 'Verificadas', 'Pendentes', 'Cobertura', 'Valor pendente', 'Bloqueantes', 'Valor bloqueante'],
            'formatos' => [1 => XlsxReport::FMT_INT, 2 => XlsxReport::FMT_INT, 3 => XlsxReport::FMT_INT, 4 => XlsxReport::FMT_PCT, 5 => XlsxReport::FMT_BRL, 6 => XlsxReport::FMT_INT, 7 => XlsxReport::FMT_BRL],
            'linhas' => $linhas,
            'total' => [
                'Total',
                array_sum(array_column($cobertura, 'total')),
                array_sum(array_column($cobertura, 'verificadas')),
                array_sum(array_column($cobertura, 'pendentes')),
                '',
                array_sum(array_column($cobertura, 'valor_pendente')),
                array_sum(array_column($cobertura, 'bloqueantes')),
                array_sum(array_column($cobertura, 'valor_bloqueante')),
            ],
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function dataHora($iso): string
    {
        if (! $iso) {
            return '';
        }

        try {
            return Carbon::parse($iso)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return (string) $iso;
        }
    }
}
