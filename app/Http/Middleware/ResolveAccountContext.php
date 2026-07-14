<?php

namespace App\Http\Middleware;

use App\Models\AccountMember;
use App\Services\Accounts\AccountService;
use App\Support\AccountContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ResolveAccountContext
{
    public function __construct(private AccountService $accounts) {}

    public function handle(Request $request, Closure $next): Response
    {
        $actor = Auth::user();
        if ($actor === null || ! Schema::hasTable('accounts') || ! Schema::hasTable('account_members')) {
            return $next($request);
        }

        $membership = AccountMember::with('account.owner.subscription.plan')
            ->where('user_id', $actor->id)
            ->first() ?? $this->accounts->ensureForOwner($actor);

        $account = $membership->account;
        $owner = $account?->owner;
        if ($account === null || $owner === null) {
            abort(403, 'Não foi possível resolver a conta deste acesso.');
        }

        // O bloqueio da credencial do ator roda antes deste middleware. Aqui cobrimos o
        // colaborador cuja credencial está válida, mas cuja conta/owner foi suspensa.
        if ($owner->bloqueado_em !== null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Esta conta está temporariamente suspensa. Fale com o suporte pelo WhatsApp para regularizar o acesso.']);
        }

        $context = new AccountContext($actor, $owner, $account, $membership);
        app()->instance(AccountContext::class, $context);
        $request->attributes->set('account_context', $context);
        view()->share([
            'accountContext' => $context,
            'actorUser' => $actor,
            'accountMembership' => $membership,
        ]);

        if ($request->is('app/*')
            && $request->isMethod('GET')
            && ! $request->ajax()
            && ! $request->expectsJson()
            && ! $request->is('app/reaceite*')
            && ($actor->terms_version !== config('legal.terms_version')
                || $actor->privacy_version !== config('legal.privacy_version'))) {
            return redirect()->route('app.reaceite.show');
        }

        // Ponte de compatibilidade: o domínio existente usa users.id como tenant key.
        // Dentro de /app, apresentar o owner aos fluxos legados compartilha dados/saldo,
        // enquanto AccountContext preserva o ator real para perfil, autorização e auditoria.
        $personalRoute = $request->is('app/perfil*')
            || $request->is('app/configuracoes*')
            || $request->is('app/privacidade*')
            || $request->is('app/equipe*')
            || $request->is('app/reaceite*')
            || $request->is('app/onboarding*');

        if ($request->is('app/*') && ! $personalRoute && $actor->id !== $owner->id) {
            Auth::setUser($owner);
            $request->setUserResolver(fn () => $owner);
        }

        return $next($request);
    }
}
