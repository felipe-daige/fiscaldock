<?php

namespace App\Services\Consultas\Fontes;

/**
 * Base para fontes de LISTA de apontamentos (protestos, falências, improbidade, CEIS/CNEP...):
 * a resposta não é uma certidão, mas uma relação de registros. Normaliza para o MESMO shape
 * textual das certidões — status 'Negativa' (nada consta) / 'Positiva' (constam N registros) —
 * para reusar CertidaoBadge/presenter sem UI nova. Guarda o total e um resumo enxuto dos
 * registros (nunca o raw inteiro).
 */
abstract class FonteListaInfoSimples extends FonteCertidaoInfoSimples
{
    /** Chaves candidatas do array de registros dentro do data[0] (varia por endpoint). */
    abstract protected function chavesLista(): array;

    /** Campos de cada registro a preservar no resumo (subset enxuto). */
    protected function camposResumo(): array
    {
        return [];
    }

    /** Teto de registros resumidos persistidos por fonte. */
    protected function maxRegistros(): int
    {
        return 20;
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        // 612 ("não retornou dados na origem") em fonte de LISTA = nenhum registro encontrado =
        // nada consta = Negativa/regular. Difere da certidão, onde 612 é neutro (documento não
        // localizado): aqui a ausência É a resposta. Visto no smoke: CEIS/CNEP devolvem 612
        // para CNPJ sem sanção.
        if ($status === 'nao_encontrado') {
            return $this->bloco([
                'status' => 'Negativa',
                'nada_consta' => true,
                'total_registros' => 0,
                'registros' => [],
                'mensagem' => 'Nada consta para este CNPJ.',
            ]);
        }

        return parent::normalizar($raw, $status);
    }

    protected function mapearSucesso(array $data): array
    {
        $registros = [];
        foreach ($this->chavesLista() as $chave) {
            if (is_array($data[$chave] ?? null)) {
                $registros = $data[$chave];
                break;
            }
        }

        $total = isset($data['total']) && is_numeric($data['total'])
            ? (int) $data['total']
            : count($registros);

        $campos = $this->camposResumo();
        $resumo = array_map(function ($r) use ($campos) {
            if (! is_array($r)) {
                return ['descricao' => (string) $r];
            }

            return $campos === [] ? $r : array_intersect_key($r, array_flip($campos));
        }, array_slice($registros, 0, $this->maxRegistros()));

        return [
            // 'Negativa'/'Positiva': mesmo vocabulário das certidões → CertidaoBadge classifica
            // sem código novo. Nada consta = Negativa (regular).
            'status' => $total === 0 ? 'Negativa' : 'Positiva',
            'nada_consta' => $total === 0,
            'total_registros' => $total,
            'registros' => $resumo,
            'mensagem' => $total === 0
                ? 'Nada consta para este CNPJ.'
                : "Constam {$total} registro(s) para este CNPJ.",
        ];
    }
}
