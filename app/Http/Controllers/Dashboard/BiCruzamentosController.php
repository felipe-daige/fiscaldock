<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Concerns\SetsDownloadToken;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\Bi\CruzamentosConsultasClearanceService;
use App\Services\Bi\Export\CruzamentosReportBuilder;
use App\Support\PdfReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * BI cross — Consultas (regularidade do fornecedor) × Clearance/EFD (volume de compras).
 * Página dedicada em /app/bi/cruzamentos. Cards-resumo também aparecem em /app/clearance/alertas.
 */
class BiCruzamentosController extends Controller
{
    use RespondeAjax;
    use SetsDownloadToken;

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
        // Um KPI por cruzamento: os números da faixa batem com as seções por construção.
        $naoTribDivergentes = $naoTributadas->whereIn('flag', ['vermelho', 'amarelo']);
        $resumo = [
            'irregulares_qtd' => $irregulares->count(),
            'irregulares_valor' => round((float) $irregulares->sum('valor_comprado'), 2),
            'canceladas_qtd' => $canceladas->count(),
            'canceladas_valor' => round((float) $canceladas->sum(fn ($n) => (float) ($n['valor'] ?? 0)), 2),
            'nao_trib_divergentes_qtd' => $naoTribDivergentes->count(),
            'nao_trib_competencias_qtd' => $naoTributadas->count(),
            'nao_trib_delta' => round((float) $naoTribDivergentes->sum(fn ($m) => abs((float) $m['delta'])), 2),
            'retencoes_total' => round((float) $retencoesFonte->sum('valor_total'), 2),
            'retencoes_fontes_qtd' => $retencoesFonte->count(),
            'retencoes_nao_consultadas_qtd' => $retencoesFonte->where('consultada', false)->count(),
            'icms_st_total' => round((float) $icmsSt['fornecedores']->sum('valor_st'), 2),
            'icms_st_fornecedores_qtd' => $icmsSt['fornecedores']->count(),
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

    /**
     * Relatório A4 (PDF) dos cruzamentos — mesmos services/filtros da tela (números batem por
     * construção), com parecer executivo, checklist de providências e metodologia auditável.
     * Gate na rota: `bi_completo` + `export`. Guarda defensiva aqui também.
     */
    public function exportarPdf(Request $request, CruzamentosReportBuilder $builder)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! $this->entitlements->permits(Auth::user(), 'bi_completo')) {
            abort(403, 'Seu plano não inclui este recurso.');
        }

        $userId = (int) Auth::id();
        $relatorio = $builder->montar($userId, $this->filtros($request));

        $pdf = PdfReport::render('reports.bi-cruzamentos', ['relatorio' => $relatorio]);

        return $this->comTokenDownload(
            $pdf->download('cruzamentos-fiscais-'.now()->format('Ymd-His').'.pdf'),
            $request
        );
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
