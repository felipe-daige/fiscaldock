<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;

/** Certidão Negativa do Ministério Público Federal. */
class CertidaoMpfFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'certidao_mpf';
    }

    public function slug(): string
    {
        return 'mpf/certidao-negativa';
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.certidao_mpf', 1.00);
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
