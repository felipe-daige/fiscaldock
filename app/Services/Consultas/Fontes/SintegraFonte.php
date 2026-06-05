<?php

namespace App\Services\Consultas\Fontes;

class SintegraFonte extends FonteInfoSimplesBase
{
    public function chave(): string
    {
        return 'sintegra';
    }

    public function slug(): string
    {
        return 'sintegra/unificada';
    }

    public function custoCreditos(): int
    {
        return (int) config('consultas.fontes.sintegra', 1);
    }

    public function params(array $alvo): array
    {
        // uf opcional: sem uf, o InfoSimples consulta o SINTEGRA do domicílio fiscal.
        $params = parent::params($alvo);
        if (! empty($alvo['uf'])) {
            $params['uf'] = strtoupper((string) $alvo['uf']);
        }

        return $params;
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        if ($status === 'sucesso') {
            $d = $raw['data'][0] ?? [];

            return $this->bloco([
                'uf' => $d['uf'] ?? null,
                'inscricao_estadual' => $d['inscricao_estadual'] ?? null,
                'situacao' => $d['situacao'] ?? ($d['situacao_cadastral'] ?? null),
                'data_situacao' => $d['data_situacao'] ?? null,
                'regime_apuracao' => $d['regime_apuracao'] ?? null,
                'atividade_economica' => $d['atividade_economica'] ?? null,
                'consulta_datahora' => $d['consulta_datahora'] ?? null,
            ]);
        }

        if ($status === 'indeterminado') {
            return $this->bloco(['situacao' => 'INDETERMINADO', 'mensagem' => $this->mensagem($raw)]);
        }

        if ($status === 'nao_encontrado') {
            return $this->bloco(['situacao' => 'NAO_ENCONTRADA', 'mensagem' => $this->mensagem($raw)]);
        }

        return [];
    }
}
