<?php

namespace App\Support;

use App\Models\EfdNota;
use App\Models\Participante;

/**
 * Fonte única da origem exibível do participante.
 *
 * O n8n não preenche `origem_tipo` nos participantes extraídos do SPED. Por isso os vínculos
 * com as importações vencem o campo legado; `null` nunca significa cadastro manual.
 */
final class ParticipanteOrigem
{
    /**
     * @return array{label: string, hex: string, arquivo: ?string, url: ?string}
     */
    public static function dados(Participante $participante): array
    {
        $tipo = strtoupper(trim((string) $participante->origem_tipo));
        $arquivoRef = trim((string) data_get($participante->origem_ref, 'arquivo')) ?: null;

        if ($participante->importacao_efd_id || str_starts_with($tipo, 'SPED')) {
            $importacao = $participante->importacaoEfd;
            if ($importacao && (int) $importacao->user_id !== (int) $participante->user_id) {
                $importacao = null;
            }
            $tipoEfd = $importacao?->tipo_efd;

            $origem = match (true) {
                $tipoEfd === 'EFD PIS/COFINS', $tipo === 'SPED_EFD_CONTRIB' => [
                    'label' => 'EFD PIS/COFINS',
                    'hex' => '#7c3aed',
                ],
                $tipoEfd === 'EFD ICMS/IPI', $tipo === 'SPED_EFD_FISCAL' => [
                    'label' => 'EFD ICMS/IPI',
                    'hex' => '#4338ca',
                ],
                default => ['label' => 'EFD', 'hex' => '#4338ca'],
            };

            return $origem + [
                'arquivo' => trim((string) $importacao?->filename) ?: $arquivoRef,
                'url' => $importacao ? route('app.importacao.efd.detalhes', $importacao->id, false) : null,
            ];
        }

        if ($participante->importacao_xml_id) {
            $importacao = $participante->importacaoXml;
            if ($importacao && (int) $importacao->user_id !== (int) $participante->user_id) {
                $importacao = null;
            }

            return [
                'label' => self::labelXml($tipo ?: strtoupper((string) $importacao?->tipo_documento)),
                'hex' => self::hexXml($tipo),
                'arquivo' => trim((string) $importacao?->filename) ?: $arquivoRef,
                'url' => $importacao ? route('app.importacao.xml.detalhes', $importacao->id, false) : null,
            ];
        }

        // Acervos antigos podem ter a nota ligada ao participante sem copiar a importação para
        // participantes.importacao_efd_id. A nota ainda é evidência suficiente de origem EFD.
        if ($tipo === '') {
            $notaEfd = EfdNota::where('user_id', $participante->user_id)
                ->where('participante_id', $participante->id)
                ->with('importacao:id,tipo_efd,filename')
                ->orderByDesc('id')
                ->first(['id', 'importacao_id']);

            if ($notaEfd) {
                $tipoEfd = $notaEfd->importacao?->tipo_efd;

                return [
                    'label' => match ($tipoEfd) {
                        'EFD PIS/COFINS' => 'EFD PIS/COFINS',
                        'EFD ICMS/IPI' => 'EFD ICMS/IPI',
                        default => 'EFD (SPED importado)',
                    },
                    'hex' => $tipoEfd === 'EFD PIS/COFINS' ? '#7c3aed' : '#4338ca',
                    'arquivo' => trim((string) $notaEfd->importacao?->filename) ?: $arquivoRef,
                    'url' => $notaEfd->importacao
                        ? route('app.importacao.efd.detalhes', $notaEfd->importacao->id, false)
                        : null,
                ];
            }
        }

        $origem = match ($tipo) {
            'XML', 'NFE' => ['label' => 'XML NF-e', 'hex' => '#0f766e'],
            'NFSE' => ['label' => 'XML NFS-e', 'hex' => '#0891b2'],
            'CTE' => ['label' => 'XML CT-e', 'hex' => '#0369a1'],
            'PROPRIO' => ['label' => 'Empresa própria', 'hex' => '#374151'],
            'MANUAL' => ['label' => 'Manual', 'hex' => '#6b7280'],
            default => ['label' => 'Não informada', 'hex' => '#9ca3af'],
        };

        return $origem + ['arquivo' => $arquivoRef, 'url' => null];
    }

    private static function labelXml(string $tipo): string
    {
        return match ($tipo) {
            'NFSE' => 'XML NFS-e',
            'CTE' => 'XML CT-e',
            default => 'XML NF-e',
        };
    }

    private static function hexXml(string $tipo): string
    {
        return match ($tipo) {
            'NFSE' => '#0891b2',
            'CTE' => '#0369a1',
            default => '#0f766e',
        };
    }
}
