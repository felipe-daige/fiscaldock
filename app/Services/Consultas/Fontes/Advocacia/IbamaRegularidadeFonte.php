<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;

/** IBAMA — Certificado de Regularidade do Cadastro Técnico Federal, por CPF ou CNPJ. */
class IbamaRegularidadeFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'ibama_regularidade';
    }

    public function slug(): string
    {
        return 'ibama/certificado-regularidade';
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
        return (float) config('consultas.fontes.ibama_regularidade', 1.00);
    }

    protected function mapearSucesso(array $data): array
    {
        $emitido = filled($data['registro'] ?? null) || filled($data['validade_data'] ?? null);

        return [
            'status' => $emitido ? 'Regular' : 'INDETERMINADO',
            'certidao_codigo' => $data['registro'] ?? null,
            'emissao_data' => $data['emissao_data'] ?? null,
            'data_validade' => $data['validade_data'] ?? null,
            'conseguiu_emitir' => $emitido,
            'interessado' => $data['razao_social'] ?? ($data['nome_fantasia'] ?? null),
            'categorias' => is_array($data['categorias'] ?? null) ? $data['categorias'] : [],
            'endereco' => array_filter([
                'logradouro' => $data['endereco_logradouro'] ?? null,
                'numero' => $data['endereco_numero'] ?? null,
                'complemento' => $data['endereco_complemento'] ?? null,
                'bairro' => $data['endereco_bairro'] ?? null,
                'municipio' => $data['endereco_municipio'] ?? null,
                'uf' => $data['endereco_uf'] ?? null,
                'cep' => $data['endereco_cep'] ?? null,
            ], fn ($valor) => $valor !== null && $valor !== ''),
            'mensagem' => $data['mensagem'] ?? null,
        ];
    }
}
