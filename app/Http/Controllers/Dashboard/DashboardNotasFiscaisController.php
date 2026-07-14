<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Concerns\SetsDownloadToken;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Services\Notas\DashboardNotasService;
use App\Services\Notas\Export\DashboardNotasCsvZipBuilder;
use App\Services\Notas\Export\DashboardNotasReportBuilder;
use App\Services\Notas\Export\DashboardNotasXlsxBuilder;
use App\Support\PdfReport;
use App\Support\Reports\XlsxReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardNotasFiscaisController extends Controller
{
    use RespondeAjax;
    use SetsDownloadToken;

    private const MESES_PT_BR = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro',
    ];

    private const VIEW = 'autenticado.notas.dashboard';

    private const LAYOUT = 'autenticado.layouts.app';

    public function __construct(private DashboardNotasService $service) {}

    public function index(Request $request)
    {
        $userId = Auth::id();

        $importacoes = EfdImportacao::where('user_id', $userId)
            ->where('status', 'concluido')
            ->orderByDesc('concluido_em')
            ->get(['id', 'filename', 'tipo_efd', 'concluido_em']);

        // Detectar range real dos dados do usuário
        $dateRange = EfdNota::where('user_id', $userId)
            ->selectRaw('MIN(data_emissao) as min_date, MAX(data_emissao) as max_date')
            ->first();

        if ($dateRange && $dateRange->min_date) {
            $inicio = Carbon::parse($dateRange->min_date)->startOfMonth()->format('Y-m');
            $fim = Carbon::parse($dateRange->max_date)->endOfMonth()->format('Y-m');
        } else {
            $inicio = Carbon::now()->subMonths(12)->startOfMonth()->format('Y-m');
            $fim = Carbon::now()->format('Y-m');
        }

        $clientes = Cliente::where('user_id', $userId)
            ->where('ativo', true)
            ->select('id', 'nome', 'razao_social', 'is_empresa_propria')
            ->orderBy('razao_social')
            ->get();

        $participantes = Participante::where('user_id', $userId)
            ->whereHas('efdNotas')
            ->select('id', 'razao_social', 'documento as cnpj')
            ->orderBy('razao_social')
            ->get();

        $data = [
            'importacoes' => $importacoes,
            'clientes' => $clientes,
            'participantes' => $participantes,
            'periodos' => $this->periodosDisponiveis($inicio, $fim),
            'defaultTab' => 'visao-geral',
            'filtros' => [
                'periodo_inicio' => $inicio,
                'periodo_fim' => $fim,
                'tipo_efd' => 'todos',
                'importacao_id' => null,
                'cliente_id' => null,
                'participante_id' => null,
            ],
        ];

        if ($this->isAjaxRequest($request)) {
            return view(self::VIEW, $data);
        }

        return view(self::LAYOUT, ['initialView' => self::VIEW, ...$data]);
    }

    /**
     * Opções de competência em português, mantendo AAAA-MM como valor do filtro.
     *
     * @return array<int, array{valor: string, rotulo: string}>
     */
    private function periodosDisponiveis(string $inicio, string $fim): array
    {
        $competencia = Carbon::parse($inicio.'-01')->startOfMonth();
        $ultimaCompetencia = Carbon::parse($fim.'-01')->startOfMonth();
        $periodos = [];

        while ($competencia->lte($ultimaCompetencia)) {
            $periodos[] = [
                'valor' => $competencia->format('Y-m'),
                'rotulo' => self::MESES_PT_BR[$competencia->month].' de '.$competencia->year,
            ];

            $competencia->addMonth();
        }

        return $periodos;
    }

    public function visaoGeral(Request $request)
    {
        return response()->json($this->service->visaoGeral(Auth::id(), $this->parseFiltros($request)));
    }

    public function cfop(Request $request)
    {
        return response()->json($this->service->cfop(Auth::id(), $this->parseFiltros($request)));
    }

    public function participantes(Request $request)
    {
        return response()->json($this->service->participantes(
            Auth::id(),
            $this->parseFiltros($request),
            (string) $request->query('tipo', 'todos'),
            $request->query('busca'),
            max(1, (int) $request->query('page', 1)),
        ));
    }

    public function tributario(Request $request)
    {
        return response()->json($this->service->tributario(Auth::id(), $this->parseFiltros($request)));
    }

    public function alertas(Request $request)
    {
        return response()->json($this->service->alertas(Auth::id(), $this->parseFiltros($request)));
    }

    public function compliance(Request $request)
    {
        return response()->json($this->service->compliance(Auth::id(), $this->parseFiltros($request)));
    }

    /**
     * PDF do "Raio-X do acervo": seções que os demais relatórios NÃO cobrem —
     * mix por modelo, concentração de contrapartes, papel/janela da relação,
     * perfil de CST, saldo mensal por tributo, alertas do acervo e exposição.
     * Ver `docs/dashboard-notas/exportacoes.md`.
     */
    public function exportarPdf(Request $request, DashboardNotasReportBuilder $builder)
    {
        $relatorio = $builder->montar(Auth::id(), $this->parseFiltros($request));

        return $this->comTokenDownload(
            PdfReport::render('reports.notas-dashboard', ['relatorio' => $relatorio])
                ->download($this->nomeBase($relatorio).'.pdf'),
            $request
        );
    }

    public function exportarXlsx(Request $request, DashboardNotasReportBuilder $builder, DashboardNotasXlsxBuilder $xlsx)
    {
        if (! XlsxReport::disponivel()) {
            abort(503, 'Exportação XLSX indisponível.');
        }

        $relatorio = $builder->montar(Auth::id(), $this->parseFiltros($request));

        return $this->comTokenDownload($xlsx->download($relatorio, $this->nomeBase($relatorio).'.xlsx'), $request);
    }

    /**
     * CSV é uma tabela; o relatório tem N seções heterogêneas → ZIP com 1 CSV por seção
     * (mesmo padrão do BI). Ver `BiCsvZipBuilder`.
     */
    public function exportarCsvZip(Request $request, DashboardNotasReportBuilder $builder, DashboardNotasCsvZipBuilder $zip)
    {
        $relatorio = $builder->montar(Auth::id(), $this->parseFiltros($request));

        return $this->comTokenDownload($zip->download($relatorio, $this->nomeBase($relatorio).'-csv.zip'), $request);
    }

    private function nomeBase(array $relatorio): string
    {
        $p = $relatorio['periodo'];
        $sufixo = ($p['inicio'] ?? '') !== '' ? '-'.$p['inicio'].'_'.$p['fim'] : '';

        return 'raio-x-notas-fiscais'.$sufixo;
    }

    private function parseFiltros(Request $request): array
    {
        return [
            'periodo_inicio' => $request->query('periodo_inicio'),
            'periodo_fim' => $request->query('periodo_fim'),
            'tipo_efd' => $request->query('tipo_efd'),
            'importacao_id' => $request->query('importacao_id'),
            'cliente_id' => $request->query('cliente_id'),
            'participante_id' => $request->query('participante_id'),
        ];
    }
}
