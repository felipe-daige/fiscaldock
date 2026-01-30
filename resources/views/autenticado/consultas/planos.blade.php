{{-- Consultas - Planos Disponiveis --}}
<div class="min-h-screen bg-gray-50" id="consultas-planos-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Planos de Consulta</h1>
                    <p class="mt-1 text-sm text-gray-600">Escolha o nivel de informacao ideal para consultar fornecedores e parceiros.</p>
                </div>
                <a
                    href="/app/consultas/nova"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold"
                    data-link
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Nova Consulta
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
                        Cada plano agrupa um conjunto de consultas. Quanto mais completo, mais informacoes voce obtem sobre a regularidade fiscal, trabalhista e socioambiental dos seus parceiros. O custo e por CNPJ consultado.
                    </p>
                </div>
            </div>
        </div>

        {{-- Grid de Planos --}}
        @php
            $planos = [
                [
                    'codigo' => 'gratuito',
                    'nome' => 'Gratuito',
                    'creditos' => 0,
                    'gratuito' => true,
                    'popular' => false,
                    'cor' => 'green',
                    'icone' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    'descricao' => 'Validacao rapida de dados cadastrais e regime tributario.',
                    'consultas' => [
                        'Situacao Cadastral (Ativa, Inapta, Baixada)',
                        'Dados Cadastrais Completos',
                        'CNAEs Principal e Secundarios',
                        'Quadro Societario (QSA)',
                        'Simples Nacional e MEI',
                    ],
                    'casos_uso' => [
                        'Validar se CNPJ existe e esta ativo',
                        'Conferir regime tributario para emissao de NF',
                        'Identificar socios antes de negociar',
                    ],
                ],
                [
                    'codigo' => 'validacao',
                    'nome' => 'Validacao',
                    'creditos' => 4,
                    'gratuito' => false,
                    'popular' => false,
                    'cor' => 'blue',
                    'icone' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    'descricao' => 'Inclui inscricao estadual e verificacao em listas restritivas.',
                    'consultas' => [
                        'Tudo do Gratuito',
                        'SINTEGRA (Inscricao Estadual)',
                        'TCU Consolidada (CEIS, CNEP, CNJ)',
                    ],
                    'casos_uso' => [
                        'Validar IE para operacoes interestaduais',
                        'Verificar se empresa esta em lista de impedidos',
                        'Qualificar fornecedores antes de cadastrar',
                    ],
                ],
                [
                    'codigo' => 'licitacao',
                    'nome' => 'Licitacao',
                    'creditos' => 10,
                    'gratuito' => false,
                    'popular' => true,
                    'cor' => 'blue',
                    'icone' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'descricao' => 'CNDs obrigatorias para editais e contratos publicos.',
                    'consultas' => [
                        'Tudo do Validacao',
                        'CND Federal (PGFN/RFB)',
                        'CRF FGTS (Regularidade)',
                        'CND Estadual (ICMS)',
                        'CNDT Trabalhista (TST)',
                    ],
                    'casos_uso' => [
                        'Emitir documentacao para editais publicos',
                        'Homologar fornecedores em licitacoes',
                        'Renovar contratos com orgaos publicos',
                    ],
                ],
                [
                    'codigo' => 'compliance',
                    'nome' => 'Compliance',
                    'creditos' => 14,
                    'gratuito' => false,
                    'popular' => false,
                    'cor' => 'purple',
                    'icone' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                    'descricao' => 'Monitoramento completo com protestos e divida ativa.',
                    'consultas' => [
                        'Tudo do Licitacao',
                        'Protestos (IEPTB)',
                        'Lista de Devedores PGFN',
                    ],
                    'casos_uso' => [
                        'Gestao continua de terceiros e fornecedores',
                        'Compliance com Lei Anticorrupcao (12.846/13)',
                        'Monitorar saude financeira de parceiros criticos',
                    ],
                ],
                [
                    'codigo' => 'due_diligence',
                    'nome' => 'Due Diligence',
                    'creditos' => 18,
                    'gratuito' => false,
                    'popular' => false,
                    'cor' => 'amber',
                    'icone' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7',
                    'descricao' => 'Analise profunda com compliance ESG.',
                    'consultas' => [
                        'Tudo do Compliance',
                        'Lista de Trabalho Escravo (MTE)',
                        'IBAMA - Autuacoes Ambientais',
                    ],
                    'casos_uso' => [
                        'Analise pre-aquisicao (M&A)',
                        'Atender requisitos ESG de investidores',
                        'Due diligence de parceiros estrategicos',
                    ],
                ],
                [
                    'codigo' => 'enterprise',
                    'nome' => 'Enterprise',
                    'creditos' => 20,
                    'gratuito' => false,
                    'popular' => false,
                    'cor' => 'slate',
                    'icone' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
                    'descricao' => 'Todas as consultas disponiveis incluindo processos judiciais.',
                    'consultas' => [
                        'Tudo do Due Diligence',
                        'Processos Judiciais (CNJ)',
                    ],
                    'casos_uso' => [
                        'Due diligence juridico completo',
                        'Analise de litigios antes de contratar',
                        'Monitoramento corporativo de alto nivel',
                    ],
                ],
            ];

            $corClasses = [
                'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => 'text-green-600', 'badge' => 'bg-green-100 text-green-700', 'border' => 'border-green-200'],
                'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'text-blue-600', 'badge' => 'bg-blue-100 text-blue-700', 'border' => 'border-blue-200'],
                'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'icon' => 'text-purple-600', 'badge' => 'bg-purple-100 text-purple-700', 'border' => 'border-purple-200'],
                'amber' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => 'text-amber-600', 'badge' => 'bg-amber-100 text-amber-700', 'border' => 'border-amber-200'],
                'slate' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'icon' => 'text-slate-600', 'badge' => 'bg-slate-100 text-slate-700', 'border' => 'border-slate-200'],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($planos as $plano)
                @php $cores = $corClasses[$plano['cor']]; @endphp
                <div class="bg-white rounded-xl border {{ $plano['codigo'] === 'enterprise' ? 'border-2 border-slate-400 ring-2 ring-slate-100' : ($plano['popular'] ? 'border-2 border-blue-500 ring-2 ring-blue-100' : 'border-gray-200') }} shadow-sm flex flex-col relative hover:shadow-md transition-shadow">
                    {{-- Badge Popular --}}
                    @if($plano['popular'])
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-600 text-white shadow-sm">
                                Mais Popular
                            </span>
                        </div>
                    @elseif($plano['codigo'] === 'enterprise')
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-700 text-white shadow-sm">
                                Mais Completo
                            </span>
                        </div>
                    @endif

                    {{-- Card Header --}}
                    <div class="p-6 {{ $plano['popular'] || $plano['codigo'] === 'enterprise' ? 'pt-7' : '' }}">
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
                                        <span class="text-sm font-medium text-green-600">Gratuito</span>
                                    @else
                                        <span class="text-sm text-gray-500">{{ $plano['creditos'] }} creditos/CNPJ</span>
                                    @endif
                                </div>
                            </div>
                            @if($plano['gratuito'])
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    Gratis
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $cores['badge'] }}">
                                    {{ $plano['creditos'] }} cred.
                                </span>
                            @endif
                        </div>

                        <p class="text-sm text-gray-600 mb-5">{{ $plano['descricao'] }}</p>

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
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg {{ $plano['codigo'] === 'enterprise' ? 'bg-slate-700 text-white hover:bg-slate-800' : ($plano['popular'] ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200') }} text-sm font-semibold transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Usar este plano
                        </a>
                    </div>
                </div>
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
                        <h2 class="text-lg font-semibold text-gray-900">Simule seu custo</h2>
                        <p class="text-sm text-gray-600">Calcule quanto voce gastaria consultando seus participantes.</p>
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
                            <option value="0">Gratuito (0 cred.)</option>
                            <option value="4">Validacao (4 cred.)</option>
                            <option value="10" selected>Licitacao (10 cred.)</option>
                            <option value="14">Compliance (14 cred.)</option>
                            <option value="18">Due Diligence (18 cred.)</option>
                            <option value="20">Enterprise (20 cred.)</option>
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
                    <span>O plano Gratuito nao consome creditos. <a href="/app/plano" data-link class="text-blue-600 hover:underline">Adquirir creditos</a></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    function initConsultasPlanos() {
        const container = document.getElementById('consultas-planos-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Consultas Planos] Inicializando...');

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

        console.log('[Consultas Planos] Inicializacao concluida');
    }

    // Expor globalmente para SPA
    window.initConsultasPlanos = initConsultasPlanos;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initConsultasPlanos, { once: true });
    } else {
        initConsultasPlanos();
    }
})();
</script>
