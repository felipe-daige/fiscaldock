<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'empresa',
        'cargo',
        'cnpj',
        'faturamento_anual',
        'desafio_principal',
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

    protected function cnpj(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? preg_replace('/\D/', '', $value) : null,
        );
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

    public function privateDocuments()
    {
        return $this->hasMany(PrivateDocument::class);
    }

    // Helper - mantém compatibilidade com código antigo
    public function empresas()
    {
        return $this->clientes();
    }

    /**
     * Retorna a empresa propria do usuario (is_empresa_propria = true).
     */
    public function empresaPropria(): ?Cliente
    {
        return $this->clientes()
            ->where('is_empresa_propria', true)
            ->where('tipo_pessoa', 'PJ')
            ->first();
    }
}
