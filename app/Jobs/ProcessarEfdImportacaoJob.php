<?php

namespace App\Jobs;

use App\Models\EfdImportacao;
use App\Services\Efd\Driver\ContribDriver;
use App\Services\Efd\Driver\FiscalDriver;
use App\Services\Efd\Driver\SpedDriver;
use App\Services\Efd\FinalizarImportacaoService;
use App\Services\Efd\ParticipanteResolver;
use App\Services\Efd\PersistenciaEngine;
use App\Services\Efd\ProgressoEmitter;
use App\Services\Efd\Sped\ContextWalker;
use App\Services\Efd\Sped\SpedParser;
use App\Services\SpedDetectorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Motor de importação EFD em Laravel — L4, esqueleto único (§L4/§10.6). Lê o SPED bruto
 * (`arquivo_base64`), roda parser → walker → driver → engine, resolve participantes,
 * integridade e finaliza. Trocar o driver (FiscalDriver → ContribDriver) cobre
 * PIS/COFINS sem tocar aqui. Fila `database` (worker existente).
 *
 * Disparado por `EfdImportacaoController::upload()` quando a flag `EFD_MOTOR`/`EFD_MOTOR_*`
 * seleciona o motor Laravel (default n8n). `timeout=900` exige `DB_QUEUE_RETRY_AFTER>900`
 * no `.env` pra fila não re-reservar um job longo. `failed()` fecha a importação em erro
 * quando o processo morre sem passar pelo catch.
 */
class ProcessarEfdImportacaoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 900;

    public function __construct(
        public int $importacaoId,
        public ?string $tabId = null,
    ) {}

    public function handle(
        PersistenciaEngine $engine,
        ParticipanteResolver $resolver,
        FinalizarImportacaoService $finalizador,
        SpedDetectorService $detector,
    ): void {
        $imp = EfdImportacao::find($this->importacaoId);
        if (! $imp) {
            Log::warning('ProcessarEfdImportacaoJob: importação não encontrada', [
                'importacao_id' => $this->importacaoId,
            ]);

            return;
        }

        // Heartbeat: ao começar a PROCESSAR (não ao enfileirar), reseta updated_at pro agora.
        // O reaper `importacao:expirar-travadas` mata `processando` com updated_at velho de
        // >15min; sem isto, um upload que esperou na fila atrás de um lote de consulta longo
        // seria marcado 'erro' no meio do processamento. Se o worker MORRER (OOM/kill), o
        // updated_at para de avançar e o reaper marca 'erro' corretamente — que é o certo.
        $imp->touch();

        $progresso = (new ProgressoEmitter)->para((int) $imp->user_id, $this->tabId, (int) $imp->id);

        try {
            $conteudo = $imp->conteudoSped();
            if ($conteudo === '') {
                throw new RuntimeException("arquivo_base64 ausente/vazio na importação #{$imp->id}");
            }

            // Guarda de tipo (defesa em profundidade): o upload() já valida a estrutura, já
            // deduplica o arquivo e já corrige tipo_efd pelo detector ANTES de criar a
            // importação. Mesmo assim o motor NUNCA confia no rótulo — escolhe o driver pelo
            // que o CONTEÚDO discrimina. Rodar EFD fiscal no fluxo de contribuições (ou
            // vice-versa) processaria com o driver errado e dropar A/F/M ou C/D em silêncio
            // (classe do bug UTIDA). Se divergir, corrige e segue pro fluxo certo.
            $tipoEfd = $this->resolverTipo($imp, $conteudo, $detector);

            $driver = $this->driverPara($tipoEfd);
            // Factory de stream FRESCO: a engine caminha o arquivo em 3 passadas (streaming,
            // sem acumular ~50k linhas em memória). SpedParser é generator — não rebobina —
            // então cada passada recria parser+walker sobre o mesmo conteúdo.
            $paresFactory = fn (): iterable => (new ContextWalker)->walk((new SpedParser)->stream($conteudo));

            $engine->executar($imp, $driver, $paresFactory, $progresso);
            $resolver->resolver($imp);
            $finalizador->finalizar($imp, (int) $imp->user_id, $this->tabId);
        } catch (Throwable $e) {
            $imp->update(['status' => 'erro']);
            $progresso->erro($e->getMessage());
            Log::error('ProcessarEfdImportacaoJob falhou', [
                'importacao_id' => $imp->id,
                'user_id' => $imp->user_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Chamado pela fila quando o job falha de vez (esgotou tries, timeout, ou o processo
     * MORREU — OOM/kill — em que o catch inline do handle nunca roda). Sem isto a importação
     * ficaria presa em 'processando' para sempre. Espelha ProcessarXmlImportacaoJob.
     */
    public function failed(Throwable $e): void
    {
        $imp = EfdImportacao::find($this->importacaoId);
        if ($imp && $imp->status !== 'concluido') {
            $imp->update(['status' => 'erro']);
            (new ProgressoEmitter)->para((int) $imp->user_id, $this->tabId, (int) $imp->id)
                ->erro('A importação falhou durante o processamento.');
        }

        Log::error('ProcessarEfdImportacaoJob: failed()', [
            'importacao_id' => $this->importacaoId,
            'error' => $e->getMessage(),
        ]);
    }

    /**
     * Tipo confiável = o que o CONTEÚDO discrimina (A100/A170/F600/bloco M/0110 → contribuições;
     * C190/E1xx/D1xx → fiscal — mutuamente exclusivos), não o rótulo do upload. Diverge →
     * corrige tipo_efd na importação, loga o redirect e roteia pro driver certo. Detector
     * sem veredito (SPED sem discriminadores) → mantém o rótulo (o upload já validou a
     * estrutura); o driver ainda tolera ausência de blocos.
     */
    private function resolverTipo(EfdImportacao $imp, string $conteudo, SpedDetectorService $detector): string
    {
        $detectado = $detector->detectar($conteudo)['tipo'];
        $atual = (string) $imp->tipo_efd;

        if ($detectado !== null && $detectado !== $atual) {
            Log::warning('ProcessarEfdImportacaoJob: tipo_efd corrigido pelo conteúdo (redirect de fluxo)', [
                'importacao_id' => $imp->id,
                'user_id' => $imp->user_id,
                'rotulo_upload' => $atual,
                'detectado' => $detectado,
            ]);
            $imp->update(['tipo_efd' => $detectado]);

            return $detectado;
        }

        return $detectado ?? $atual;
    }

    private function driverPara(string $tipoEfd): SpedDriver
    {
        return match ($tipoEfd) {
            'EFD ICMS/IPI' => new FiscalDriver,
            'EFD PIS/COFINS' => new ContribDriver,
            default => throw new RuntimeException("Motor Laravel não cobre tipo_efd '{$tipoEfd}'."),
        };
    }
}
