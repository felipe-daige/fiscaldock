{{-- Consultas - Histórico --}}
<div class="min-h-screen bg-gray-50" id="consultas-historico-container">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Historico de Consultas</h1>
                    <p class="text-xs text-gray-500 mt-1">Visualize e baixe os relatorios das suas consultas de CNPJ.</p>
                </div>
                <a
                    href="/app/consultas/nova"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold"
                    data-link
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nova Consulta
                </a>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="space-y-6">
            <style>
                @keyframes card-slide-in {
                    from { opacity: 0; transform: translateY(60px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .ch-animate {
                    opacity: 0;
                    animation: card-slide-in 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                }
                @media (prefers-reduced-motion: reduce) {
                    .ch-animate { opacity: 1; animation: none; }
                }
            </style>

        {{-- Lotes (Novo Sistema) --}}
        @if($lotes->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6 ch-animate" style="animation-delay: 0.05s">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Consultas Recentes</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-44">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Plano</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">Participantes</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-36">Créditos</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($lotes as $lote)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                    {{ $lote->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="font-medium text-gray-800">{{ $lote->plano?->nome ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap tabular-nums">
                                    {{ $lote->total_participantes }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap tabular-nums">
                                    {{ $lote->creditos_cobrados }} {{ $lote->creditos_cobrados === 1 ? 'crédito' : 'créditos' }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    @php $badge = $lote->status_badge; @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge['class'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right">
                                    @if($lote->isConcluido())
                                        <div class="flex items-center justify-end gap-2">
                                            <a
                                                href="/app/consultas/lote/{{ $lote->id }}/baixar?formato=csv"
                                                class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                </svg>
                                                CSV
                                            </a>
                                            @if($lote->hasResultados())
                                                <a
                                                    href="/app/consultas/lote/{{ $lote->id }}/baixar?formato=pdf"
                                                    class="inline-flex items-center gap-1 text-gray-600 hover:text-gray-800 font-medium"
                                                >
                                                    PDF
                                                </a>
                                            @endif
                                        </div>
                                    @elseif($lote->isErro())
                                        <span class="text-red-500 text-xs" title="{{ $lote->error_message }}">
                                            {{ $lote->error_code ?? 'Erro no processamento' }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginação --}}
            @if($lotes->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $lotes->links() }}
                </div>
            @endif
        </div>
        @endif

        {{-- Relatórios Legados --}}
        @if($relatoriosLegados->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm ch-animate" style="animation-delay: 0.1s">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-semibold text-gray-900">Relatórios Legados</h3>
                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">Sistema Antigo</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">Relatórios gerados pelo sistema legado de análise de SPED. Estes dados não serão atualizados; para novas consultas, utilize o sistema atual.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-44">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">Participantes</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($relatoriosLegados as $relatorio)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                    {{ $relatorio->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap tabular-nums">
                                    {{ $relatorio->total_participants ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right">
                                    <span class="text-gray-400">-</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Estado vazio --}}
        @if($lotes->isEmpty() && $relatoriosLegados->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center ch-animate" style="animation-delay: 0.05s">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Nenhuma consulta realizada ainda</h3>
            <p class="text-gray-600 mb-6">Suas consultas de CNPJ aparecerão aqui após a primeira execução. Consulte situação cadastral, CNDs, FGTS e muito mais em lote.</p>
            <a href="/app/consultas/nova" data-link class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Iniciar Primeira Consulta
            </a>
        </div>
        @endif
        </div>
    </div>
</div>
