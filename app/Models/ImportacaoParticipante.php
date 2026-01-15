<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportacaoParticipante extends Model
{
    protected $table = 'importacoes_participantes';

    protected $fillable = [
        'user_id',
        'tipo_efd',
        'filename',
        'total_cnpjs',
        'processados',
        'importados',
        'duplicados',
        'status',
        'iniciado_em',
        'concluido_em',
    ];

    protected function casts(): array
    {
        return [
            'total_cnpjs' => 'integer',
            'processados' => 'integer',
            'importados' => 'integer',
            'duplicados' => 'integer',
            'iniciado_em' => 'datetime',
            'concluido_em' => 'datetime',
        ];
    }

    // Relacionamentos

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Acessores

    public function getPorcentagemAttribute(): int
    {
        return $this->total_cnpjs > 0
            ? (int) round(($this->processados / $this->total_cnpjs) * 100)
            : 0;
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
