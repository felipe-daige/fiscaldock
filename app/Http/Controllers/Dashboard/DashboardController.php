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
            if($request->ajax()){
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

        if($request->ajax()){
            return view($dashboardView, $data);
        }
        
        // Para requisições não-AJAX, passar dados para o layout
        // As variáveis serão automaticamente disponíveis na view incluída
        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $dashboardView
        ], $data));
    }

    private function renderAutenticado(Request $request, string $viewName){
        $autenticadoView = self::AUTH_VIEW_PREFIX . $viewName;

        if(!view()->exists($autenticadoView)){
            abort(404);
        }

        if(!Auth::check()){
            if($request->ajax()){
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login'
                ]);
            }
            return redirect('/login');
        }

        if($request->ajax()){
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

    public function perfil(Request $request){
        $perfilView = self::AUTH_VIEW_PREFIX . 'perfil';

        if(!view()->exists($perfilView)){
            abort(404);
        }

        if(!Auth::check()){
            if($request->ajax()){
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login'
                ]);
            }
            return redirect('/login');
        }

        $user = Auth::user();

        if($request->ajax()){
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
                'modalidade' => 'required|in:regime,completa',
                'sped' => 'required|file|mimes:txt,text/plain|max:10240', // 10 MB
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
                $userId // user_id
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

