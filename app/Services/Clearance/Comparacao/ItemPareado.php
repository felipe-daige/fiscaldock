<?php

namespace App\Services\Clearance\Comparacao;

final class ItemPareado
{
    public function __construct(
        public readonly ItemNormalizado|ComponenteCte|null $declarado,
        public readonly ItemNormalizado|ComponenteCte|null $sefaz,
        public readonly string $matchType,
        public readonly array $diffs,
        public readonly bool $temDivergencia,
    ) {}
}
