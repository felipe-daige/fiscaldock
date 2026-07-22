<?php

namespace App\Services\Efd\Handlers;

use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;

/**
 * C190 (analítico consolidado de ICMS por CST+CFOP+alíquota) → `efd_notas_consolidados`.
 * |C190|CST_ICMS|CFOP|ALIQ_ICMS|VL_OPR|VL_BC_ICMS|VL_ICMS|VL_BC_ICMS_ST|VL_ICMS_ST|VL_RED_BC|VL_IPI|COD_OBS|
 *
 * NÃO vai em itens. Saída perfil B pode ter só C190 (sem C170). efd_nota_id resolvido
 * pela engine via pai. ON CONFLICT (efd_nota_id, cst_icms, cfop, aliquota_icms).
 */
class HandlerC190 implements SpedRegistroHandler
{
    public function registros(): array
    {
        return ['C190'];
    }

    public function tabela(): string
    {
        return 'efd_notas_consolidados';
    }

    public function mapear(SpedRecord $rec, ?Contexto $pai): ?array
    {
        return [
            'cst_icms' => Campos::texto($rec->campo(2)),
            'cfop' => Campos::inteiro($rec->campo(3)),
            'aliquota_icms' => Campos::dec($rec->campo(4)),
            'valor_operacao' => Campos::dec($rec->campo(5)),
            'valor_bc_icms' => Campos::dec($rec->campo(6)),
            'valor_icms' => Campos::dec($rec->campo(7)),
            'valor_bc_icms_st' => Campos::dec($rec->campo(8)),
            'valor_icms_st' => Campos::dec($rec->campo(9)),
            'valor_reducao_bc' => Campos::dec($rec->campo(10)),
            'valor_ipi' => Campos::dec($rec->campo(11)),
            'cod_obs' => Campos::texto($rec->campo(12)),
        ];
    }
}
