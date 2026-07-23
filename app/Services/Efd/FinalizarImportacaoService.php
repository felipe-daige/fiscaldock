<?php

namespace App\Services\Efd;

use App\Models\EfdImportacao;
use App\Services\EfdAuditoriaService;
use App\Services\EfdResumoBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Finaliza uma importação EFD: constrói o `resumo_final` a partir do banco (single
 * source of truth), roda o guardrail de integridade (SPED bruto × banco), persiste na
 * importação e atualiza o cache do SSE pra UI fechar. §L4/§10.6 passo 6.
 *
 * Miolo da finalização usado pelo motor Laravel (`ProcessarEfdImportacaoJob`).
 */
class FinalizarImportacaoService
{
    public function __construct(
        private EfdResumoBuilder $builder,
        private EfdAuditoriaService $auditoria,
    ) {}

    /**
     * @return array<string, mixed> o resumo_final gravado (com `.integridade`)
     */
    public function finalizar(EfdImportacao $imp, int $userId, ?string $tabId): array
    {
        $resumo = $this->builder->build($imp);
        $tempoSegundos = $imp->iniciado_em
            ? (int) $imp->iniciado_em->diffInSeconds(now())
            : null;

        // Guardrail universal: toda importação se autoverifica contra o SPED bruto. Se o
        // pipeline dropou notas (ex.: Merge C100↔0150 soltando NFC-e — bug UTIDA), a
        // importação NÃO fica "concluído" mudo: grava o veredito em resumo_final.integridade
        // e loga. Barato (só conta chaves), degrada seguro sem arquivo retido.
        $integridade = $this->auditoria->integridade($imp);
        $resumo['integridade'] = $integridade;
        if (! $integridade['ok']) {
            Log::warning('EFD import: notas do SPED ausentes no banco (pipeline dropou)', [
                'importacao_id' => $imp->id,
                'user_id' => $imp->user_id,
                'tipo_efd' => $imp->tipo_efd,
                'esperadas' => $integridade['esperadas'],
                'faltando' => $integridade['faltando'],
                'amostra' => $integridade['amostra_faltando'],
            ]);
        }

        $est = $resumo['estatisticas'] ?? [];
        $imp->update([
            'status' => 'concluido',
            'resumo_final' => $resumo,
            'concluido_em' => now(),
            'tempo_processamento_segundos' => $tempoSegundos,
            'total_participantes' => $est['total_participantes_processados'] ?? 0,
            'total_cnpjs_unicos' => $est['total_cnpjs_unicos'] ?? 0,
            'total_cpfs_unicos' => $est['total_cpfs_unicos'] ?? 0,
            'novos' => $est['participantes_novos'] ?? 0,
            'duplicados' => $est['participantes_repetidos'] ?? 0,
            'total_notas' => $est['total_notas_processadas'] ?? 0,
            'notas_extraidas' => $est['notas_novas'] ?? 0,
            'participante_ids' => $resumo['participante_ids'] ?? [],
        ]);

        // Fecha o SSE (a aba que subiu o arquivo). Sem tab_id (cutover em background), pula.
        if ($tabId !== null && $tabId !== '') {
            $cacheKey = "progresso:{$userId}:{$tabId}";
            $existing = Cache::get($cacheKey, []);
            Cache::put($cacheKey, array_merge($existing, [
                'user_id' => $userId,
                'tab_id' => $tabId,
                'importacao_id' => $imp->id,
                'status' => 'concluido',
                'progresso' => 100,
                'mensagem' => $resumo['mensagem'] ?? 'Importação concluída.',
                'resumo_final' => $resumo,
                'updated_at' => now()->toIso8601String(),
            ]), 600);
        }

        return $resumo;
    }
}
