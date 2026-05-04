@php
    $clientes = $clientes ?? collect();
    $filtros = $filtros ?? [];
    $kpis = $kpis ?? [];
    $participantes = $participantes ?? [];
    $regimes = $regimes ?? [];
    $situacoes = $situacoes ?? [];
    $temDados = ! empty($participantes);
@endphp

<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-8">
            <a href="/app/bi/dashboard" data-link class="inline-flex items-center gap-2 text-xs text-gray-600 hover:text-gray-900 hover:underline mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Voltar para BI Fiscal
            </a>
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Consultas CNPJ × EFD</h1>
            <p class="text-xs text-gray-500 mt-1">Cruza participantes consultados no `minhareceita.org` com a materialidade fiscal declarada nas EFDs.</p>
        </div>

        <form method="GET" action="/app/bi/consultas-efd" class="bg-white rounded border border-gray-300 overflow-hidden mb-6">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Filtros</span>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Data início</label>
                    <input type="date" name="data_inicio" value="{{ $filtros['data_inicio'] ?? '' }}" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:border-gray-500">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Data fim</label>
                    <input type="date" name="data_fim" value="{{ $filtros['data_fim'] ?? '' }}" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:border-gray-500">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Cliente</label>
                    <select name="cliente_id" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:border-gray-500">
                        <option value="">Todos</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}" {{ ($filtros['cliente_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->nome ?? $c->documento }}{{ $c->is_empresa_propria ? ' (própria)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-1.5 bg-gray-900 text-white rounded text-sm font-medium hover:bg-gray-700 transition-colors">Aplicar</button>
                    <a href="/app/bi/consultas-efd" class="px-4 py-1.5 bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded text-sm font-medium">Limpar</a>
                </div>
            </div>
        </form>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Indicadores operacionais</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-5 divide-x divide-gray-200">
                <div class="px-4 py-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Consultados</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format((int) ($kpis['participantes_consultados'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Com EFD</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format((int) ($kpis['participantes_com_movimentacao'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Sem EFD</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format((int) ($kpis['participantes_sem_movimentacao'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Valor EFD</p>
                    <p class="text-lg font-bold text-gray-900">R$ {{ number_format((float) ($kpis['valor_total_efd'] ?? 0), 2, ',', '.') }}</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Notas EFD</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format((int) ($kpis['total_notas_efd'] ?? 0), 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        @if(! $temDados)
            <div class="bg-white rounded border border-gray-300 p-6 text-center">
                <p class="text-sm text-gray-500">Nenhum participante consultado com movimentação EFD foi encontrado para os filtros atuais.</p>
            </div>
        @endif

        @if(! empty($regimes))
            <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Regime tributário × materialidade</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Regime</th>
                                <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Participantes</th>
                                <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Notas</th>
                                <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Valor</th>
                                <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Ticket/part.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($regimes as $linha)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $linha['regime'] }}</td>
                                    <td class="px-3 py-2 text-right font-mono text-sm text-gray-700">{{ $linha['participantes'] }}</td>
                                    <td class="px-3 py-2 text-right font-mono text-sm text-gray-700">{{ $linha['total_notas_efd'] }}</td>
                                    <td class="px-3 py-2 text-right font-mono text-sm text-gray-900">R$ {{ number_format($linha['valor_total_efd'], 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right font-mono text-sm text-gray-700">R$ {{ number_format($linha['ticket_medio_participante'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if(! empty($situacoes))
            <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Situação cadastral × impacto fiscal</span>
                </div>
                <div class="p-4 flex flex-wrap gap-2">
                    @foreach($situacoes as $linha)
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ strtoupper($linha['situacao']) === 'ATIVA' ? '#047857' : '#b45309' }}" title="{{ $linha['participantes'] }} participantes · R$ {{ number_format($linha['valor_total_efd'], 2, ',', '.') }}">
                            {{ $linha['situacao'] }} · {{ $linha['participantes'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        @if(! empty($participantes))
            <div class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Participantes consultados com impacto fiscal</span>
                    <span class="text-[10px] font-semibold text-gray-400">Ordenado por valor EFD</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Participante</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Regime</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Situação</th>
                                <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Entradas</th>
                                <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Saídas</th>
                                <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Notas</th>
                                <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Valor</th>
                                <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Parecer</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($participantes as $linha)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-3 py-2">
                                        <a href="{{ route('app.participante', $linha['participante_id']) }}" data-link class="block hover:underline text-gray-900">
                                            <div class="text-sm">{{ $linha['razao_social'] }}</div>
                                            <div class="text-[11px] text-gray-500 font-mono">{{ $linha['documento'] }}</div>
                                        </a>
                                        @if($linha['consultado_em'])
                                            <div class="text-[11px] text-gray-400">Consulta: {{ \Illuminate\Support\Carbon::parse($linha['consultado_em'])->format('d/m/Y H:i') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $linha['regime_tributario'] }}</td>
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $linha['situacao_irregular'] ? '#b45309' : '#047857' }}">
                                            {{ $linha['situacao_cadastral'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="font-mono text-sm text-gray-700">R$ {{ number_format($linha['valor_entradas'], 2, ',', '.') }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $linha['total_entradas'] }} notas</div>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="font-mono text-sm text-gray-700">R$ {{ number_format($linha['valor_saidas'], 2, ',', '.') }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $linha['total_saidas'] }} notas</div>
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono text-sm text-gray-700">{{ $linha['total_notas_efd'] }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="font-mono text-sm font-semibold text-gray-900">R$ {{ number_format($linha['valor_total_efd'], 2, ',', '.') }}</div>
                                        <div class="text-[11px] text-gray-400">Ticket R$ {{ number_format($linha['ticket_medio'], 2, ',', '.') }}</div>
                                    </td>
                                    <td class="px-3 py-2">
                                        @if(empty($linha['parecer_resumo']))
                                            <span class="text-[11px] text-gray-400">Sem destaque</span>
                                        @else
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($linha['parecer_resumo'] as $parecer)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $parecer['hex'] }}" title="{{ $parecer['tooltip'] }}">{{ $parecer['label'] }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
