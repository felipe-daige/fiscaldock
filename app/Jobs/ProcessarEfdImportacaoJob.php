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
 * Não é disparado em produção ainda — o cutover no `EfdImportacaoController::upload()`
 * atrás da flag `EFD_MOTOR` é F4. Aqui só existe + é exercido pelo e2e.
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
    ): void {
        $imp = EfdImportacao::find($this->importacaoId);
        if (! $imp) {
            Log::warning('ProcessarEfdImportacaoJob: importação não encontrada', [
                'importacao_id' => $this->importacaoId,
            ]);

            return;
        }

        $progresso = (new ProgressoEmitter)->para((int) $imp->user_id, $this->tabId, (int) $imp->id);

        try {
            $conteudo = $this->lerConteudo($imp);
            if ($conteudo === '') {
                throw new RuntimeException("arquivo_base64 ausente/vazio na importação #{$imp->id}");
            }

            $driver = $this->driverPara((string) $imp->tipo_efd);
            $pares = (new ContextWalker)->walk((new SpedParser)->stream($conteudo));

            $engine->executar($imp, $driver, $pares, $progresso);
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

    private function driverPara(string $tipoEfd): SpedDriver
    {
        return match ($tipoEfd) {
            'EFD ICMS/IPI' => new FiscalDriver,
            'EFD PIS/COFINS' => new ContribDriver,
            default => throw new RuntimeException("Motor Laravel não cobre tipo_efd '{$tipoEfd}'."),
        };
    }

    /** SPED bruto de `arquivo_base64` (string JSON-encoded — mesmo contrato do auditar). */
    private function lerConteudo(EfdImportacao $imp): string
    {
        $raw = $imp->arquivo_base64;
        if (! $raw) {
            return '';
        }
        $decoded = json_decode($raw, true);

        return is_string($decoded) ? $decoded : (string) $raw;
    }
}
