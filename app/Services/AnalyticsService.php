<?php

namespace App\Services;

use App\Models\NotaFiscal;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Faturamento por periodo (mensal).
     */
    public function getFaturamentoPorPeriodo(int $userId, ?string $dataInicio = null, ?string $dataFim = null, ?int $clienteId = null): array
    {
        $query = NotaFiscal::where('user_id', $userId)
            ->where('tipo_nota', NotaFiscal::TIPO_SAIDA)
            ->where('finalidade', '!=', NotaFiscal::FINALIDADE_DEVOLUCAO);

        if ($dataInicio) {
            $query->where('data_emissao', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->where('data_emissao', '<=', $dataFim);
        }

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        return $query->select(
            DB::raw("DATE_TRUNC('month', data_emissao) as mes"),
            DB::raw('SUM(valor_total) as faturamento'),
            DB::raw('COUNT(*) as qtd_notas')
        )
            ->groupBy(DB::raw("DATE_TRUNC('month', data_emissao)"))
            ->orderBy('mes')
            ->get()
            ->map(function ($item) {
                return [
                    'mes' => $item->mes,
                    'mes_formatado' => $item->mes ? date('m/Y', strtotime($item->mes)) : null,
                    'faturamento' => (float) $item->faturamento,
                    'qtd_notas' => (int) $item->qtd_notas,
                ];
            })
            ->toArray();
    }

    /**
     * Top clientes por valor de venda.
     */
    public function getTopClientes(int $userId, int $limit = 10, ?string $dataInicio = null, ?string $dataFim = null, ?int $clienteId = null): array
    {
        $query = NotaFiscal::where('user_id', $userId)
            ->where('tipo_nota', NotaFiscal::TIPO_SAIDA)
            ->where('finalidade', '!=', NotaFiscal::FINALIDADE_DEVOLUCAO);

        if ($dataInicio) {
            $query->where('data_emissao', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->where('data_emissao', '<=', $dataFim);
        }

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        return $query->select(
            'dest_cnpj',
            'dest_razao_social',
            DB::raw('SUM(valor_total) as total'),
            DB::raw('COUNT(*) as qtd_notas')
        )
            ->groupBy('dest_cnpj', 'dest_razao_social')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'cnpj' => $item->dest_cnpj,
                    'razao_social' => $item->dest_razao_social,
                    'total' => (float) $item->total,
                    'qtd_notas' => (int) $item->qtd_notas,
                ];
            })
            ->toArray();
    }

    /**
     * Top fornecedores por valor de compra.
     */
    public function getTopFornecedores(int $userId, int $limit = 10, ?string $dataInicio = null, ?string $dataFim = null, ?int $clienteId = null): array
    {
        $query = NotaFiscal::where('user_id', $userId)
            ->where('tipo_nota', NotaFiscal::TIPO_ENTRADA)
            ->where('finalidade', '!=', NotaFiscal::FINALIDADE_DEVOLUCAO);

        if ($dataInicio) {
            $query->where('data_emissao', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->where('data_emissao', '<=', $dataFim);
        }

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        return $query->select(
            'emit_cnpj',
            'emit_razao_social',
            DB::raw('SUM(valor_total) as total'),
            DB::raw('COUNT(*) as qtd_notas')
        )
            ->groupBy('emit_cnpj', 'emit_razao_social')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'cnpj' => $item->emit_cnpj,
                    'razao_social' => $item->emit_razao_social,
                    'total' => (float) $item->total,
                    'qtd_notas' => (int) $item->qtd_notas,
                ];
            })
            ->toArray();
    }

    /**
     * Carga tributaria por periodo.
     */
    public function getCargaTributaria(int $userId, ?string $dataInicio = null, ?string $dataFim = null, ?int $clienteId = null): array
    {
        $query = NotaFiscal::where('user_id', $userId);

        if ($dataInicio) {
            $query->where('data_emissao', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->where('data_emissao', '<=', $dataFim);
        }

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        return $query->select(
            DB::raw("DATE_TRUNC('month', data_emissao) as mes"),
            DB::raw('SUM(valor_total) as faturamento'),
            DB::raw('SUM(COALESCE(icms_valor, 0)) as icms'),
            DB::raw('SUM(COALESCE(icms_st_valor, 0)) as icms_st'),
            DB::raw('SUM(COALESCE(pis_valor, 0)) as pis'),
            DB::raw('SUM(COALESCE(cofins_valor, 0)) as cofins'),
            DB::raw('SUM(COALESCE(ipi_valor, 0)) as ipi'),
            DB::raw('SUM(COALESCE(icms_valor, 0) + COALESCE(icms_st_valor, 0) + COALESCE(pis_valor, 0) + COALESCE(cofins_valor, 0) + COALESCE(ipi_valor, 0)) as tributos_total')
        )
            ->groupBy(DB::raw("DATE_TRUNC('month', data_emissao)"))
            ->orderBy('mes')
            ->get()
            ->map(function ($item) {
                $faturamento = (float) $item->faturamento;
                $tributos = (float) $item->tributos_total;
                $aliquotaEfetiva = $faturamento > 0 ? round(($tributos / $faturamento) * 100, 2) : 0;

                return [
                    'mes' => $item->mes,
                    'mes_formatado' => $item->mes ? date('m/Y', strtotime($item->mes)) : null,
                    'faturamento' => $faturamento,
                    'icms' => (float) $item->icms,
                    'icms_st' => (float) $item->icms_st,
                    'pis' => (float) $item->pis,
                    'cofins' => (float) $item->cofins,
                    'ipi' => (float) $item->ipi,
                    'tributos_total' => $tributos,
                    'aliquota_efetiva' => $aliquotaEfetiva,
                ];
            })
            ->toArray();
    }

    /**
     * Entradas vs Saidas por periodo.
     */
    public function getEntradasVsSaidas(int $userId, ?string $dataInicio = null, ?string $dataFim = null, ?int $clienteId = null): array
    {
        $query = NotaFiscal::where('user_id', $userId)
            ->where('finalidade', '!=', NotaFiscal::FINALIDADE_DEVOLUCAO);

        if ($dataInicio) {
            $query->where('data_emissao', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->where('data_emissao', '<=', $dataFim);
        }

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        return $query->select(
            DB::raw("DATE_TRUNC('month', data_emissao) as mes"),
            DB::raw('SUM(CASE WHEN tipo_nota = 0 THEN valor_total ELSE 0 END) as entradas'),
            DB::raw('SUM(CASE WHEN tipo_nota = 1 THEN valor_total ELSE 0 END) as saidas'),
            DB::raw('COUNT(CASE WHEN tipo_nota = 0 THEN 1 END) as qtd_entradas'),
            DB::raw('COUNT(CASE WHEN tipo_nota = 1 THEN 1 END) as qtd_saidas')
        )
            ->groupBy(DB::raw("DATE_TRUNC('month', data_emissao)"))
            ->orderBy('mes')
            ->get()
            ->map(function ($item) {
                return [
                    'mes' => $item->mes,
                    'mes_formatado' => $item->mes ? date('m/Y', strtotime($item->mes)) : null,
                    'entradas' => (float) $item->entradas,
                    'saidas' => (float) $item->saidas,
                    'saldo' => (float) $item->saidas - (float) $item->entradas,
                    'qtd_entradas' => (int) $item->qtd_entradas,
                    'qtd_saidas' => (int) $item->qtd_saidas,
                ];
            })
            ->toArray();
    }

    /**
     * Devolucoes por periodo.
     */
    public function getDevolucoes(int $userId, ?string $dataInicio = null, ?string $dataFim = null, ?int $clienteId = null): array
    {
        $query = NotaFiscal::where('user_id', $userId)
            ->where('finalidade', NotaFiscal::FINALIDADE_DEVOLUCAO);

        if ($dataInicio) {
            $query->where('data_emissao', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->where('data_emissao', '<=', $dataFim);
        }

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        return $query->select(
            DB::raw("DATE_TRUNC('month', data_emissao) as mes"),
            DB::raw('SUM(valor_total) as valor_devolucoes'),
            DB::raw('COUNT(*) as qtd_devolucoes')
        )
            ->groupBy(DB::raw("DATE_TRUNC('month', data_emissao)"))
            ->orderBy('mes')
            ->get()
            ->map(function ($item) {
                return [
                    'mes' => $item->mes,
                    'mes_formatado' => $item->mes ? date('m/Y', strtotime($item->mes)) : null,
                    'valor_devolucoes' => (float) $item->valor_devolucoes,
                    'qtd_devolucoes' => (int) $item->qtd_devolucoes,
                ];
            })
            ->toArray();
    }

    /**
     * Resumo geral para o dashboard.
     */
    public function getResumoGeral(int $userId, ?int $clienteId = null): array
    {
        $query = NotaFiscal::where('user_id', $userId);

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        $totais = $query->select(
            DB::raw('COUNT(*) as total_notas'),
            DB::raw('SUM(CASE WHEN tipo_nota = 1 AND finalidade != 4 THEN valor_total ELSE 0 END) as total_vendas'),
            DB::raw('SUM(CASE WHEN tipo_nota = 0 AND finalidade != 4 THEN valor_total ELSE 0 END) as total_compras'),
            DB::raw('SUM(CASE WHEN finalidade = 4 THEN valor_total ELSE 0 END) as total_devolucoes'),
            DB::raw('SUM(COALESCE(icms_valor, 0) + COALESCE(pis_valor, 0) + COALESCE(cofins_valor, 0) + COALESCE(ipi_valor, 0)) as total_tributos'),
            DB::raw('COUNT(DISTINCT emit_cnpj) as total_fornecedores'),
            DB::raw('COUNT(DISTINCT dest_cnpj) as total_clientes')
        )->first();

        $totalVendas = (float) ($totais->total_vendas ?? 0);
        $totalTributos = (float) ($totais->total_tributos ?? 0);

        return [
            'total_notas' => (int) ($totais->total_notas ?? 0),
            'total_vendas' => $totalVendas,
            'total_compras' => (float) ($totais->total_compras ?? 0),
            'total_devolucoes' => (float) ($totais->total_devolucoes ?? 0),
            'total_tributos' => $totalTributos,
            'aliquota_media' => $totalVendas > 0 ? round(($totalTributos / $totalVendas) * 100, 2) : 0,
            'total_fornecedores' => (int) ($totais->total_fornecedores ?? 0),
            'total_clientes' => (int) ($totais->total_clientes ?? 0),
        ];
    }

    /**
     * Faturamento por UF.
     */
    public function getFaturamentoPorUf(int $userId, ?string $dataInicio = null, ?string $dataFim = null, ?int $clienteId = null): array
    {
        $query = NotaFiscal::where('user_id', $userId)
            ->where('tipo_nota', NotaFiscal::TIPO_SAIDA)
            ->where('finalidade', '!=', NotaFiscal::FINALIDADE_DEVOLUCAO)
            ->whereNotNull('dest_uf');

        if ($dataInicio) {
            $query->where('data_emissao', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->where('data_emissao', '<=', $dataFim);
        }

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        return $query->select(
            'dest_uf as uf',
            DB::raw('SUM(valor_total) as total'),
            DB::raw('COUNT(*) as qtd_notas')
        )
            ->groupBy('dest_uf')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                return [
                    'uf' => $item->uf,
                    'total' => (float) $item->total,
                    'qtd_notas' => (int) $item->qtd_notas,
                ];
            })
            ->toArray();
    }

    /**
     * Tributos por tipo.
     */
    public function getTributosPorTipo(int $userId, ?string $dataInicio = null, ?string $dataFim = null, ?int $clienteId = null): array
    {
        $query = NotaFiscal::where('user_id', $userId);

        if ($dataInicio) {
            $query->where('data_emissao', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->where('data_emissao', '<=', $dataFim);
        }

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        $totais = $query->select(
            DB::raw('SUM(COALESCE(icms_valor, 0)) as icms'),
            DB::raw('SUM(COALESCE(icms_st_valor, 0)) as icms_st'),
            DB::raw('SUM(COALESCE(pis_valor, 0)) as pis'),
            DB::raw('SUM(COALESCE(cofins_valor, 0)) as cofins'),
            DB::raw('SUM(COALESCE(ipi_valor, 0)) as ipi')
        )->first();

        return [
            ['tipo' => 'ICMS', 'valor' => (float) ($totais->icms ?? 0)],
            ['tipo' => 'ICMS-ST', 'valor' => (float) ($totais->icms_st ?? 0)],
            ['tipo' => 'PIS', 'valor' => (float) ($totais->pis ?? 0)],
            ['tipo' => 'COFINS', 'valor' => (float) ($totais->cofins ?? 0)],
            ['tipo' => 'IPI', 'valor' => (float) ($totais->ipi ?? 0)],
        ];
    }
}
