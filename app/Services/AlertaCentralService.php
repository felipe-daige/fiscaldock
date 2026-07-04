<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AlertaCentralService
{
    public function __construct(
        private NotasFiscaisAlertService $notasFiscaisAlertService,
        private GuiaAlertaService $guiaAlertaService,
    ) {}

    /**
     * Classes de alerta da Central (espelha `tabTipos` em central.blade.php).
     * Cada classe agrupa os `tipo`s exibidos numa aba. Fonte única usada pela
     * exportação em PDF (modal de seleção + relatório).
     *
     * @var array<string, array{label: string, cor: string, tipos: string[]}>
     */
    public const CLASSES = [
        'notas_fiscais' => [
            'label' => 'Notas Fiscais',
            'cor' => '#374151',
            'tipos' => ['notas_duplicadas', 'notas_sem_participante', 'notas_valor_zerado', 'notas_sem_itens', 'notas_data_futura'],
        ],
        'pis_cofins' => [
            'label' => 'PIS/COFINS',
            'cor' => '#6d28d9',
            'tipos' => ['pis_cofins_incompleto'],
        ],
        'compliance' => [
            'label' => 'Compliance',
            'cor' => '#4338ca',
            'tipos' => ['situacao_irregular', 'certidao_positiva', 'consulta_vencida', 'nunca_consultado', 'cnpj_situacao_irregular', 'participante_inativo', 'participante_sem_ie'],
        ],
        'fornecedores' => [
            'label' => 'Fornecedores',
            'cor' => '#b45309',
            'tipos' => ['fornecedor_irregular'],
        ],
        'importacao' => [
            'label' => 'Importação',
            'cor' => '#0f766e',
            'tipos' => ['gap_importacao', 'gap_temporal'],
        ],
    ];

    private const CLASSE_OUTROS = ['label' => 'Outros', 'cor' => '#6b7280'];

    /**
     * Alertas ativos do usuário agrupados por classe (na ordem de `CLASSES`),
     * opcionalmente restritos a um conjunto de IDs. Só inclui classes com ≥1 alerta.
     * Reusado pelo modal de exportação e pela geração do PDF.
     *
     * @param  int[]|null  $ids
     * @return array<int, array{key: string, label: string, cor: string, alertas: Collection<int, Alerta>}>
     */
    public function alertasAtivosAgrupados(int $userId, ?array $ids = null): array
    {
        $query = Alerta::doUsuario($userId)->ativos()
            ->with(['participante:id,razao_social,documento', 'cliente:id,razao_social']);

        if ($ids !== null) {
            $query->whereIn('id', $ids);
        }

        $alertas = $query
            ->orderByRaw("CASE severidade WHEN 'alta' THEN 3 WHEN 'media' THEN 2 WHEN 'baixa' THEN 1 ELSE 0 END DESC")
            ->orderByDesc('created_at')
            ->get();

        // Índice tipo → classe (com fallback "outros").
        $tipoParaClasse = [];
        foreach (self::CLASSES as $key => $meta) {
            foreach ($meta['tipos'] as $tipo) {
                $tipoParaClasse[$tipo] = $key;
            }
        }

        // tipo mapeado vence; senão cai na `categoria` do alerta (se for uma classe conhecida); senão "outros".
        $porClasse = $alertas->groupBy(function (Alerta $a) use ($tipoParaClasse) {
            if (isset($tipoParaClasse[$a->tipo])) {
                return $tipoParaClasse[$a->tipo];
            }

            return isset(self::CLASSES[$a->categoria]) ? $a->categoria : 'outros';
        });

        $grupos = [];
        foreach (self::CLASSES as $key => $meta) {
            if ($porClasse->has($key)) {
                $grupos[] = [
                    'key' => $key,
                    'label' => $meta['label'],
                    'cor' => $meta['cor'],
                    'alertas' => $porClasse->get($key)->values(),
                ];
            }
        }

        if ($porClasse->has('outros')) {
            $grupos[] = [
                'key' => 'outros',
                'label' => self::CLASSE_OUTROS['label'],
                'cor' => self::CLASSE_OUTROS['cor'],
                'alertas' => $porClasse->get('outros')->values(),
            ];
        }

        return $grupos;
    }

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

        // 2. Alertas de compliance por participante (acionáveis, 1 por CNPJ).
        // `nunca_consultado` NÃO entra aqui — é agregado num único alerta abaixo
        // (senão um import de empresa própria gera dezenas de alertas de ruído).
        $complianceDetectors = [
            'situacao_irregular' => 'detectarSituacaoIrregular',
            'consulta_vencida' => 'detectarConsultaVencida',
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

        // 2b. Nunca consultado — alerta AGREGADO (1 por usuário, não 1 por CNPJ).
        $nuncaConsultados = $this->detectarNuncaConsultado($userId);
        if ($nuncaConsultados->isNotEmpty()) {
            $hash = hash('sha256', "$userId:nunca_consultado");
            $allHashes[] = $hash;

            $total = $nuncaConsultados->count();
            $data = [
                'tipo' => 'nunca_consultado',
                'categoria' => 'compliance',
                'severidade' => 'baixa',
                'titulo' => "{$total} participante(s) nunca consultado(s)",
                'descricao' => "{$total} participante(s) com notas fiscais nunca tiveram os dados cadastrais verificados na Receita Federal. Consulte-os para checar regularidade.",
                'total_afetados' => $total,
                // Lista (renderizada como tabela no detalhe); capada por defesa de escala.
                'detalhes' => $nuncaConsultados->take(100)->map(fn ($p) => [
                    'razao_social' => $p->razao_social,
                    'documento' => $p->documento,
                ])->values()->all(),
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

        // 3. Alertas de risco BI (fornecedores irregulares com notas, gap de importações)
        $fornecedoresIrregulares = $this->detectarFornecedoresIrregularesComNotas($userId);
        foreach ($fornecedoresIrregulares as $f) {
            $hash = hash('sha256', "$userId:fornecedor_irregular:{$f->participante_id}");
            $allHashes[] = $hash;

            $valorFormatado = number_format((float) $f->valor_em_risco, 2, ',', '.');

            $data = [
                'tipo' => 'fornecedor_irregular',
                'categoria' => 'compliance',
                'severidade' => 'alta',
                'participante_id' => $f->participante_id,
                'cliente_id' => $f->cliente_id,
                'titulo' => "Fornecedor irregular com {$f->total_notas} nota(s) — R$ {$valorFormatado} em risco",
                'descricao' => "{$f->razao_social} ({$f->documento}) esta com situacao {$f->situacao_cadastral} e possui {$f->total_notas} nota(s) fiscal(is) vinculadas totalizando R$ {$valorFormatado}.",
                'total_afetados' => (int) $f->total_notas,
                'detalhes' => [
                    'participante_id' => $f->participante_id,
                    'razao_social' => $f->razao_social,
                    'documento' => $f->documento,
                    'situacao_cadastral' => $f->situacao_cadastral,
                    'total_notas' => (int) $f->total_notas,
                    'valor_em_risco' => (float) $f->valor_em_risco,
                ],
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

        // 3b. Certidões positivas (fornecedores E clientes) — 1 alerta por CNPJ, agrupando
        // as certidões irregulares. Fonte: participante_scores (cobre participante e cliente,
        // já mescla consultas parciais). Complementa `fornecedor_irregular` (que só olha
        // situação cadastral) — aqui o gatilho é a certidão positiva.
        $certidoesPositivas = $this->detectarCertidoesPositivas($userId);
        foreach ($certidoesPositivas as $alvo) {
            $hash = hash('sha256', "$userId:certidao_positiva:{$alvo['tipo_alvo']}:{$alvo['alvo_id']}");
            $allHashes[] = $hash;

            $data = $this->buildCertidaoPositivaAlertData($alvo);

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

        $gapImportacoes = $this->detectarGapImportacoes($userId);
        if ($gapImportacoes) {
            $hash = hash('sha256', "$userId:gap_importacao");
            $allHashes[] = $hash;

            $totalMeses = count($gapImportacoes);
            $data = [
                'tipo' => 'gap_importacao',
                'categoria' => 'importacao',
                'severidade' => 'media',
                'titulo' => "{$totalMeses} mês(es) sem importação EFD nos últimos 12 meses",
                'descricao' => "Foram detectados {$totalMeses} meses sem nenhuma importação EFD (Fiscal ou Contribuições). Meses faltantes podem indicar obrigações acessórias não entregues.",
                'total_afetados' => $totalMeses,
                'detalhes' => [
                    'meses_faltantes' => $gapImportacoes,
                    'total_meses' => $totalMeses,
                ],
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

        // 4. Auto-resolver alertas que não foram mais detectados
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
            'participante:id,razao_social,documento',
            'cliente:id,razao_social',
        ]);

        $query->orderByDesc('prioridade')
            ->orderByRaw("CASE severidade WHEN 'alta' THEN 3 WHEN 'media' THEN 2 WHEN 'baixa' THEN 1 ELSE 0 END DESC")
            ->orderByDesc('created_at');

        // Anexa o resumo do guia (cta) pra lista renderizar a ação inline.
        return $query->paginate(50)->through(function (Alerta $alerta) {
            $alerta->setAttribute('guia', $this->guiaAlertaService->resumo($alerta));

            return $alerta;
        });
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
                'importacao' => $porCategoria['importacao'] ?? 0,
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
     * Marca o status de vários alertas de uma vez. Escopado ao user_id (nunca
     * confiar nos ids do frontend). Retorna a quantidade efetivamente alterada.
     *
     * @param  array<int, int|string>  $alertaIds
     */
    public function marcarStatusEmLote(array $alertaIds, int $userId, string $status, ?string $notas = null): int
    {
        $alertas = Alerta::whereIn('id', $alertaIds)
            ->where('user_id', $userId)
            ->get();

        foreach ($alertas as $alerta) {
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
        }

        return $alertas->count();
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
        // Critério canônico de irregularidade: NOT IN ('02','ATIVA') — o código '02' da Receita
        // É "ATIVA". Usar `!= 'ATIVA'` flagrava '02' como irregular (falso) e divergia dos demais
        // detectores (detectarFornecedoresIrregularesComNotas / BiService).
        return Participante::where('user_id', $userId)
            ->whereNotNull('situacao_cadastral')
            ->whereRaw("UPPER(situacao_cadastral) NOT IN ('', '02', 'ATIVA')")
            ->whereHas('efdNotas')
            ->get(['id', 'razao_social', 'documento as documento', 'situacao_cadastral', 'cliente_id']);
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
            ->get(['id', 'razao_social', 'documento as documento', 'ultima_consulta_em', 'cliente_id']);
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
            ->get(['id', 'razao_social', 'documento as documento', 'cliente_id']);
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
                'descricao' => "{$p->razao_social} ({$p->documento_formatado}) esta com situacao cadastral {$p->situacao_cadastral} na Receita Federal.",
                'total_afetados' => 1,
                'detalhes' => [
                    'participante_id' => $p->id,
                    'razao_social' => $p->razao_social,
                    'documento' => $p->documento,
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
                    'documento' => $p->documento,
                    'ultima_consulta_em' => $p->ultima_consulta_em->toIso8601String(),
                ],
            ]),
            'nunca_consultado' => array_merge($base, [
                'tipo' => 'nunca_consultado',
                'severidade' => 'baixa',
                'titulo' => "Participante nunca consultado — {$p->razao_social}",
                'descricao' => "{$p->razao_social} ({$p->documento}) possui notas fiscais mas nunca teve seus dados cadastrais verificados.",
                'total_afetados' => 1,
                'detalhes' => [
                    'participante_id' => $p->id,
                    'razao_social' => $p->razao_social,
                    'documento' => $p->documento,
                ],
            ]),
        };
    }

    // -------------------------------------------------------
    // BI risk detectors (private)
    // -------------------------------------------------------

    /**
     * Fornecedores com situação irregular que possuem notas EFD vinculadas.
     */
    private function detectarFornecedoresIrregularesComNotas(int $userId): Collection
    {
        return DB::table('efd_notas as n')
            ->join('participantes as p', 'p.id', '=', 'n.participante_id')
            ->where('n.user_id', $userId)
            ->where('n.cancelada', false) // P4: nota cancelada não é valor em risco
            // P1: a MESMA NF-e está nas 2 origens — sem dedup o "valor em risco" e a contagem dobram.
            ->whereRaw("(n.origem_arquivo = 'fiscal' OR NOT EXISTS (SELECT 1 FROM efd_notas f WHERE f.user_id = n.user_id AND f.origem_arquivo = 'fiscal' AND f.chave_acesso IS NOT NULL AND f.chave_acesso = n.chave_acesso))")
            ->whereNotNull('p.situacao_cadastral')
            ->whereRaw("UPPER(p.situacao_cadastral) NOT IN ('02', 'ATIVA')")
            ->select([
                'p.id as participante_id',
                'p.cliente_id',
                'p.documento',
                'p.razao_social',
                'p.situacao_cadastral',
                DB::raw('COUNT(n.id) as total_notas'),
                DB::raw('SUM(n.valor_total) as valor_em_risco'),
            ])
            ->groupBy('p.id', 'p.cliente_id', 'p.documento', 'p.razao_social', 'p.situacao_cadastral')
            ->get();
    }

    /** Coluna de subscore ↔ chave(s) no dados_consultados ↔ rótulo, por certidão de regularidade. */
    private const CERTIDOES_MAP = [
        'cnd_federal' => ['score' => 'score_cnd_federal', 'chaves' => ['cnd_federal'], 'label' => 'CND Federal'],
        'cnd_estadual' => ['score' => 'score_cnd_estadual', 'chaves' => ['cnd_estadual'], 'label' => 'CND Estadual'],
        'fgts' => ['score' => 'score_fgts', 'chaves' => ['crf_fgts', 'fgts'], 'label' => 'FGTS/CRF'],
        'trabalhista' => ['score' => 'score_trabalhista', 'chaves' => ['cndt'], 'label' => 'CNDT (Trabalhista)'],
    ];

    /**
     * Alvos (fornecedores e clientes) com ≥1 certidão de regularidade IRREGULAR (Positiva).
     * Lê de participante_scores (subscore de certidão > 0 = irregular; cobre participante e
     * cliente e já mescla consultas parciais). Anexa valor comprado quando o alvo é fornecedor.
     *
     * @return array<int, array<string, mixed>>
     */
    private function detectarCertidoesPositivas(int $userId): array
    {
        $scores = ParticipanteScore::where('user_id', $userId)
            ->where(function ($q) {
                foreach (self::CERTIDOES_MAP as $m) {
                    $q->orWhere($m['score'], '>', 0);
                }
            })
            ->with([
                'participante:id,razao_social,documento,cliente_id',
                'cliente:id,razao_social,documento',
            ])
            ->get();

        $participanteIds = $scores->pluck('participante_id')->filter()->unique()->values()->all();
        $compras = $participanteIds ? $this->comprasPorParticipanteIds($userId, $participanteIds) : [];

        $alvos = [];
        foreach ($scores as $s) {
            $certidoes = [];
            $severidade = null;

            foreach (self::CERTIDOES_MAP as $categoria => $m) {
                if ((int) ($s->{$m['score']} ?? 0) <= 0) {
                    continue;
                }
                $sev = RiskScoreService::GRAVIDADE_CERTIDAO[$categoria]['severidade'];
                $certidoes[] = [
                    'chave' => $categoria,
                    'label' => $m['label'],
                    'status' => $this->statusCertidaoDados($s->dados_consultados, $m['chaves']),
                    'severidade' => $sev,
                ];
                $severidade = $this->maiorSeveridade($severidade, $sev);
            }

            if ($certidoes === []) {
                continue;
            }

            // Precedência: score de participante (fornecedor) vence score de cliente.
            // `cliente_id` do alerta = cliente do participante (score de participante não guarda
            // cliente_id) — senão o filtro de alertas por cliente exclui este alerta.
            if ($s->participante_id && $s->participante) {
                $tipoAlvo = 'participante';
                $alvoId = $s->participante_id;
                $razao = $s->participante->razao_social;
                $documento = $s->participante->documento;
                $clienteIdAlerta = $s->participante->cliente_id;
            } elseif ($s->cliente_id && $s->cliente) {
                $tipoAlvo = 'cliente';
                $alvoId = $s->cliente_id;
                $razao = $s->cliente->razao_social;
                $documento = $s->cliente->documento;
                $clienteIdAlerta = $s->cliente_id;
            } else {
                continue; // score órfão (alvo removido)
            }

            $alvos[] = [
                'tipo_alvo' => $tipoAlvo,
                'alvo_id' => $alvoId,
                'participante_id' => $s->participante_id,
                'cliente_id' => $clienteIdAlerta,
                'razao_social' => $razao,
                'documento' => $documento,
                'certidoes' => $certidoes,
                'severidade' => $severidade ?? 'media',
                'valor_total' => $compras[$s->participante_id]['valor_total'] ?? null,
                'valor_12m' => $compras[$s->participante_id]['valor_12m'] ?? null,
                'valor_5anos' => $compras[$s->participante_id]['valor_5anos'] ?? null,
                'qtd_notas' => $compras[$s->participante_id]['qtd'] ?? null,
                'qtd_12m' => $compras[$s->participante_id]['qtd_12m'] ?? null,
            ];
        }

        return $alvos;
    }

    /**
     * Valor COMPRADO por participante (fornecedor), em três janelas por `data_emissao`:
     *  - 12 meses → risco "vivo" (relação corrente): principal para triagem;
     *  - 5 anos (decadência tributária, CTN) → exposição sujeita a glosa de crédito. Créditos de
     *    compras fora dessa janela em regra escapam à revisão do Fisco;
     *  - total → contexto histórico e reconciliação com o Cruzamentos (que é all-time).
     * Semântica idêntica ao Cruzamentos: `origem_arquivo='fiscal'` + `tipo_operacao='entrada'`,
     * sem recorte de cliente. (5 anos a partir da emissão é aproximação — o marco exato de
     * decadência varia entre CTN art. 150 §4º e 173; suficiente para triagem.)
     */
    private function comprasPorParticipanteIds(int $userId, array $ids): array
    {
        $desde12m = now()->subMonths(12)->toDateString();
        $desde5anos = now()->subYears(5)->toDateString();

        return DB::table('efd_notas as n')
            ->where('n.user_id', $userId)
            ->where('n.origem_arquivo', 'fiscal')
            ->where('n.tipo_operacao', 'entrada')
            ->whereIn('n.participante_id', $ids)
            ->groupBy('n.participante_id')
            ->selectRaw('n.participante_id')
            ->selectRaw('COUNT(n.id) as qtd')
            ->selectRaw('SUM(n.valor_total) as valor_total')
            ->selectRaw('SUM(CASE WHEN n.data_emissao >= ? THEN n.valor_total ELSE 0 END) as valor_12m', [$desde12m])
            ->selectRaw('SUM(CASE WHEN n.data_emissao >= ? THEN 1 ELSE 0 END) as qtd_12m', [$desde12m])
            ->selectRaw('SUM(CASE WHEN n.data_emissao >= ? THEN n.valor_total ELSE 0 END) as valor_5anos', [$desde5anos])
            ->get()
            ->keyBy('participante_id')
            ->map(fn ($r) => [
                'valor_total' => (float) $r->valor_total,
                'valor_12m' => (float) $r->valor_12m,
                'valor_5anos' => (float) $r->valor_5anos,
                'qtd' => (int) $r->qtd,
                'qtd_12m' => (int) $r->qtd_12m,
            ])
            ->all();
    }

    /** Extrai o texto de status da certidão do dados_consultados (bloco aninhado ou string). */
    private function statusCertidaoDados(?array $dados, array $chaves): ?string
    {
        if (! is_array($dados)) {
            return null;
        }

        foreach ($chaves as $chave) {
            $bloco = $dados[$chave] ?? null;
            if (is_array($bloco) && ! empty($bloco['status'])) {
                return (string) $bloco['status'];
            }
            if (is_string($bloco) && $bloco !== '') {
                return $bloco;
            }
        }

        return null;
    }

    /** Maior severidade entre duas (baixa < media < alta). */
    private function maiorSeveridade(?string $atual, string $nova): string
    {
        $rank = ['baixa' => 1, 'media' => 2, 'alta' => 3];

        if ($atual === null) {
            return $nova;
        }

        return ($rank[$nova] ?? 0) > ($rank[$atual] ?? 0) ? $nova : $atual;
    }

    /**
     * Monta o alerta agregado de certidão positiva (1 por CNPJ).
     *
     * @param  array<string, mixed>  $alvo
     * @return array<string, mixed>
     */
    private function buildCertidaoPositivaAlertData(array $alvo): array
    {
        $labels = array_map(fn ($c) => $c['label'], $alvo['certidoes']);
        $qtdCertidoes = count($alvo['certidoes']);
        $tipoTxt = $alvo['tipo_alvo'] === 'cliente' ? 'Cliente' : 'Fornecedor';
        $listaCert = implode(', ', $labels);

        $descricao = "{$alvo['razao_social']} ({$alvo['documento']}) possui "
            .($qtdCertidoes === 1 ? 'certidão positiva' : "{$qtdCertidoes} certidões positivas")
            .": {$listaCert}. Certidão positiva indica débito(s) exigível(is) na fonte oficial.";

        // Compras (fornecedor): 12m = risco vivo; 5 anos = exposição sujeita a glosa (decadência);
        // total = contexto/reconciliação com o Cruzamentos.
        if (($alvo['valor_total'] ?? null) !== null && $alvo['valor_total'] > 0) {
            $recenteFmt = number_format((float) ($alvo['valor_12m'] ?? 0), 2, ',', '.');
            $glosavelFmt = number_format((float) ($alvo['valor_5anos'] ?? 0), 2, ',', '.');
            $totalFmt = number_format((float) $alvo['valor_total'], 2, ',', '.');
            $qtdNotas = $alvo['qtd_notas'];

            if (($alvo['valor_12m'] ?? 0) > 0) {
                $descricao .= " Você comprou R$ {$recenteFmt} desse fornecedor nos últimos 12 meses"
                    ." — R$ {$glosavelFmt} na janela de 5 anos (exposição sujeita a glosa de crédito),"
                    ." R$ {$totalFmt} no total ({$qtdNotas} nota(s)).";
            } elseif (($alvo['valor_5anos'] ?? 0) > 0) {
                $descricao .= " Sem compras nos últimos 12 meses, mas R$ {$glosavelFmt} na janela de 5 anos"
                    ." (exposição sujeita a glosa de crédito). Total histórico: R$ {$totalFmt}.";
            } else {
                $descricao .= " Você comprou R$ {$totalFmt} desse fornecedor no total ({$qtdNotas} nota(s)),"
                    .' mas nada nos últimos 5 anos — fora da janela de decadência, sem exposição a glosa.';
            }
        }

        return [
            'tipo' => 'certidao_positiva',
            'categoria' => 'compliance',
            'severidade' => $alvo['severidade'],
            'participante_id' => $alvo['participante_id'],
            'cliente_id' => $alvo['cliente_id'],
            'titulo' => "{$tipoTxt} com certidão positiva — {$alvo['razao_social']}",
            'descricao' => $descricao,
            // "afetados" = nº de certidões positivas (não de notas); a exposição em notas vai no detalhe.
            'total_afetados' => $qtdCertidoes,
            'detalhes' => [
                'tipo_alvo' => $alvo['tipo_alvo'],
                'razao_social' => $alvo['razao_social'],
                'documento' => $alvo['documento'],
                'certidoes' => $alvo['certidoes'],
                'valor_total' => $alvo['valor_total'],
                'valor_12m' => $alvo['valor_12m'],
                'valor_5anos' => $alvo['valor_5anos'],
                'qtd_notas' => $alvo['qtd_notas'],
                'qtd_12m' => $alvo['qtd_12m'],
            ],
        ];
    }

    /**
     * Meses sem importação EFD nos últimos 12 meses.
     * Retorna array de labels (ex: ["jan/26", "fev/26"]) ou null se não houver gaps.
     */
    private function detectarGapImportacoes(int $userId): ?array
    {
        $inicio = Carbon::now()->subMonths(11)->startOfMonth();
        $fim = Carbon::now()->startOfMonth();
        $mesesFaltantes = [];

        // Cobertura por COMPETÊNCIA (período da EFD), não por created_at — a data
        // do upload não é a competência (ver BiService::getGapImportacoes). Janela:
        // últimos 12 meses a partir de hoje (nudge de obrigação acessória recorrente).
        $competenciasEntregues = [];
        $importacoes = DB::table('efd_importacoes')
            ->where('user_id', $userId)
            ->where('status', 'concluido')
            ->whereNotNull('periodo_inicio')
            ->get(['periodo_inicio', 'periodo_fim']);

        foreach ($importacoes as $imp) {
            $ini = Carbon::parse($imp->periodo_inicio)->startOfMonth();
            $end = $imp->periodo_fim ? Carbon::parse($imp->periodo_fim)->startOfMonth() : $ini;
            foreach (CarbonPeriod::create($ini, '1 month', $end) as $mes) {
                $competenciasEntregues[$mes->format('Y-m')] = true;
            }
        }

        foreach (CarbonPeriod::create($inicio, '1 month', $fim) as $mes) {
            if (! isset($competenciasEntregues[$mes->format('Y-m')])) {
                $mesesFaltantes[] = $mes->locale('pt_BR')->isoFormat('MMM/YY');
            }
        }

        return count($mesesFaltantes) > 0 ? $mesesFaltantes : null;
    }

    /**
     * Registra (ou atualiza) um alerta in-app do monitoramento contínuo.
     * Dedup por (user_id, hash). `monitoramento_consulta_id` vai no `detalhes`
     * (a tabela `alertas` não tem coluna própria pra isso).
     *
     * @param  array<string, mixed>  $payload
     */
    public function registrarAlertaMonitoramento(array $payload): Alerta
    {
        $userId = $payload['user_id'];
        $consultaId = $payload['monitoramento_consulta_id'] ?? null;

        $hash = hash('sha256', implode(':', [
            $userId,
            $payload['tipo'],
            $payload['participante_id'] ?? '',
            $payload['cliente_id'] ?? '',
            $consultaId ?? '',
        ]));

        $data = [
            'tipo' => $payload['tipo'],
            'categoria' => 'monitoramento',
            'severidade' => $payload['severidade'],
            'titulo' => $payload['titulo'],
            'descricao' => $payload['descricao'],
            'participante_id' => $payload['participante_id'] ?? null,
            'cliente_id' => $payload['cliente_id'] ?? null,
            'detalhes' => $consultaId ? ['monitoramento_consulta_id' => $consultaId] : null,
        ];

        $existing = Alerta::where('user_id', $userId)->where('hash', $hash)->first();

        if ($existing) {
            if (! in_array($existing->status, ['resolvido', 'ignorado'], true)) {
                $data['status'] = 'ativo';
            }
            $existing->update($data);

            return $existing;
        }

        return Alerta::create(array_merge($data, [
            'user_id' => $userId,
            'hash' => $hash,
            'status' => 'ativo',
        ]));
    }
}
