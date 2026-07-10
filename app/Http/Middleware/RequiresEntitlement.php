<?php

namespace App\Http\Middleware;

use App\Services\Entitlements\EntitlementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresEntitlement
{
    public function __construct(private EntitlementService $entitlements) {}

    public function handle(Request $request, Closure $next, string $capability, ?string $formato = null): Response
    {
        $user = $request->user();

        if ($user === null || ! $this->entitlements->permits($user, $capability)) {
            abort(403, 'Seu plano não inclui este recurso.');
        }

        // Gate fino de export por formato: ':export,excel' exige 'excel' na lista do plano.
        if ($capability === 'export' && $formato !== null && ! $this->entitlements->permitsExportFormat($user, $formato)) {
            abort(403, 'Seu plano não inclui exportação neste formato.');
        }

        return $next($request);
    }
}
