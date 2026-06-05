<?php

namespace App\Services\Consultas\Dto;

class ResultadoFonte
{
    public function __construct(
        public readonly string $chave,
        public readonly array $dados,
        public readonly string $status,
        public readonly int $custoCreditos = 0,
        public readonly ?string $mensagem = null,
    ) {}

    public function ehFalhaEstornavel(): bool
    {
        return in_array($this->status, ['fatal'], true);
    }
}
