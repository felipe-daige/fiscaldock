<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingPage extends Controller
{
    public function inicio(Request $request){
        if(!view()->exists("landing_page.inicio")){
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
            return view("landing_page.inicio");
        }
        return view("landing_page.layout", ['initialView' => 'inicio']);
    }
    public function solucoes(Request $request){
        if(!view()->exists("landing_page.solucoes")){
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
            return view("landing_page.solucoes");
        }
        return view("landing_page.layout", ['initialView' => 'solucoes']);
    }
    public function beneficios(Request $request){
        if(!view()->exists("landing_page.beneficios")){
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
            return view("landing_page.beneficios");
        }
        return view("landing_page.layout", ['initialView' => 'beneficios']);
    }
    public function impactos(Request $request){
        if(!view()->exists("landing_page.impactos")){
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
            return view("landing_page.impactos");
        }
        return view("landing_page.layout", ['initialView' => 'impactos']);
    }
    public function faq(Request $request){
        if(!view()->exists("landing_page.faq")){
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
            return view("landing_page.faq");
        }
        return view("landing_page.layout", ['initialView' => 'faq']);
    }

    public function questionario(Request $request){
        if(!view()->exists("landing_page.questionario")){
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
            return view("landing_page.questionario");
        }
        return view("landing_page.layout", ['initialView' => 'questionario']);
    }
}
