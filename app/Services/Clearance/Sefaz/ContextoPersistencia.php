<?php

namespace App\Services\Clearance\Sefaz;

final class ContextoPersistencia
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $clienteId = null,
        public readonly ?int $consultaLoteId = null,
        public readonly ?int $creditTransactionId = null,
        public readonly ?string $correlationId = null,
        public readonly float $custo = 0.0,
    ) {}
}
