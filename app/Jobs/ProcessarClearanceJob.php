<?php

namespace App\Jobs;

use App\Models\CteConsulta;
use App\Models\NfeConsulta;
use App\Services\Clearance\Sefaz\ContextoPersistencia;
use App\Services\Clearance\Sefaz\DocumentoConsultaService;
use App\Services\Clearance\Sefaz\SnapshotPersister;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ProcessarClearanceJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $loteId,
        public string $chave,
        public string $tipoDocumento, // 'nfe' | 'cte'
        public int $userId,
        public string $tabId,
        public ?int $clienteId,
        public int $custoCreditos,
        public int $indice = 1,
        public int $total = 1,
    ) {}

    public function handle(DocumentoConsultaService $svc, SnapshotPersister $persister): void
    {
        $resumo = strlen($this->chave) === 44 ? substr($this->chave, 0, 6).'...'.substr($this->chave, 40) : $this->chave;

        // Progresso ANTES da chamada lenta (barra honesta; % global monotônico por índice/total).
        $pct = (int) round((($this->indice - 1) / max(1, $this->total)) * 100);
        $this->progresso($pct, "Consultando documento {$resumo} ({$this->indice} de {$this->total})");

        // Idempotência: chave já persistida neste lote → não re-consultar nem re-cobrar.
        if ($this->jaPersistida()) {
            return;
        }

        $snapshot = $svc->consultar($this->chave, $this->tipoDocumento, $this->clienteId);

        if ($snapshot->persistivel) {
            $persister->upsert($snapshot, new ContextoPersistencia(
                userId: $this->userId,
                clienteId: $this->clienteId,
                consultaLoteId: $this->loteId,
                custo: (float) $this->custoCreditos,
            ));
        }

        // Estorno por doc (overwrite = idempotente em retry do job). Somado por FecharClearanceLoteService.
        Cache::put(
            "clearance_estorno:{$this->loteId}:{$this->chave}",
            $snapshot->estornavel ? $this->custoCreditos : 0,
            86400
        );
    }

    private function jaPersistida(): bool
    {
        $model = strtolower($this->tipoDocumento) === 'cte' ? CteConsulta::class : NfeConsulta::class;

        return $model::where('user_id', $this->userId)
            ->where('chave_acesso', $this->chave)
            ->where('consulta_lote_id', $this->loteId)
            ->exists();
    }

    private function progresso(int $pct, string $mensagem): void
    {
        Cache::put("progresso:{$this->userId}:{$this->tabId}", [
            'tab_id' => $this->tabId,
            'etapa' => 2,
            'total_etapas' => 2,
            'etapa_label' => 'Consultando SEFAZ',
            'status' => 'processando',
            'progresso' => $pct,
            'mensagem' => $mensagem,
        ], 600);
    }
}
