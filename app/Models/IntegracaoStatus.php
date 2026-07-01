<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Status de disponibilidade de uma integração externa (fonte de consulta ou serviço de
 * plataforma). Editado manualmente pelo operador admin; exibido read-only em /app/status.
 * "Problema" = qualquer status != operacional.
 */
class IntegracaoStatus extends Model
{
    public const STATUS_OPERACIONAL = 'operacional';
    public const STATUS_DEGRADADO = 'degradado';
    public const STATUS_FORA = 'fora';
    public const STATUS_MANUTENCAO = 'manutencao';

    public const GRUPO_CONSULTAS = 'consultas';
    public const GRUPO_PLATAFORMA = 'plataforma';

    protected $table = 'integracao_statuses';

    protected $fillable = [
        'chave', 'nome', 'grupo', 'ordem', 'status', 'mensagem', 'atualizado_por',
    ];

    /** @return list<string> */
    public static function statusesValidos(): array
    {
        return [self::STATUS_OPERACIONAL, self::STATUS_DEGRADADO, self::STATUS_FORA, self::STATUS_MANUTENCAO];
    }

    public static function problemasCount(): int
    {
        return static::query()->problemas()->count();
    }

    public function scopeGrupo(Builder $q, string $grupo): Builder
    {
        return $q->where('grupo', $grupo);
    }

    public function scopeProblemas(Builder $q): Builder
    {
        return $q->where('status', '!=', self::STATUS_OPERACIONAL);
    }

    public function scopeOrdenado(Builder $q): Builder
    {
        return $q->orderBy('grupo')->orderBy('ordem');
    }

    public function getLabelAttribute(): string
    {
        return [
            self::STATUS_OPERACIONAL => 'Operacional',
            self::STATUS_DEGRADADO => 'Degradado',
            self::STATUS_FORA => 'Fora do ar',
            self::STATUS_MANUTENCAO => 'Em manutenção',
        ][$this->status] ?? 'Desconhecido';
    }

    public function getEmojiAttribute(): string
    {
        return [
            self::STATUS_OPERACIONAL => '🟢',
            self::STATUS_DEGRADADO => '🟡',
            self::STATUS_FORA => '🔴',
            self::STATUS_MANUTENCAO => '🔵',
        ][$this->status] ?? '⚪';
    }

    public function getCorClasseAttribute(): string
    {
        return [
            self::STATUS_OPERACIONAL => 'bg-emerald-100 text-emerald-800',
            self::STATUS_DEGRADADO => 'bg-amber-100 text-amber-800',
            self::STATUS_FORA => 'bg-red-100 text-red-800',
            self::STATUS_MANUTENCAO => 'bg-blue-100 text-blue-800',
        ][$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function atualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atualizado_por');
    }
}
