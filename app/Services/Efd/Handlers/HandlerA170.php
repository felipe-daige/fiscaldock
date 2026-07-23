<?php

namespace App\Services\Efd\Handlers;

use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;

/**
 * A170 (item da NFS-e) → `efd_notas_itens`. EFD Contribuições.
 * Índices (set-node A170, fields[N] ⟺ $p[N+2]): NUM_ITEM=$p[2], COD_ITEM=$p[3],
 * DESCR=$p[4], VL_ITEM=$p[5], VL_DESC=$p[6], NAT_BC_CRED=$p[7], IND_ORIG_CRED=$p[8],
 * CST_PIS=$p[9], VL_BC_PIS=$p[10], ALIQ_PIS=$p[11], VL_PIS=$p[12], CST_COFINS=$p[13],
 * VL_BC_COFINS=$p[14], ALIQ_COFINS=$p[15], VL_COFINS=$p[16], COD_CTA=$p[17], COD_CCUS=$p[18].
 *
 * Serviço não tem ICMS/CFOP/quantidade/unidade — essas colunas ficam null. efd_nota_id
 * resolvido pela engine via pai (numero|serie|modelo|cod_part — NFS-e não tem chave).
 */
class HandlerA170 implements SpedRegistroHandler
{
    public function registros(): array
    {
        return ['A170'];
    }

    public function tabela(): string
    {
        return 'efd_notas_itens';
    }

    public function mapear(SpedRecord $rec, ?Contexto $pai): ?array
    {
        return [
            'numero_item' => Campos::inteiro($rec->campo(2)) ?? 0,
            'codigo_item' => Campos::texto($rec->campo(3)) ?? '', // NOT NULL — não abortar por COD_ITEM vazio

            'descricao' => Campos::texto($rec->campo(4)),
            'quantidade' => null,
            'unidade_medida' => null,
            'valor_unitario' => null,
            'valor_total' => Campos::dec($rec->campo(5)),
            'cfop' => null,
            'cst_icms' => null,
            'aliquota_icms' => null,
            'valor_icms' => null,
            'cst_pis' => Campos::texto($rec->campo(9)),
            'aliquota_pis' => Campos::dec($rec->campo(11)),
            'valor_pis' => Campos::dec($rec->campo(12)),
            'cst_cofins' => Campos::texto($rec->campo(13)),
            'aliquota_cofins' => Campos::dec($rec->campo(15)),
            'valor_cofins' => Campos::dec($rec->campo(16)),
            'metadados' => [
                'vl_desc' => Campos::dec($rec->campo(6)),
                'nat_bc_cred' => Campos::texto($rec->campo(7)),
                'ind_orig_cred' => Campos::texto($rec->campo(8)),
                'vl_bc_pis' => Campos::dec($rec->campo(10)),
                'vl_bc_cofins' => Campos::dec($rec->campo(14)),
                'cod_cta' => Campos::texto($rec->campo(17)),
                'cod_ccus' => Campos::texto($rec->campo(18)),
            ],
        ];
    }
}
