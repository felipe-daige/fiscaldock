<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosloginController extends Controller
{
    public function dashboard(Request $request){
        if(!view()->exists("autenticado.dashboard")){
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
            return view("autenticado.dashboard");
        }
        return view("autenticado.layout", ['initialView' => 'dashboard']);
    }
}
