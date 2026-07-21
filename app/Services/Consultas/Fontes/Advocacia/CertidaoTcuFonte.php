<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteListaInfoSimples;

/**
 * TCU — Consulta Consolidada de PJ (inidôneos + inabilitados + certidões, em 1 chamada).
 * Shape de lista: nada consta = Negativa.
 */
class CertidaoTcuFonte extends FonteListaInfoSimples
{
    public function chave(): string
    {
        return 'certidao_tcu';
    }

    public function slug(): string
    {
        return 'tcu/consolidada-pj';
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.certidao_tcu', 1.00);
    }

    protected function chavesLista(): array
    {
        return ['ocorrencias', 'registros', 'processos', 'inidoneos', 'certidoes'];
    }
}
