<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteFuncionario extends Model
{
    use HasFactory;

    protected $table = 'clientes_funcionarios';

    protected $fillable = [
        'cliente_id',
        'nome',
        'sobrenome',
        'email',
        'senha',
        'cargo',
        'departamento',
        'nivel_acesso',
        'criado_por',
    ];

    protected $hidden = [
        'senha',
    ];

    // Relacionamentos
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    // Helpers
    public function getNomeCompletoAttribute(): string
    {
        return $this->nome . ' ' . $this->sobrenome;
    }

    public function isAdmin(): bool
    {
        return $this->nivel_acesso === 'admin';
    }
}

