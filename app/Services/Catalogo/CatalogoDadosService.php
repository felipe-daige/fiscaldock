<?php

namespace App\Services\Catalogo;

use App\Models\EfdCatalogoItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Fonte única das agregações do Catálogo de Produtos (`/app/catalogo`).
 *
 * A tela (`CatalogoController::index`) e os exports (PDF/XLSX/CSV) leem daqui — extraído
 * do controller em 2026-07-08 sem alterar semântica. Regras P1/P2/P4 (dedup origem, ICMS
 * do C170 fiscal ≈ 0, notas canceladas fora) comentadas junto de cada query, iguais às do
 * controller original.
 *
 * Escopo dos filtros: KPIs e gráficos respeitam APENAS cliente/importação; a tabela de
 * itens respeita todos (tipo/ncm/busca/cfop/cst) — espelha o comportamento da tela.
 */
class CatalogoDadosService
{
    /**
     * @param  array{cliente_id?:mixed,importacao_id?:mixed,tipo_item?:mixed,ncm?:mixed,busca?:mixed,cfops?:array,csts?:array}  $filtros
     */
    public function kpis(int $userId, array $filtros): array
    {
        $baseQuery = EfdCatalogoItem::where('user_id', $userId);
        if (! empty($filtros['cliente_id'])) {
            $baseQuery->where('cliente_id', $filtros['cliente_id']);
        }
        if (! empty($filtros['importacao_id'])) {
            $baseQuery->where('importacao_id', $filtros['importacao_id']);
        }

        $totalProdutos = (clone $baseQuery)->distinct('cod_item')->count('cod_item');

        // "NCM faltando" = só mercadoria/produto (tipo 00–06) sem NCM — gap fiscal real.
        // Itens que não exigem NCM (07–10/99) não entram, senão o número engana.
        $ncmFaltando = (clone $baseQuery)
            ->whereIn('tipo_item', EfdCatalogoItem::TIPOS_EXIGEM_NCM)
            ->where(fn ($q) => $q->whereNull('cod_ncm')->orWhere('cod_ncm', ''))
            ->distinct('cod_item')->count('cod_item');

        $clienteFilter = $this->clienteFilterSql($filtros);
        $movCliente = $this->movimentoClienteFilterSql($filtros);

        // P4: notas canceladas (cod_sit 02/03/04/05) não movimentam — join a efd_notas p/ filtrar.
        // P1: itens fiscais (C170) e de contribuicoes são DISJUNTOS por chave — somar não dobra.
        $comMovimentacao = DB::selectOne("
            SELECT COUNT(DISTINCT ci.cod_item) as total
            FROM efd_catalogo_itens ci
            INNER JOIN efd_notas_itens ni ON ni.codigo_item = ci.cod_item AND ni.user_id = ci.user_id
            INNER JOIN efd_notas n ON n.id = ni.efd_nota_id AND n.cancelada = false
            WHERE ci.user_id = ?{$clienteFilter}{$movCliente}
        ", [$userId]);

        // IN(subselect) em vez de JOIN ao catálogo: se o mesmo cod_item existe em >1 cliente
        // (numeração 0200 é por empresa), o JOIN faria fanout e dobraria o SUM. O IN casa a nota
        // ao universo de cod_item do catálogo sem multiplicar por linha de cadastro.
        $valorMovimentado = DB::selectOne("
            SELECT COALESCE(SUM(ni.valor_total), 0) as total
            FROM efd_notas_itens ni
            INNER JOIN efd_notas n ON n.id = ni.efd_nota_id AND n.cancelada = false
            WHERE ni.user_id = ?{$movCliente}
            AND ni.codigo_item IN (SELECT ci.cod_item FROM efd_catalogo_itens ci WHERE ci.user_id = ?{$clienteFilter})
        ", [$userId, $userId]);

        // P2: no perfil comercial o C170 fiscal NÃO carrega alíquota de ICMS (vem 0); o valor real
        // está no C190/contribuicoes. Só comparar itens efetivamente tributados (aliquota_icms > 0).
        $aliqDivergente = DB::selectOne("
            SELECT COUNT(DISTINCT ci.cod_item) as total
            FROM efd_catalogo_itens ci
            INNER JOIN efd_notas_itens ni ON ni.codigo_item = ci.cod_item AND ni.user_id = ci.user_id
            INNER JOIN efd_notas n ON n.id = ni.efd_nota_id AND n.cancelada = false
            WHERE ci.user_id = ?
            AND ci.aliq_icms IS NOT NULL AND ni.aliquota_icms > 0
            AND ABS(ci.aliq_icms - ni.aliquota_icms) > 0.01{$clienteFilter}{$movCliente}
        ", [$userId]);

        return [
            'total_produtos' => $totalProdutos,
            'com_movimentacao' => (int) ($comMovimentacao->total ?? 0),
            'sem_movimentacao' => $totalProdutos - (int) ($comMovimentacao->total ?? 0),
            'valor_movimentado' => (float) ($valorMovimentado->total ?? 0),
            'aliq_divergente' => (int) ($aliqDivergente->total ?? 0),
            'ncm_faltando' => $ncmFaltando,
        ];
    }

    /**
     * Query dos itens (registro mais recente por cod_item), com movimentação/valor/alíquota média
     * agregados e todos os filtros aplicados. O chamador ordena/pagina/conta.
     */
    public function itensQuery(int $userId, array $filtros): Builder
    {
        $baseQuery = EfdCatalogoItem::where('user_id', $userId);
        if (! empty($filtros['cliente_id'])) {
            $baseQuery->where('cliente_id', $filtros['cliente_id']);
        }
        if (! empty($filtros['importacao_id'])) {
            $baseQuery->where('importacao_id', $filtros['importacao_id']);
        }

        $latestIds = (clone $baseQuery)
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('cod_item');

        // Movimentação escopada por cliente quando o filtro está ativo: cod_item é numeração
        // própria de cada empresa (0200), então o mesmo código em clientes diferentes pode ser
        // produto distinto — sem isto a movimentação vazaria entre clientes.
        $movCliente = $this->movimentoClienteFilterSql($filtros);

        // Subqueries por item: cancelada fora (P4); alíquota média só de itens tributados (P2).
        $itensQuery = EfdCatalogoItem::whereIn('id', $latestIds)
            ->select('efd_catalogo_itens.*')
            ->addSelect(DB::raw("(SELECT COUNT(*) FROM efd_notas_itens ni JOIN efd_notas n ON n.id = ni.efd_nota_id AND n.cancelada = false WHERE ni.codigo_item = efd_catalogo_itens.cod_item AND ni.user_id = {$userId}{$movCliente}) as total_movimentacoes"))
            ->addSelect(DB::raw("(SELECT COALESCE(SUM(ni.valor_total), 0) FROM efd_notas_itens ni JOIN efd_notas n ON n.id = ni.efd_nota_id AND n.cancelada = false WHERE ni.codigo_item = efd_catalogo_itens.cod_item AND ni.user_id = {$userId}{$movCliente}) as valor_movimentado"))
            ->addSelect(DB::raw("(SELECT AVG(ni.aliquota_icms) FROM efd_notas_itens ni JOIN efd_notas n ON n.id = ni.efd_nota_id AND n.cancelada = false WHERE ni.codigo_item = efd_catalogo_itens.cod_item AND ni.user_id = {$userId}{$movCliente} AND ni.aliquota_icms > 0) as aliq_icms_media_notas"));

        if (! empty($filtros['tipo_item'])) {
            $itensQuery->where('tipo_item', $filtros['tipo_item']);
        }
        if (! empty($filtros['ncm'])) {
            $itensQuery->where('cod_ncm', 'ilike', '%'.$filtros['ncm'].'%');
        }
        if (! empty($filtros['busca'])) {
            $busca = $filtros['busca'];
            $itensQuery->where(function ($q) use ($busca) {
                $q->where('cod_item', 'ilike', "%{$busca}%")
                    ->orWhere('descr_item', 'ilike', "%{$busca}%")
                    ->orWhere('cod_ncm', 'ilike', "%{$busca}%");
            });
        }

        // CFOP/CST: produto entra se TEM movimentação (nota não cancelada) casando. cfop=integer no SPED.
        if (! empty($filtros['cfops'])) {
            $cfops = array_map('intval', $filtros['cfops']);
            $itensQuery->whereExists(fn ($s) => $this->movimentoExists($s, $userId, $filtros)->whereIn('ni.cfop', $cfops));
        }
        if (! empty($filtros['csts'])) {
            $csts = $filtros['csts'];
            $itensQuery->whereExists(fn ($s) => $this->movimentoExists($s, $userId, $filtros)->whereIn('ni.cst_icms', $csts));
        }

        return $itensQuery;
    }

    /** Top 10 CFOPs por frequência (P4: exclui cancelada). */
    public function cfopsTop(int $userId, array $filtros): array
    {
        $clienteFilter = $this->clienteFilterSql($filtros);
        $movCliente = $this->movimentoClienteFilterSql($filtros);

        return DB::select("
            SELECT ni.cfop, COUNT(*) as total, SUM(ni.valor_total) as valor
            FROM efd_notas_itens ni
            INNER JOIN efd_notas n ON n.id = ni.efd_nota_id AND n.cancelada = false
            WHERE ni.user_id = ? AND ni.cfop IS NOT NULL{$movCliente}
            AND ni.codigo_item IN (SELECT ci.cod_item FROM efd_catalogo_itens ci WHERE ci.user_id = ?{$clienteFilter})
            GROUP BY ni.cfop
            ORDER BY total DESC
            LIMIT 10
        ", [$userId, $userId]);
    }

    /** Top 10 CSTs ICMS por frequência (P4: exclui cancelada). */
    public function cstsTop(int $userId, array $filtros): array
    {
        $clienteFilter = $this->clienteFilterSql($filtros);
        $movCliente = $this->movimentoClienteFilterSql($filtros);

        return DB::select("
            SELECT ni.cst_icms, COUNT(*) as total
            FROM efd_notas_itens ni
            INNER JOIN efd_notas n ON n.id = ni.efd_nota_id AND n.cancelada = false
            WHERE ni.user_id = ? AND ni.cst_icms IS NOT NULL AND ni.cst_icms != ''{$movCliente}
            AND ni.codigo_item IN (SELECT ci.cod_item FROM efd_catalogo_itens ci WHERE ci.user_id = ?{$clienteFilter})
            GROUP BY ni.cst_icms
            ORDER BY total DESC
            LIMIT 10
        ", [$userId, $userId]);
    }

    /** Universo de CFOP/CST da movimentação do catálogo (respeita cliente/importação). */
    public function facetaRows(int $userId, array $filtros): array
    {
        $clienteFilter = $this->clienteFilterSql($filtros);
        $movCliente = $this->movimentoClienteFilterSql($filtros);

        return DB::select("
            SELECT DISTINCT ni.cfop::text AS cfop, ni.cst_icms AS cst
            FROM efd_notas_itens ni
            INNER JOIN efd_notas n ON n.id = ni.efd_nota_id AND n.cancelada = false
            INNER JOIN efd_catalogo_itens ci ON ci.cod_item = ni.codigo_item AND ci.user_id = ni.user_id
            WHERE ci.user_id = ?{$clienteFilter}{$movCliente}
        ", [$userId]);
    }

    /** Fragmento SQL " AND ci.cliente_id = N AND ci.importacao_id = M" — valores castados a int. */
    private function clienteFilterSql(array $filtros): string
    {
        $sql = ! empty($filtros['cliente_id']) ? ' AND ci.cliente_id = '.((int) $filtros['cliente_id']) : '';
        $sql .= ! empty($filtros['importacao_id']) ? ' AND ci.importacao_id = '.((int) $filtros['importacao_id']) : '';

        return $sql;
    }

    /**
     * Fragmento " AND n.cliente_id = N" pro lado da MOVIMENTAÇÃO (efd_notas). cod_item é
     * numeração própria de cada empresa (0200) → o mesmo código em clientes distintos pode
     * ser produto diferente; escopar a nota pelo cliente evita vazamento entre clientes.
     * NÃO escopa por importacao_id: a nota não pertence à importação do catálogo.
     */
    private function movimentoClienteFilterSql(array $filtros): string
    {
        return ! empty($filtros['cliente_id']) ? ' AND n.cliente_id = '.((int) $filtros['cliente_id']) : '';
    }

    /**
     * Subquery base de movimentação (efd_notas_itens, nota não cancelada) ligada ao item do
     * catálogo da linha externa. Chamador encadeia o whereIn de cfop/cst.
     */
    private function movimentoExists($sub, int $userId, array $filtros = [])
    {
        $sub = $sub->selectRaw('1')
            ->from('efd_notas_itens as ni')
            ->join('efd_notas as n', fn ($j) => $j->on('n.id', '=', 'ni.efd_nota_id')->where('n.cancelada', false))
            ->whereColumn('ni.codigo_item', 'efd_catalogo_itens.cod_item')
            ->where('ni.user_id', $userId);

        if (! empty($filtros['cliente_id'])) {
            $sub->where('n.cliente_id', (int) $filtros['cliente_id']);
        }

        return $sub;
    }
}
