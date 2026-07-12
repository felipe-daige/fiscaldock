<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Admin\AdminAcaoService;
use App\Services\Admin\AdminUsuariosService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Console admin — usuários, atividade derivada e operações de suporte.
 * Gate: middleware EnsureAdmin na rota.
 */
class AdminUsuariosController extends Controller
{
    use RespondeAjax;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        private AdminUsuariosService $usuarios,
        private AdminAcaoService $acoes,
    ) {}

    public function index(Request $request)
    {
        $view = 'autenticado.admin.usuarios.index';
        $usuariosPg = $this->usuarios->lista(
            ['q' => $request->input('q'), 'ordenar' => $request->input('ordenar')],
            20,
            (int) $request->input('page', 1),
        );
        $data = [
            'usuarios' => $usuariosPg,
            'planos' => SubscriptionPlan::orderBy('ordem')->get(),
            'q' => (string) $request->input('q', ''),
            'ordenar' => (string) $request->input('ordenar', 'created_at'),
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function create(Request $request)
    {
        $view = 'autenticado.admin.usuarios.create';
        $data = $this->formData();

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function store(Request $request)
    {
        $dados = $this->validarUsuario($request);

        // Saldo inicial é digitado em R$ e normalizado para a unidade física do ledger.
        if (isset($dados['credits'])) {
            $dados['credits'] = app(\App\Services\PricingCatalogService::class)->currencyToCredits((float) $dados['credits']);
        }

        $usuario = $this->acoes->criarUsuario($request->user(), $dados, $dados['motivo']);

        return redirect()->route('app.admin.usuarios.show', $usuario->id)
            ->with('status', 'Usuário criado.');
    }

    public function show(Request $request, int $id)
    {
        $view = 'autenticado.admin.usuarios.show';
        $usuario = User::findOrFail($id);
        $data = [
            'usuario' => $usuario,
            'kpis' => $this->usuarios->kpis($id),
            'detalheAdmin' => $this->usuarios->detalhe($usuario),
            'assinatura' => $this->usuarios->assinaturaAtiva($id),
            'assinaturaAtual' => $this->usuarios->assinaturaAtual($id),
            'planos' => SubscriptionPlan::orderBy('ordem')->get(),
            'sessao' => $this->usuarios->ultimaSessao($id),
            'timeline' => $this->usuarios->timeline($id),
            'trilhaAdmin' => \App\Models\AdminActionLog::with('admin')
                ->where('target_user_id', $id)->orderByDesc('created_at')->limit(50)->get(),
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function edit(Request $request, int $id)
    {
        $view = 'autenticado.admin.usuarios.edit';
        $usuario = User::findOrFail($id);
        $data = array_merge($this->formData($usuario), [
            'usuario' => $usuario,
        ]);

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function update(Request $request, int $id)
    {
        $usuario = User::findOrFail($id);
        $dados = $this->validarUsuario($request, $usuario);

        try {
            $this->acoes->atualizarUsuario($request->user(), $usuario, $dados, $dados['motivo']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['motivo' => $e->getMessage()])->withInput();
        }

        return redirect()->route('app.admin.usuarios.show', $usuario->id)
            ->with('status', 'Usuário atualizado.');
    }

    public function destroy(Request $request, int $id)
    {
        $dados = $request->validate([
            'motivo' => ['required', 'string', 'min:3', 'max:500'],
            'confirmacao' => ['required', 'in:ANONIMIZAR'],
        ]);
        $usuario = User::findOrFail($id);

        try {
            $this->acoes->anonimizarUsuario($request->user(), $usuario, $dados['motivo']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['confirmacao' => $e->getMessage()]);
        }

        return redirect()->route('app.admin.usuarios.show', $usuario->id)
            ->with('status', 'Usuário anonimizado e acesso bloqueado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?User $usuario = null): array
    {
        return [
            'usuario' => $usuario,
            'faturamentos' => config('cadastro.faturamento', []),
            'desafios' => config('cadastro.desafios', []),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validarUsuario(Request $request, ?User $usuario = null): array
    {
        $id = $usuario?->id;
        $passwordRules = $usuario ? ['nullable', 'string', 'min:8'] : ['required', 'string', 'min:8'];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sobrenome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'telefone' => ['required', 'string', 'max:20'],
            'password' => $passwordRules,
            'empresa' => ['nullable', 'string', 'max:255'],
            'cargo' => ['nullable', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'faturamento_anual' => ['nullable', 'string', 'max:80'],
            'desafio_principal' => ['nullable', 'string', 'max:80'],
            'desafio_secundario' => ['nullable', 'string', 'max:80'],
            'credits' => [$usuario ? 'nullable' : 'required', 'numeric', 'min:0'],
            'email_verified' => ['nullable', 'boolean'],
            'marketing_opt_in' => ['nullable', 'boolean'],
            'is_admin' => ['nullable', 'boolean'],
            'bloqueado' => ['nullable', 'boolean'],
            'alertas_operacionais' => ['nullable', 'boolean'],
            'alertas_monitoramento' => ['nullable', 'boolean'],
            'resumo_periodico' => ['nullable', 'boolean'],
            'force_terms_reaccept' => ['nullable', 'boolean'],
            'motivo' => ['required', 'string', 'min:3', 'max:500'],
        ]);
    }
}
