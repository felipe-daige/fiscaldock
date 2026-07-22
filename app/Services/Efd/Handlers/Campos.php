<?php

namespace App\Services\Efd\Handlers;

/**
 * Normalizações de campo SPED, compartilhadas pelos handlers (motor-laravel.md §10.1).
 * SPED usa vírgula decimal, datas DDMMAAAA e campos vazios/ausentes. Preserva strings
 * (zeros à esquerda) — nunca numeriza códigos.
 */
class Campos
{
    /** Decimal BR ("5590,70") → string canônica ("5590.70"); vazio/null → "0". */
    public static function dec(?string $v): string
    {
        $v = trim((string) $v);

        return $v === '' ? '0' : str_replace(',', '.', $v);
    }

    /** Texto opcional: trim; vazio → null. Preserva o literal (não numeriza). */
    public static function texto(?string $v): ?string
    {
        $v = trim((string) $v);

        return $v === '' ? null : $v;
    }

    /** Inteiro opcional (CFOP, NUM_ITEM…): vazio → null; senão (int). */
    public static function inteiro(?string $v): ?int
    {
        $v = trim((string) $v);

        return $v === '' ? null : (int) $v;
    }

    /** Data SPED DDMMAAAA → ISO AAAA-MM-DD; comprimento ≠ 8 → null. */
    public static function dataIso(?string $v): ?string
    {
        $v = trim((string) $v);

        if (strlen($v) !== 8) {
            return null;
        }

        return substr($v, 4, 4).'-'.substr($v, 2, 2).'-'.substr($v, 0, 2);
    }
}
