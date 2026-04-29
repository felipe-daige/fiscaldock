<?php

namespace App\Services\Clearance\Comparacao;

final class Comparacao
{
    /**
     * @param  array<int, CampoComparado>  $headerDiff
     * @param  array<string, array<int, CampoComparado>>  $partesDiff
     * @param  array<int, CampoComparado>  $totaisDiff
     * @param  array<int, ItemPareado>  $itensPareados
     */
    public function __construct(
        public readonly string $chave,
        public readonly string $tipoDocumento,
        public readonly ?NotaNormalizada $declarado,
        public readonly ?NotaNormalizada $sefaz,
        public readonly array $headerDiff,
        public readonly array $partesDiff,
        public readonly array $totaisDiff,
        public readonly array $itensPareados,
        public readonly ResumoComparacao $resumo,
    ) {}
}
