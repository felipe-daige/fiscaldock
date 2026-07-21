<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteListaInfoSimples;

/** CNJ — Cadastro de Improbidade Administrativa e Inelegibilidade. */
class ImprobidadeFonte extends FonteListaInfoSimples
{
    public function chave(): string
    {
        return 'improbidade';
    }

    public function slug(): string
    {
        return 'cnj/improbidade';
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.improbidade', 1.00);
    }

    protected function chavesLista(): array
    {
        return ['condenacoes', 'processos', 'registros', 'ocorrencias'];
    }

    protected function camposResumo(): array
    {
        return ['processo', 'numero_processo', 'tribunal', 'tipo_pena', 'situacao', 'data_transito'];
    }
}
