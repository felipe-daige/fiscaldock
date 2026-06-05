<?php

namespace App\Services\Consultas\Fontes;

class CnjImprobidadeFonte extends FonteInfoSimplesBase
{
    public function chave(): string
    {
        return 'cnj_improbidade';
    }

    public function slug(): string
    {
        return 'cnj/improbidade';
    }

    public function custoCreditos(): int
    {
        return (int) config('consultas.fontes.cnj_improbidade', 2);
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        // Sucesso com data vazio = SEM condenação. data preenchido = possui condenação.
        if ($status === 'sucesso') {
            $condenacoes = $raw['data'] ?? [];

            return $this->bloco([
                'possui_condenacao' => count($condenacoes) > 0,
                'total_condenacoes' => count($condenacoes),
                'condenacoes' => $condenacoes,
                'consulta_datahora' => $raw['data'][0]['consulta_datahora'] ?? null,
            ]);
        }

        if ($status === 'nao_encontrado') {
            return $this->bloco(['possui_condenacao' => false, 'total_condenacoes' => 0, 'condenacoes' => []]);
        }

        if ($status === 'indeterminado') {
            return $this->bloco(['status' => 'INDETERMINADO', 'mensagem' => $this->mensagem($raw)]);
        }

        return [];
    }
}
