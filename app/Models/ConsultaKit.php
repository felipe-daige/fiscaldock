<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Kit da consulta avulsa por fontes (vertical advocacia): preset NOMEADO de seleção com
 * desconto percentual. É dado (CRUD admin), não plano/entidade de billing — o clique preenche
 * os checkboxes e o desconto só vale quando a seleção bate exatamente com as fontes do kit.
 *
 * Spec: docs/advocacia/consultas-certidoes.md (fase 3).
 */
class ConsultaKit extends Model
{
    protected $table = 'consulta_kits';

    protected $fillable = [
        'user_id', 'nome', 'slug', 'descricao', 'fontes', 'desconto_percentual', 'sistema', 'ativo', 'ordem',
    ];

    protected function casts(): array
    {
        return [
            'fontes' => 'array',
            'desconto_percentual' => 'decimal:2',
            'sistema' => 'boolean',
            'ativo' => 'boolean',
        ];
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true)->orderBy('ordem');
    }

    /** Kits GLOBAIS (preset do admin, user_id null) — os únicos que carregam desconto. */
    public function scopeGlobais($query)
    {
        return $query->whereNull('user_id');
    }

    /** Planos DO SISTEMA (vitrine oficial do contador: Gratuito/Validação/Licitação/Compliance). */
    public function scopeSistema($query)
    {
        return $query->whereNull('user_id')->where('sistema', true);
    }

    /** Presets PESSOAIS de um usuário (combinações salvas na tela de consulta, sem desconto). */
    public function scopeDoUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
