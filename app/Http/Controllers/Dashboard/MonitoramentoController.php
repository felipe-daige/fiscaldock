<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoConsulta;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\RafRelatorioProcessado;
use App\Services\CreditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // Buscar participantes com suas assinaturas
        $participantes = Participante::where('user_id', $userId)
            ->with(['assinaturas.plano', 'consultas' => function ($query) {
                $query->latest('executado_em')->limit(1);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $data = [
            'stats' => $stats,
            'planos' => MonitoramentoPlano::ativos(),
            'participantes' => $participantes,
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

        $data = [
            'relatorios' => $relatorios,
            'credits' => $this->creditService->getBalance($user),
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

        $data = [
            'planos' => MonitoramentoPlano::ativos(),
            'credits' => $this->creditService->getBalance($user),
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
        // TODO: Criar view participante.blade.php
        // Por enquanto, redirecionar para index
        return redirect()->route('app.monitoramento.index');
    }

    /**
     * Detalhes de uma consulta específica.
     */
    public function consultaDetalhes(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // TODO: Implementar quando o banco estiver pronto
        return response()->json([
            'success' => false,
            'message' => 'Consulta não encontrada.',
        ], Response::HTTP_NOT_FOUND);
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
     * Cria assinatura de monitoramento para participantes.
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
        $participanteIds = $request->input('participantes', []);
        $planoId = $request->input('plano_id');

        if (empty($participanteIds) || empty($planoId)) {
            return response()->json([
                'success' => false,
                'message' => 'Dados incompletos. Selecione participantes e um plano.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // TODO: Implementar quando o banco estiver pronto
        Log::info('Criação de assinatura solicitada', [
            'user_id' => $user->id,
            'participantes' => count($participanteIds),
            'plano_id' => $planoId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Funcionalidade em desenvolvimento. Assinatura será criada para ' . count($participanteIds) . ' participantes.',
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
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        // TODO: Implementar quando o banco estiver pronto
        Log::info('Cancelamento de assinatura solicitado', [
            'user_id' => $user->id,
            'assinatura_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Funcionalidade em desenvolvimento. Assinatura será cancelada.',
        ]);
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
