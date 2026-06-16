<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe rotas ao operador FiscalDock (users.is_admin = true).
 *
 * Aplicado por FQCN nas rotas (bootstrap/app.php não é montado — mesma razão do
 * RequiresEntitlement). Visitante → login; autenticado sem flag → 403.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->guest(route('login'));
        }

        if (! Auth::user()->is_admin) {
            abort(403, 'Acesso restrito ao operador FiscalDock.');
        }

        return $next($request);
    }
}
