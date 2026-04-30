<?php

namespace App\Services\Clearance\Comparacao;

use App\Models\XmlNota;
use Illuminate\Support\Facades\DB;

/**
 * Indicadores agregados de divergência declarado vs SEFAZ.
 * Lê dos campos persistidos em xml_notas (populados pelo XmlNotaSefazSyncObserver),
 * sem rodar ComparacaoNotaService linha a linha.
 */
class DivergenciaIndicadoresService
{
    /**
     * @return array{
     *   total_com_snapshot: int,
     *   ok: int,
     *   revisar: int,
     *   critica: int,
     *   valor_exposto: float,
     *   total_geral: int,
     *   sem_snapshot: int,
     * }
     */
    public function resumo(int $userId): array
    {
        $row = DB::table('xml_notas')
            ->where('user_id', $userId)
            ->selectRaw("
                COUNT(*)                                                                          as total_geral,
                COUNT(*) FILTER (WHERE situacao_sefaz IS NOT NULL)                                as total_com_snapshot,
                COUNT(*) FILTER (WHERE divergencia_severidade = ?)                                as ok,
                COUNT(*) FILTER (WHERE divergencia_severidade = ?)                                as revisar,
                COUNT(*) FILTER (WHERE divergencia_severidade = ?)                                as critica,
                COALESCE(SUM(valor_total) FILTER (WHERE divergencia_severidade = ?), 0)::numeric  as valor_exposto
            ", [
                XmlNota::DIVERGENCIA_OK,
                XmlNota::DIVERGENCIA_REVISAR,
                XmlNota::DIVERGENCIA_CRITICA,
                XmlNota::DIVERGENCIA_CRITICA,
            ])
            ->first();

        return [
            'total_geral' => (int) ($row->total_geral ?? 0),
            'total_com_snapshot' => (int) ($row->total_com_snapshot ?? 0),
            'sem_snapshot' => (int) (($row->total_geral ?? 0) - ($row->total_com_snapshot ?? 0)),
            'ok' => (int) ($row->ok ?? 0),
            'revisar' => (int) ($row->revisar ?? 0),
            'critica' => (int) ($row->critica ?? 0),
            'valor_exposto' => (float) ($row->valor_exposto ?? 0),
        ];
    }

    /**
     * Top emitentes ranqueados por número de divergências (CRITICA + REVISAR).
     *
     * @return array<int, array{cnpj: string, razao_social: ?string, divergencias: int, valor_exposto: float, criticas: int}>
     */
    public function topEmitentes(int $userId, int $limite = 5): array
    {
        return DB::table('xml_notas')
            ->where('user_id', $userId)
            ->whereIn('divergencia_severidade', [XmlNota::DIVERGENCIA_REVISAR, XmlNota::DIVERGENCIA_CRITICA])
            ->whereNotNull('emit_cnpj')
            ->selectRaw("
                emit_cnpj                                                              as cnpj,
                MAX(emit_razao_social)                                                  as razao_social,
                COUNT(*)                                                                as divergencias,
                COUNT(*) FILTER (WHERE divergencia_severidade = ?)                       as criticas,
                COALESCE(SUM(valor_total) FILTER (WHERE divergencia_severidade = ?), 0)  as valor_exposto
            ", [XmlNota::DIVERGENCIA_CRITICA, XmlNota::DIVERGENCIA_CRITICA])
            ->groupBy('emit_cnpj')
            ->orderByDesc('divergencias')
            ->orderByDesc('valor_exposto')
            ->limit($limite)
            ->get()
            ->map(fn ($row) => [
                'cnpj' => $row->cnpj,
                'razao_social' => $row->razao_social,
                'divergencias' => (int) $row->divergencias,
                'criticas' => (int) $row->criticas,
                'valor_exposto' => (float) $row->valor_exposto,
            ])
            ->all();
    }

    /**
     * Lista as notas críticas mais recentes (pra alertas e detalhamento).
     *
     * @return array<int, array{id: int, chave: string, numero: ?int, emit_razao_social: ?string, divergencia_count: ?int, valor_total: float, comparado_em: ?string}>
     */
    public function notasCriticas(int $userId, int $limite = 10): array
    {
        return DB::table('xml_notas')
            ->where('user_id', $userId)
            ->where('divergencia_severidade', XmlNota::DIVERGENCIA_CRITICA)
            ->orderByDesc('comparado_em')
            ->limit($limite)
            ->get(['id', 'nfe_id as chave', 'numero_nota as numero', 'emit_razao_social', 'divergencia_count', 'valor_total', 'comparado_em', 'situacao_sefaz'])
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'chave' => $row->chave,
                'numero' => $row->numero,
                'emit_razao_social' => $row->emit_razao_social,
                'divergencia_count' => $row->divergencia_count !== null ? (int) $row->divergencia_count : null,
                'valor_total' => (float) $row->valor_total,
                'comparado_em' => $row->comparado_em,
                'situacao_sefaz' => $row->situacao_sefaz,
            ])
            ->all();
    }
}
