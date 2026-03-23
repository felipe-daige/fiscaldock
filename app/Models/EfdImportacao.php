<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EfdImportacao extends Model
{
    protected $table = 'efd_importacoes';

    protected $fillable = [
        'user_id',
        'cliente_id',
        'tipo_efd',
        'filename',
        'arquivo_base64',
        'total_participantes',
        'total_cnpjs_unicos',
        'total_cpfs_unicos',
        'novos',
        'duplicados',
        'status',
        'extrair_notas',
        'total_notas',
        'notas_extraidas',
        'creditos_cobrados',
        'participante_ids',
        'iniciado_em',
        'concluido_em',
        'resumo_final',
    ];

    protected function casts(): array
    {
        return [
            'total_participantes' => 'integer',
            'total_cnpjs_unicos' => 'integer',
            'total_cpfs_unicos' => 'integer',
            'novos' => 'integer',
            'duplicados' => 'integer',
            'extrair_notas' => 'boolean',
            'total_notas' => 'integer',
            'notas_extraidas' => 'integer',
            'creditos_cobrados' => 'integer',
            'participante_ids' => 'array',
            'iniciado_em' => 'datetime',
            'concluido_em' => 'datetime',
            'resumo_final' => 'array',
        ];
    }

    // Relacionamentos

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(Participante::class, 'importacao_efd_id');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(EfdNota::class, 'importacao_id');
    }

    // Acessores

    /**
     * Total de participantes processados (novos + duplicados).
     */
    public function getTotalProcessadosAttribute(): int
    {
        return $this->novos + $this->duplicados;
    }

    /**
     * Tempo de processamento formatado (ex: "2m 34s").
     */
    public function getTempoProcessamentoAttribute(): string
    {
        if (! $this->iniciado_em || ! $this->concluido_em) {
            return '—';
        }
        $diff = $this->iniciado_em->diff($this->concluido_em);
        if ($diff->h > 0) {
            return $diff->h.'h '.$diff->i.'m';
        }
        if ($diff->i > 0) {
            return $diff->i.'m '.$diff->s.'s';
        }
        if ($diff->s > 0) {
            return $diff->s.'s';
        }

        return '< 1s';
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
