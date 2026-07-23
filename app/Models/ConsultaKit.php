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
        'user_id', 'nome', 'slug', 'descricao', 'fontes', 'desconto_percentual', 'preco_fixo', 'sistema', 'publico', 'ativo', 'ordem',
    ];

    protected function casts(): array
    {
        return [
            'fontes' => 'array',
            'desconto_percentual' => 'decimal:2',
            'preco_fixo' => 'decimal:2',
            'sistema' => 'boolean',
            'ativo' => 'boolean',
        ];
    }

    /** Usuários que enxergam/pagam o kit quando `publico='selecionados'` (kit global segmentado). */
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'consulta_kit_usuarios', 'kit_id', 'user_id');
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

    /**
     * Kits GLOBAIS visíveis para um usuário: os de `publico='todos'` mais os `selecionados` que
     * têm o usuário na pivot. Encapsula a regra de segmentação (usada por vitrine E precificação,
     * pra que quem não recebeu o kit também não leve o preço/desconto).
     */
    public function scopeParaUsuario($query, int $userId)
    {
        return $query->whereNull('user_id')->where(function ($q) use ($userId) {
            $q->where('publico', 'todos')
                ->orWhere(function ($s) use ($userId) {
                    $s->where('publico', 'selecionados')
                        ->whereExists(function ($e) use ($userId) {
                            $e->selectRaw('1')->from('consulta_kit_usuarios')
                                ->whereColumn('consulta_kit_usuarios.kit_id', 'consulta_kits.id')
                                ->where('consulta_kit_usuarios.user_id', $userId);
                        });
                });
        });
    }
}
