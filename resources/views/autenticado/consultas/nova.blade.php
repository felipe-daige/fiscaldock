{{-- Consultas - Nova Consulta --}}
<div class="min-h-screen bg-gray-50" id="consultas-nova-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Nova Consulta</h1>
                    <p class="mt-1 text-sm text-gray-500">Selecione os participantes e o tipo de consulta desejado.</p>
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

        @php
                // Metadata visual por codigo de plano (DB)
                $planoMeta = [
                    'gratuito' => [
                        'cor' => 'green',
                        'icone' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                        'consultas_display' => ['Situação Cadastral (Ativa, Inapta, Baixada)', 'Dados Cadastrais Completos', 'CNAEs Principal e Secundários', 'Quadro Societário (QSA)', 'Simples Nacional e MEI'],
                        'casos_uso' => ['Checar se CNPJ está ativo', 'Conferir regime para emitir NF', 'Consultar sócios e QSA'],
                    ],
                    'validacao' => [
                        'cor' => 'blue',
                        'icone' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                        'consultas_display' => ['Situação Cadastral (Ativa, Inapta, Baixada)', 'Dados Completos, CNAEs e QSA', 'Simples Nacional e MEI', 'SINTEGRA — IE ativa em todos os estados', 'TCU Consolidada (CEIS, CNEP, Inidôneos)'],
                        'casos_uso' => ['Conferir IE interestadual', 'Checar listas restritivas do TCU', 'Qualificar novos fornecedores'],
                    ],
                    'licitacao' => [
                        'cor' => 'blue',
                        'icone' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                        'consultas_display' => ['Tudo do Validação', 'CND Federal (PGFN/RFB)', 'CRF FGTS (Regularidade)', 'CND Estadual (ICMS)', 'CNDT Trabalhista (TST)'],
                        'casos_uso' => ['Documentação para editais', 'Homologar com CNDs exigidas', 'Renovar contratos públicos'],
                        'promo' => true,
                    ],
                    'compliance' => [
                        'cor' => 'purple',
                        'icone' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                        'consultas_display' => ['Situação Cadastral e Dados Completos', 'SINTEGRA e TCU Consolidada', 'CND Federal, Estadual, CRF e CNDT', 'Protestos em Cartório (IEPTB Nacional)', 'Devedores da Dívida Ativa (PGFN)', 'Análise completa de risco financeiro'],
                        'casos_uso' => ['Gestão de risco de terceiros', 'Atender Lei Anticorrupção', 'Monitorar protestos e dívidas'],
                    ],
                    'due_diligence' => [
                        'cor' => 'amber',
                        'icone' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7',
                        'consultas_display' => ['Todas as CNDs (Federal, Estadual, FGTS, Trabalhista)', 'Protestos e Devedores PGFN', 'SINTEGRA e TCU Consolidada', 'Trabalho Escravo (Lista Suja — MTE)', 'IBAMA — Autuações Ambientais', 'Compliance trabalhista e ambiental (ESG)'],
                        'casos_uso' => ['Análise pré-aquisição (M&A)', 'Atender requisitos ESG', 'Riscos trabalhistas e ambientais'],
                    ],
                    'enterprise' => [
                        'cor' => 'slate',
                        'icone' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
                        'consultas_display' => ['Todas as CNDs e Certidões', 'Protestos, Dívida Ativa e TCU', 'Trabalho Escravo e IBAMA (ESG)', 'Processos Judiciais (CNJ/SEEU)', 'SINTEGRA — Inscrição Estadual', 'Raio-X completo — 18 consultas por CNPJ'],
                        'casos_uso' => ['Due diligence jurídico completo', 'Mapear litígios antes de contratar', 'Relatório para comitês internos'],
                    ],
                ];

                $planosDetalhados = [];
                foreach ($planos as $p) {
                    $meta = $planoMeta[$p->codigo] ?? ['cor' => 'gray', 'icone' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'consultas_display' => [], 'casos_uso' => []];
                    $planosDetalhados[] = [
                        'codigo' => $p->codigo,
                        'nome' => $p->nome,
                        'creditos' => $p->custo_creditos,
                        'gratuito' => $p->is_gratuito,
                        'descricao' => $p->descricao,
                        'cor' => $meta['cor'],
                        'icone' => $meta['icone'],
                        'consultas' => $meta['consultas_display'],
                        'casos_uso' => $meta['casos_uso'],
                        'promo' => $meta['promo'] ?? false,
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

            {{-- Card: Adicionar CNPJ --}}
            <div class="bg-white rounded-lg border border-gray-200 mb-6">
                <div class="px-5 py-4">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-900">Adicionar CNPJ</h3>
                    </div>
                    {{-- Linha 1: Input + Botao --}}
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
                    {{-- Linha 2: Radio Participante / Cliente --}}
                    <div class="flex items-center gap-4 mt-3">
                        <span class="text-xs text-gray-500">Cadastrar como:</span>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="tipo_cadastro_cnpj" value="participante" checked class="w-3.5 h-3.5 text-gray-600 border-gray-300" id="radio-tipo-participante">
                            <span class="text-sm text-gray-700">Participante</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="tipo_cadastro_cnpj" value="cliente" class="w-3.5 h-3.5 text-gray-600 border-gray-300" id="radio-tipo-cliente">
                            <span class="text-sm text-gray-700">Cliente</span>
                        </label>
                    </div>
                    {{-- Linha 3: Select cliente (condicional) --}}
                    <div id="container-select-cliente" class="hidden mt-3">
                        <select id="select-cliente-cnpj" class="w-full sm:w-64 px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700">
                            <option value="novo">+ Novo cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->razao_social ?? $cliente->nome }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Selecione um cliente existente ou crie um novo.</p>
                    </div>
                    <div id="feedback-adicionar-cnpj" class="hidden mt-2 px-3 py-2 rounded-lg text-sm"></div>
                </div>
            </div>

            {{-- Layout 2 colunas --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Coluna Esquerda: Filtros e Lista de Participantes (2/3) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg border border-gray-200">
                        {{-- Filtros --}}
                        <div class="px-5 py-4 border-b border-gray-100">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                {{-- Filtro Grupo --}}
                                <div>
                                    <select id="filtro-grupo" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700">
                                        <option value="">Todos os grupos</option>
                                        @foreach($grupos as $grupo)
                                            <option value="{{ $grupo->id }}">{{ $grupo->nome }} ({{ $grupo->participantes_count }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filtro Origem --}}
                                <div>
                                    <select id="filtro-origem" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700">
                                        <option value="">Todas as origens</option>
                                        <option value="NFE">NF-e</option>
                                        <option value="NFSE">NFS-e</option>
                                        <option value="CTE">CT-e</option>
                                        <option value="SPED_EFD_FISCAL">SPED EFD Fiscal</option>
                                        <option value="SPED_EFD_CONTRIB">SPED EFD Contribuicoes</option>
                                        <option value="MANUAL">Manual</option>
                                    </select>
                                </div>

                                {{-- Filtro Cliente --}}
                                <div>
                                    <select id="filtro-cliente" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700">
                                        <option value="">Todos os clientes</option>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->id }}">{{ $cliente->razao_social ?? $cliente->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Busca --}}
                                <div>
                                    <input
                                        type="text"
                                        id="filtro-busca"
                                        placeholder="Buscar CNPJ ou razao social..."
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700"
                                    >
                                </div>
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
                        <div>
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
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
                                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition plano-label {{ $idx === 0 ? 'border-blue-500 bg-blue-50/60 ring-2 ring-blue-100' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-500/8' }}" data-plano-id="{{ $planos[$idx]->id }}">
                                    <input type="radio" name="plano_id" value="{{ $planos[$idx]->id }}" class="w-4 h-4 text-gray-600 border-gray-300" data-custo="{{ $pd['creditos'] }}" data-gratuito="{{ $pd['gratuito'] ? '1' : '0' }}" {{ $idx === 0 ? 'checked' : '' }}>
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
                                            @else
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">{{ $pd['creditos'] }} cred.</span>
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

            {{-- Modal de Progresso --}}
            <div id="modal-progresso" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-lg max-w-xs w-full mx-4 p-5">
                    <div class="text-center">
                        <svg class="animate-spin h-6 w-6 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <h3 id="progresso-titulo" class="text-sm font-semibold text-gray-900 mb-1">Processando consulta...</h3>
                        <p id="progresso-mensagem" class="text-xs text-gray-500 mb-3">Aguarde enquanto processamos.</p>
                        <div class="w-full bg-gray-100 rounded-full h-1 mb-1">
                            <div id="progresso-barra" class="bg-gray-900 h-1 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="progresso-percentual" class="text-xs text-gray-400">0%</p>
                    </div>
                </div>
            </div>

            {{-- Modal de Sucesso --}}
            <div id="modal-sucesso" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-lg max-w-xs w-full mx-4 p-5">
                    <div class="text-center">
                        <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Consulta concluida</h3>
                        <p class="text-xs text-gray-500 mb-4">Download iniciado automaticamente.</p>
                        <div class="flex gap-2">
                            <a id="link-download-manual" href="#" class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Baixar
                            </a>
                            <button type="button" id="btn-fechar-sucesso" class="flex-1 py-2 border border-gray-200 bg-white text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
                                Fechar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal de Erro --}}
            <div id="modal-erro" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-lg mx-4 p-4" style="max-width: 280px;">
                    <div class="text-center">
                        <div class="w-8 h-8 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Erro</h3>
                        <p id="erro-mensagem" class="text-xs text-gray-500 mb-3 break-words">Ocorreu um erro inesperado.</p>
                        <button type="button" id="btn-fechar-erro" class="w-full py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition text-sm font-medium">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
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
                            <span id="carousel-counter-lote" class="text-xs text-gray-400">1 / {{ count($planosDetalhados) }}</span>
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
                                                    @if($pd['promo'] ?? false)
                                                        <span class="text-sm text-amber-700 font-semibold">{{ $pd['creditos'] }} cred./CNPJ</span>
                                                    @else
                                                        <span class="text-sm {{ $pd['gratuito'] ? 'text-green-600 font-medium' : 'text-gray-500' }}">
                                                            {{ $pd['gratuito'] ? 'Gratuito' : $pd['creditos'] . ' creditos/CNPJ' }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($pd['promo'] ?? false)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">{{ $pd['creditos'] }} cred.</span>
                                                @elseif($pd['gratuito'])
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Gratis</span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $cores['badge'] }}">{{ $pd['creditos'] }} cred.</span>
                                                @endif
                                            </div>

                                            {{-- Description --}}
                                            <p class="text-sm text-gray-600 mb-3">{{ $pd['descricao'] }}</p>

                                            @if($pd['promo'] ?? false)
                                                <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-lg mb-3">
                                                    <p class="text-xs font-semibold text-amber-800">&#x1f3f7;&#xfe0e; Oferta por tempo limitado</p>
                                                    <p class="text-xs text-amber-700 mt-0.5">Todas as CNDs por {{ $pd['creditos'] }} cred./CNPJ — aproveite antes do reajuste.</p>
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
            calcularCusto: '/app/consultas/nova/calcular-custo',
            executar: '/app/consultas/nova/executar',
            adicionarCnpj: '/app/consultas/nova/adicionar-cnpj',
            progressoStream: '/app/consultas/nova/progresso/stream',
            baixarLote: '/app/consultas/lote/{id}/baixar'
        },
        planos: {
            @foreach($planos as $plano)
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
