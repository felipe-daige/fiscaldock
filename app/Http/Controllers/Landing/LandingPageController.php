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
        return $this->renderLanding($request, 'paginas.inicio');
    }

    public function solucoes(Request $request){
        return $this->renderLanding($request, 'solucoes.index');
    }

    public function sobre(Request $request){
        return $this->renderLanding($request, 'paginas.sobre');
    }

    public function beneficios(Request $request){
        return $this->renderLanding($request, 'paginas.beneficios');
    }

    public function impactos(Request $request){
        return $this->renderLanding($request, 'paginas.impactos');
    }

    public function faq(Request $request){
        return $this->renderLanding($request, 'paginas.faq');
    }

    public function precos(Request $request){
        return $this->renderLanding($request, 'paginas.precos');
    }

    public function questionario(Request $request){
        return $this->renderLanding($request, 'paginas.questionario');
    }

    public function importacaoXml(Request $request){
        return $this->renderLanding($request, 'solucoes.importacao_xml');
    }

    public function conciliacaoBancaria(Request $request){
        return $this->renderLanding($request, 'solucoes.conciliacao_bancaria');
    }

    public function gestaoCnds(Request $request){
        return $this->renderLanding($request, 'solucoes.gestao_cnds');
    }

    public function inteligenciaTributaria(Request $request){
        return $this->renderLanding($request, 'solucoes.inteligencia_tributaria');
    }

    /**
     * Renderiza uma view da landing page aplicando o tema padrão e redirecionando
     * usuários autenticados para o dashboard.
     */
    private function renderLanding(Request $request, string $viewName){
        $fullViewName = "landing_page.$viewName";

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

        return view("landing_page.layouts.public", [
            'initialView' => $viewName,
            'themeClass' => $this->themeClass
        ]);
    }
}

