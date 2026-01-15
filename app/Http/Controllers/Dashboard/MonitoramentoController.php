<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoConsulta;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\ParticipanteGrupo;
use App\Models\RafRelatorioProcessado;
use App\Services\CreditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class MonitoramentoController extends Controller
{
    private const AUTH_VIEW_PREFIX = 'autenticado.monitoramento.';
    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Dashboard principal do monitoramento - lista participantes e estatísticas.
     */
    public function index(Request $request)
    {
        $indexView = self::AUTH_VIEW_PREFIX . 'index';

        if (!view()->exists($indexView)) {
            abort(404);
        }

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        // Estatísticas do mês atual
        $inicioMes = Carbon::now()->startOfMonth();
        $fimMes = Carbon::now()->endOfMonth();

        $stats = [
            'total_participantes' => Participante::where('user_id', $userId)->count(),
            'total_assinaturas' => MonitoramentoAssinatura::where('user_id', $userId)
                ->where('status', 'ativo')
                ->count(),
            'consultas_mes' => MonitoramentoConsulta::where('user_id', $userId)
                ->whereBetween('created_at', [$inicioMes, $fimMes])
                ->count(),
            'creditos_gastos_mes' => MonitoramentoConsulta::where('user_id', $userId)
                ->whereBetween('created_at', [$inicioMes, $fimMes])
                ->sum('creditos_cobrados'),
        ];

        // Filtro por grupo
        $grupoId = $request->get('grupo');

        // Buscar participantes com suas assinaturas e grupos
        $participantes = Participante::where('user_id', $userId)
            ->when($grupoId, function ($q) use ($grupoId) {
                $q->whereHas('grupos', fn($q) => $q->where('participante_grupos.id', $grupoId));
            })
            ->with(['assinaturas.plano', 'grupos', 'consultas' => function ($query) {
                $query->latest('executado_em')->limit(1);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Buscar grupos do usuário
        $grupos = ParticipanteGrupo::where('user_id', $userId)
            ->withCount('participantes')
            ->orderBy('nome')
            ->get();

        $data = [
            'stats' => $stats,
            'planos' => MonitoramentoPlano::ativos(),
            'participantes' => $participantes,
            'grupos' => $grupos,
            'grupoAtivo' => $grupoId,
            'coresPredefinidas' => ParticipanteGrupo::CORES_PREDEFINIDAS,
            'credits' => $this->creditService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($indexView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $indexView
        ], $data));
    }

    /**
     * Lista relatórios RAF para importação de participantes.
     */
    public function importarSped(Request $request)
    {
        $spedView = self::AUTH_VIEW_PREFIX . 'sped';

        if (!view()->exists($spedView)) {
            abort(404);
        }

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        // Buscar relatórios RAF processados do usuário
        $relatorios = RafRelatorioProcessado::where('user_id', $userId)
            ->whereNotNull('report_csv_base64')
            ->with('cliente')
            ->orderBy('created_at', 'desc')
            ->get();

        // Buscar clientes ativos do usuário para o select de associação
        $clientes = Cliente::where('user_id', $userId)
            ->where('ativo', true)
            ->orderBy('razao_social')
            ->get();

        $data = [
            'relatorios' => $relatorios,
            'credits' => $this->creditService->getBalance($user),
            'clientes' => $clientes,
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($spedView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $spedView
        ], $data));
    }

    /**
     * Formulário de consulta avulsa.
     */
    public function consultaAvulsa(Request $request)
    {
        $avulsoView = self::AUTH_VIEW_PREFIX . 'avulso';

        if (!view()->exists($avulsoView)) {
            abort(404);
        }

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        // Buscar participantes do usuário para lista
        $participantes = Participante::where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        // Buscar clientes ativos do usuário para o select de associação
        $clientes = Cliente::where('user_id', $userId)
            ->where('ativo', true)
            ->orderBy('razao_social')
            ->get();

        $data = [
            'planos' => MonitoramentoPlano::ativos(),
            'credits' => $this->creditService->getBalance($user),
            'participantes' => $participantes,
            'clientes' => $clientes,
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($avulsoView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $avulsoView
        ], $data));
    }

    /**
     * Histórico de consultas realizadas.
     */
    public function historico(Request $request)
    {
        $historicoView = self::AUTH_VIEW_PREFIX . 'historico';

        if (!view()->exists($historicoView)) {
            abort(404);
        }

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        // Buscar consultas com relacionamentos
        $consultas = MonitoramentoConsulta::where('user_id', $userId)
            ->with(['participante', 'plano', 'assinatura'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $data = [
            'consultas' => $consultas,
            'planos' => MonitoramentoPlano::ativos(),
            'credits' => $this->creditService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($historicoView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $historicoView
        ], $data));
    }

    /**
     * Detalhes de um participante específico.
     */
    public function participante(Request $request, $id)
    {
        $participanteView = self::AUTH_VIEW_PREFIX . 'participante';

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        $participante = Participante::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Carregar consultas do participante
        $consultas = MonitoramentoConsulta::where('participante_id', $participante->id)
            ->where('user_id', $userId)
            ->with('plano')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Buscar assinatura ativa ou pausada
        $assinaturaAtiva = MonitoramentoAssinatura::where('participante_id', $participante->id)
            ->where('user_id', $userId)
            ->whereIn('status', ['ativo', 'pausado'])
            ->with('plano')
            ->first();

        // Carregar planos disponíveis
        $planos = MonitoramentoPlano::ativos()->get();

        // Estatísticas do participante
        $estatisticas = [
            'total_consultas' => MonitoramentoConsulta::where('participante_id', $participante->id)
                ->where('user_id', $userId)
                ->count(),
            'consultas_sucesso' => MonitoramentoConsulta::where('participante_id', $participante->id)
                ->where('user_id', $userId)
                ->where('status', 'sucesso')
                ->count(),
            'consultas_erro' => MonitoramentoConsulta::where('participante_id', $participante->id)
                ->where('user_id', $userId)
                ->where('status', 'erro')
                ->count(),
            'creditos_utilizados' => MonitoramentoConsulta::where('participante_id', $participante->id)
                ->where('user_id', $userId)
                ->sum('creditos_cobrados'),
        ];

        // Saldo de créditos do usuário
        $credits = $this->creditService->getBalance($user);

        $data = [
            'participante' => $participante,
            'consultas' => $consultas,
            'assinaturaAtiva' => $assinaturaAtiva,
            'planos' => $planos,
            'estatisticas' => $estatisticas,
            'credits' => $credits,
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($participanteView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $participanteView
        ], $data));
    }

    /**
     * Detalhes de uma consulta específica (retorna JSON).
     */
    public function consultaDetalhes(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $consulta = MonitoramentoConsulta::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['participante', 'plano'])
            ->first();

        if (!$consulta) {
            return response()->json([
                'success' => false,
                'message' => 'Consulta não encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'id' => $consulta->id,
            'tipo' => $consulta->tipo,
            'status' => $consulta->status,
            'creditos_cobrados' => $consulta->creditos_cobrados,
            'executado_em' => $consulta->executado_em?->format('d/m/Y H:i'),
            'created_at' => $consulta->created_at->format('d/m/Y H:i'),
            'plano' => $consulta->plano ? [
                'nome' => $consulta->plano->nome,
                'codigo' => $consulta->plano->codigo,
            ] : null,
            'resultado' => $consulta->resultado ?? [
                'cnpj' => $consulta->participante?->cnpj,
                'razao_social' => $consulta->participante?->razao_social,
                'situacao_cadastral' => $consulta->participante?->situacao_cadastral,
                'regime_tributario' => $consulta->participante?->regime_tributario,
                'detalhes' => [],
            ],
            'error_message' => $consulta->error_message,
        ]);
    }

    /**
     * Lista participantes de um relatório RAF para importação.
     */
    public function participantesRaf(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $relatorio = RafRelatorioProcessado::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$relatorio || empty($relatorio->report_csv_base64)) {
            return response()->json([
                'success' => false,
                'message' => 'Relatório não encontrado ou sem dados.',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            // Decodificar CSV base64
            $csvContent = base64_decode($relatorio->report_csv_base64);
            $lines = explode("\n", $csvContent);

            $participantes = [];
            $header = null;
            $cnpjIndex = null;
            $razaoSocialIndex = null;
            $situacaoIndex = null;
            $regimeIndex = null;

            foreach ($lines as $lineNum => $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // Parse CSV line
                $columns = str_getcsv($line, ';');

                if ($lineNum === 0) {
                    // Header - encontrar índices das colunas relevantes
                    $header = array_map('strtolower', array_map('trim', $columns));

                    foreach ($header as $idx => $col) {
                        if (str_contains($col, 'cnpj') && !str_contains($col, 'empresa')) {
                            $cnpjIndex = $idx;
                        } elseif (str_contains($col, 'razao') || str_contains($col, 'razão')) {
                            $razaoSocialIndex = $idx;
                        } elseif (str_contains($col, 'situacao') || str_contains($col, 'situação')) {
                            $situacaoIndex = $idx;
                        } elseif (str_contains($col, 'regime')) {
                            $regimeIndex = $idx;
                        }
                    }
                    continue;
                }

                if ($cnpjIndex === null) continue;

                $cnpj = preg_replace('/[^0-9]/', '', $columns[$cnpjIndex] ?? '');
                if (strlen($cnpj) !== 14) continue;

                $participantes[] = [
                    'cnpj' => $cnpj,
                    'cnpj_formatado' => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj),
                    'razao_social' => $razaoSocialIndex !== null ? ($columns[$razaoSocialIndex] ?? 'Não informado') : 'Não informado',
                    'situacao' => $situacaoIndex !== null ? ($columns[$situacaoIndex] ?? 'Desconhecida') : 'Desconhecida',
                    'regime' => $regimeIndex !== null ? ($columns[$regimeIndex] ?? 'Não informado') : 'Não informado',
                ];
            }

            return response()->json([
                'success' => true,
                'relatorio' => [
                    'id' => $relatorio->id,
                    'cnpj_empresa' => $relatorio->cnpj_empresa_analisada,
                    'razao_social' => $relatorio->razao_social_empresa,
                    'document_type' => $relatorio->document_type,
                    'total_participantes' => count($participantes),
                ],
                'participantes' => $participantes,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao extrair participantes do RAF', [
                'relatorio_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar dados do relatório.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Importa participantes de um relatório RAF.
     */
    public function importarDoRaf(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $participantesData = $request->input('participantes', []);

        if (empty($participantesData)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum participante selecionado para importação.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $relatorio = RafRelatorioProcessado::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$relatorio) {
            return response()->json([
                'success' => false,
                'message' => 'Relatório não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $importados = 0;
        $duplicados = 0;

        try {
            DB::beginTransaction();

            foreach ($participantesData as $p) {
                $cnpj = preg_replace('/[^0-9]/', '', $p['cnpj'] ?? '');
                if (strlen($cnpj) !== 14) continue;

                // Verificar se já existe
                $existente = Participante::where('user_id', $user->id)
                    ->where('cnpj', $cnpj)
                    ->first();

                if ($existente) {
                    $duplicados++;
                    continue;
                }

                Participante::create([
                    'user_id' => $user->id,
                    'cnpj' => $cnpj,
                    'razao_social' => $p['razao_social'] ?? null,
                    'situacao_cadastral' => $p['situacao'] ?? null,
                    'regime_tributario' => $p['regime'] ?? null,
                    'origem_tipo' => $relatorio->document_type === 'EFD Fiscal' ? 'SPED_EFD_FISCAL' : 'SPED_EFD_CONTRIB',
                    'origem_ref' => ['raf_relatorio_id' => $relatorio->id],
                ]);

                $importados++;
            }

            DB::commit();

            Log::info('Participantes importados do RAF', [
                'user_id' => $user->id,
                'relatorio_id' => $id,
                'importados' => $importados,
                'duplicados' => $duplicados,
            ]);

            $message = $importados . ' participante(s) importado(s) com sucesso.';
            if ($duplicados > 0) {
                $message .= ' ' . $duplicados . ' já existiam e foram ignorados.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'importados' => $importados,
                'duplicados' => $duplicados,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao importar participantes do RAF', [
                'user_id' => $user->id,
                'relatorio_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar participantes. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Adiciona CNPJs avulsos para monitoramento.
     */
    public function adicionarCnpj(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $cnpjsInput = $request->input('cnpjs', '');

        // Aceita string separada por vírgula, quebra de linha ou array
        if (is_string($cnpjsInput)) {
            $cnpjs = preg_split('/[,;\n\r]+/', $cnpjsInput);
        } else {
            $cnpjs = $cnpjsInput;
        }

        $cnpjs = array_filter(array_map(function ($cnpj) {
            return preg_replace('/[^0-9]/', '', trim($cnpj));
        }, $cnpjs), function ($cnpj) {
            return strlen($cnpj) === 14;
        });

        if (empty($cnpjs)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum CNPJ válido informado.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $adicionados = 0;
        $duplicados = 0;

        try {
            DB::beginTransaction();

            foreach ($cnpjs as $cnpj) {
                // Verificar se já existe
                $existente = Participante::where('user_id', $user->id)
                    ->where('cnpj', $cnpj)
                    ->first();

                if ($existente) {
                    $duplicados++;
                    continue;
                }

                Participante::create([
                    'user_id' => $user->id,
                    'cnpj' => $cnpj,
                    'origem_tipo' => 'MANUAL',
                ]);

                $adicionados++;
            }

            DB::commit();

            Log::info('CNPJs avulsos adicionados', [
                'user_id' => $user->id,
                'adicionados' => $adicionados,
                'duplicados' => $duplicados,
            ]);

            $message = $adicionados . ' CNPJ(s) adicionado(s) com sucesso.';
            if ($duplicados > 0) {
                $message .= ' ' . $duplicados . ' já existiam e foram ignorados.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'adicionados' => $adicionados,
                'duplicados' => $duplicados,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao adicionar CNPJs', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar CNPJs. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cria assinatura de monitoramento para um ou mais participantes.
     */
    public function criarAssinatura(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        // Aceita participante_id (único) ou participantes (array)
        $participanteId = $request->input('participante_id');
        $participanteIds = $request->input('participantes', []);
        $planoId = $request->input('plano_id');
        $frequencia = $request->input('frequencia', 'quinzenal');

        // Se participante_id foi passado, converter para array
        if ($participanteId && empty($participanteIds)) {
            $participanteIds = [$participanteId];
        }

        if (empty($participanteIds) || empty($planoId)) {
            return response()->json([
                'success' => false,
                'error' => 'Dados incompletos. Selecione participantes e um plano.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validar frequência
        $frequenciasValidas = ['diario', 'semanal', 'quinzenal', 'mensal'];
        if (!in_array($frequencia, $frequenciasValidas)) {
            return response()->json([
                'success' => false,
                'error' => 'Frequência inválida.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Buscar plano
        $plano = MonitoramentoPlano::find($planoId);
        if (!$plano || !$plano->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Plano não encontrado ou inativo.',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();

            $assinaturasCriadas = 0;
            $jaExistentes = 0;

            foreach ($participanteIds as $pId) {
                // Verificar se participante pertence ao usuário
                $participante = Participante::where('id', $pId)
                    ->where('user_id', $user->id)
                    ->first();

                if (!$participante) {
                    continue;
                }

                // Verificar se já existe assinatura ativa/pausada
                $assinaturaExistente = MonitoramentoAssinatura::where('participante_id', $participante->id)
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['ativo', 'pausado'])
                    ->first();

                if ($assinaturaExistente) {
                    $jaExistentes++;
                    continue;
                }

                // Converter frequência para dias
                $frequenciaDias = $this->frequenciaParaDias($frequencia);
                $proximaExecucao = Carbon::now()->addDays($frequenciaDias)->setTime(8, 0, 0);

                // Criar assinatura
                MonitoramentoAssinatura::create([
                    'user_id' => $user->id,
                    'participante_id' => $participante->id,
                    'plano_id' => $plano->id,
                    'frequencia_dias' => $frequenciaDias,
                    'status' => 'ativo',
                    'proxima_execucao_em' => $proximaExecucao,
                ]);

                $assinaturasCriadas++;
            }

            DB::commit();

            Log::info('Assinatura(s) criada(s)', [
                'user_id' => $user->id,
                'criadas' => $assinaturasCriadas,
                'ja_existentes' => $jaExistentes,
                'plano_id' => $planoId,
                'frequencia' => $frequencia,
            ]);

            if ($assinaturasCriadas === 0 && $jaExistentes > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Já existe assinatura ativa para este(s) participante(s).',
                ], Response::HTTP_CONFLICT);
            }

            return response()->json([
                'success' => true,
                'message' => $assinaturasCriadas . ' assinatura(s) criada(s) com sucesso.',
                'criadas' => $assinaturasCriadas,
                'ja_existentes' => $jaExistentes,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar assinatura', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao criar assinatura. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Pausa uma assinatura de monitoramento.
     */
    public function pausarAssinatura(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $assinatura = MonitoramentoAssinatura::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$assinatura) {
            return response()->json([
                'success' => false,
                'error' => 'Assinatura não encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($assinatura->status !== 'ativo') {
            return response()->json([
                'success' => false,
                'error' => 'Apenas assinaturas ativas podem ser pausadas.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $assinatura->update(['status' => 'pausado']);

        Log::info('Assinatura pausada', [
            'user_id' => $user->id,
            'assinatura_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assinatura pausada com sucesso.',
        ]);
    }

    /**
     * Reativa uma assinatura pausada.
     */
    public function reativarAssinatura(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $assinatura = MonitoramentoAssinatura::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$assinatura) {
            return response()->json([
                'success' => false,
                'error' => 'Assinatura não encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($assinatura->status !== 'pausado') {
            return response()->json([
                'success' => false,
                'error' => 'Apenas assinaturas pausadas podem ser reativadas.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Recalcular próxima execução baseado em frequencia_dias
        $proximaExecucao = Carbon::now()->addDays($assinatura->frequencia_dias)->setTime(8, 0, 0);

        $assinatura->update([
            'status' => 'ativo',
            'proxima_execucao_em' => $proximaExecucao,
        ]);

        Log::info('Assinatura reativada', [
            'user_id' => $user->id,
            'assinatura_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assinatura reativada com sucesso.',
        ]);
    }

    /**
     * Cancela uma assinatura de monitoramento.
     */
    public function cancelarAssinatura(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $assinatura = MonitoramentoAssinatura::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$assinatura) {
            return response()->json([
                'success' => false,
                'error' => 'Assinatura não encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($assinatura->status === 'cancelado') {
            return response()->json([
                'success' => false,
                'error' => 'Assinatura já foi cancelada.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $assinatura->update([
            'status' => 'cancelado',
        ]);

        Log::info('Assinatura cancelada', [
            'user_id' => $user->id,
            'assinatura_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assinatura cancelada com sucesso.',
        ]);
    }

    /**
     * Converte frequência textual para número de dias.
     */
    private function frequenciaParaDias(string $frequencia): int
    {
        return match ($frequencia) {
            'diario' => 1,
            'semanal' => 7,
            'quinzenal' => 15,
            'mensal' => 30,
            default => 15,
        };
    }

    /**
     * Executa consulta avulsa.
     */
    public function executarConsultaAvulsa(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $cnpjsInput = $request->input('cnpjs', '');
        $planoId = $request->input('plano_id');

        // Aceita string separada por vírgula, quebra de linha ou array
        if (is_string($cnpjsInput)) {
            $cnpjs = preg_split('/[,;\n\r]+/', $cnpjsInput);
        } else {
            $cnpjs = $cnpjsInput;
        }

        $cnpjs = array_filter(array_map(function ($cnpj) {
            return preg_replace('/[^0-9]/', '', trim($cnpj));
        }, $cnpjs), function ($cnpj) {
            return strlen($cnpj) === 14;
        });

        if (empty($cnpjs) || empty($planoId)) {
            return response()->json([
                'success' => false,
                'message' => 'Dados incompletos. Informe CNPJs válidos e selecione um plano.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Buscar plano no banco
        $plano = MonitoramentoPlano::find($planoId);

        if (!$plano || !$plano->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Plano não encontrado ou inativo.',
            ], Response::HTTP_NOT_FOUND);
        }

        $totalCreditos = count($cnpjs) * $plano->custo_creditos;
        $saldoAtual = $this->creditService->getBalance($user);

        if ($totalCreditos > 0 && !$this->creditService->hasEnough($user, $totalCreditos)) {
            return response()->json([
                'success' => false,
                'insufficient_credits' => true,
                'credits' => $saldoAtual,
                'required' => $totalCreditos,
                'message' => 'Créditos insuficientes. Você precisa de ' . $totalCreditos . ' créditos.',
            ], Response::HTTP_PAYMENT_REQUIRED);
        }

        try {
            DB::beginTransaction();

            $consultasCriadas = [];

            foreach ($cnpjs as $cnpj) {
                // Criar ou buscar participante
                $participante = Participante::firstOrCreate(
                    ['user_id' => $user->id, 'cnpj' => $cnpj],
                    ['origem_tipo' => 'MANUAL']
                );

                // Criar consulta
                $consulta = MonitoramentoConsulta::create([
                    'user_id' => $user->id,
                    'participante_id' => $participante->id,
                    'plano_id' => $plano->id,
                    'tipo' => 'avulso',
                    'status' => 'pendente',
                    'creditos_cobrados' => $plano->custo_creditos,
                ]);

                $consultasCriadas[] = $consulta->id;
            }

            // Descontar créditos
            if ($totalCreditos > 0) {
                $this->creditService->deduct($user, $totalCreditos);
            }

            DB::commit();

            Log::info('Consulta avulsa criada', [
                'user_id' => $user->id,
                'total_cnpjs' => count($cnpjs),
                'plano_id' => $planoId,
                'total_creditos' => $totalCreditos,
                'consultas' => $consultasCriadas,
            ]);

            // TODO: Enviar para n8n webhook para processamento
            // Por enquanto, apenas criamos os registros

            return response()->json([
                'success' => true,
                'message' => 'Consulta iniciada para ' . count($cnpjs) . ' CNPJ(s). Os resultados serão processados em breve.',
                'creditos_cobrados' => $totalCreditos,
                'saldo_restante' => $this->creditService->getBalance($user),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar consulta avulsa', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar consulta. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Lista grupos de participantes.
     */
    public function grupos(Request $request)
    {
        $gruposView = self::AUTH_VIEW_PREFIX . 'grupos';

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        // Buscar grupos do usuário com contagem de participantes
        $grupos = ParticipanteGrupo::where('user_id', $userId)
            ->withCount('participantes')
            ->orderBy('nome')
            ->get();

        $data = [
            'grupos' => $grupos,
            'coresPredefinidas' => ParticipanteGrupo::CORES_PREDEFINIDAS,
            'credits' => $this->creditService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($gruposView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $gruposView
        ], $data));
    }

    /**
     * Cria um novo grupo de participantes.
     */
    public function criarGrupo(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $nome = trim($request->input('nome', ''));
        $cor = $request->input('cor', ParticipanteGrupo::CORES_PREDEFINIDAS[0]);
        $descricao = $request->input('descricao');

        if (empty($nome)) {
            return response()->json([
                'success' => false,
                'error' => 'Nome do grupo é obrigatório.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verificar se já existe grupo com mesmo nome
        $existente = ParticipanteGrupo::where('user_id', $user->id)
            ->where('nome', $nome)
            ->exists();

        if ($existente) {
            return response()->json([
                'success' => false,
                'error' => 'Já existe um grupo com este nome.',
            ], Response::HTTP_CONFLICT);
        }

        try {
            $grupo = ParticipanteGrupo::create([
                'user_id' => $user->id,
                'nome' => $nome,
                'cor' => $cor,
                'descricao' => $descricao,
                'is_auto' => false,
            ]);

            Log::info('Grupo de participantes criado', [
                'user_id' => $user->id,
                'grupo_id' => $grupo->id,
                'nome' => $nome,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Grupo criado com sucesso.',
                'grupo' => [
                    'id' => $grupo->id,
                    'nome' => $grupo->nome,
                    'cor' => $grupo->cor,
                    'descricao' => $grupo->descricao,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar grupo', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao criar grupo. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Edita um grupo de participantes.
     */
    public function editarGrupo(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $grupo = ParticipanteGrupo::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$grupo) {
            return response()->json([
                'success' => false,
                'error' => 'Grupo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $nome = trim($request->input('nome', $grupo->nome));
        $cor = $request->input('cor', $grupo->cor);
        $descricao = $request->input('descricao', $grupo->descricao);

        if (empty($nome)) {
            return response()->json([
                'success' => false,
                'error' => 'Nome do grupo é obrigatório.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verificar se já existe outro grupo com mesmo nome
        $existente = ParticipanteGrupo::where('user_id', $user->id)
            ->where('nome', $nome)
            ->where('id', '!=', $id)
            ->exists();

        if ($existente) {
            return response()->json([
                'success' => false,
                'error' => 'Já existe outro grupo com este nome.',
            ], Response::HTTP_CONFLICT);
        }

        try {
            $grupo->update([
                'nome' => $nome,
                'cor' => $cor,
                'descricao' => $descricao,
            ]);

            Log::info('Grupo de participantes editado', [
                'user_id' => $user->id,
                'grupo_id' => $grupo->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Grupo atualizado com sucesso.',
                'grupo' => [
                    'id' => $grupo->id,
                    'nome' => $grupo->nome,
                    'cor' => $grupo->cor,
                    'descricao' => $grupo->descricao,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao editar grupo', [
                'user_id' => $user->id,
                'grupo_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao atualizar grupo. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exclui um grupo de participantes.
     */
    public function excluirGrupo(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $grupo = ParticipanteGrupo::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$grupo) {
            return response()->json([
                'success' => false,
                'error' => 'Grupo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $nome = $grupo->nome;
            $grupo->delete();

            Log::info('Grupo de participantes excluído', [
                'user_id' => $user->id,
                'grupo_id' => $id,
                'nome' => $nome,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Grupo excluído com sucesso.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir grupo', [
                'user_id' => $user->id,
                'grupo_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao excluir grupo. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Associa participantes a um grupo.
     */
    public function associarGrupo(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $grupoId = $request->input('grupo_id');
        $participanteIds = $request->input('participantes', []);
        $acao = $request->input('acao', 'adicionar'); // adicionar ou remover

        if (empty($grupoId) || empty($participanteIds)) {
            return response()->json([
                'success' => false,
                'error' => 'Selecione um grupo e participantes.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $grupo = ParticipanteGrupo::where('id', $grupoId)
            ->where('user_id', $user->id)
            ->first();

        if (!$grupo) {
            return response()->json([
                'success' => false,
                'error' => 'Grupo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Filtrar apenas participantes do usuário
        $participantesValidos = Participante::where('user_id', $user->id)
            ->whereIn('id', $participanteIds)
            ->pluck('id')
            ->toArray();

        if (empty($participantesValidos)) {
            return response()->json([
                'success' => false,
                'error' => 'Nenhum participante válido selecionado.',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            if ($acao === 'remover') {
                $grupo->participantes()->detach($participantesValidos);
                $message = count($participantesValidos) . ' participante(s) removido(s) do grupo.';
            } else {
                $grupo->participantes()->syncWithoutDetaching($participantesValidos);
                $message = count($participantesValidos) . ' participante(s) adicionado(s) ao grupo.';
            }

            Log::info('Participantes associados ao grupo', [
                'user_id' => $user->id,
                'grupo_id' => $grupoId,
                'participantes' => count($participantesValidos),
                'acao' => $acao,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao associar participantes ao grupo', [
                'user_id' => $user->id,
                'grupo_id' => $grupoId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao associar participantes. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Recebe arquivo .txt e envia para n8n processar.
     * Laravel não valida/extrai CNPJs - apenas repassa o arquivo em base64.
     */
    public function importarTxt(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->validate([
            'arquivo' => 'required|file|max:10240', // Máximo 10MB
            'tipo_efd' => 'required|in:EFD Fiscal,EFD Contribuições',
            'cliente_id' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $arquivo = $request->file('arquivo');
        $webhookUrl = config('services.webhook.monitoramento_importacao_txt_url');

        // Validar que o cliente pertence ao usuário (se fornecido)
        $clienteId = $request->input('cliente_id');
        if ($clienteId) {
            $cliente = Cliente::where('id', $clienteId)
                ->where('user_id', $user->id)
                ->first();
            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cliente não encontrado ou não pertence ao usuário.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        if (empty($webhookUrl)) {
            Log::error('Webhook URL para importação .txt não configurada (WEBHOOK_MONITORAMENTO_IMPORTACAO_TXT_URL)');
            return response()->json([
                'success' => false,
                'error' => 'Serviço de importação não configurado. Verifique as variáveis de ambiente.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        try {
            // Enviar arquivo em base64 para n8n (n8n faz toda validação e extração)
            $response = Http::timeout(30)->post($webhookUrl, [
                'user_id' => $user->id,
                'tipo_efd' => $request->tipo_efd,
                'filename' => $arquivo->getClientOriginalName(),
                'file_base64' => base64_encode(file_get_contents($arquivo->path())),
                'progress_url' => url('/api/monitoramento/sped/importacao-txt/progress'),
                'cliente_id' => $clienteId,
            ]);

            if (!$response->successful()) {
                Log::error('Erro ao enviar arquivo para n8n', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $response->json('error') ?? 'Erro ao processar arquivo.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $importacaoId = $response->json('importacao_id');

            Log::info('Arquivo .txt enviado para n8n', [
                'user_id' => $user->id,
                'filename' => $arquivo->getClientOriginalName(),
                'importacao_id' => $importacaoId,
                'cliente_id' => $clienteId,
            ]);

            return response()->json([
                'success' => true,
                'importacao_id' => $importacaoId,
            ]);
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar arquivo para n8n', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro de conexão com o serviço de importação.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * SSE para acompanhar progresso da importação.
     * Lê dados do cache (enviados pelo n8n via API) - não consulta banco.
     */
    public function streamImportacao($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->stream(function () use ($id) {
            $cacheKey = "importacao_progresso_{$id}";
            $tentativas = 0;
            $maxTentativas = 300; // 5 minutos (300 segundos)

            while ($tentativas < $maxTentativas) {
                // Lê dados do cache (n8n envia via API)
                $dados = Cache::get($cacheKey);

                if (!$dados) {
                    // Ainda não começou ou não existe
                    echo "data: " . json_encode(['status' => 'aguardando']) . "\n\n";
                } else {
                    echo "data: " . json_encode($dados) . "\n\n";

                    if (in_array($dados['status'] ?? '', ['concluido', 'erro'])) {
                        Cache::forget($cacheKey); // Limpa cache
                        break;
                    }
                }

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

                sleep(1);
                $tentativas++;
            }

            // Se chegou no limite, encerra
            if ($tentativas >= $maxTentativas) {
                echo "data: " . json_encode(['status' => 'timeout', 'error' => 'Tempo limite atingido']) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Redireciona para login.
     */
    private function redirectToLogin(Request $request)
    {
        if ($this->isAjaxRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não está logado',
                'redirect' => '/login'
            ]);
        }
        return redirect('/login');
    }

    /**
     * Verifica se a requisição é AJAX.
     */
    private function isAjaxRequest(Request $request): bool
    {
        if (method_exists($request, 'ajax')) {
            return $request->ajax();
        }

        $xRequestedWith = $request->header('X-Requested-With');
        $wantsJson = $request->wantsJson();
        $expectsJson = $request->expectsJson();

        return $wantsJson
            || $expectsJson
            || $xRequestedWith === 'XMLHttpRequest';
    }
}
