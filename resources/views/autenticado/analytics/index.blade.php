{{-- BI Analytics - Dashboard --}}
<div class="min-h-screen bg-gray-50" id="analytics-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">BI Fiscal</h1>
                    <p class="mt-1 text-sm text-gray-600">Analise o desempenho fiscal e tributario das suas operacoes.</p>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Filtro de Cliente --}}
                    <select id="filtro-cliente" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos os Clientes</option>
                        @foreach($clientes ?? [] as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                        @endforeach
                    </select>
                    {{-- Filtro de Periodo --}}
                    <select id="filtro-periodo" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="12">Ultimos 12 meses</option>
                        <option value="6">Ultimos 6 meses</option>
                        <option value="3">Ultimos 3 meses</option>
                        <option value="1">Este mes</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            {{-- Total Vendas --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900" id="kpi-vendas">R$ {{ number_format($resumo['total_vendas'] ?? 0, 2, ',', '.') }}</p>
                        <p class="text-sm text-gray-500">Total Vendas</p>
                    </div>
                </div>
            </div>

            {{-- Total Compras --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900" id="kpi-compras">R$ {{ number_format($resumo['total_compras'] ?? 0, 2, ',', '.') }}</p>
                        <p class="text-sm text-gray-500">Total Compras</p>
                    </div>
                </div>
            </div>

            {{-- Total Tributos --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900" id="kpi-tributos">R$ {{ number_format($resumo['total_tributos'] ?? 0, 2, ',', '.') }}</p>
                        <p class="text-sm text-gray-500">Total Tributos</p>
                    </div>
                </div>
            </div>

            {{-- Aliquota Media --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900" id="kpi-aliquota">{{ $resumo['aliquota_media'] ?? 0 }}%</p>
                        <p class="text-sm text-gray-500">Aliquota Media</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs de Navegacao --}}
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button data-tab="faturamento" class="analytics-tab active border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Faturamento
                    </button>
                    <button data-tab="compras" class="analytics-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Compras
                    </button>
                    <button data-tab="tributos" class="analytics-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Tributos
                    </button>
                </nav>
            </div>
        </div>

        {{-- Tab Faturamento --}}
        <div id="tab-faturamento" class="analytics-tab-content">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Grafico Faturamento Mensal --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Faturamento Mensal</h3>
                    <div id="chart-faturamento" class="h-80"></div>
                </div>

                {{-- Top Clientes --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Clientes</h3>
                    <div id="chart-top-clientes" class="h-80"></div>
                </div>

                {{-- Faturamento por UF --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Faturamento por UF</h3>
                    <div id="chart-faturamento-uf" class="h-80"></div>
                </div>
            </div>
        </div>

        {{-- Tab Compras --}}
        <div id="tab-compras" class="analytics-tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Entradas vs Saidas --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Entradas vs Saidas</h3>
                    <div id="chart-entradas-saidas" class="h-80"></div>
                </div>

                {{-- Top Fornecedores --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Fornecedores</h3>
                    <div id="chart-top-fornecedores" class="h-80"></div>
                </div>

                {{-- Devolucoes --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Devolucoes</h3>
                    <div id="chart-devolucoes" class="h-80"></div>
                </div>
            </div>
        </div>

        {{-- Tab Tributos --}}
        <div id="tab-tributos" class="analytics-tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Carga Tributaria --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Carga Tributaria Mensal</h3>
                    <div id="chart-carga-tributaria" class="h-80"></div>
                </div>

                {{-- Tributos por Tipo --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Tributos por Tipo</h3>
                    <div id="chart-tributos-tipo" class="h-80"></div>
                </div>

                {{-- Aliquota Efetiva --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Evolucao da Aliquota Efetiva</h3>
                    <div id="chart-aliquota-efetiva" class="h-80"></div>
                </div>
            </div>
        </div>

        {{-- Estado vazio --}}
        <div id="analytics-empty" class="hidden">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum dado disponivel</h3>
                <p class="mt-2 text-sm text-gray-500">Importe notas fiscais para visualizar as analises.</p>
                <a href="/app/monitoramento/xml" data-link class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Importar XMLs
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ApexCharts CDN --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="{{ asset('js/analytics.js') }}"></script>
