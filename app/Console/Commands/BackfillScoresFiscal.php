<?php

namespace App\Console\Commands;

use App\Models\ConsultaResultado;
use App\Services\RiskScoreService;
use Illuminate\Console\Command;

/**
 * Backfill do Score Fiscal a partir das consultas JÁ realizadas.
 *
 * Contexto: o score só passa a ser persistido (`FecharLoteService::persistirScores`) nos lotes
 * fechados a partir da entrega do Score Fiscal (2026-06-06). As consultas anteriores têm
 * `resultado_dados` em `consulta_resultados` mas nunca geraram linha em `participante_scores`,
 * então aparecem como "não consultados" na tela. Este comando reprocessa TODOS os resultados
 * em ordem cronológica — cada um mescla suas categorias sobre as anteriores (ver
 * RiskScoreService::persistirScore), então uma consulta parcial (só cadastro) não apaga
 * certidões avaliadas antes. Idempotente (updateOrCreate por participante).
 *
 * Clientes (resultado com `cliente_id`) ficam de fora — `participante_scores` é por participante.
 */
class BackfillScoresFiscal extends Command
{
    protected $signature = 'score:backfill
        {--user= : Restringe a um user_id}
        {--dry-run : Não grava, só relata}';

    protected $description = 'Recalcula e persiste o Score Fiscal a partir das consultas já realizadas (consulta_resultados).';

    public function handle(RiskScoreService $riskScoreService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $userOpt = $this->option('user') !== null ? (int) $this->option('user') : null;
        $prefixo = $dryRun ? '[dry-run] ' : '';

        // PARTICIPANTES — histórico completo, do mais antigo ao mais novo (a chave mais
        // recente prevalece no merge do persistirScore).
        $qParticipantes = ConsultaResultado::query()
            ->whereNotNull('participante_id')
            ->whereNotNull('resultado_dados')
            ->where('status', ConsultaResultado::STATUS_SUCESSO)
            ->with('participante')
            ->orderBy('consultado_em')
            ->orderBy('id');

        if ($userOpt !== null) {
            $qParticipantes->whereHas('participante', fn ($q) => $q->where('user_id', $userOpt));
        }

        $vistos = [];
        foreach ($qParticipantes->cursor() as $resultado) {
            if (! $resultado->participante) {
                continue;
            }
            $vistos[$resultado->participante_id] = true;

            if (! $dryRun) {
                $riskScoreService->atualizarScore($resultado->participante, (array) $resultado->resultado_dados);
            }
        }
        $participantes = count($vistos);

        // CLIENTES — histórico completo, mesma ordem cronológica.
        $qClientes = ConsultaResultado::query()
            ->whereNotNull('cliente_id')
            ->whereNotNull('resultado_dados')
            ->where('status', ConsultaResultado::STATUS_SUCESSO)
            ->with('cliente')
            ->orderBy('consultado_em')
            ->orderBy('id');

        if ($userOpt !== null) {
            $qClientes->whereHas('cliente', fn ($q) => $q->where('user_id', $userOpt));
        }

        $vistosCliente = [];
        foreach ($qClientes->cursor() as $resultado) {
            if (! $resultado->cliente) {
                continue;
            }
            $vistosCliente[$resultado->cliente_id] = true;

            if (! $dryRun) {
                $riskScoreService->atualizarScoreCliente($resultado->cliente, (array) $resultado->resultado_dados);
            }
        }
        $clientes = count($vistosCliente);

        $this->info("{$prefixo}Scores recalculados — participantes: {$participantes}, clientes: {$clientes}");

        return self::SUCCESS;
    }
}
