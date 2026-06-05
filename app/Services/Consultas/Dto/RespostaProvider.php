<?php

namespace App\Services\Consultas\Dto;

class RespostaProvider
{
    public function __construct(
        public readonly string $status,   // sucesso|nao_encontrado|erro_participante|retry|fatal
        public readonly int $httpCode,
        public readonly array $raw,
        public readonly ?string $mensagem = null,
    ) {}
}
