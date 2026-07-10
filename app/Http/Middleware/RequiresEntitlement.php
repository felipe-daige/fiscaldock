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

        if ($user === null) {
            abort(403, 'Seu plano não inclui este recurso.');
        }

        // Export SEM formato = PDF: universal. Free recebe com marca d'água (aplicada no
        // reports.layout via composer) — vira canal de aquisição, não parede. CSV/XLSX
        // seguem gated pelo formato abaixo.
        if ($capability === 'export' && $formato === null) {
            return $next($request);
        }

        if (! $this->entitlements->permits($user, $capability)) {
            abort(403, 'Seu plano não inclui este recurso.');
        }

        // Gate fino de export por formato: ':export,excel' exige 'excel' na lista do plano.
        if ($capability === 'export' && $formato !== null && ! $this->entitlements->permitsExportFormat($user, $formato)) {
            abort(403, 'Seu plano não inclui exportação neste formato.');
        }

        return $next($request);
    }
}
