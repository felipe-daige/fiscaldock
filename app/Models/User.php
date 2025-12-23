<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', // Mantém o campo name padrão
        'sobrenome',
        'email',
        'password',
        'telefone',
        'credits',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'credits' => 'integer',
        ];
    }

    // Relacionamentos
    public function empresas()
    {
        return $this->hasMany(Empresa::class);
    }

    public function funcionariosCriados()
    {
        return $this->hasMany(Funcionario::class, 'criado_por');
    }

    public function solicitacoesSocio()
    {
        return $this->hasMany(SolicitacaoSocio::class);
    }

    public function rafConsultasPendentes()
    {
        return $this->hasMany(RafConsultaPendente::class);
    }
}