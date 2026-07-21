<?php

namespace App\Services\Perfis;

use App\Models\Cliente;
use App\Models\ConsultaResultado;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use App\Support\Cnpj;

class PerfilCnpjSnapshotService
{
    /**
     * Resolve a projeção consolidada de um CNPJ independentemente de a consulta ter sido
     * gravada no cliente ou em um participante espelho do mesmo documento.
     *
     * @return array{ultima_consulta: ?ConsultaResultado, score: ?ParticipanteScore, dados: array<string, mixed>}
     */
    public function resolver(int $userId, string $documento): array
    {
        $cnpj = Cnpj::digitos($documento);

        if (strlen($cnpj) !== 14) {
            return ['ultima_consulta' => null, 'score' => null, 'dados' => []];
        }

        $clienteIds = Cliente::query()
            ->where('user_id', $userId)
            ->where('documento', $cnpj)
            ->pluck('id');
        $participanteIds = Participante::query()
            ->where('user_id', $userId)
            ->where('documento', $cnpj)
            ->pluck('id');

        if ($clienteIds->isEmpty() && $participanteIds->isEmpty()) {
            return ['ultima_consulta' => null, 'score' => null, 'dados' => []];
        }

        $scores = ParticipanteScore::query()
            ->where('user_id', $userId)
            ->where(function ($query) use ($clienteIds, $participanteIds) {
                if ($clienteIds->isNotEmpty()) {
                    $query->whereIn('cliente_id', $clienteIds);
                }
                if ($participanteIds->isNotEmpty()) {
                    $metodo = $clienteIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                    $query->{$metodo}('participante_id', $participanteIds);
                }
            })
            ->orderBy('ultima_consulta_em')
            ->get();

        $ultimaConsulta = ConsultaResultado::query()
            ->where('status', ConsultaResultado::STATUS_SUCESSO)
            ->whereHas('lote', fn ($query) => $query->where('user_id', $userId))
            ->where(function ($query) use ($clienteIds, $participanteIds) {
                if ($clienteIds->isNotEmpty()) {
                    $query->whereIn('cliente_id', $clienteIds);
                }
                if ($participanteIds->isNotEmpty()) {
                    $metodo = $clienteIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                    $query->{$metodo}('participante_id', $participanteIds);
                }
            })
            ->with(['lote:id,plano_id,created_at,user_id', 'lote.plano:id,nome,codigo,consultas_incluidas'])
            ->latest('consultado_em')
            ->first();

        $dados = [];
        $consultasRealizadas = [];

        foreach ($scores as $score) {
            $snapshot = is_array($score->dados_consultados) ? $score->dados_consultados : [];
            $dados = array_merge($dados, $snapshot);
            $consultasRealizadas = array_merge(
                $consultasRealizadas,
                (array) ($snapshot['consultas_realizadas'] ?? [])
            );
        }

        if ($ultimaConsulta) {
            $atuais = is_array($ultimaConsulta->resultado_dados) ? $ultimaConsulta->resultado_dados : [];
            $dados = array_merge($dados, $atuais);
            $consultasRealizadas = array_merge(
                $consultasRealizadas,
                (array) ($atuais['consultas_realizadas'] ?? [])
            );
        }

        $fontesConhecidas = [
            'cnd_federal',
            'cnd_estadual',
            'cnd_municipal',
            'crf_fgts',
            'cndt',
            'sintegra',
            'qsa',
        ];
        $consultasRealizadas = array_values(array_unique(array_merge(
            $consultasRealizadas,
            array_values(array_intersect($fontesConhecidas, array_keys($dados)))
        )));

        if ($consultasRealizadas !== []) {
            $dados['consultas_realizadas'] = $consultasRealizadas;
        }

        if (! $ultimaConsulta && $dados !== []) {
            $ultimaConsulta = new ConsultaResultado([
                'status' => ConsultaResultado::STATUS_SUCESSO,
                'consultado_em' => $scores->last()?->ultima_consulta_em,
            ]);
        }

        if ($ultimaConsulta) {
            // Mutação somente em memória: os resultados brutos continuam imutáveis no histórico.
            $ultimaConsulta->resultado_dados = $dados;
        }

        return [
            'ultima_consulta' => $ultimaConsulta,
            'score' => $scores->last(),
            'dados' => $dados,
        ];
    }
}
