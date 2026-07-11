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

    public function __construct(
        private CruzamentosConsultasClearanceService $service,
        private \App\Services\Bi\CruzamentosEfdInternosService $efdInternos,
        private \App\Services\Entitlements\EntitlementService $entitlements,
    ) {}

    /** Free (sem bi_completo): tela abre em paywall — skeleton borrado + card, dados nem são computados. */
    private function paywall(Request $request)
    {
        $view = 'autenticado.partials.paywall-page';
        $data = [
            'paginaTitulo' => 'Cruzamentos — Consultas × Notas',
            'paginaSubtitulo' => 'Fornecedores irregulares × volume comprado e notas canceladas na SEFAZ.',
            'paywallTitulo' => 'Cruzamentos são do BI completo',
            'paywallDescricao' => 'O cruzamento de regularidade dos fornecedores com o seu volume de compras (e notas canceladas × emitente) faz parte do BI completo, disponível nos planos pagos.',
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function index(Request $request)
    {
        $view = 'autenticado.bi.cruzamentos';

        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response('Não autenticado', 401);
            }

            return redirect()->route('login');
        }

        if (! $this->entitlements->permits(Auth::user(), 'bi_completo')) {
            return $this->paywall($request);
        }

        $userId = (int) Auth::id();

        $filtros = $this->filtros($request);

        $irregulares = $this->service->fornecedoresIrregularesComCompras($userId, $filtros);
        $canceladas = $this->service->notasCanceladasComEmitente($userId, $filtros);
        $naoTributadas = $this->efdInternos->receitasNaoTributadasPorCompetencia($userId, $filtros);
        $retencoesFonte = $this->efdInternos->retencoesPorFonte($userId, $filtros);
        $icmsSt = $this->efdInternos->icmsStRegime($userId, $filtros);
        $estoque = $this->efdInternos->estoqueVsMovimentacao($userId, $filtros);

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

        $data = compact('irregulares', 'canceladas', 'naoTributadas', 'retencoesFonte', 'icmsSt', 'estoque', 'resumo', 'diagnostico', 'clientes', 'filtros');

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    /**
     * Drill-down do cruzamento: documentos de compra (EFD + XML) do fornecedor irregular,
     * com os mesmos filtros da tela. JSON consumido pelo expand da linha.
     */
    public function fornecedorNotas(Request $request, int $participanteId)
    {
        if (! $this->entitlements->permits($request->user(), 'bi_completo')) {
            return response()->json(['message' => 'Disponível no BI completo.'], 403);
        }

        $userId = (int) Auth::id();

        $existe = \App\Models\Participante::where('user_id', $userId)->whereKey($participanteId)->exists();
        if (! $existe) {
            return response()->json(['message' => 'Fornecedor não encontrado.'], 404);
        }

        $notas = $this->service->notasDoFornecedor($userId, $participanteId, $this->filtros($request));

        return response()->json(['notas' => $notas]);
    }

    /** @return array{cliente_id?:int, data_inicio?:string, data_fim?:string} */
    private function filtros(Request $request): array
    {
        $validos = $request->validate([
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date'],
        ]);

        return array_filter([
            'cliente_id' => $request->integer('cliente_id') ?: null,
            'data_inicio' => ($validos['data_inicio'] ?? null) ?: null,
            'data_fim' => ($validos['data_fim'] ?? null) ?: null,
        ]);
    }
}
