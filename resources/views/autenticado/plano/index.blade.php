@php
    $pacotes = [
        ['slug' => 'starter', 'nome' => 'Starter', 'creditos' => 100, 'preco' => 26.00, 'desconto' => null],
        ['slug' => 'growth', 'nome' => 'Growth', 'creditos' => 500, 'preco' => 117.00, 'desconto' => 10],
        ['slug' => 'business', 'nome' => 'Business', 'creditos' => 1000, 'preco' => 208.00, 'desconto' => 20],
        ['slug' => 'enterprise', 'nome' => 'Enterprise', 'creditos' => 5000, 'preco' => 910.00, 'desconto' => 30],
    ];
@endphp

<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">

        <div>
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Meu Plano</h1>
            <p class="text-xs text-gray-500 mt-1">Resumo operacional do consumo de créditos.</p>
        </div>

        @if(($trialResumo['is_active'] ?? false) || ($trialResumo['is_expired'] ?? false))
            <div class="bg-white rounded border border-gray-300 p-4 border-l-4 {{ ($trialResumo['is_active'] ?? false) ? 'border-l-blue-500' : 'border-l-amber-500' }}">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Créditos promocionais</p>
                @if($trialResumo['is_active'] ?? false)
                    <p class="mt-2 text-sm text-gray-700">
                        Você recebeu {{ number_format($trialResumo['granted'] ?? 0, 0, ',', '.') }} créditos grátis.
                        Restam {{ number_format($trialResumo['remaining'] ?? 0, 0, ',', '.') }} até {{ optional($trialResumo['expires_at'])->format('d/m/Y H:i') }}.
                    </p>
                @else
                    <p class="mt-2 text-sm text-gray-700">
                        O trial expirou em {{ optional($trialResumo['expires_at'])->format('d/m/Y H:i') }}.
                        Créditos comprados continuam válidos normalmente.
                    </p>
                @endif
            </div>
        @endif

        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-200">
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Saldo atual</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ number_format($saldoAtual, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">créditos disponíveis</p>
                </div>
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Usados no mês</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ number_format($creditosUsadosMes, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">créditos</p>
                </div>
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Consultas no mês</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $consultasMes }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">lotes executados</p>
                </div>
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Média / consulta</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $mediaCreditos }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">créditos por lote</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Consumo mensal (últimos 6 meses)</span>
                    </div>
                    <div class="p-6 space-y-3">
                        @foreach($consumoMensal as $mes)
                            @php
                                $width = $maxConsumo > 0 ? min(100, max(0, ($mes['valor'] / $maxConsumo) * 100)) : 0;
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-[11px] text-gray-500 w-16 text-right font-mono">{{ $mes['label'] }}</span>
                                <div class="flex-1 bg-gray-100 rounded-full h-6 overflow-hidden">
                                    <div class="h-6 rounded-full" style="width: {{ $width }}%; background-color: #374151"></div>
                                </div>
                                <span class="text-[11px] text-gray-500 font-mono">{{ number_format($mes['valor'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                        @if($maxConsumo === 0)
                            <p class="text-sm text-gray-400 text-center">Nenhum consumo registrado nos últimos 6 meses.</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Histórico de consumo</span>
                        <a href="/app/creditos" data-link class="text-xs text-gray-600 hover:text-gray-900 hover:underline">Ver pacotes</a>
                    </div>
                    @if($ultimasTransacoes->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-gray-300">
                                        <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Data</th>
                                        <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Descrição</th>
                                        <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Plano</th>
                                        <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Participantes</th>
                                        <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Créditos</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($ultimasTransacoes as $tx)
                                        @php
                                            $statusHex = match($tx->status) {
                                                'concluido' => '#047857',
                                                'processando' => '#d97706',
                                                'erro' => '#dc2626',
                                                default => '#9ca3af',
                                            };
                                            $statusLabel = match($tx->status) {
                                                'concluido' => 'Concluído',
                                                'processando' => 'Processando',
                                                'pendente' => 'Pendente',
                                                'erro' => 'Erro',
                                                default => ucfirst($tx->status ?: '—'),
                                            };
                                        @endphp
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-3 py-2.5 text-sm text-gray-700 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-3 py-2.5 text-sm text-gray-700">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusHex }}">{{ $statusLabel }}</span>
                                                <span class="ml-2">Consulta em Lote #{{ $tx->id }}</span>
                                            </td>
                                            <td class="px-3 py-2.5 text-sm text-gray-600">{{ $tx->plano->nome ?? '-' }}</td>
                                            <td class="px-3 py-2.5 text-sm text-gray-700 text-center">{{ $tx->total_participantes ?? '-' }}</td>
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
                        <div class="p-6 text-center text-sm text-gray-500 space-y-2">
                            <p>Nenhuma consulta realizada ainda.</p>
                            <a href="/app/consulta/nova" data-link class="text-xs text-gray-600 hover:text-gray-900 hover:underline">Iniciar primeira consulta</a>
                        </div>
                    @endif
                </div>

            </div>

            <div class="space-y-6">
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Saldo disponível</span>
                    </div>
                    <div class="p-6 space-y-1 text-center">
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($saldoAtual, 0, ',', '.') }}</p>
                        <p class="text-sm text-gray-500">Créditos liberados para consultas</p>
                    </div>
                </div>

                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Comprar créditos</span>
                        <a href="/app/creditos" data-link class="text-xs text-gray-600 hover:text-gray-900 hover:underline" aria-label="Ver todos os pacotes">Ver todos</a>
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($pacotes as $pacote)
                            <a href="/app/checkout/{{ $pacote['slug'] }}" data-link class="block border border-gray-200 rounded px-4 py-3 transition-colors hover:border-gray-400">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $pacote['nome'] }}</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">{{ number_format($pacote['creditos'], 0, ',', '.') }} créditos</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-gray-900">R$ {{ number_format($pacote['preco'], 0, ',', '.') }}</p>
                                        @if(! empty($pacote['desconto']))
                                            <p class="text-[11px] font-semibold" style="color: #047857">-{{ $pacote['desconto'] }}%</p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Como funciona</span>
                    </div>
                    <div class="p-6 space-y-4 text-sm text-gray-700">
                        <div class="flex items-start gap-3">
                            <span class="text-[10px] font-bold uppercase tracking-wide text-white rounded px-2 py-0.5" style="background-color: #374151">1</span>
                            <p>Créditos são pré-pagos e ficam disponíveis instantaneamente após a compra.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-[10px] font-bold uppercase tracking-wide text-white rounded px-2 py-0.5" style="background-color: #374151">2</span>
                            <p>O consumo varia conforme o plano da consulta; acompanhe abaixo o uso mensal.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-[10px] font-bold uppercase tracking-wide text-white rounded px-2 py-0.5" style="background-color: #374151">3</span>
                            <p>Créditos comprados não expiram; créditos promocionais do trial expiram ao fim do período informado.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
