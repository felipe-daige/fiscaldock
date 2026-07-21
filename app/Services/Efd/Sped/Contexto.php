<?php

namespace App\Services\Efd\Sped;

/**
 * Documento-pai corrente durante a caminhada do arquivo (C100/D100…). É o
 * "Anotador" do n8n: os registros-filho (C170/C190/D190) herdam a
 * chave/identificação daqui em vez de re-parsear o cabeçalho.
 */
class Contexto
{
    public function __construct(
        public readonly string $reg,
        public readonly ?string $chave,
        public readonly ?string $numero,
        public readonly ?string $serie,
        public readonly ?string $modelo,
        public readonly ?string $tipoOperacao,
    ) {}
}
