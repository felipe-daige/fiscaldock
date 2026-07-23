<?php

namespace App\Services\Efd\Handlers;

use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;

/**
 * Bloco M (apuração PIS/COFINS) + 0110 (regime) → 1 linha em `efd_apuracoes_contribuicoes`
 * por importação. EFD Contribuições. Agregador (mesmo padrão do bloco E fiscal).
 *
 * M200 (12 campos $p[2..13]) → colunas `pis_*`; M600 idem → `cofins_*`; 0110 → regime.
 * M210/M610 (detalhe NC), M400/M410 (receitas não tributadas), M605 (COFINS a recolher)
 * vão nos 4 jsonb (`pis_detalhes`/`cofins_detalhes`/`pis_nao_tributado`/`cofins_recolher_detalhe`).
 * O total a recolher (`pis_total_recolher`=$p[13], `cofins_total_recolher`=$p[13]) é o que o
 * `EfdResumoBuilder` lê pro resumo.
 *
 * Estado por instância ⇒ o ContribDriver reusa a MESMA instância no job inteiro.
 */
class HandlerApuracaoM implements HandlerAgregador
{
    /** @var array<string, array<int, array<string, mixed>>> registros nomeados por REG */
    private array $registros = [];

    /** CST-pai corrente das receitas não tributadas (M400→M410 e M800→M810 são pai→filho). */
    private ?string $cstPisNaoTrib = null;

    private ?string $cstCofinsNaoTrib = null;

    public function registros(): array
    {
        // M800/M810 = receitas não tributadas de COFINS (espelho de M400/M410 do PIS).
        return ['0110', 'M200', 'M210', 'M400', 'M410', 'M600', 'M605', 'M610', 'M800', 'M810'];
    }

    public function tabela(): string
    {
        return 'efd_apuracoes_contribuicoes';
    }

    public function mapear(SpedRecord $rec, ?Contexto $pai): ?array
    {
        $nomeado = $this->nomear($rec);

        // Receitas não tributadas: M400/M800 são o cabeçalho por CST, M410/M810 o detalhe por
        // natureza da receita DENTRO daquele CST. Como guardo por-REG (perco o interleave),
        // carimbo o CST-pai corrente no filho pra a tela poder mostrar CST + natureza juntos.
        match ($rec->reg) {
            'M400' => $this->cstPisNaoTrib = $nomeado['CST_PIS'] ?? null,
            'M410' => $nomeado['CST_PIS'] = $this->cstPisNaoTrib,
            'M800' => $this->cstCofinsNaoTrib = $nomeado['CST_COFINS'] ?? null,
            'M810' => $nomeado['CST_COFINS'] = $this->cstCofinsNaoTrib,
            default => null,
        };

        $this->registros[$rec->reg][] = $nomeado;

        return null; // agregador: linha só em finalizar()
    }

    public function finalizar(): ?array
    {
        $m200 = $this->registros['M200'][0] ?? null;
        $m600 = $this->registros['M600'][0] ?? null;
        $z0110 = $this->registros['0110'][0] ?? null;

        if ($m200 === null && $m600 === null && $z0110 === null) {
            return null; // sem apuração PIS/COFINS no arquivo
        }

        $m200 ??= [];
        $m600 ??= [];
        $z0110 ??= [];

        return [
            // M200 — consolidação PIS do período
            'pis_nao_cumulativo' => $this->dec($m200['PIS_NAO_CUMULATIVO'] ?? null),
            'pis_credito_descontado' => $this->dec($m200['PIS_CREDITO_DESCONTADO'] ?? null),
            'pis_credito_desc_ant' => $this->dec($m200['PIS_CREDITO_DESC_ANT'] ?? null),
            'pis_nc_devida' => $this->dec($m200['PIS_NC_DEVIDA'] ?? null),
            'pis_retencao_nc' => $this->dec($m200['PIS_RETENCAO_NC'] ?? null),
            'pis_outras_deducoes_nc' => $this->dec($m200['PIS_OUTRAS_DEDUCOES_NC'] ?? null),
            'pis_nc_recolher' => $this->dec($m200['PIS_NC_RECOLHER'] ?? null),
            'pis_cumulativo' => $this->dec($m200['PIS_CUMULATIVO'] ?? null),
            'pis_retencao_cum' => $this->dec($m200['PIS_RETENCAO_CUM'] ?? null),
            'pis_outras_deducoes_cum' => $this->dec($m200['PIS_OUTRAS_DEDUCOES_CUM'] ?? null),
            'pis_cum_recolher' => $this->dec($m200['PIS_CUM_RECOLHER'] ?? null),
            'pis_total_recolher' => $this->dec($m200['PIS_TOTAL_RECOLHER'] ?? null),

            // M600 — consolidação COFINS do período
            'cofins_nao_cumulativo' => $this->dec($m600['COFINS_NAO_CUMULATIVO'] ?? null),
            'cofins_credito_descontado' => $this->dec($m600['COFINS_CREDITO_DESCONTADO'] ?? null),
            'cofins_credito_desc_ant' => $this->dec($m600['COFINS_CREDITO_DESC_ANT'] ?? null),
            'cofins_nc_devida' => $this->dec($m600['COFINS_NC_DEVIDA'] ?? null),
            'cofins_retencao_nc' => $this->dec($m600['COFINS_RETENCAO_NC'] ?? null),
            'cofins_outras_deducoes_nc' => $this->dec($m600['COFINS_OUTRAS_DEDUCOES_NC'] ?? null),
            'cofins_nc_recolher' => $this->dec($m600['COFINS_NC_RECOLHER'] ?? null),
            'cofins_cumulativo' => $this->dec($m600['COFINS_CUMULATIVO'] ?? null),
            'cofins_retencao_cum' => $this->dec($m600['COFINS_RETENCAO_CUM'] ?? null),
            'cofins_outras_deducoes_cum' => $this->dec($m600['COFINS_OUTRAS_DEDUCOES_CUM'] ?? null),
            'cofins_cum_recolher' => $this->dec($m600['COFINS_CUM_RECOLHER'] ?? null),
            'cofins_total_recolher' => $this->dec($m600['COFINS_TOTAL_RECOLHER'] ?? null),

            // 0110 — regime de incidência
            'cod_inc_tributaria' => Campos::texto($z0110['COD_INC_TRIB'] ?? null),
            'ind_apropriacao_credito' => Campos::texto($z0110['IND_APRO_CRED'] ?? null),
            'cod_tipo_contribuicao' => Campos::texto($z0110['COD_TIPO_CONT'] ?? null),
            'ind_regime_cumulativo' => Campos::texto($z0110['IND_REG_CUM'] ?? null),

            // Detalhamentos (jsonb)
            'pis_detalhes' => ['items' => $this->registros['M210'] ?? []],
            'cofins_detalhes' => ['items' => $this->registros['M610'] ?? []],
            // Receitas não tributadas: 1 linha por natureza da receita, com PIS (M410) e
            // COFINS (M810 casado por NAT_REC) lado a lado + o CST-pai carimbado.
            'pis_nao_tributado' => ['items' => $this->receitasNaoTributadas()],
            'cofins_recolher_detalhe' => ['items' => $this->registros['M605'] ?? []],

            // Backup cru
            'dados_brutos' => $this->registros,
        ];
    }

    /**
     * Receitas não tributadas/isentas por natureza da receita: cada M410 (PIS) casado com o
     * M810 (COFINS) de MESMA NAT_REC, mais o CST-pai. Shape que a tela lê direto
     * (CST_PIS/NAT_REC/VL_REC/VL_REC_COFINS). Sem M410/M810 → lista vazia.
     *
     * @return array<int, array<string, mixed>>
     */
    private function receitasNaoTributadas(): array
    {
        $cofinsPorNat = [];
        foreach ($this->registros['M810'] ?? [] as $m) {
            $cofinsPorNat[(string) ($m['NAT_REC'] ?? '')] = $m['VL_REC'] ?? '0';
        }

        $items = [];
        foreach ($this->registros['M410'] ?? [] as $m) {
            $nat = (string) ($m['NAT_REC'] ?? '');
            $items[] = [
                'CST_PIS' => $m['CST_PIS'] ?? null,
                'NAT_REC' => $m['NAT_REC'] ?? null,
                'VL_REC' => $m['VL_REC'] ?? '0',
                'VL_REC_COFINS' => $cofinsPorNat[$nat] ?? '0',
            ];
            unset($cofinsPorNat[$nat]);
        }

        // COFINS não tributado cuja natureza não apareceu no PIS (raro, mas não perder).
        foreach ($cofinsPorNat as $nat => $vlCofins) {
            $items[] = ['CST_PIS' => null, 'NAT_REC' => $nat, 'VL_REC' => '0', 'VL_REC_COFINS' => $vlCofins];
        }

        return $items;
    }

    /** Nomeia os campos de um registro do bloco M / 0110 (Guia EFD Contribuições). */
    private function nomear(SpedRecord $rec): array
    {
        $c = fn (int $i): ?string => $rec->campo($i);

        return match ($rec->reg) {
            // M200/M600: 12 valores $p[2..13] na mesma ordem (PIS e COFINS espelhados).
            'M200' => [
                'PIS_NAO_CUMULATIVO' => $c(2), 'PIS_CREDITO_DESCONTADO' => $c(3),
                'PIS_CREDITO_DESC_ANT' => $c(4), 'PIS_NC_DEVIDA' => $c(5),
                'PIS_RETENCAO_NC' => $c(6), 'PIS_OUTRAS_DEDUCOES_NC' => $c(7),
                'PIS_NC_RECOLHER' => $c(8), 'PIS_CUMULATIVO' => $c(9),
                'PIS_RETENCAO_CUM' => $c(10), 'PIS_OUTRAS_DEDUCOES_CUM' => $c(11),
                'PIS_CUM_RECOLHER' => $c(12), 'PIS_TOTAL_RECOLHER' => $c(13),
            ],
            'M600' => [
                'COFINS_NAO_CUMULATIVO' => $c(2), 'COFINS_CREDITO_DESCONTADO' => $c(3),
                'COFINS_CREDITO_DESC_ANT' => $c(4), 'COFINS_NC_DEVIDA' => $c(5),
                'COFINS_RETENCAO_NC' => $c(6), 'COFINS_OUTRAS_DEDUCOES_NC' => $c(7),
                'COFINS_NC_RECOLHER' => $c(8), 'COFINS_CUMULATIVO' => $c(9),
                'COFINS_RETENCAO_CUM' => $c(10), 'COFINS_OUTRAS_DEDUCOES_CUM' => $c(11),
                'COFINS_CUM_RECOLHER' => $c(12), 'COFINS_TOTAL_RECOLHER' => $c(13),
            ],
            '0110' => [
                'COD_INC_TRIB' => $c(2), 'IND_APRO_CRED' => $c(3),
                'COD_TIPO_CONT' => $c(4), 'IND_REG_CUM' => $c(5),
            ],
            // M210/M610 — apuração da contribuição por CST (base, alíquota, valor). CST é
            // texto (zeros à esquerda); valores viram dot-decimal pro number_format da tela.
            'M210' => [
                'COD_CONT' => Campos::texto($c(2)), 'VL_REC_BRT' => Campos::dec($c(3)),
                'VL_BC_CONT' => Campos::dec($c(4)), 'VL_BC_CONT_AJUS' => Campos::dec($c(7)),
                'ALIQ_PIS' => Campos::dec($c(8)), 'VL_CONT_APUR' => Campos::dec($c(11)),
                'VL_CONT' => Campos::dec($c(16)),
            ],
            'M610' => [
                'COD_CONT' => Campos::texto($c(2)), 'VL_REC_BRT' => Campos::dec($c(3)),
                'VL_BC_CONT' => Campos::dec($c(4)), 'VL_BC_CONT_AJUS' => Campos::dec($c(7)),
                'ALIQ_COFINS' => Campos::dec($c(8)), 'VL_CONT_APUR' => Campos::dec($c(11)),
                'VL_CONT' => Campos::dec($c(16)),
            ],
            // M400/M800 — receita não tributada por CST (PIS / COFINS). M410/M810 — o detalhe
            // por natureza da receita dentro do CST. CST/NAT_REC texto; valores dot-decimal.
            'M400' => ['CST_PIS' => Campos::texto($c(2)), 'VL_TOT_REC' => Campos::dec($c(3))],
            'M410' => ['NAT_REC' => Campos::texto($c(2)), 'VL_REC' => Campos::dec($c(3))],
            'M800' => ['CST_COFINS' => Campos::texto($c(2)), 'VL_TOT_REC' => Campos::dec($c(3))],
            'M810' => ['NAT_REC' => Campos::texto($c(2)), 'VL_REC' => Campos::dec($c(3))],
            'M605' => [
                'NUM_CAMPO' => Campos::texto($c(2)), 'COD_REC' => Campos::texto($c(3)),
                'VL_DEBITO' => Campos::dec($c(4)),
            ],
            default => ['_campos' => array_slice($rec->campos, 2)],
        };
    }

    /** Decimal BR → float (round 2). Espelha o dec() do bloco E. */
    private function dec(mixed $v): float
    {
        if ($v === null || $v === '') {
            return 0.0;
        }

        $s = str_replace(',', '.', str_replace('.', '', (string) $v));
        $n = (float) $s;

        return is_nan($n) ? 0.0 : round($n, 2);
    }
}
