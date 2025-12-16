{{-- Dashboard - Painel de Controle Contábil Rubi --}}
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        Olá, {{ Auth::user()->name ?? 'Contador' }}! 👋
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ now()->format('d/m/Y') }} - {{ now()->format('H:i') }}
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Sincronizado há {{ isset($ultima_sincronizacao) ? $ultima_sincronizacao->diffForHumans() : 'agora' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- KPIs Cards Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Card 1: Risco Fiscal --}}
            <div class="bg-white rounded-lg shadow-md border-l-4 border-red-500 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Risco Fiscal</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ $kpi_cnd_risco ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">CNDs vencidas ou vencendo</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 2: Pendência de Processamento --}}
            <div class="bg-white rounded-lg shadow-md border-l-4 border-blue-500 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pendência XML</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">{{ $kpi_xml_pendentes ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">Documentos não processados</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 3: Obrigações SPED --}}
            <div class="bg-white rounded-lg shadow-md border-l-4 border-purple-500 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Obrigações SPED</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2">{{ $kpi_sped_pendentes ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">Pendentes no mês</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 4: Carteira Ativa --}}
            <div class="bg-white rounded-lg shadow-md border-l-4 border-green-500 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Carteira Ativa</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ $total_empresas ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">Empresas monitoradas</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabela RAF Section --}}
        <div class="bg-white rounded-lg shadow-sm mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Monitoramento em Tempo Real (RAF)</h2>
                <p class="text-sm text-gray-500 mt-1">Regime Tributário e Status CND das empresas</p>
            </div>

            {{-- Tabela Responsiva --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Empresa
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Regime Tributário
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status CND
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Última Importação XML
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Conciliação
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse(($monitoramento_empresas ?? []) as $empresa)
                            <tr class="hover:bg-gray-50 transition-colors">
                                {{-- Coluna Empresa --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $empresa['nome'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $empresa['cnpj'] }}</div>
                                    </div>
                                </td>

                                {{-- Coluna Regime Tributário --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $regimeClasses = [
                                            'Simples Nacional' => 'bg-blue-100 text-blue-800',
                                            'Lucro Presumido' => 'bg-orange-100 text-orange-800',
                                            'Lucro Real' => 'bg-blue-200 text-blue-900',
                                        ];
                                        $regimeClass = $regimeClasses[$empresa['regime']] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $regimeClass }}">
                                        {{ $empresa['regime'] }}
                                    </span>
                                </td>

                                {{-- Coluna Status CND --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($empresa['cnd_status'] === 'regular')
                                        <div class="flex items-center gap-2">
                                            <span class="text-green-500 text-lg">✅</span>
                                            <span class="text-sm font-medium text-green-700">Regular</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Vence em {{ \Carbon\Carbon::parse($empresa['cnd_vencimento'])->format('d/m/Y') }}
                                        </div>
                                    @elseif($empresa['cnd_status'] === 'warning')
                                        <div class="flex items-center gap-2">
                                            <span class="text-yellow-500 text-lg">⚠️</span>
                                            <span class="text-sm font-medium text-yellow-700">Vence em breve</span>
                                        </div>
                                        <div class="text-xs text-yellow-600 mt-1 font-semibold">
                                            Vence em {{ \Carbon\Carbon::parse($empresa['cnd_vencimento'])->format('d/m/Y') }}
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="text-red-500 text-lg">🚫</span>
                                            <span class="text-sm font-bold text-red-700">Irregular/Vencida</span>
                                        </div>
                                        <div class="text-xs text-red-600 mt-1 font-bold">
                                            Venceu em {{ \Carbon\Carbon::parse($empresa['cnd_vencimento'])->format('d/m/Y') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Coluna Última Importação XML --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($empresa['ultima_importacao'])
                                        <div class="text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($empresa['ultima_importacao'])->diffForHumans() }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($empresa['ultima_importacao'])->format('d/m/Y H:i') }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">Nunca importado</span>
                                    @endif
                                </td>

                                {{-- Coluna Conciliação --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[100px]">
                                            <div 
                                                class="h-2 rounded-full transition-all {{ $empresa['conciliacao_pct'] >= 90 ? 'bg-green-500' : ($empresa['conciliacao_pct'] >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                style="width: {{ $empresa['conciliacao_pct'] }}%"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700">{{ $empresa['conciliacao_pct'] }}%</span>
                                    </div>
                                    @if($empresa['xml_pendentes'] > 0)
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $empresa['xml_pendentes'] }} pendente(s)
                                        </div>
                                    @endif
                                </td>

                                {{-- Coluna Ações --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-900 flex items-center gap-1 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Ver Detalhes
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="text-gray-400">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="mt-2 text-sm">Nenhuma empresa cadastrada</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Quick Actions Widget --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Card: Importar Documentos --}}
            <a href="#" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-all hover:scale-105 border border-gray-200 group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">
                            Importar Documentos
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Envie arquivos XML para processamento</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>

            {{-- Card: Conciliar Banco --}}
            <a href="#" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-all hover:scale-105 border border-gray-200 group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800 group-hover:text-green-600 transition-colors">
                            Conciliar Banco
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Concilie extratos bancários</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>

            {{-- Card: Auditor SPED --}}
            <a href="#" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-all hover:scale-105 border border-gray-200 group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800 group-hover:text-purple-600 transition-colors">
                            Auditor SPED
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Valide arquivos SPED</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
        </div>
    </div>
</div>
