<?php

namespace App\Services\Clearance\Comparacao;

final class ItemNormalizado
{
    public function __construct(
        public readonly ?string $cProd,
        public readonly int $nItem,
        public readonly ?string $xProd,
        public readonly ?string $ncm,
        public readonly ?string $cfop,
        public readonly ?float $qCom,
        public readonly ?string $uCom,
        public readonly ?float $vUnCom,
        public readonly ?float $vProd,
    ) {}
}
