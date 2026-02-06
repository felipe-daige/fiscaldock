<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingPageController extends Controller
{
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

