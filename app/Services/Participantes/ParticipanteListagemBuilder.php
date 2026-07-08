<?php

namespace App\Services\Participantes;

use App\Models\Participante;
use App\Services\Consultas\ParticipanteFiscalResumoService;
use Illuminate\Support\Carbon;

/**
 * Monta o panorama tabular ("de uma folha") dos participantes para o PDF de listagem.
 * Espelha o `ClienteListagemBuilder`, mas o volume vem de `resumoMovimentacao` — a fonte
 * única do papel/valor/qtd por participante (dedup P1 escopado via `dedupParticipanteSql`,
 * o mesmo número da ficha/dossiê/Score Fiscal). Escopo sempre por `user_id`.
 */
final class ParticipanteListagemBuilder
{
    private const REGULARIDADE_LABEL = [
        'regular' => 'Regular',
        'irregular' => 'Irregular',
        'indeterminada' => 'Indeterminada',
    ];

    private const PAPEL_LABEL = [
        'fornecedor' => 'Fornecedor',
        'cliente' => 'Cliente',
        'ambos' => 'Ambos',
    ];

    public function __construct(private ParticipanteFiscalResumoService $resumo) {}

    /**
     * @param  array<int,int|string>  $ids  participantes selecionados (filtrados por user_id)
     * @return array{participantes:array<int,array<string,mixed>>,total:int,total_movimentado:float,gerado_em:string}|null
     */
    public function montar(int $userId, array $ids): ?array
    {
        $idsLimpos = collect($ids)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        if ($idsLimpos->isEmpty()) {
            return null;
        }

        $participantes = Participante::where('user_id', $userId)
            ->excludingEmpresaPropria()
            ->whereIn('id', $idsLimpos)
            ->orderByRaw("COALESCE(razao_social, '') asc")
            ->get();

        if ($participantes->isEmpty()) {
            return null;
        }

        // Fonte única: papel + valor + qtd por participante (dedup P1 escopado).
        $resumoMov = $this->resumo->resumoMovimentacao($userId);
        // Fonte única de regularidade (CertidaoBadge por trás).
        $regularidadeMap = $this->resumo->regularidadePorParticipante($userId);

        $linhas = $participantes->map(function (Participante $p) use ($resumoMov, $regularidadeMap) {
            $mov = $resumoMov[$p->id] ?? null;
            $classe = $regularidadeMap[$p->id] ?? null;
            $papel = $mov['papel'] ?? null;

            return [
                'nome' => $p->razao_social ?: '—',
                'documento' => $p->documento_formatado,
                'uf' => $p->uf ?: '—',
                'situacao' => $p->situacao_cadastral ?: '—',
                'regime' => $p->regime_tributario ?: '—',
                'papel' => $papel ? (self::PAPEL_LABEL[$papel] ?? ucfirst($papel)) : 'Sem movimentação',
                'papel_classe' => $papel ?? 'sem_movimentacao',
                'movimentado' => (float) ($mov['valor'] ?? 0),
                'notas' => (int) ($mov['qtd'] ?? 0),
                'regularidade' => $classe ? (self::REGULARIDADE_LABEL[$classe] ?? ucfirst($classe)) : 'Não consultado',
                'regularidade_classe' => $classe ?? 'nao_consultado',
                'ultima_consulta' => $p->ultima_consulta_em ? Carbon::parse($p->ultima_consulta_em)->format('d/m/Y') : null,
            ];
        })->all();

        return [
            'participantes' => $linhas,
            'total' => count($linhas),
            'total_movimentado' => (float) collect($linhas)->sum('movimentado'),
            'gerado_em' => now()->format('d/m/Y H:i'),
        ];
    }
}
