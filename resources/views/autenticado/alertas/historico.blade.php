{{-- Histórico / Auditoria de Alertas --}}
@php
    // Cor por ação (hex inline — Tailwind v4 compila cor de bg pra oklch e não renderiza sempre).
    $acaoHex = [
        'criado' => '#6b7280',
        'resolvido' => '#047857',
        'auto_resolvido' => '#059669',
        'ignorado' => '#9ca3af',
        'visto' => '#374151',
        'reaberto' => '#d97706',
        'reativado' => '#b45309',
    ];
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        {{-- Header --}}
        <div class="mb-4 sm:mb-6">
            <a href="/app/alertas" data-link class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 hover:underline transition-colors mb-3">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Voltar para Central de Alertas
            </a>
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Histórico de Alertas</h1>
            <p class="mt-1 text-xs text-gray-500">Trilha de auditoria — quem resolveu, ignorou ou reabriu cada alerta, e as ações automáticas do sistema.</p>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="/app/alertas/historico" class="bg-white rounded border border-gray-300 overflow-hidden mb-5" data-mobile-filters>
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Filtros</span>
            </div>
            <div class="p-4 flex flex-col sm:flex-row flex-wrap items-stretch sm:items-end gap-3">
                <div class="flex-1 min-w-[140px]">
                    <label for="f-acao" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Ação</label>
                    <select id="f-acao" name="acao" class="w-full px-3 py-2 rounded border border-gray-300 bg-white text-gray-700 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                        <option value="">Todas</option>
                        @foreach(['resolvido' => 'Resolvido', 'ignorado' => 'Ignorado', 'reaberto' => 'Reaberto', 'visto' => 'Visto', 'criado' => 'Aberto', 'auto_resolvido' => 'Resolvido (auto)', 'reativado' => 'Reativado (auto)'] as $val => $lbl)
                            <option value="{{ $val }}" @selected($filtroAcao === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[160px]">
                    <label for="f-cliente" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Cliente</label>
                    <select id="f-cliente" name="cliente_id" class="w-full px-3 py-2 rounded border border-gray-300 bg-white text-gray-700 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                        <option value="">Todos</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}" @selected($filtroCliente === $c->id)>{{ $c->razao_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[120px]">
                    <label for="f-periodo" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Período</label>
                    <select id="f-periodo" name="periodo" class="w-full px-3 py-2 rounded border border-gray-300 bg-white text-gray-700 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                        <option value="">Tudo</option>
                        <option value="30" @selected($filtroPeriodo === 30)>Últimos 30 dias</option>
                        <option value="90" @selected($filtroPeriodo === 90)>Últimos 90 dias</option>
                        <option value="365" @selected($filtroPeriodo === 365)>Último ano</option>
                    </select>
                </div>
                <div class="mobile-filter-actions flex-shrink-0 flex gap-2">
                    <button type="submit" class="px-5 py-2 bg-gray-800 text-white text-sm font-medium rounded hover:bg-gray-700 transition-colors">Filtrar</button>
                    <a href="/app/alertas/historico" data-link class="px-4 py-2 bg-white border border-gray-300 text-gray-600 text-sm font-medium rounded hover:bg-gray-50 transition-colors">Limpar</a>
                </div>
            </div>
        </form>

        {{-- Timeline --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Linha do tempo</span>
                <span class="text-[11px] text-gray-400">{{ $eventos->total() }} evento(s)</span>
            </div>

            @forelse($eventos as $ev)
                @php $hex = $acaoHex[$ev->acao] ?? '#6b7280'; @endphp
                <div class="flex items-start gap-3 px-4 py-3 border-b border-gray-100 last:border-b-0">
                    <span class="mt-1.5 w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $hex }}"></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $hex }}">{{ $ev->acaoLabel() }}</span>
                            @if($ev->alerta)
                                <a href="/app/alertas/{{ $ev->alerta_id }}" data-link class="text-sm font-medium text-gray-900 hover:text-gray-600 hover:underline truncate">{{ $ev->alerta->titulo }}</a>
                            @else
                                <span class="text-sm font-medium text-gray-400 italic">alerta removido</span>
                            @endif
                        </div>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] text-gray-500">
                            <span>por <span class="font-medium text-gray-700">{{ $ev->atorLabel() }}</span></span>
                            <span>{{ $ev->created_at?->format('d/m/Y H:i') }}</span>
                            @if($ev->alerta && $ev->alerta->cliente)
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    {{ $ev->alerta->cliente->razao_social }}
                                </span>
                            @endif
                        </div>
                        @if($ev->notas)
                            <p class="mt-1 text-[12px] text-gray-600 bg-gray-50 border border-gray-100 rounded px-2 py-1">{{ $ev->notas }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-12 text-center">
                    <p class="text-sm text-gray-500">Nenhum evento no histórico ainda.</p>
                    <p class="text-xs text-gray-400 mt-1">Ações sobre alertas (resolver, ignorar, reabrir) e eventos automáticos aparecem aqui.</p>
                </div>
            @endforelse
        </div>

        @if($eventos->hasPages())
            <div class="mt-4">{{ $eventos->onEachSide(1)->links() }}</div>
        @endif

    </div>
</div>
