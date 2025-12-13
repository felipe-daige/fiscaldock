<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingPage extends Controller
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

    public function beneficios(Request $request){
        return $this->renderLanding($request, 'beneficios');
    }

    public function impactos(Request $request){
        return $this->renderLanding($request, 'impactos');
    }

    public function faq(Request $request){
        return $this->renderLanding($request, 'faq');
    }

    public function questionario(Request $request){
        return $this->renderLanding($request, 'questionario');
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

        return view("landing_page.layout", [
            'initialView' => $viewName,
            'themeClass' => $this->themeClass
        ]);
    }
}
