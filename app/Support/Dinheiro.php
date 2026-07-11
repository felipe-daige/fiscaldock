<?php

namespace App\Support;

final class Dinheiro
{
    public static function brl(float|int $reais): string
    {
        // NBSP entre "R$" e o número: valor monetário nunca quebra linha no meio
        // (mesma convenção do Intl/toLocaleString do JS). deBrl() já o remove.
        return 'R$'."\u{A0}".number_format((float) $reais, 2, ',', '.');
    }

    /**
     * Inverso de brl(): "1.234,56" (com ou sem prefixo "R$") → 1234.56.
     * Para reconverter valores já formatados em pt-BR em número real
     * (ex.: células numéricas do XLSX a partir de datasets formatados).
     */
    public static function deBrl(string $brl): float
    {
        $s = trim(str_replace(['R$', "\u{A0}", ' '], '', $brl));

        return (float) str_replace(',', '.', str_replace('.', '', $s));
    }
}
