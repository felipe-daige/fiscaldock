<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteListaInfoSimples;

/** Protestos em cartório — IEPTB/CENPROT nacional. */
class ProtestosFonte extends FonteListaInfoSimples
{
    public function chave(): string
    {
        return 'protestos';
    }

    public function slug(): string
    {
        return 'ieptb/protestos';
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.protestos', 1.00);
    }

    protected function chavesLista(): array
    {
        return ['cartorios', 'protestos', 'registros'];
    }

    protected function camposResumo(): array
    {
        return ['cartorio', 'cidade', 'uf', 'quantidade_titulos', 'valor_protestado', 'telefone'];
    }

    protected function mapearSucesso(array $data): array
    {
        $dados = parent::mapearSucesso($data);

        // IEPTB agrega por cartório: o total de TÍTULOS protestados (se presente) é mais
        // significativo que o nº de cartórios.
        if (isset($data['quantidade_titulos']) && is_numeric($data['quantidade_titulos'])) {
            $dados['total_titulos'] = (int) $data['quantidade_titulos'];
            $dados['status'] = $dados['total_titulos'] === 0 ? 'Negativa' : 'Positiva';
            $dados['nada_consta'] = $dados['total_titulos'] === 0;
            $dados['mensagem'] = $dados['total_titulos'] === 0
                ? 'Nada consta para este CNPJ.'
                : "Constam {$dados['total_titulos']} título(s) protestado(s) para este CNPJ.";
        }

        return $dados;
    }
}
