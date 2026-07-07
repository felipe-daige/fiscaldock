<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\Bi\CruzamentosConsultasClearanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * BI cross — Consultas (regularidade do fornecedor) × Clearance/EFD (volume de compras).
 * Página dedicada em /app/bi/cruzamentos. Cards-resumo também aparecem em /app/clearance/alertas.
 */
class BiCruzamentosController extends Controller
{
    use RespondeAjax;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(private CruzamentosConsultasClearanceService $service) {}

    public function index(Request $request)
    {
        $view = 'autenticado.bi.cruzamentos';

        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response('Não autenticado', 401);
            }

            return redirect()->route('login');
        }

        $userId = (int) Auth::id();

        $filtros = array_filter([
            'cliente_id' => $request->integer('cliente_id') ?: null,
        ]);

        $irregulares = $this->service->fornecedoresIrregularesComCompras($userId, $filtros);
        $canceladas = $this->service->notasCanceladasComEmitente($userId, $filtros);

        // Deriva o resumo das coleções já carregadas (mesmo contrato de service->resumo, sem recomputar).
        $resumo = [
            'irregulares_qtd' => $irregulares->count(),
            'irregulares_valor' => round((float) $irregulares->sum('valor_comprado'), 2),
            'canceladas_qtd' => $canceladas->count(),
        ];

        $diagnostico = $this->service->diagnostico($userId);

        $clientes = Cliente::where('user_id', $userId)
            ->orderByDesc('is_empresa_propria')
            ->orderBy('razao_social')
            ->get(['id', 'razao_social']);

        $data = compact('irregulares', 'canceladas', 'resumo', 'diagnostico', 'clientes', 'filtros');

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }
}
