<?php

namespace App\Services\Clientes;

use App\Models\Cliente;
use App\Models\EfdNota;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Agrega movimentações fiscais (EFD) de UM cliente, escopado por user_id + cliente_id.
 * Exclui notas canceladas. Espelha ParticipanteMovimentacaoService (que é participante_id).
 * Dívida futura: extrair core MovimentacaoEfdQuery(userId, coluna, id) quando houver 3º consumidor.
 */
final class ClienteMovimentacaoService
{
    /**
     * Notas EFD do cliente, não canceladas, DEDUPLICADAS por origem (P1 global: fiscal OR
     * sem gêmea fiscal de mesma chave). Espelha EfdAgregadorService::notasDedup (a base do
     * BI/dashboard) para o ledger próprio do cliente — a NF-e nas duas EFD não dobra e os
     * documentos só-contribuições entram uma vez. Não aplica o filtro comercial de CFOP
     * (fora-faturamento) de propósito: o dossiê é movimento bruto do ledger, simétrico ao
     * dossiê do participante (que segue a ficha, também sem filtro comercial).
     */
    private function notasQuery(Cliente $c): \Illuminate\Database\Eloquent\Builder
    {
        return EfdNota::query()
            ->where('user_id', $c->user_id)
            ->where('cliente_id', $c->id)
            ->where('cancelada', false)
            ->where(fn ($q) => $q->where('origem_arquivo', 'fiscal')
                ->orWhereRaw('NOT EXISTS (SELECT 1 FROM efd_notas f WHERE f.user_id = ? AND f.origem_arquivo = ? AND f.chave_acesso IS NOT NULL AND f.chave_acesso = efd_notas.chave_acesso)', [$c->user_id, 'fiscal']));
    }

    /**
     * Consolidado C190 (fonte canônica de ICMS/IPI/CFOP/CST) das notas fiscais não canceladas.
     * Mesma base de TopMovimentacaoQuery::cfops → infográfico e tabela do dossiê reconciliam.
     */
    private function consolidadosQuery(Cliente $c): Builder
    {
        return DB::table('efd_notas_consolidados as c')
            ->join('efd_notas as n', 'n.id', '=', 'c.efd_nota_id')
            ->where('n.user_id', $c->user_id)
            ->where('n.cliente_id', $c->id)
            ->where('n.origem_arquivo', 'fiscal')
            ->where(fn ($q) => $q->whereNull('n.cancelada')->orWhere('n.cancelada', false));
    }

    public function kpis(Cliente $c): array
    {
        $rows = $this->notasQuery($c)
            ->selectRaw('tipo_operacao, count(*) as qtd, coalesce(sum(valor_total),0) as valor')
            ->groupBy('tipo_operacao')
            ->get()
            ->keyBy('tipo_operacao');

        $entQtd = (int) ($rows['entrada']->qtd ?? 0);
        $entVal = (float) ($rows['entrada']->valor ?? 0);
        $saiQtd = (int) ($rows['saida']->qtd ?? 0);
        $saiVal = (float) ($rows['saida']->valor ?? 0);

        $periodo = $this->notasQuery($c)
            ->whereNotNull('data_emissao')
            ->selectRaw("min(to_char(data_emissao,'YYYY-MM')) as ini, max(to_char(data_emissao,'YYYY-MM')) as fim")
            ->first();

        return [
            'total_notas' => $entQtd + $saiQtd,
            'valor_movimentado' => $entVal + $saiVal,
            'entradas_qtd' => $entQtd,
            'entradas_valor' => $entVal,
            'saidas_qtd' => $saiQtd,
            'saidas_valor' => $saiVal,
            'periodo_inicio' => $periodo->ini ?? null,
            'periodo_fim' => $periodo->fim ?? null,
        ];
    }

    public function porCompetencia(Cliente $c): array
    {
        $rows = $this->notasQuery($c)
            ->whereNotNull('data_emissao')
            ->selectRaw("to_char(data_emissao,'YYYY-MM') as comp, tipo_operacao, coalesce(sum(valor_total),0) as v")
            ->groupBy('comp', 'tipo_operacao')
            ->orderBy('comp')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $map[$r->comp] ??= ['competencia' => $r->comp, 'entrada' => 0.0, 'saida' => 0.0];
            if ($r->tipo_operacao === 'entrada') {
                $map[$r->comp]['entrada'] = (float) $r->v;
            } elseif ($r->tipo_operacao === 'saida') {
                $map[$r->comp]['saida'] = (float) $r->v;
            }
        }

        return array_values($map);
    }

    public function porCfop(Cliente $c, int $limite = 10): array
    {
        return $this->consolidadosQuery($c)
            ->whereNotNull('c.cfop')
            ->selectRaw('c.cfop as cfop, count(*) as qtd, coalesce(sum(c.valor_operacao),0) as valor')
            ->groupBy('c.cfop')
            ->orderByDesc('valor')
            ->limit($limite)
            ->get()
            ->map(fn ($r) => ['cfop' => (string) $r->cfop, 'qtd' => (int) $r->qtd, 'valor' => (float) $r->valor])
            ->all();
    }

    public function kpisEResumoParaPreview(Cliente $c): array
    {
        return [
            'kpis' => $this->kpis($c),
            'por_competencia' => $this->porCompetencia($c),
            'por_cfop' => $this->porCfop($c, 5),
        ];
    }

    public function porCst(Cliente $c): array
    {
        return $this->consolidadosQuery($c)
            ->selectRaw('c.cst_icms as cst, count(*) as qtd, coalesce(sum(c.valor_operacao),0) as valor')
            ->groupBy('c.cst_icms')
            ->orderByDesc('valor')
            ->get()
            ->map(fn ($r) => ['cst' => (string) $r->cst, 'qtd' => (int) $r->qtd, 'valor' => (float) $r->valor])
            ->all();
    }

    /**
     * ICMS/IPI vêm do C190 consolidado (fonte canônica; o item-level valor_icms fica ~zero na
     * EFD ICMS/IPI, o imposto está no registro C190). PIS/COFINS vêm dos itens da EFD de
     * contribuições. Alíquota média ponderada pela base ICMS (C190), sem diluir com PIS/COFINS.
     */
    public function impostos(Cliente $c): array
    {
        $r = $this->consolidadosQuery($c)
            ->selectRaw('
                coalesce(sum(c.valor_icms),0) as icms,
                coalesce(sum(c.valor_ipi),0) as ipi,
                coalesce(sum(c.aliquota_icms * c.valor_operacao),0) as aliq_peso,
                coalesce(sum(c.valor_operacao),0) as base
            ')
            ->first();

        $pc = DB::table('efd_notas_itens as i')
            ->join('efd_notas as n', 'n.id', '=', 'i.efd_nota_id')
            ->where('n.user_id', $c->user_id)
            ->where('n.cliente_id', $c->id)
            ->where('n.origem_arquivo', 'contribuicoes')
            ->where(fn ($q) => $q->whereNull('n.cancelada')->orWhere('n.cancelada', false))
            ->selectRaw('coalesce(sum(i.valor_pis),0) as pis, coalesce(sum(i.valor_cofins),0) as cofins')
            ->first();

        $base = (float) ($r->base ?? 0);

        return [
            'icms' => (float) ($r->icms ?? 0),
            'pis' => (float) ($pc->pis ?? 0),
            'cofins' => (float) ($pc->cofins ?? 0),
            'aliquota_icms_media' => $base > 0 ? round(((float) $r->aliq_peso) / $base, 2) : 0.0,
        ];
    }
}
