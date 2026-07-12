<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Admin\AdminAcaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminUsuarioAcaoController extends Controller
{
    public function __construct(private AdminAcaoService $acoes) {}

    public function creditar(Request $request, int $id)
    {
        $dados = $request->validate([
            'valor' => ['required', 'numeric', 'not_in:0'],
            'motivo' => ['required', 'string', 'min:3', 'max:500'],
        ]);
        $alvo = User::findOrFail($id);

        // Movimento digitado em R$ — converte para a unidade do ledger preservando o sinal.
        $reais = (float) $dados['valor'];
        $creditos = ($reais < 0 ? -1 : 1) * app(\App\Services\PricingCatalogService::class)->currencyToCredits(abs($reais));

        try {
            $this->acoes->creditar($request->user(), $alvo, (float) $creditos, $dados['motivo']);
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            return back()->withErrors(['valor' => $e->getMessage()]);
        }

        return back()->with('status', 'Saldo ajustado.');
    }

    public function bloquear(Request $request, int $id)
    {
        $dados = $request->validate(['motivo' => ['required', 'string', 'min:3', 'max:500']]);
        $alvo = User::findOrFail($id);

        try {
            $alvo->bloqueado_em
                ? $this->acoes->desbloquear($request->user(), $alvo, $dados['motivo'])
                : $this->acoes->bloquear($request->user(), $alvo, $dados['motivo']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['motivo' => $e->getMessage()]);
        }

        return redirect("/app/admin/usuarios/{$id}")->with('status', 'Status de acesso atualizado.');
    }

    public function admin(Request $request, int $id)
    {
        $dados = $request->validate(['motivo' => ['required', 'string', 'min:3', 'max:500']]);
        $alvo = User::findOrFail($id);

        try {
            $this->acoes->definirAdmin($request->user(), $alvo, ! $alvo->is_admin, $dados['motivo']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['motivo' => $e->getMessage()]);
        }

        return redirect("/app/admin/usuarios/{$id}")->with('status', 'Permissão de admin atualizada.');
    }

    public function assinatura(Request $request, int $id)
    {
        $alvo = User::with('subscription')->findOrFail($id);

        $dados = $request->validate([
            'subscription_plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'status' => ['required_with:subscription_plan_id', 'in:'.implode(',', AdminAcaoService::STATUS_ASSINATURA)],
            'ciclo' => ['required_with:subscription_plan_id', 'in:mensal,anual'],
            'iniciada_em' => ['nullable', 'date'],
            'renova_em' => ['nullable', 'date'],
            'proximo_grant_em' => ['nullable', 'date'],
            'ultimo_grant_em' => ['nullable', 'date'],
            // Digitados em R$ no modal — convertidos pra unidade do ledger abaixo.
            'creditos_inclusos_saldo' => ['nullable', 'numeric', 'min:0'],
            'limite_consumo_automatico' => ['nullable', 'numeric', 'min:0'],
            'assentos_extras' => ['nullable', 'numeric', 'min:0', 'multiple_of:1'],
            'mp_preapproval_id' => [
                'nullable',
                'string',
                'max:120',
                Rule::unique('account_subscriptions', 'mp_preapproval_id')->ignore($alvo->subscription?->id),
            ],
            'motivo' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $precos = app(\App\Services\PricingCatalogService::class);
        foreach (['creditos_inclusos_saldo', 'limite_consumo_automatico'] as $campoReais) {
            if (isset($dados[$campoReais])) {
                $dados[$campoReais] = $precos->currencyToCredits((float) $dados[$campoReais]);
            }
        }

        $plano = isset($dados['subscription_plan_id'])
            ? SubscriptionPlan::find((int) $dados['subscription_plan_id'])
            : null;

        try {
            $this->acoes->atualizarAssinatura($request->user(), $alvo, $plano, $dados, $dados['motivo']);
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            return back()->withErrors(['subscription_plan_id' => $e->getMessage()]);
        }

        return back()->with('status', 'Assinatura/plano atualizado.');
    }

    public function trial(Request $request, int $id)
    {
        $dados = $request->validate([
            'trial_used' => ['nullable', 'boolean'],
            'trial_started_at' => ['nullable', 'date'],
            'trial_expires_at' => ['nullable', 'date'],
            'trial_credits_granted' => ['nullable', 'integer', 'min:0'],
            'trial_credits_remaining' => ['nullable', 'integer', 'min:0'],
            'trial_credits_expired' => ['nullable', 'integer', 'min:0'],
            'trial_source' => ['nullable', 'string', 'max:80'],
            'motivo' => ['required', 'string', 'min:3', 'max:500'],
        ]);
        $alvo = User::findOrFail($id);

        $this->acoes->atualizarTrial($request->user(), $alvo, $dados, $dados['motivo']);

        return redirect("/app/admin/usuarios/{$id}")->with('status', 'Trial atualizado.');
    }

    public function impersonar(Request $request, int $id)
    {
        $dados = $request->validate(['motivo' => ['required', 'string', 'min:3', 'max:500']]);
        $alvo = User::findOrFail($id);
        $admin = $request->user();

        if ($alvo->id === $admin->id || $alvo->is_admin || $alvo->bloqueado_em) {
            return back()->withErrors(['motivo' => 'Alvo inválido para impersonação.']);
        }

        $this->acoes->registrar($admin, $alvo, 'impersonar', $dados['motivo']);
        $request->session()->put('impersonator_id', $admin->id);
        Auth::login($alvo);
        $request->session()->regenerate();

        return redirect('/app');
    }

    public function auditoria(Request $request)
    {
        $logs = \App\Models\AdminActionLog::with(['admin', 'alvo'])
            ->orderByDesc('created_at')->paginate(30);

        $view = 'autenticado.admin.auditoria';
        $data = ['logs' => $logs, 'tab' => 'auditoria'];

        if ($request->ajax() || $request->wantsJson()) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view('autenticado.layouts.app', array_merge(['initialView' => $view], $data));
    }

    public function impersonarSair(Request $request)
    {
        $adminId = $request->session()->pull('impersonator_id');
        if (! $adminId) {
            return redirect('/app');
        }

        $alvoId = Auth::id();
        $admin = User::find($adminId);

        if ($admin) {
            Auth::login($admin);
            $request->session()->regenerate();
            $this->acoes->registrar($admin, User::find($alvoId), 'impersonar_sair', 'fim da impersonação');

            return redirect("/app/admin/usuarios/{$alvoId}");
        }

        return redirect('/app');
    }
}
