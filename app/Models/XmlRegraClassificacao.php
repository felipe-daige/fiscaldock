<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XmlRegraClassificacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_regra',
        'condicoes',
        'acao',
        'prioridade',
        'ativo',
        'vezes_usada',
    ];

    protected $casts = [
        'condicoes' => 'array',
        'acao' => 'array',
        'ativo' => 'boolean',
    ];

    /**
     * Incrementa o contador de uso da regra
     */
    public function incrementarUso()
    {
        $this->increment('vezes_usada');
    }
}
