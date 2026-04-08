@php
    $tipoBadgeMap = [
        'purchase' => ['label' => 'Compra', 'hex' => '#047857'],
        'refund' => ['label' => 'Reembolso', 'hex' => '#d97706'],
        'manual_add' => ['label' => 'Ajuste', 'hex' => '#4338ca'],
    ];
@endphp

<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">

        <div>
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Comprar créditos</h1>
            <p class="text-xs text-gray-500 mt-1">Pacotes disponíveis e histórico de compras.</p>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-200">
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Saldo atual</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ number_format($saldoAtual, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">créditos</p>
                </div>
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Total comprado</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ number_format($totalComprado, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">créditos</p>
                </div>
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Total consumido</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ number_format($totalConsumido, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">créditos</p>
                </div>
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Última compra</p>
                    @if($ultimaCompra)
                        <p class="text-lg sm:text-xl font-bold text-gray-900">+{{ number_format($ultimaCompra->amount, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-gray-500 mt-1">{{ $ultimaCompra->created_at->format('d/m/Y') }}</p>
                    @else
                        <p class="text-lg sm:text-xl font-bold text-gray-900">--</p>
                        <p class="text-[11px] text-gray-500 mt-1">nenhuma compra</p>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Pacotes disponíveis</h2>
                <a href="/app/plano" data-link class="text-xs text-gray-600 hover:text-gray-900 hover:underline">Gerenciar plano</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($pacotes as $pacote)
                    <div class="bg-white border border-gray-200 rounded overflow-hidden flex flex-col h-full">
                        <div class="px-4 py-5 space-y-2 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-900">{{ $pacote['nome'] }}</p>
                                @if($pacote['slug'] === 'business')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #0f766e">Popular</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-gray-400">{{ number_format($pacote['creditos'], 0, ',', '.') }} créditos</p>
                            <p class="text-2xl font-bold text-gray-900">R$ {{ number_format($pacote['preco'], 0, ',', '.') }}</p>
                            @if(! empty($pacote['desconto']))
                                <p class="text-[11px] font-semibold" style="color: #047857">-{{ $pacote['desconto'] }}%</p>
                            @endif
                        </div>
                        <div class="px-4 py-4 border-t border-gray-100 mt-auto">
                            <a href="/app/checkout/{{ $pacote['slug'] }}" data-link class="w-full inline-flex items-center justify-center px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-white rounded" style="background-color: #1f2937">Comprar</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Histórico de compras</span>
                <a href="/app/plano" data-link class="text-xs text-gray-600 hover:text-gray-900 hover:underline">Ver consumo detalhado</a>
            </div>
            @if($historicoCompras->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Data</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Tipo</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Descrição</th>
                                <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Créditos</th>
                                <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Saldo após</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($historicoCompras as $tx)
                                @php
                                    $badge = $tipoBadgeMap[$tx->type] ?? ['label' => ucfirst($tx->type ?? 'Outro'), 'hex' => '#9ca3af'];
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-3 py-2.5 text-sm text-gray-700 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-3 py-2.5 text-sm">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $badge['hex'] }}">{{ $badge['label'] }}</span>
                                    </td>
                                    <td class="px-3 py-2.5 text-sm text-gray-600">{{ $tx->description ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-sm text-right font-semibold text-emerald-600">+{{ number_format($tx->amount, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2.5 text-sm text-right text-gray-500">{{ $tx->balance_after !== null ? number_format($tx->balance_after, 0, ',', '.') : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-center text-sm text-gray-500 space-y-2">
                    <p>Nenhuma compra realizada ainda.</p>
                    <p class="text-xs text-gray-400">Escolha um pacote acima para começar.</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded border border-gray-300 p-4 text-center space-y-2">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Pre-pago</p>
                <p class="text-sm font-semibold text-gray-900">Compre e use quando precisar.</p>
            </div>
            <div class="bg-white rounded border border-gray-300 p-4 text-center space-y-2">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Consultas por CNPJ</p>
                <p class="text-sm font-semibold text-gray-900">Custo varia por plano.</p>
            </div>
            <div class="bg-white rounded border border-gray-300 p-4 text-center space-y-2">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Sem expiração</p>
                <p class="text-sm font-semibold text-gray-900">Use os créditos no seu ritmo.</p>
            </div>
            <div class="bg-white rounded border border-gray-300 p-4 text-center space-y-2">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Transparência</p>
                <p class="text-sm font-semibold text-gray-900">Histórico completo em tabela.</p>
            </div>
        </div>

    </div>
</div>
