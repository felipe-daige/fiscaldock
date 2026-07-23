<?php

namespace App\Support\Efd;

/**
 * Classificação canônica do MODELO de documento fiscal (SPED) em bloco de resumo.
 * Fonte única — evita o drift que fazia NFC-e (65) sumir num site e CT-e OS (67) cair
 * em mercadorias noutro. Consumido por EfdResumoBuilder, EfdProgressoBuilder,
 * PersistenciaEngine e BiService (via SQL).
 */
class ModeloDocumento
{
    /** NFS-e municipal (bloco A). */
    public const MODELO_SERVICO = '00';

    /** NFC-e — venda a consumidor final (varejo). */
    public const MODELOS_CONSUMIDOR = ['65'];

    /** CT-e / CT-e OS (transporte). */
    public const MODELOS_TRANSPORTE = ['57', '67'];

    /**
     * Bloco de 4 vias (consumidor separado de mercadoria) — usado no resumo detalhado.
     * Tudo que não é serviço/consumidor/transporte é mercadoria (NF-e 55, avulsa, produtor).
     */
    public static function bucket(?string $modelo): string
    {
        $m = trim((string) $modelo);

        if ($m === self::MODELO_SERVICO) {
            return 'notas_servicos';
        }
        if (in_array($m, self::MODELOS_CONSUMIDOR, true)) {
            return 'notas_consumidor';
        }
        if (in_array($m, self::MODELOS_TRANSPORTE, true)) {
            return 'notas_transportes';
        }

        return 'notas_mercadorias';
    }

    /**
     * Bloco de 3 vias — consumidor (NFC-e) dobra em mercadorias. Usado onde não há série
     * própria de consumidor (strip de progresso ao vivo, volume por bloco do BI).
     */
    public static function bucketAgrupado(?string $modelo): string
    {
        $b = self::bucket($modelo);

        return $b === 'notas_consumidor' ? 'notas_mercadorias' : $b;
    }

    /**
     * Expressão SQL CASE equivalente ao bucketAgrupado (3 vias), pra agregações que
     * classificam no banco. `$coluna` deve ser um nome de coluna confiável (nunca input).
     */
    public static function sqlBlocoAgrupado(string $coluna): string
    {
        $transporte = "'".implode("','", self::MODELOS_TRANSPORTE)."'";

        return "CASE WHEN {$coluna} = '".self::MODELO_SERVICO."' THEN 'notas_servicos'"
            ." WHEN {$coluna} IN ({$transporte}) THEN 'notas_transportes'"
            .' ELSE '."'notas_mercadorias' END";
    }
}
