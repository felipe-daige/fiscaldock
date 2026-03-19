@php
    $fmt = fn(float $v) => 'R$ ' . number_format(abs($v), 2, ',', '.');
    $fmtSinal = function(float $v): string {
        $sinal = $v < 0 ? '− ' : '';
        return $sinal . 'R$ ' . number_format(abs($v), 2, ',', '.');
    };
@endphp

<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">

    {{-- Card 1: Entradas --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col gap-1 col-span-1">
        <div class="flex items-center gap-2 text-emerald-600">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
            <span class="text-xs font-semibold uppercase tracking-wide">Entradas</span>
        </div>
        <p class="text-xl font-bold text-gray-900 mt-1">{{ $fmt($kpis['total_entradas_valor']) }}</p>
        <p class="text-xs text-gray-400">{{ number_format($kpis['total_entradas_notas']) }} notas</p>
    </div>

    {{-- Card 2: Saídas --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col gap-1 col-span-1">
        <div class="flex items-center gap-2 text-rose-600">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
            <span class="text-xs font-semibold uppercase tracking-wide">Saídas</span>
        </div>
        <p class="text-xl font-bold text-gray-900 mt-1">{{ $fmt($kpis['total_saidas_valor']) }}</p>
        <p class="text-xs text-gray-400">{{ number_format($kpis['total_saidas_notas']) }} notas</p>
    </div>

    {{-- Card 3: Saldo Líquido --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col gap-1 col-span-1">
        <div class="flex items-center gap-2 {{ $kpis['saldo_liquido'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5 5 0 006.9 5.05 5 5 0 00-6.9-5.05m3-1H3m3 0l3 1m0 0l3-9m-3 9l3 1m0 0A5 5 0 0021 6a5 5 0 00-5.1 5m5.1-5h-3"/>
            </svg>
            <span class="text-xs font-semibold uppercase tracking-wide">Saldo Líquido</span>
        </div>
        <p class="text-xl font-bold {{ $kpis['saldo_liquido'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }} mt-1">
            {{ $fmtSinal($kpis['saldo_liquido']) }}
        </p>
    </div>

    {{-- Card 4: Carga Tributária --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col gap-1 col-span-1">
        <div class="flex items-center gap-2 text-amber-600">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span class="text-xs font-semibold uppercase tracking-wide">ICMS + PIS + COFINS</span>
        </div>
        <p class="text-xl font-bold text-gray-900 mt-1">{{ $fmt($kpis['carga_tributaria']) }}</p>
    </div>

    {{-- Card 5: Participantes Ativos --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col gap-1 col-span-1">
        <div class="flex items-center gap-2 text-blue-600">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5 5 0 019.288 0"/>
            </svg>
            <span class="text-xs font-semibold uppercase tracking-wide">Fornecedores/Clientes</span>
        </div>
        <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($kpis['participantes_ativos']) }}</p>
        <p class="text-xs text-gray-400">com notas no período</p>
    </div>

    {{-- Card 6: Notas em Risco --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col gap-1 col-span-1
        {{ $kpis['notas_em_risco'] > 0 ? 'border-rose-200 bg-rose-50' : '' }}">
        <div class="flex items-center gap-2 {{ $kpis['notas_em_risco'] > 0 ? 'text-rose-600' : 'text-gray-400' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="text-xs font-semibold uppercase tracking-wide">Fornecedor Irregular</span>
        </div>
        <p class="text-xl font-bold {{ $kpis['notas_em_risco'] > 0 ? 'text-rose-700' : 'text-gray-400' }} mt-1">
            {{ number_format($kpis['notas_em_risco']) }}
        </p>
        @if($kpis['notas_em_risco'] > 0)
            <a href="#" class="text-xs text-rose-600 underline mt-1">Ver detalhes</a>
        @else
            <p class="text-xs text-gray-400">nenhuma irregularidade</p>
        @endif
    </div>

</div>
