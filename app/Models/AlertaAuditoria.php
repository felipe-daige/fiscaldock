<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trilha de auditoria de alertas: 1 linha por transição de status (append-only).
 * user_id null (ou ator_nome "Sistema") = evento automático do recalcular.
 */
class AlertaAuditoria extends Model
{
    protected $table = 'alerta_auditorias';

    public const UPDATED_AT = null; // append-only: só created_at

    protected $fillable = [
        'alerta_id',
        'user_id',
        'acao',
        'de_status',
        'para_status',
        'ator_nome',
        'notas',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function alerta(): BelongsTo
    {
        return $this->belongsTo(Alerta::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Rótulo legível da ação (fonte única para UI web + eventual export). */
    public function acaoLabel(): string
    {
        return match ($this->acao) {
            'criado' => 'Alerta aberto',
            'resolvido' => 'Resolvido',
            'ignorado' => 'Ignorado',
            'visto' => 'Marcado como visto',
            'reaberto' => 'Reaberto',
            'auto_resolvido' => 'Resolvido automaticamente',
            'reativado' => 'Reativado automaticamente',
            default => ucfirst(str_replace('_', ' ', (string) $this->acao)),
        };
    }

    /** Nome do ator para exibição: snapshot, ou "Sistema" quando automático. */
    public function atorLabel(): string
    {
        return $this->ator_nome ?: 'Sistema';
    }
}
