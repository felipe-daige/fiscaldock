<?php

namespace App\Console\Commands;

use App\Models\ConsultaResultado;
use App\Models\CteConsulta;
use App\Models\NfeConsulta;
use App\Services\Consultas\ComprovanteArquivador;
use Illuminate\Console\Command;

/**
 * Backfill de nomes de comprovantes arquivados antes do rótulo descritivo:
 * renomeia "{ulid}.{ext}" para "{rotulo}__{ulid}.{ext}" e atualiza o path
 * persistido no JSONB (resultado_dados / payload).
 */
class RotularComprovantesLegadoCommand extends Command
{
    /** @var string */
    protected $signature = 'comprovantes:rotular-legado
        {--user= : Restringe a um user_id}
        {--dry-run : Apenas contabiliza arquivos renomeáveis}';

    /** @var string */
    protected $description = 'Renomeia comprovantes legados (só-ULID) com rótulo descritivo no nome do arquivo';

    private int $renomeados = 0;

    private int $renomeaveisDryRun = 0;

    private bool $dryRun = false;

    private ?int $userId = null;

    public function handle(ComprovanteArquivador $arquivador): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $this->userId = $this->option('user') !== null ? (int) $this->option('user') : null;

        $this->processarResultadosCnpj($arquivador);
        $this->processarSnapshots(NfeConsulta::class, $arquivador);
        $this->processarSnapshots(CteConsulta::class, $arquivador);

        if ($this->dryRun) {
            $this->line('Renomeáveis (dry-run): '.$this->renomeaveisDryRun);
        } else {
            $this->line('Renomeados: '.$this->renomeados);
        }

        return self::SUCCESS;
    }

    private function processarResultadosCnpj(ComprovanteArquivador $arquivador): void
    {
        $query = ConsultaResultado::query()
            ->with(['lote:id,user_id', 'participante:id,documento', 'cliente:id,documento'])
            ->whereNotNull('resultado_dados');

        if ($this->userId !== null) {
            $query->whereHas('lote', fn ($q) => $q->where('user_id', $this->userId));
        }

        foreach ($query->lazyById() as $resultado) {
            $dados = is_array($resultado->resultado_dados) ? $resultado->resultado_dados : [];
            $alterado = false;

            foreach ($dados as $fonte => $bloco) {
                if (! is_array($bloco) || empty($bloco['comprovante_arquivo'])) {
                    continue;
                }

                $novoPath = $this->renomear(
                    $arquivador,
                    (string) $bloco['comprovante_arquivo'],
                    ComprovanteArquivador::rotuloFonte(
                        (string) $fonte,
                        $resultado->participante?->documento ?? $resultado->cliente?->documento,
                    ),
                );
                if ($novoPath !== null) {
                    $dados[$fonte]['comprovante_arquivo'] = $novoPath;
                    $alterado = true;
                }
            }

            if ($alterado) {
                $resultado->resultado_dados = $dados;
                $resultado->save();
            }
        }
    }

    /** @param class-string<NfeConsulta|CteConsulta> $model */
    private function processarSnapshots(string $model, ComprovanteArquivador $arquivador): void
    {
        $query = $model::query()->whereNotNull('payload');
        if ($this->userId !== null) {
            $query->where('user_id', $this->userId);
        }

        foreach ($query->lazyById() as $snapshot) {
            $payload = is_array($snapshot->payload) ? $snapshot->payload : [];
            $arquivos = (array) ($payload['comprovantes_arquivos'] ?? []);
            $alterado = false;

            foreach ($arquivos as $tipo => $path) {
                if (! is_string($path) || $path === '') {
                    continue;
                }

                $novoPath = $this->renomear(
                    $arquivador,
                    $path,
                    ComprovanteArquivador::rotuloDocumento(
                        $model === CteConsulta::class ? 'CTE' : 'NFE',
                        (string) $snapshot->chave_acesso,
                        (string) $tipo,
                    ),
                );
                if ($novoPath !== null) {
                    $arquivos[$tipo] = $novoPath;
                    $alterado = true;
                }
            }

            if ($alterado) {
                $payload['comprovantes_arquivos'] = $arquivos;
                $snapshot->payload = $payload;
                $snapshot->save();
            }
        }
    }

    private function renomear(ComprovanteArquivador $arquivador, string $path, string $rotulo): ?string
    {
        if ($this->dryRun) {
            if (ComprovanteArquivador::rotuloDePath($path) === null
                && \Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
                $this->renomeaveisDryRun++;
            }

            return null;
        }

        $novoPath = $arquivador->renomearComRotulo($path, $rotulo);
        if ($novoPath !== null) {
            $this->renomeados++;
        }

        return $novoPath;
    }
}
