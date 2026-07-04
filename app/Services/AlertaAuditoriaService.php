<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\AlertaAuditoria;

/**
 * Registra a trilha de auditoria de alertas (1 linha por transição de status).
 *
 * Atribuição: ações do usuário passam por comAtor() (grava quem + nota); transições
 * fora desse contexto (auto-resolve / reativação no recalcular) ficam como "Sistema".
 * Escrita durante impersonação é bloqueada por middleware, então o ator é sempre o
 * usuário real quando presente.
 */
class AlertaAuditoriaService
{
    /** @var array{ator_id: ?int, ator_nome: ?string, notas: ?string}|null */
    private static ?array $contexto = null;

    /**
     * Executa $fn atribuindo as transições de status a um ator (usuário). Restaura o
     * contexto anterior ao final (seguro para aninhamento e exceções).
     *
     * @template T
     *
     * @param  callable(): T  $fn
     * @return T
     */
    public static function comAtor(?int $atorId, ?string $atorNome, ?string $notas, callable $fn): mixed
    {
        $anterior = self::$contexto;
        self::$contexto = ['ator_id' => $atorId, 'ator_nome' => $atorNome, 'notas' => $notas];

        try {
            return $fn();
        } finally {
            self::$contexto = $anterior;
        }
    }

    /** Registra uma transição de status. No-op se de === para (nada mudou). */
    public static function registrarTransicao(Alerta $alerta, ?string $de, ?string $para): void
    {
        if ($de === $para) {
            return;
        }

        $ctx = self::$contexto;
        $manual = $ctx !== null;

        AlertaAuditoria::create([
            'alerta_id' => $alerta->id,
            'user_id' => $ctx['ator_id'] ?? null,
            'acao' => self::derivarAcao($de, $para, $manual),
            'de_status' => $de,
            'para_status' => $para,
            'ator_nome' => $ctx['ator_nome'] ?? null,
            'notas' => $ctx['notas'] ?? null,
            'created_at' => now(),
        ]);
    }

    /** Deriva a ação a partir da transição e de ser (ou não) uma ação manual do usuário. */
    private static function derivarAcao(?string $de, ?string $para, bool $manual): string
    {
        if ($de === null) {
            return 'criado';
        }

        return match ($para) {
            'resolvido' => $manual ? 'resolvido' : 'auto_resolvido',
            'ignorado' => 'ignorado',
            'visto' => 'visto',
            'ativo' => $manual ? 'reaberto' : 'reativado',
            default => (string) $para,
        };
    }
}
