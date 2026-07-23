<?php

namespace App\Services\Consultas;

use App\Models\ConsultaLote;
use App\Support\Cnpj;

class HistoricoConsultaPresenter
{
    /**
     * Monta o resumo visual de um lote sem disparar queries adicionais.
     *
     * @return array<string, mixed>
     */
    public function paraLote(ConsultaLote $lote): array
    {
        $statusNormalizado = ConsultaLote::normalizeStatus($lote->status);
        $status = match ($statusNormalizado) {
            ConsultaLote::STATUS_FINALIZADO => ['label' => 'Finalizado', 'hex' => '#047857'],
            ConsultaLote::STATUS_PROCESSANDO => ['label' => 'Processando', 'hex' => '#b45309'],
            ConsultaLote::STATUS_ERRO => ['label' => 'Erro', 'hex' => '#dc2626'],
            default => ['label' => 'Pendente', 'hex' => '#9ca3af'],
        };

        $alvosDosResultados = $lote->relationLoaded('resultados')
            ? $lote->resultados->map(function ($resultado) {
                $entidade = $resultado->participante ?: $resultado->cliente;
                $payload = is_array($resultado->resultado_dados) ? $resultado->resultado_dados : [];

                return [
                    'nome' => trim((string) (
                        $payload['razao_social']
                        ?? $entidade?->razao_social
                        ?? $entidade?->nome_fantasia
                        ?? ''
                    )),
                    'documento' => $entidade?->documento
                        ?? $payload['cnpj']
                        ?? $payload['documento']
                        ?? null,
                ];
            })->filter(fn (array $alvo) => $alvo['nome'] !== '' || ! empty($alvo['documento']))
            : collect();

        $alvosDoVinculo = $lote->relationLoaded('participantes')
            ? $lote->participantes->map(fn ($participante) => [
                'nome' => trim((string) ($participante->razao_social ?: $participante->nome_fantasia)),
                'documento' => $participante->documento,
            ])
            : collect();

        $alvos = ($alvosDosResultados->isNotEmpty() ? $alvosDosResultados : $alvosDoVinculo)
            ->unique(fn (array $alvo) => $alvo['documento'] ?: $alvo['nome'])
            ->values();

        $alvoPrincipal = $alvos->first();
        $nomePrincipal = trim((string) ($alvoPrincipal['nome'] ?? ''));
        if ($nomePrincipal === '') {
            $totalFormatado = number_format($lote->total_participantes, 0, ',', '.');
            $nomePrincipal = "Consulta de {$totalFormatado} CNPJ"
                .($lote->total_participantes === 1 ? '' : 's');
        }

        $resultadosTotal = (int) ($lote->resultados_count ?? 0);
        $resultadosSucesso = (int) ($lote->resultados_sucesso_count ?? 0);
        $resultadosFalha = (int) ($lote->resultados_falha_count ?? 0);
        $resultadosPendentes = max(0, $resultadosTotal - $resultadosSucesso - $resultadosFalha);

        if ($resultadosSucesso > 0) {
            $resultadoTitulo = number_format($resultadosSucesso, 0, ',', '.').' '
                .($resultadosSucesso === 1 ? 'disponível' : 'disponíveis');
            $resultadoHex = '#047857';
        } elseif ($statusNormalizado === ConsultaLote::STATUS_ERRO) {
            $resultadoTitulo = 'Não concluída';
            $resultadoHex = '#dc2626';
        } else {
            $resultadoTitulo = 'Aguardando resultados';
            $resultadoHex = '#b45309';
        }

        $resultadoDetalhe = match (true) {
            $resultadosFalha > 0 => number_format($resultadosFalha, 0, ',', '.').' com falha',
            $resultadosPendentes > 0 => number_format($resultadosPendentes, 0, ',', '.').' em processamento',
            $lote->processado_em !== null => 'Concluída às '.$lote->processado_em->format('H:i'),
            default => null,
        };

        return [
            'status' => $status,
            'produto' => [
                'nome' => $lote->plano?->nome ?? 'Consulta CNPJ',
                'hex' => match ($lote->plano?->codigo) {
                    'validacao' => '#1d4ed8',
                    'licitacao' => '#7c3aed',
                    'compliance' => '#0f766e',
                    'due_diligence' => '#b45309',
                    default => '#4b5563',
                },
                'origem' => ($lote->eh_monitoramento ?? false) ? 'Consulta automática' : 'Consulta manual',
            ],
            'alvo' => [
                'nome' => $nomePrincipal,
                'documento' => ! empty($alvoPrincipal['documento'])
                    ? Cnpj::formatar((string) $alvoPrincipal['documento'])
                    : null,
                'outros_total' => max(0, (int) $lote->total_participantes - 1),
                'outros_nomes' => $alvos->skip(1)
                    ->pluck('nome')
                    ->filter()
                    ->take(2)
                    ->implode(' · '),
            ],
            'resultado' => [
                'titulo' => $resultadoTitulo,
                'hex' => $resultadoHex,
                'detalhe' => $resultadoDetalhe,
                'sucessos' => $resultadosSucesso,
                'falhas' => $resultadosFalha,
            ],
            'data_label' => $lote->created_at->isToday()
                ? 'Hoje'
                : ($lote->created_at->isYesterday() ? 'Ontem' : $lote->created_at->format('d/m')),
        ];
    }
}
