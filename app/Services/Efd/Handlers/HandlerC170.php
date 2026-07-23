<?php

namespace App\Services\Efd\Handlers;

use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;

/**
 * C170 (item da NF-e mercadoria) → `efd_notas_itens`.
 * Índices do set-node C170 (fields[N] ⟺ $p[N+2]): NUM_ITEM=2, COD_ITEM=3, DESCR=4,
 * QTD=5, UNID=6, VL_ITEM=7, VL_DESC=8, IND_MOV=9, CST_ICMS=10, CFOP=11, COD_NAT=12,
 * VL_BC_ICMS=13, ALIQ_ICMS=14, VL_ICMS=15, VL_BC_ICMS_ST=16, ALIQ_ST=17, VL_ICMS_ST=18,
 * IND_APUR=19, CST_IPI=20, COD_ENQ=21, VL_BC_IPI=22, ALIQ_IPI=23, VL_IPI=24, CST_PIS=25,
 * VL_BC_PIS=26, ALIQ_PIS=27, QUANT_BC_PIS=28, VL_PIS=30, CST_COFINS=31, VL_BC_COFINS=32,
 * ALIQ_COFINS=33, QUANT_BC_COFINS=34, VL_COFINS=36, COD_CTA=37.
 *
 * ICMS/PIS/COFINS tipados em coluna; IPI/ST/bases em `metadados` jsonb. valor_unitario
 * não é mapeado (C170 não traz). efd_nota_id resolvido pela engine via pai (chave).
 */
class HandlerC170 implements SpedRegistroHandler
{
    public function registros(): array
    {
        return ['C170'];
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
            'quantidade' => Campos::dec($rec->campo(5)),
            'unidade_medida' => Campos::texto($rec->campo(6)),
            'valor_unitario' => null,
            'valor_total' => Campos::dec($rec->campo(7)),
            'cfop' => Campos::inteiro($rec->campo(11)),
            'cst_icms' => Campos::texto($rec->campo(10)),
            'aliquota_icms' => Campos::dec($rec->campo(14)),
            'valor_icms' => Campos::dec($rec->campo(15)),
            'cst_pis' => Campos::texto($rec->campo(25)),
            'aliquota_pis' => Campos::dec($rec->campo(27)),
            'valor_pis' => Campos::dec($rec->campo(30)),
            'cst_cofins' => Campos::texto($rec->campo(31)),
            'aliquota_cofins' => Campos::dec($rec->campo(33)),
            'valor_cofins' => Campos::dec($rec->campo(36)),
            'metadados' => [
                'vl_desc' => Campos::dec($rec->campo(8)),
                'ind_mov' => Campos::texto($rec->campo(9)),
                'cod_nat' => Campos::texto($rec->campo(12)),
                'vl_bc_icms' => Campos::dec($rec->campo(13)),
                'vl_bc_icms_st' => Campos::dec($rec->campo(16)),
                'aliq_st' => Campos::dec($rec->campo(17)),
                'vl_icms_st' => Campos::dec($rec->campo(18)),
                'ind_apur' => Campos::texto($rec->campo(19)),
                'cst_ipi' => Campos::texto($rec->campo(20)),
                'cod_enq' => Campos::texto($rec->campo(21)),
                'vl_bc_ipi' => Campos::dec($rec->campo(22)),
                'aliq_ipi' => Campos::dec($rec->campo(23)),
                'vl_ipi' => Campos::dec($rec->campo(24)),
                'vl_bc_pis' => Campos::dec($rec->campo(26)),
                'quant_bc_pis' => Campos::texto($rec->campo(28)),
                'vl_bc_cofins' => Campos::dec($rec->campo(32)),
                'quant_bc_cofins' => Campos::texto($rec->campo(34)),
                'cod_cta' => Campos::texto($rec->campo(37)),
            ],
        ];
    }
}
