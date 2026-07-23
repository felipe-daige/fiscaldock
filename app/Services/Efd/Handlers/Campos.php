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

    /** PJ (14 dígitos) | PF (11) | null. O insert direto da engine pula o hook do Model. */
    public static function tipoDocumento(?string $documento): ?string
    {
        return match (strlen(preg_replace('/\D/', '', (string) $documento))) {
            14 => 'PJ',
            11 => 'PF',
            default => null,
        };
    }

    /** Prefixo do código IBGE de município (2 primeiros dígitos) → sigla da UF. */
    private const UF_POR_CODIGO_IBGE = [
        '11' => 'RO', '12' => 'AC', '13' => 'AM', '14' => 'RR', '15' => 'PA', '16' => 'AP', '17' => 'TO',
        '21' => 'MA', '22' => 'PI', '23' => 'CE', '24' => 'RN', '25' => 'PB', '26' => 'PE', '27' => 'AL', '28' => 'SE', '29' => 'BA',
        '31' => 'MG', '32' => 'ES', '33' => 'RJ', '35' => 'SP',
        '41' => 'PR', '42' => 'SC', '43' => 'RS',
        '50' => 'MS', '51' => 'MT', '52' => 'GO', '53' => 'DF',
    ];

    /** UF a partir do código IBGE de município (0150 não traz UF, mas o COD_MUN a codifica). */
    public static function ufPorCodigoMunicipio(?string $codMun): ?string
    {
        $cod = preg_replace('/\D/', '', (string) $codMun);

        return strlen($cod) >= 2 ? (self::UF_POR_CODIGO_IBGE[substr($cod, 0, 2)] ?? null) : null;
    }
}
