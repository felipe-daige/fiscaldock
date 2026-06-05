<?php

namespace App\Services\Consultas\Persistencia;

use App\Models\ConsultaResultado;
use App\Services\Consultas\Dto\ResultadoFonte;

class PersistenciaCnpj
{
    public function gravar(int $loteId, int $participanteId, ResultadoFonte $resultado): void
    {
        $linha = ConsultaResultado::firstOrNew([
            'consulta_lote_id' => $loteId,
            'participante_id' => $participanteId,
        ]);

        $dados = $linha->resultado_dados ?? [];

        // merge: campos da fonte sobrescrevem; consultas_realizadas acumula sem duplicar
        $realizadas = array_values(array_unique(array_merge(
            $dados['consultas_realizadas'] ?? [],
            $resultado->dados['consultas_realizadas'] ?? [],
        )));

        $dados = array_merge($dados, $resultado->dados);
        if ($realizadas) {
            $dados['consultas_realizadas'] = $realizadas;
        }

        $linha->resultado_dados = $dados;
        $linha->status = $resultado->status === 'sucesso' ? 'sucesso' : ($linha->status ?: 'erro');
        if ($resultado->status !== 'sucesso' && $resultado->mensagem) {
            $linha->error_message = $resultado->mensagem;
        }
        $linha->consultado_em = now();
        $linha->save();
    }
}
