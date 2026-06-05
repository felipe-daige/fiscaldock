<?php

namespace App\Services\Consultas\Fontes;

class CndEstadualFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'cnd_estadual';
    }

    public function slug(): string
    {
        return 'sefaz/certidao-debitos';
    }

    public function custoCreditos(): int
    {
        return (int) config('consultas.fontes.cnd_estadual', 2);
    }

    public function params(array $alvo): array
    {
        // SEFAZ exige a UF do domicílio do participante.
        return parent::params($alvo) + ['uf' => strtoupper((string) ($alvo['uf'] ?? ''))];
    }

    protected function mapearSucesso(array $data): array
    {
        return [
            'uf' => $data['uf'] ?? null,
            'status' => $data['tipo'] ?? null, // Negativa / Positiva com efeitos / Positiva
            'certidao_codigo' => $data['certidao_codigo'] ?? null,
            'emissao_data' => $data['emissao_data'] ?? null,
            'data_validade' => $data['validade_data'] ?? ($data['validade'] ?? null),
            'conseguiu_emitir' => (bool) ($data['conseguiu_emitir_certidao_negativa'] ?? false),
            'mensagem' => $data['mensagem'] ?? null,
        ];
    }
}
