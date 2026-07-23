<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Preço de venda de UMA fonte de consulta (R$), gerenciado no admin (/app/admin/fontes).
 *
 * Fonte única do preço por-fonte quando existe linha: CatalogoFontesAvulsas::precoDe resolve
 * DB override → config('advocacia.precos.*') → config('advocacia.preco_fonte_default'). Tabela
 * vazia = zero mudança de preço (cai no config). `ativo=false` esconde a fonte da tela de
 * seleção sem depender do env CONSULTAS_FONTES_PAUSADAS (controle comercial, não operacional).
 */
class FontePreco extends Model
{
    protected $table = 'fonte_precos';

    protected $fillable = ['chave', 'preco', 'ativo'];

    protected function casts(): array
    {
        return [
            'preco' => 'decimal:2',
            'ativo' => 'boolean',
        ];
    }
}
