<?php

namespace App\Jobs;

use App\Models\CteConsulta;
use App\Models\NfeConsulta;
use App\Services\Clearance\ParticipanteAutoCadastroService;
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
        public float $custoCreditos,
        public int $indice = 1,
        public int $total = 1,
        // Faixa da barra que ESTA fase ocupa. No clearance completo os documentos vão de 0 a 50 e a
        // regularidade das contrapartes ocupa 50→95 (o 100 vem do 'finalizado'). Sem tier full, os
        // documentos usam a faixa inteira.
        public int $pctSpan = 95,
        // Trilha de etapas do lote (ClearanceEtapas): 2 no básico, 5 no completo. Os documentos são
        // SEMPRE a etapa 2 — o strip da tela usa isso pra marcar a etapa corrente pelo número.
        public int $totalEtapas = 2,
    ) {}

    public function handle(DocumentoConsultaService $svc, SnapshotPersister $persister): void
    {
        $resumo = strlen($this->chave) === 44 ? substr($this->chave, 0, 6).'...'.substr($this->chave, 40) : $this->chave;

        // Progresso ANTES da chamada lenta (a consulta SEFAZ leva dezenas de segundos e pode
        // retentar): a barra mostra o documento em andamento em vez de ficar parada.
        $this->progresso(
            $this->pct($this->indice - 1),
            "Consultando documento {$resumo} ({$this->indice} de {$this->total})"
        );

        // Idempotência: chave já persistida neste lote → não re-consultar nem re-cobrar.
        if ($this->jaPersistida()) {
            $this->progresso($this->pct($this->indice), "Documento {$resumo} já consultado ({$this->indice} de {$this->total})");

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

            // Contrapartes (emit/dest/tomador/remetente) com CNPJ novo viram Participante
            // (create-only, sem consulta externa) — o usuário decide depois se consulta o CNPJ.
            app(ParticipanteAutoCadastroService::class)
                ->criarDesdeSnapshot($snapshot, $this->userId, $this->clienteId);
        }

        // Estorno por doc (overwrite = idempotente em retry do job). Somado por FecharClearanceLoteService.
        Cache::put(
            "clearance_estorno:{$this->loteId}:{$this->chave}",
            $snapshot->estornavel ? $this->custoCreditos : 0,
            86400
        );

        // Fecha ESTE documento na barra. Sem isto, um lote de 1 nota emitia só o 0% inicial e a
        // barra ficava parada em zero a consulta inteira (bug relatado em 2026-07-13).
        $this->progresso(
            $this->pct($this->indice),
            "Documento {$resumo} verificado ({$this->indice} de {$this->total})"
        );
    }

    /** % da barra para "N documentos concluídos", dentro da faixa desta fase. */
    private function pct(int $concluidos): int
    {
        return (int) round(($concluidos / max(1, $this->total)) * $this->pctSpan);
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
            // Documentos = etapa 2 da trilha (ver ClearanceEtapas). O total muda com o tier: 2 no
            // básico, 5 no completo — assim o strip da tela já mostra as etapas da contraparte
            // como pendentes enquanto os documentos rodam.
            'etapa' => 2,
            'total_etapas' => $this->totalEtapas,
            'etapa_label' => 'Consultando SEFAZ',
            'status' => 'processando',
            'progresso' => $pct,
            'mensagem' => $mensagem,
        ], 600);
    }
}
