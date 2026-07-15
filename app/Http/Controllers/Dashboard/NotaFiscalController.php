<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\CteConsulta;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\NfeConsulta;
use App\Models\Participante;
use App\Models\XmlNota;
use App\Services\Consultas\ResultadoDetalhePresenter;
use App\Services\NotaFiscalService;
use App\Support\Cfop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotaFiscalController extends Controller
{
    use RespondeAjax;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        private NotaFiscalService $service,
        private ResultadoDetalhePresenter $detalhePresenter,
    ) {}

    public function index(Request $request)
    {
        $view = 'autenticado.notas.index';

        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $userId = (int) Auth::id();

        $filtros = $request->only([
            'origem', 'data_inicio', 'data_fim', 'tipo_operacao',
            'modelo', 'cliente_id', 'participante_id', 'importacao_id', 'busca',
        ]);

        // CFOP/CST: multi-select (1+), saneados antes de ir pro IN(...).
        if ($cfops = $this->parseLista($request->input('cfops'), '/\D/')) {
            $filtros['cfops'] = $cfops;
        }
        if ($csts = $this->parseLista($request->input('csts'), '/[^0-9A-Za-z]/')) {
            $filtros['csts'] = $csts;
        }

        $perPage = 25;
        $page = max(1, (int) $request->get('page', 1));

        $notas = $this->service->listarUnificadas($userId, $filtros, $perPage, $page);
        $kpis = $this->service->calcularKpis($userId, $filtros);

        $clientes = Cliente::where('user_id', $userId)
            ->orderBy('razao_social')
            ->get(['id', 'razao_social']);

        $participantes = Participante::where('user_id', $userId)
            ->orderBy('razao_social')
            ->get(['id', 'razao_social', 'documento']);

        $importacoes = EfdImportacao::where('user_id', $userId)
            ->where('status', 'concluido')
            ->orderByDesc('created_at')
            ->get(['id', 'filename', 'tipo_efd', 'created_at']);

        $facetas = $this->service->facetasCfopCst($userId, $filtros);
        $cfopOpcoes = array_map(fn (string $c) => $this->rotularCfop($c), $facetas['cfops']);

        $data = [
            'notas' => $notas,
            'kpis' => $kpis,
            'clientes' => $clientes,
            'participantes' => $participantes,
            'importacoes' => $importacoes,
            'filtros' => $filtros,
            'facetas' => $facetas,
            'cfopOpcoes' => $cfopOpcoes,
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    public function detalhes(Request $request, string $origem, int $id)
    {
        if (! Auth::check()) {
            return response('Não autenticado', 401);
        }

        $userId = (int) Auth::id();

        if ($origem === 'efd') {
            $nota = EfdNota::where('id', $id)
                ->where('user_id', $userId)
                ->with(['participante', 'itens' => fn ($q) => $q->orderBy('numero_item')->orderBy('id'), 'cliente', 'consolidados'])
                ->first();

            if (! $nota) {
                return response('Nota não encontrada', 404);
            }

            $consulta = $this->consultaSnapshot($userId, $nota->chave_acesso);
            $auditoria = $this->auditoriaSnapshot($userId, $consulta);

            // O drill-down da listagem permanece leve: não consulta a regularidade das partes.
            if ($this->isAjaxRequest($request) && $this->querDetalheInline($request)) {
                return view('autenticado.notas.partials.efd-inline', compact('nota', 'consulta', 'auditoria'));
            }

            $participanteConsultaDetalhe = $nota->participante
                ? $this->detalhePresenter->detalheDoParticipante($nota->participante, somenteConsultadas: true)
                : null;
            $clienteConsultaDetalhe = $nota->cliente
                ? $this->detalhePresenter->detalheDoCliente($nota->cliente, somenteConsultadas: true)
                : null;
            $viewData = compact(
                'nota',
                'consulta',
                'auditoria',
                'participanteConsultaDetalhe',
                'clienteConsultaDetalhe',
            );

            if ($this->isAjaxRequest($request)) {
                // Navegação SPA serve a página cheia, idêntica ao reload direto.
                return view('autenticado.importacao.efd-nota', $viewData);
            }

            return view(self::AUTH_LAYOUT_VIEW, [
                'initialView' => 'autenticado.importacao.efd-nota',
                ...$viewData,
            ]);
        }

        if ($origem === 'xml') {
            $nota = XmlNota::where('id', $id)
                ->where('user_id', $userId)
                ->with(['emitCliente', 'destCliente', 'cliente', 'itens' => fn ($q) => $q->orderBy('numero_item')->orderBy('id')])
                ->first();

            if (! $nota) {
                return response('Nota não encontrada', 404);
            }

            $consulta = $this->consultaSnapshot($userId, $nota->chave_acesso);
            $auditoria = $this->auditoriaSnapshot($userId, $consulta);

            if ($this->isAjaxRequest($request)) {
                $view = $this->querDetalheInline($request)
                    ? 'autenticado.notas.partials.xml-inline'
                    : 'autenticado.notas.xml-nota';

                return view($view, compact('nota', 'consulta', 'auditoria'));
            }

            return view(self::AUTH_LAYOUT_VIEW, [
                'initialView' => 'autenticado.notas.xml-nota',
                'nota' => $nota,
                'consulta' => $consulta,
                'auditoria' => $auditoria,
            ]);
        }

        return response('Origem inválida', 400);
    }

    /**
     * O card compacto (efd-inline/xml-inline) só é servido quando o drill-down da
     * listagem o pede explicitamente via header. Navegação SPA recebe a página cheia.
     */
    private function querDetalheInline(Request $request): bool
    {
        return $request->header('X-Nota-Detalhe') === 'inline';
    }

    /**
     * Snapshot da última consulta SEFAZ/Clearance daquela nota, por (user_id, chave_acesso).
     * NF-e/NFC-e caem em nfe_consultas; CT-e em cte_consultas (UNIQUE por chave → no máx. 1).
     */
    private function consultaSnapshot(int $userId, ?string $chave): ?object
    {
        if (! $chave) {
            return null;
        }

        $nfe = NfeConsulta::where('user_id', $userId)->where('chave_acesso', $chave)->first();
        if ($nfe) {
            $nfe->tipo_snapshot = 'nfe';

            $this->preferirComprovantesArquivados($nfe, 'nfe');

            return $nfe;
        }

        $cte = CteConsulta::where('user_id', $userId)->where('chave_acesso', $chave)->first();
        if ($cte) {
            $cte->tipo_snapshot = 'cte';

            $this->preferirComprovantesArquivados($cte, 'cte');

            return $cte;
        }

        return null;
    }

    private function preferirComprovantesArquivados(object $snapshot, string $tipo): void
    {
        foreach ([
            'html' => 'url_html',
            'xml' => 'url_xml',
            'site_receipt' => 'url_site_receipt',
        ] as $arquivo => $atributo) {
            $path = data_get($snapshot->payload, "comprovantes_arquivos.{$arquivo}");
            if (! is_string($path) || trim($path) === '') {
                continue;
            }

            $snapshot->{$atributo} = route('app.clearance.comprovante', [
                'tipo' => $tipo,
                'id' => $snapshot->id,
                'arquivo' => $arquivo,
            ]);
        }
    }

    /**
     * Auditoria Declarado × SEFAZ do snapshot (mesma engine canônica do clearance em lote).
     * Confronta a contraparte contra emit/dest do SEFAZ tolerando máscara — não emit=emit fixo.
     */
    private function auditoriaSnapshot(int $userId, ?object $consulta): ?array
    {
        if (! $consulta) {
            return null;
        }

        return app(\App\Services\Clearance\DivergenciaService::class)
            ->auditarUmDocumento($userId, $consulta);
    }

    /**
     * Normaliza input multi-select numa lista saneada (remove o que casar com $stripPattern),
     * sem vazios e sem repetição. Aceita só array.
     *
     * @return list<string>
     */
    private function parseLista(mixed $raw, string $stripPattern): array
    {
        return collect(is_array($raw) ? $raw : [])
            ->map(fn ($v) => preg_replace($stripPattern, '', (string) $v))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Rotula um CFOP pra opção do filtro: código + descrição CONFAZ + tipo (entrada/saída).
     *
     * @return array{codigo:string,descricao:string,tipo:string}
     */
    private function rotularCfop(string $codigo): array
    {
        $full = Cfop::descricao($codigo);
        $descricao = str_contains($full, ' — ') ? trim(explode(' — ', $full, 2)[1]) : '';

        return ['codigo' => $codigo, 'descricao' => $descricao, 'tipo' => Cfop::tipoOperacao($codigo)];
    }

    private function redirectToLogin(Request $request)
    {
        if ($this->isAjaxRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não está logado',
            ], 401);
        }

        return redirect()->route('login');
    }
}
