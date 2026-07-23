<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteListaInfoSimples;

/** IBAMA — primeira página de autuações ambientais de um CPF/CNPJ em um ano. */
class IbamaAutuacoesFonte extends FonteListaInfoSimples
{
    public function chave(): string
    {
        return 'ibama_autuacoes';
    }

    public function slug(): string
    {
        return 'ibama/autuacoes';
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
        $ano = (int) ($alvo['ano'] ?? 0);

        return $ano >= 1900 && $ano <= (int) date('Y');
    }

    public function motivoIndisponivel(array $alvo): string
    {
        return 'A consulta de Autuações Ambientais exige um ano válido.';
    }

    public function params(array $alvo): array
    {
        return parent::params($alvo) + ['ano' => (int) ($alvo['ano'] ?? 0)];
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.ibama_autuacoes', 1.00);
    }

    protected function chavesLista(): array
    {
        return ['infracoes'];
    }

    protected function camposResumo(): array
    {
        return [
            'numero', 'tipo', 'data', 'bioma', 'estado', 'municipio', 'cpf_cnpj',
            'nome_autuado', 'numero_auto', 'serie_auto', 'valor_multa', 'numero_processo',
            'status_debito', 'sancoes',
        ];
    }

    protected function mapearSucesso(array $data): array
    {
        $infracoes = is_array($data['infracoes'] ?? null) ? $data['infracoes'] : [];
        $total = is_numeric($data['total_infracoes'] ?? null)
            ? (int) $data['total_infracoes']
            : count($infracoes);

        return [
            'status' => $total === 0 ? 'Negativa' : 'Positiva',
            'nada_consta' => $total === 0,
            'total_registros' => $total,
            'registros' => array_map(
                fn ($item) => array_intersect_key((array) $item, array_flip($this->camposResumo())),
                array_slice($infracoes, 0, $this->maxRegistros()),
            ),
            'valor_infracoes' => $data['valor_infracoes'] ?? null,
            'normalizado_valor_infracoes' => $data['normalizado_valor_infracoes'] ?? null,
            'pagina' => 1,
            'somente_primeira_pagina' => true,
            'mensagem' => $total === 0
                ? 'Nada consta para este documento no ano consultado.'
                : "Constam {$total} autuação(ões) no ano consultado; a fonte retorna a primeira página.",
        ];
    }
}
