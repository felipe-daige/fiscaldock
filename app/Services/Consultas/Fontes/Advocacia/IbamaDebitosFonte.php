<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;

/** IBAMA/Sicafi — Certidão de Débitos, por CPF ou CNPJ + nome. */
class IbamaDebitosFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'ibama_debitos';
    }

    public function slug(): string
    {
        return 'ibama/certidao-debitos';
    }

    public function pronta(): bool
    {
        return parent::pronta() && $this->validadaParaPublico();
    }

    public function aceitaPessoa(): array
    {
        return ['PF', 'PJ'];
    }

    public function aplicavelPara(array $alvo): bool
    {
        return trim((string) ($alvo['nome'] ?? $alvo['razao_social'] ?? '')) !== '';
    }

    public function motivoIndisponivel(array $alvo): string
    {
        return 'A Certidão de Débitos do IBAMA exige o nome completo ou a razão social do alvo.';
    }

    public function params(array $alvo): array
    {
        return parent::params($alvo) + [
            'nome' => trim((string) ($alvo['nome'] ?? $alvo['razao_social'] ?? '')),
        ];
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.ibama_debitos', 1.00);
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
            'mensagem' => $data['mensagem'] ?? ($data['mensagem_consta'] ?? null),
        ];
    }
}
