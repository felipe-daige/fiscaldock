<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Participante;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AlertaCentralService
{
    public function __construct(
        private NotasFiscaisAlertService $notasFiscaisAlertService,
    ) {}

    /**
     * Recalcula todos os alertas para o usuário.
     */
    public function recalcular(int $userId, ?int $clienteId = null): array
    {
        $novos = 0;
        $atualizados = 0;
        $allHashes = [];

        // 1. Alertas de notas fiscais (7 detectores do NotasFiscaisAlertService)
        $resultado = $this->notasFiscaisAlertService->detectar($userId, []);

        foreach ($resultado['alertas'] as $alerta) {
            if (($alerta['tipo'] ?? '') === 'paid' || ($alerta['total_afetados'] ?? 0) <= 0) {
                continue;
            }

            $hash = hash('sha256', "$userId:{$alerta['id']}");
            $allHashes[] = $hash;

            $data = [
                'tipo' => $alerta['id'],
                'titulo' => $alerta['titulo'],
                'descricao' => $alerta['descricao'],
                'severidade' => $alerta['severidade'],
                'total_afetados' => $alerta['total_afetados'],
                'detalhes' => $alerta['detalhes'],
                'categoria' => 'notas_fiscais',
            ];

            $existing = Alerta::where('user_id', $userId)->where('hash', $hash)->first();

            if ($existing) {
                $updateData = $data;
                if (! in_array($existing->status, ['resolvido', 'ignorado'])) {
                    $updateData['status'] = 'ativo';
                }
                $existing->update($updateData);
                $atualizados++;
            } else {
                Alerta::create(array_merge($data, [
                    'user_id' => $userId,
                    'hash' => $hash,
                    'status' => 'ativo',
                ]));
                $novos++;
            }
        }

        // 2. Alertas de compliance (3 detectores)
        $complianceDetectors = [
            'situacao_irregular' => 'detectarSituacaoIrregular',
            'consulta_vencida' => 'detectarConsultaVencida',
            'nunca_consultado' => 'detectarNuncaConsultado',
        ];

        foreach ($complianceDetectors as $tipo => $method) {
            $participantes = $this->$method($userId);

            foreach ($participantes as $p) {
                $hash = hash('sha256', "$userId:$tipo:{$p->id}");
                $allHashes[] = $hash;

                $data = $this->buildComplianceAlertData($tipo, $p);

                $existing = Alerta::where('user_id', $userId)->where('hash', $hash)->first();

                if ($existing) {
                    $updateData = $data;
                    if (! in_array($existing->status, ['resolvido', 'ignorado'])) {
                        $updateData['status'] = 'ativo';
                    }
                    $existing->update($updateData);
                    $atualizados++;
                } else {
                    Alerta::create(array_merge($data, [
                        'user_id' => $userId,
                        'hash' => $hash,
                        'status' => 'ativo',
                    ]));
                    $novos++;
                }
            }
        }

        // 3. Auto-resolver alertas que não foram mais detectados
        $resolvidos = Alerta::where('user_id', $userId)
            ->where('status', 'ativo')
            ->whereNotIn('hash', $allHashes)
            ->update([
                'status' => 'resolvido',
                'resolvido_em' => now(),
            ]);

        return [
            'novos' => $novos,
            'atualizados' => $atualizados,
            'resolvidos' => $resolvidos,
        ];
    }

    /**
     * Obtém alertas paginados com filtros.
     */
    public function obterAlertas(int $userId, array $filtros): LengthAwarePaginator
    {
        $query = Alerta::doUsuario($userId);

        // Filtro de status (default: ativo)
        $status = $filtros['status'] ?? 'ativo';
        $query->where('status', $status);

        if (! empty($filtros['severidade'])) {
            $query->where('severidade', $filtros['severidade']);
        }

        if (! empty($filtros['categoria'])) {
            $query->where('categoria', $filtros['categoria']);
        }

        if (! empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }

        $query->with([
            'participante:id,razao_social,cnpj',
            'cliente:id,razao_social',
        ]);

        $query->orderByDesc('prioridade')
            ->orderByRaw("CASE severidade WHEN 'alta' THEN 3 WHEN 'media' THEN 2 WHEN 'baixa' THEN 1 ELSE 0 END DESC")
            ->orderByDesc('created_at');

        return $query->paginate(50);
    }

    /**
     * Retorna resumo dos alertas do usuário.
     */
    public function obterResumo(int $userId): array
    {
        $base = Alerta::doUsuario($userId)->ativos();

        $porSeveridade = (clone $base)
            ->selectRaw('severidade, COUNT(*) as total')
            ->groupBy('severidade')
            ->pluck('total', 'severidade')
            ->toArray();

        $porCategoria = (clone $base)
            ->selectRaw('categoria, COUNT(*) as total')
            ->groupBy('categoria')
            ->pluck('total', 'categoria')
            ->toArray();

        $totalAtivos = array_sum($porSeveridade);

        $novosHoje = Alerta::doUsuario($userId)
            ->ativos()
            ->whereDate('created_at', today())
            ->count();

        $ultimaAtualizacao = Alerta::doUsuario($userId)
            ->max('updated_at');

        return [
            'total_ativos' => $totalAtivos,
            'por_severidade' => [
                'alta' => $porSeveridade['alta'] ?? 0,
                'media' => $porSeveridade['media'] ?? 0,
                'baixa' => $porSeveridade['baixa'] ?? 0,
            ],
            'por_categoria' => [
                'notas_fiscais' => $porCategoria['notas_fiscais'] ?? 0,
                'compliance' => $porCategoria['compliance'] ?? 0,
            ],
            'novos_hoje' => $novosHoje,
            'ultima_atualizacao' => $ultimaAtualizacao,
        ];
    }

    /**
     * Marca o status de um alerta.
     */
    public function marcarStatus(int $alertaId, int $userId, string $status, ?string $notas = null): Alerta
    {
        $alerta = Alerta::where('id', $alertaId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $alerta->status = $status;

        if ($status === 'visto' && $alerta->visto_em === null) {
            $alerta->visto_em = now();
        }

        if ($status === 'resolvido') {
            $alerta->resolvido_em = now();
        }

        if ($notas !== null) {
            $alerta->notas = $notas;
        }

        $alerta->save();

        return $alerta;
    }

    /**
     * Retorna dados de evolução semanal para gráfico ApexCharts.
     */
    public function obterEvolucao(int $userId): array
    {
        $inicioSemanas = now()->subWeeks(11)->startOfWeek();

        $dados = Alerta::doUsuario($userId)
            ->where('created_at', '>=', $inicioSemanas)
            ->selectRaw("DATE_TRUNC('week', created_at) as semana, severidade, COUNT(*) as total")
            ->groupBy('semana', 'severidade')
            ->orderBy('semana')
            ->get();

        // Gerar todas as 12 semanas
        $semanas = [];
        $cursor = $inicioSemanas->copy();
        for ($i = 0; $i < 12; $i++) {
            $semanas[] = $cursor->copy();
            $cursor->addWeek();
        }

        $categorias = [];
        $seriesData = [
            'alta' => [],
            'media' => [],
            'baixa' => [],
        ];

        foreach ($semanas as $index => $semana) {
            $categorias[] = 'Sem '.($index + 1);
            $semanaStr = $semana->format('Y-m-d');

            foreach (['alta', 'media', 'baixa'] as $severidade) {
                $count = $dados
                    ->where('severidade', $severidade)
                    ->first(function ($item) use ($semanaStr) {
                        return Carbon::parse($item->semana)->format('Y-m-d') === $semanaStr;
                    });

                $seriesData[$severidade][] = $count ? (int) $count->total : 0;
            }
        }

        return [
            'categorias' => $categorias,
            'series' => [
                ['name' => 'Alta', 'data' => $seriesData['alta'], 'color' => '#EF4444'],
                ['name' => 'Media', 'data' => $seriesData['media'], 'color' => '#F59E0B'],
                ['name' => 'Baixa', 'data' => $seriesData['baixa'], 'color' => '#6B7280'],
            ],
        ];
    }

    // -------------------------------------------------------
    // Compliance detectors (private)
    // -------------------------------------------------------

    /**
     * Participantes com situação cadastral diferente de ATIVA.
     */
    private function detectarSituacaoIrregular(int $userId): Collection
    {
        return Participante::where('user_id', $userId)
            ->whereNotNull('situacao_cadastral')
            ->where('situacao_cadastral', '!=', '')
            ->where('situacao_cadastral', '!=', 'ATIVA')
            ->whereHas('efdNotas')
            ->get(['id', 'razao_social', 'cnpj', 'situacao_cadastral', 'cliente_id']);
    }

    /**
     * Participantes com última consulta há mais de 90 dias.
     */
    private function detectarConsultaVencida(int $userId): Collection
    {
        return Participante::where('user_id', $userId)
            ->whereNotNull('ultima_consulta_em')
            ->where('ultima_consulta_em', '<', now()->subDays(90))
            ->whereHas('efdNotas')
            ->get(['id', 'razao_social', 'cnpj', 'ultima_consulta_em', 'cliente_id']);
    }

    /**
     * Participantes que nunca foram consultados.
     */
    private function detectarNuncaConsultado(int $userId): Collection
    {
        return Participante::where('user_id', $userId)
            ->whereNull('ultima_consulta_em')
            ->whereHas('efdNotas')
            ->excludingEmpresaPropria()
            ->get(['id', 'razao_social', 'cnpj', 'cliente_id']);
    }

    /**
     * Monta os dados do alerta de compliance a partir do tipo e participante.
     */
    private function buildComplianceAlertData(string $tipo, Participante $p): array
    {
        $base = [
            'categoria' => 'compliance',
            'cliente_id' => $p->cliente_id,
            'participante_id' => $p->id,
        ];

        return match ($tipo) {
            'situacao_irregular' => array_merge($base, [
                'tipo' => 'situacao_irregular',
                'severidade' => 'alta',
                'titulo' => "Participante com situacao cadastral {$p->situacao_cadastral}",
                'descricao' => "{$p->razao_social} ({$p->cnpj_formatado}) esta com situacao cadastral {$p->situacao_cadastral} na Receita Federal.",
                'total_afetados' => 1,
                'detalhes' => [
                    'participante_id' => $p->id,
                    'razao_social' => $p->razao_social,
                    'cnpj' => $p->cnpj,
                    'situacao_cadastral' => $p->situacao_cadastral,
                ],
            ]),
            'consulta_vencida' => array_merge($base, [
                'tipo' => 'consulta_vencida',
                'severidade' => 'media',
                'titulo' => "Consulta vencida — {$p->razao_social}",
                'descricao' => "Ultima consulta realizada ha mais de 90 dias ({$p->ultima_consulta_em->format('d/m/Y')}). Recomendamos atualizar os dados cadastrais.",
                'total_afetados' => 1,
                'detalhes' => [
                    'participante_id' => $p->id,
                    'razao_social' => $p->razao_social,
                    'cnpj' => $p->cnpj,
                    'ultima_consulta_em' => $p->ultima_consulta_em->toIso8601String(),
                ],
            ]),
            'nunca_consultado' => array_merge($base, [
                'tipo' => 'nunca_consultado',
                'severidade' => 'baixa',
                'titulo' => "Participante nunca consultado — {$p->razao_social}",
                'descricao' => "{$p->razao_social} ({$p->cnpj}) possui notas fiscais mas nunca teve seus dados cadastrais verificados.",
                'total_afetados' => 1,
                'detalhes' => [
                    'participante_id' => $p->id,
                    'razao_social' => $p->razao_social,
                    'cnpj' => $p->cnpj,
                ],
            ]),
        };
    }
}
