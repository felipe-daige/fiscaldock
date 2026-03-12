{{-- Consultas - Nova Consulta --}}
<div class="min-h-screen bg-gray-50" id="consultas-nova-container">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Nova Consulta</h1>
                    <p class="text-xs text-gray-500 mt-1">Selecione os participantes e o tipo de consulta desejado.</p>
                </div>
                <a
                    href="/app/consultas/historico"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-700 text-sm font-medium transition hover:bg-gray-50"
                    data-link
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Historico
                </a>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="space-y-6">
            <style>
                @keyframes card-slide-in {
                    from { opacity: 0; transform: translateY(60px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .cn-animate {
                    opacity: 0;
                    animation: card-slide-in 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                }
                @media (prefers-reduced-motion: reduce) {
                    .cn-animate { opacity: 1; animation: none; }
                }
            </style>

        @php
                // Metadata visual por codigo de plano (DB)
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

                $planosDetalhados = [];
                $planosAtivos = $planos->where('is_active', true)->values();
                foreach ($planosAtivos as $p) {
                    $meta = $planoMeta[$p->codigo] ?? ['cor' => 'gray', 'icone' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'consultas_display' => [], 'casos_uso' => []];
                    $planosDetalhados[] = [
                        'codigo' => $p->codigo,
                        'nome' => $p->nome,
                        'creditos' => $p->custo_creditos,
                        'descricao' => $p->descricao,
                        'cor' => $meta['cor'],
                        'icone' => $meta['icone'],
                        'consultas' => $meta['consultas_display'],
                        'casos_uso' => $meta['casos_uso'],
                        'coming_soon' => $meta['coming_soon'] ?? false,
                        'gratuito' => $p->is_gratuito,
                        'promo' => $meta['promo'] ?? false,
                        'preco_original' => $meta['preco_original'] ?? null,
                    ];
                }

                $corClasses = [
                    'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => 'text-green-600', 'badge' => 'bg-green-100 text-green-700', 'border' => 'border-green-200', 'btn' => 'bg-green-600 hover:bg-green-700'],
                    'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'text-blue-600', 'badge' => 'bg-blue-100 text-blue-700', 'border' => 'border-blue-200', 'btn' => 'bg-blue-600 hover:bg-blue-700'],
                    'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'icon' => 'text-purple-600', 'badge' => 'bg-purple-100 text-purple-700', 'border' => 'border-purple-200', 'btn' => 'bg-purple-600 hover:bg-purple-700'],
                    'amber' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => 'text-amber-600', 'badge' => 'bg-amber-100 text-amber-700', 'border' => 'border-amber-200', 'btn' => 'bg-amber-600 hover:bg-amber-700'],
                    'slate' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'icon' => 'text-slate-600', 'badge' => 'bg-slate-100 text-slate-700', 'border' => 'border-slate-200', 'btn' => 'bg-slate-700 hover:bg-slate-800'],
                ];
            @endphp

            <div id="consulta-form-section">

            {{-- Card: Adicionar CNPJ --}}
            <div class="bg-white rounded-lg border border-gray-200 mb-6 cn-animate" style="animation-delay: 0.05s">
                <div class="px-5 py-4">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-900">Adicionar CNPJ</h3>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <input
                                type="text"
                                id="input-adicionar-cnpj"
                                placeholder="00.000.000/0000-00"
                                maxlength="18"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700 font-mono"
                            >
                        </div>
                        <div class="w-full sm:w-56">
                            <select id="select-cliente-associar" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700">
                                <option value="">Sem vinculo a cliente</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->razao_social ?? $cliente->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button
                            type="button"
                            id="btn-adicionar-cnpj"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition text-sm font-medium whitespace-nowrap"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Adicionar
                        </button>
                    </div>
                    <div id="feedback-adicionar-cnpj" class="hidden mt-2 px-3 py-2 rounded-lg text-sm"></div>
                </div>
            </div>

            {{-- Layout 2 colunas --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 cn-animate" style="animation-delay: 0.1s">
                {{-- Coluna Esquerda: Filtros e Lista de Participantes (2/3) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        {{-- Tab Bar --}}
                        <div class="px-5 py-3 border-b border-gray-100">
                            <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1" id="search-tabs">
                                <button type="button" data-tab="participantes"
                                    class="search-tab flex-1 px-3 py-1.5 text-sm font-medium rounded-md transition bg-white text-gray-900 shadow-sm">
                                    Participantes
                                </button>
                                <button type="button" data-tab="clientes"
                                    class="search-tab flex-1 px-3 py-1.5 text-sm font-medium rounded-md transition text-gray-500 hover:text-gray-700">
                                    Clientes
                                </button>
                                <button type="button" data-tab="grupos"
                                    class="search-tab flex-1 px-3 py-1.5 text-sm font-medium rounded-md transition text-gray-500 hover:text-gray-700">
                                    Grupos
                                </button>
                            </div>
                            <script>
                            (function() {
                                var c = document.getElementById('search-tabs');
                                if (c && !c._earlyTab) {
                                    c._earlyTab = true;
                                    c.addEventListener('click', function(e) {
                                        var tab = e.target.closest('.search-tab');
                                        if (!tab || !tab.dataset.tab) return;
                                        document.querySelectorAll('.search-tab').forEach(function(t) {
                                            if (t.dataset.tab === tab.dataset.tab) {
                                                t.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                                                t.classList.remove('text-gray-500', 'hover:text-gray-700');
                                            } else {
                                                t.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                                                t.classList.add('text-gray-500', 'hover:text-gray-700');
                                            }
                                        });
                                        document.querySelectorAll('.search-view').forEach(function(v) { v.classList.add('hidden'); });
                                        var view = document.getElementById('view-' + tab.dataset.tab);
                                        if (view) view.classList.remove('hidden');
                                    });
                                }
                            })();
                            </script>
                        </div>

                        {{-- View: Participantes (default) --}}
                        <div id="view-participantes" class="search-view">
                            {{-- Barra de contexto (aparece ao filtrar por cliente/grupo) --}}
                            <div id="participantes-context" class="hidden px-5 py-2 bg-blue-50 border-b border-blue-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <button type="button" id="btn-clear-filter-context" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                        &larr; Todos
                                    </button>
                                    <span class="text-xs text-gray-400">|</span>
                                    <span id="filter-context-label" class="text-xs text-blue-700 font-medium"></span>
                                </div>
                                <button type="button" id="btn-remove-filter-chip" class="p-0.5 text-blue-400 hover:text-blue-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            {{-- Filtros --}}
                            <div class="px-5 py-3 border-b border-gray-100">
                                <div class="flex gap-3">
                                    <select id="filtro-origem" class="w-full sm:w-48 px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700">
                                        <option value="">Todas as origens</option>
                                        <option value="NFE">NF-e</option>
                                        <option value="NFSE">NFS-e</option>
                                        <option value="CTE">CT-e</option>
                                        <option value="SPED_EFD_FISCAL">SPED EFD Fiscal</option>
                                        <option value="SPED_EFD_CONTRIB">SPED EFD Contribuicoes</option>
                                        <option value="MANUAL">Manual</option>
                                    </select>
                                    <input
                                        type="text"
                                        id="filtro-busca"
                                        placeholder="Buscar CNPJ ou razao social..."
                                        class="flex-1 px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700"
                                    >
                                </div>

                                {{-- Acoes em massa --}}
                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                                    <div class="flex items-center gap-4 text-sm">
                                        <button type="button" id="btn-selecionar-todos" class="text-gray-600 hover:text-gray-900">
                                            Selecionar todos
                                        </button>
                                        <button type="button" id="btn-limpar-selecao" class="text-gray-500 hover:text-gray-700">
                                            Limpar
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span id="contador-selecionados" class="text-xs text-gray-500">
                                            <span id="total-selecionados">0</span> selecionados
                                        </span>
                                        <button type="button" onclick="if(window.reloadParticipantes) window.reloadParticipantes();"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition"
                                            title="Atualizar lista">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Atualizar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabela de Participantes --}}
                            <div class="overflow-x-auto">
                                <table class="w-full table-fixed">
                                    <thead class="bg-gray-50 border-b border-gray-200 hidden md:table-header-group">
                                        <tr>
                                            <th class="w-10 px-4 py-3 text-left">
                                                <input type="checkbox" id="checkbox-todos" class="w-4 h-4 text-gray-600 rounded border-gray-300">
                                            </th>
                                            <th class="w-40 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CNPJ</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razao Social</th>
                                            <th class="w-32 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabela-participantes" class="divide-y divide-gray-100">
                                        {{-- Preenchido via JS --}}
                                        <tr id="loading-row">
                                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                                <svg class="animate-spin h-5 w-5 text-gray-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span class="text-sm">Carregando...</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Paginacao --}}
                            <div id="paginacao-container" class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    <span id="pag-inicio">0</span>-<span id="pag-fim">0</span> de <span id="pag-total">0</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" id="btn-pag-anterior" class="px-2.5 py-1 border border-gray-200 rounded text-xs text-gray-600 hover:bg-gray-50 disabled:opacity-40" disabled>
                                        Anterior
                                    </button>
                                    <span id="pag-atual" class="text-xs text-gray-500">1</span>
                                    <button type="button" id="btn-pag-proximo" class="px-2.5 py-1 border border-gray-200 rounded text-xs text-gray-600 hover:bg-gray-50 disabled:opacity-40" disabled>
                                        Proximo
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- View: Clientes --}}
                        <div id="view-clientes" class="search-view hidden">
                            <div class="px-5 py-3 border-b border-gray-100">
                                <input type="text" id="busca-clientes" placeholder="Buscar cliente por nome ou CNPJ..."
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700">
                            </div>
                            <div class="overflow-x-auto">
                            <table class="w-full table-fixed">
                                <thead class="bg-gray-50 border-b border-gray-200 hidden md:table-header-group">
                                    <tr>
                                        <th class="w-10 px-4 py-3 text-left">
                                            <input type="checkbox" id="checkbox-todos-clientes" class="w-4 h-4 text-gray-600 rounded border-gray-300">
                                        </th>
                                        <th class="w-40 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CNPJ</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                        <th class="w-36 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participantes</th>
                                    </tr>
                                </thead>
                                <tbody id="lista-clientes" class="divide-y divide-gray-100">
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">Carregando clientes...</td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                        </div>

                        {{-- View: Grupos --}}
                        <div id="view-grupos" class="search-view hidden">
                            <div id="lista-grupos" class="divide-y divide-gray-100">
                                <div class="px-5 py-8 text-center text-sm text-gray-400">Carregando grupos...</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Coluna Direita: Tipo de Analise e Resumo (1/3) --}}
                <div class="space-y-4 lg:sticky lg:top-4 lg:self-start">
                    {{-- Card Tipo de Analise --}}
                    <div class="bg-white rounded-lg border border-gray-200">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-900">Tipo de Consulta</h3>
                                <button type="button" id="btn-ver-detalhes-planos-lote" class="text-xs text-blue-600 hover:text-blue-800">Ver detalhes</button>
                            </div>
                        </div>
                        <div class="p-4 space-y-2">
                            @foreach($planosDetalhados as $idx => $pd)
                                @php $cores = $corClasses[$pd['cor']]; @endphp
                                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition plano-label {{ $idx === 0 ? 'border-blue-500 bg-blue-50/60 ring-2 ring-blue-100' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-500/8' }}" data-plano-id="{{ $planosAtivos[$idx]->id }}">
                                    <input type="radio" name="plano_id" value="{{ $planosAtivos[$idx]->id }}" class="w-4 h-4 text-gray-600 border-gray-300" data-custo="{{ $pd['creditos'] }}" data-gratuito="{{ $pd['gratuito'] ? '1' : '0' }}" {{ $idx === 0 ? 'checked' : '' }}>
                                    <div class="flex-shrink-0 w-7 h-7 rounded-md {{ $cores['bg'] }} flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 {{ $cores['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $pd['icone'] }}"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900">{{ $pd['nome'] }}</span>
                                            @if($pd['gratuito'])
                                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">Gratis</span>
                                            @elseif($pd['promo'])
                                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-medium rounded-full">{{ $pd['creditos'] }} cred.</span>
                                            @else
                                                <span class="px-2 py-0.5 {{ $cores['badge'] }} text-xs font-medium rounded-full">{{ $pd['creditos'] }} cred.</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $pd['descricao'] }}</p>
                                    </div>
                                    <button type="button" class="btn-info-plano-lote flex-shrink-0 w-6 h-6 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:border-blue-300 transition" data-slide-index="{{ $idx }}" onclick="event.preventDefault(); event.stopPropagation();">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Card Resumo --}}
                    <div id="card-resumo-consulta" class="bg-white rounded-lg border border-gray-200">
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total</span>
                                <span id="resumo-custo-total" class="px-3 py-1 bg-blue-100 text-blue-700 text-base font-semibold rounded-full">0 créditos</span>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500">Participantes</span>
                                    <span id="resumo-participantes" class="text-gray-900 font-medium">0</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500">Custo unitário</span>
                                    <span id="resumo-custo-unitario" class="px-2 py-0.5 bg-blue-50 text-blue-600 text-xs font-medium rounded-full">0 créditos</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                                    <span class="text-gray-500">Seu saldo</span>
                                    <span id="resumo-saldo" class="px-2 py-0.5 bg-green-50 text-green-600 text-xs font-medium rounded-full">{{ number_format($credits, 0, ',', '.') }} créditos</span>
                                </div>
                            </div>

                            {{-- Alerta créditos --}}
                            <div id="alerta-creditos-insuficientes" class="hidden mt-3 p-2 bg-red-50 border border-red-100 rounded-lg text-xs text-red-600">
                                Créditos insuficientes
                            </div>

                            <button type="button" id="btn-gerar-relatorio" class="w-full mt-4 py-2.5 rounded-lg text-sm font-medium transition" style="background-color: #d1d5db; color: #6b7280; cursor: not-allowed;" disabled>
                                Executar Consulta
                            </button>
                        </div>
                    </div>

                    {{-- Card Consultas Incluidas --}}
                    <div id="card-consultas-incluidas" class="bg-white rounded-lg border border-gray-200">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-900">Consultas Incluidas</h3>
                        </div>
                        <div id="lista-consultas-incluidas" class="p-4 space-y-1">
                            {{-- Preenchido via JS --}}
                        </div>
                    </div>
                </div>
            </div>

            </div>{{-- /consulta-form-section --}}

            {{-- Seção de Progresso Inline (inicialmente oculta) --}}
            <div id="consulta-progresso-section" class="hidden">

                {{-- Card de Progresso --}}
                <div id="consulta-progresso-card" class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    {{-- Header: ícone + título --}}
                    <div class="flex items-start gap-3 mb-4">
                        <div id="consulta-progresso-icon" class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 id="progresso-titulo" class="font-semibold text-gray-900 truncate">Processando consulta...</h3>
                            <p id="consulta-progresso-subtitulo" class="text-sm text-gray-500 hidden"></p>
                        </div>
                    </div>
                    {{-- Barra de progresso --}}
                    <div class="mb-3">
                        <div class="flex justify-between text-sm mb-1">
                            <span id="progresso-mensagem" class="text-gray-600">Iniciando...</span>
                            <span id="progresso-percentual" class="font-medium text-gray-900">0%</span>
                        </div>
                        <div class="bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div id="progresso-barra" class="bg-blue-600 h-full rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
                        </div>
                    </div>
                    {{-- Seção de erro (oculta por padrão) --}}
                    <div id="consulta-progresso-erro" class="hidden pt-3 border-t border-red-100">
                        <p id="consulta-progresso-erro-msg" class="text-sm text-gray-700 mb-3">Ocorreu um erro durante o processamento.</p>
                        <p class="text-sm text-gray-600 mb-4">
                            Por favor, tente novamente mais tarde.<br>
                            Se o erro persistir, entre em contato com o suporte:
                        </p>
                        <a href="https://wa.me/5567999844366"
                           target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 transition mb-3">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            WhatsApp: (67) 99984-4366
                        </a>
                        <div>
                            <button type="button"
                                    id="btn-tentar-novamente"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Tentar Novamente
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Seção de Resultados (aparece ao concluir) --}}
                <div id="resultado-consulta" class="hidden mt-4">
                    <div class="bg-white border border-green-200 rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Consulta Concluída</h3>
                                        <p class="text-sm text-gray-600" id="resultado-consulta-info">-</p>
                                    </div>
                                </div>
                                <button type="button" id="btn-nova-consulta"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Nova Consulta
                                </button>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <a id="link-download-relatorio" href="#"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Baixar Relatório
                            </a>
                        </div>
                    </div>
                </div>

            </div>{{-- /consulta-progresso-section --}}
            {{-- Modal: Carousel de Planos --}}
            <div id="modal-planos-carousel-lote" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] flex flex-col relative overflow-visible">
                    {{-- Modal Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="text-base font-semibold text-gray-900">Detalhes dos Planos</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <span id="carousel-counter-lote" class="text-xs text-gray-400">1 / {{ count($planosAtivos) }}</span>
                            <button type="button" id="btn-fechar-carousel-lote" class="p-1 text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Navigation arrows --}}
                    <button type="button" id="swiper-planos-prev-lote" class="absolute -left-5 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm border border-gray-200 shadow-md flex items-center justify-center text-gray-500 hover:bg-white hover:text-gray-700 hover:shadow-lg transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button type="button" id="swiper-planos-next-lote" class="absolute -right-5 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm border border-gray-200 shadow-md flex items-center justify-center text-gray-500 hover:bg-white hover:text-gray-700 hover:shadow-lg transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    {{-- Swiper Carousel --}}
                    <div class="flex-1 overflow-hidden relative">
                        <div class="swiper h-full" id="swiper-planos-lote">
                            <div class="swiper-wrapper">
                                @foreach($planosDetalhados as $idx => $pd)
                                    @php $cores = $corClasses[$pd['cor']]; @endphp
                                    <div class="swiper-slide">
                                        <div class="p-5 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                                            {{-- Plan header --}}
                                            <div class="flex items-center gap-3 mb-3">
                                                <div class="flex-shrink-0 w-9 h-9 rounded-lg {{ $cores['bg'] }} flex items-center justify-center">
                                                    <svg class="w-[18px] h-[18px] {{ $cores['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $pd['icone'] }}"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="text-base font-bold text-gray-900">{{ $pd['nome'] }}</h4>
                                                    @if($pd['gratuito'])
                                                        <span class="text-sm font-semibold text-green-600">Gratuito</span>
                                                    @elseif($pd['promo'])
                                                        <span class="text-sm text-gray-400 line-through">{{ $pd['preco_original'] }} creditos/CNPJ</span>
                                                        <span class="text-sm font-semibold text-amber-600 ml-1">{{ $pd['creditos'] }} creditos/CNPJ</span>
                                                    @else
                                                        <span class="text-sm text-gray-500">{{ $pd['creditos'] }} creditos/CNPJ</span>
                                                    @endif
                                                </div>
                                                @if($pd['gratuito'])
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Gratis</span>
                                                @elseif($pd['promo'])
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">{{ $pd['creditos'] }} cred.</span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $cores['badge'] }}">{{ $pd['creditos'] }} cred.</span>
                                                @endif
                                            </div>

                                            {{-- Description --}}
                                            <p class="text-sm text-gray-600 mb-3">{{ $pd['descricao'] }}</p>

                                            @if($pd['promo'])
                                                <div class="mb-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span class="text-xs font-semibold text-amber-800">Promocao: de {{ $pd['preco_original'] }} por {{ $pd['creditos'] }} creditos/CNPJ</span>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Consultas incluidas --}}
                                            <div class="mb-3">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Consultas incluidas</p>
                                                <ul class="space-y-1">
                                                    @foreach($pd['consultas'] as $consulta)
                                                        <li class="flex items-start gap-2 text-sm text-gray-700">
                                                            <svg class="w-4 h-4 {{ $cores['icon'] }} mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            <span>{{ $consulta }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                            {{-- Quando usar --}}
                                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 mb-4">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Quando usar</p>
                                                <ul class="space-y-1">
                                                    @foreach($pd['casos_uso'] as $caso)
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
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Footer: botao selecionar --}}
                    <div class="px-6 pb-4 pt-3 border-t border-gray-100 flex-shrink-0">
                        <button
                            type="button"
                            id="btn-selecionar-plano-footer-lote"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition-colors"
                            data-plano-index="0"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Selecionar este plano
                        </button>
                    </div>

                    {{-- Pagination dots --}}
                    <div class="px-6 py-3 border-t border-gray-100 flex-shrink-0">
                        <div id="swiper-planos-pagination-lote" class="flex justify-center"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #swiper-planos-pagination-lote .swiper-pagination-bullet {
        width: 8px;
        height: 8px;
        background: #d1d5db;
        opacity: 1;
        margin: 0 4px;
        border-radius: 50%;
        transition: all 0.2s;
    }
    #swiper-planos-pagination-lote .swiper-pagination-bullet-active {
        background: #3b82f6;
        width: 20px;
        border-radius: 4px;
    }
</style>

{{-- Dados para JS --}}
<script>
    window.consultaData = {
        credits: {{ $credits ?? 0 }},
        csrfToken: '{{ csrf_token() }}',
        routes: {
            getParticipantes: '/app/consultas/nova/participantes',
            getParticipantesGrupo: '/app/consultas/nova/participantes/grupo/',
            getClientes: '/app/consultas/nova/clientes',
            getGrupos: '/app/consultas/nova/grupos',
            calcularCusto: '/app/consultas/nova/calcular-custo',
            executar: '/app/consultas/nova/executar',
            adicionarCnpj: '/app/consultas/nova/adicionar-cnpj',
            progressoStream: '/app/consultas/nova/progresso/stream',
            baixarLote: '/app/consultas/lote/{id}/baixar',
            loteStatus: '/app/consultas/lote/{id}/status',
            participantesPorClientes: '/app/consultas/nova/participantes-por-clientes'
        },
        planos: {
            @foreach($planosAtivos as $plano)
                {{ $plano->id }}: {
                    codigo: '{{ $plano->codigo }}',
                    consultas: {!! json_encode($plano->consultas_incluidas) !!}
                },
            @endforeach
        },
        planosDetalhados: {!! json_encode(collect($planosDetalhados)->values()) !!},
        corClasses: {!! json_encode($corClasses) !!}
    };
</script>
<script src="/js/consulta-lote.js"></script>
<script>
(function() {
    function tryInit(attempts) {
        if (typeof window.initConsultaLote === 'function') {
            window.initConsultaLote();
            // Safety: se loading ficar travado, forçar reload
            setTimeout(function() {
                var lr = document.getElementById('loading-row');
                if (lr && lr.style.display !== 'none' && lr.parentNode && lr.offsetParent !== null) {
                    if (typeof window.reloadParticipantes === 'function') {
                        window.reloadParticipantes();
                    }
                }
            }, 2000);
        } else if (attempts < 50) {
            setTimeout(function() { tryInit(attempts + 1); }, 100);
        } else {
            forceLoadScript();
        }
    }

    function forceLoadScript() {
        var existing = document.querySelector('script[src*="consulta-lote"]');
        if (existing) existing.parentNode.removeChild(existing);
        window._consultaLoteModuleLoaded = false;

        var s = document.createElement('script');
        s.src = '/js/consulta-lote.js?_=' + Date.now();
        s.onload = function() {
            if (typeof window.initConsultaLote === 'function') {
                window.initConsultaLote();
            } else {
                showError();
            }
        };
        s.onerror = function() { showError(); };
        document.head.appendChild(s);
    }

    function showError() {
        var lr = document.getElementById('loading-row');
        if (lr) lr.style.display = 'none';
        var tb = document.getElementById('tabela-participantes');
        if (tb) tb.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-red-500">Erro ao carregar. Clique em Atualizar.</td></tr>';

        window.reloadParticipantes = function() {
            if (typeof window.initConsultaLote === 'function') {
                window.initConsultaLote();
            } else {
                if (tb) tb.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500"><span class="text-sm">Carregando...</span></td></tr>';
                forceLoadScript();
            }
        };
    }

    tryInit(0);
})();
</script>
