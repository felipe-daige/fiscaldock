<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ImpersonacaoReadOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->session()->has('impersonator_id')
            && ! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)
            && $request->route()?->getName() !== 'app.admin.impersonar.sair'
        ) {
            abort(403, 'Modo leitura: ações desativadas durante impersonação.');
        }

        return $next($request);
    }
}
