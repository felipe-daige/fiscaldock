<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Concerns\SetsDownloadToken;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\EfdCatalogoItem;
use App\Models\EfdImportacao;
use App\Services\Catalogo\CatalogoDadosService;
use App\Services\Catalogo\Export\CatalogoCsvZipBuilder;
use App\Services\Catalogo\Export\CatalogoReportBuilder;
use App\Services\Catalogo\Export\CatalogoXlsxBuilder;
use App\Services\CatalogoHistoricoService;
use App\Support\Cfop;
use App\Support\PdfReport;
use App\Support\Reports\XlsxReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CatalogoController extends Controller
{
    use RespondeAjax;
    use SetsDownloadToken;

    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        private CatalogoHistoricoService $historicoService,
        private CatalogoDadosService $dados,
    ) {}

    public function index(Request $request)
    {
        $view = 'autenticado.catalogo.index';

        if (! Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response('Não autenticado', 401);
            }

            return redirect()->route('login');
        }

        $userId = (int) Auth::id();

        $filtros = $this->parseFiltros($request);

        $perPage = 25;
        $page = max(1, (int) $request->get('page', 1));

        $kpis = $this->dados->kpis($userId, $filtros);

        $itensQuery = $this->dados->itensQuery($userId, $filtros);
        $totalItens = (clone $itensQuery)->count();
        $itens = (clone $itensQuery)
            ->orderBy('cod_item')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $clientes = Cliente::where('user_id', $userId)
            ->orderBy('razao_social')
            ->get(['id', 'razao_social']);

        $importacoes = EfdImportacao::where('user_id', $userId)
            ->where('status', 'concluido')
            ->orderByDesc('created_at')
            ->get(['id', 'filename', 'tipo_efd', 'created_at']);

        $cfops = $this->dados->cfopsTop($userId, $filtros);
        $cstsIcms = $this->dados->cstsTop($userId, $filtros);

        // Drift de cadastro: mudanças de NCM/alíquota/unidade/descrição entre importações.
        $drift = $this->historicoService->resumoMudancas($userId);

        // Opções dos filtros CFOP/CST: universo de movimentação do catálogo (respeita cliente/importação).
        $facetaRows = $this->dados->facetaRows($userId, $filtros);
        $pick = fn (string $f) => collect($facetaRows)->pluck($f)
            ->map(fn ($v) => $v !== null ? trim((string) $v) : '')
            ->filter(fn ($v) => $v !== '')->unique()->sort()->values()->all();
        $facetas = ['cfops' => $pick('cfop'), 'csts' => $pick('cst')];
        $cfopOpcoes = array_map(fn (string $c) => $this->rotularCfop($c), $facetas['cfops']);

        $data = [
            'itens' => $itens,
            'kpis' => $kpis,
            'clientes' => $clientes,
            'importacoes' => $importacoes,
            'filtros' => $filtros,
            'facetas' => $facetas,
            'cfopOpcoes' => $cfopOpcoes,
            'cfops' => $cfops,
            'csts_icms' => $cstsIcms,
            'drift' => $drift,
            'paginacao' => [
                'total' => $totalItens,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($totalItens / $perPage),
            ],
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(['initialView' => $view], $data));
    }

    /**
     * Filtros normalizados da tela (compartilhados por index e exports). CFOP/CST são
     * multi-select (1+) e filtram o catálogo pela movimentação (efd_notas_itens).
     *
     * @return array{cliente_id?:mixed,tipo_item?:mixed,ncm?:mixed,importacao_id?:mixed,busca?:mixed,cfops?:list<string>,csts?:list<string>}
     */
    private function parseFiltros(Request $request): array
    {
        $filtros = $request->only(['cliente_id', 'tipo_item', 'ncm', 'importacao_id', 'busca']);

        if ($cfopsSel = $this->parseLista($request->input('cfops'), '/\D/')) {
            $filtros['cfops'] = $cfopsSel;
        }
        if ($cstsSel = $this->parseLista($request->input('csts'), '/[^0-9A-Za-z]/')) {
            $filtros['csts'] = $cstsSel;
        }

        return $filtros;
    }

    public function exportarPdf(Request $request, CatalogoReportBuilder $builder)
    {
        $relatorio = $builder->montar((int) Auth::id(), $this->parseFiltros($request));

        return $this->comTokenDownload(
            PdfReport::render('reports.catalogo', ['relatorio' => $relatorio], 'landscape')
                ->download($this->nomeBase($request).'.pdf'),
            $request
        );
    }

    public function exportarXlsx(Request $request, CatalogoReportBuilder $builder, CatalogoXlsxBuilder $xlsx)
    {
        if (! XlsxReport::disponivel()) {
            abort(503, 'Exportação XLSX indisponível.');
        }

        $relatorio = $builder->montar((int) Auth::id(), $this->parseFiltros($request));

        return $this->comTokenDownload($xlsx->download($relatorio, $this->nomeBase($request).'.xlsx'), $request);
    }

    /** CSV é uma tabela; o relatório tem N seções → ZIP com 1 CSV por seção (padrão do BI). */
    public function exportarCsvZip(Request $request, CatalogoReportBuilder $builder, CatalogoCsvZipBuilder $zip)
    {
        $relatorio = $builder->montar((int) Auth::id(), $this->parseFiltros($request));

        return $this->comTokenDownload($zip->download($relatorio, $this->nomeBase($request).'-csv.zip'), $request);
    }

    private function nomeBase(Request $request): string
    {
        $clienteId = $request->query('cliente_id');
        $sufixo = $clienteId ? '-cliente'.((int) $clienteId) : '';

        return 'catalogo-produtos'.$sufixo;
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

    public function historico(Request $request, string $codItem)
    {
        if (! Auth::check()) {
            return response('Não autenticado', 401);
        }

        $userId = (int) Auth::id();
        $clienteId = $request->query('cliente_id');

        $query = EfdCatalogoItem::where('user_id', $userId)
            ->where('cod_item', $codItem)
            ->with('importacao:id,filename,concluido_em')
            ->orderByDesc('id');

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        $registros = $query->get();

        // Fiscal summary from notas (P4: exclui cancelada dos totais)
        $fiscalQuery = DB::select('
            SELECT ni.cfop, ni.cst_icms, ni.cst_pis, n.tipo_operacao,
                   COUNT(*) as cnt, SUM(ni.valor_total) as valor
            FROM efd_notas_itens ni
            JOIN efd_notas n ON n.id = ni.efd_nota_id AND n.cancelada = false
            WHERE ni.codigo_item = ? AND ni.user_id = ?
            GROUP BY ni.cfop, ni.cst_icms, ni.cst_pis, n.tipo_operacao
            ORDER BY cnt DESC
        ', [$codItem, $userId]);

        $entradas = collect($fiscalQuery)->where('tipo_operacao', 'entrada');
        $saidas = collect($fiscalQuery)->where('tipo_operacao', 'saida');
        $cfopsUnicos = collect($fiscalQuery)->pluck('cfop')->filter()->unique()->values();
        $cstsUnicos = collect($fiscalQuery)->pluck('cst_icms')->filter()->unique()->values();
        $totalEntradas = $entradas->sum('valor');
        $totalSaidas = $saidas->sum('valor');

        // Notas fiscais que contêm este item (últimas 10) — P4: exclui cancelada
        $notas = DB::select('
            SELECT DISTINCT n.id, n.numero, n.serie, n.tipo_operacao, n.valor_total, n.data_emissao,
                   n.origem_arquivo, p.razao_social as participante_nome
            FROM efd_notas_itens ni
            JOIN efd_notas n ON n.id = ni.efd_nota_id AND n.cancelada = false
            LEFT JOIN participantes p ON p.id = n.participante_id
            WHERE ni.codigo_item = ? AND ni.user_id = ?
            ORDER BY n.data_emissao DESC
            LIMIT 10
        ', [$codItem, $userId]);

        $totalNotas = count($notas);
        $totalRegistros = $registros->count();
        $descricaoItem = $registros->first()?->descr_item ?? '';

        // ── Container principal ──
        $html = '<div class="p-4 space-y-3">';

        // ── Mudanças de cadastro (drift): timeline do change-log ──
        $timeline = $this->historicoService->timelineItem($userId, $codItem);
        if (! empty($timeline)) {
            $labelHex = ['cod_ncm' => '#b91c1c', 'aliq_icms' => '#b45309', 'unid_inv' => '#4338ca', 'descr_item' => '#374151'];
            $html .= '<div class="bg-white rounded border border-gray-300 overflow-hidden">';
            $html .= '<div class="bg-amber-50 px-4 py-2 border-b border-amber-200 flex items-center justify-between">';
            $html .= '<span class="text-[10px] font-semibold text-amber-700 uppercase tracking-widest">Mudanças de cadastro</span>';
            $html .= '<span class="text-[10px] font-semibold text-white px-2 py-0.5 rounded" style="background-color: #b45309">'.count($timeline).'</span>';
            $html .= '</div><div class="divide-y divide-gray-100">';
            foreach ($timeline as $m) {
                $hex = $labelHex[$m['campo']] ?? '#374151';
                $data = $m['changed_at'] ? date('d/m/Y', strtotime($m['changed_at'])) : '—';
                $html .= '<div class="px-4 py-2.5 flex items-center gap-3">';
                $html .= '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold text-white shrink-0" style="background-color: '.$hex.'">'.e($m['label']).'</span>';
                $html .= '<span class="text-sm text-gray-500 line-through">'.e($m['de'] ?? '—').'</span>';
                $html .= '<svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';
                $html .= '<span class="text-sm font-semibold text-gray-900">'.e($m['para'] ?? '—').'</span>';
                $html .= '<span class="text-[11px] text-gray-400 ml-auto shrink-0">'.$data.'</span>';
                $html .= '</div>';
            }
            $html .= '</div></div>';
        }

        // ── KPIs em linha (estilo DANFE divide-x) ──
        if (! empty($fiscalQuery)) {
            $html .= '<div class="bg-white rounded border border-gray-300">';
            $html .= '<div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-gray-200">';

            // Entradas
            $html .= '<div class="px-4 py-3">';
            $html .= '<p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Entradas</p>';
            $html .= '<p class="text-lg font-bold text-gray-900">R$ '.number_format($totalEntradas, 2, ',', '.').'</p>';
            $html .= '<p class="text-[11px] text-gray-500 mt-0.5">'.$entradas->sum('cnt').' ocorrências</p>';
            $html .= '</div>';

            // Saídas
            $html .= '<div class="px-4 py-3">';
            $html .= '<p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Saídas</p>';
            $html .= '<p class="text-lg font-bold text-gray-900">R$ '.number_format($totalSaidas, 2, ',', '.').'</p>';
            $html .= '<p class="text-[11px] text-gray-500 mt-0.5">'.$saidas->sum('cnt').' ocorrências</p>';
            $html .= '</div>';

            // CFOPs
            $html .= '<div class="px-4 py-3">';
            $html .= '<p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">CFOPs</p>';
            $html .= '<div class="flex flex-wrap gap-1 mt-1">';
            foreach ($cfopsUnicos as $cfop) {
                $html .= '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold text-white" style="background-color: #4338ca">'.e($cfop).'</span>';
            }
            $html .= '</div></div>';

            // CSTs
            $html .= '<div class="px-4 py-3">';
            $html .= '<p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">CSTs ICMS</p>';
            $html .= '<div class="flex flex-wrap gap-1 mt-1">';
            foreach ($cstsUnicos as $cst) {
                $html .= '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold text-white" style="background-color: #374151">'.e($cst).'</span>';
            }
            $html .= '</div></div>';

            $html .= '</div></div>';
        }

        // ── Notas Fiscais ──
        if (! empty($notas)) {
            $html .= '<div class="bg-white rounded border border-gray-300 overflow-hidden">';
            $html .= '<div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">';
            $html .= '<span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Notas Fiscais</span>';
            $html .= '<span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">'.$totalNotas.'</span>';
            $html .= '</div>';
            $html .= '<div class="overflow-x-auto">';
            $html .= '<table class="min-w-full">';
            $html .= '<thead><tr class="border-b border-gray-300">';
            $html .= '<th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Número</th>';
            $html .= '<th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Participante</th>';
            $html .= '<th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Tipo</th>';
            $html .= '<th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Valor</th>';
            $html .= '<th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Emissão</th>';
            $html .= '<th class="px-3 py-2 bg-gray-50"></th>';
            $html .= '</tr></thead><tbody class="divide-y divide-gray-100">';

            foreach ($notas as $nota) {
                $url = '/app/notas/efd/'.$nota->id;
                $tipoOp = $nota->tipo_operacao === 'entrada' ? 'Entrada' : 'Saída';
                $tipoBg = $nota->tipo_operacao === 'entrada' ? '#047857' : '#d97706';
                $dataFmt = $nota->data_emissao ? date('d/m/Y', strtotime($nota->data_emissao)) : '—';

                $html .= '<tr class="hover:bg-gray-50/50 transition-colors">';
                $html .= '<td class="px-3 py-2"><a href="'.$url.'" data-link class="font-mono text-sm text-gray-900 hover:text-gray-600 hover:underline">'.e($nota->numero ?: '—').($nota->serie ? '/'.e($nota->serie) : '').'</a></td>';
                $html .= '<td class="px-3 py-2 text-sm text-gray-700 truncate max-w-[200px]">'.e($nota->participante_nome ?: '—').'</td>';
                $html .= '<td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: '.$tipoBg.'">'.$tipoOp.'</span></td>';
                $html .= '<td class="px-3 py-2 text-right text-sm font-semibold text-gray-900 font-mono">R$ '.number_format((float) ($nota->valor_total ?? 0), 2, ',', '.').'</td>';
                $html .= '<td class="px-3 py-2 text-sm text-gray-500">'.$dataFmt.'</td>';
                $html .= '<td class="px-3 py-2 text-right"><a href="'.$url.'" data-link class="inline-flex items-center gap-1 text-xs text-gray-600 hover:text-gray-900 hover:underline">Ver nota <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a></td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table></div></div>';
        }

        // ── Histórico de Importações ──
        if ($registros->isEmpty()) {
            $html .= '<div class="bg-white rounded border border-gray-300 p-4 text-center">';
            $html .= '<p class="text-sm text-gray-400">Nenhum registro de importação encontrado.</p>';
            $html .= '</div>';
        } else {
            $html .= '<div class="bg-white rounded border border-gray-300 overflow-hidden">';
            $html .= '<div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">';
            $html .= '<span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Histórico de Importações</span>';
            $html .= '<span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">'.$totalRegistros.'</span>';
            $html .= '</div>';
            $html .= '<div class="overflow-x-auto">';
            $html .= '<table class="min-w-full">';
            $html .= '<thead><tr class="border-b border-gray-300">';
            $html .= '<th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Arquivo</th>';
            $html .= '<th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Data</th>';
            $html .= '<th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">NCM</th>';
            $html .= '<th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Alíq. ICMS</th>';
            $html .= '<th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Unidade</th>';
            $html .= '<th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Descrição</th>';
            $html .= '</tr></thead><tbody class="divide-y divide-gray-100">';

            $prevNcm = null;
            $prevAliq = null;
            foreach ($registros as $reg) {
                $ncmChanged = $prevNcm !== null && $prevNcm !== $reg->cod_ncm;
                $aliqChanged = $prevAliq !== null && abs((float) $prevAliq - (float) $reg->aliq_icms) > 0.01;
                $highlightClass = ($ncmChanged || $aliqChanged) ? ' style="border-left: 3px solid #d97706"' : '';

                $html .= '<tr class="hover:bg-gray-50/50 transition-colors"'.$highlightClass.'>';
                $html .= '<td class="px-3 py-2 text-sm text-gray-700">'.e($reg->importacao?->filename ?? 'ID '.$reg->importacao_id).'</td>';
                $html .= '<td class="px-3 py-2 text-sm text-gray-500">'.($reg->importacao?->concluido_em?->format('d/m/Y') ?? '—').'</td>';
                $html .= '<td class="px-3 py-2 font-mono text-sm '.($ncmChanged ? 'font-semibold' : 'text-gray-700').'">';
                if ($ncmChanged) {
                    $html .= '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold text-white" style="background-color: #d97706">'.e($reg->cod_ncm ?: '—').'</span>';
                } else {
                    $html .= e($reg->cod_ncm ?: '—');
                }
                $html .= '</td>';
                $html .= '<td class="px-3 py-2 text-right text-sm font-mono '.($aliqChanged ? 'font-semibold' : 'text-gray-700').'">';
                if ($aliqChanged) {
                    $html .= '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold text-white" style="background-color: #d97706">'.($reg->aliq_icms !== null ? number_format((float) $reg->aliq_icms, 2, ',', '.').'%' : '—').'</span>';
                } else {
                    $html .= ($reg->aliq_icms !== null ? number_format((float) $reg->aliq_icms, 2, ',', '.').'%' : '—');
                }
                $html .= '</td>';
                $html .= '<td class="px-3 py-2 text-sm text-gray-600">'.e($reg->unid_inv ?: '—').'</td>';
                $html .= '<td class="px-3 py-2 text-sm text-gray-700 truncate max-w-[300px]">'.e($reg->descr_item ?: '—').'</td>';
                $html .= '</tr>';

                $prevNcm = $reg->cod_ncm;
                $prevAliq = $reg->aliq_icms;
            }

            $html .= '</tbody></table></div></div>';
        }
        $html .= '</div>';

        return response($html)->header('Content-Type', 'text/html');
    }
}
