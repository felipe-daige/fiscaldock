<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\IntegracaoStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Gerenciador admin do status das integrações. Gate: EnsureAdmin na rota. Padrão SPA-partial.
 * Admin só edita `status` e `mensagem` — o catálogo (chave/nome/grupo/ordem) vem do seeder.
 */
class AdminIntegracaoController extends Controller
{
    use RespondeAjax;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function index(Request $request)
    {
        $view = 'autenticado.admin.integracoes.index';
        $integracoes = IntegracaoStatus::query()->ordenado()->with('atualizadoPor')->get();
        $data = [
            'grupos' => [
                'consultas' => ['titulo' => 'Consultas', 'itens' => $integracoes->where('grupo', 'consultas')],
                'plataforma' => ['titulo' => 'Plataforma', 'itens' => $integracoes->where('grupo', 'plataforma')],
            ],
            'statuses' => IntegracaoStatus::statusesValidos(),
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function update(Request $request, IntegracaoStatus $integracao)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(IntegracaoStatus::statusesValidos())],
            'mensagem' => 'nullable|string|max:500',
        ]);
        $data['atualizado_por'] = $request->user()->id;
        $integracao->update($data);

        return redirect()->route('app.admin.integracoes.index')->with('ok', 'Status atualizado.');
    }
}
