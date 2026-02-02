<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\RelatorioCompletoController;
use App\Models\Cliente;
use App\Models\ConsultaLote;
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
        $dashboardView = self::AUTH_VIEW_PREFIX . 'dashboard';

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

    public function solucoes(Request $request){
        return $this->renderAutenticado($request, 'solucoes');
    }

    public function importacaoXml(Request $request){
        return $this->renderAutenticado($request, 'importacao_xml');
    }

    public function conciliacaoBancaria(Request $request){
        return $this->renderAutenticado($request, 'conciliacao_bancaria');
    }

    public function gestaoCnds(Request $request){
        return $this->renderAutenticado($request, 'gestao_cnds');
    }

    public function inteligenciaTributaria(Request $request){
        return $this->renderAutenticado($request, 'inteligencia_tributaria');
    }

    public function raf(Request $request){
        $autenticadoView = self::AUTH_VIEW_PREFIX . 'raf';

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

        // Buscar clientes do usuário logado (ativos)
        $clientes = Cliente::where('user_id', Auth::id())
            ->where('ativo', true)
            ->select('id', 'nome', 'razao_social', 'documento', 'tipo_pessoa')
            ->orderBy('nome')
            ->get();

        if($this->isAjaxRequest($request)){
            return view($autenticadoView, ['clientes' => $clientes]);
        }
        
        return view(self::AUTH_LAYOUT_VIEW, [
            'initialView' => $autenticadoView,
            'clientes' => $clientes
        ]);
    }

    public function spedImportar(Request $request){
        return $this->renderAutenticado($request, 'sped_importar');
    }

    public function spedAnaliseRisco(Request $request){
        return $this->renderAutenticado($request, 'sped_analise_risco');
    }

    public function validarXml(Request $request){
        return $this->renderAutenticado($request, 'validar_xml');
    }

    public function xmlAnaliseRisco(Request $request){
        return $this->renderAutenticado($request, 'xml_analise_risco');
    }

    public function novoCliente(Request $request){
        return $this->renderAutenticado($request, 'novo_cliente');
    }

    public function consultarCliente(Request $request){
        $autenticadoView = self::AUTH_VIEW_PREFIX . 'consultar_cliente';

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

        // Buscar clientes PJ do usuário logado
        $clientes = Cliente::where('user_id', Auth::id())
            ->where('tipo_pessoa', 'PJ')
            ->where('ativo', true)
            ->select('id', 'nome', 'documento')
            ->orderBy('nome')
            ->get();

        if($this->isAjaxRequest($request)){
            return view($autenticadoView, ['clientes' => $clientes]);
        }
        
        return view(self::AUTH_LAYOUT_VIEW, [
            'initialView' => $autenticadoView,
            'clientes' => $clientes
        ]);
    }

    public function clientes(Request $request){
        $autenticadoView = self::AUTH_VIEW_PREFIX . 'clientes';

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

        // Buscar clientes do usuário logado
        $clientes = Cliente::where('user_id', Auth::id())
            ->with('endereco')
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

    public function consultarInscricaoEstadual(Request $request){
        return $this->renderAutenticado($request, 'consultar_inscricao_estadual');
    }

    public function consultarListasRestritivas(Request $request){
        return $this->renderAutenticado($request, 'consultar_listas_restritivas');
    }

    public function consultarCnpj(Request $request){
        return $this->renderAutenticado($request, 'consultar_cnpj');
    }

    public function perfil(Request $request){
        $perfilView = self::AUTH_VIEW_PREFIX . 'perfil';

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
                'tipo' => 'required|in:EFD Contribuições,EFD Fiscal',
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
        $placeholderView = self::AUTH_VIEW_PREFIX . 'placeholder';

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

    // ==================== CERTIDÕES ====================

    public function certidoes(Request $request)
    {
        return $this->renderPlaceholder($request,
            'Painel de CNDs',
            'Gerencie todas as suas certidões negativas em um só lugar.',
            'document-check',
            [
                'Visualizar status de todas as CNDs',
                'Acompanhar vencimentos',
                'Receber alertas automáticos',
                'Histórico de emissões'
            ]
        );
    }

    public function certidoesEmitir(Request $request)
    {
        return $this->renderPlaceholder($request,
            'Emitir Certidão Avulsa',
            'Emita certidões negativas individualmente.',
            'upload',
            [
                'Emitir CND Federal',
                'Emitir CND Estadual',
                'Emitir CND Municipal',
                'Emitir FGTS e CNDT'
            ]
        );
    }

    public function certidoesLicitacao(Request $request)
    {
        return $this->renderPlaceholder($request,
            'Kit Licitação',
            'Gere o pacote completo de certidões para licitações.',
            'package',
            [
                'Gerar todas as certidões em lote',
                'Download em ZIP organizado',
                'Verificação automática de validade',
                'Relatório de conformidade'
            ]
        );
    }

    // ==================== CONSULTAS ====================

    public function consultarCpf(Request $request)
    {
        return $this->renderPlaceholder($request,
            'Consultar CPF',
            'Consulte informações de pessoas físicas.',
            'user',
            [
                'Verificar situação cadastral',
                'Consultar restrições',
                'Histórico de consultas',
                'Exportar relatório'
            ]
        );
    }

    public function consultarSimples(Request $request)
    {
        return $this->renderPlaceholder($request,
            'Simples Nacional',
            'Consulte informações do Simples Nacional.',
            'calculator',
            [
                'Verificar opção pelo Simples',
                'Consultar data de opção/exclusão',
                'Histórico de alterações',
                'Alertas de desenquadramento'
            ]
        );
    }

    // ==================== NOTAS FISCAIS ====================

    public function notasHistorico(Request $request)
    {
        return $this->renderPlaceholder($request,
            'Histórico de Notas',
            'Visualize o histórico de notas fiscais processadas.',
            'clock',
            [
                'Buscar notas por período',
                'Filtrar por tipo (NF-e, NFS-e, CT-e)',
                'Exportar relatórios',
                'Análise de tendências'
            ]
        );
    }

    // ==================== RELATÓRIOS ====================

    public function relatoriosDiagnostico(Request $request)
    {
        return $this->renderPlaceholder($request,
            'Diagnóstico Fiscal',
            'Análise completa da situação fiscal dos seus clientes.',
            'chart',
            [
                'Diagnóstico fiscal completo',
                'Identificação de riscos',
                'Recomendações de ação',
                'Comparativo histórico'
            ]
        );
    }

    public function relatoriosExportar(Request $request)
    {
        return $this->renderPlaceholder($request,
            'Exportar Dados',
            'Exporte dados e relatórios em diversos formatos.',
            'download',
            [
                'Exportar para Excel',
                'Exportar para PDF',
                'Exportar para CSV',
                'Agendamento de relatórios'
            ]
        );
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
        return $this->renderPlaceholder($request,
            'Meu Plano',
            'Gerencie seu plano e créditos.',
            'credit-card',
            [
                'Ver saldo de créditos',
                'Histórico de consumo',
                'Upgrade de plano',
                'Comprar créditos adicionais'
            ]
        );
    }
}

