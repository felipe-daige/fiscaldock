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
                    <p class="text-lg font-bold text-gray-900 font-mono">{{ \App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency((int) ($resumo['creditos'] ?? 0))) }}</p>
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
            <div class="overflow-x-auto">
                <table class="min-w-full tabela-cards">
                    <thead class="bg-gray-50">
                        <tr class="border-b border-gray-300">
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Lote / Data</th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Escopo</th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Documentos</th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Custo</th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Status</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Ação</th>
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
                                $preview = $lote->resultado_preview ?? [];
                                $detalheId = 'historico-notas-detalhe-'.$lote->id;
                                $resultadoUrl = route('app.clearance.notas.resultado', ['consultaLoteId' => $lote->id]);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3" data-label="Lote / Data">
                                    <p class="text-sm font-semibold text-gray-900">Lote #{{ $lote->id }}</p>
                                    <p class="text-[11px] text-gray-500 mt-0.5">{{ $lote->created_at->format('d/m/Y H:i') }}</p>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-700" data-label="Escopo">{{ $tier }}</td>
                                <td class="px-3 py-3 text-sm text-gray-700" data-label="Documentos">{{ number_format($lote->total_participantes, 0, ',', '.') }}</td>
                                <td class="px-3 py-3 text-sm font-mono font-semibold text-gray-900" data-label="Custo">{{ \App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency((int) $lote->creditos_cobrados)) }}</td>
                                <td class="px-3 py-3" data-label="Status"><span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusMeta['hex'] }}">{{ $statusMeta['label'] }}</span></td>
                                <td class="px-3 py-3 text-right whitespace-nowrap" data-label="Ação">
                                    <button type="button"
                                            class="historico-notas-details-toggle inline-flex items-center gap-1 whitespace-nowrap text-xs font-semibold text-gray-700 hover:text-gray-900 hover:underline"
                                            data-history-details-toggle="{{ $detalheId }}"
                                            aria-controls="{{ $detalheId }}"
                                            aria-expanded="false">
                                        <span data-history-details-label>Ver detalhes</span>
                                        <svg class="w-3.5 h-3.5 transition-transform" data-history-details-chevron viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"></path>
                                        </svg>
                                    </button>
                                    <a href="{{ $resultadoUrl }}" data-link class="ml-3 inline-flex whitespace-nowrap text-xs font-semibold text-gray-700 hover:text-gray-900 hover:underline">Abrir resultado</a>
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
        if (!toggle || !root.contains(toggle)) return;

        const detailRow = document.getElementById(toggle.dataset.historyDetailsToggle);
        if (!detailRow) return;

        const willOpen = detailRow.classList.contains('hidden');
        detailRow.classList.toggle('hidden', !willOpen);
        toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

        const label = toggle.querySelector('[data-history-details-label]');
        if (label) label.textContent = willOpen ? 'Ocultar detalhes' : 'Ver detalhes';

        const chevron = toggle.querySelector('[data-history-details-chevron]');
        if (chevron) chevron.classList.toggle('rotate-180', willOpen);
    });
})();
</script>
