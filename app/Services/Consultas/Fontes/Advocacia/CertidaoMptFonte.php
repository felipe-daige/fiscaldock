<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;

/**
 * Certidão Negativa de Feitos do Ministério Público do Trabalho (unificada nacional).
 * `uf` é OBRIGATÓRIA no endpoint (smoke lote 260: sem ela a chamada dá 607) — vem do cadastro,
 * que roda antes e injeta a UF autoritativa no alvo. Interior de SP seria o valor especial
 * `CAMPINAS` (PRT15) — mesma limitação MVP do CEAT, documentada na spec.
 */
class CertidaoMptFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'certidao_mpt';
    }

    public function slug(): string
    {
        return 'mpt/cnf/unificada';
    }

    /** MPT aceita CPF e CNPJ; PF liberada via advocacia.fontes_pf_liberadas (smoke 2026-07-23). */
    public function aceitaPessoa(): array
    {
        return $this->tiposPessoaComPfValidado();
    }

    public function aplicavelPara(array $alvo): bool
    {
        return trim((string) ($alvo['uf'] ?? '')) !== '';
    }

    public function motivoIndisponivel(array $alvo): string
    {
        return 'A certidão do MPT exige a UF da sede — UF indisponível no cadastro deste CNPJ.';
    }

    public function params(array $alvo): array
    {
        return parent::params($alvo) + ['uf' => strtoupper(trim((string) ($alvo['uf'] ?? '')))];
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.certidao_mpt', 1.00);
    }

    protected function mapearSucesso(array $data): array
    {
        // Contrato real (doc detalhada): sem `tipo`/`conseguiu_emitir` — o veredito é
        // `nada_consta`; feitos vêm em procedimentos[] (classe NF/PAJ/IC + situacao).
        $procedimentos = is_array($data['procedimentos'] ?? null) ? $data['procedimentos'] : [];
        $nadaConsta = $data['nada_consta'] ?? null;

        return [
            'status' => $this->statusCertidao($data)
                ?? ($nadaConsta === null ? null : ($nadaConsta ? 'Negativa' : 'Positiva')),
            'certidao_codigo' => $data['codigo'] ?? null,
            'emissao_data' => $data['emissao_data'] ?? null,
            'data_validade' => $data['validade_data'] ?? null,
            'nada_consta' => $nadaConsta,
            'titulo' => $data['titulo'] ?? null,
            'total_procedimentos' => count($procedimentos),
            'procedimentos' => array_map(fn ($p) => array_intersect_key((array) $p, array_flip([
                'ano_autuacao', 'classe', 'numero', 'situacao',
            ])), array_slice($procedimentos, 0, 20)),
            'mensagem' => $data['mensagem'] ?? ($data['mensagem_sistema'] ?? null),
        ];
    }
}
