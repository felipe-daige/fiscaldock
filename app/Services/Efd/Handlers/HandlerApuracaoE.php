<?php

namespace App\Services\Efd\Handlers;

use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;

/**
 * Bloco E (apuração ICMS + ICMS-ST + DIFAL/FCP + IPI) → 1 linha em `efd_apuracoes_icms`
 * por importação. Porta `docs/n8n/extracao-efd-icms-ipi/code-apuracao-icms.js` 1:1.
 *
 * Agregador: `mapear` nomeia + acumula cada E-registro e devolve null; `finalizar`
 * monta a linha única (colunas tipadas E110/E210 + obrigações E116/E250 jsonb +
 * DIFAL/IPI jsonb + dados_brutos). ST single-UF (E200[0]/E210[0]); multi-UF só em
 * dados_brutos. IPI/DIFAL vêm null em comércio (cabeados p/ indústria — deferido no n8n).
 *
 * Estado por instância ⇒ o FiscalDriver reusa a MESMA instância no job inteiro.
 */
class HandlerApuracaoE implements HandlerAgregador
{
    /** Campos texto (não decimais) — espelha o camposTexto do code-apuracao-icms.js. */
    private const CAMPOS_TEXTO = [
        'ICMS_DT_INI', 'ICMS_DT_FIN', 'ICMS_COD_OBRIGACAO', 'ICMS_DATA_VENCIMENTO',
        'ICMS_COD_RECEITA', 'ICMS_NUM_PROCESSO', 'ICMS_IND_PROCESSO', 'ICMS_PROCESSO',
        'ICMS_TXT_COMPLEMENTAR', 'ICMS_MES_REFERENCIA',
        'ST_UF', 'ST_DT_INI', 'ST_DT_FIN', 'ST_IND_MOVIMENTACAO',
        'ST_COD_OBRIGACAO', 'ST_DATA_VENCIMENTO', 'ST_COD_RECEITA',
        'ST_NUM_PROCESSO', 'ST_IND_PROCESSO', 'ST_PROCESSO',
        'ST_TXT_COMPLEMENTAR', 'ST_MES_REFERENCIA',
        'DIFAL_UF', 'DIFAL_DT_INI', 'DIFAL_DT_FIN', 'DIFAL_IND_MOVIMENTACAO',
        'IPI_IND_APURACAO', 'IPI_DT_INI', 'IPI_DT_FIN',
    ];

    /** @var array<string, array<int, array<string, mixed>>> registros nomeados por REG */
    private array $registros = [];

    public function registros(): array
    {
        return ['E100', 'E110', 'E116', 'E200', 'E210', 'E250', 'E300', 'E310', 'E500', 'E520'];
    }

    public function tabela(): string
    {
        return 'efd_apuracoes_icms';
    }

    public function mapear(SpedRecord $rec, ?Contexto $pai): ?array
    {
        $this->registros[$rec->reg][] = $this->nomear($rec);

        return null; // agregador: linha só em finalizar()
    }

    public function finalizar(): ?array
    {
        $e100 = $this->registros['E100'][0] ?? null;
        if ($e100 === null) {
            return null; // sem apuração de ICMS no arquivo
        }

        $e110 = $this->registros['E110'][0] ?? [];
        $e200 = $this->registros['E200'][0] ?? [];
        $e210 = $this->registros['E210'][0] ?? [];

        $temDifal = isset($this->registros['E300']) || isset($this->registros['E310']);
        $temIpi = isset($this->registros['E500']) || isset($this->registros['E520']);

        return [
            'periodo_inicio' => Campos::dataIso($e100['ICMS_DT_INI'] ?? null),
            'periodo_fim' => Campos::dataIso($e100['ICMS_DT_FIN'] ?? null),

            // E110 — ICMS próprio (colunas tipadas)
            'icms_tot_debitos' => $this->dec($e110['ICMS_TOT_DEBITOS'] ?? null),
            'icms_aj_debitos' => $this->dec($e110['ICMS_AJ_DEBITOS'] ?? null),
            'icms_tot_aj_debitos' => $this->dec($e110['ICMS_TOT_AJ_DEBITOS'] ?? null),
            'icms_estornos_credito' => $this->dec($e110['ICMS_ESTORNOS_CREDITO'] ?? null),
            'icms_tot_creditos' => $this->dec($e110['ICMS_TOT_CREDITOS'] ?? null),
            'icms_aj_creditos' => $this->dec($e110['ICMS_AJ_CREDITOS'] ?? null),
            'icms_tot_aj_creditos' => $this->dec($e110['ICMS_TOT_AJ_CREDITOS'] ?? null),
            'icms_estornos_debito' => $this->dec($e110['ICMS_ESTORNOS_DEBITO'] ?? null),
            'icms_sld_credor_ant' => $this->dec($e110['ICMS_SLD_CREDOR_ANT'] ?? null),
            'icms_sld_apurado' => $this->dec($e110['ICMS_SLD_APURADO'] ?? null),
            'icms_tot_deducoes' => $this->dec($e110['ICMS_TOT_DEDUCOES'] ?? null),
            'icms_a_recolher' => $this->dec($e110['ICMS_A_RECOLHER'] ?? null),
            'icms_sld_credor_transportar' => $this->dec($e110['ICMS_SLD_CREDOR_TRANSPORTAR'] ?? null),
            'icms_deb_especiais' => $this->dec($e110['ICMS_DEB_ESPECIAIS'] ?? null),

            // E200/E210 — ICMS-ST (só 1ª UF; multi-UF em dados_brutos)
            'st_uf' => Campos::texto($e200['ST_UF'] ?? null),
            'st_ind_movimentacao' => Campos::texto($e210['ST_IND_MOVIMENTACAO'] ?? null),
            'st_sld_credor_ant' => $this->dec($e210['ST_SLD_CREDOR_ANT'] ?? null),
            'st_devolucoes' => $this->dec($e210['ST_DEVOLUCOES'] ?? null),
            'st_ressarcimentos' => $this->dec($e210['ST_RESSARCIMENTOS'] ?? null),
            'st_outros_creditos' => $this->dec($e210['ST_OUTROS_CREDITOS'] ?? null),
            'st_aj_creditos' => $this->dec($e210['ST_AJ_CREDITOS'] ?? null),
            'st_retencao' => $this->dec($e210['ST_RETENCAO'] ?? null),
            'st_outros_debitos' => $this->dec($e210['ST_OUTROS_DEBITOS'] ?? null),
            'st_aj_debitos' => $this->dec($e210['ST_AJ_DEBITOS'] ?? null),
            'st_sld_devedor_ant' => $this->dec($e210['ST_SLD_DEVEDOR_ANT'] ?? null),
            'st_deducoes' => $this->dec($e210['ST_DEDUCOES'] ?? null),
            'st_icms_recolher' => $this->dec($e210['ST_ICMS_RECOLHER'] ?? null),
            'st_sld_credor_transportar' => $this->dec($e210['ST_SLD_CREDOR_TRANSPORTAR'] ?? null),
            'st_deb_especiais' => $this->dec($e210['ST_DEB_ESPECIAIS'] ?? null),

            // Obrigações (todas as ocorrências) + DIFAL/IPI opcionais — jsonb
            'icms_obrigacoes' => ['items' => $this->parseArray($this->registros['E116'] ?? [])],
            'st_obrigacoes' => ['items' => $this->parseArray($this->registros['E250'] ?? [])],
            'difal_fcp' => $temDifal ? [
                'E300' => $this->parseArray($this->registros['E300'] ?? []),
                'E310' => $this->parseArray($this->registros['E310'] ?? []),
            ] : null,
            'ipi' => $temIpi ? [
                'E500' => $this->parseArray($this->registros['E500'] ?? []),
                'E520' => $this->parseArray($this->registros['E520'] ?? []),
            ] : null,

            // Backup cru (todos os E-registros nomeados)
            'dados_brutos' => $this->registros,
        ];
    }

    /** Nomeia os campos brutos de um E-registro conforme o Guia Prático EFD ICMS/IPI. */
    private function nomear(SpedRecord $rec): array
    {
        $c = fn (int $i): ?string => $rec->campo($i);

        return match ($rec->reg) {
            'E100' => [
                'ICMS_DT_INI' => $c(2), 'ICMS_DT_FIN' => $c(3),
            ],
            'E110' => [
                'ICMS_TOT_DEBITOS' => $c(2), 'ICMS_AJ_DEBITOS' => $c(3),
                'ICMS_TOT_AJ_DEBITOS' => $c(4), 'ICMS_ESTORNOS_CREDITO' => $c(5),
                'ICMS_TOT_CREDITOS' => $c(6), 'ICMS_AJ_CREDITOS' => $c(7),
                'ICMS_TOT_AJ_CREDITOS' => $c(8), 'ICMS_ESTORNOS_DEBITO' => $c(9),
                'ICMS_SLD_CREDOR_ANT' => $c(10), 'ICMS_SLD_APURADO' => $c(11),
                'ICMS_TOT_DEDUCOES' => $c(12), 'ICMS_A_RECOLHER' => $c(13),
                'ICMS_SLD_CREDOR_TRANSPORTAR' => $c(14), 'ICMS_DEB_ESPECIAIS' => $c(15),
            ],
            'E116' => [
                'ICMS_COD_OBRIGACAO' => $c(2), 'ICMS_VALOR_OBRIGACAO' => $c(3),
                'ICMS_DATA_VENCIMENTO' => $c(4), 'ICMS_COD_RECEITA' => $c(5),
                'ICMS_NUM_PROCESSO' => $c(6), 'ICMS_IND_PROCESSO' => $c(7),
                'ICMS_PROCESSO' => $c(8), 'ICMS_TXT_COMPLEMENTAR' => $c(9),
                'ICMS_MES_REFERENCIA' => $c(10),
            ],
            'E200' => [
                'ST_UF' => $c(2), 'ST_DT_INI' => $c(3), 'ST_DT_FIN' => $c(4),
            ],
            'E210' => [
                'ST_IND_MOVIMENTACAO' => $c(2), 'ST_SLD_CREDOR_ANT' => $c(3),
                'ST_DEVOLUCOES' => $c(4), 'ST_RESSARCIMENTOS' => $c(5),
                'ST_OUTROS_CREDITOS' => $c(6), 'ST_AJ_CREDITOS' => $c(7),
                'ST_RETENCAO' => $c(8), 'ST_OUTROS_DEBITOS' => $c(9),
                'ST_AJ_DEBITOS' => $c(10), 'ST_SLD_DEVEDOR_ANT' => $c(11),
                'ST_DEDUCOES' => $c(12), 'ST_ICMS_RECOLHER' => $c(13),
                'ST_SLD_CREDOR_TRANSPORTAR' => $c(14), 'ST_DEB_ESPECIAIS' => $c(15),
            ],
            'E250' => [
                'ST_COD_OBRIGACAO' => $c(2), 'ST_VALOR_OBRIGACAO' => $c(3),
                'ST_DATA_VENCIMENTO' => $c(4), 'ST_COD_RECEITA' => $c(5),
                'ST_NUM_PROCESSO' => $c(6), 'ST_IND_PROCESSO' => $c(7),
                'ST_PROCESSO' => $c(8), 'ST_TXT_COMPLEMENTAR' => $c(9),
                'ST_MES_REFERENCIA' => $c(10),
            ],
            'E300' => [
                'DIFAL_UF' => $c(2), 'DIFAL_DT_INI' => $c(3), 'DIFAL_DT_FIN' => $c(4),
            ],
            'E500' => [
                'IPI_IND_APURACAO' => $c(2), 'IPI_DT_INI' => $c(3), 'IPI_DT_FIN' => $c(4),
            ],
            'E520' => [
                'IPI_VL_SD_ANT' => $c(2), 'IPI_VL_DEB' => $c(3), 'IPI_VL_CRED' => $c(4),
                'IPI_VL_OD' => $c(5), 'IPI_VL_SC' => $c(6), 'IPI_VL_SD' => $c(7),
            ],
            // E310 (apuração DIFAL/FCP): layout varia por versão + deferido (null em
            // comércio). Nomeia o indicador e preserva a linha crua p/ não perder dado.
            'E310' => [
                'DIFAL_IND_MOVIMENTACAO' => $c(2),
                '_campos' => array_slice($rec->campos, 2),
            ],
            default => ['_campos' => array_slice($rec->campos, 2)],
        };
    }

    /** Decimal BR → float (round 2), 1:1 com o dec() do code-apuracao-icms.js. */
    private function dec(mixed $v): float
    {
        if ($v === null || $v === '') {
            return 0.0;
        }

        // Remove '.' (defensivo — no-op no SPED) e troca ',' por '.'.
        $s = str_replace(',', '.', str_replace('.', '', (string) $v));
        $n = (float) $s;

        return is_nan($n) ? 0.0 : round($n, 2);
    }

    /**
     * Tipa um registro nomeado: texto preserva string; demais viram float (dec).
     * Valores array (ex.: _campos crus) passam intactos.
     *
     * @param  array<string, mixed>  $reg
     * @return array<string, mixed>
     */
    private function parseRegistro(array $reg): array
    {
        $out = [];
        foreach ($reg as $k => $v) {
            if (is_array($v)) {
                $out[$k] = $v;
            } elseif (in_array($k, self::CAMPOS_TEXTO, true)) {
                $out[$k] = (string) ($v ?? '');
            } else {
                $out[$k] = $this->dec($v);
            }
        }

        return $out;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function parseArray(array $items): array
    {
        return array_map(fn (array $r): array => $this->parseRegistro($r), $items);
    }
}
