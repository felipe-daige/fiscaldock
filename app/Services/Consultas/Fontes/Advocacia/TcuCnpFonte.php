<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;

/** TCU — Certidão Negativa de Processo, por CPF ou CNPJ. */
class TcuCnpFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'tcu_cnp';
    }

    public function slug(): string
    {
        return 'tcu/cnp';
    }

    public function pronta(): bool
    {
        return parent::pronta() && $this->validadaParaPublico();
    }

    public function aceitaPessoa(): array
    {
        return ['PF', 'PJ'];
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.tcu_cnp', 1.00);
    }

    protected function mapearSucesso(array $data): array
    {
        return [
            'status' => $this->statusCertidao($data),
            'certidao_codigo' => $data['codigo_controle'] ?? null,
            'emissao_data' => $data['data_emissao'] ?? null,
            'data_validade' => $data['data_validade'] ?? null,
            'conseguiu_emitir' => (bool) ($data['conseguiu_emitir_certidao_negativa'] ?? false),
            'requerente' => $data['requerente'] ?? null,
            'mensagem' => $data['mensagem'] ?? null,
        ];
    }
}
