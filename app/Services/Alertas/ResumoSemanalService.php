<?php

namespace App\Services\Alertas;

use App\Models\Alerta;
use App\Models\ConsultaLote;
use App\Models\CteConsulta;
use App\Models\EfdImportacao;
use App\Models\NfeConsulta;
use App\Models\User;
use App\Models\XmlImportacao;
use Carbon\CarbonInterface;

/**
 * Monta o dado do resumo semanal de um usuário (7 dias). Separado da Notification
 * pra ser testável sem e-mail e reusável se um dia virar tela.
 */
class ResumoSemanalService
{
    /**
     * @return array{
     *   periodo_inicio: CarbonInterface, periodo_fim: CarbonInterface,
     *   por_severidade: array{alta:int, media:int, baixa:int},
     *   destaques: array<int, array{titulo:string, severidade:string, valor_risco:float, id:int}>,
     *   consultas: int, clearance: int, importacoes: int,
     *   vazio: bool
     * }
     */
    public function montar(User $user, CarbonInterface $inicio, CarbonInterface $fim): array
    {
        $alertas = Alerta::where('user_id', $user->id)
            ->whereBetween('created_at', [$inicio, $fim])
            ->get();

        $porSeveridade = [
            'alta' => $alertas->where('severidade', 'alta')->count(),
            'media' => $alertas->where('severidade', 'media')->count(),
            'baixa' => $alertas->where('severidade', 'baixa')->count(),
        ];

        $ordemSeveridade = ['alta' => 3, 'media' => 2, 'baixa' => 1];

        $destaques = $alertas
            ->sortByDesc(fn (Alerta $a) => [
                $ordemSeveridade[$a->severidade] ?? 0,
                (float) $a->valor_risco,
            ])
            ->take(5)
            ->map(fn (Alerta $a) => [
                'id' => $a->id,
                'titulo' => $a->titulo,
                'severidade' => $a->severidade,
                'valor_risco' => (float) $a->valor_risco,
            ])
            ->values()
            ->all();

        $consultas = ConsultaLote::where('user_id', $user->id)
            ->whereBetween('created_at', [$inicio, $fim])
            ->sum('total_participantes');

        $clearance = NfeConsulta::where('user_id', $user->id)
            ->whereBetween('consultado_em', [$inicio, $fim])->count()
            + CteConsulta::where('user_id', $user->id)
                ->whereBetween('consultado_em', [$inicio, $fim])->count();

        $importacoes = EfdImportacao::where('user_id', $user->id)
            ->whereBetween('created_at', [$inicio, $fim])->count()
            + XmlImportacao::where('user_id', $user->id)
                ->whereBetween('created_at', [$inicio, $fim])->count();

        $totalAlertas = array_sum($porSeveridade);

        return [
            'periodo_inicio' => $inicio,
            'periodo_fim' => $fim,
            'por_severidade' => $porSeveridade,
            'destaques' => $destaques,
            'consultas' => (int) $consultas,
            'clearance' => (int) $clearance,
            'importacoes' => (int) $importacoes,
            // Semana sem nada não vira e-mail — "nada aconteceu" é ruído, não notificação.
            'vazio' => $totalAlertas === 0 && (int) $consultas === 0 && $clearance === 0 && $importacoes === 0,
        ];
    }
}
