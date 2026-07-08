<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Concerns\SetsDownloadToken;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\EfdNota;
use App\Services\ResumoFiscalService;
use App\Support\CsvExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResumoFiscalController extends Controller
{
    use RespondeAjax;
    use SetsDownloadToken;

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

        // Competências disponíveis (meses com dados EFD) do cliente selecionado
        $competencias = $defaultCliente
            ? $this->competenciasDoCliente($userId, $defaultCliente->id)
            : collect();

        $defaultCompetencia = $competencias->first() ?? now()->subMonth()->format('Y-m');

        $data = [
            'clientes' => $clientes,
            'competencias' => $competencias,
            'defaultClienteId' => $defaultCliente?->id,
            'defaultCompetencia' => $defaultCompetencia,
            'temDados' => $competencias->isNotEmpty(),
        ];

        if ($this->isAjaxRequest($request)) {
            return view(self::VIEW, $data);
        }

        return view(self::LAYOUT, ['initialView' => self::VIEW, ...$data]);
    }

    public function competencias(Request $request)
    {
        $userId = Auth::id();
        $clienteId = (int) $request->query('cliente_id');

        if (! $clienteId) {
            abort(422, 'Parâmetro obrigatório: cliente_id');
        }

        $cliente = Cliente::where('user_id', $userId)->where('id', $clienteId)->first();
        if (! $cliente) {
            abort(404, 'Cliente não encontrado');
        }

        $competencias = $this->competenciasDoCliente($userId, $clienteId)
            ->map(fn ($comp) => [
                'value' => $comp,
                'label' => \Carbon\Carbon::parse($comp.'-01')->translatedFormat('M/Y'),
            ])
            ->values();

        return response()->json(['competencias' => $competencias]);
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

    public function exportar(Request $request)
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

        $filename = 'fechamento-a-recolher-'.$competencia.'.csv';

        return $this->comTokenDownload(CsvExport::download($filename, $colunas, $linhas), $request);
    }

    /**
     * Payload completo do fechamento (as 7 seções da tela). Base comum do PDF e do XLSX —
     * ambos leem os MESMOS métodos do service, então os números batem entre si e com a tela.
     *
     * @return array{0:array<string,mixed>,1:string} [$dados, $nomeBase]
     */
    private function payloadFechamento(Request $request): array
    {
        [$userId, $clienteId, $competencia] = $this->validarParams($request);
        $cliente = Cliente::where('user_id', $userId)->where('id', $clienteId)->firstOrFail();

        $dados = [
            'cliente' => $cliente,
            'competencia' => $competencia,
            'competenciaLabel' => \Carbon\Carbon::parse($competencia.'-01')->translatedFormat('F/Y'),
            'geradoEm' => now(),
            'resumo' => $this->service->getResumoExecutivo($userId, $clienteId, $competencia),
            'aRecolher' => $this->service->getARecolherData($userId, $clienteId, $competencia),
            'cruzamentos' => $this->service->getCruzamentosData($userId, $clienteId, $competencia),
            'alertas' => $this->service->getAlertasFiscaisData($userId, $clienteId, $competencia),
            'icms' => $this->service->getApuracaoIcmsData($userId, $clienteId, $competencia),
            'pisCofins' => $this->service->getApuracaoPisCofinsData($userId, $clienteId, $competencia),
            'retencoes' => $this->service->getRetencoesData($userId, $clienteId, $competencia),
            'hashDoc' => \App\Support\PdfReport::hashDocumento($userId, 'resumo-fiscal', $clienteId, $competencia),
        ];

        $doc = preg_replace('/\D/', '', (string) ($cliente->documento ?? ''));
        $nomeBase = 'fechamento-fiscal-'.($doc !== '' ? $doc.'-' : '').$competencia;

        return [$dados, $nomeBase];
    }

    public function exportarPdf(Request $request)
    {
        [$dados, $nomeBase] = $this->payloadFechamento($request);

        return $this->comTokenDownload(
            \App\Support\PdfReport::render('reports.resumo-fiscal', $dados, 'portrait')->download($nomeBase.'.pdf'),
            $request
        );
    }

    /**
     * XLSX do fechamento: 1 aba por seção (export COMPLETO). O CSV (`exportar`) segue
     * cobrindo só "A Recolher" — assimetria registrada no spec das planilhas.
     */
    public function exportarXlsx(Request $request)
    {
        if (! \App\Support\Reports\XlsxReport::disponivel()) {
            abort(503, 'Exportação XLSX indisponível.');
        }

        [$dados, $nomeBase] = $this->payloadFechamento($request);

        return $this->comTokenDownload(
            (new \App\Services\ResumoFiscal\Export\ResumoFiscalXlsxBuilder)->download($dados, $nomeBase.'.xlsx'),
            $request
        );
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

    /**
     * Competências (YYYY-MM) com notas EFD para um cliente, mais recentes primeiro.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function competenciasDoCliente(int $userId, int $clienteId)
    {
        // data_emissao pode ser NULL em documentos cancelados/inutilizados cujo C100
        // não traz DT_DOC no SPED (valor 0, sem participante). Sem este filtro, o
        // TO_CHAR(NULL) vira uma competência fantasma que a view renderiza como "dez/1969".
        return EfdNota::where('user_id', $userId)
            ->where('cliente_id', $clienteId)
            ->whereNotNull('data_emissao')
            ->selectRaw("DISTINCT TO_CHAR(data_emissao, 'YYYY-MM') as competencia")
            ->orderByRaw('competencia DESC')
            ->pluck('competencia');
    }
}
