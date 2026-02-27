{{-- Monitoramento - Planos de Consulta --}}
<div class="min-h-screen bg-gray-50" id="monitoramento-planos-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Planos de Consulta</h1>
                    <p class="mt-1 text-sm text-gray-600">Escolha o nível de informação ideal para consultar fornecedores e parceiros.</p>
                </div>
                <a
                    href="/app/consultas/nova"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                    data-link
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
            </div>
        </div>

        {{-- Info Box --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-8">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-blue-900">Como funcionam os planos?</h3>
                    <p class="text-sm text-blue-800 mt-1">
                        Cada plano agrupa um conjunto de consultas. Quanto mais completo, mais informações você obtém sobre a regularidade fiscal, trabalhista e socioambiental dos seus parceiros. O custo é por CNPJ consultado.
                    </p>
                </div>
            </div>
        </div>

        {{-- Grid de Planos --}}
        @php
            // Metadata visual por codigo (mesmo padrao de avulso.blade.php)
            $planoMeta = [
                'gratuito' => [
                    'cor' => 'green',
                    'icone' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    'consultas_display' => ['Situação Cadastral (Ativa, Inapta, Baixada)', 'Dados Cadastrais Completos', 'CNAEs Principal e Secundários', 'Quadro Societário (QSA)'],
                    'casos_uso' => ['Checar se CNPJ está ativo', 'Conferir dados cadastrais', 'Consultar sócios e QSA'],
                ],
                'validacao' => [
                    'cor' => 'blue',
                    'icone' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    'consultas_display' => ['Situação Cadastral (Ativa, Inapta, Baixada)', 'Dados Cadastrais Completos', 'CNAEs Principal e Secundários', 'Quadro Societário (QSA)', 'Simples Nacional e MEI'],
                    'casos_uso' => ['Conferir regime tributário', 'Verificar se é MEI ou Simples', 'Qualificar novos fornecedores'],
                ],
                'licitacao' => [
                    'cor' => 'blue',
                    'icone' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'consultas_display' => ['Tudo do Validação', 'CND Federal (PGFN/RFB)'],
                    'casos_uso' => ['Documentação para editais', 'Verificar regularidade federal', 'Homologar fornecedores'],
                    'promo' => true,
                    'preco_original' => 4,
                ],
                'compliance' => [
                    'cor' => 'purple',
                    'icone' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                    'consultas_display' => [],
                    'casos_uso' => [],
                    'coming_soon' => true,
                ],
                'due_diligence' => [
                    'cor' => 'amber',
                    'icone' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7',
                    'consultas_display' => [],
                    'casos_uso' => [],
                    'coming_soon' => true,
                ],
                'enterprise' => [
                    'cor' => 'slate',
                    'icone' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
                    'consultas_display' => [],
                    'casos_uso' => [],
                    'coming_soon' => true,
                ],
            ];

            // Merge DB data + visual metadata (active plans + coming soon)
            $planosDetalhados = [];
            foreach ($planos->where('is_active', true) as $p) {
                $meta = $planoMeta[$p->codigo] ?? ['cor' => 'gray', 'icone' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'consultas_display' => [], 'casos_uso' => []];
                if ($meta['coming_soon'] ?? false) continue;
                $planosDetalhados[] = [
                    'codigo' => $p->codigo,
                    'nome' => $p->nome,
                    'creditos' => $p->custo_creditos,
                    'descricao' => $p->descricao,
                    'cor' => $meta['cor'],
                    'icone' => $meta['icone'],
                    'consultas' => $meta['consultas_display'],
                    'casos_uso' => $meta['casos_uso'],
                    'popular' => $p->codigo === 'licitacao',
                    'coming_soon' => $meta['coming_soon'] ?? false,
                    'gratuito' => $p->is_gratuito,
                    'promo' => $meta['promo'] ?? false,
                    'preco_original' => $meta['preco_original'] ?? null,
                ];
            }
            // Add coming soon plans from metadata (not in DB active)
            foreach (['compliance', 'due_diligence', 'enterprise'] as $comingSoonCodigo) {
                $meta = $planoMeta[$comingSoonCodigo];
                $dbPlano = $planos->firstWhere('codigo', $comingSoonCodigo);
                $planosDetalhados[] = [
                    'codigo' => $comingSoonCodigo,
                    'nome' => $dbPlano->nome ?? ucfirst(str_replace('_', ' ', $comingSoonCodigo)),
                    'creditos' => 0,
                    'descricao' => $dbPlano->descricao ?? '',
                    'cor' => $meta['cor'],
                    'icone' => $meta['icone'],
                    'consultas' => [],
                    'casos_uso' => [],
                    'popular' => false,
                    'coming_soon' => true,
                ];
            }

            $corClasses = [
                'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => 'text-green-600', 'badge' => 'bg-green-100 text-green-700', 'border' => 'border-green-200'],
                'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'text-blue-600', 'badge' => 'bg-blue-100 text-blue-700', 'border' => 'border-blue-200'],
                'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'icon' => 'text-purple-600', 'badge' => 'bg-purple-100 text-purple-700', 'border' => 'border-purple-200'],
                'amber' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => 'text-amber-600', 'badge' => 'bg-amber-100 text-amber-700', 'border' => 'border-amber-200'],
                'slate' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'icon' => 'text-slate-600', 'badge' => 'bg-slate-100 text-slate-700', 'border' => 'border-slate-200'],
                'gray' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'icon' => 'text-gray-600', 'badge' => 'bg-gray-100 text-gray-700', 'border' => 'border-gray-200'],
            ];
        @endphp

        <style>
            @keyframes card-slide-in {
                from {
                    opacity: 0;
                    transform: translateY(60px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .plan-card-animate {
                opacity: 0;
                animation: card-slide-in 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            @media (prefers-reduced-motion: reduce) {
                .plan-card-animate {
                    opacity: 1;
                    animation: none;
                }
            }
            .promo-card {
                background: linear-gradient(to bottom, #fffbeb, white 40%);
            }
        </style>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($planosDetalhados as $plano)
                @php $cores = $corClasses[$plano['cor']]; @endphp

                @if($plano['coming_soon'])
                    {{-- Coming Soon Card --}}
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col relative opacity-50 pointer-events-none plan-card-animate" style="animation-delay: {{ $loop->index * 0.12 }}s">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-600 shadow-sm">
                                Em breve
                            </span>
                        </div>
                        <div class="p-6 pt-7">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ $cores['bg'] }} flex items-center justify-center">
                                        <svg class="w-5 h-5 {{ $cores['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $plano['icone'] }}"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">{{ $plano['nome'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-5">{{ $plano['descricao'] }}</p>
                        </div>
                        <div class="mt-auto p-6 pt-0">
                            <button disabled class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gray-100 text-gray-400 text-sm font-semibold cursor-not-allowed">
                                Em breve
                            </button>
                        </div>
                    </div>
                @else
                    {{-- Active Plan Card --}}
                    <div class="{{ $plano['promo'] ? 'promo-card' : 'bg-white' }} rounded-xl border {{ $plano['promo'] ? 'border-2 border-amber-400 ring-2 ring-amber-100' : ($plano['popular'] ? 'border-2 border-blue-500 ring-2 ring-blue-100' : 'border-gray-200') }} shadow-sm flex flex-col relative hover:shadow-md transition-shadow plan-card-animate" style="animation-delay: {{ $loop->index * 0.12 }}s">
                        @if($plano['promo'])
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-600 text-white shadow-sm">
                                    Mais Popular
                                </span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500 text-white shadow-sm">
                                    25% OFF
                                </span>
                            </div>
                        @elseif($plano['popular'])
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-600 text-white shadow-sm">
                                    Mais Popular
                                </span>
                            </div>
                        @endif

                        <div class="p-6 {{ ($plano['popular'] || $plano['promo']) ? 'pt-7' : '' }}">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ $cores['bg'] }} flex items-center justify-center">
                                        <svg class="w-5 h-5 {{ $cores['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $plano['icone'] }}"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">{{ $plano['nome'] }}</h3>
                                        @if($plano['gratuito'])
                                            <span class="text-sm font-semibold text-green-600">Gratuito</span>
                                        @elseif($plano['promo'])
                                            <span class="text-sm text-gray-400 line-through">{{ $plano['preco_original'] }} créditos/CNPJ</span>
                                            <span class="text-sm font-semibold text-amber-600 ml-1">{{ $plano['creditos'] }} créditos/CNPJ</span>
                                        @else
                                            <span class="text-sm text-gray-500">{{ $plano['creditos'] }} créditos/CNPJ</span>
                                        @endif
                                    </div>
                                </div>
                                @if($plano['gratuito'])
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        Gratis
                                    </span>
                                @elseif($plano['promo'])
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                        {{ $plano['creditos'] }} cred.
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $cores['badge'] }}">
                                        {{ $plano['creditos'] }} cred.
                                    </span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-600 mb-5">{{ $plano['descricao'] }}</p>

                            @if($plano['promo'])
                                <div class="mb-5 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-xs font-semibold text-amber-800">Promocao: de {{ $plano['preco_original'] }} por {{ $plano['creditos'] }} creditos/CNPJ</span>
                                    </div>
                                </div>
                            @endif

                            {{-- Consultas Incluidas --}}
                            <div class="mb-5">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Consultas incluidas</p>
                                <ul class="space-y-1.5">
                                    @foreach($plano['consultas'] as $consulta)
                                        <li class="flex items-start gap-2 text-sm text-gray-700">
                                            <svg class="w-4 h-4 {{ $cores['icon'] }} mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span>{{ $consulta }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- Casos de Uso --}}
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Quando usar</p>
                                <ul class="space-y-1">
                                    @foreach($plano['casos_uso'] as $caso)
                                        <li class="flex items-start gap-2 text-xs text-gray-600">
                                            <svg class="w-3 h-3 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                            <span>{{ $caso }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="mt-auto p-6 pt-0">
                            <a
                                href="/app/consultas/nova"
                                data-link
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg {{ $plano['promo'] ? 'bg-amber-500 text-white hover:bg-amber-600' : ($plano['popular'] ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }} text-sm font-semibold transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Usar este plano
                            </a>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Calculadora de Custos --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Simule seu custo mensal</h2>
                        <p class="text-sm text-gray-600">Calcule quanto você gastaria monitorando seus participantes.</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                    {{-- Quantidade de CNPJs --}}
                    <div>
                        <label for="calc-cnpjs" class="block text-sm font-medium text-gray-700 mb-2">
                            Quantidade de CNPJs
                        </label>
                        <input
                            type="number"
                            id="calc-cnpjs"
                            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ex: 50"
                            min="1"
                            max="10000"
                            value="10"
                        >
                    </div>

                    {{-- Frequencia --}}
                    <div>
                        <label for="calc-frequencia" class="block text-sm font-medium text-gray-700 mb-2">
                            Frequencia de consulta
                        </label>
                        <select
                            id="calc-frequencia"
                            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="1">Mensal (1x por mes)</option>
                            <option value="2">Quinzenal (2x por mes)</option>
                            <option value="4">Semanal (4x por mes)</option>
                        </select>
                    </div>

                    {{-- Plano --}}
                    <div>
                        <label for="calc-plano" class="block text-sm font-medium text-gray-700 mb-2">
                            Plano escolhido
                        </label>
                        <select
                            id="calc-plano"
                            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            @foreach($planosDetalhados as $plano)
                                @if(!$plano['coming_soon'])
                                    <option value="{{ $plano['creditos'] }}" {{ $plano['codigo'] === 'licitacao' ? 'selected' : '' }}>
                                        {{ $plano['nome'] }} ({{ $plano['creditos'] }} cred.)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Resultado --}}
                    <div class="bg-blue-50 rounded-xl p-4 text-center border border-blue-100">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wider mb-1">Custo mensal</p>
                        <div class="flex items-baseline justify-center gap-1">
                            <span class="text-3xl font-bold text-gray-900" id="calc-creditos">100</span>
                            <span class="text-sm text-gray-600">creditos</span>
                        </div>
                    </div>
                </div>

                {{-- Dica --}}
                <div class="mt-4 flex items-start gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>O custo depende do plano escolhido e da quantidade de CNPJs. <a href="/app/creditos" data-link class="text-blue-600 hover:underline">Adquirir creditos</a></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    function initMonitoramentoPlanos() {
        const container = document.getElementById('monitoramento-planos-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Monitoramento Planos] Inicializando...');

        // Calculadora
        const calcCnpjs = document.getElementById('calc-cnpjs');
        const calcFrequencia = document.getElementById('calc-frequencia');
        const calcPlano = document.getElementById('calc-plano');
        const calcCreditos = document.getElementById('calc-creditos');

        function calcular() {
            const cnpjs = parseInt(calcCnpjs.value) || 0;
            const frequencia = parseInt(calcFrequencia.value) || 1;
            const plano = parseInt(calcPlano.value) || 0;

            const total = cnpjs * frequencia * plano;

            calcCreditos.textContent = total.toLocaleString('pt-BR');
        }

        if (calcCnpjs) {
            calcCnpjs.addEventListener('input', calcular);
        }
        if (calcFrequencia) {
            calcFrequencia.addEventListener('change', calcular);
        }
        if (calcPlano) {
            calcPlano.addEventListener('change', calcular);
        }

        // Calculo inicial
        calcular();

        console.log('[Monitoramento Planos] Inicializacao concluida');
    }

    // Expor globalmente para SPA
    window.initMonitoramentoPlanos = initMonitoramentoPlanos;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMonitoramentoPlanos, { once: true });
    } else {
        initMonitoramentoPlanos();
    }
})();
</script>
