<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteListaInfoSimples;

/**
 * Lista de Devedores da PGFN — dívida ativa da União e FGTS.
 *
 * Contrato público: CPF ou CNPJ, sem parâmetro complementar. A fonte fica registrada mas
 * `pronta=false` até um payload real sanitizado validar shape, 612, custo e comprovantes.
 */
class PgfnDevedoresFonte extends FonteListaInfoSimples
{
    public function chave(): string
    {
        return 'pgfn_devedores';
    }

    public function slug(): string
    {
        return 'receita-federal/pgfn/devedores';
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
        return (float) config('consultas.fontes.pgfn_devedores', 1.00);
    }

    protected function chavesLista(): array
    {
        return ['naturezas_debitos'];
    }

    protected function camposResumo(): array
    {
        return ['descricao', 'total', 'debitos'];
    }

    protected function mapearSucesso(array $data): array
    {
        $naturezas = is_array($data['naturezas_debitos'] ?? null) ? $data['naturezas_debitos'] : [];
        $temDivida = $naturezas !== [] || $this->valorPositivo($data['total_divida'] ?? null);

        return [
            'status' => $temDivida ? 'Positiva' : 'Negativa',
            'nada_consta' => ! $temDivida,
            'total_registros' => count($naturezas),
            'registros' => array_map(
                fn ($item) => array_intersect_key((array) $item, array_flip($this->camposResumo())),
                array_slice($naturezas, 0, $this->maxRegistros()),
            ),
            'total_divida' => $data['total_divida'] ?? null,
            'total_tributario' => $data['total_tributario'] ?? null,
            'total_nao_tributario' => $data['total_nao_tributario'] ?? null,
            'nome' => $data['nome'] ?? null,
            'nome_fantasia' => $data['nome_fantasia'] ?? null,
            'municipio' => $data['municipio'] ?? null,
            'uf' => $data['uf'] ?? null,
            'mensagem' => $temDivida
                ? 'Constam débitos inscritos na Dívida Ativa.'
                : 'Nada consta na Lista de Devedores da PGFN.',
        ];
    }

    private function valorPositivo(mixed $valor): bool
    {
        if (is_int($valor) || is_float($valor)) {
            return $valor > 0;
        }

        $texto = preg_replace('/[^0-9,.-]/', '', (string) $valor);
        if ($texto === '' || $texto === null) {
            return false;
        }

        if (str_contains($texto, ',')) {
            $texto = str_replace('.', '', $texto);
            $texto = str_replace(',', '.', $texto);
        }

        return is_numeric($texto) && (float) $texto > 0;
    }
}
