<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Se a requisição espera JSON (AJAX/API), retornar null
        // Isso faz o Laravel retornar JSON 401 em vez de redirecionar
        if ($request->expectsJson()) {
            return null;
        }
        
        // Para requisições web normais, redirecionar para login
        return route('login');
    }
}


