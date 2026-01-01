<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\RelatorioCompletoController;
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
        $data = $this->dashboardDataService->getDashboardData($user->id);

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
        return $this->renderAutenticado($request, 'raf');
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

    public function clientes(Request $request){
        return $this->renderAutenticado($request, 'clientes');
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
        
        try {
            $result = $this->spedUploadService->uploadAndProcess(
                $file,
                $validated['tipo'],
                $originalName,
                true, // isAuthenticated
                $validated['modalidade'],
                $userId, // user_id
                $validated['tab_id'] ?? null // tab_id
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
}

