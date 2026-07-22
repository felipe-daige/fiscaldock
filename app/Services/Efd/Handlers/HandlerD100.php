<?php

namespace App\Services\Efd\Handlers;

use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;

/**
 * D100 (CT-e transporte) → `efd_notas` (modelo 57/67).
 * |D100|IND_OPER|IND_EMIT|COD_PART|COD_MOD|COD_SIT|SER|SUB|NUM_DOC|CHV_CTE|DT_DOC|…|VL_DOC($p15)|VL_DESC($p16)|
 *
 * Difere do C100: chave em $p[10] (CHV_CTE), NUM_DOC em $p[9] (após SUB), VL_DOC em
 * $p[15] (Guia; validar no golden com CT-e real), cancelada só COD_SIT=='02'.
 */
class HandlerD100 implements SpedRegistroHandler
{
    public function registros(): array
    {
        return ['D100'];
    }

    public function tabela(): string
    {
        return 'efd_notas';
    }

    public function mapear(SpedRecord $rec, ?Contexto $pai): ?array
    {
        $codSit = Campos::texto($rec->campo(6));
        $codPart = $rec->campo(4);

        return [
            'chave_acesso' => Campos::texto($rec->campo(10)),
            'modelo' => Campos::texto($rec->campo(5)) ?? '57',
            'numero' => Campos::inteiro($rec->campo(9)) ?? 0,
            'serie' => Campos::texto($rec->campo(7)),
            'data_emissao' => Campos::dataIso($rec->campo(11)),
            'tipo_operacao' => $rec->campo(2) === '1' ? 'saida' : 'entrada',
            'valor_total' => Campos::dec($rec->campo(15)),
            'valor_desconto' => Campos::dec($rec->campo(16)),
            'cancelada' => $codSit === '02',
            'metadados' => [
                'cod_sit' => $codSit,
                'cod_part' => ($codPart === null || $codPart === '') ? null : $codPart,
            ],
        ];
    }
}
