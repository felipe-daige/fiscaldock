<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    private const AUTH_VIEW_PREFIX = 'autenticado.analytics.';
    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Dashboard principal de Analytics.
     */
    public function index(Request $request)
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $userId = Auth::id();

        // Buscar clientes para filtro
        $clientes = Cliente::where('user_id', $userId)
            ->where('ativo', true)
            ->select('id', 'nome', 'documento')
            ->orderBy('nome')
            ->get();

        // Resumo geral
        $resumo = $this->analyticsService->getResumoGeral($userId);

        $data = [
            'clientes' => $clientes,
            'resumo' => $resumo,
        ];

        return $this->render($request, 'index', $data);
    }

    /**
     * Dados de faturamento (para AJAX).
     */
    public function faturamento(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id();
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        $clienteId = $request->get('cliente_id');

        $faturamento = $this->analyticsService->getFaturamentoPorPeriodo($userId, $dataInicio, $dataFim, $clienteId);
        $topClientes = $this->analyticsService->getTopClientes($userId, 10, $dataInicio, $dataFim, $clienteId);
        $faturamentoPorUf = $this->analyticsService->getFaturamentoPorUf($userId, $dataInicio, $dataFim, $clienteId);

        return response()->json([
            'faturamento_mensal' => $faturamento,
            'top_clientes' => $topClientes,
            'faturamento_por_uf' => $faturamentoPorUf,
        ]);
    }

    /**
     * Dados de compras (para AJAX).
     */
    public function compras(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id();
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        $clienteId = $request->get('cliente_id');

        $topFornecedores = $this->analyticsService->getTopFornecedores($userId, 10, $dataInicio, $dataFim, $clienteId);
        $entradasVsSaidas = $this->analyticsService->getEntradasVsSaidas($userId, $dataInicio, $dataFim, $clienteId);
        $devolucoes = $this->analyticsService->getDevolucoes($userId, $dataInicio, $dataFim, $clienteId);

        return response()->json([
            'top_fornecedores' => $topFornecedores,
            'entradas_vs_saidas' => $entradasVsSaidas,
            'devolucoes' => $devolucoes,
        ]);
    }

    /**
     * Dados de tributos (para AJAX).
     */
    public function tributos(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id();
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        $clienteId = $request->get('cliente_id');

        $cargaTributaria = $this->analyticsService->getCargaTributaria($userId, $dataInicio, $dataFim, $clienteId);
        $tributosPorTipo = $this->analyticsService->getTributosPorTipo($userId, $dataInicio, $dataFim, $clienteId);

        return response()->json([
            'carga_tributaria' => $cargaTributaria,
            'tributos_por_tipo' => $tributosPorTipo,
        ]);
    }

    /**
     * Resumo geral (para AJAX).
     */
    public function resumo(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id();
        $clienteId = $request->get('cliente_id');

        $resumo = $this->analyticsService->getResumoGeral($userId, $clienteId);

        return response()->json($resumo);
    }

    /**
     * Verifica se e requisicao AJAX.
     */
    private function isAjaxRequest(Request $request): bool
    {
        if (method_exists($request, 'ajax') && $request->ajax()) {
            return true;
        }

        return $request->header('X-Requested-With') === 'XMLHttpRequest' ||
               $request->wantsJson() ||
               $request->expectsJson();
    }

    /**
     * Renderiza view com suporte a AJAX.
     */
    private function render(Request $request, string $viewName, array $data = [])
    {
        $view = self::AUTH_VIEW_PREFIX.$viewName;

        if (! view()->exists($view)) {
            abort(404);
        }

        if ($this->isAjaxRequest($request)) {
            return view($view, $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $view,
        ], $data));
    }

    /**
     * Redireciona para login.
     */
    private function redirectToLogin(Request $request)
    {
        if ($this->isAjaxRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Voce nao esta logado',
                'redirect' => '/login',
            ]);
        }

        return redirect('/login');
    }
}
