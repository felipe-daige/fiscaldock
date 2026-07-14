<?php

namespace App\Services\Consultas;

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\CteConsulta;
use App\Models\NfeConsulta;
use App\Models\Participante;
use App\Support\Cnpj;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Read-side das últimas consultas de um CNPJ nos perfis de Cliente/Participante.
 *
 * Une a Consulta CNPJ (lote + resultado) aos snapshots de Clearance NF-e/CT-e sem
 * duplicar persistência. Como os snapshots são UPSERT por chave, cada documento
 * representa somente sua verificação mais recente.
 */
class PerfilConsultaHistoricoService
{
    private const ITENS_POR_PAGINA = 5;

    private const NOME_PAGINA = 'consultas_page';

    public function paraCliente(
        Cliente $cliente,
        int $porPagina = self::ITENS_POR_PAGINA,
        ?int $pagina = null,
    ): LengthAwarePaginator {
        return $this->montar(
            (int) $cliente->user_id,
            Cnpj::digitos((string) $cliente->documento),
            (int) $cliente->id,
            $porPagina,
            $pagina,
        );
    }

    public function paraParticipante(
        Participante $participante,
        int $porPagina = self::ITENS_POR_PAGINA,
        ?int $pagina = null,
    ): LengthAwarePaginator {
        return $this->montar(
            (int) $participante->user_id,
            Cnpj::digitos((string) $participante->documento),
            null,
            $porPagina,
            $pagina,
        );
    }

    private function montar(
        int $userId,
        string $documento,
        ?int $clienteId,
        int $porPagina,
        ?int $pagina,
    ): LengthAwarePaginator {
        $porPagina = max(1, min($porPagina, 20));
        $pagina = max(1, $pagina ?? LengthAwarePaginator::resolveCurrentPage(self::NOME_PAGINA));
        $quantidadeNecessaria = $pagina * $porPagina;

        [$historicoCnpj, $totalCnpj] = $this->consultasCnpj($userId, $documento, $quantidadeNecessaria);
        [$historicoDfe, $totalDfe] = $this->consultasDfe($userId, $documento, $clienteId, $quantidadeNecessaria);
        $total = $totalCnpj + $totalDfe;
        $ultimaPagina = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $ultimaPagina);

        $itens = $historicoCnpj
            ->concat($historicoDfe)
            ->sortByDesc(fn (array $item) => $item['consultado_em']?->getTimestamp() ?? 0)
            ->slice(($pagina - 1) * $porPagina, $porPagina)
            ->values();

        return (new LengthAwarePaginator(
            $itens,
            $total,
            $porPagina,
            $pagina,
            [
                'path' => request()->url(),
                'pageName' => self::NOME_PAGINA,
            ],
        ))->withQueryString()->fragment('historico-consultas-perfil');
    }

    /** @return array{0: Collection, 1: int} */
    private function consultasCnpj(int $userId, string $documento, int $limite): array
    {
        if (strlen($documento) !== 14) {
            return [collect(), 0];
        }

        $participanteIds = Participante::query()
            ->where('user_id', $userId)
            ->where('documento', $documento)
            ->pluck('id');
        $clienteIds = Cliente::query()
            ->where('user_id', $userId)
            ->where('documento', $documento)
            ->pluck('id');

        $query = ConsultaLote::query()
            ->where('user_id', $userId)
            ->where(function ($query) use ($participanteIds, $clienteIds) {
                $query->whereHas('resultados', function ($resultados) use ($participanteIds, $clienteIds) {
                    $resultados->where(function ($alvos) use ($participanteIds, $clienteIds) {
                        if ($participanteIds->isNotEmpty()) {
                            $alvos->whereIn('participante_id', $participanteIds);
                        }
                        if ($clienteIds->isNotEmpty()) {
                            $metodo = $participanteIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                            $alvos->{$metodo}('cliente_id', $clienteIds);
                        }
                    });
                });

                // Preserva consultas do participante que ainda não produziram a primeira fonte.
                if ($participanteIds->isNotEmpty()) {
                    $query->orWhereHas('participantes', fn ($participantes) => $participantes->whereIn('participantes.id', $participanteIds));
                }
            });

        $total = (clone $query)->count();
        $lotes = $query
            ->with([
                'plano:id,nome,codigo',
                'resultados' => function ($resultados) use ($participanteIds, $clienteIds) {
                    $resultados->where(function ($alvos) use ($participanteIds, $clienteIds) {
                        if ($participanteIds->isNotEmpty()) {
                            $alvos->whereIn('participante_id', $participanteIds);
                        }
                        if ($clienteIds->isNotEmpty()) {
                            $metodo = $participanteIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                            $alvos->{$metodo}('cliente_id', $clienteIds);
                        }
                    })->orderByDesc('consultado_em');
                },
            ])
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get();

        $itens = $lotes->map(function (ConsultaLote $lote) {
            /** @var ConsultaResultado|null $resultado */
            $resultado = $lote->resultados->first();
            $statusLote = ConsultaLote::normalizeStatus($lote->status);
            $status = in_array($statusLote, [ConsultaLote::STATUS_PROCESSANDO, ConsultaLote::STATUS_ERRO], true)
                ? $statusLote
                : ($resultado?->status ?? $statusLote);
            $statusMeta = $this->statusCnpj($status);
            $plano = $lote->plano?->nome;

            return [
                'tipo' => 'cnpj',
                'origem_label' => 'Consulta CNPJ',
                'origem_hex' => '#4338ca',
                'titulo' => $plano ?: 'Regularidade cadastral e fiscal',
                'descricao' => 'Lote #'.$lote->id,
                'identificador' => null,
                'status_label' => $statusMeta['label'],
                'status_hex' => $statusMeta['hex'],
                'consultado_em' => $resultado?->consultado_em ?? $lote->processado_em ?? $lote->created_at,
                'url' => '/app/consulta/lote/'.$lote->id,
            ];
        });

        return [$itens, $total];
    }

    /** @return array{0: Collection, 1: int} */
    private function consultasDfe(int $userId, string $documento, ?int $clienteId, int $limite): array
    {
        // Participante PF não pode cair numa query sem predicado e receber o histórico DF-e
        // inteiro do usuário. Cliente ainda pode ser resolvido pelo cliente_id explícito.
        if ($clienteId === null && strlen($documento) !== 14) {
            return [collect(), 0];
        }

        $queryNfe = NfeConsulta::query()
            ->where('user_id', $userId)
            ->where(function ($query) use ($documento, $clienteId) {
                if ($clienteId !== null) {
                    $query->where('cliente_id', $clienteId);
                }
                if (strlen($documento) === 14) {
                    $metodo = $clienteId !== null ? 'orWhereIn' : 'whereIn';
                    $query->{$metodo}('emit_cnpj', [$documento])
                        ->orWhereIn('dest_cnpj', [$documento]);
                }
            });
        $totalNfe = (clone $queryNfe)->count();
        $nfe = $queryNfe
            ->orderByDesc('consultado_em')
            ->limit($limite)
            ->get();

        $queryCte = CteConsulta::query()
            ->where('user_id', $userId)
            ->where(function ($query) use ($documento, $clienteId) {
                if ($clienteId !== null) {
                    $query->where('cliente_id', $clienteId);
                }
                if (strlen($documento) === 14) {
                    $metodo = $clienteId !== null ? 'orWhereIn' : 'whereIn';
                    $query->{$metodo}('emit_cnpj', [$documento]);
                    foreach (['tomador_cnpj', 'remet_cnpj', 'dest_cnpj', 'expedidor_cnpj', 'recebedor_cnpj'] as $coluna) {
                        $query->orWhereIn($coluna, [$documento]);
                    }
                }
            });
        $totalCte = (clone $queryCte)->count();
        $cte = $queryCte
            ->orderByDesc('consultado_em')
            ->limit($limite)
            ->get();

        $snapshots = $nfe
            ->map(fn (NfeConsulta $consulta) => ['modelo' => $consulta, 'tipo' => 'nfe'])
            ->concat($cte->map(fn (CteConsulta $consulta) => ['modelo' => $consulta, 'tipo' => 'cte']));

        $lotes = ConsultaLote::query()
            ->where('user_id', $userId)
            ->whereIn('id', $snapshots->pluck('modelo.consulta_lote_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $itens = $snapshots->map(function (array $item) use ($documento, $clienteId, $lotes) {
            $consulta = $item['modelo'];
            $tipo = $item['tipo'];
            $lote = $consulta->consulta_lote_id ? $lotes->get($consulta->consulta_lote_id) : null;
            $fluxo = $lote?->resultado_resumo['fluxo_origem'] ?? ($lote ? 'lote' : 'avulsa');
            $rotuloDocumento = $tipo === 'cte' ? 'CT-e' : 'NF-e';
            $vinculos = $this->vinculosDfe($consulta, $tipo, $documento, $clienteId);
            $statusMeta = $this->statusDfe((string) $consulta->status);
            $numero = trim((string) ($consulta->numero ?? ''));

            return [
                'tipo' => $tipo,
                'origem_label' => $fluxo === 'avulsa' ? 'Busca avulsa '.$rotuloDocumento : 'Clearance '.$rotuloDocumento,
                'origem_hex' => $tipo === 'cte' ? '#0369a1' : '#0f766e',
                'titulo' => $numero !== '' ? $rotuloDocumento.' nº '.$numero : $rotuloDocumento.' consultada',
                'descricao' => implode(' · ', array_filter([
                    $lote ? 'Lote #'.$lote->id : null,
                    $vinculos !== [] ? implode(', ', $vinculos) : null,
                ])),
                'identificador' => $consulta->chave_acesso,
                'status_label' => $statusMeta['label'],
                'status_hex' => $statusMeta['hex'],
                'consultado_em' => $consulta->consultado_em ?? $consulta->created_at,
                'url' => $lote
                    ? ($fluxo === 'avulsa'
                        ? '/app/clearance/buscar/resultado/'.$lote->id
                        : '/app/clearance/notas/resultado/'.$lote->id)
                    : '/app/clearance/buscar/historico',
            ];
        });

        return [$itens, $totalNfe + $totalCte];
    }

    private function vinculosDfe(object $consulta, string $tipo, string $documento, ?int $clienteId): array
    {
        $campos = $tipo === 'cte'
            ? [
                'emit_cnpj' => 'Emitente',
                'tomador_cnpj' => 'Tomador',
                'remet_cnpj' => 'Remetente',
                'dest_cnpj' => 'Destinatário',
                'expedidor_cnpj' => 'Expedidor',
                'recebedor_cnpj' => 'Recebedor',
            ]
            : [
                'emit_cnpj' => 'Emitente',
                'dest_cnpj' => 'Destinatário',
            ];

        $vinculos = [];
        foreach ($campos as $campo => $label) {
            if ($documento !== '' && Cnpj::digitos((string) ($consulta->{$campo} ?? '')) === $documento) {
                $vinculos[] = $label;
            }
        }

        if ($vinculos === [] && $clienteId !== null && (int) $consulta->cliente_id === $clienteId) {
            $vinculos[] = 'Cliente vinculado';
        }

        return array_values(array_unique($vinculos));
    }

    private function statusCnpj(string $status): array
    {
        return match ($status) {
            ConsultaResultado::STATUS_SUCESSO, ConsultaLote::STATUS_FINALIZADO, ConsultaLote::STATUS_CONCLUIDO => ['label' => 'Concluída', 'hex' => '#047857'],
            ConsultaResultado::STATUS_ERRO, ConsultaResultado::STATUS_TIMEOUT => ['label' => $status === ConsultaResultado::STATUS_TIMEOUT ? 'Timeout' : 'Erro', 'hex' => '#dc2626'],
            ConsultaLote::STATUS_PROCESSANDO => ['label' => 'Em andamento', 'hex' => '#4338ca'],
            default => ['label' => 'Pendente', 'hex' => '#6b7280'],
        };
    }

    private function statusDfe(string $status): array
    {
        $status = strtoupper(trim($status));

        return match ($status) {
            'AUTORIZADA' => ['label' => 'Autorizada', 'hex' => '#047857'],
            'CANCELADA', 'DENEGADA', 'INUTILIZADA', 'ERRO_PARAMETRO', 'ERRO_INTEGRACAO' => [
                'label' => str_replace('_', ' ', ucfirst(strtolower($status))),
                'hex' => '#dc2626',
            ],
            'TIMEOUT' => ['label' => 'Timeout', 'hex' => '#b45309'],
            'NAO_ENCONTRADA' => ['label' => 'Não encontrada', 'hex' => '#6b7280'],
            'INDETERMINADO' => ['label' => 'Indeterminado', 'hex' => '#b45309'],
            default => ['label' => $status !== '' ? str_replace('_', ' ', ucfirst(strtolower($status))) : 'Pendente', 'hex' => '#6b7280'],
        };
    }
}
