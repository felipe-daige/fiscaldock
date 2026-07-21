<?php

namespace App\Services\Participantes;

use App\Models\EfdNota;
use App\Models\Participante;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Agrega movimentações fiscais (EFD) de UM participante, escopado por user_id.
 * Exclui notas canceladas. XML fora do MVP (depende de xml_notas_itens).
 */
final class ParticipanteMovimentacaoService
{
    /**
     * Notas EFD do participante, não canceladas, DEDUPLICADAS por origem com a regra
     * canônica do BI (P1 escopado ao participante). Fonte única: BiService::dedupParticipanteSql
     * — o dossiê PDF converge com a ficha `/app/participante` (a NF-e escriturada nas duas EFD
     * não dobra; documentos só-contribuições, ex. NFS-e de serviço, entram uma vez).
     */
    private function notasQuery(Participante $p): \Illuminate\Database\Eloquent\Builder
    {
        return EfdNota::query()
            ->where('user_id', $p->user_id)
            ->where('participante_id', $p->id)
            ->where('cancelada', false)
            ->whereRaw(\App\Services\BiService::dedupParticipanteSql('efd_notas'));
    }

    /**
     * Consolidado C190 (fonte canônica de ICMS/IPI/CFOP/CST) das notas fiscais não canceladas.
     * Mesma base de TopMovimentacaoQuery::cfops → infográfico e tabela do dossiê reconciliam.
     */
    private function consolidadosQuery(Participante $p): Builder
    {
        return DB::table('efd_notas_consolidados as c')
            ->join('efd_notas as n', 'n.id', '=', 'c.efd_nota_id')
            ->where('n.user_id', $p->user_id)
            ->where('n.participante_id', $p->id)
            ->where('n.origem_arquivo', 'fiscal')
            ->where(fn ($q) => $q->whereNull('n.cancelada')->orWhere('n.cancelada', false));
    }

    public function kpis(Participante $p): array
    {
        $rows = $this->notasQuery($p)
            ->selectRaw('tipo_operacao, count(*) as qtd, coalesce(sum(valor_total),0) as valor')
            ->groupBy('tipo_operacao')
            ->get()
            ->keyBy('tipo_operacao');

        $entQtd = (int) ($rows['entrada']->qtd ?? 0);
        $entVal = (float) ($rows['entrada']->valor ?? 0);
        $saiQtd = (int) ($rows['saida']->qtd ?? 0);
        $saiVal = (float) ($rows['saida']->valor ?? 0);

        $periodo = $this->notasQuery($p)
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

    /**
     * Entradas/saídas (qtd + valor) e totais de VÁRIOS participantes numa query só —
     * mesma base e dedup do kpis() individual (fiscal, não canceladas, dedup cross-EFD).
     * Para o export "todos os participantes" do BI sem cair em N queries. Chave =
     * participante_id; ids sem nota ficam ausentes (o caller aplica zero).
     *
     * @param  array<int, int>  $ids
     * @return array<int, array{total_notas:int, valor_movimentado:float, entradas_qtd:int, entradas_valor:float, saidas_qtd:int, saidas_valor:float}>
     */
    public function kpisEmLote(int $userId, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return EfdNota::query()
            ->where('user_id', $userId)
            ->whereIn('participante_id', $ids)
            ->where('cancelada', false)
            ->whereRaw(\App\Services\BiService::dedupParticipanteSql('efd_notas'))
            ->groupBy('participante_id')
            ->selectRaw("
                participante_id,
                count(*) filter (where tipo_operacao = 'entrada') as ent_qtd,
                coalesce(sum(valor_total) filter (where tipo_operacao = 'entrada'), 0) as ent_val,
                count(*) filter (where tipo_operacao = 'saida') as sai_qtd,
                coalesce(sum(valor_total) filter (where tipo_operacao = 'saida'), 0) as sai_val
            ")
            ->get()
            ->keyBy('participante_id')
            ->map(fn ($r) => [
                'total_notas' => (int) $r->ent_qtd + (int) $r->sai_qtd,
                'valor_movimentado' => (float) $r->ent_val + (float) $r->sai_val,
                'entradas_qtd' => (int) $r->ent_qtd,
                'entradas_valor' => (float) $r->ent_val,
                'saidas_qtd' => (int) $r->sai_qtd,
                'saidas_valor' => (float) $r->sai_val,
            ])
            ->all();
    }

    public function porCompetencia(Participante $p): array
    {
        $rows = $this->notasQuery($p)
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

    public function porCfop(Participante $p, int $limite = 10): array
    {
        return $this->consolidadosQuery($p)
            ->whereNotNull('c.cfop')
            ->selectRaw('c.cfop as cfop, count(*) as qtd, coalesce(sum(c.valor_operacao),0) as valor')
            ->groupBy('c.cfop')
            ->orderByDesc('valor')
            ->limit($limite)
            ->get()
            ->map(fn ($r) => ['cfop' => (string) $r->cfop, 'qtd' => (int) $r->qtd, 'valor' => (float) $r->valor])
            ->all();
    }

    public function kpisEResumoParaPreview(Participante $p): array
    {
        return [
            'kpis' => $this->kpis($p),
            'por_competencia' => $this->porCompetencia($p),
            'por_cfop' => $this->porCfop($p, 5),
        ];
    }

    public function porCst(Participante $p): array
    {
        return $this->consolidadosQuery($p)
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
    public function impostos(Participante $p): array
    {
        $r = $this->consolidadosQuery($p)
            ->selectRaw('
                coalesce(sum(c.valor_icms),0) as icms,
                coalesce(sum(c.valor_ipi),0) as ipi,
                coalesce(sum(c.aliquota_icms * c.valor_operacao),0) as aliq_peso,
                coalesce(sum(c.valor_operacao),0) as base
            ')
            ->first();

        $pc = DB::table('efd_notas_itens as i')
            ->join('efd_notas as n', 'n.id', '=', 'i.efd_nota_id')
            ->where('n.user_id', $p->user_id)
            ->where('n.participante_id', $p->id)
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
