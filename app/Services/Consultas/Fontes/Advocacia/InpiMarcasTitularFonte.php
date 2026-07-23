<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteListaInfoSimples;

/** INPI — primeira página de marcas vinculadas ao titular CPF/CNPJ. */
class InpiMarcasTitularFonte extends FonteListaInfoSimples
{
    public function chave(): string
    {
        return 'inpi_marcas_titular';
    }

    public function slug(): string
    {
        return 'inpi/marcas-titular';
    }

    public function pronta(): bool
    {
        return parent::pronta() && $this->validadaParaPublico();
    }

    public function aceitaPessoa(): array
    {
        return ['PF', 'PJ'];
    }

    public function params(array $alvo): array
    {
        return parent::params($alvo) + ['pagina' => 1];
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.inpi_marcas_titular', 1.00);
    }

    protected function chavesLista(): array
    {
        return ['processos'];
    }

    protected function camposResumo(): array
    {
        return ['numero', 'prioridade', 'tipo', 'marca', 'registro', 'situacao', 'titular', 'classe'];
    }

    protected function mapearSucesso(array $data): array
    {
        $processos = is_array($data['processos'] ?? null) ? $data['processos'] : [];
        $total = is_numeric($data['processos_total'] ?? null)
            ? (int) $data['processos_total']
            : count($processos);
        $totalPaginas = is_numeric($data['total_paginas'] ?? null) ? (int) $data['total_paginas'] : 1;
        $paginaAtual = is_numeric($data['pagina_atual'] ?? null) ? (int) $data['pagina_atual'] : 1;

        return [
            'status' => $total === 0 ? 'Negativa' : 'Positiva',
            'nada_consta' => $total === 0,
            'total_registros' => $total,
            'registros' => array_map(
                fn ($item) => array_intersect_key((array) $item, array_flip($this->camposResumo())),
                array_slice($processos, 0, $this->maxRegistros()),
            ),
            'pagina_atual' => $paginaAtual,
            'total_paginas' => $totalPaginas,
            'tem_mais_paginas' => $paginaAtual < $totalPaginas,
            'mensagem' => $total === 0
                ? 'Nenhuma marca encontrada para este titular.'
                : "Foram encontrados {$total} processo(s) de marca; exibindo a página {$paginaAtual} de {$totalPaginas}.",
        ];
    }
}
