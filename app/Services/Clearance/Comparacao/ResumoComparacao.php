<?php

namespace App\Services\Clearance\Comparacao;

final class ResumoComparacao
{
    public function __construct(
        public readonly int $headerDivergencias,
        public readonly int $totaisDivergencias,
        public readonly int $itensDivergentes,
        public readonly int $itensFantasmaDeclarado,
        public readonly int $itensFantasmaSefaz,
        public readonly string $severidade,
        public readonly bool $sefazAusente,
        public readonly bool $declaradoAusente,
    ) {}
}
