<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Sped\SpedUploadService;

class LandingPageController extends Controller
{
    public function __construct(
        protected SpedUploadService $spedUploadService
    ) {}
    /**
     * Tema padrão usado nas páginas públicas.
     */
    protected string $themeClass = 'theme-default';

    public function inicio(Request $request){
        return $this->renderLanding($request, 'inicio');
    }

    public function solucoes(Request $request){
        return $this->renderLanding($request, 'solucoes');
    }

    public function sobre(Request $request){
        return $this->renderLanding($request, 'sobre');
    }

    public function beneficios(Request $request){
        return $this->renderLanding($request, 'beneficios');
    }

    public function impactos(Request $request){
        return $this->renderLanding($request, 'impactos');
    }

    public function faq(Request $request){
        return $this->renderLanding($request, 'faq');
    }

    public function precos(Request $request){
        return $this->renderLanding($request, 'precos');
    }

    public function questionario(Request $request){
        return $this->renderLanding($request, 'questionario');
    }

    public function importacaoXml(Request $request){
        return $this->renderLanding($request, 'importacao_xml');
    }

    public function conciliacaoBancaria(Request $request){
        return $this->renderLanding($request, 'conciliacao_bancaria');
    }

    public function gestaoCnds(Request $request){
        return $this->renderLanding($request, 'gestao_cnds');
    }

    public function inteligenciaTributaria(Request $request){
        return $this->renderLanding($request, 'inteligencia_tributaria');
    }

    public function raf(Request $request){
        return $this->renderLanding($request, 'raf');
    }

    /**
     * Endpoint público para upload de SPED (EFD Contribuições) que encaminha
     * o arquivo ao webhook e retorna JSON para renderização da tabela/CSV.
     */
    public function uploadSpedPublic(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipo' => 'required|in:EFD Contribuições,EFD Fiscal',
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
        
        $result = $this->spedUploadService->uploadAndProcess(
            $file,
            $validated['tipo'],
            $originalName,
            false // isAuthenticated = false para endpoint público
        );

        if (!$result['success']) {
            $statusCode = $result['message'] === 'Webhook não configurado.' 
                ? Response::HTTP_BAD_GATEWAY 
                : (str_contains($result['message'] ?? '', 'erro') 
                    ? Response::HTTP_BAD_GATEWAY 
                    : Response::HTTP_BAD_GATEWAY);
            
            // Se o resultado contém status code, usar ele
            if (isset($result['status'])) {
                $statusCode = $result['status'];
            }
            
            return response()->json($result, $statusCode);
        }

        return response()->json($result);
    }

    /**
     * Renderiza uma view da landing page aplicando o tema padrão e redirecionando
     * usuários autenticados para o dashboard.
     */
    private function renderLanding(Request $request, string $viewName){
        // Todas as views da landing page agora têm sufixo _public
        $actualViewName = $viewName . '_public';
        $fullViewName = "landing_page.$actualViewName";

        if(!view()->exists($fullViewName)){
            abort(404);
        }

        if(Auth::check()){
            if($request->ajax()){
                return response()->json([
                    'success' => true,
                    'message' => 'Você já está logado',
                    'redirect' => '/dashboard'
                ]);
            }

            return redirect('/dashboard');
        }

        if($request->ajax()){
            return view($fullViewName);
        }

        return view("landing_page.layout_public", [
            'initialView' => $actualViewName,
            'themeClass' => $this->themeClass
        ]);
    }
}

