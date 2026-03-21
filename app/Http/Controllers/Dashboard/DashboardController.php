<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\RelatorioCompletoController;
use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\CreditTransaction;
use App\Models\Participante;
use App\Models\XmlNota;
use App\Services\Dashboard\DashboardDataService;
use App\Services\Sped\SpedUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardDataService $dashboardDataService,
        protected SpedUploadService $spedUploadService
    ) {}

    private const AUTH_VIEW_PREFIX = 'autenticado.';
    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function dashboard(Request $request){
        $dashboardView = self::AUTH_VIEW_PREFIX . 'dashboard.index';

        if(!view()->exists($dashboardView)){
            abort(404);
        }

        if(!Auth::check()){
            if($this->isAjaxRequest($request)){
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login'
                ]);
            }
            return redirect('/login');
        }

        $user = Auth::user();
        $userId = $user->id;

        $kpis = $this->dashboardDataService->getKpis($userId, $user);

        $ultimasConsultas = ConsultaLote::where('user_id', $userId)
            ->with('plano:id,nome,codigo')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $data = [
            'kpi_conformidade' => $kpis['conformidade'],
            'kpi_impostos_recuperaveis' => $kpis['impostos_recuperaveis'],
            'kpi_creditos' => $kpis['creditos'],
            'kpi_alertas_criticos' => $kpis['alertas_criticos'],
            'ultimasConsultas' => $ultimasConsultas,
        ];

        if($this->isAjaxRequest($request)){
            return view($dashboardView, $data);
        }

        // Para requisições não-AJAX, passar dados para o layout
        // As variáveis serão automaticamente disponíveis na view incluída
        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $dashboardView
        ], $data));
    }

    /**
     * Verifica se a requisição é AJAX de forma compatível com Laravel 11 e 12
     */
    private function isAjaxRequest(Request $request): bool
    {
        // Verifica se o método ajax() existe (Laravel 11)
        if (method_exists($request, 'ajax') && $request->ajax()) {
            return true;
        }
        
        // Verifica o header X-Requested-With diretamente (compatível com Laravel 12)
        return $request->header('X-Requested-With') === 'XMLHttpRequest' ||
               $request->wantsJson() ||
               $request->expectsJson();
    }

    private function renderAutenticado(Request $request, string $viewName){
        $autenticadoView = self::AUTH_VIEW_PREFIX . $viewName;

        if(!view()->exists($autenticadoView)){
            abort(404);
        }

        if(!Auth::check()){
            if($this->isAjaxRequest($request)){
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login'
                ]);
            }
            return redirect('/login');
        }

        if($this->isAjaxRequest($request)){
            return view($autenticadoView);
        }
        
        return view(self::AUTH_LAYOUT_VIEW, [
            'initialView' => $autenticadoView
        ]);
    }

    public function novoCliente(Request $request){
        return $this->renderAutenticado($request, 'clientes.novo');
    }

    public function clientes(Request $request){
        $autenticadoView = self::AUTH_VIEW_PREFIX . 'clientes.index';

        if(!view()->exists($autenticadoView)){
            abort(404);
        }

        if(!Auth::check()){
            if($this->isAjaxRequest($request)){
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login'
                ]);
            }
            return redirect('/login');
        }

        // Buscar clientes do usuario logado
        $clientes = Cliente::where('user_id', Auth::id())
            ->orderBy('nome')
            ->get();

        // Estatísticas
        $totalAtivos = $clientes->where('ativo', true)->count();
        $totalInativos = $clientes->where('ativo', false)->count();
        $totalPJ = $clientes->where('tipo_pessoa', 'PJ')->count();
        $totalPF = $clientes->where('tipo_pessoa', 'PF')->count();

        $data = [
            'clientes' => $clientes,
            'totalAtivos' => $totalAtivos,
            'totalInativos' => $totalInativos,
            'totalPJ' => $totalPJ,
            'totalPF' => $totalPF,
        ];

        if($this->isAjaxRequest($request)){
            return view($autenticadoView, $data);
        }
        
        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $autenticadoView
        ], $data));
    }

    public function clienteDetalhes(Request $request, int $id)
    {
        if (!Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['success' => false, 'message' => 'Nao autenticado'], 401);
            }
            return redirect('/login');
        }

        $cliente = Cliente::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$cliente) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['success' => false, 'message' => 'Cliente nao encontrado'], 404);
            }
            abort(404);
        }

        // Empresa própria: redirect to /app/minha-empresa
        if ($cliente->is_empresa_propria) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['redirect' => '/app/minha-empresa']);
            }
            return redirect('/app/minha-empresa');
        }

        $totalParticipantes = Participante::where('user_id', Auth::id())
            ->where('cliente_id', $cliente->id)
            ->count();

        $totalNotas = XmlNota::where('user_id', Auth::id())
            ->where('cliente_id', $cliente->id)
            ->count();

        $showView = self::AUTH_VIEW_PREFIX . 'clientes.show';

        if ($this->isAjaxRequest($request)) {
            // Modal requests send Accept: application/json — return JSON for the modal to populate
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'cliente' => [
                        'id' => $cliente->id,
                        'nome' => $cliente->nome,
                        'razao_social' => $cliente->razao_social,
                        'documento_formatado' => $cliente->documento_formatado,
                        'tipo_pessoa' => $cliente->tipo_pessoa,
                        'email' => $cliente->email,
                        'telefone' => $cliente->telefone,
                        'ativo' => $cliente->ativo,
                        'is_empresa_propria' => $cliente->is_empresa_propria,
                        'uf' => $cliente->uf,
                        'cep' => $cliente->cep,
                        'municipio' => $cliente->municipio,
                        'created_at' => $cliente->created_at?->format('d/m/Y H:i'),
                    ],
                    'stats' => [
                        'total_participantes' => $totalParticipantes,
                        'total_notas' => $totalNotas,
                    ],
                ]);
            }
            // SPA navigation sends Accept: text/html — return HTML view
            return view($showView, [
                'cliente' => $cliente,
                'totalParticipantes' => $totalParticipantes,
                'totalNotas' => $totalNotas,
            ]);
        }

        return view(self::AUTH_LAYOUT_VIEW, [
            'initialView' => $showView,
            'cliente' => $cliente,
            'totalParticipantes' => $totalParticipantes,
            'totalNotas' => $totalNotas,
        ]);
    }

    public function perfil(Request $request){
        $perfilView = self::AUTH_VIEW_PREFIX . 'usuario.perfil';

        if(!view()->exists($perfilView)){
            abort(404);
        }

        if(!Auth::check()){
            if($this->isAjaxRequest($request)){
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login'
                ]);
            }
            return redirect('/login');
        }

        $user = Auth::user();

        if($this->isAjaxRequest($request)){
            return view($perfilView, ['user' => $user]);
        }
        
        return view(self::AUTH_LAYOUT_VIEW, [
            'initialView' => $perfilView,
            'user' => $user
        ]);
    }

    /**
     * Upload de SPED e envio ao webhook n8n.
     */
    public function uploadSped(Request $request)
    {
        // Define timeout de 1 hora (3600 segundos) para processamento SPED
        set_time_limit(3600);
        
        try {
            $validated = $request->validate([
                'tipo' => 'required|in:EFD PIS/COFINS,EFD ICMS/IPI',
                'modalidade' => 'required|in:gratuito,completa',
                'sped' => 'required|file|mimes:txt,text/plain|max:10240', // 10 MB
                'tab_id' => 'nullable|string|max:36',
                'cliente_id' => 'nullable|integer|min:0',
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            
            if (isset($errors['sped'])) {
                $errorMessages = array_merge($errorMessages, $errors['sped']);
            }
            if (isset($errors['tipo'])) {
                $errorMessages = array_merge($errorMessages, $errors['tipo']);
            }
            if (isset($errors['modalidade'])) {
                $errorMessages = array_merge($errorMessages, $errors['modalidade']);
            }
            
            $message = !empty($errorMessages) 
                ? implode(', ', $errorMessages) 
                : 'Dados inválidos';
            
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $file = $request->file('sped');
        $originalName = $file->getClientOriginalName();
        
        // Obter user_id do usuário autenticado
        $user = Auth::user();
        $userId = $user ? $user->id : null;
        
        // Obter cliente_id (0 se não fornecido)
        $clienteId = isset($validated['cliente_id']) && $validated['cliente_id'] !== null 
            ? (int) $validated['cliente_id'] 
            : 0;
        
        // Validar se o cliente pertence ao usuário (se cliente_id > 0)
        if ($clienteId > 0) {
            $cliente = Cliente::where('id', $clienteId)
                ->where('user_id', $userId)
                ->where('ativo', true)
                ->first();
            
            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente não encontrado ou não pertence ao usuário.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
        
        try {
            $result = $this->spedUploadService->uploadAndProcess(
                $file,
                $validated['tipo'],
                $originalName,
                true, // isAuthenticated
                $validated['modalidade'],
                $userId, // user_id
                $validated['tab_id'] ?? null, // tab_id
                $clienteId // cliente_id
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parâmetros inválidos: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$result['success']) {
            $statusCode = $result['message'] === 'Webhook não configurado.' 
                ? Response::HTTP_BAD_GATEWAY 
                : Response::HTTP_BAD_GATEWAY;
            
            return response()->json($result, $statusCode);
        }

        return response()->json($result);
    }

    /**
     * Recebe payload do frontend e chama internamente a API para confirmar relatório completo.
     * Retorna dados formatados para o frontend exibir.
     */
    public function confirmarRelatorio(Request $request)
    {
        // Instancia o controller da API e chama o método diretamente
        $apiController = app(RelatorioCompletoController::class);

        // Chama o método da API passando a requisição atual
        return $apiController->confirmarRelatorioCompleto($request);
    }

    /**
     * Renderiza uma página placeholder "Em construção" com dados customizados.
     */
    private function renderPlaceholder(Request $request, string $titulo, string $descricao, string $icone, array $features = [])
    {
        $placeholderView = self::AUTH_VIEW_PREFIX . 'partials.placeholder';

        if (!Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login'
                ]);
            }
            return redirect('/login');
        }

        $data = [
            'titulo' => $titulo,
            'descricao' => $descricao,
            'icone' => $icone,
            'features' => $features,
        ];

        if ($this->isAjaxRequest($request)) {
            return view($placeholderView, $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $placeholderView
        ], $data));
    }

    public function alertas(Request $request)
    {
        return $this->renderPlaceholder($request,
            'Central de Alertas',
            'Gerencie todos os alertas e notificações do sistema.',
            'bell',
            [
                'Alertas de CNDs vencendo',
                'Notificações de consultas',
                'Alertas de risco fiscal',
                'Configurar preferências'
            ]
        );
    }

    // ==================== USUÁRIO ====================

    public function configuracoes(Request $request)
    {
        return $this->renderPlaceholder($request,
            'Configurações',
            'Configure suas preferências e integrações.',
            'cog',
            [
                'Preferências de notificação',
                'Configurar webhooks',
                'Gerenciar integrações',
                'Personalizar interface'
            ]
        );
    }

    public function meuPlano(Request $request)
    {
        if (!Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voce nao esta logado',
                    'redirect' => '/login'
                ]);
            }
            return redirect('/login');
        }

        $user = Auth::user();
        $now = now();
        $mesInicio = $now->copy()->startOfMonth();
        $mesFim = $now->copy()->endOfMonth();

        // KPI 1: Saldo atual
        $saldoAtual = (int) $user->credits;

        // KPI 2: Creditos usados no mes
        $creditosUsadosMes = ConsultaLote::where('user_id', $user->id)
            ->where('status', 'concluido')
            ->whereBetween('created_at', [$mesInicio, $mesFim])
            ->sum('creditos_cobrados');

        // KPI 3: Consultas no mes
        $consultasMes = ConsultaLote::where('user_id', $user->id)
            ->whereBetween('created_at', [$mesInicio, $mesFim])
            ->count();

        // KPI 4: Media creditos/consulta
        $totalConsultas = ConsultaLote::where('user_id', $user->id)
            ->where('status', 'concluido')
            ->count();
        $totalCreditosHistorico = ConsultaLote::where('user_id', $user->id)
            ->where('status', 'concluido')
            ->sum('creditos_cobrados');
        $mediaCreditos = $totalConsultas > 0 ? round($totalCreditosHistorico / $totalConsultas, 1) : 0;

        // Ultimas 20 transacoes (consulta_lotes como fallback)
        $ultimasTransacoes = ConsultaLote::where('user_id', $user->id)
            ->with('plano:id,nome,codigo')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Consumo mensal ultimos 6 meses
        $consumoMensal = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = $now->copy()->subMonths($i);
            $consumo = ConsultaLote::where('user_id', $user->id)
                ->where('status', 'concluido')
                ->whereYear('created_at', $mes->year)
                ->whereMonth('created_at', $mes->month)
                ->sum('creditos_cobrados');
            $consumoMensal[] = [
                'label' => $mes->translatedFormat('M/y'),
                'valor' => (int) $consumo,
            ];
        }

        $maxConsumo = max(array_column($consumoMensal, 'valor') ?: [1]);

        $planoView = self::AUTH_VIEW_PREFIX . 'plano.index';

        $data = [
            'saldoAtual' => $saldoAtual,
            'creditosUsadosMes' => (int) $creditosUsadosMes,
            'consultasMes' => $consultasMes,
            'mediaCreditos' => $mediaCreditos,
            'ultimasTransacoes' => $ultimasTransacoes,
            'consumoMensal' => $consumoMensal,
            'maxConsumo' => $maxConsumo,
        ];

        if ($this->isAjaxRequest($request)) {
            return view($planoView, $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $planoView
        ], $data));
    }

    public function creditos(Request $request)
    {
        if (!Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voce nao esta logado',
                    'redirect' => '/login'
                ]);
            }
            return redirect('/login');
        }

        $user = Auth::user();

        $saldoAtual = (int) $user->credits;

        $totalComprado = (int) CreditTransaction::where('user_id', $user->id)
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalConsumido = (int) abs(CreditTransaction::where('user_id', $user->id)
            ->where('amount', '<', 0)
            ->sum('amount'));

        $ultimaCompra = CreditTransaction::where('user_id', $user->id)
            ->where('amount', '>', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        $historicoCompras = CreditTransaction::where('user_id', $user->id)
            ->where('amount', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        $pacotes = [
            ['slug' => 'starter', 'nome' => 'Starter', 'creditos' => 100, 'preco' => 26.00, 'desconto' => null],
            ['slug' => 'growth', 'nome' => 'Growth', 'creditos' => 500, 'preco' => 117.00, 'desconto' => 10],
            ['slug' => 'business', 'nome' => 'Business', 'creditos' => 1000, 'preco' => 208.00, 'desconto' => 20],
            ['slug' => 'enterprise', 'nome' => 'Enterprise', 'creditos' => 5000, 'preco' => 910.00, 'desconto' => 30],
        ];

        $creditosView = self::AUTH_VIEW_PREFIX . 'creditos.index';

        $data = [
            'saldoAtual' => $saldoAtual,
            'totalComprado' => $totalComprado,
            'totalConsumido' => $totalConsumido,
            'ultimaCompra' => $ultimaCompra,
            'historicoCompras' => $historicoCompras,
            'pacotes' => $pacotes,
        ];

        if ($this->isAjaxRequest($request)) {
            return view($creditosView, $data);
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $creditosView
        ], $data));
    }

    public function checkout(Request $request, string $pacote)
    {
        $pacotes = [
            'starter' => ['nome' => 'Starter', 'creditos' => 100, 'preco' => 26.00, 'desconto' => null],
            'growth' => ['nome' => 'Growth', 'creditos' => 500, 'preco' => 117.00, 'desconto' => 10],
            'business' => ['nome' => 'Business', 'creditos' => 1000, 'preco' => 208.00, 'desconto' => 20],
            'enterprise' => ['nome' => 'Enterprise', 'creditos' => 5000, 'preco' => 910.00, 'desconto' => 30],
        ];

        if (!isset($pacotes[$pacote])) {
            return redirect()->route('app.plano');
        }

        $dados = $pacotes[$pacote];
        $dados['slug'] = $pacote;

        $checkoutView = self::AUTH_VIEW_PREFIX . 'plano.checkout';

        if ($this->isAjaxRequest($request)) {
            return view($checkoutView, ['pacote' => $dados]);
        }

        return view(self::AUTH_LAYOUT_VIEW, [
            'initialView' => $checkoutView,
            'pacote' => $dados,
        ]);
    }

    public function scoreFiscalPlaceholder(Request $request)
    {
        return $this->renderPlaceholder($request,
            'Score Fiscal',
            'Avaliação de risco fiscal e compliance de participantes.',
            'document-check',
            [
                'Score de risco ponderado por categoria',
                'Classificação automática (baixo a crítico)',
                'Consulta em lote de participantes',
                'Monitoramento contínuo de CNDs'
            ]
        );
    }

    public function validacaoPlaceholder(Request $request)
    {
        return $this->renderPlaceholder($request,
            'Validação Contábil',
            'Análise e validação inteligente de notas fiscais.',
            'calculator',
            [
                'Validação automática de notas fiscais',
                'Alertas por nível (bloqueante, atenção, info)',
                'Análise de CFOP, CST e NCM',
                'Score de conformidade por nota'
            ]
        );
    }

    public function biPlaceholder(Request $request)
    {
        return $this->renderPlaceholder($request,
            'BI Fiscal',
            'Dashboard gerencial para análise de faturamento, compras e tributos.',
            'chart',
            [
                'Faturamento por período e cliente',
                'Análise de compras e fornecedores',
                'Carga tributária efetiva',
                'Top 10 clientes e fornecedores'
            ]
        );
    }
}

