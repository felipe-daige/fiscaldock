<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\Catalogo\NotaItemUnificadoService;
use App\Services\Catalogo\ReconciliacaoXmlEfdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BiCatalogoItensController extends Controller
{
    use RespondeAjax;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        private NotaItemUnificadoService $service,
        private ReconciliacaoXmlEfdService $reconciliacao,
    ) {}

    public function index(Request $request)
    {
        $view = 'autenticado.bi.catalogo-itens';

        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response('Não autenticado', 401);
            }

            return redirect()->route('login');
        }

        $userId = (int) Auth::id();

        $filtros = array_filter([
            'cliente_id' => $request->integer('cliente_id') ?: null,
            'periodo_de' => $request->input('periodo_de') ?: null,
            'periodo_ate' => $request->input('periodo_ate') ?: null,
            'fonte' => in_array($request->input('fonte'), ['efd', 'xml', 'ambas'], true) ? $request->input('fonte') : null,
        ]);

        $itens = $this->service->itensAgregados($userId, $filtros);

        $kpis = [
            'total_itens' => $itens->count(),
            'com_catalogo' => $itens->where('tem_catalogo', true)->count(),
            'sem_catalogo' => $itens->where('tem_catalogo', false)->count(),
            'valor_movimentado' => (float) $itens->sum('valor_total'),
            'sem_ncm' => $itens->filter(fn ($i) => empty($i['ncm']))->count(),
        ];

        $divergencias = collect($this->service->divergenciasNcmPorItem($userId, $filtros))
            ->filter(fn ($d) => $d['ncm_divergente'])
            ->map(fn ($d, $cod) => array_merge(['codigo_item' => $cod], $d))
            ->values();

        $kpis['ncm_revisar'] = $divergencias->count();

        $reconciliacao = $this->reconciliacao->resumo($userId, $filtros);

        $clientes = Cliente::where('user_id', $userId)->orderByDesc('is_empresa_propria')->orderBy('razao_social')->get(['id', 'razao_social']);

        $data = ['itens' => $itens, 'kpis' => $kpis, 'clientes' => $clientes, 'filtros' => $filtros,
            'divergencias' => $divergencias, 'reconciliacao' => $reconciliacao];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }
}
