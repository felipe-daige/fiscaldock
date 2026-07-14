<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Parse canônico de datas no formato brasileiro d/m/Y (padrão das fontes InfoSimples,
 * ex.: data_validade de certidões). Carbon::parse('05/08/2026') interpreta m/d/Y
 * (8 de maio) — usar este helper sempre que a origem do dado for pt-BR.
 */
class DataBr
{
    public static function parse(?string $valor): ?Carbon
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $valor)) {
            try {
                return Carbon::createFromFormat('d/m/Y', $valor)->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        // Formatos não-BR (ISO 8601 etc.) seguem o parse padrão.
        try {
            return Carbon::parse($valor);
        } catch (\Throwable) {
            return null;
        }
    }
}
