<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\IntegracaoStatus;
use Illuminate\Http\Request;

/**
 * View interna de status das integrações (read-only). Acessível a qualquer usuário
 * autenticado — sem EnsureAdmin. Padrão SPA-partial (initialView + $data).
 */
class StatusController extends Controller
{
    use RespondeAjax;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function index(Request $request)
    {
        $view = 'autenticado.status.index';
        $integracoes = IntegracaoStatus::query()->ordenado()->with('atualizadoPor')->get();
        $data = [
            'grupos' => [
                'consultas' => ['titulo' => 'Consultas', 'itens' => $integracoes->where('grupo', 'consultas')],
                'plataforma' => ['titulo' => 'Plataforma', 'itens' => $integracoes->where('grupo', 'plataforma')],
            ],
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }
}
