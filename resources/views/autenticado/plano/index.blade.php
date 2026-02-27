{{-- Meu Plano --}}
<div class="min-h-screen bg-gray-50" id="plano-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <style>
            @keyframes card-slide-in {
                from { opacity: 0; transform: translateY(60px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .pl-animate {
                opacity: 0;
                animation: card-slide-in 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            @media (prefers-reduced-motion: reduce) {
                .pl-animate { opacity: 1; animation: none; }
            }
        </style>

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Meu Plano</h1>
            <p class="mt-1 text-sm text-gray-600">Gerencie seus créditos e acompanhe o consumo.</p>
        </div>

        {{-- KPI Strip --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            {{-- Saldo Atual --}}
            <div class="pl-animate bg-white rounded-lg border border-gray-100 border-t-2 border-t-emerald-500 p-6" style="animation-delay: 0.1s">
                <span class="text-xs uppercase tracking-wide text-gray-400 font-semibold">Saldo Atual</span>
                <div class="mt-2">
                    <span class="text-3xl font-bold {{ $saldoAtual > 0 ? 'text-emerald-600' : 'text-gray-400' }}">{{ number_format($saldoAtual, 0, ',', '.') }}</span>
                    <p class="text-sm text-gray-400 mt-0.5">créditos</p>
                </div>
            </div>

            {{-- Usados no Mês --}}
            <div class="pl-animate bg-white rounded-lg border border-gray-100 border-t-2 border-t-blue-500 p-6" style="animation-delay: 0.15s">
                <span class="text-xs uppercase tracking-wide text-gray-400 font-semibold">Usados no Mês</span>
                <div class="mt-2">
                    <span class="text-3xl font-bold text-blue-600">{{ number_format($creditosUsadosMes, 0, ',', '.') }}</span>
                    <p class="text-sm text-gray-400 mt-0.5">créditos</p>
                </div>
            </div>

            {{-- Consultas no Mês --}}
            <div class="pl-animate bg-white rounded-lg border border-gray-100 border-t-2 border-t-indigo-500 p-6" style="animation-delay: 0.2s">
                <span class="text-xs uppercase tracking-wide text-gray-400 font-semibold">Consultas no Mês</span>
                <div class="mt-2">
                    <span class="text-3xl font-bold text-indigo-600">{{ $consultasMes }}</span>
                    <p class="text-sm text-gray-400 mt-0.5">lotes executados</p>
                </div>
            </div>

            {{-- Media por Consulta --}}
            <div class="pl-animate bg-white rounded-lg border border-gray-100 border-t-2 border-t-gray-300 p-6" style="animation-delay: 0.25s">
                <span class="text-xs uppercase tracking-wide text-gray-400 font-semibold">Média / Consulta</span>
                <div class="mt-2">
                    <span class="text-3xl font-bold text-gray-700">{{ $mediaCreditos }}</span>
                    <p class="text-sm text-gray-400 mt-0.5">créditos por lote</p>
                </div>
            </div>
        </div>

        {{-- Two Columns --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column (2/3) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Grafico de Consumo Mensal --}}
                <div class="pl-animate bg-white rounded-lg border border-gray-100 p-6" style="animation-delay: 0.3s">
                    <h3 class="text-xs uppercase tracking-wide text-gray-400 font-semibold mb-4">Consumo Mensal (Últimos 6 Meses)</h3>

                    <div class="space-y-3" id="chart-consumo"
                         data-values="{{ json_encode(array_column($consumoMensal, 'valor')) }}"
                         data-labels="{{ json_encode(array_column($consumoMensal, 'label')) }}"
                         data-max="{{ $maxConsumo }}">
                        @foreach($consumoMensal as $i => $mes)
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-500 w-16 text-right font-mono">{{ $mes['label'] }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-6 relative overflow-hidden">
                                <div class="bg-blue-500 h-6 rounded-full transition-all duration-700 ease-out flex items-center justify-end pr-2"
                                     style="width: {{ $maxConsumo > 0 ? max(($mes['valor'] / $maxConsumo) * 100, 0) : 0 }}%; min-width: {{ $mes['valor'] > 0 ? '2rem' : '0' }}"
                                     data-bar-index="{{ $i }}">
                                    @if($mes['valor'] > 0)
                                    <span class="text-xs text-white font-semibold">{{ $mes['valor'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @if($maxConsumo == 0)
                    <div class="text-center py-6">
                        <p class="text-sm text-gray-400">Nenhum consumo registrado nos últimos 6 meses</p>
                    </div>
                    @endif
                </div>

                {{-- Histórico de Consumo --}}
                <div class="pl-animate bg-white rounded-lg border border-gray-100 p-6" style="animation-delay: 0.4s">
                    <h3 class="text-xs uppercase tracking-wide text-gray-400 font-semibold mb-4">Histórico de Consumo</h3>

                    @if($ultimasTransacoes->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Descrição</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Plano</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Participantes</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Créditos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($ultimasTransacoes as $tx)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2.5 text-sm text-gray-700 whitespace-nowrap">
                                        {{ $tx->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-3 py-2.5 text-sm text-gray-700">
                                        @php
                                            $statusCor = match($tx->status) {
                                                'concluido' => 'green',
                                                'processando' => 'blue',
                                                'erro' => 'red',
                                                default => 'gray'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $statusCor }}-100 text-{{ $statusCor }}-700">
                                            {{ ucfirst($tx->status) }}
                                        </span>
                                        Consulta em Lote #{{ $tx->id }}
                                    </td>
                                    <td class="px-3 py-2.5 text-sm text-gray-600">
                                        {{ $tx->plano->nome ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2.5 text-sm text-gray-700 text-center">
                                        {{ $tx->total_participantes ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2.5 text-sm font-semibold text-right {{ $tx->creditos_cobrados > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                        @if($tx->creditos_cobrados > 0)
                                            -{{ $tx->creditos_cobrados }}
                                        @else
                                            0
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-sm text-gray-400">Nenhuma consulta realizada ainda</p>
                        <a href="/app/consultas/nova" data-link class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 mt-2">
                            Iniciar primeira consulta
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Right Column (1/3) --}}
            <div class="space-y-6">

                {{-- Card Saldo Destaque --}}
                <div class="pl-animate bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg p-6 text-white" style="animation-delay: 0.3s">
                    <span class="text-xs uppercase tracking-wide text-emerald-200 font-semibold">Saldo Disponível</span>
                    <div class="mt-3">
                        <span class="text-4xl font-bold">{{ number_format($saldoAtual, 0, ',', '.') }}</span>
                        <span class="text-lg text-emerald-200 ml-1">créditos</span>
                    </div>
                    <div class="mt-2 pt-2 border-t border-emerald-400/30">
                        <span class="text-sm text-emerald-100">Saldo disponivel para consultas</span>
                    </div>
                </div>

                {{-- Comprar Créditos --}}
                <div class="pl-animate bg-white rounded-lg border border-gray-100 p-6" style="animation-delay: 0.4s">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs uppercase tracking-wide text-gray-400 font-semibold">Comprar Créditos</h3>
                        <a href="/app/creditos" data-link class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                            Ver todos
                            <svg class="w-3 h-3 inline ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>

                    <div class="space-y-3">
                        {{-- Starter --}}
                        <a href="/app/checkout/starter" data-link class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors block">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Starter</p>
                                <p class="text-xs text-gray-400">100 creditos</p>
                            </div>
                            <span class="text-sm font-bold text-gray-700">R$ 26</span>
                        </a>

                        {{-- Growth --}}
                        <a href="/app/checkout/growth" data-link class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors block">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Growth</p>
                                <p class="text-xs text-gray-400">500 creditos</p>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold text-gray-700">R$ 117</span>
                                <span class="block text-xs text-emerald-600 font-medium">-10%</span>
                            </div>
                        </a>

                        {{-- Business --}}
                        <a href="/app/checkout/business" data-link class="flex items-center justify-between p-3 rounded-lg border-2 border-blue-500 bg-blue-50/50 relative block">
                            <span class="absolute top-1.5 right-1.5 px-2 py-0.5 bg-blue-600 text-white text-[10px] uppercase font-bold rounded">Popular</span>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Business</p>
                                <p class="text-xs text-gray-400">1.000 creditos</p>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold text-gray-700">R$ 208</span>
                                <span class="block text-xs text-emerald-600 font-medium">-20%</span>
                            </div>
                        </a>

                        {{-- Enterprise --}}
                        <a href="/app/checkout/enterprise" data-link class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors block">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Enterprise</p>
                                <p class="text-xs text-gray-400">5.000 creditos</p>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold text-gray-700">R$ 910</span>
                                <span class="block text-xs text-emerald-600 font-medium">-30%</span>
                            </div>
                        </a>
                    </div>

                    <a href="/app/creditos" data-link class="block mt-4 text-center text-xs text-blue-600 hover:text-blue-700 font-medium">
                        Ver todos os pacotes e historico de compras
                        <svg class="w-3 h-3 inline ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>

                {{-- Como funciona --}}
                <div class="pl-animate bg-white rounded-lg border border-gray-100 p-6" style="animation-delay: 0.5s">
                    <h3 class="text-xs uppercase tracking-wide text-gray-400 font-semibold mb-3">Como Funciona</h3>
                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">1</span>
                            <p>Modelo <span class="font-medium text-gray-800">pré-pago</span>: compre créditos e use quando precisar.</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">2</span>
                            <p>O custo varia conforme o <span class="font-medium text-gray-800">plano de consulta</span> escolhido.</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">3</span>
                            <p>Créditos <span class="font-medium text-gray-800">não expiram</span>. Use no seu ritmo.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.initPlano = function() {
    // CSS bar chart animations are handled via inline styles
    // Animate bars on load with a slight delay
    var bars = document.querySelectorAll('[data-bar-index]');
    bars.forEach(function(bar, i) {
        var targetWidth = bar.style.width;
        bar.style.width = '0%';
        setTimeout(function() {
            bar.style.width = targetWidth;
        }, 100 + (i * 80));
    });
};

// Auto-init if DOM already loaded
if (document.readyState !== 'loading') {
    window.initPlano();
} else {
    document.addEventListener('DOMContentLoaded', window.initPlano);
}
</script>
