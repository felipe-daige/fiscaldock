<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nome_empresa',
        'cnpj',
        'cargo',
        'faturamento_anual',
        'preparacao_reforma',
        'telefone_empresa',
        'email_empresa',
    ];

    // Relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function endereco()
    {
        return $this->hasOne(Endereco::class);
    }

    public function funcionarios()
    {
        return $this->hasMany(Funcionario::class);
    }

    public function solicitacoesSocio()
    {
        return $this->hasMany(SolicitacaoSocio::class);
    }
}