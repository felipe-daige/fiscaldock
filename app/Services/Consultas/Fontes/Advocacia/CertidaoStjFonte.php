<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;

class CertidaoStjFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'certidao_stj';
    }

    public function slug(): string
    {
        return 'tribunal/stj/certidao-negativa';
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.certidao_stj', 1.00);
    }

    protected function mapearSucesso(array $data): array
    {
        return [
            'status' => $this->statusCertidao($data),
            'certidao_codigo' => $data['certidao_codigo'] ?? ($data['numero_certidao'] ?? null),
            'emissao_data' => $data['emissao_data'] ?? null,
            'data_validade' => $data['validade_data'] ?? null,
            'mensagem' => $data['mensagem'] ?? null,
        ];
    }
}
