@php
    $origemFiltro = $filtros['origem'] ?? '';
    $tipoFiltro = $filtros['tipo_operacao'] ?? '';
    $modeloFiltro = $filtros['modelo'] ?? '';
    $clienteFiltro = $filtros['cliente_id'] ?? '';
    $participanteFiltro = $filtros['participante_id'] ?? '';
    $buscaFiltro = $filtros['busca'] ?? '';
    $dataInicio = $filtros['data_inicio'] ?? '';
    $dataFim = $filtros['data_fim'] ?? '';
@endphp

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <style>
            @keyframes card-slide-in {
                from { opacity: 0; transform: translateY(60px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .dash-animate {
                opacity: 0;
                animation: card-slide-in 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            @media (prefers-reduced-motion: reduce) {
                .dash-animate { opacity: 1; animation: none; }
            }
        </style>

        {{-- Header --}}
        <div class="mb-4 sm:mb-8">
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Notas Fiscais</h1>
            <p class="mt-1 text-sm text-gray-500">Visualize todas as notas importadas via EFD ou XML</p>
        </div>

        {{-- KPIs — Linha 1: Operações --}}
        @php
            $ops = $kpis['operacoes'];
            $trib = $kpis['tributos'];
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-3 sm:mb-4">
            {{-- Entradas --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Entradas</p>
                        <p class="text-xl sm:text-3xl font-semibold whitespace-nowrap text-green-600">{{ number_format($ops['entradas']['quantidade'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 mt-1 sm:mt-2 truncate">R$ {{ number_format($ops['entradas']['valor'], 2, ',', '.') }}</p>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-green-50 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    </div>
                </div>
            </div>

            {{-- Saidas --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Saidas</p>
                        <p class="text-xl sm:text-3xl font-semibold whitespace-nowrap text-red-500">{{ number_format($ops['saidas']['quantidade'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 mt-1 sm:mt-2 truncate">R$ {{ number_format($ops['saidas']['valor'], 2, ',', '.') }}</p>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-red-50 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    </div>
                </div>
            </div>

            {{-- Devoluções --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Devoluções</p>
                        <p class="text-xl sm:text-3xl font-semibold whitespace-nowrap text-amber-500">{{ number_format($ops['devolucoes']['quantidade'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 mt-1 sm:mt-2 truncate">R$ {{ number_format($ops['devolucoes']['valor'], 2, ',', '.') }}</p>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-amber-50 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    </div>
                </div>
            </div>

            {{-- Total --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Total</p>
                        <p class="text-xl sm:text-3xl font-semibold whitespace-nowrap text-gray-900">{{ number_format($ops['total']['quantidade'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 mt-1 sm:mt-2 truncate">R$ {{ number_format($ops['total']['valor'], 2, ',', '.') }}</p>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-gray-100 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPIs — Linha 2: Tributário --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6 mb-3 sm:mb-4">
            {{-- ICMS --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.35s">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2 sm:mb-3">ICMS</p>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-green-600 font-medium">Credito</span>
                        <span class="text-sm font-semibold text-green-600 truncate ml-2">R$ {{ number_format($trib['icms']['credito'], 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-red-500 font-medium">Debito</span>
                        <span class="text-sm font-semibold text-red-500 truncate ml-2">R$ {{ number_format($trib['icms']['debito'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- PIS --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.4s">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2 sm:mb-3">PIS</p>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-green-600 font-medium">Credito</span>
                        <span class="text-sm font-semibold text-green-600 truncate ml-2">R$ {{ number_format($trib['pis']['credito'], 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-red-500 font-medium">Debito</span>
                        <span class="text-sm font-semibold text-red-500 truncate ml-2">R$ {{ number_format($trib['pis']['debito'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- COFINS --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.45s">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2 sm:mb-3">COFINS</p>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-green-600 font-medium">Credito</span>
                        <span class="text-sm font-semibold text-green-600 truncate ml-2">R$ {{ number_format($trib['cofins']['credito'], 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-red-500 font-medium">Debito</span>
                        <span class="text-sm font-semibold text-red-500 truncate ml-2">R$ {{ number_format($trib['cofins']['debito'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-lg border border-gray-200 mb-6 dash-animate" style="animation-delay: 0.6s">
            <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100 hidden sm:block">
                <h2 class="text-sm font-semibold text-gray-900">Filtros</h2>
            </div>
            <form id="nf-filtros-form">
                {{-- Toggle mobile --}}
                <button type="button" id="nf-filtros-toggle" class="sm:hidden w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-gray-700">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filtros
                        @if(array_filter($filtros))
                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-blue-600 rounded-full">{{ count(array_filter($filtros)) }}</span>
                        @endif
                    </span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 nf-filtros-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="nf-filtros-grid" class="hidden sm:block px-4 sm:px-5 py-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
                    {{-- Origem --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Origem</label>
                        <select name="origem" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="">Todas</option>
                            <option value="efd" {{ $origemFiltro === 'efd' ? 'selected' : '' }}>EFD</option>
                            <option value="xml" {{ $origemFiltro === 'xml' ? 'selected' : '' }}>XML</option>
                        </select>
                    </div>

                    {{-- Período início --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">De</label>
                        <input type="date" name="data_inicio" value="{{ $dataInicio }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- Período fim --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Até</label>
                        <input type="date" name="data_fim" value="{{ $dataFim }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- Tipo operação --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tipo</label>
                        <select name="tipo_operacao" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="">Todos</option>
                            <option value="entrada" {{ $tipoFiltro === 'entrada' ? 'selected' : '' }}>Entrada</option>
                            <option value="saida" {{ $tipoFiltro === 'saida' ? 'selected' : '' }}>Saída</option>
                        </select>
                    </div>

                    {{-- Modelo --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Modelo</label>
                        <select name="modelo" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="">Todos</option>
                            <option value="nfe" {{ $modeloFiltro === 'nfe' ? 'selected' : '' }}>NF-e</option>
                            <option value="cte" {{ $modeloFiltro === 'cte' ? 'selected' : '' }}>CT-e</option>
                            <option value="nfce" {{ $modeloFiltro === 'nfce' ? 'selected' : '' }}>NFC-e</option>
                            <option value="nfse" {{ $modeloFiltro === 'nfse' ? 'selected' : '' }}>NFS-e</option>
                        </select>
                    </div>

                    {{-- Cliente --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Cliente</label>
                        <select name="cliente_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="">Todos</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}" {{ $clienteFiltro == $c->id ? 'selected' : '' }}>{{ $c->razao_social }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Participante --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Participante</label>
                        <select name="participante_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="">Todos</option>
                            @foreach($participantes as $p)
                                <option value="{{ $p->id }}" {{ $participanteFiltro == $p->id ? 'selected' : '' }}>{{ $p->razao_social }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Busca + botões --}}
                <div class="flex flex-col sm:flex-row items-end gap-3 mt-3">
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Busca (chave ou número)</label>
                        <input type="text" name="busca" value="{{ $buscaFiltro }}" placeholder="Chave de acesso ou número da nota" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            Filtrar
                        </button>
                        <a href="/app/notas-fiscais" data-link class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                            Limpar
                        </a>
                    </div>
                </div>
                </div>
            </form>
        </div>

        {{-- Tabela (desktop) --}}
        @if($notas->total() > 0)
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden dash-animate" style="animation-delay: 0.7s">
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Origem</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número / Série</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modelo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emissão</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participante</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($notas as $n)
                        @php
                            $origemClass = $n['origem'] === 'efd' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700';
                            $origemLabel = strtoupper($n['origem']);
                            $tipoClass = $n['tipo_operacao'] === 'entrada' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                            $tipoLabel = $n['tipo_operacao'] === 'entrada' ? 'Entrada' : 'Saída';
                            $dataFormatada = $n['data_emissao'] ? \Carbon\Carbon::parse($n['data_emissao'])->format('d/m/Y') : '—';
                            $numero = $n['numero'] ?? '—';
                            $serie = $n['serie'] ? ' / ' . $n['serie'] : '';
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors nf-row" data-origem="{{ $n['origem'] }}" data-id="{{ $n['id'] }}">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $origemClass }}">{{ $origemLabel }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono whitespace-nowrap">
                                <a href="/app/notas-fiscais/{{ $n['origem'] }}/{{ $n['id'] }}" data-link class="text-gray-900 hover:text-blue-600 hover:underline transition-colors">{{ $numero }}{{ $serie }}</a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">{{ $n['modelo_label'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $dataFormatada }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $tipoClass }}">{{ $tipoLabel }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-xs truncate">
                                @if($n['participante_id'])
                                    <a href="/app/participante/{{ $n['participante_id'] }}" data-link class="hover:text-blue-600 hover:underline">
                                        <div class="truncate">{{ $n['participante_nome'] ?? '—' }}</div>
                                    </a>
                                @else
                                    <div class="truncate">{{ $n['participante_nome'] ?? '—' }}</div>
                                @endif
                                @if($n['participante_doc'])
                                <div class="text-xs font-mono text-gray-400">{{ $n['participante_doc'] }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-[10rem]">
                                @if($n['cliente_id'])
                                    <a href="/app/cliente/{{ $n['cliente_id'] }}" data-link class="hover:text-blue-600 hover:underline truncate block">{{ $n['cliente_nome'] ?? '—' }}</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right whitespace-nowrap">
                                R$ {{ number_format($n['valor_total'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" class="nf-expand-btn text-gray-400 hover:text-blue-600 transition-colors p-1" data-origem="{{ $n['origem'] }}" data-id="{{ $n['id'] }}" title="Ver detalhes">
                                    <svg class="w-5 h-5 nf-expand-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr class="nf-detail-row hidden" data-detail-for="{{ $n['origem'] }}-{{ $n['id'] }}">
                            <td colspan="9" class="px-0 py-0">
                                <div class="nf-detail-content bg-gray-50 border-t border-gray-100"></div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="md:hidden divide-y divide-gray-200">
                @foreach($notas as $n)
                @php
                    $origemClass = $n['origem'] === 'efd' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700';
                    $origemLabel = strtoupper($n['origem']);
                    $tipoClass = $n['tipo_operacao'] === 'entrada' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                    $tipoLabel = $n['tipo_operacao'] === 'entrada' ? 'Entrada' : 'Saída';
                    $dataFormatada = $n['data_emissao'] ? \Carbon\Carbon::parse($n['data_emissao'])->format('d/m/Y') : '—';
                    $numero = $n['numero'] ?? '—';
                    $serie = $n['serie'] ? ' / ' . $n['serie'] : '';
                @endphp
                <div class="px-4 py-4 nf-card" data-origem="{{ $n['origem'] }}" data-id="{{ $n['id'] }}">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $origemClass }}">{{ $origemLabel }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">{{ $n['modelo_label'] }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $tipoClass }}">{{ $tipoLabel }}</span>
                        </div>
                        <button type="button" class="nf-expand-btn text-gray-400 hover:text-blue-600 p-2 -mr-2 min-w-[40px] min-h-[40px] flex items-center justify-center" data-origem="{{ $n['origem'] }}" data-id="{{ $n['id'] }}">
                            <svg class="w-5 h-5 nf-expand-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                    <div class="flex items-baseline justify-between gap-2">
                        <a href="/app/notas-fiscais/{{ $n['origem'] }}/{{ $n['id'] }}" data-link class="text-sm font-mono font-medium text-gray-900 hover:text-blue-600 hover:underline transition-colors">{{ $numero }}{{ $serie }}</a>
                        <span class="text-sm font-medium text-gray-900">R$ {{ number_format($n['valor_total'], 2, ',', '.') }}</span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500">{{ $dataFormatada }}</div>
                    @if($n['participante_nome'])
                    <div class="mt-1 text-sm text-gray-700 truncate">
                        @if($n['participante_id'])
                            <a href="/app/participante/{{ $n['participante_id'] }}" data-link class="hover:text-blue-600 hover:underline">{{ $n['participante_nome'] }}</a>
                        @else
                            {{ $n['participante_nome'] }}
                        @endif
                    </div>
                    @endif
                    @if($n['cliente_nome'])
                    <div class="mt-0.5 text-xs text-gray-500 truncate">
                        @if($n['cliente_id'])
                            <a href="/app/cliente/{{ $n['cliente_id'] }}" data-link class="hover:text-blue-600 hover:underline">{{ $n['cliente_nome'] }}</a>
                        @else
                            {{ $n['cliente_nome'] }}
                        @endif
                    </div>
                    @endif
                    <div class="nf-mobile-detail hidden mt-3 bg-gray-50 rounded-lg border border-gray-100"></div>
                </div>
                @endforeach
            </div>

            {{-- Paginação --}}
            @if($notas->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-2">
                <p class="text-xs text-gray-500">
                    Mostrando {{ $notas->firstItem() }}-{{ $notas->lastItem() }} de {{ number_format($notas->total()) }}
                </p>
                <div class="flex items-center gap-1">
                    @if($notas->onFirstPage())
                        <span class="px-3 py-1.5 text-xs text-gray-400 bg-gray-100 rounded">Anterior</span>
                    @else
                        <a href="{{ $notas->previousPageUrl() }}" data-link class="px-3 py-1.5 text-xs text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">Anterior</a>
                    @endif

                    <span class="hidden sm:contents">
                    @foreach($notas->getUrlRange(max(1, $notas->currentPage() - 2), min($notas->lastPage(), $notas->currentPage() + 2)) as $p => $url)
                        @if($p == $notas->currentPage())
                            <span class="px-3 py-1.5 text-xs text-white bg-blue-600 rounded">{{ $p }}</span>
                        @else
                            <a href="{{ $url }}" data-link class="px-3 py-1.5 text-xs text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">{{ $p }}</a>
                        @endif
                    @endforeach
                    </span>
                    <span class="sm:hidden px-3 py-1.5 text-xs text-gray-500">{{ $notas->currentPage() }}/{{ $notas->lastPage() }}</span>

                    @if($notas->hasMorePages())
                        <a href="{{ $notas->nextPageUrl() }}" data-link class="px-3 py-1.5 text-xs text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">Próxima</a>
                    @else
                        <span class="px-3 py-1.5 text-xs text-gray-400 bg-gray-100 rounded">Próxima</span>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @else
        {{-- Empty state --}}
        <div class="bg-white rounded-lg border border-gray-200 px-6 py-12 text-center dash-animate" style="animation-delay: 0.7s">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-3 text-sm font-medium text-gray-900">Nenhuma nota encontrada</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(array_filter($filtros))
                    Tente ajustar os filtros para encontrar notas.
                @else
                    Importe arquivos EFD ou XML para visualizar notas fiscais aqui.
                @endif
            </p>
        </div>
        @endif

    </div>
</div>

<script>
(function() {
    // Cleanup de navegação SPA anterior
    if (window._cleanupFunctions && window._cleanupFunctions.notasFiscais) {
        window._cleanupFunctions.notasFiscais();
    }

    var cache = {};

    // Toggle filtros mobile
    var filtrosToggle = document.getElementById('nf-filtros-toggle');
    var filtrosGrid = document.getElementById('nf-filtros-grid');
    function handleFiltrosToggle() {
        var isHidden = filtrosGrid.classList.contains('hidden');
        if (isHidden) {
            filtrosGrid.classList.remove('hidden');
            filtrosToggle.querySelector('.nf-filtros-chevron').style.transform = 'rotate(180deg)';
        } else {
            filtrosGrid.classList.add('hidden');
            filtrosToggle.querySelector('.nf-filtros-chevron').style.transform = '';
        }
    }
    if (filtrosToggle) {
        filtrosToggle.addEventListener('click', handleFiltrosToggle);
    }

    function toggleDetail(origem, id, btnEl) {
        var key = origem + '-' + id;
        var icon = btnEl.querySelector('.nf-expand-icon');

        // Desktop
        var detailRow = document.querySelector('tr[data-detail-for="' + key + '"]');
        // Mobile
        var card = btnEl.closest('.nf-card');
        var mobileDetail = card ? card.querySelector('.nf-mobile-detail') : null;

        var target = mobileDetail || detailRow;
        if (!target) return;

        var isOpen = !target.classList.contains('hidden');

        if (isOpen) {
            target.classList.add('hidden');
            if (icon) icon.style.transform = '';
            return;
        }

        target.classList.remove('hidden');
        if (icon) icon.style.transform = 'rotate(180deg)';

        var contentEl = mobileDetail || (detailRow ? detailRow.querySelector('.nf-detail-content') : null);

        if (cache[key]) {
            contentEl.innerHTML = cache[key];
            return;
        }

        contentEl.innerHTML = '<div class="px-6 py-4 text-sm text-gray-500">Carregando...</div>';

        fetch('/app/notas-fiscais/' + origem + '/' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) {
            if (!r.ok) throw new Error('Erro ' + r.status);
            return r.text();
        })
        .then(function(html) {
            cache[key] = html;
            contentEl.innerHTML = html;
        })
        .catch(function(err) {
            contentEl.innerHTML = '<div class="px-6 py-4 text-sm text-red-500">Erro ao carregar detalhes.</div>';
        });
    }

    function handleExpandClick(e) {
        var btn = e.target.closest('.nf-expand-btn');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        toggleDetail(btn.dataset.origem, btn.dataset.id, btn);
    }

    document.addEventListener('click', handleExpandClick);

    // Filtros via form submit
    var form = document.getElementById('nf-filtros-form');
    function handleFormSubmit(e) {
        e.preventDefault();
        var params = new URLSearchParams();
        var formData = new FormData(form);
        formData.forEach(function(value, key) {
            if (value) params.set(key, value);
        });
        var url = '/app/notas-fiscais' + (params.toString() ? '?' + params.toString() : '');

        // SPA navigation
        var link = document.createElement('a');
        link.href = url;
        link.setAttribute('data-link', '');
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    // Registrar cleanup para SPA
    if (!window._cleanupFunctions) window._cleanupFunctions = {};
    window._cleanupFunctions.notasFiscais = function() {
        document.removeEventListener('click', handleExpandClick);
        if (form) form.removeEventListener('submit', handleFormSubmit);
        if (filtrosToggle) filtrosToggle.removeEventListener('click', handleFiltrosToggle);
    };
})();
</script>
