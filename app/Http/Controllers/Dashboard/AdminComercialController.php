<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Services\Admin\ComercialParametroService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Painel admin de parâmetros comerciais (CFO §6.1) — somente operador FiscalDock.
 * Edita os overrides de `comercial_parametros`; sem override, vale o default hardcoded.
 * Gate: middleware EnsureAdmin na rota.
 */
class AdminComercialController extends Controller
{
    use RespondeAjax;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(private ComercialParametroService $comercial) {}

    public function index(Request $request)
    {
        $view = 'autenticado.admin.comercial';
        $data = ['parametros' => $this->comercial->efetivos()];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function update(Request $request, string $chave)
    {
        if (! array_key_exists($chave, ComercialParametroService::DEFAULTS)) {
            abort(404);
        }

        $validated = $request->validate([
            'valor' => ['required', 'numeric', 'min:0'],
        ]);

        $this->comercial->definir($chave, $validated['valor'], (int) Auth::id());

        Log::info('admin.comercial.override', [
            'admin_id' => Auth::id(),
            'chave' => $chave,
            'valor' => $validated['valor'],
        ]);

        return back()->with('status', "Parâmetro \"{$chave}\" atualizado.");
    }

    public function reset(Request $request, string $chave)
    {
        if (! array_key_exists($chave, ComercialParametroService::DEFAULTS)) {
            abort(404);
        }

        $this->comercial->resetar($chave);

        Log::info('admin.comercial.reset', ['admin_id' => Auth::id(), 'chave' => $chave]);

        return back()->with('status', "Parâmetro \"{$chave}\" voltou ao padrão.");
    }
}
