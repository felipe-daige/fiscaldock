{{-- Comprar Creditos --}}
<div class="min-h-screen bg-gray-50" id="creditos-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <style>
            @keyframes cr-slide-in {
                from { opacity: 0; transform: translateY(60px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .cr-animate {
                opacity: 0;
                animation: cr-slide-in 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            @media (prefers-reduced-motion: reduce) {
                .cr-animate { opacity: 1; animation: none; }
            }
        </style>

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Comprar Creditos</h1>
            <p class="mt-1 text-sm text-gray-600">Adquira pacotes de creditos e acompanhe suas compras.</p>
        </div>

        {{-- KPI Strip --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            {{-- Saldo Atual --}}
            <div class="cr-animate bg-white rounded-lg border border-gray-100 border-t-2 border-t-emerald-500 p-6" style="animation-delay: 0.1s">
                <span class="text-xs uppercase tracking-wide text-gray-400 font-semibold">Saldo Atual</span>
                <div class="mt-2">
                    <span class="text-3xl font-bold {{ $saldoAtual > 0 ? 'text-emerald-600' : 'text-gray-400' }}">{{ number_format($saldoAtual, 0, ',', '.') }}</span>
                    <p class="text-sm text-gray-400 mt-0.5">creditos</p>
                </div>
            </div>

            {{-- Total Comprado --}}
            <div class="cr-animate bg-white rounded-lg border border-gray-100 border-t-2 border-t-blue-500 p-6" style="animation-delay: 0.15s">
                <span class="text-xs uppercase tracking-wide text-gray-400 font-semibold">Total Comprado</span>
                <div class="mt-2">
                    <span class="text-3xl font-bold text-blue-600">{{ number_format($totalComprado, 0, ',', '.') }}</span>
                    <p class="text-sm text-gray-400 mt-0.5">creditos</p>
                </div>
            </div>

            {{-- Total Consumido --}}
            <div class="cr-animate bg-white rounded-lg border border-gray-100 border-t-2 border-t-orange-500 p-6" style="animation-delay: 0.2s">
                <span class="text-xs uppercase tracking-wide text-gray-400 font-semibold">Total Consumido</span>
                <div class="mt-2">
                    <span class="text-3xl font-bold text-orange-600">{{ number_format($totalConsumido, 0, ',', '.') }}</span>
                    <p class="text-sm text-gray-400 mt-0.5">creditos</p>
                </div>
            </div>

            {{-- Ultima Compra --}}
            <div class="cr-animate bg-white rounded-lg border border-gray-100 border-t-2 border-t-gray-300 p-6" style="animation-delay: 0.25s">
                <span class="text-xs uppercase tracking-wide text-gray-400 font-semibold">Ultima Compra</span>
                <div class="mt-2">
                    @if($ultimaCompra)
                        <span class="text-3xl font-bold text-gray-700">+{{ number_format($ultimaCompra->amount, 0, ',', '.') }}</span>
                        <p class="text-sm text-gray-400 mt-0.5">creditos</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $ultimaCompra->created_at->format('d/m/Y') }}</p>
                    @else
                        <span class="text-3xl font-bold text-gray-300">--</span>
                        <p class="text-sm text-gray-400 mt-0.5">nenhuma compra</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pacotes Disponiveis --}}
        <div class="cr-animate mb-8" style="animation-delay: 0.3s">
            <h2 class="text-xs uppercase tracking-wide text-gray-400 font-semibold mb-4">Pacotes Disponiveis</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($pacotes as $pacote)
                <a href="/app/checkout/{{ $pacote['slug'] }}" data-link
                   class="bg-white rounded-lg border {{ $pacote['slug'] === 'business' ? 'border-2 border-blue-500' : 'border-gray-200 hover:border-blue-300' }} p-6 flex flex-col relative transition-colors group">

                    @if($pacote['slug'] === 'business')
                        <span class="absolute top-3 right-3 px-2 py-0.5 bg-blue-600 text-white text-[10px] uppercase font-bold rounded">Popular</span>
                    @endif

                    <h3 class="text-lg font-bold text-gray-900">{{ $pacote['nome'] }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ number_format($pacote['creditos'], 0, ',', '.') }} creditos</p>

                    <div class="mt-4 flex-1">
                        <span class="text-2xl font-bold text-gray-900">R$ {{ number_format($pacote['preco'], 0, ',', '.') }}</span>
                        @if($pacote['desconto'])
                            <span class="ml-2 text-sm font-semibold text-emerald-600">-{{ $pacote['desconto'] }}%</span>
                        @endif
                    </div>

                    <p class="text-xs text-gray-400 mt-2">R$ {{ number_format($pacote['preco'] / $pacote['creditos'], 2, ',', '.') }} / credito</p>

                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <span class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium rounded-lg
                            {{ $pacote['slug'] === 'business' ? 'bg-blue-600 text-white group-hover:bg-blue-700' : 'bg-gray-100 text-gray-700 group-hover:bg-blue-50 group-hover:text-blue-600' }}
                            transition-colors">
                            Comprar
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Historico de Compras --}}
        <div class="cr-animate bg-white rounded-lg border border-gray-100 p-6 mb-8" style="animation-delay: 0.4s">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs uppercase tracking-wide text-gray-400 font-semibold">Historico de Compras</h3>
                <a href="/app/plano" data-link class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                    Ver consumo detalhado
                    <svg class="w-3 h-3 inline ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>

            @if($historicoCompras->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Descricao</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Creditos</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Saldo Apos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($historicoCompras as $tx)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2.5 text-sm text-gray-700 whitespace-nowrap">
                                {{ $tx->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-3 py-2.5 text-sm">
                                @php
                                    $tipoBadge = match($tx->type) {
                                        'purchase' => ['Compra', 'blue'],
                                        'refund' => ['Reembolso', 'amber'],
                                        'manual_add' => ['Ajuste', 'purple'],
                                        default => [ucfirst($tx->type ?? 'Outro'), 'gray'],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $tipoBadge[1] }}-100 text-{{ $tipoBadge[1] }}-700">
                                    {{ $tipoBadge[0] }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-sm text-gray-600">
                                {{ $tx->description ?? '-' }}
                            </td>
                            <td class="px-3 py-2.5 text-sm font-semibold text-right text-emerald-600">
                                +{{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2.5 text-sm text-gray-500 text-right">
                                {{ $tx->balance_after !== null ? number_format($tx->balance_after, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path>
                </svg>
                <p class="text-sm text-gray-400">Nenhuma compra realizada ainda</p>
                <p class="text-xs text-gray-400 mt-1">Escolha um pacote acima para comecar</p>
            </div>
            @endif
        </div>

        {{-- Como Funciona --}}
        <div class="cr-animate" style="animation-delay: 0.5s">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg border border-gray-100 p-5 text-center">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-800">Pre-pago</p>
                    <p class="text-xs text-gray-400 mt-1">Compre e use quando precisar</p>
                </div>

                <div class="bg-white rounded-lg border border-gray-100 p-5 text-center">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-800">Consultas por CNPJ</p>
                    <p class="text-xs text-gray-400 mt-1">Custo varia por plano</p>
                </div>

                <div class="bg-white rounded-lg border border-gray-100 p-5 text-center">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-800">Plano Gratuito</p>
                    <p class="text-xs text-gray-400 mt-1">Consultas basicas sem custo extra</p>
                </div>

                <div class="bg-white rounded-lg border border-gray-100 p-5 text-center">
                    <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-800">Nao expiram</p>
                    <p class="text-xs text-gray-400 mt-1">Use no seu ritmo</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.initCreditos = function() {
    // Page is static, no special init needed
};

if (document.readyState !== 'loading') {
    window.initCreditos();
} else {
    document.addEventListener('DOMContentLoaded', window.initCreditos);
}
</script>
