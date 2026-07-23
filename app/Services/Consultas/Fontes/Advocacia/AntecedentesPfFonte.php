<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;
use App\Support\Cpf;

/**
 * Dado pessoal sensível. Código fica pronto, mas a fonte só aparece/roda mediante flag explícita
 * após definição de base legal, finalidade e retenção.
 */
class AntecedentesPfFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'antecedentes_pf';
    }

    public function slug(): string
    {
        return 'antecedentes-criminais/pf/emit';
    }

    public function aceitaPessoa(): array
    {
        return ['PF'];
    }

    public function pronta(): bool
    {
        return parent::pronta() && (bool) config('advocacia.fontes_sensiveis.antecedentes_pf', false);
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.antecedentes_pf', 1.00);
    }

    public function params(array $alvo): array
    {
        return [
            'cpf' => Cpf::digitos($alvo['cpf'] ?? $alvo['documento'] ?? ''),
            'birthdate' => trim((string) ($alvo['birthdate'] ?? '')),
            'nome' => trim((string) ($alvo['nome'] ?? $alvo['razao_social'] ?? '')),
            'nome_mae' => trim((string) ($alvo['nome_mae'] ?? '')),
            'nome_pai' => trim((string) ($alvo['nome_pai'] ?? '')),
            'uf_nascimento' => strtoupper(trim((string) ($alvo['uf_nascimento'] ?? ''))),
        ];
    }

    public function aplicavelPara(array $alvo): bool
    {
        $params = $this->params($alvo);

        return Cpf::valido($params['cpf'])
            && ! in_array('', [
                $params['birthdate'],
                $params['nome'],
                $params['nome_mae'],
                $params['nome_pai'],
                $params['uf_nascimento'],
            ], true);
    }

    public function motivoIndisponivel(array $alvo): string
    {
        return 'Antecedentes PF exigem CPF, nome, nascimento, filiação e UF de nascimento.';
    }

    protected function mapearSucesso(array $data): array
    {
        return [
            'status' => ! empty($data['conseguiu_emitir_certidao_negativa']) ? 'Negativa' : 'INDETERMINADO',
            'conseguiu_emitir' => (bool) ($data['conseguiu_emitir_certidao_negativa'] ?? false),
            'certidao_codigo' => $data['certidao_codigo'] ?? $data['numero'] ?? null,
            'emissao_data' => $data['emissao_data'] ?? $data['emissao_datahora'] ?? null,
            'data_validade' => $data['validade_data'] ?? null,
            'mensagem' => $data['mensagem'] ?? null,
        ];
    }
}
