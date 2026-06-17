<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\CatalogoAlertaDescarte;
use App\Models\Cliente;
use App\Services\Catalogo\AlertaCatalogoDescarteService;
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
        private AlertaCatalogoDescarteService $descartes,
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

        $mostrarDispensados = $request->boolean('dispensados');
        $descNcm = $this->descartes->descartados($userId, 'ncm_divergente');
        $descSem = $this->descartes->descartados($userId, 'sem_catalogo');

        // Divergência é sempre XML×catálogo (não-deduplicado); o filtro `fonte` não se aplica a ela.
        $divergencias = collect($this->service->divergenciasNcmPorItem($userId, $filtros))
            ->filter(fn ($d) => $d['ncm_divergente'])
            ->map(fn ($d, $cod) => array_merge([
                'codigo_item' => (string) $cod,
                'dispensado' => in_array((string) $cod, $descNcm, true),
            ], $d))
            ->values();

        $semCatalogo = $this->service->itensSemCatalogo($userId, $filtros)
            ->map(fn ($i) => array_merge($i, ['dispensado' => in_array($i['codigo_item'], $descSem, true)]))
            ->values();

        $kpis = [
            'total_itens' => $itens->count(),
            'com_catalogo' => $itens->where('tem_catalogo', true)->count(),
            // contagens dos alertas refletem só os ATIVOS (não dispensados)
            'sem_catalogo' => $semCatalogo->where('dispensado', false)->count(),
            'ncm_revisar' => $divergencias->where('dispensado', false)->count(),
            'valor_movimentado' => (float) $itens->sum('valor_total'),
            'sem_ncm' => $itens->filter(fn ($i) => empty($i['ncm']))->count(),
        ];

        $totalDispensados = $divergencias->where('dispensado', true)->count()
            + $semCatalogo->where('dispensado', true)->count();

        // sem o toggle, os painéis escondem os dispensados
        $divergenciasView = $mostrarDispensados ? $divergencias : $divergencias->where('dispensado', false)->values();
        $semCatalogoView = $mostrarDispensados ? $semCatalogo : $semCatalogo->where('dispensado', false)->values();

        $reconciliacao = $this->reconciliacao->resumo($userId, $filtros);

        $clientes = Cliente::where('user_id', $userId)->orderByDesc('is_empresa_propria')->orderBy('razao_social')->get(['id', 'razao_social']);

        $data = ['itens' => $itens, 'kpis' => $kpis, 'clientes' => $clientes, 'filtros' => $filtros,
            'divergencias' => $divergenciasView, 'semCatalogo' => $semCatalogoView, 'reconciliacao' => $reconciliacao,
            'mostrarDispensados' => $mostrarDispensados, 'totalDispensados' => $totalDispensados];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    /** Dispensa um alerta de catálogo (NCM a revisar / sem catálogo) para o usuário. */
    public function descartarAlerta(Request $request)
    {
        return $this->mutarDescarte($request, descartar: true);
    }

    /** Restaura (reativa) um alerta dispensado. */
    public function restaurarAlerta(Request $request)
    {
        return $this->mutarDescarte($request, descartar: false);
    }

    private function mutarDescarte(Request $request, bool $descartar)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }

        $tipo = (string) $request->input('tipo');
        $codigo = trim((string) $request->input('codigo_item'));

        if (! in_array($tipo, CatalogoAlertaDescarte::TIPOS, true) || $codigo === '') {
            return response()->json(['error' => 'Parâmetros inválidos'], 422);
        }

        $userId = (int) Auth::id();
        $descartar
            ? $this->descartes->descartar($userId, $tipo, $codigo)
            : $this->descartes->restaurar($userId, $tipo, $codigo);

        return response()->json(['ok' => true]);
    }
}
