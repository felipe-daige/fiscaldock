<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureNaoBloqueado
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->bloqueado_em !== null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Sua conta está temporariamente suspensa. Fale com o suporte pelo WhatsApp para regularizar o acesso.']);
        }

        return $next($request);
    }
}
