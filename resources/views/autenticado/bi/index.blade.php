{{-- BI Fiscal - Dashboard --}}
<div class="min-h-screen bg-gray-50" id="bi-container">
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

        {{-- Page Header --}}
        <div class="mb-4 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">BI Fiscal</h1>
                    <p class="mt-1 text-sm text-gray-500">Analise o desempenho fiscal e tributário das suas operações.</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
                    {{-- Filtro de Cliente --}}
                    <select id="filtro-cliente" class="w-full sm:w-auto px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                    <select id="filtro-periodo" class="w-full sm:w-auto px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="0">Todos os períodos</option>
                        <option value="12">Últimos 12 meses</option>
                        <option value="6">Últimos 6 meses</option>
                        <option value="3">Últimos 3 meses</option>
                        <option value="1">Este mês</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- KPIs Consolidados --}}
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
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-4 sm:mb-6">
            {{-- Faturamento --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Faturamento</p>
                        <p class="text-xl sm:text-3xl font-semibold text-gray-900 truncate" id="kpi-faturamento">{{ $compactBrl($resumo['total_vendas'] ?? 0) }}</p>
                        <div class="mt-1 sm:mt-2 space-y-0.5">
                            <p class="text-xs text-gray-500" id="kpi-faturamento-notas">{{ number_format($resumo['total_notas'] ?? 0, 0, ',', '.') }} notas emitidas</p>
                            <p class="text-xs text-gray-500">Alíquota média: <span id="kpi-faturamento-aliquota" class="font-medium text-gray-700">{{ $resumo['aliquota_media'] ?? 0 }}%</span></p>
                        </div>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-emerald-50 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Aquisições --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Aquisições</p>
                        <p class="text-xl sm:text-3xl font-semibold text-gray-900 truncate" id="kpi-aquisicoes">{{ $compactBrl($resumo['total_compras'] ?? 0) }}</p>
                        <div class="mt-1 sm:mt-2 space-y-0.5">
                            <p class="text-xs text-gray-500" id="kpi-aquisicoes-notas">{{ number_format(($resumoEfd['total_entradas_notas'] ?? 0), 0, ',', '.') }} notas recebidas</p>
                            <p class="text-xs text-gray-500">Ticket médio: <span id="kpi-aquisicoes-ticket" class="font-medium text-gray-700">{{ $compactBrl($resumoEfd['ticket_medio'] ?? 0) }}</span></p>
                        </div>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-blue-50 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Tributação --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Tributação</p>
                        <p class="text-xl sm:text-3xl font-semibold text-gray-900 truncate" id="kpi-tributacao">{{ $compactBrl($resumo['total_tributos'] ?? 0) }}</p>
                        <div class="mt-1 sm:mt-2 space-y-0.5">
                            <p class="text-xs text-gray-500">Carga EFD: <span id="kpi-tributacao-efd" class="font-medium text-gray-700">{{ $compactBrl($resumoEfd['carga_tributaria'] ?? 0) }}</span></p>
                            <p class="text-xs text-gray-500">ICMS, PIS, COFINS</p>
                        </div>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-amber-50 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Saldo Líquido --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Saldo Líquido</p>
                        @php $saldoLiquido = ($resumoEfd['saldo_liquido'] ?? 0); @endphp
                        <p class="text-xl sm:text-3xl font-semibold truncate {{ $saldoLiquido >= 0 ? 'text-gray-900' : 'text-rose-600' }}" id="kpi-saldo">{{ $compactBrl($saldoLiquido) }}</p>
                        <div class="mt-1 sm:mt-2 space-y-0.5">
                            <p class="text-xs text-gray-500">Entradas: <span id="kpi-saldo-entradas" class="font-medium text-gray-700">{{ $compactBrl($resumoEfd['total_entradas_valor'] ?? 0) }}</span></p>
                            <p class="text-xs text-gray-500">Saídas: <span id="kpi-saldo-saidas" class="font-medium text-gray-700">{{ $compactBrl($resumoEfd['total_saidas_valor'] ?? 0) }}</span></p>
                        </div>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-gray-100 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barra de Métricas Secundárias --}}
        <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-4 mb-6 sm:mb-10 dash-animate" style="animation-delay: 0.45s">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-0 sm:divide-x sm:divide-gray-200">
                <div class="text-center px-2 sm:px-4">
                    <p class="text-xs text-gray-500">Participantes ativos</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5" id="kpi-sec-participantes">{{ $resumoEfd['participantes_ativos'] ?? 0 }}</p>
                </div>
                <div class="text-center px-2 sm:px-4">
                    <p class="text-xs text-gray-500">Notas em risco</p>
                    @php $notasRisco = $resumoEfd['notas_em_risco'] ?? 0; @endphp
                    <p class="text-sm font-semibold mt-0.5 {{ $notasRisco > 0 ? 'text-rose-600' : 'text-gray-900' }}" id="kpi-sec-risco">{{ $notasRisco }}</p>
                </div>
                <div class="text-center px-2 sm:px-4">
                    <p class="text-xs text-gray-500">Notas sem itens</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5" id="kpi-sec-sem-itens">{{ $resumoEfd['notas_sem_itens'] ?? 0 }}</p>
                </div>
                <div class="text-center px-2 sm:px-4">
                    <p class="text-xs text-gray-500">Fornecedores</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5" id="kpi-sec-fornecedores">{{ $resumo['total_fornecedores'] ?? 0 }}</p>
                </div>
            </div>
        </div>

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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                {{-- Grafico Faturamento Mensal --}}
                <div class="bg-white rounded-lg border border-gray-200 lg:col-span-2">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Faturamento Mensal</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-faturamento" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
                </div>

                {{-- Top Clientes --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Top 10 Clientes</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-top-clientes" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
                </div>

                {{-- Faturamento por UF --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Faturamento por UF</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-faturamento-uf" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Compras --}}
        <div id="tab-compras" class="bi-tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                {{-- Entradas vs Saídas --}}
                <div class="bg-white rounded-lg border border-gray-200 lg:col-span-2">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Entradas vs Saídas</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-entradas-saidas" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
                </div>

                {{-- Top Fornecedores --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Top 10 Fornecedores</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-top-fornecedores" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
                </div>

                {{-- Devoluções --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Devoluções</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-devolucoes" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Tributos --}}
        <div id="tab-tributos" class="bi-tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                {{-- Carga Tributária --}}
                <div class="bg-white rounded-lg border border-gray-200 lg:col-span-2">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Carga Tributária Mensal</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-carga-tributaria" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
                </div>

                {{-- Tributos por Tipo --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Tributos por Tipo</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-tributos-tipo" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
                </div>

                {{-- Alíquota Efetiva --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Evolução da Alíquota Efetiva</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-aliquota-efetiva" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab EFD --}}
        <div id="tab-efd" class="bi-tab-content {{ $defaultTab !== 'efd' ? 'hidden' : '' }}">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                {{-- Fluxo Mensal --}}
                <div class="bg-white rounded-lg border border-gray-200 lg:col-span-2">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Fluxo Mensal Entradas vs Saídas (EFD)</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-efd-fluxo" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
                </div>

                {{-- Volume por Bloco --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Volume por Bloco EFD</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-efd-blocos" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
                </div>

                {{-- Tributos por Tipo --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Tributos por Tipo (EFD)</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-efd-tributos" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
                </div>

                {{-- Top Fornecedores --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Top 10 Fornecedores (EFD)</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-efd-fornecedores" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
                </div>

                {{-- Top Clientes --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Top 10 Clientes (EFD)</h3>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div id="chart-efd-clientes" class="h-56 sm:h-72 lg:h-80"></div>
                    </div>
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
                    class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-sm font-medium bg-gray-900 text-white">
                    Fornecedores
                </button>
                <button id="btn-clientes"
                    class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                    Clientes
                </button>
            </div>

            {{-- Tabela de ranking --}}
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
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
            <div id="ficha-participante" class="hidden bg-white rounded-lg border border-gray-200 p-4 sm:p-6 scroll-mt-4">
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
                            <p class="text-lg font-semibold text-gray-900" id="ficha-total-notas">—</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500 mb-1">Entradas</p>
                            <p class="text-lg font-semibold text-gray-900" id="ficha-entradas">—</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500 mb-1">Saídas</p>
                            <p class="text-lg font-semibold text-gray-900" id="ficha-saidas">—</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500 mb-1">Tributos</p>
                            <p class="text-lg font-semibold text-gray-900" id="ficha-tributos">—</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500 mb-1">Ticket Médio</p>
                            <p class="text-lg font-semibold text-gray-900" id="ficha-ticket">—</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500 mb-1">Última Consulta</p>
                            <p class="text-sm font-semibold text-gray-900" id="ficha-ultima-consulta">—</p>
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
            <div id="score-carteira" class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-6 mb-6">
                <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-5 text-center">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Participantes</p>
                    <p class="text-xl sm:text-3xl font-semibold text-gray-900" id="score-total-participantes">—</p>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-5 text-center">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Irregulares</p>
                    <p class="text-xl sm:text-3xl font-semibold text-gray-900" id="score-irregulares">—</p>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-5 text-center">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">% Regular</p>
                    <p class="text-xl sm:text-3xl font-semibold text-gray-900" id="score-percentual-regular">—</p>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-5 text-center">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Valor em Risco</p>
                    <p class="text-xl sm:text-3xl font-semibold text-gray-900" id="score-valor-risco">—</p>
                </div>
            </div>

            {{-- Gap de Importações --}}
            <div class="bg-white rounded-lg border border-gray-200 mb-6">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Gap de Importações (12 meses)</h3>
                </div>
                <div class="p-4 sm:p-5">
                    <div id="gap-importacoes" class="overflow-x-auto scroll-fade-right-white"></div>
                </div>
            </div>

            {{-- Fornecedores Irregulares --}}
            <div class="bg-white rounded-lg border border-gray-200 mb-6">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Fornecedores/Participantes Irregulares</h3>
                </div>
                <div class="p-4 sm:p-5">
                    <div id="tabela-irregulares-container" class="overflow-x-auto scroll-fade-right-white"></div>
                </div>
            </div>

            {{-- Mudanças Recentes --}}
            <div class="bg-white rounded-lg border border-gray-200 mb-6">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Mudanças Recentes de Cadastro (90 dias)</h3>
                </div>
                <div class="p-4 sm:p-5">
                    <div id="tabela-mudancas-container" class="overflow-x-auto scroll-fade-right-white"></div>
                </div>
            </div>

            {{-- Notas com Fornecedor Irregular --}}
            <div class="bg-white rounded-lg border border-gray-200 mb-6">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Notas com Participante Irregular</h3>
                </div>
                <div class="p-4 sm:p-5">
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
