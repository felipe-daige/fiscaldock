<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
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
    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    public function funcionariosCriados()
    {
        return $this->hasMany(ClienteFuncionario::class, 'criado_por');
    }

    public function solicitacoes()
    {
        return $this->hasMany(ClienteSolicitacao::class);
    }

    public function rafConsultasPendentes()
    {
        return $this->hasMany(RafConsultaPendente::class);
    }

    public function rafRelatoriosProcessados()
    {
        return $this->hasMany(RafRelatorioProcessado::class);
    }

    public function privateDocuments()
    {
        return $this->hasMany(PrivateDocument::class);
    }

    // Helper - mantém compatibilidade com código antigo
    public function empresas()
    {
        return $this->clientes();
    }
}
