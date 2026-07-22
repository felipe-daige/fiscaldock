<?php

namespace App\Services\Efd\Handlers;

use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;

/**
 * A100 (NFS-e — serviço) → `efd_notas` (modelo fixo '00'). EFD Contribuições.
 * |A100|IND_OPER|IND_EMIT|COD_PART|COD_SIT|SER|SUB|NUM_DOC|CHV|DT_DOC|DT_EXE_SERV|VL_DOC|
 *      IND_PGTO|VL_DESC|VL_BC_PIS|VL_PIS|VL_BC_COFINS|VL_COFINS|VL_PIS_RET|VL_COFINS_RET|VL_ISS|
 * Índices (set-node A100, fields[N] ⟺ $p[N+2]): COD_PART=$p[4], COD_SIT=$p[5], SER=$p[6],
 * NUM_DOC=$p[8], CHV=$p[9], DT_DOC=$p[10], VL_DOC=$p[12], VL_DESC=$p[14], VL_ISS=$p[21].
 *
 * NFS-e geralmente NÃO tem chave de acesso ($p[9] vazio) — a linkagem A170↔A100 e o
 * índice único usam numero|serie|modelo|cod_part. **VL_ISS = $p[21]** (o set-node n8n
 * gravava fields[19] = $p[21], correto — o bug era ler [20]); normalizado `,`→`.` porque
 * o BI soma `(metadados->>'vl_iss')::numeric` ([[project-iss-carga-tributaria-bi]]).
 */
class HandlerA100 implements SpedRegistroHandler
{
    public function registros(): array
    {
        return ['A100'];
    }

    public function tabela(): string
    {
        return 'efd_notas';
    }

    public function mapear(SpedRecord $rec, ?Contexto $pai): ?array
    {
        $codSit = Campos::texto($rec->campo(5));
        $codPart = $rec->campo(4);

        return [
            'chave_acesso' => Campos::texto($rec->campo(9)),
            'modelo' => '00',
            'numero' => Campos::inteiro($rec->campo(8)) ?? 0,
            'serie' => Campos::texto($rec->campo(6)),
            'data_emissao' => Campos::dataIso($rec->campo(10)),
            'tipo_operacao' => $rec->campo(2) === '1' ? 'saida' : 'entrada',
            'valor_total' => Campos::dec($rec->campo(12)),
            'valor_desconto' => Campos::dec($rec->campo(14)),
            'cancelada' => $codSit === '02',
            'metadados' => [
                'cod_sit' => $codSit,
                'cod_part' => ($codPart === null || $codPart === '') ? null : $codPart,
                'ind_emit' => Campos::texto($rec->campo(3)),
                'ind_pgto' => Campos::texto($rec->campo(13)),
                'dt_exe_serv' => Campos::texto($rec->campo(11)),
                'vl_bc_pis' => Campos::dec($rec->campo(15)),
                'vl_pis' => Campos::dec($rec->campo(16)),
                'vl_bc_cofins' => Campos::dec($rec->campo(17)),
                'vl_cofins' => Campos::dec($rec->campo(18)),
                'vl_pis_ret' => Campos::dec($rec->campo(19)),
                'vl_cofins_ret' => Campos::dec($rec->campo(20)),
                'vl_iss' => Campos::dec($rec->campo(21)),
            ],
        ];
    }
}
