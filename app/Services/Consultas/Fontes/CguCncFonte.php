<?php

namespace App\Services\Consultas\Fontes;

class CguCncFonte extends FonteInfoSimplesBase
{
    public function chave(): string
    {
        return 'cgu_cnc';
    }

    public function slug(): string
    {
        return 'cgu/cnc-tipo1';
    }

    public function custoCreditos(): int
    {
        return (int) config('consultas.fontes.cgu_cnc', 2);
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        // Sucesso com data vazio = SEM sanção (não confundir com 612). data preenchido = sancionado.
        if ($status === 'sucesso') {
            $sancoes = $raw['data'] ?? [];

            return $this->bloco([
                'possui_sancao' => count($sancoes) > 0,
                'total_sancoes' => count($sancoes),
                'sancoes' => $sancoes,
                'consulta_datahora' => $raw['data'][0]['consulta_datahora'] ?? null,
            ]);
        }

        if ($status === 'nao_encontrado') {
            return $this->bloco(['possui_sancao' => false, 'total_sancoes' => 0, 'sancoes' => []]);
        }

        if ($status === 'indeterminado') {
            return $this->bloco(['status' => 'INDETERMINADO', 'mensagem' => $this->mensagem($raw)]);
        }

        return [];
    }
}
