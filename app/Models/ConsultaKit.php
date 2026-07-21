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
        'nome', 'slug', 'descricao', 'fontes', 'desconto_percentual', 'ativo', 'ordem',
    ];

    protected function casts(): array
    {
        return [
            'fontes' => 'array',
            'desconto_percentual' => 'decimal:2',
            'ativo' => 'boolean',
        ];
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true)->orderBy('ordem');
    }
}
