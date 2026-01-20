<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportacaoParticipante extends Model
{
    protected $table = 'importacoes_participantes';

    protected $fillable = [
        'user_id',
        'tipo_efd',
        'filename',
        'total_participantes',
        'total_cnpjs_unicos',
        'total_cpfs_unicos',
        'novos',
        'duplicados',
        'status',
        'participante_ids',
        'iniciado_em',
        'concluido_em',
    ];

    protected function casts(): array
    {
        return [
            'total_participantes' => 'integer',
            'total_cnpjs_unicos' => 'integer',
            'total_cpfs_unicos' => 'integer',
            'novos' => 'integer',
            'duplicados' => 'integer',
            'participante_ids' => 'array',
            'iniciado_em' => 'datetime',
            'concluido_em' => 'datetime',
        ];
    }

    // Relacionamentos

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(Participante::class, 'importacao_participante_id');
    }

    // Acessores

    /**
     * Total de participantes processados (novos + duplicados).
     */
    public function getTotalProcessadosAttribute(): int
    {
        return $this->novos + $this->duplicados;
    }

    // Scopes

    public function scopeDoUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeProcessando($query)
    {
        return $query->where('status', 'processando');
    }

    public function scopeConcluidas($query)
    {
        return $query->where('status', 'concluido');
    }
}
