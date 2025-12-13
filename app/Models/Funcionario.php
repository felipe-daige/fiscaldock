<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Funcionario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'empresa_id',
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
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'senha' => 'hashed',
        ];
    }

    // Relacionamentos
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    // Scopes
    public function scopeAdmins($query)
    {
        return $query->where('nivel_acesso', 'admin');
    }

    public function scopeFuncionarios($query)
    {
        return $query->where('nivel_acesso', 'funcionario');
    }
}