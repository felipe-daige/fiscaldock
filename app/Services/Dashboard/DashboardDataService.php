<?php

namespace App\Services\Dashboard;

use App\Models\XmlNota;
use App\Models\Participante;
use App\Models\User;
use App\Services\CreditService;
use App\Services\RiskScoreService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DashboardDataService
{
    public function __construct(
        protected RiskScoreService $riskScoreService,
        protected CreditService $creditService
    ) {}

    /**
     * Obtém todos os KPIs do dashboard para um usuário.
     */
    public function getKpis(int $userId, User $user): array
    {
        return [
            'conformidade' => $this->getConformidadePercent($userId),
            'impostos_recuperaveis' => $this->getImpostosRecuperaveis($userId),
            'creditos' => $this->creditService->getBalance($user),
            'alertas_criticos' => $this->getAlertasCriticos($userId),
        ];
    }

    /**
     * Calcula % de participantes com baixo risco (classificacao = 'baixo').
     */
    public function getConformidadePercent(int $userId): float
    {
        $estatisticas = $this->riskScoreService->getEstatisticas($userId);

        $totalAvaliados = $estatisticas['total_avaliados'] ?? 0;
        $baixoRisco = $estatisticas['baixo_risco'] ?? 0;

        if ($totalAvaliados === 0) {
            return 0;
        }

        return round(($baixoRisco / $totalAvaliados) * 100, 1);
    }

    /**
     * Calcula SUM(pis_valor + cofins_valor) de notas entrada (tipo_nota=0), excluindo devoluções.
     */
    public function getImpostosRecuperaveis(int $userId): float
    {
        $total = XmlNota::where('user_id', $userId)
            ->where('tipo_nota', XmlNota::TIPO_ENTRADA)
            ->where('finalidade', '!=', XmlNota::FINALIDADE_DEVOLUCAO)
            ->select(DB::raw('SUM(COALESCE(pis_valor, 0) + COALESCE(cofins_valor, 0)) as total'))
            ->value('total');

        return (float) ($total ?? 0);
    }

    /**
     * Conta alertas críticos:
     * 1. Participantes com situacao_cadastral NOT IN ('ATIVA', '', null)
     * 2. Notas com alertas bloqueantes (validacao->'alertas' WHERE nivel='bloqueante')
     */
    public function getAlertasCriticos(int $userId): int
    {
        // Participantes com situação irregular
        $participantesIrregulares = Participante::where('user_id', $userId)
            ->whereNotNull('situacao_cadastral')
            ->where('situacao_cadastral', '!=', '')
            ->whereNotIn('situacao_cadastral', ['ATIVA', 'ativa'])
            ->count();

        // Notas com alertas bloqueantes
        $notasBloqueantes = XmlNota::where('user_id', $userId)
            ->whereNotNull('validacao')
            ->whereRaw("EXISTS (SELECT 1 FROM jsonb_array_elements(validacao->'alertas') AS a WHERE a->>'nivel' = 'bloqueante')")
            ->count();

        return $participantesIrregulares + $notasBloqueantes;
    }

    /**
     * Retorna participantes paginados com score, ordenados por risco (mais arriscados primeiro).
     */
    public function getParticipantesPaginados(int $userId, ?string $busca = null, int $perPage = 20): LengthAwarePaginator
    {
        return Participante::where('participantes.user_id', $userId)
            ->with('score')
            ->leftJoin('participante_scores', 'participantes.id', '=', 'participante_scores.participante_id')
            ->select('participantes.*')
            ->when($busca, function ($q) use ($busca) {
                $busca = trim($busca);
                // Remove caracteres especiais para busca por CNPJ
                $cnpjLimpo = preg_replace('/[^0-9]/', '', $busca);

                $q->where(function ($q) use ($busca, $cnpjLimpo) {
                    $q->where('participantes.cnpj', 'like', "%{$cnpjLimpo}%")
                        ->orWhere('participantes.razao_social', 'ilike', "%{$busca}%")
                        ->orWhere('participantes.nome_fantasia', 'ilike', "%{$busca}%");
                });
            })
            ->orderByDesc('participante_scores.score_total')
            ->orderBy('participantes.razao_social')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Retorna o total de participantes do usuário.
     */
    public function getTotalParticipantes(int $userId): int
    {
        return Participante::where('user_id', $userId)->count();
    }
}
