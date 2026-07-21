<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteListaInfoSimples;

/** Banco Nacional de Falências e Recuperações (TST). */
class FalenciasFonte extends FonteListaInfoSimples
{
    public function chave(): string
    {
        return 'falencias';
    }

    public function slug(): string
    {
        return 'tribunal/tst/banco-falencias';
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.falencias', 1.00);
    }

    protected function chavesLista(): array
    {
        return ['falencias', 'processos', 'registros', 'ocorrencias'];
    }

    protected function camposResumo(): array
    {
        return ['processo', 'numero_processo', 'tipo', 'situacao', 'vara', 'comarca', 'uf', 'data'];
    }
}
