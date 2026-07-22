<?php

namespace App\Services\Efd\Handlers;

use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;

/**
 * C100 (NF-e/NFC-e mercadoria) → `efd_notas`.
 * |C100|IND_OPER|IND_EMIT|COD_PART|COD_MOD|COD_SIT|SER|NUM_DOC|CHV_NFE|DT_DOC|DT_E_S|VL_DOC|IND_PGTO|VL_DESC|…
 *
 * NUNCA dropa por COD_PART vazio (NFC-e a consumidor final = participante null;
 * o bug UTIDA). participante_id fica pro ParticipanteResolver (F3); o cod_part vai
 * em metadados. Engine adiciona user_id/cliente_id/importacao_id/origem_arquivo.
 */
class HandlerC100 implements SpedRegistroHandler
{
    /** COD_SIT que zera a nota dos totais válidos (cancelada/denegada/inutilizada). */
    private const COD_SIT_CANCELADA = ['02', '03', '04', '05'];

    public function registros(): array
    {
        return ['C100'];
    }

    public function tabela(): string
    {
        return 'efd_notas';
    }

    public function mapear(SpedRecord $rec, ?Contexto $pai): ?array
    {
        $codSit = Campos::texto($rec->campo(6));
        $codPart = $rec->campo(4); // '' é legítimo (consumidor final) — nunca dropa

        return [
            'chave_acesso' => Campos::texto($rec->campo(9)),
            'modelo' => Campos::texto($rec->campo(5)) ?? '55',
            'numero' => Campos::inteiro($rec->campo(8)) ?? 0,
            'serie' => Campos::texto($rec->campo(7)),
            'data_emissao' => Campos::dataIso($rec->campo(10)),
            'tipo_operacao' => $rec->campo(2) === '1' ? 'saida' : 'entrada',
            'valor_total' => Campos::dec($rec->campo(12)),
            'valor_desconto' => Campos::dec($rec->campo(14)),
            'cancelada' => in_array($codSit, self::COD_SIT_CANCELADA, true),
            'metadados' => [
                'cod_sit' => $codSit,
                'cod_part' => ($codPart === null || $codPart === '') ? null : $codPart,
            ],
        ];
    }
}
