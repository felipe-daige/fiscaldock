<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteListaInfoSimples;

/** Portal da Transparência — CEIS (Empresas Inidôneas e Suspensas). */
class CeisFonte extends FonteListaInfoSimples
{
    public function chave(): string
    {
        return 'ceis';
    }

    public function slug(): string
    {
        return 'portal-transparencia/ceis';
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.ceis', 1.00);
    }

    protected function chavesLista(): array
    {
        return ['sancoes', 'registros', 'ocorrencias'];
    }

    protected function camposResumo(): array
    {
        return ['tipo_sancao', 'orgao_sancionador', 'data_inicio', 'data_fim', 'uf'];
    }
}
