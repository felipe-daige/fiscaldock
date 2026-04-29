<?php

namespace App\Services\Clearance\Comparacao;

final class ComponenteCte
{
    public function __construct(
        public readonly string $nome,
        public readonly float $valor,
    ) {}
}
