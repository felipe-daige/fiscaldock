<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminArmazenamentoService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Console admin — saúde do disco da VPS e quota lógica por conta.
 * Gate: middleware EnsureAdmin na rota.
 */
class AdminArmazenamentoController extends Controller
{
    use RespondeAjax;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(private AdminArmazenamentoService $armazenamento) {}

    public function index(Request $request)
    {
        $view = 'autenticado.admin.armazenamento.index';
        $busca = trim((string) $request->query('q', ''));
        $ordenar = (string) $request->query('ordenar', 'uso_desc');
        if (! in_array($ordenar, ['uso_desc', 'percentual_desc', 'nome_asc'], true)) {
            $ordenar = 'uso_desc';
        }

        $painel = $this->armazenamento->painel([
            'q' => $busca,
            'ordenar' => $ordenar,
        ]);

        $porPagina = 20;
        $pagina = max(1, (int) $request->query('page', 1));
        $contas = new LengthAwarePaginator(
            $painel['contas']->forPage($pagina, $porPagina)->values(),
            $painel['contas']->count(),
            $porPagina,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()],
        );
        $data = [
            'disco' => $painel['disco'],
            'resumoArmazenamento' => $painel['resumo'],
            'contas' => $contas,
            'buscaArmazenamento' => $busca,
            'ordemArmazenamento' => $ordenar,
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }
}
