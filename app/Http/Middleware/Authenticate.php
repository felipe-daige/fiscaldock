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
        // Sempre retorna a rota de login para garantir redirecionamento
        // O JavaScript do SPA intercepta status 401/419 e redireciona
        return route('login');
    }
}


