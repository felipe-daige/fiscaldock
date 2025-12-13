<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoSocio extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'user_id',
        'status',
        'solicitado_em',
        'respondido_em',
    ];

    protected $casts = [
        'solicitado_em' => 'datetime',
        'respondido_em' => 'datetime',
    ];

    // Relacionamentos
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeAprovadas($query)
    {
        return $query->where('status', 'aprovado');
    }

    public function scopeRejeitadas($query)
    {
        return $query->where('status', 'rejeitado');
    }
}