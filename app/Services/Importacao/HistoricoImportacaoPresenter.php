<?php

namespace App\Services\Importacao;

use App\Support\Cnpj;
use App\Support\Dinheiro;
use Carbon\Carbon;

class HistoricoImportacaoPresenter
{
    /**
     * @param  array<string, mixed>  $importacao
     * @return array<string, mixed>
     */
    public function paraImportacao(array $importacao): array
    {
        $tipo = (string) ($importacao['_tipo'] ?? '');
        $id = (int) ($importacao['id'] ?? 0);
        $arquivo = (string) ($importacao['filename'] ?? $importacao['arquivo'] ?? "Importação #{$id}");
        $clienteId = $importacao['cliente']['id'] ?? null;
        $clientesResolvidos = (int) ($importacao['clientes_resolvidos'] ?? 0);
        $clienteNome = trim((string) ($importacao['cliente']['razao_social'] ?? ''));

        if ($clienteNome === '' && $clientesResolvidos > 1) {
            $clienteNome = "Vários ({$clientesResolvidos} clientes)";
        }

        $data = ! empty($importacao['created_at'])
            ? Carbon::parse($importacao['created_at'])
            : null;

        $competencia = null;
        if (! empty($importacao['periodo_inicio'])) {
            $periodo = Carbon::parse($importacao['periodo_inicio']);
            $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            $competencia = $meses[$periodo->month - 1].'/'.$periodo->year;
        }

        $status = match ($importacao['status'] ?? '') {
            'concluido' => ['label' => 'Concluído', 'hex' => '#047857'],
            'processando' => ['label' => 'Processando', 'hex' => '#b45309'],
            'erro' => ['label' => 'Erro', 'hex' => '#dc2626'],
            default => ['label' => 'Pendente', 'hex' => '#9ca3af'],
        };

        if ($tipo === 'efd') {
            $pisCofins = ($importacao['tipo_efd'] ?? '') === 'EFD PIS/COFINS';
            $notas = max(
                (int) ($importacao['notas_extraidas'] ?? 0),
                (int) ($importacao['total_notas'] ?? 0)
            );
            $participantes = (int) ($importacao['total_participantes'] ?? 0);
            if ($participantes === 0 && is_array($importacao['participante_ids'] ?? null)) {
                $participantes = count($importacao['participante_ids']);
            }
            if ($participantes === 0) {
                $participantes = (int) ($importacao['novos'] ?? 0) + (int) ($importacao['duplicados'] ?? 0);
            }

            $conteudo = [
                'badge' => ['label' => 'EFD', 'hex' => $pisCofins ? '#0f766e' : '#4338ca'],
                'detalhe' => $pisCofins ? 'PIS/COFINS' : 'ICMS/IPI',
            ];
            $resultadoTitulo = $notas > 0
                ? number_format($notas, 0, ',', '.').' nota'.($notas === 1 ? '' : 's')
                : number_format($participantes, 0, ',', '.').' participante'.($participantes === 1 ? '' : 's');
            $resultadoDetalhes = $notas > 0
                ? [number_format($participantes, 0, ',', '.').' participante'.($participantes === 1 ? '' : 's')]
                : [];
            $href = "/app/importacao/efd/{$id}";
        } else {
            $tipoDocumento = strtolower((string) ($importacao['tipo_documento'] ?? ''));
            $conteudo = match ($tipoDocumento) {
                'nfe' => ['badge' => ['label' => 'NF-e', 'hex' => '#0f766e'], 'detalhe' => 'Documentos XML'],
                'nfse' => ['badge' => ['label' => 'NFS-e', 'hex' => '#374151'], 'detalhe' => 'Documentos XML'],
                'cte' => ['badge' => ['label' => 'CT-e', 'hex' => '#b45309'], 'detalhe' => 'Documentos XML'],
                default => ['badge' => ['label' => 'XML', 'hex' => '#374151'], 'detalhe' => 'Lote de documentos'],
            };
            $totalXmls = max(
                (int) ($importacao['xmls_processados'] ?? 0),
                (int) ($importacao['total_xmls'] ?? 0)
            );
            $resultadoTitulo = number_format($totalXmls, 0, ',', '.').' XML'.($totalXmls === 1 ? '' : 's');

            $resultadoDetalhes = collect([
                (int) ($importacao['xmls_novos'] ?? 0) > 0
                    ? number_format((int) $importacao['xmls_novos'], 0, ',', '.').' novo'.((int) $importacao['xmls_novos'] === 1 ? '' : 's')
                    : null,
                (int) ($importacao['xmls_duplicados_processados'] ?? 0) > 0
                    ? number_format((int) $importacao['xmls_duplicados_processados'], 0, ',', '.').' duplicado'.((int) $importacao['xmls_duplicados_processados'] === 1 ? '' : 's')
                    : null,
                (int) ($importacao['xmls_com_erro'] ?? 0) > 0
                    ? number_format((int) $importacao['xmls_com_erro'], 0, ',', '.').' com erro'
                    : null,
            ])->filter()->values()->all();
            $href = "/app/importacao/xml/{$id}";
        }

        $cnpjArquivo = ! empty($importacao['cnpj'])
            ? Cnpj::formatar((string) $importacao['cnpj'])
            : null;
        $titulo = $clienteNome !== ''
            ? $clienteNome
            : ($cnpjArquivo ?: $arquivo);

        return [
            'tipo' => $tipo,
            'id' => $id,
            'href' => $href,
            'arquivo' => $arquivo,
            'titulo' => $titulo,
            'cliente_id' => $clienteId,
            'cliente_nome' => $clienteNome,
            'cnpj' => $cnpjArquivo,
            'competencia' => $competencia,
            'conteudo' => $conteudo,
            'resultado' => [
                'titulo' => $resultadoTitulo,
                'detalhes' => $resultadoDetalhes,
                'valor' => $tipo === 'xml' && (float) ($importacao['valor_total'] ?? 0) > 0
                    ? Dinheiro::brl((float) $importacao['valor_total']).' em documentos'
                    : null,
            ],
            'status' => $status,
            'processando' => in_array($importacao['status'] ?? '', ['processando', 'pendente'], true),
            'data' => $data,
            'data_label' => $data?->isToday()
                ? 'Hoje'
                : ($data?->isYesterday() ? 'Ontem' : $data?->format('d/m')),
        ];
    }
}
