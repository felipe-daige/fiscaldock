<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteSolicitacao extends Model
{
    use HasFactory;

    protected $table = 'clientes_solicitacoes';

    protected $fillable = [
        'cliente_id',
        'user_id',
        'tipo',
        'status',
        'descricao',
        'solicitado_em',
        'respondido_em',
    ];

    protected $casts = [
        'solicitado_em' => 'datetime',
        'respondido_em' => 'datetime',
    ];

    // Relacionamentos
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helpers
    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isAprovado(): bool
    {
        return $this->status === 'aprovado';
    }

    public function isRejeitado(): bool
    {
        return $this->status === 'rejeitado';
    }

    public function aprovar(): void
    {
        $this->update([
            'status' => 'aprovado',
            'respondido_em' => now(),
        ]);
    }

    public function rejeitar(): void
    {
        $this->update([
            'status' => 'rejeitado',
            'respondido_em' => now(),
        ]);
    }
}

