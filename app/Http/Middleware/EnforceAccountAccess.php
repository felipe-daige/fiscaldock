<?php

namespace App\Http\Middleware;

use App\Support\AccountContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceAccountAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('app/*') || ! app()->bound(AccountContext::class)) {
            return $next($request);
        }

        $context = app(AccountContext::class);
        $name = (string) optional($request->route())->getName();

        // O painel interno FiscalDock possui autorização própria pelo ator real.
        if (str_starts_with($name, 'app.admin.')) {
            return $next($request);
        }

        if ($this->isBillingRoute($name) && ! $context->isOwner()) {
            return $this->deny($request, 'Somente o dono da conta pode acessar cobrança, saldo e assinatura.');
        }

        if (str_starts_with($name, 'app.equipe.') && ! $context->canManageTeam()) {
            return $this->deny($request, 'Seu papel não permite gerenciar a equipe.');
        }

        if (str_starts_with($name, 'app.minha-empresa.') && ! $context->canManageTeam()) {
            return $this->deny($request, 'Seu papel não permite alterar os dados ou certificado da conta.');
        }

        $module = $this->moduleForRoute($name);
        if ($module !== null && ! $context->permits($module)) {
            return $this->deny($request, 'Você não tem permissão para acessar este módulo.');
        }

        if ($context->isReadOnly() && ! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $this->deny($request, 'Seu acesso é somente para leitura.');
        }

        return $next($request);
    }

    private function isBillingRoute(string $name): bool
    {
        foreach ([
            'app.planos', 'app.faixa-comercial', 'app.checkout', 'app.saldo',
            'app.pagamento.', 'app.assinatura.', 'app.recarga.',
        ] as $prefix) {
            if ($name === $prefix || str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function moduleForRoute(string $name): ?string
    {
        $map = [
            'painel' => ['dashboard', 'app.dashboard', 'app.alertas', 'app.alertas.'],
            'clientes' => ['app.cliente', 'app.clientes', 'app.participante', 'app.participantes'],
            'documentos' => ['app.importacao', 'app.notas', 'app.clearance', 'app.arquivos'],
            'consultas' => ['app.consulta', 'app.monitoramento', 'app.risk'],
            'relatorios' => ['app.bi', 'app.resumo-fiscal', 'app.catalogo'],
        ];

        foreach ($map as $module => $prefixes) {
            foreach ($prefixes as $prefix) {
                if ($name === $prefix || str_starts_with($name, $prefix.'.')) {
                    return $module;
                }
            }
        }

        return null;
    }

    private function deny(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        abort(403, $message);
    }
}
