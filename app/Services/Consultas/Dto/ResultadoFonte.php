<?php

namespace App\Services\Consultas\Dto;

class ResultadoFonte
{
    public function __construct(
        public readonly string $chave,
        public readonly array $dados,
        public readonly string $status,
        public readonly float $custoCreditos = 0,
        public readonly ?string $mensagem = null,
    ) {}

    public function ehFalhaEstornavel(): bool
    {
        // Classe `retry` (timeout/instabilidade do provedor, ex. código 600) também é estornável:
        // o cliente não recebeu o dado, então o valor volta no fechamento do lote (igual `fatal`).
        // Base econômica da reconsulta com desconto (docs/superpowers/specs/2026-06-24-reconsulta-fontes-falha-design.md).
        return in_array($this->status, ['fatal', 'retry'], true);
    }
}
