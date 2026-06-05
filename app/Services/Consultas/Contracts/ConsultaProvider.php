<?php

namespace App\Services\Consultas\Contracts;

use App\Services\Consultas\Dto\RespostaProvider;

interface ConsultaProvider
{
    /** Chave do provider: 'minhareceita' | 'infosimples'. */
    public function nome(): string;

    /** Faz a chamada HTTP e classifica o código bruto. Não normaliza. */
    public function consultar(string $slug, array $params): RespostaProvider;
}
