<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\ParticipanteGrupo;
use App\Services\CreditService;
use App\Services\RafReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RafConsultaController extends Controller
{
    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        protected CreditService $creditService,
        protected RafReportService $reportService
    ) {}

    /**
     * Determina o prefixo da view baseado na rota atual.
     * Suporta tanto /app/raf/* quanto /app/consultas/*
     */
    private function getViewPrefix(): string
    {
        $currentPath = request()->path();

        if (str_starts_with($currentPath, 'app/consultas')) {
            return 'autenticado.consultas.';
        }

        return 'autenticado.raf.';
    }

    /**
     * Página principal de consulta.
     */
    public function index(Request $request)
    {
        $viewPrefix = $this->getViewPrefix();
        $viewName = str_ends_with($viewPrefix, 'consultas.') ? 'nova' : 'consulta';
        $consultaView = $viewPrefix.$viewName;

        if (! view()->exists($consultaView)) {
            abort(404);
        }

        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();

        // Buscar planos ativos
        $planos = MonitoramentoPlano::ativos();

        // Buscar clientes do usuário
        $clientes = Cliente::where('user_id', $user->id)
            ->orderBy('razao_social')
            ->get();

        // Buscar grupos do usuário
        $grupos = ParticipanteGrupo::doUsuario($user->id)
            ->withCount('participantes')
            ->orderBy('nome')
            ->get();

        // Contagem total de participantes
        $totalParticipantes = Participante::where('user_id', $user->id)->count();

        // Últimos lotes do usuário
        $ultimosLotes = ConsultaLote::where('user_id', $user->id)
            ->with('plano')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $data = [
            'planos' => $planos,
            'clientes' => $clientes,
            'grupos' => $grupos,
            'totalParticipantes' => $totalParticipantes,
            'ultimosLotes' => $ultimosLotes,
            'credits' => $this->creditService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($consultaView, $data)->render();

            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $consultaView,
        ], $data));
    }

    /**
     * Retorna lista de participantes com filtros (AJAX).
     */
    public function getParticipantes(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'grupo_id' => 'nullable|integer|exists:participantes_grupos,id',
            'cliente_id' => 'nullable|integer|exists:clientes,id',
            'origem_tipo' => 'nullable|string|in:NFE,NFSE,CTE,SPED_EFD_FISCAL,SPED_EFD_CONTRIB,MANUAL',
            'busca' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $perPage = $validated['per_page'] ?? 50;

        $query = Participante::where('user_id', $user->id)
            ->with(['grupos:id,nome,cor']);

        // Filtro por grupo
        if (! empty($validated['grupo_id'])) {
            $query->whereHas('grupos', function ($q) use ($validated) {
                $q->where('participantes_grupos.id', $validated['grupo_id']);
            });
        }

        // Filtro por cliente
        if (! empty($validated['cliente_id'])) {
            $query->where('cliente_id', $validated['cliente_id']);
        }

        // Filtro por origem
        if (! empty($validated['origem_tipo'])) {
            $query->where('origem_tipo', $validated['origem_tipo']);
        }

        // Filtro por busca (CNPJ ou razão social)
        if (! empty($validated['busca'])) {
            $busca = $validated['busca'];
            $buscaLimpa = preg_replace('/[^0-9]/', '', $busca);

            $query->where(function ($q) use ($busca, $buscaLimpa) {
                $q->where('razao_social', 'ILIKE', "%{$busca}%")
                    ->orWhere('nome_fantasia', 'ILIKE', "%{$busca}%");

                if (strlen($buscaLimpa) >= 3) {
                    $q->orWhere('cnpj', 'LIKE', "%{$buscaLimpa}%");
                }
            });
        }

        $participantes = $query->orderBy('razao_social')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $participantes->items(),
            'pagination' => [
                'current_page' => $participantes->currentPage(),
                'last_page' => $participantes->lastPage(),
                'per_page' => $participantes->perPage(),
                'total' => $participantes->total(),
            ],
        ]);
    }

    /**
     * Retorna IDs de participantes de um grupo específico.
     */
    public function getParticipantesGrupo(Request $request, int $grupoId): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $grupo = ParticipanteGrupo::where('id', $grupoId)
            ->where('user_id', $user->id)
            ->first();

        if (! $grupo) {
            return response()->json([
                'success' => false,
                'error' => 'Grupo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $participanteIds = $grupo->participantes()->pluck('participantes.id')->toArray();

        return response()->json([
            'success' => true,
            'grupo_id' => $grupoId,
            'grupo_nome' => $grupo->nome,
            'participante_ids' => $participanteIds,
            'total' => count($participanteIds),
        ]);
    }

    /**
     * Calcula custo da consulta antes de executar.
     */
    public function calcularCusto(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'participante_ids' => 'required|array|min:1',
            'participante_ids.*' => 'integer|exists:participantes,id',
            'plano_id' => 'required|integer|exists:monitoramento_planos,id',
        ]);

        // Verificar que os participantes pertencem ao usuário
        $participantesValidos = Participante::where('user_id', $user->id)
            ->whereIn('id', $validated['participante_ids'])
            ->count();

        if ($participantesValidos !== count($validated['participante_ids'])) {
            return response()->json([
                'success' => false,
                'error' => 'Alguns participantes selecionados não pertencem ao usuário.',
            ], Response::HTTP_FORBIDDEN);
        }

        $plano = MonitoramentoPlano::find($validated['plano_id']);

        if (! $plano || ! $plano->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Plano não disponível.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $totalParticipantes = count($validated['participante_ids']);
        $custoUnitario = $plano->custo_creditos;
        $custoTotal = $totalParticipantes * $custoUnitario;
        $saldoAtual = $this->creditService->getBalance($user);
        $saldoApos = $saldoAtual - $custoTotal;

        return response()->json([
            'success' => true,
            'calculo' => [
                'total_participantes' => $totalParticipantes,
                'plano_codigo' => $plano->codigo,
                'plano_nome' => $plano->nome,
                'custo_unitario' => $custoUnitario,
                'custo_total' => $custoTotal,
                'is_gratuito' => $plano->is_gratuito,
                'saldo_atual' => $saldoAtual,
                'saldo_apos' => $saldoApos,
                'creditos_suficientes' => $saldoApos >= 0,
            ],
        ]);
    }

    /**
     * Executa a consulta de lote.
     */
    public function executar(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'participante_ids' => 'required|array|min:1|max:1000',
            'participante_ids.*' => 'integer|exists:participantes,id',
            'plano_id' => 'required|integer|exists:monitoramento_planos,id',
            'cliente_id' => 'nullable|integer|exists:clientes,id',
            'tab_id' => 'required|string|max:36',
        ]);

        // Verificar que os participantes pertencem ao usuário
        $participantes = Participante::where('user_id', $user->id)
            ->whereIn('id', $validated['participante_ids'])
            ->get(['id', 'cnpj', 'razao_social', 'uf', 'crt']);

        if ($participantes->count() !== count($validated['participante_ids'])) {
            return response()->json([
                'success' => false,
                'error' => 'Alguns participantes selecionados não pertencem ao usuário.',
            ], Response::HTTP_FORBIDDEN);
        }

        $plano = MonitoramentoPlano::find($validated['plano_id']);

        if (! $plano || ! $plano->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Plano não disponível.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Calcular custo
        $totalParticipantes = $participantes->count();
        $custoTotal = $totalParticipantes * $plano->custo_creditos;

        // Verificar créditos (se não for gratuito)
        if (! $plano->is_gratuito && ! $this->creditService->hasEnough($user, $custoTotal)) {
            return response()->json([
                'success' => false,
                'error' => 'Créditos insuficientes.',
                'creditos_necessarios' => $custoTotal,
                'creditos_disponiveis' => $this->creditService->getBalance($user),
            ], Response::HTTP_PAYMENT_REQUIRED);
        }

        // Verificar webhook configurado (novo padrão com fallback para legado)
        $webhookUrl = config('services.webhook.consultas_lotes_url')
            ?: config('services.webhook.raf_consulta_url');

        if (empty($webhookUrl)) {
            Log::error('Consultas: webhook não configurado (WEBHOOK_CONSULTAS_LOTES_URL)');

            return response()->json([
                'success' => false,
                'error' => 'Configuração de webhook ausente. Contate o suporte.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            // Debitar créditos (se não for gratuito)
            if (! $plano->is_gratuito) {
                $debitado = $this->creditService->deduct($user, $custoTotal);
                if (! $debitado) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Falha ao debitar créditos. Tente novamente.',
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            // Criar lote
            $lote = ConsultaLote::create([
                'user_id' => $user->id,
                'cliente_id' => $validated['cliente_id'] ?? null,
                'plano_id' => $plano->id,
                'status' => ConsultaLote::STATUS_PROCESSANDO,
                'total_participantes' => $totalParticipantes,
                'creditos_cobrados' => $custoTotal,
                'tab_id' => $validated['tab_id'],
            ]);

            // Associar participantes
            $lote->participantes()->attach($validated['participante_ids']);

            Log::info('Consulta: lote criado', [
                'consulta_lote_id' => $lote->id,
                'user_id' => $user->id,
                'plano' => $plano->codigo,
                'total_participantes' => $totalParticipantes,
                'creditos_cobrados' => $custoTotal,
            ]);

            // Preparar payload para n8n
            $payload = [
                'user_id' => $user->id,
                'consulta_lote_id' => $lote->id,
                'tab_id' => $validated['tab_id'],
                'plano_codigo' => $plano->codigo,
                'consultas_incluidas' => $plano->consultas_incluidas,
                'participantes' => $participantes->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'cnpj' => preg_replace('/[^0-9]/', '', $p->cnpj),
                        'razao_social' => $p->razao_social,
                        'uf' => $p->uf,
                        'crt' => $p->crt,
                    ];
                })->toArray(),
                'progress_url' => url('/api/consultas/lote/progress'),
                'resultado_url' => url('/api/consultas/lote/resultado'),
            ];

            // Enviar para n8n
            $response = Http::timeout(60)
                ->withHeaders([
                    'X-API-Token' => config('services.api.token'),
                    'Content-Type' => 'application/json',
                ])
                ->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Consulta: enviado para n8n com sucesso', [
                    'consulta_lote_id' => $lote->id,
                    'response_status' => $response->status(),
                ]);

                return response()->json([
                    'success' => true,
                    'consulta_lote_id' => $lote->id,
                    'message' => 'Consulta iniciada com sucesso.',
                    'creditos_cobrados' => $custoTotal,
                    'novo_saldo' => $this->creditService->getBalance($user),
                ]);
            } else {
                // Falha no envio - estornar créditos e marcar erro
                if (! $plano->is_gratuito) {
                    $this->creditService->add($user, $custoTotal);
                }

                $lote->update([
                    'status' => ConsultaLote::STATUS_ERRO,
                    'error_code' => 'WEBHOOK_ERROR',
                    'error_message' => 'Erro ao enviar para processamento: '.$response->status(),
                ]);

                Log::error('Consulta: erro na resposta do n8n', [
                    'consulta_lote_id' => $lote->id,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao iniciar processamento. Créditos foram estornados.',
                ], Response::HTTP_BAD_GATEWAY);
            }

        } catch (\Exception $e) {
            Log::error('Consulta: exceção ao executar', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Se lote foi criado, marcar como erro
            if (isset($lote)) {
                $lote->update([
                    'status' => ConsultaLote::STATUS_ERRO,
                    'error_code' => 'INTERNAL_ERROR',
                    'error_message' => $e->getMessage(),
                ]);

                // Estornar créditos
                if (! $plano->is_gratuito && $custoTotal > 0) {
                    $this->creditService->add($user, $custoTotal);
                }
            }

            return response()->json([
                'success' => false,
                'error' => 'Erro interno ao processar consulta.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * SSE para acompanhar progresso do lote.
     */
    public function streamProgresso(Request $request)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userId = auth()->id();
        $tabId = $request->query('tab_id');

        if (! $tabId) {
            return response()->json([
                'success' => false,
                'error' => 'tab_id obrigatório.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $cacheKey = "progresso:{$userId}:{$tabId}";

        Log::info('SSE Consulta streamProgresso iniciado', [
            'user_id' => $userId,
            'tab_id' => $tabId,
            'cache_key' => $cacheKey,
        ]);

        return response()->stream(function () use ($cacheKey, $userId, $tabId) {
            $tentativas = 0;
            $maxTentativas = 600; // 10 minutos
            $lastDataHash = null;

            echo ": SSE connection established for consulta progress stream (user:{$userId}, tab:{$tabId})\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            while ($tentativas < $maxTentativas) {
                try {
                    $data = Cache::get($cacheKey);

                    if ($data) {
                        $currentHash = md5(json_encode($data));

                        if ($currentHash !== $lastDataHash) {
                            $lastDataHash = $currentHash;

                            echo 'data: '.json_encode($data)."\n\n";

                            if (ob_get_level() > 0) {
                                ob_flush();
                            }
                            flush();

                            if (in_array($data['status'] ?? '', ['concluido', 'erro'])) {
                                Log::info('SSE Consulta streamProgresso: status final recebido', [
                                    'user_id' => $userId,
                                    'tab_id' => $tabId,
                                    'status' => $data['status'],
                                ]);
                                Cache::forget($cacheKey);
                                break;
                            }
                        }
                    }

                    if (connection_aborted()) {
                        Log::info('SSE Consulta streamProgresso: conexão abortada pelo cliente', [
                            'user_id' => $userId,
                            'tab_id' => $tabId,
                        ]);
                        break;
                    }

                    sleep(1);
                    $tentativas++;

                } catch (\Exception $e) {
                    Log::error('SSE Consulta streamProgresso: erro no loop', [
                        'user_id' => $userId,
                        'tab_id' => $tabId,
                        'error' => $e->getMessage(),
                    ]);
                    sleep(1);
                    $tentativas++;
                    if (connection_aborted()) {
                        break;
                    }
                }
            }

            if ($tentativas >= $maxTentativas) {
                echo 'data: '.json_encode([
                    'status' => 'timeout',
                    'progresso' => 0,
                    'mensagem' => 'Tempo limite atingido. Verifique o histórico.',
                ])."\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                Log::warning('SSE Consulta streamProgresso: timeout', [
                    'user_id' => $userId,
                    'tab_id' => $tabId,
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Download do relatório de um lote (CSV ou PDF).
     *
     * Gera o relatório on-demand a partir dos dados em consulta_resultados.
     * Fallback para report_csv_base64 se não houver resultados na nova tabela.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function baixarLote(Request $request, int $id)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $lote = ConsultaLote::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['plano', 'resultados.participante'])
            ->first();

        if (! $lote) {
            abort(404, 'Lote não encontrado.');
        }

        if (! $lote->isConcluido()) {
            abort(400, 'Lote ainda não foi processado.');
        }

        $formato = strtolower($request->query('formato', 'csv'));

        // Se tem resultados na nova tabela, gerar on-demand
        if ($lote->hasResultados()) {
            if ($formato === 'pdf') {
                $pdf = $this->reportService->gerarPdf($lote);
                $filename = "consulta_lote_{$lote->id}.pdf";

                return $pdf->download($filename);
            }

            // CSV
            $csvContent = $this->reportService->gerarCsv($lote);
            $filename = $lote->filename ?? "consulta_lote_{$lote->id}.csv";

            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
        }

        // Fallback: usar CSV pré-gerado (compatibilidade com lotes antigos)
        if (! empty($lote->report_csv_base64)) {
            if ($formato === 'pdf') {
                abort(400, 'Formato PDF não disponível para este lote. Use CSV.');
            }

            $csvContent = base64_decode($lote->report_csv_base64);
            $filename = $lote->filename ?? "consulta_lote_{$lote->id}.csv";

            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
        }

        abort(404, 'Relatório não disponível.');
    }

    /**
     * Adiciona um CNPJ como participante (cadastro rápido).
     * Opcionalmente cria ou vincula a um Cliente.
     */
    public function adicionarCnpj(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $cnpjRaw = $request->input('cnpj', '');
        $cnpj = preg_replace('/[^0-9]/', '', trim($cnpjRaw));

        if (strlen($cnpj) !== 14) {
            return response()->json([
                'success' => false,
                'error' => 'CNPJ inválido. Informe 14 dígitos.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $criarCliente = (bool) $request->input('criar_cliente', false);
        $clienteIdInput = $request->input('cliente_id');
        $clienteId = null;

        if ($criarCliente) {
            if ($clienteIdInput === 'novo' || $clienteIdInput === null) {
                // Criar novo Cliente ou usar existente do mesmo usuario
                $clienteId = $this->resolveOrCreateCliente($user, $cnpj);

                if ($clienteId instanceof JsonResponse) {
                    return $clienteId; // Retorna erro
                }
            } else {
                // Vincular a cliente existente
                $clienteId = (int) $clienteIdInput;
                $clienteExists = Cliente::where('id', $clienteId)
                    ->where('user_id', $user->id)
                    ->exists();

                if (! $clienteExists) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Cliente não encontrado.',
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        $participante = Participante::firstOrCreate(
            ['user_id' => $user->id, 'cnpj' => $cnpj],
            [
                'origem_tipo' => 'MANUAL',
                'tipo_documento' => 'PJ',
                'cliente_id' => $clienteId,
            ]
        );

        $isNew = $participante->wasRecentlyCreated;

        // Se já existia e cliente_id foi informado, atualizar vínculo
        if (! $isNew && $clienteId && ! $participante->cliente_id) {
            $participante->update(['cliente_id' => $clienteId]);
        }

        $message = $isNew
            ? 'Participante adicionado com sucesso.'
            : 'CNPJ já cadastrado. Selecionado para consulta.';

        if ($criarCliente && $isNew) {
            $message = 'Participante e cliente criados com sucesso.';
        } elseif ($criarCliente && ! $isNew) {
            $message = 'CNPJ já cadastrado. Vinculado ao cliente.';
        }

        return response()->json([
            'success' => true,
            'is_new' => $isNew,
            'participante' => [
                'id' => $participante->id,
                'cnpj' => $participante->cnpj,
                'razao_social' => $participante->razao_social,
                'uf' => $participante->uf,
            ],
            'message' => $message,
        ]);
    }

    /**
     * Resolve ou cria um Cliente para o CNPJ informado.
     *
     * @return int|JsonResponse ID do cliente ou resposta de erro
     */
    private function resolveOrCreateCliente($user, string $cnpj)
    {
        // Verificar se já existe cliente com este documento para o mesmo usuario
        $existing = Cliente::where('documento', $cnpj)
            ->first();

        if ($existing) {
            if ($existing->user_id === $user->id) {
                return $existing->id;
            }

            // Documento pertence a outro usuario (unique global)
            return response()->json([
                'success' => false,
                'error' => 'Este CNPJ já está cadastrado como cliente por outro usuário.',
            ], Response::HTTP_CONFLICT);
        }

        // Criar novo cliente
        $cnpjFormatado = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);

        try {
            $cliente = Cliente::create([
                'user_id' => $user->id,
                'tipo_pessoa' => 'PJ',
                'documento' => $cnpj,
                'razao_social' => "CNPJ {$cnpjFormatado}",
                'ativo' => true,
            ]);

            return $cliente->id;
        } catch (\Illuminate\Database\QueryException $e) {
            // Unique violation (race condition)
            if (str_contains($e->getMessage(), '23505') || str_contains($e->getMessage(), 'unique')) {
                $existing = Cliente::where('documento', $cnpj)->first();
                if ($existing && $existing->user_id === $user->id) {
                    return $existing->id;
                }

                return response()->json([
                    'success' => false,
                    'error' => 'Este CNPJ já está cadastrado como cliente.',
                ], Response::HTTP_CONFLICT);
            }

            throw $e;
        }
    }

    /**
     * Histórico de lotes do usuário.
     */
    public function historico(Request $request)
    {
        $historicoView = $this->getViewPrefix().'historico';

        if (! view()->exists($historicoView)) {
            abort(404);
        }

        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();

        // Lotes de consultas
        $lotes = ConsultaLote::where('user_id', $user->id)
            ->with('plano')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $data = [
            'lotes' => $lotes,
            'relatoriosLegados' => collect([]), // Tabelas legadas removidas
            'credits' => $this->creditService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($historicoView, $data)->render();

            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $historicoView,
        ], $data));
    }

    /**
     * Verifica se a requisição é AJAX (navegação SPA).
     */
    private function isAjaxRequest(Request $request): bool
    {
        return $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Redireciona para login preservando URL.
     */
    private function redirectToLogin(Request $request)
    {
        session(['url.intended' => $request->fullUrl()]);

        return redirect()->route('login');
    }
}
