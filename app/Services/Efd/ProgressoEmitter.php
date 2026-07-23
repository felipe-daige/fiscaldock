<?php

namespace App\Services\Efd;

use Illuminate\Support\Facades\Cache;

/**
 * Escreve o progresso da importação DIRETO no cache do SSE, em processo (o motor Laravel
 * não faz callback HTTP). O frontend (`renderEtapasStrip`) lê o mesmo contrato. §10.7.
 *
 * Chaves: `efd_notas_progress:{user}:{tab}:{bloco}` (strip por bloco) + o principal
 * `progresso:{user}:{tab}` (lido pelo SSE). No-op quando não há tab_id (cutover em
 * background sem aba aberta). Instância por importação (guarda user/tab/importacao).
 */
class ProgressoEmitter
{
    /** Ordem canônica dos blocos (espelha o endpoint) — usada pra marcar anteriores. */
    private const ORDEM = [
        'participantes', 'notas_servicos', 'notas_mercadorias', 'notas_transportes',
        'catalogo', 'apuracao_icms', 'retencoes_fonte', 'apuracao_pis_cofins',
    ];

    public function __construct(
        private int $userId = 0,
        private ?string $tabId = null,
        private int $importacaoId = 0,
    ) {}

    /** Fábrica: instância ligada a uma importação concreta. */
    public function para(int $userId, ?string $tabId, int $importacaoId): self
    {
        return new self($userId, $tabId, $importacaoId);
    }

    /**
     * Emite o estado de um bloco. `status`: inicio|processando|concluido|skip.
     * Atualiza a chave do bloco, toca o cache principal e conclui blocos anteriores.
     */
    public function bloco(string $bloco, string $status, int $progresso = 0, ?string $mensagem = null): void
    {
        if (! $this->ativo()) {
            return;
        }

        Cache::put("efd_notas_progress:{$this->userId}:{$this->tabId}:{$bloco}", [
            'bloco' => $bloco,
            'status' => $status,
            'progresso' => $progresso,
            'mensagem' => $mensagem,
            'updated_at' => now()->toIso8601String(),
        ], 600);

        $this->tocarPrincipal($bloco, $status, $progresso, $mensagem);

        if (in_array($status, ['inicio', 'processando'], true)) {
            $this->marcarAnteriores($bloco);
        }
    }

    /** Payload de erro no cache principal (mesmo shape do endpoint). */
    public function erro(string $mensagem): void
    {
        if (! $this->ativo()) {
            return;
        }

        $mainKey = "progresso:{$this->userId}:{$this->tabId}";
        $existing = Cache::get($mainKey, []);
        Cache::put($mainKey, array_merge($existing, [
            'user_id' => $this->userId,
            'tab_id' => $this->tabId,
            'importacao_id' => $this->importacaoId,
            'status' => 'erro',
            'progresso' => 0,
            'mensagem' => $mensagem,
            'error_message' => $mensagem,
            'updated_at' => now()->toIso8601String(),
        ]), 600);
    }

    private function ativo(): bool
    {
        return $this->tabId !== null && $this->tabId !== '';
    }

    /** Atualiza o cache principal sem rebaixar um 'concluido' já gravado. */
    private function tocarPrincipal(string $bloco, string $status, int $progresso, ?string $mensagem): void
    {
        $mainKey = "progresso:{$this->userId}:{$this->tabId}";
        $existing = Cache::get($mainKey, []);
        $jaConcluido = ($existing['status'] ?? null) === 'concluido';

        Cache::put($mainKey, array_merge($existing, [
            'user_id' => $this->userId,
            'tab_id' => $this->tabId,
            'importacao_id' => $this->importacaoId,
            'status' => $jaConcluido ? 'concluido' : 'processando',
            'progresso' => $jaConcluido ? ($existing['progresso'] ?? 100) : $progresso,
            'bloco' => $bloco,
            'mensagem' => $jaConcluido ? ($existing['mensagem'] ?? $mensagem) : $mensagem,
            'updated_at' => now()->toIso8601String(),
        ]), 600);
    }

    /** Marca blocos anteriores ainda 'processando' como concluídos (strip não trava). */
    private function marcarAnteriores(string $bloco): void
    {
        $idx = array_search($bloco, self::ORDEM, true);
        if ($idx === false) {
            return;
        }

        for ($i = 0; $i < $idx; $i++) {
            $key = "efd_notas_progress:{$this->userId}:{$this->tabId}:".self::ORDEM[$i];
            $prior = Cache::get($key);
            if ($prior && ! in_array($prior['status'], ['concluido', 'skip'], true)) {
                $prior['status'] = 'concluido';
                $prior['progresso'] = 100;
                Cache::put($key, $prior, 600);
            }
        }
    }
}
