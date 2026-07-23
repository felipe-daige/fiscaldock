<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;

/** IBAMA — Certidão de Nada Consta de Embargos, por CPF ou CNPJ. */
class IbamaEmbargosFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'ibama_embargos';
    }

    public function slug(): string
    {
        return 'ibama/certidao-embargos';
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
        return (float) config('consultas.fontes.ibama_embargos', 1.00);
    }

    protected function mapearSucesso(array $data): array
    {
        return [
            'status' => $this->statusCertidao($data),
            'certidao_codigo' => $data['numero'] ?? null,
            'emissao_data' => $data['emissao_data'] ?? null,
            'data_validade' => $data['validade_data'] ?? null,
            'conseguiu_emitir' => (bool) ($data['conseguiu_emitir_certidao_negativa'] ?? false),
            'interessado' => $data['interessado'] ?? null,
            'endereco' => $data['endereco'] ?? null,
            'mensagem' => $data['mensagem'] ?? null,
        ];
    }
}
