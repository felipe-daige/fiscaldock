<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\AdminPendencia;
use App\Models\IntegracaoStatus;
use App\Services\Admin\AdminAnalyticsService;
use App\Services\Admin\AdminArmazenamentoService;
use Illuminate\Http\Request;

/**
 * Console admin — dashboard de analytics do negócio (read-only, somente operador FiscalDock).
 * Gate: middleware EnsureAdmin na rota.
 */
class AdminAnalyticsController extends Controller
{
    use RespondeAjax;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        private AdminAnalyticsService $analytics,
        private AdminArmazenamentoService $armazenamento,
    ) {}

    public function index(Request $request)
    {
        $view = 'autenticado.admin.dashboard';
        $data = [
            'm' => $this->analytics->resumo(['periodo' => $request->input('periodo', '30')]),
            'operacao' => [
                'disco' => $this->armazenamento->medirDisco(),
                'pendencias_abertas' => AdminPendencia::abertas()->count(),
                'pendencias_vencidas' => AdminPendencia::vencidas()->count(),
                'integracoes_problemas' => IntegracaoStatus::problemasCount(),
            ],
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }
}
