<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\EfdNota;
use App\Services\ResumoFiscalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResumoFiscalController extends Controller
{
    private const VIEW = 'autenticado.resumo-fiscal.index';

    private const LAYOUT = 'autenticado.layouts.app';

    public function __construct(private ResumoFiscalService $service) {}

    public function index(Request $request)
    {
        $userId = Auth::id();

        $clientes = Cliente::where('user_id', $userId)
            ->where('ativo', true)
            ->select('id', 'nome', 'razao_social', 'is_empresa_propria')
            ->orderBy('razao_social')
            ->get();

        $defaultCliente = $clientes->firstWhere('is_empresa_propria', true) ?? $clientes->first();

        // Competências disponíveis (meses com dados EFD)
        $competencias = EfdNota::where('user_id', $userId)
            ->selectRaw("DISTINCT TO_CHAR(data_emissao, 'YYYY-MM') as competencia")
            ->orderByRaw('competencia DESC')
            ->pluck('competencia');

        $defaultCompetencia = $competencias->first() ?? now()->subMonth()->format('Y-m');

        $data = [
            'clientes' => $clientes,
            'competencias' => $competencias,
            'defaultClienteId' => $defaultCliente?->id,
            'defaultCompetencia' => $defaultCompetencia,
        ];

        if ($this->isAjaxRequest($request)) {
            return view(self::VIEW, $data);
        }

        return view(self::LAYOUT, ['initialView' => self::VIEW, ...$data]);
    }

    public function resumoExecutivo(Request $request)
    {
        [$userId, $clienteId, $competencia] = $this->validarParams($request);

        return response()->json(
            $this->service->getResumoExecutivo($userId, $clienteId, $competencia)
        );
    }

    public function apuracaoIcms(Request $request)
    {
        [$userId, $clienteId, $competencia] = $this->validarParams($request);

        return response()->json(
            $this->service->getApuracaoIcmsData($userId, $clienteId, $competencia)
        );
    }

    public function apuracaoPisCofins(Request $request)
    {
        [$userId, $clienteId, $competencia] = $this->validarParams($request);

        return response()->json(
            $this->service->getApuracaoPisCofinsData($userId, $clienteId, $competencia)
        );
    }

    public function retencoesFonte(Request $request)
    {
        [$userId, $clienteId, $competencia] = $this->validarParams($request);

        return response()->json(
            $this->service->getRetencoesData($userId, $clienteId, $competencia)
        );
    }

    public function aRecolher(Request $request)
    {
        [$userId, $clienteId, $competencia] = $this->validarParams($request);

        return response()->json(
            $this->service->getARecolherData($userId, $clienteId, $competencia)
        );
    }

    public function exportar(Request $request, \App\Services\BiExportService $export)
    {
        [$userId, $clienteId, $competencia] = $this->validarParams($request);
        $data = $this->service->getARecolherData($userId, $clienteId, $competencia);

        $colunas = ['Tributo', 'Valor', 'Vencimento', 'Vencimento estimado', 'Fonte'];
        $linhas = array_map(fn ($l) => [
            $l['tributo'],
            number_format($l['valor'], 2, ',', '.'),
            $l['vencimento'] ? \Illuminate\Support\Carbon::parse($l['vencimento'])->format('d/m/Y') : '',
            $l['vencimento_estimado'] ? 'sim' : 'não',
            $l['fonte'] ?? '',
        ], $data['linhas']);
        $linhas[] = ['Total do mês', number_format($data['total'], 2, ',', '.'), '', '', ''];

        $csv = $export->toCsv($colunas, $linhas);
        $filename = 'fechamento-a-recolher-'.$competencia.'.csv';

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function cruzamentos(Request $request)
    {
        [$userId, $clienteId, $competencia] = $this->validarParams($request);

        return response()->json(
            $this->service->getCruzamentosData($userId, $clienteId, $competencia)
        );
    }

    public function alertasFiscais(Request $request)
    {
        [$userId, $clienteId, $competencia] = $this->validarParams($request);

        return response()->json(
            $this->service->getAlertasFiscaisData($userId, $clienteId, $competencia)
        );
    }

    private function validarParams(Request $request): array
    {
        $userId = Auth::id();
        $clienteId = (int) $request->query('cliente_id');
        $competencia = $request->query('competencia');

        if (! $clienteId || ! $competencia) {
            abort(422, 'Parâmetros obrigatórios: cliente_id, competencia');
        }

        $cliente = Cliente::where('user_id', $userId)->where('id', $clienteId)->first();
        if (! $cliente) {
            abort(404, 'Cliente não encontrado');
        }

        return [$userId, $clienteId, $competencia];
    }

    private function isAjaxRequest(Request $request): bool
    {
        if (method_exists($request, 'ajax') && $request->ajax()) {
            return true;
        }

        return $request->header('X-Requested-With') === 'XMLHttpRequest'
            || $request->wantsJson()
            || $request->expectsJson();
    }
}
