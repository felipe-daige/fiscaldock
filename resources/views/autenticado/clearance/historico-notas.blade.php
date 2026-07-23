@php
    $filtros = $filtros ?? [];
    $busca = $filtros['busca'] ?? '';
    $status = $filtros['status'] ?? '';
    $tierFiltro = $filtros['tier'] ?? '';
    $dataInicio = $filtros['data_inicio'] ?? '';
    $dataFim = $filtros['data_fim'] ?? '';
@endphp

<div class="min-h-screen bg-gray-100" id="clearance-historico-notas-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-4 sm:mb-6">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Histórico de Verificações</h1>
                <p class="text-xs text-gray-500 mt-1">Lotes executados em Verificar Notas, com escopo, custo e acesso ao resultado.</p>
            </div>
            <a href="{{ route('app.clearance.notas') }}" data-link class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded text-sm font-medium self-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Verificar Notas
            </a>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-200">
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Lotes</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($resumo['lotes'] ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Documentos</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($resumo['documentos'] ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Custo</p>
                    <p class="text-lg font-bold text-gray-900 font-mono">{{ \App\Support\Dinheiro::brl((($resumo['creditos'] ?? 0))) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Filtros</span>
                @if(($filtrosAtivos ?? 0) > 0)
                    <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $filtrosAtivos }} ativos</span>
                @endif
            </div>
            <form method="GET" action="{{ route('app.clearance.notas.historico') }}">
                <div class="px-4 py-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3">
                        <input type="text" name="busca" value="{{ $busca }}" placeholder="Lote ou erro..." class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 focus:ring-1 focus:ring-gray-400 xl:col-span-1">
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 focus:ring-1 focus:ring-gray-400">
                            <option value="">Todos os status</option>
                            <option value="pendente" {{ $status === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="processando" {{ $status === 'processando' ? 'selected' : '' }}>Processando</option>
                            <option value="finalizado" {{ in_array($status, ['finalizado', 'concluido'], true) ? 'selected' : '' }}>Finalizado</option>
                            <option value="erro" {{ $status === 'erro' ? 'selected' : '' }}>Erro</option>
                        </select>
                        <select name="tier" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 focus:ring-1 focus:ring-gray-400">
                            <option value="">Todos os escopos</option>
                            <option value="basico" {{ $tierFiltro === 'basico' ? 'selected' : '' }}>Básica</option>
                            <option value="full" {{ $tierFiltro === 'full' ? 'selected' : '' }}>Completa</option>
                        </select>
                        <input type="date" name="data_inicio" value="{{ $dataInicio }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 focus:ring-1 focus:ring-gray-400">
                        <input type="date" name="data_fim" value="{{ $dataFim }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 focus:ring-1 focus:ring-gray-400">
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4 pt-3 border-t border-gray-200">
                        <p class="text-xs text-gray-500">{{ $lotes->total() }} lote{{ $lotes->total() === 1 ? '' : 's' }} encontrado{{ $lotes->total() === 1 ? '' : 's' }}</p>
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium">Filtrar</button>
                            <a href="{{ route('app.clearance.notas.historico') }}" data-link class="flex-1 sm:flex-none px-4 py-2 bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded text-sm font-medium text-center">Limpar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden" data-history-view="clearance-lote">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Verificações</span>
            </div>
            <div class="w-full min-w-0">
                <table class="tabela-cards historico-tabela">
                    <colgroup>
                        <col class="w-[31%]">
                        <col class="w-[12%]">
                        <col class="w-[23%]">
                        <col class="w-[11%]">
                        <col class="w-[13%]">
                        <col class="w-[10%]">
                    </colgroup>
                    <thead class="bg-gray-50">
                        <tr class="border-b border-gray-300">
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Verificação realizada</th>
                            <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Escopo</th>
                            <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Resultado</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Custo</th>
                            <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Status</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide"><span class="sr-only">Ações</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($lotes as $lote)
                            @php
                                $statusMeta = match(\App\Models\ConsultaLote::normalizeStatus($lote->status)) {
                                    'finalizado' => ['label' => 'Finalizado', 'hex' => '#047857'],
                                    'processando' => ['label' => 'Processando', 'hex' => '#b45309'],
                                    'erro' => ['label' => 'Erro', 'hex' => '#dc2626'],
                                    default => ['label' => 'Pendente', 'hex' => '#6b7280'],
                                };
                                $tier = ($lote->resultado_resumo['tier'] ?? 'basico') === 'full' ? 'Completa' : 'Básica';
                                $tierHex = $tier === 'Completa' ? '#7c3aed' : '#1d4ed8';
                                $preview = $lote->resultado_preview ?? [];
                                $documentosPreview = collect($preview['documentos'] ?? []);
                                $documentoPrincipal = $documentosPreview->first();
                                $totalRetornos = (int) ($preview['total'] ?? 0);
                                $totalEsperados = (int) ($preview['esperados'] ?? $lote->total_participantes);
                                $parteOrigem = $documentoPrincipal?->emit_nome;
                                $parteDestino = $documentoPrincipal?->dest_nome ?: $documentoPrincipal?->tomador_nome;
                                $tituloVerificacao = $parteOrigem && $parteDestino
                                    ? $parteOrigem.' → '.$parteDestino
                                    : ($parteOrigem ?: ($parteDestino ?: number_format($totalEsperados, 0, ',', '.').' documento'.($totalEsperados === 1 ? '' : 's').' enviado'.($totalEsperados === 1 ? '' : 's')));
                                $tipoDocumentoPrincipal = strtoupper((string) ($documentoPrincipal?->tipo_documento ?: ''));
                                $numeroDocumentoPrincipal = $documentoPrincipal?->numero;
                                $outrosDocumentos = max(0, $totalEsperados - 1);
                                $vereditoMeta = match(data_get($preview, 'veredito.severidade')) {
                                    'critica' => ['label' => 'Divergências críticas', 'hex' => '#dc2626'],
                                    'revisar' => ['label' => 'Revisão necessária', 'hex' => '#b45309'],
                                    'ruido' => ['label' => 'Dentro da tolerância', 'hex' => '#6b7280'],
                                    'ok' => ['label' => 'Tudo correto', 'hex' => '#047857'],
                                    default => match(\App\Models\ConsultaLote::normalizeStatus($lote->status)) {
                                        'erro' => ['label' => 'Não concluída', 'hex' => '#dc2626'],
                                        'processando' => ['label' => 'Em análise', 'hex' => '#b45309'],
                                        default => ['label' => 'Sem retorno', 'hex' => '#6b7280'],
                                    },
                                };
                                $autorizadas = (int) ($preview['autorizadas'] ?? 0);
                                $alertasResultado = (int) ($preview['alertas'] ?? 0);
                                $revisarResultado = (int) ($preview['indeterminadas'] ?? 0) + (int) ($preview['erros'] ?? 0);
                                $dataLabel = $lote->created_at->isToday()
                                    ? 'Hoje'
                                    : ($lote->created_at->isYesterday() ? 'Ontem' : $lote->created_at->format('d/m'));
                                $detalheId = 'historico-notas-detalhe-'.$lote->id;
                                $resultadoUrl = route('app.clearance.notas.resultado', ['consultaLoteId' => $lote->id]);
                            @endphp
                            <tr class="cursor-pointer hover:bg-gray-50"
                                data-history-result-url="{{ $resultadoUrl }}">
                                <td class="px-3 py-3.5">
                                    <div class="flex w-full min-w-0 items-start gap-3">
                                        <div class="w-12 shrink-0 border-r border-gray-200 pr-3 text-center" title="{{ $lote->created_at->format('d/m/Y H:i') }}">
                                            <p class="text-[10px] font-bold uppercase text-gray-500">{{ $dataLabel }}</p>
                                            <p class="mt-0.5 text-xs font-semibold text-gray-900">{{ $lote->created_at->format('H:i') }}</p>
                                        </div>
                                        <div class="min-w-0 max-w-[390px]">
                                            <p class="truncate text-sm font-semibold text-gray-900" title="{{ $tituloVerificacao }}">{{ $tituloVerificacao }}</p>
                                            <div class="mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-[11px] text-gray-500">
                                                @if($tipoDocumentoPrincipal !== '')
                                                    <span>{{ $tipoDocumentoPrincipal }}{{ $numeroDocumentoPrincipal ? ' nº '.$numeroDocumentoPrincipal : '' }}</span>
                                                @endif
                                                @if($outrosDocumentos > 0)
                                                    <span aria-hidden="true">•</span>
                                                    <span class="font-semibold text-gray-700">+ {{ number_format($outrosDocumentos, 0, ',', '.') }} documento{{ $outrosDocumentos === 1 ? '' : 's' }}</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-[10px] uppercase text-gray-400">Lote #{{ $lote->id }} · {{ $totalRetornos }} de {{ $totalEsperados }} retorno{{ $totalEsperados === 1 ? '' : 's' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center" data-label="Escopo">
                                    <span class="inline-block whitespace-nowrap rounded px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $tierHex }}">{{ $tier }}</span>
                                </td>
                                <td class="px-3 py-3 text-center" data-label="Resultado">
                                    <span class="inline-block whitespace-nowrap rounded px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $vereditoMeta['hex'] }}">{{ $vereditoMeta['label'] }}</span>
                                    @if($totalRetornos > 0)
                                        <p class="mt-1.5 text-[10px] text-gray-500">{{ $autorizadas }} autorizada{{ $autorizadas === 1 ? '' : 's' }} · {{ $alertasResultado }} alerta{{ $alertasResultado === 1 ? '' : 's' }} · {{ $revisarResultado }} a revisar</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right text-sm font-mono font-semibold text-gray-900" data-label="Custo">
                                    <span>{{ \App\Support\Dinheiro::brl(($lote->creditos_cobrados)) }}</span>
                                </td>
                                <td class="px-3 py-3 text-center" data-label="Status">
                                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusMeta['hex'] }}">{{ $statusMeta['label'] }}</span>
                                </td>
                                <td class="px-3 py-3 text-right whitespace-nowrap">
                                    <button type="button"
                                            class="historico-notas-details-toggle inline-flex h-8 w-8 items-center justify-center rounded text-gray-500 hover:bg-gray-100 hover:text-gray-900"
                                            data-history-details-toggle="{{ $detalheId }}"
                                            aria-controls="{{ $detalheId }}"
                                            aria-expanded="false"
                                            aria-label="Ver detalhes"
                                            title="Ver detalhes">
                                        <span class="sr-only" data-history-details-label>Ver detalhes</span>
                                        <svg class="w-3.5 h-3.5 transition-transform" data-history-details-chevron viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr id="{{ $detalheId }}" class="hidden historico-notas-detail-row" data-history-details="lote-{{ $lote->id }}">
                                <td colspan="6" class="px-4 py-4" style="background-color: #f9fafb">
                                    @include('autenticado.clearance.partials._preview-historico-notas', [
                                        'lote' => $lote,
                                        'preview' => $preview,
                                        'statusMeta' => $statusMeta,
                                        'resultadoUrl' => $resultadoUrl,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center"><p class="text-sm font-semibold text-gray-900">Nenhuma verificação encontrada</p><p class="text-xs text-gray-500 mt-1">Execute uma verificação ou ajuste os filtros.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($lotes->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">{{ $lotes->links() }}</div>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    const root = document.getElementById('clearance-historico-notas-container');
    if (!root || root.dataset.detailsInitialized === '1') return;

    root.dataset.detailsInitialized = '1';
    root.addEventListener('click', function (event) {
        const toggle = event.target.closest('[data-history-details-toggle]');
        if (toggle && root.contains(toggle)) {
            const detailRow = document.getElementById(toggle.dataset.historyDetailsToggle);
            if (!detailRow) return;

            const willOpen = detailRow.classList.contains('hidden');
            detailRow.classList.toggle('hidden', !willOpen);
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

            const label = toggle.querySelector('[data-history-details-label]');
            if (label) label.textContent = willOpen ? 'Ocultar detalhes' : 'Ver detalhes';
            toggle.setAttribute('aria-label', willOpen ? 'Ocultar detalhes' : 'Ver detalhes');
            toggle.title = willOpen ? 'Ocultar detalhes' : 'Ver detalhes';

            const chevron = toggle.querySelector('[data-history-details-chevron]');
            if (chevron) chevron.classList.toggle('rotate-180', willOpen);
            return;
        }

        if (event.target.closest('a, button, input, label, select, [data-acoes-menu]')) return;

        const row = event.target.closest('[data-history-result-url]');
        if (!row || !root.contains(row)) return;

        const url = row.dataset.historyResultUrl;
        if (!url) return;

        window.navigateTo ? window.navigateTo(url) : window.location.href = url;
    });
})();
</script>
