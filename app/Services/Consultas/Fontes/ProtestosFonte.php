<?php

namespace App\Services\Consultas\Fontes;

class ProtestosFonte extends FonteInfoSimplesBase
{
    public function chave(): string
    {
        return 'protestos';
    }

    public function slug(): string
    {
        return 'ieptb/protestos';
    }

    public function custoCreditos(): int
    {
        return (int) config('consultas.fontes.protestos', 2);
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        // Sucesso com data vazio = SEM protesto. data preenchido = possui protesto.
        if ($status === 'sucesso') {
            $protestos = $raw['data'] ?? [];

            return $this->bloco([
                'possui_protesto' => count($protestos) > 0,
                'total_protestos' => count($protestos),
                'protestos' => $protestos,
                'consulta_datahora' => $raw['data'][0]['consulta_datahora'] ?? null,
            ]);
        }

        if ($status === 'nao_encontrado') {
            return $this->bloco(['possui_protesto' => false, 'total_protestos' => 0, 'protestos' => []]);
        }

        if ($status === 'indeterminado') {
            return $this->bloco(['status' => 'INDETERMINADO', 'mensagem' => $this->mensagem($raw)]);
        }

        return [];
    }
}
