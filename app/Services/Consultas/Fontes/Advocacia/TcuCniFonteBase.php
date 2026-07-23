<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;

/**
 * Contrato comum do TCU CNI.
 *
 * A origem cobra uma chamada por `tipo_relacao`, portanto Inidôneo e Inabilitado são fontes
 * comerciais distintas mesmo compartilhando slug e normalização.
 */
abstract class TcuCniFonteBase extends FonteCertidaoInfoSimples
{
    abstract protected function tipoRelacao(): int;

    public function slug(): string
    {
        return 'tcu/cni';
    }

    public function pronta(): bool
    {
        return parent::pronta() && $this->validadaParaPublico();
    }

    public function params(array $alvo): array
    {
        return parent::params($alvo) + ['tipo_relacao' => $this->tipoRelacao()];
    }

    protected function mapearSucesso(array $data): array
    {
        $processos = is_array($data['processos'] ?? null) ? $data['processos'] : [];

        return [
            'status' => $this->statusCertidao($data),
            'certidao_codigo' => $data['codigo_controle'] ?? null,
            'emissao_data' => $data['data_emissao'] ?? null,
            'data_validade' => $data['data_validade'] ?? null,
            'conseguiu_emitir' => (bool) ($data['conseguiu_emitir_certidao_negativa'] ?? false),
            'cpf_cnpj' => $data['normalizado_cpf_cnpj'] ?? $data['cpf_cnpj'] ?? null,
            'nome' => $data['nome'] ?? null,
            'titulo' => $data['titulo'] ?? null,
            'processos' => array_map(
                fn ($processo) => array_intersect_key((array) $processo, array_flip([
                    'processo', 'acordao', 'entrada_cadastro', 'saida_cadastro',
                ])),
                array_slice($processos, 0, 20),
            ),
            'total_processos' => count($processos),
            'mensagem' => $data['mensagem'] ?? null,
        ];
    }
}
