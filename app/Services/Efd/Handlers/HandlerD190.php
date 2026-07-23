<?php

namespace App\Services\Efd\Handlers;

use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;

/**
 * D190 (analítico consolidado de ICMS do CT-e) → `efd_notas_consolidados` (mesma tabela
 * do C190). CT-e não tem ST nem IPI no analítico → essas colunas vão 0.
 * |D190|CST_ICMS|CFOP|ALIQ_ICMS|VL_OPR|VL_BC_ICMS|VL_ICMS|VL_RED_BC|COD_OBS|
 */
class HandlerD190 implements SpedRegistroHandler
{
    public function registros(): array
    {
        return ['D190'];
    }

    public function tabela(): string
    {
        return 'efd_notas_consolidados';
    }

    public function mapear(SpedRecord $rec, ?Contexto $pai): ?array
    {
        return [
            // cst_icms/cfop NOT NULL — coalesce p/ linha truncada não abortar o import.
            'cst_icms' => Campos::texto($rec->campo(2)) ?? '',
            'cfop' => Campos::inteiro($rec->campo(3)) ?? 0,
            'aliquota_icms' => Campos::dec($rec->campo(4)),
            'valor_operacao' => Campos::dec($rec->campo(5)),
            'valor_bc_icms' => Campos::dec($rec->campo(6)),
            'valor_icms' => Campos::dec($rec->campo(7)),
            'valor_bc_icms_st' => '0',
            'valor_icms_st' => '0',
            'valor_reducao_bc' => Campos::dec($rec->campo(8)),
            'valor_ipi' => '0',
            'cod_obs' => Campos::texto($rec->campo(9)),
        ];
    }
}
