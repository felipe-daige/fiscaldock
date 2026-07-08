<?php

namespace App\Services\Catalogo\Export;

use App\Helpers\CstIcmsHelper;
use App\Models\Cliente;
use App\Models\EfdCatalogoItem;
use App\Models\EfdImportacao;
use App\Services\Catalogo\CatalogoDadosService;
use App\Services\CatalogoHistoricoService;
use App\Support\Cfop;
use App\Support\Reports\XlsxReport;

/**
 * Monta o relatório do Catálogo de Produtos (registro 0200) — payload único do PDF, XLSX e
 * CSV/ZIP de `/app/catalogo`. Lê `CatalogoDadosService`, a mesma fonte da tela, então os
 * números batem. Ver `docs/catalogo/exportacoes.md`.
 *
 * Contrato de seção idêntico ao do Raio-X de notas:
 *   ['chave','titulo','colunas','linhas','formatos'?,'grafico'?,'nota'?,'cores'?,'total'?]
 * `linhas` carrega número real nas colunas numéricas (XLSX precisa; CSV converte; PDF formata).
 */
class CatalogoReportBuilder
{
    /** Linhas de tabela longa que cabem no PDF (XLSX/CSV levam tudo). */
    public const LIMITE_PDF = 60;

    private const HEX_OK = '#047857';

    private const HEX_DIVERGENTE = '#d97706';

    private const HEX_SEM_MOV = '#9ca3af';

    private const HEX_CFOP = '#4338ca';

    private const PALETA = ['#2563eb', '#0d9488', '#b45309', '#7c3aed', '#be185d', '#0891b2', '#65a30d', '#374151', '#9ca3af', '#1f2937'];

    public function __construct(
        private CatalogoDadosService $dados,
        private CatalogoHistoricoService $historico,
    ) {}

    public function montar(int $userId, array $filtros): array
    {
        $kpis = $this->dados->kpis($userId, $filtros);
        $itens = $this->dados->itensQuery($userId, $filtros)->orderBy('cod_item')->get();
        $cfops = $this->dados->cfopsTop($userId, $filtros);
        $csts = $this->dados->cstsTop($userId, $filtros);
        $drift = $this->historico->resumoMudancas($userId);

        $secoes = [
            $this->secaoItens($itens),
            $this->secaoCfops($cfops),
            $this->secaoCsts($csts),
            $this->secaoDrift($drift),
        ];

        return [
            'titulo' => 'Catálogo de Produtos — Registro 0200',
            'gerado_em' => now(),
            'filtros' => $this->descreverFiltros($userId, $filtros),
            'kpis' => $kpis,
            'drift' => $drift,
            'secoes' => collect($secoes)->keyBy('chave')->all(),
            'ordem_secoes' => array_column($secoes, 'chave'),
        ];
    }

    // ── Seções ────────────────────────────────────────────────────────────────

    /** Tabela consolidada do catálogo: cadastro 0200 × movimentação real nas notas. */
    private function secaoItens($itens): array
    {
        $linhas = [];
        $cores = [];

        foreach ($itens as $i => $item) {
            $aliqCat = $item->aliq_icms !== null ? (float) $item->aliq_icms : null;
            $aliqNotas = $item->aliq_icms_media_notas !== null ? (float) $item->aliq_icms_media_notas : null;
            $divergente = $aliqCat !== null && $aliqNotas !== null && abs($aliqCat - $aliqNotas) > 0.01;
            $semMov = ((int) ($item->total_movimentacoes ?? 0)) === 0;

            [$status, $statusHex] = $divergente
                ? ['Divergente', self::HEX_DIVERGENTE]
                : ($semMov ? ['Sem movimentação', self::HEX_SEM_MOV] : ['OK', self::HEX_OK]);

            $linhas[] = [
                $item->cod_item,
                $item->descr_item ?: '—',
                $item->cod_ncm ?: '—',
                EfdCatalogoItem::TIPO_ITEM_LABELS[$item->tipo_item] ?? ($item->tipo_item ?: '—'),
                $aliqCat,
                $aliqNotas,
                (int) ($item->total_movimentacoes ?? 0),
                (float) ($item->valor_movimentado ?? 0),
                $status,
            ];
            $cores[$i] = [8 => $statusHex];
        }

        return [
            'chave' => 'itens',
            'titulo' => 'Catálogo de itens — cadastro × movimentação',
            'nota' => 'Alíq. catálogo = cadastro 0200; Alíq. notas = média nas notas tributadas (ICMS > 0). "Divergente" = as duas diferem > 0,01 p.p. Valor/movimentação excluem notas canceladas.',
            'colunas' => ['Código', 'Descrição', 'NCM', 'Tipo', 'Alíq. catálogo', 'Alíq. notas', 'Movim.', 'Valor movim.', 'Status'],
            'formatos' => [4 => XlsxReport::FMT_PCT, 5 => XlsxReport::FMT_PCT, 6 => XlsxReport::FMT_INT, 7 => XlsxReport::FMT_BRL],
            'linhas' => $linhas,
            'cores' => $cores,
        ];
    }

    /** Top 10 CFOPs por frequência na movimentação do catálogo. */
    private function secaoCfops(array $cfops): array
    {
        $rows = collect($cfops);
        $max = (float) $rows->max('total') ?: 1.0;

        $linhas = $rows->map(fn ($c) => [
            (string) $c->cfop,
            Cfop::descricao((string) $c->cfop),
            ucfirst(Cfop::tipoOperacao((string) $c->cfop)),
            (int) $c->total,
            (float) ($c->valor ?? 0),
        ])->all();

        $grafico = $rows->map(fn ($c) => [
            'label' => $c->cfop.' — '.mb_strimwidth(Cfop::descricao((string) $c->cfop), 0, 40, '…'),
            'pct' => (int) round((float) $c->total / $max * 100),
            'valor' => (string) $c->total,
            'hex' => self::HEX_CFOP,
        ])->all();

        return [
            'chave' => 'cfops',
            'titulo' => 'Top 10 CFOPs por frequência',
            'nota' => 'Frequência = nº de itens de nota (não cancelados) com o CFOP, entre produtos do catálogo.',
            'colunas' => ['CFOP', 'Descrição', 'Tipo', 'Frequência', 'Valor'],
            'formatos' => [3 => XlsxReport::FMT_INT, 4 => XlsxReport::FMT_BRL],
            'linhas' => $linhas,
            'grafico' => ['tipo' => 'barras', 'itens' => $grafico],
        ];
    }

    /** Top 10 CSTs ICMS por frequência (barra empilhada 100%). */
    private function secaoCsts(array $csts): array
    {
        $rows = collect($csts);
        $total = (float) $rows->sum('total') ?: 1.0;

        $linhas = $rows->map(fn ($c) => [
            (string) ($c->cst_icms ?? '—'),
            CstIcmsHelper::descricao($c->cst_icms),
            (int) $c->total,
            round((float) $c->total / $total * 100, 1),
        ])->all();

        $grafico = $rows->values()->map(fn ($c, $i) => [
            'label' => ($c->cst_icms ?? '—').' — '.mb_strimwidth(CstIcmsHelper::descricao($c->cst_icms), 0, 28, '…'),
            'pct' => round((float) $c->total / $total * 100, 1),
            'valor' => (string) $c->total,
            'hex' => self::PALETA[$i % count(self::PALETA)],
        ])->all();

        return [
            'chave' => 'csts',
            'titulo' => 'Top 10 CSTs ICMS por frequência',
            'nota' => 'Distribuição da tributação do ICMS nos itens movimentados (tributado, substituição tributária, isento, …).',
            'colunas' => ['CST', 'Descrição', 'Frequência', '% do total'],
            'formatos' => [2 => XlsxReport::FMT_INT, 3 => XlsxReport::FMT_PCT],
            'linhas' => $linhas,
            'grafico' => ['tipo' => 'stacked', 'itens' => $grafico],
        ];
    }

    /** Drift de cadastro entre importações (NCM/alíquota/unidade/descrição). */
    private function secaoDrift(array $drift): array
    {
        $labels = [
            'cod_ncm' => 'NCM',
            'aliq_icms' => 'Alíquota',
            'unid_inv' => 'Unidade',
            'descr_item' => 'Descrição',
        ];

        $linhas = [];
        foreach ($labels as $campo => $label) {
            $linhas[] = [$label, (int) ($drift['por_campo'][$campo] ?? 0)];
        }

        return [
            'chave' => 'drift',
            'titulo' => 'Mudanças de cadastro (drift entre importações)',
            'nota' => $drift['total'] > 0
                ? $drift['itens_afetados'].' item(ns) afetado(s). NCM alterado pode indicar reclassificação ou erro de cadastro (risco fiscal).'
                : 'Nenhuma mudança de cadastro detectada entre importações.',
            'colunas' => ['Campo alterado', 'Mudanças'],
            'formatos' => [1 => XlsxReport::FMT_INT],
            'linhas' => $linhas,
            'total' => ['Total', (int) ($drift['total'] ?? 0)],
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function descreverFiltros(int $userId, array $filtros): array
    {
        $out = [];

        $out['Cliente'] = ! empty($filtros['cliente_id'])
            ? (Cliente::where('user_id', $userId)->find($filtros['cliente_id'])?->razao_social ?? 'Cliente #'.$filtros['cliente_id'])
            : 'Todos';

        $out['Importação'] = ! empty($filtros['importacao_id'])
            ? (EfdImportacao::where('user_id', $userId)->find($filtros['importacao_id'])?->filename ?? '#'.$filtros['importacao_id'])
            : 'Todas';

        if (! empty($filtros['tipo_item'])) {
            $out['Tipo'] = EfdCatalogoItem::TIPO_ITEM_LABELS[$filtros['tipo_item']] ?? $filtros['tipo_item'];
        }
        if (! empty($filtros['ncm'])) {
            $out['NCM'] = $filtros['ncm'];
        }
        if (! empty($filtros['busca'])) {
            $out['Busca'] = $filtros['busca'];
        }
        if (! empty($filtros['cfops'])) {
            $out['CFOPs'] = implode(', ', $filtros['cfops']);
        }
        if (! empty($filtros['csts'])) {
            $out['CSTs'] = implode(', ', $filtros['csts']);
        }

        return $out;
    }
}
