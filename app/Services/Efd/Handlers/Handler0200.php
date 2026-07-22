<?php

namespace App\Services\Efd\Handlers;

use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;

/**
 * 0200 (catálogo de item) → `efd_catalogo_itens`.
 * |0200|COD_ITEM|DESCR_ITEM|COD_BARRA|COD_ANT|UNID_INV|TIPO_ITEM|COD_NCM|EX_IPI|COD_GEN|COD_LST|ALIQ_ICMS|
 *
 * COD_ITEM LITERAL (zeros à esquerda — nunca parseInt). Engine adiciona
 * user_id/cliente_id/importacao_id. ON CONFLICT (cliente_id, cod_item) DO UPDATE (drift).
 */
class Handler0200 implements SpedRegistroHandler
{
    public function registros(): array
    {
        return ['0200'];
    }

    public function tabela(): string
    {
        return 'efd_catalogo_itens';
    }

    public function mapear(SpedRecord $rec, ?Contexto $pai): ?array
    {
        $codItem = Campos::texto($rec->campo(2));

        // cod_item é a chave do catálogo (NOT NULL + unique com cliente).
        if ($codItem === null) {
            return null;
        }

        return [
            'cod_item' => $codItem,
            'descr_item' => Campos::texto($rec->campo(3)),
            'cod_barra' => Campos::texto($rec->campo(4)),
            'unid_inv' => Campos::texto($rec->campo(6)),
            'tipo_item' => Campos::texto($rec->campo(7)),
            'cod_ncm' => Campos::texto($rec->campo(8)),
            'cod_gen' => Campos::texto($rec->campo(10)),
            'aliq_icms' => Campos::dec($rec->campo(12)),
        ];
    }
}
