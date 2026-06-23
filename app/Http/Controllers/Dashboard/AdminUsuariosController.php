<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\AdminUsuariosService;
use Illuminate\Http\Request;

/**
 * Console admin — usuários e atividade derivada (read-only, somente operador FiscalDock).
 * Gate: middleware EnsureAdmin na rota.
 */
class AdminUsuariosController extends Controller
{
    use RespondeAjax;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(private AdminUsuariosService $usuarios) {}

    public function index(Request $request)
    {
        $view = 'autenticado.admin.usuarios.index';
        $usuariosPg = $this->usuarios->lista(
            ['q' => $request->input('q'), 'ordenar' => $request->input('ordenar')],
            20,
            (int) $request->input('page', 1),
        );
        $data = ['usuarios' => $usuariosPg, 'q' => (string) $request->input('q', '')];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function show(Request $request, int $id)
    {
        $view = 'autenticado.admin.usuarios.show';
        $usuario = User::findOrFail($id);
        $data = [
            'usuario' => $usuario,
            'kpis' => $this->usuarios->kpis($id),
            'assinatura' => $this->usuarios->assinaturaAtiva($id),
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
}
