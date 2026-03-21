{{-- BI Fiscal - Dashboard --}}
<div class="min-h-screen bg-gray-50" id="bi-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        {{-- Page Header --}}
        <div class="mb-4 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">BI Fiscal</h1>
                    <p class="mt-1 text-sm text-gray-600">Analise o desempenho fiscal e tributário das suas operações.</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
                    {{-- Filtro de Cliente --}}
                    <select id="filtro-cliente" class="w-full sm:w-auto px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos os Clientes</option>
                        @foreach($clientes ?? [] as $cliente)
                            @if($cliente->is_empresa_propria)
                                <option value="{{ $cliente->id }}">★ {{ $cliente->nome }} (Minha Empresa)</option>
                            @else
                                <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                            @endif
                        @endforeach
                    </select>
                    {{-- Filtro de Periodo --}}
                    <select id="filtro-periodo" class="w-full sm:w-auto px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="0">Todos os períodos</option>
                        <option value="12">Últimos 12 meses</option>
                        <option value="6">Últimos 6 meses</option>
                        <option value="3">Últimos 3 meses</option>
                        <option value="1">Este mês</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- KPIs --}}
        @php
        $compactBrl = function(float $value): string {
            $abs = abs($value);
            $sign = $value < 0 ? '-' : '';
            if ($abs >= 1e9) return $sign . 'R$ ' . number_format($abs / 1e9, 1, ',', '.') . ' bi';
            if ($abs >= 1e6) return $sign . 'R$ ' . number_format($abs / 1e6, 1, ',', '.') . ' mi';
            if ($abs >= 1e4) return $sign . 'R$ ' . number_format($abs / 1e3, 1, ',', '.') . ' mil';
            return $sign . 'R$ ' . number_format($abs, 2, ',', '.');
        };
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-8">
            {{-- Total Vendas --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-6">
                <div class="flex items-center gap-2 sm:gap-4">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-12 sm:h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base sm:text-2xl font-bold text-gray-900 whitespace-nowrap" id="kpi-vendas">{{ $compactBrl($resumo['total_vendas'] ?? 0) }}</p>
                        <p class="text-xs sm:text-sm text-gray-500">Total Vendas</p>
                    </div>
                </div>
            </div>

            {{-- Total Compras --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-6">
                <div class="flex items-center gap-2 sm:gap-4">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-12 sm:h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base sm:text-2xl font-bold text-gray-900 whitespace-nowrap" id="kpi-compras">{{ $compactBrl($resumo['total_compras'] ?? 0) }}</p>
                        <p class="text-xs sm:text-sm text-gray-500">Total Compras</p>
                    </div>
                </div>
            </div>

            {{-- Total Tributos --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-6">
                <div class="flex items-center gap-2 sm:gap-4">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-12 sm:h-12 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base sm:text-2xl font-bold text-gray-900 whitespace-nowrap" id="kpi-tributos">{{ $compactBrl($resumo['total_tributos'] ?? 0) }}</p>
                        <p class="text-xs sm:text-sm text-gray-500">Total Tributos</p>
                    </div>
                </div>
            </div>

            {{-- Alíquota Média --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-6">
                <div class="flex items-center gap-2 sm:gap-4">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-12 sm:h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base sm:text-2xl font-bold text-gray-900 whitespace-nowrap" id="kpi-aliquota">{{ $resumo['aliquota_media'] ?? 0 }}%</p>
                        <p class="text-xs sm:text-sm text-gray-500">Alíquota Média</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPIs EFD --}}
        @php $efdTotal = ($resumoEfd['total_entradas_notas'] ?? 0) + ($resumoEfd['total_saidas_notas'] ?? 0); @endphp
        @if($efdTotal > 0)
        <div class="mb-4 sm:mb-8">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-3">EFD — Escrituração Fiscal Digital</p>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                {{-- Entradas --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-6">
                    <div class="flex items-center gap-2 sm:gap-4">
                        <div class="flex-shrink-0 w-8 h-8 sm:w-12 sm:h-12 rounded-lg bg-green-100 flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-base sm:text-2xl font-bold text-gray-900 whitespace-nowrap" id="kpi-efd-entradas">{{ $compactBrl($resumoEfd['total_entradas_valor'] ?? 0) }}</p>
                            <p class="text-xs sm:text-sm text-gray-500" id="kpi-efd-entradas-sub">{{ $resumoEfd['total_entradas_notas'] ?? 0 }} notas de entrada</p>
                        </div>
                    </div>
                </div>

                {{-- Saídas --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-6">
                    <div class="flex items-center gap-2 sm:gap-4">
                        <div class="flex-shrink-0 w-8 h-8 sm:w-12 sm:h-12 rounded-lg bg-red-100 flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-base sm:text-2xl font-bold text-gray-900 whitespace-nowrap" id="kpi-efd-saidas">{{ $compactBrl($resumoEfd['total_saidas_valor'] ?? 0) }}</p>
                            <p class="text-xs sm:text-sm text-gray-500" id="kpi-efd-saidas-sub">{{ $resumoEfd['total_saidas_notas'] ?? 0 }} notas de saída</p>
                        </div>
                    </div>
                </div>

                {{-- Saldo --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-6">
                    <div class="flex items-center gap-2 sm:gap-4">
                        <div class="flex-shrink-0 w-8 h-8 sm:w-12 sm:h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-base sm:text-2xl font-bold text-gray-900 whitespace-nowrap" id="kpi-efd-saldo">{{ $compactBrl($resumoEfd['saldo_liquido'] ?? 0) }}</p>
                            <p class="text-xs sm:text-sm text-gray-500">Saldo líquido</p>
                        </div>
                    </div>
                </div>

                {{-- Tributos --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-6">
                    <div class="flex items-center gap-2 sm:gap-4">
                        <div class="flex-shrink-0 w-8 h-8 sm:w-12 sm:h-12 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-base sm:text-2xl font-bold text-gray-900 whitespace-nowrap" id="kpi-efd-tributos">R$ 0,00</p>
                            <p class="text-xs sm:text-sm text-gray-500">Carga tributária</p>
                        </div>
                    </div>
                </div>

                {{-- Participantes --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-6">
                    <div class="flex items-center gap-2 sm:gap-4">
                        <div class="flex-shrink-0 w-8 h-8 sm:w-12 sm:h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-base sm:text-2xl font-bold text-gray-900 whitespace-nowrap" id="kpi-efd-participantes">{{ $resumoEfd['participantes_ativos'] ?? 0 }}</p>
                            <p class="text-xs sm:text-sm text-gray-500">Participantes ativos</p>
                        </div>
                    </div>
                </div>

                {{-- Risco --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-6">
                    <div class="flex items-center gap-2 sm:gap-4">
                        <div class="flex-shrink-0 w-8 h-8 sm:w-12 sm:h-12 rounded-lg bg-rose-100 flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-base sm:text-2xl font-bold text-gray-900 whitespace-nowrap" id="kpi-efd-risco">{{ $resumoEfd['notas_em_risco'] ?? 0 }}</p>
                            <p class="text-xs sm:text-sm text-gray-500">Notas em risco</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Tabs de Navegacao --}}
        @php
            $defaultTab = $defaultTab ?? 'faturamento';
            $tabClassMobile = fn($tab) => $tab === $defaultTab
                ? 'bi-tab active border-blue-500 text-blue-600 whitespace-nowrap py-3 sm:py-4 px-3 sm:px-1 border-b-2 font-medium text-sm'
                : 'bi-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 sm:py-4 px-3 sm:px-1 border-b-2 font-medium text-sm';
        @endphp
        <div class="mb-4 sm:mb-6" data-default-tab="{{ $defaultTab }}">
            <div class="border-b border-gray-200 scroll-fade-right sm:after:hidden">
                <nav class="-mb-px flex space-x-4 sm:space-x-8 overflow-x-auto scrollbar-hide tab-scroll-snap" aria-label="Tabs">
                    <button data-tab="faturamento" class="{{ $tabClassMobile('faturamento') }}">
                        Faturamento
                    </button>
                    <button data-tab="compras" class="{{ $tabClassMobile('compras') }}">
                        Compras
                    </button>
                    <button data-tab="tributos" class="{{ $tabClassMobile('tributos') }}">
                        Tributos
                    </button>
                    <button data-tab="efd" class="{{ $tabClassMobile('efd') }}">
                        EFD
                    </button>
                    <button data-tab="participantes" class="{{ $tabClassMobile('participantes') }}">
                        Participantes
                    </button>
                    <button data-tab="riscos" class="{{ $tabClassMobile('riscos') }}">
                        &#9888; Riscos
                    </button>
                    <button data-tab="tributario-efd" class="{{ $tabClassMobile('tributario-efd') }}">
                        Tributário EFD
                    </button>
                </nav>
            </div>
        </div>

        {{-- Tab Faturamento --}}
        <div id="tab-faturamento" class="bi-tab-content {{ $defaultTab !== 'faturamento' ? 'hidden' : '' }}">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Grafico Faturamento Mensal --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6 lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Faturamento Mensal</h3>
                    <div id="chart-faturamento" class="h-56 sm:h-72 lg:h-80"></div>
                </div>

                {{-- Top Clientes --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Clientes</h3>
                    <div id="chart-top-clientes" class="h-56 sm:h-72 lg:h-80"></div>
                </div>

                {{-- Faturamento por UF --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Faturamento por UF</h3>
                    <div id="chart-faturamento-uf" class="h-56 sm:h-72 lg:h-80"></div>
                </div>
            </div>
        </div>

        {{-- Tab Compras --}}
        <div id="tab-compras" class="bi-tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Entradas vs Saídas --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6 lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Entradas vs Saídas</h3>
                    <div id="chart-entradas-saidas" class="h-56 sm:h-72 lg:h-80"></div>
                </div>

                {{-- Top Fornecedores --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Fornecedores</h3>
                    <div id="chart-top-fornecedores" class="h-56 sm:h-72 lg:h-80"></div>
                </div>

                {{-- Devoluções --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Devoluções</h3>
                    <div id="chart-devolucoes" class="h-56 sm:h-72 lg:h-80"></div>
                </div>
            </div>
        </div>

        {{-- Tab Tributos --}}
        <div id="tab-tributos" class="bi-tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Carga Tributária --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6 lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Carga Tributária Mensal</h3>
                    <div id="chart-carga-tributaria" class="h-56 sm:h-72 lg:h-80"></div>
                </div>

                {{-- Tributos por Tipo --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Tributos por Tipo</h3>
                    <div id="chart-tributos-tipo" class="h-56 sm:h-72 lg:h-80"></div>
                </div>

                {{-- Alíquota Efetiva --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Evolução da Alíquota Efetiva</h3>
                    <div id="chart-aliquota-efetiva" class="h-56 sm:h-72 lg:h-80"></div>
                </div>
            </div>
        </div>

        {{-- Tab EFD --}}
        <div id="tab-efd" class="bi-tab-content {{ $defaultTab !== 'efd' ? 'hidden' : '' }}">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Fluxo Mensal --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6 lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Fluxo Mensal Entradas vs Saídas (EFD)</h3>
                    <div id="chart-efd-fluxo" class="h-56 sm:h-72 lg:h-80"></div>
                </div>

                {{-- Volume por Bloco --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Volume por Bloco EFD</h3>
                    <div id="chart-efd-blocos" class="h-56 sm:h-72 lg:h-80"></div>
                </div>

                {{-- Tributos por Tipo --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Tributos por Tipo (EFD)</h3>
                    <div id="chart-efd-tributos" class="h-56 sm:h-72 lg:h-80"></div>
                </div>

                {{-- Top Fornecedores --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Fornecedores (EFD)</h3>
                    <div id="chart-efd-fornecedores" class="h-56 sm:h-72 lg:h-80"></div>
                </div>

                {{-- Top Clientes --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Clientes (EFD)</h3>
                    <div id="chart-efd-clientes" class="h-56 sm:h-72 lg:h-80"></div>
                </div>
            </div>
        </div>

        {{-- Tab Participantes --}}
        <div id="tab-participantes" class="bi-tab-content hidden">
            {{-- Alertas de concentracao --}}
            <div id="concentracao-alertas" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6"></div>

            {{-- Toggle Fornecedores / Clientes --}}
            <div class="flex items-center gap-2 mb-4">
                <button id="btn-fornecedores"
                    class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 text-white shadow-sm">
                    Fornecedores
                </button>
                <button id="btn-clientes"
                    class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Clientes
                </button>
            </div>

            {{-- Tabela de ranking --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                <div class="overflow-x-auto scroll-fade-right-white">
                    <table class="min-w-[700px] w-full divide-y divide-gray-200 text-xs sm:text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left font-semibold text-gray-600">#</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left font-semibold text-gray-600">Participante</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-right font-semibold text-gray-600">Total</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-right font-semibold text-gray-600">Notas</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-right font-semibold text-gray-600">Ticket Médio</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-right font-semibold text-gray-600">% do Total</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-center font-semibold text-gray-600 sticky right-0 bg-gray-50">Ficha</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-participantes" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
                <div id="participantes-empty" class="hidden py-10 text-center text-gray-400 text-sm">
                    Nenhum participante encontrado no período.
                </div>
            </div>

            {{-- Ficha inline --}}
            <div id="ficha-participante" class="hidden bg-white rounded-xl border border-blue-200 shadow-sm p-4 sm:p-6 scroll-mt-4">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900" id="ficha-nome">—</h3>
                        <p class="text-sm text-gray-500 mt-0.5" id="ficha-cnpj">—</p>
                    </div>
                    <button id="fechar-ficha" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                </div>

                <div id="ficha-loading" class="hidden py-8 text-center text-gray-400 text-sm">Carregando...</div>

                <div id="ficha-content" class="hidden">
                    {{-- KPIs da ficha --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500 mb-1">Notas</p>
                            <p class="text-lg font-bold text-gray-900" id="ficha-total-notas">—</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500 mb-1">Entradas</p>
                            <p class="text-lg font-bold text-green-700" id="ficha-entradas">—</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500 mb-1">Saídas</p>
                            <p class="text-lg font-bold text-red-700" id="ficha-saidas">—</p>
                        </div>
                        <div class="bg-amber-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500 mb-1">Tributos</p>
                            <p class="text-lg font-bold text-amber-700" id="ficha-tributos">—</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500 mb-1">Ticket Médio</p>
                            <p class="text-lg font-bold text-blue-700" id="ficha-ticket">—</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500 mb-1">Última Consulta</p>
                            <p class="text-sm font-semibold text-purple-700" id="ficha-ultima-consulta">—</p>
                        </div>
                    </div>

                    {{-- Gráfico evolução --}}
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Evolução Mensal</h4>
                        <div id="chart-ficha-evolucao" class="h-48 sm:h-64"></div>
                    </div>

                    {{-- Últimas notas --}}
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Últimas Notas</h4>
                        <div class="overflow-x-auto scroll-fade-right-white">
                            <table class="min-w-[400px] w-full text-xs divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 sm:px-3 py-2 text-left text-gray-500">Data</th>
                                        <th class="px-2 sm:px-3 py-2 text-left text-gray-500">Tipo</th>
                                        <th class="px-2 sm:px-3 py-2 text-left text-gray-500">Bloco</th>
                                        <th class="px-2 sm:px-3 py-2 text-right text-gray-500">Valor</th>
                                    </tr>
                                </thead>
                                <tbody id="ficha-ultimas-notas" class="divide-y divide-gray-50"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Riscos --}}
        <div id="tab-riscos" class="bi-tab-content hidden">
            {{-- Score da Carteira --}}
            <div id="score-carteira" class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-5 text-center">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Participantes</p>
                    <p class="text-base sm:text-2xl font-bold text-gray-900" id="score-total-participantes">—</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-5 text-center">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Irregulares</p>
                    <p class="text-base sm:text-2xl font-bold text-gray-900" id="score-irregulares">—</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-5 text-center">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">% Regular</p>
                    <p class="text-base sm:text-2xl font-bold text-gray-900" id="score-percentual-regular">—</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 sm:p-5 text-center">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Valor em Risco</p>
                    <p class="text-base sm:text-2xl font-bold text-gray-900" id="score-valor-risco">—</p>
                </div>
            </div>

            {{-- Gap de Importações --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6 mb-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Gap de Importações (12 meses)</h3>
                <div id="gap-importacoes" class="overflow-x-auto scroll-fade-right-white"></div>
            </div>

            {{-- Fornecedores Irregulares --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6 mb-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Fornecedores/Participantes Irregulares</h3>
                <div id="tabela-irregulares-container" class="overflow-x-auto scroll-fade-right-white"></div>
            </div>

            {{-- Mudanças Recentes --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6 mb-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Mudanças Recentes de Cadastro (90 dias)</h3>
                <div id="tabela-mudancas-container" class="overflow-x-auto scroll-fade-right-white"></div>
            </div>

            {{-- Notas com Fornecedor Irregular --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6 mb-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Notas com Participante Irregular</h3>
                <div id="tabela-notas-risco-container" class="overflow-x-auto scroll-fade-right-white"></div>
            </div>
        </div>

        {{-- Tab Tributário EFD --}}
        <div id="tab-tributario-efd" class="bi-tab-content hidden">
            {{-- Consolidado Crédito vs Débito --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6 mb-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Consolidado Crédito vs Débito</h3>
                <div id="tabela-tributario-consolidado" class="overflow-x-auto scroll-fade-right-white"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Grafico Mensal --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Evolução Mensal de Tributos</h3>
                    <div id="chart-trib-mensal" class="h-56 sm:h-64 lg:h-72"></div>
                </div>

                {{-- Alíquota Efetiva --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Alíquota Efetiva Mensal (%)</h3>
                    <div id="chart-trib-aliquota" class="h-56 sm:h-64 lg:h-72"></div>
                </div>
            </div>

            {{-- Carga por Regime --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6 mb-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Carga Tributária por Regime</h3>
                <div id="tabela-trib-regime" class="overflow-x-auto scroll-fade-right-white"></div>
            </div>
        </div>

        {{-- Estado vazio --}}
        <div id="bi-empty" class="hidden">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum dado disponível</h3>
                <p class="mt-2 text-sm text-gray-500">Importe notas fiscais para visualizar as análises.</p>
                <a href="/app/importacao/xml" data-link class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Importar XMLs
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ApexCharts (local) --}}
<script src="/js/apexcharts.min.js"></script>
<script src="/js/bi.js"></script>
