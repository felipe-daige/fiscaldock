<?php

namespace App\Services\Efd\Handlers;

use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;

/**
 * F600 (retenção na fonte de PIS/COFINS) → `efd_retencoes_fonte`. EFD Contribuições.
 * Índices (set-node F600, fields[N] ⟺ $p[N+2]): NAT=$p[2], DT_RET=$p[3], VL_BC_RET=$p[4],
 * VL_RET=$p[5], COD_REC=$p[6], IND_NAT_RET=$p[7], CNPJ=$p[8], VL_RET_PIS=$p[9],
 * VL_RET_COFINS=$p[10], IND_DEC=$p[11].
 *
 * Sem chave única natural — a engine faz DELETE por importação antes de inserir (idempotência).
 */
class HandlerF600 implements SpedRegistroHandler
{
    public function registros(): array
    {
        return ['F600'];
    }

    public function tabela(): string
    {
        return 'efd_retencoes_fonte';
    }

    public function mapear(SpedRecord $rec, ?Contexto $pai): ?array
    {
        return [
            // natureza/cod_receita/natureza_receita/cnpj/ind_declarante são NOT NULL:
            // coalesce (retenção por órgão federal traz CNPJ vazio, legítimo) p/ não abortar.
            'natureza' => Campos::texto($rec->campo(2)) ?? '',
            'data_retencao' => Campos::dataIso($rec->campo(3)),
            'base_calculo' => Campos::dec($rec->campo(4)),
            'valor_total' => Campos::dec($rec->campo(5)),
            'cod_receita' => Campos::texto($rec->campo(6)) ?? '',
            'natureza_receita' => Campos::texto($rec->campo(7)) ?? '',
            'cnpj' => preg_replace('/\D/', '', (string) $rec->campo(8)),
            'valor_pis' => Campos::dec($rec->campo(9)),
            'valor_cofins' => Campos::dec($rec->campo(10)),
            'ind_declarante' => Campos::texto($rec->campo(11)) ?? '0',
            'dados_brutos' => [
                'REG' => 'F600',
                'RETENCAO_NATUREZA' => $rec->campo(2),
                'RETENCAO_DATA' => $rec->campo(3),
                'RETENCAO_BASE_CALCULO' => $rec->campo(4),
                'RETENCAO_VALOR_TOTAL' => $rec->campo(5),
                'RETENCAO_COD_RECEITA' => $rec->campo(6),
                'RETENCAO_NATUREZA_RECEITA' => $rec->campo(7),
                'RETENCAO_CNPJ' => $rec->campo(8),
                'RETENCAO_VALOR_PIS' => $rec->campo(9),
                'RETENCAO_VALOR_COFINS' => $rec->campo(10),
                'RETENCAO_IND_DECLARANTE' => $rec->campo(11),
            ],
        ];
    }
}
