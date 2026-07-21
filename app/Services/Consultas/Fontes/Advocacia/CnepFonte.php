<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteListaInfoSimples;

/** Portal da Transparência — CNEP (Empresas Punidas, Lei Anticorrupção). */
class CnepFonte extends FonteListaInfoSimples
{
    public function chave(): string
    {
        return 'cnep';
    }

    public function slug(): string
    {
        return 'portal-transparencia/cnep';
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.cnep', 1.00);
    }

    protected function chavesLista(): array
    {
        return ['sancoes', 'registros', 'ocorrencias'];
    }

    protected function camposResumo(): array
    {
        return ['tipo_sancao', 'orgao_sancionador', 'data_inicio', 'data_fim', 'valor_multa'];
    }
}
