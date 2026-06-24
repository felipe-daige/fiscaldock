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
}
