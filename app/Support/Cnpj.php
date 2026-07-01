<?php

namespace App\Support;

/**
 * Helpers stateless de CNPJ. Assumem documento numérico (normalizam os dígitos por garantia).
 *
 * A CND Federal (RFB/PGFN) é unificada por base e só é emitida para a MATRIZ (ordem 0001),
 * então o app deriva o CNPJ da matriz a partir de qualquer filial. Ver
 * ResultadoDetalhePresenter::notaMatrizFederal e docs/.
 *
 * (Arquivo restaurado 2026-07-01: era untracked e foi wipeado do working tree, mas é
 * dependência de código commitado — sua ausência quebrava blocos()/dossiê. Se outra sessão
 * mantinha uma versão mais rica, reconciliar.)
 */
final class Cnpj
{
    /** Apenas os dígitos do documento. */
    public static function digitos(?string $doc): string
    {
        return preg_replace('/\D/', '', (string) $doc) ?? '';
    }

    /** True se for um CNPJ (14 dígitos) de FILIAL — ordem (posições 9-12) diferente de 0001. */
    public static function ehFilial(string $cnpj): bool
    {
        $d = self::digitos($cnpj);

        return strlen($d) === 14 && substr($d, 8, 4) !== '0001';
    }

    /**
     * CNPJ da matriz a partir de qualquer CNPJ da base: 8 dígitos da base + ordem 0001 +
     * dígitos verificadores recalculados (mod-11). Se não for um CNPJ de 14 dígitos, devolve
     * os dígitos originais sem alterar.
     */
    public static function matriz(string $cnpj): string
    {
        $d = self::digitos($cnpj);
        if (strlen($d) !== 14) {
            return $d;
        }

        $base = substr($d, 0, 8).'0001';

        return $base.self::dv($base);
    }

    /** Formata XX.XXX.XXX/XXXX-XX (CNPJ). Se não tiver 14 dígitos, devolve o valor original. */
    public static function formatar(string $cnpj): string
    {
        $d = self::digitos($cnpj);
        if (strlen($d) !== 14) {
            return $cnpj;
        }

        return substr($d, 0, 2).'.'.substr($d, 2, 3).'.'.substr($d, 5, 3).'/'.substr($d, 8, 4).'-'.substr($d, 12, 2);
    }

    /** Os 2 dígitos verificadores (mod-11) a partir dos 12 primeiros dígitos do CNPJ. */
    private static function dv(string $base12): string
    {
        $calc = static function (string $numeros): int {
            // dv1 usa peso inicial 5 (12 dígitos); dv2 usa 6 (13 dígitos). Decresce até 2 e
            // volta pra 9 — sequência oficial [5,4,3,2,9,8,7,6,5,4,3,2] / [6,5,...].
            $peso = strlen($numeros) === 12 ? 5 : 6;
            $soma = 0;
            foreach (str_split($numeros) as $n) {
                $soma += (int) $n * $peso;
                $peso = $peso === 2 ? 9 : $peso - 1;
            }
            $resto = $soma % 11;

            return $resto < 2 ? 0 : 11 - $resto;
        };

        $dv1 = $calc($base12);
        $dv2 = $calc($base12.$dv1);

        return (string) $dv1.(string) $dv2;
    }
}
