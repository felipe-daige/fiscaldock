<?php

namespace App\Services\Consultas\Fiscal;

/**
 * Helpers de classificação e datas compartilhados pelos serviços de resumo fiscal.
 */
trait AgregacaoFiscalHelpers
{
    protected function papelDe(bool $temEntrada, bool $temSaida): string
    {
        return match (true) {
            $temEntrada && $temSaida => 'ambos',
            $temEntrada => 'fornecedor',
            default => 'cliente',
        };
    }

    protected function menorData(?string $atual, ?string $nova): ?string
    {
        $nova = $nova ? substr((string) $nova, 0, 10) : null;
        if ($nova === null) {
            return $atual;
        }

        return $atual === null || $nova < $atual ? $nova : $atual;
    }

    protected function maiorData(?string $atual, ?string $nova): ?string
    {
        $nova = $nova ? substr((string) $nova, 0, 10) : null;
        if ($nova === null) {
            return $atual;
        }

        return $atual === null || $nova > $atual ? $nova : $atual;
    }

    /** Teto de itens buscados/expandíveis por lista no card de panorama. */
    protected function panoramaMaximo(): int
    {
        return (int) config('consultas.panorama_fiscal.maximo', 30);
    }

    /** Top CFOPs mostrados por contraparte (participante × empresa) no resumo fiscal.
        (Restaurado 2026-07-01: definição perdida do trait, quebrava o PDF do lote.) */
    protected function cfopsPorContraparteNum(): int
    {
        return (int) config('consultas.panorama_fiscal.cfops_contraparte', 5);
    }
}
