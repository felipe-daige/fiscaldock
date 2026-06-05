<?php

namespace App\Services\Consultas\Fontes;

class ProcessosFonte extends FonteInfoSimplesBase
{
    public function chave(): string
    {
        return 'processos';
    }

    public function slug(): string
    {
        return 'tribunal/trt/processo'; // Consulta Processual Trabalhista Unificada
    }

    public function custoCreditos(): int
    {
        return (int) config('consultas.fontes.processos', 2);
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        // Sucesso com data vazio = SEM processo. data preenchido = possui processo.
        if ($status === 'sucesso') {
            $processos = $raw['data'] ?? [];

            return $this->bloco([
                'possui_processo' => count($processos) > 0,
                'total_processos' => count($processos),
                'processos' => $processos,
                'consulta_datahora' => $raw['data'][0]['consulta_datahora'] ?? null,
            ]);
        }

        if ($status === 'nao_encontrado') {
            return $this->bloco(['possui_processo' => false, 'total_processos' => 0, 'processos' => []]);
        }

        if ($status === 'indeterminado') {
            return $this->bloco(['status' => 'INDETERMINADO', 'mensagem' => $this->mensagem($raw)]);
        }

        return [];
    }
}
