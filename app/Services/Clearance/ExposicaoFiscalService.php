<?php

namespace App\Services\Clearance;

use Carbon\CarbonImmutable;

/**
 * Monetiza a exposição fiscal de uma divergência de clearance.
 *
 * Escopo MVP (2026-06-16): multa de ofício + decadência. Selic acumulada = fase 2
 * (exige tabela Selic mensal). Ver docs/clearance/pdf-executivo.md.
 *
 * - Multa de ofício: 75% sobre a base (crédito/imposto exposto) — art. 44, I da Lei 9.430/96.
 * - Decadência: 5 anos contados da emissão do documento — art. 173, I do CTN.
 */
class ExposicaoFiscalService
{
    /** Multa de ofício (art. 44, I, Lei 9.430/96). */
    public const ALIQUOTA_MULTA_OFICIO = 0.75;

    /** Prazo decadencial (art. 173, I, CTN). */
    public const ANOS_DECADENCIA = 5;

    public function multa(float $base): float
    {
        return round($base * self::ALIQUOTA_MULTA_OFICIO, 2);
    }

    public function decadencia(?string $dataEmissao): ?CarbonImmutable
    {
        if ($dataEmissao === null || trim($dataEmissao) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($dataEmissao)->addYears(self::ANOS_DECADENCIA);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{base: float, multa: float, total: float, decadencia: ?CarbonImmutable, decadencia_label: string}
     */
    public function montar(float $base, ?string $dataEmissao): array
    {
        $base = round($base, 2);
        $multa = $this->multa($base);
        $decadencia = $this->decadencia($dataEmissao);

        return [
            'base' => $base,
            'multa' => $multa,
            'total' => round($base + $multa, 2),
            'decadencia' => $decadencia,
            'decadencia_label' => $decadencia?->format('d/m/Y') ?? '—',
        ];
    }
}
