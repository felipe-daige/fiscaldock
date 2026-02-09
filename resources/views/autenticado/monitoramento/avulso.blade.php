{{-- Monitoramento - Consulta Avulsa --}}
<div class="min-h-screen bg-gray-50" id="monitoramento-avulso-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Consulta Avulsa</h1>
                    <p class="mt-1 text-sm text-gray-600">Consulte a situacao cadastral e fiscal de CNPJs.</p>
                </div>
                <a
                    href="/app/monitoramento"
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
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-blue-900">Como funciona?</h3>
                    <p class="text-sm text-blue-800 mt-1">
                        Consulte a situacao cadastral e fiscal de CNPJs individualmente. Escolha o tipo de consulta e receba informacoes detalhadas sobre fornecedores e clientes.
                    </p>
                </div>
            </div>
        </div>

        {{-- Grid: Formulario + Info --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 items-stretch">
            {{-- Card Esquerdo: Nova Consulta --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm h-full flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Nova Consulta</h2>
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    <form id="form-consulta-avulsa" class="flex-1 flex flex-col">
                        {{-- Input CNPJ unico --}}
                        <div class="mb-4">
                            <label for="cnpj-input" class="block text-sm font-medium text-gray-700 mb-2">
                                CNPJ:
                            </label>
                            <input
                                type="text"
                                id="cnpj-input"
                                name="cnpj"
                                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                                placeholder="00.000.000/0000-00"
                                maxlength="18"
                                autocomplete="off"
                            >
                            <p class="mt-1 text-xs text-gray-500">
                                Digite o CNPJ do fornecedor ou cliente que deseja consultar.
                            </p>
                        </div>

                        {{-- Selecao de Cliente (Opcional) --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Associar a um Cliente: <span class="text-gray-400 font-normal">(opcional)</span>
                            </label>
                            <select id="cliente-select" name="cliente_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Nao associar a um cliente</option>
                                @foreach($clientes ?? [] as $cliente)
                                    <option value="{{ $cliente->id }}">
                                        {{ $cliente->razao_social ?? $cliente->nome }}
                                        ({{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cliente->documento) }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Vincule o participante a um cliente para melhor organizacao.
                            </p>
                        </div>

                        {{-- Selecao do Plano --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de consulta:
                            </label>
                            <div class="space-y-2" id="planos-grid">
                                @php
                                    $planosEstaticos = [
                                        ['codigo' => 'basico', 'nome' => 'Basico', 'creditos' => 0, 'gratuito' => true, 'descricao' => 'Dados cadastrais + Simples/MEI'],
                                        ['codigo' => 'cadastral_plus', 'nome' => 'Cadastral+', 'creditos' => 3, 'gratuito' => false, 'descricao' => 'Basico + SINTEGRA + TCU Consolidada'],
                                        ['codigo' => 'fiscal_federal', 'nome' => 'Fiscal Federal', 'creditos' => 6, 'gratuito' => false, 'descricao' => 'Cadastral+ + CND Federal + CRF FGTS'],
                                        ['codigo' => 'fiscal_completo', 'nome' => 'Fiscal Completo', 'creditos' => 12, 'gratuito' => false, 'descricao' => 'Fiscal Federal + CND Estadual + CNDT'],
                                        ['codigo' => 'due_diligence', 'nome' => 'Due Diligence', 'creditos' => 16, 'gratuito' => false, 'descricao' => 'Fiscal Completo + Lista Devedores PGFN'],
                                        ['codigo' => 'esg', 'nome' => 'ESG', 'creditos' => 6, 'gratuito' => false, 'descricao' => 'Trabalho Escravo + IBAMA Autuacoes'],
                                        ['codigo' => 'completo', 'nome' => 'Completo', 'creditos' => 22, 'gratuito' => false, 'descricao' => 'Todas as consultas disponiveis'],
                                    ];

                                    $planosDetalhados = [
                                        [
                                            'codigo' => 'basico', 'nome' => 'Basico', 'creditos' => 0, 'gratuito' => true,
                                            'cor' => 'green',
                                            'icone' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                            'descricao' => 'Validacao rapida de dados cadastrais e regime tributario.',
                                            'consultas' => ['Situacao Cadastral (Ativa, Inapta, Baixada)', 'Dados Cadastrais Completos', 'CNAEs Principal e Secundarios', 'Quadro Societario (QSA)', 'Simples Nacional e MEI'],
                                            'casos_uso' => ['Validar se CNPJ existe e esta ativo', 'Conferir regime tributario para emissao de NF', 'Identificar socios antes de negociar'],
                                        ],
                                        [
                                            'codigo' => 'cadastral_plus', 'nome' => 'Cadastral+', 'creditos' => 3, 'gratuito' => false,
                                            'cor' => 'blue',
                                            'icone' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                                            'descricao' => 'Inclui inscricao estadual e verificacao em listas restritivas.',
                                            'consultas' => ['Tudo do Basico', 'SINTEGRA (Inscricao Estadual)', 'TCU Consolidada (CEIS, CNEP, CNJ)'],
                                            'casos_uso' => ['Validar IE para operacoes interestaduais', 'Verificar se empresa esta em lista de impedidos', 'Qualificar fornecedores antes de cadastrar'],
                                        ],
                                        [
                                            'codigo' => 'fiscal_federal', 'nome' => 'Fiscal Federal', 'creditos' => 6, 'gratuito' => false,
                                            'cor' => 'blue',
                                            'icone' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                                            'descricao' => 'CNDs federais obrigatorias para contratos publicos.',
                                            'consultas' => ['Tudo do Cadastral+', 'CND Federal (PGFN/RFB)', 'CRF FGTS (Regularidade)'],
                                            'casos_uso' => ['Emitir CNDs para editais publicos', 'Verificar regularidade fiscal federal', 'Homologar fornecedores em licitacoes'],
                                        ],
                                        [
                                            'codigo' => 'fiscal_completo', 'nome' => 'Fiscal Completo', 'creditos' => 12, 'gratuito' => false,
                                            'cor' => 'purple',
                                            'icone' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                                            'descricao' => 'Pacote completo de certidoes fiscais e trabalhistas.',
                                            'consultas' => ['Tudo do Fiscal Federal', 'CND Estadual (ICMS)', 'CNDT Trabalhista (TST)'],
                                            'casos_uso' => ['Renovar contratos com orgaos publicos', 'Compliance trabalhista e fiscal completo', 'Monitoramento de fornecedores criticos'],
                                        ],
                                        [
                                            'codigo' => 'due_diligence', 'nome' => 'Due Diligence', 'creditos' => 16, 'gratuito' => false,
                                            'cor' => 'amber',
                                            'icone' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7',
                                            'descricao' => 'Analise aprofundada com divida ativa detalhada.',
                                            'consultas' => ['Tudo do Fiscal Completo', 'Lista de Devedores PGFN (divida detalhada)'],
                                            'casos_uso' => ['Analise pre-aquisicao (M&A)', 'Gestao continua de terceiros', 'Compliance com Lei Anticorrupcao'],
                                        ],
                                        [
                                            'codigo' => 'esg', 'nome' => 'ESG', 'creditos' => 6, 'gratuito' => false,
                                            'cor' => 'green',
                                            'icone' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                            'descricao' => 'Compliance ambiental e trabalhista para ESG.',
                                            'consultas' => ['Lista de Trabalho Escravo (MTE)', 'IBAMA - Autuacoes Ambientais'],
                                            'casos_uso' => ['Atender requisitos ESG de investidores', 'Verificar compliance socioambiental', 'Due diligence ambiental e trabalhista'],
                                        ],
                                        [
                                            'codigo' => 'completo', 'nome' => 'Completo', 'creditos' => 22, 'gratuito' => false,
                                            'cor' => 'slate',
                                            'icone' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
                                            'descricao' => 'Todas as consultas disponiveis em um unico pacote.',
                                            'consultas' => ['Tudo do Due Diligence', 'Trabalho Escravo + IBAMA', 'Processos Judiciais (CNJ)'],
                                            'casos_uso' => ['Due diligence juridico completo', 'Analise de litigios antes de contratar', 'Monitoramento corporativo de alto nivel'],
                                        ],
                                    ];

                                    $corClasses = [
                                        'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => 'text-green-600', 'badge' => 'bg-green-100 text-green-700', 'border' => 'border-green-200', 'btn' => 'bg-green-600 hover:bg-green-700'],
                                        'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'text-blue-600', 'badge' => 'bg-blue-100 text-blue-700', 'border' => 'border-blue-200', 'btn' => 'bg-blue-600 hover:bg-blue-700'],
                                        'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'icon' => 'text-purple-600', 'badge' => 'bg-purple-100 text-purple-700', 'border' => 'border-purple-200', 'btn' => 'bg-purple-600 hover:bg-purple-700'],
                                        'amber' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => 'text-amber-600', 'badge' => 'bg-amber-100 text-amber-700', 'border' => 'border-amber-200', 'btn' => 'bg-amber-600 hover:bg-amber-700'],
                                        'slate' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'icon' => 'text-slate-600', 'badge' => 'bg-slate-100 text-slate-700', 'border' => 'border-slate-200', 'btn' => 'bg-slate-700 hover:bg-slate-800'],
                                    ];
                                @endphp

                                @foreach($planos ?? $planosEstaticos as $index => $plano)
                                    @php
                                        $codigo = is_array($plano) ? $plano['codigo'] : $plano->codigo;
                                        $nome = is_array($plano) ? $plano['nome'] : $plano->nome;
                                        $creditos = is_array($plano) ? $plano['creditos'] : $plano->custo_creditos;
                                        $gratuito = is_array($plano) ? $plano['gratuito'] : $plano->is_gratuito;
                                        $descricao = is_array($plano) ? $plano['descricao'] : $plano->descricao;
                                    @endphp
                                    <label class="plano-option relative cursor-pointer block">
                                        <input
                                            type="radio"
                                            name="plano"
                                            value="{{ $codigo }}"
                                            data-creditos="{{ $creditos }}"
                                            class="sr-only peer"
                                            {{ $index === 0 ? 'checked' : '' }}
                                        >
                                        <div class="p-3 rounded-lg border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-gray-900">{{ $nome }}</span>
                                                    <span class="text-xs text-gray-500">- {{ $descricao }}</span>
                                                </div>
                                                @if($gratuito)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                                        Gratis
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                                        {{ $creditos }} cred.
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Resumo e Submit --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Custo:</p>
                                    <p class="text-lg font-bold text-gray-900">
                                        <span id="custo-total">0</span> creditos
                                    </p>
                                    <p class="text-xs text-gray-500">Saldo: <strong>{{ $credits ?? 0 }}</strong> creditos</p>
                                </div>
                                <button
                                    type="submit"
                                    id="btn-consultar"
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled
                                >
                                    <svg class="w-4 h-4 btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    <svg class="btn-spinner hidden w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span class="btn-text">Consultar</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Card Direito: Como Funciona --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm h-full flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">Como Funciona</h3>
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    {{-- Passo a passo --}}
                        <div class="mb-6 flex-shrink-0">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Como Funciona</h4>
                            <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">1</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Digite o CNPJ</p>
                                    <p class="text-xs text-gray-500">Informe o CNPJ do fornecedor ou cliente que deseja consultar. Opcionalmente, associe a um cliente para melhor organizacao.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">2</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Escolha o tipo de consulta</p>
                                    <p class="text-xs text-gray-500">Quanto mais completa, mais informacoes voce recebe</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">3</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Notificações</p>
                                    <p class="text-xs text-gray-500">Configure frequencia automatica de consultas (semanal, mensal ou trimestral) e receba notificacoes sobre alteracoes</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold">4</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Resultado salvo automaticamente</p>
                                    <p class="text-xs text-gray-500">O participante sera adicionado a sua lista para futuras consultas</p>
                                </div>
                            </div>
                            </div>
                        </div>

                        {{-- Planos disponiveis - Badges compactos --}}
                        <div class="border-t border-gray-200 pt-4 mt-4 flex-1 flex flex-col">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-semibold text-gray-900">Planos disponiveis</h4>
                                <button type="button" id="btn-ver-detalhes-planos" class="text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1">
                                    Ver detalhes
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="flex flex-col gap-2 w-full">
                                @foreach($planosDetalhados as $idx => $pd)
                                    @php
                                        $badgeCor = match($pd['cor']) {
                                            'green' => 'bg-green-100 text-green-700',
                                            'blue' => 'bg-blue-100 text-blue-700',
                                            'purple' => 'bg-purple-100 text-purple-700',
                                            'amber' => 'bg-amber-100 text-amber-700',
                                            'slate' => 'bg-slate-100 text-slate-700',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <button
                                        type="button"
                                        class="badge-plano group w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 hover:border-gray-300 transition-colors cursor-pointer text-left"
                                        data-slide-index="{{ $idx }}"
                                    >
                                        <div class="flex-1 min-w-0">
                                            <span class="text-xs font-semibold text-gray-800 group-hover:text-gray-900 transition-colors">{{ $pd['nome'] }}</span>
                                            <p class="text-xs text-gray-400 group-hover:text-gray-500 transition-colors truncate">{{ $pd['descricao'] }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $badgeCor }} transition-colors whitespace-nowrap flex-shrink-0">
                                            {{ $pd['gratuito'] ? 'Gratis' : $pd['creditos'] . ' cred.' }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-400 mt-2">Clique para ver detalhes.</p>
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
                    <p id="sucesso-mensagem" class="text-xs text-gray-500 mb-4">Resultado pronto para download.</p>
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
        <div id="modal-planos-carousel" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] flex flex-col relative">
                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">Detalhes dos Planos</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <span id="carousel-counter" class="text-xs text-gray-400">1 / 7</span>
                        <button type="button" id="btn-fechar-carousel" class="p-1 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Navigation arrows (overlay) --}}
                <button type="button" id="swiper-planos-prev" class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm border border-gray-200 shadow-md flex items-center justify-center text-gray-500 hover:bg-white hover:text-gray-700 hover:shadow-lg transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button type="button" id="swiper-planos-next" class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm border border-gray-200 shadow-md flex items-center justify-center text-gray-500 hover:bg-white hover:text-gray-700 hover:shadow-lg transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                {{-- Swiper Carousel --}}
                <div class="flex-1 overflow-hidden relative">
                    <div class="swiper h-full" id="swiper-planos">
                        <div class="swiper-wrapper">
                            @foreach($planosDetalhados as $idx => $pd)
                                @php $cores = $corClasses[$pd['cor']]; @endphp
                                <div class="swiper-slide">
                                    <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 140px);">
                                        {{-- Plan header --}}
                                        <div class="flex items-center gap-3 mb-4">
                                            <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ $cores['bg'] }} flex items-center justify-center">
                                                <svg class="w-5 h-5 {{ $cores['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $pd['icone'] }}"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="text-lg font-bold text-gray-900">{{ $pd['nome'] }}</h4>
                                                <span class="text-sm {{ $pd['gratuito'] ? 'text-green-600 font-medium' : 'text-gray-500' }}">
                                                    {{ $pd['gratuito'] ? 'Gratuito' : $pd['creditos'] . ' creditos/CNPJ' }}
                                                </span>
                                            </div>
                                            @if($pd['gratuito'])
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Gratis</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $cores['badge'] }}">{{ $pd['creditos'] }} cred.</span>
                                            @endif
                                        </div>

                                        {{-- Description --}}
                                        <p class="text-sm text-gray-600 mb-4">{{ $pd['descricao'] }}</p>

                                        {{-- Consultas incluidas --}}
                                        <div class="mb-4">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Consultas incluidas</p>
                                            <ul class="space-y-1.5">
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

                                        {{-- Selecionar este plano --}}
                                        <button
                                            type="button"
                                            class="btn-selecionar-plano w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg {{ $cores['btn'] }} text-white text-sm font-semibold transition-colors"
                                            data-plano-codigo="{{ $pd['codigo'] }}"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Selecionar este plano
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- Pagination dots --}}
                <div class="px-6 py-3 border-t border-gray-100 flex-shrink-0">
                    <div id="swiper-planos-pagination" class="flex justify-center"></div>
                </div>
            </div>
        </div>

        {{-- Secao: Participantes Cadastrados --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900">Participantes Cadastrados</h2>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            id="busca-participante"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-full sm:w-64"
                            placeholder="Buscar CNPJ ou razao social..."
                        >
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CNPJ</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razao Social</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Situacao</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Regime</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ultima Consulta</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white" id="participantes-tbody">
                        @forelse($participantes ?? [] as $participante)
                            <tr class="hover:bg-gray-50 participante-row" data-cnpj="{{ $participante->cnpj }}" data-razao="{{ $participante->razao_social ?? '' }}">
                                <td class="px-4 py-3 text-sm font-mono text-gray-900">
                                    {{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $participante->cnpj) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $participante->razao_social ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($participante->situacao_cadastral === 'ATIVA')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Ativa</span>
                                    @elseif($participante->situacao_cadastral === 'BAIXADA' || $participante->situacao_cadastral === 'INAPTA')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">{{ $participante->situacao_cadastral }}</span>
                                    @elseif($participante->situacao_cadastral)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">{{ $participante->situacao_cadastral }}</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $participante->regime_tributario ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $participante->ultima_consulta_em ? $participante->ultima_consulta_em->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <button
                                        type="button"
                                        class="btn-reconsultar inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm font-medium"
                                        data-cnpj="{{ $participante->cnpj }}"
                                        data-id="{{ $participante->id }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Reconsultar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="empty-row">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    Nenhum participante cadastrado ainda. Faca uma consulta para adicionar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    #swiper-planos-pagination .swiper-pagination-bullet {
        width: 8px;
        height: 8px;
        background: #d1d5db;
        opacity: 1;
        margin: 0 4px;
        border-radius: 50%;
        transition: all 0.2s;
    }
    #swiper-planos-pagination .swiper-pagination-bullet-active {
        background: #3b82f6;
        width: 20px;
        border-radius: 4px;
    }
</style>

<script>
(function() {
    'use strict';

    function initMonitoramentoAvulso() {
        const container = document.getElementById('monitoramento-avulso-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Monitoramento Avulso] Inicializando...');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const form = document.getElementById('form-consulta-avulsa');
        const cnpjInput = document.getElementById('cnpj-input');
        const clienteSelect = document.getElementById('cliente-select');
        const custoTotal = document.getElementById('custo-total');
        const btnConsultar = document.getElementById('btn-consultar');
        const buscaParticipante = document.getElementById('busca-participante');

        // Mascara de CNPJ
        function formatarCnpj(valor) {
            valor = valor.replace(/\D/g, '');
            if (valor.length > 14) valor = valor.slice(0, 14);

            if (valor.length > 12) {
                valor = valor.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2})/, '$1.$2.$3/$4-$5');
            } else if (valor.length > 8) {
                valor = valor.replace(/(\d{2})(\d{3})(\d{3})(\d{0,4})/, '$1.$2.$3/$4');
            } else if (valor.length > 5) {
                valor = valor.replace(/(\d{2})(\d{3})(\d{0,3})/, '$1.$2.$3');
            } else if (valor.length > 2) {
                valor = valor.replace(/(\d{2})(\d{0,3})/, '$1.$2');
            }
            return valor;
        }

        function extrairCnpj(texto) {
            if (!texto) return '';
            return texto.replace(/\D/g, '');
        }

        function getCreditosPlano() {
            const planoSelecionado = document.querySelector('input[name="plano"]:checked');
            return planoSelecionado ? parseInt(planoSelecionado.dataset.creditos || 0) : 0;
        }

        function atualizarCalculos() {
            const cnpj = extrairCnpj(cnpjInput.value);
            const creditosPlano = getCreditosPlano();
            const total = cnpj.length === 14 ? creditosPlano : 0;

            custoTotal.textContent = total.toLocaleString('pt-BR');
            btnConsultar.disabled = cnpj.length !== 14;
        }

        // Event listeners
        if (cnpjInput) {
            cnpjInput.addEventListener('input', function(e) {
                e.target.value = formatarCnpj(e.target.value);
                atualizarCalculos();
            });
        }

        // Mudanca de plano
        document.querySelectorAll('input[name="plano"]').forEach(function(radio) {
            radio.addEventListener('change', atualizarCalculos);
        });

        // Busca de participantes
        if (buscaParticipante) {
            buscaParticipante.addEventListener('input', function() {
                const termo = this.value.toLowerCase().replace(/\D/g, '');
                const termoTexto = this.value.toLowerCase();
                const rows = document.querySelectorAll('.participante-row');

                rows.forEach(function(row) {
                    const cnpj = row.dataset.cnpj || '';
                    const razao = (row.dataset.razao || '').toLowerCase();

                    if (cnpj.includes(termo) || razao.includes(termoTexto) || !termoTexto) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Reconsultar participante
        document.querySelectorAll('.btn-reconsultar').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const cnpj = this.dataset.cnpj;
                if (cnpjInput && cnpj) {
                    cnpjInput.value = formatarCnpj(cnpj);
                    atualizarCalculos();

                    // Scroll para o formulario
                    document.querySelector('#form-consulta-avulsa').scrollIntoView({ behavior: 'smooth', block: 'start' });

                    // Highlight visual
                    cnpjInput.classList.add('ring-2', 'ring-blue-500');
                    setTimeout(function() {
                        cnpjInput.classList.remove('ring-2', 'ring-blue-500');
                    }, 2000);
                }
            });
        });

        // Modal helpers
        let eventSource = null;
        let consultaLoteId = null;

        function showModal(tipo) {
            document.getElementById('modal-' + tipo)?.classList.remove('hidden');
        }

        function hideModal(tipo) {
            document.getElementById('modal-' + tipo)?.classList.add('hidden');
        }

        function updateProgresso(percentual, mensagem) {
            const barra = document.getElementById('progresso-barra');
            const pct = document.getElementById('progresso-percentual');
            const msg = document.getElementById('progresso-mensagem');
            if (barra) barra.style.width = percentual + '%';
            if (pct) pct.textContent = percentual + '%';
            if (msg && mensagem) msg.textContent = mensagem;
        }

        function fecharSSE() {
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }
        }

        function resetarBotao() {
            const btnText = btnConsultar.querySelector('.btn-text');
            const btnSpinner = btnConsultar.querySelector('.btn-spinner');
            const btnIcon = btnConsultar.querySelector('.btn-icon');
            btnConsultar.disabled = false;
            if (btnText) btnText.textContent = 'Consultar';
            if (btnSpinner) btnSpinner.classList.add('hidden');
            if (btnIcon) btnIcon.classList.remove('hidden');
            atualizarCalculos();
        }

        function iniciarSSE(tabId) {
            fecharSSE();

            const url = '/app/consultas/nova/progresso/stream?tab_id=' + encodeURIComponent(tabId);
            eventSource = new EventSource(url);

            eventSource.addEventListener('progresso', function(e) {
                try {
                    const data = JSON.parse(e.data);
                    const pct = data.progresso || 0;
                    const msg = data.mensagem || 'Processando...';
                    const status = data.status || '';

                    updateProgresso(pct, msg);

                    if (status === 'concluido') {
                        fecharSSE();
                        hideModal('progresso');

                        // Configurar link de download
                        const linkDownload = document.getElementById('link-download-manual');
                        if (linkDownload && consultaLoteId) {
                            linkDownload.href = '/app/consultas/lote/' + consultaLoteId + '/baixar';
                        }

                        showModal('sucesso');
                        resetarBotao();
                    } else if (status === 'erro') {
                        fecharSSE();
                        hideModal('progresso');

                        const erroMsg = document.getElementById('erro-mensagem');
                        if (erroMsg) erroMsg.textContent = msg || 'Ocorreu um erro no processamento.';

                        showModal('erro');
                        resetarBotao();
                    }
                } catch (err) {
                    console.error('[Avulso SSE] Erro ao parsear:', err);
                }
            });

            eventSource.onerror = function() {
                console.warn('[Avulso SSE] Conexao perdida, tentando reconectar...');
            };
        }

        // Fechar modais
        document.getElementById('btn-fechar-sucesso')?.addEventListener('click', function() {
            hideModal('sucesso');
        });
        document.getElementById('btn-fechar-erro')?.addEventListener('click', function() {
            hideModal('erro');
        });

        // Submit do formulario
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const cnpj = extrairCnpj(cnpjInput.value);
                if (cnpj.length !== 14) {
                    if (window.showToast) {
                        window.showToast('warning', 'Por favor, insira um CNPJ valido com 14 digitos.');
                    } else {
                        alert('Por favor, insira um CNPJ valido com 14 digitos.');
                    }
                    return;
                }

                const planoSelecionado = document.querySelector('input[name="plano"]:checked');
                if (!planoSelecionado) {
                    if (window.showToast) {
                        window.showToast('warning', 'Por favor, selecione um tipo de consulta.');
                    } else {
                        alert('Por favor, selecione um tipo de consulta.');
                    }
                    return;
                }

                const btnText = btnConsultar.querySelector('.btn-text');
                const btnSpinner = btnConsultar.querySelector('.btn-spinner');
                const btnIcon = btnConsultar.querySelector('.btn-icon');

                btnConsultar.disabled = true;
                if (btnText) btnText.textContent = 'Consultando...';
                if (btnSpinner) btnSpinner.classList.remove('hidden');
                if (btnIcon) btnIcon.classList.add('hidden');

                const tabId = crypto.randomUUID();

                try {
                    const payload = {
                        cnpj: cnpj,
                        plano: planoSelecionado.value,
                        tab_id: tabId,
                    };

                    // Adicionar cliente_id se selecionado
                    if (clienteSelect && clienteSelect.value) {
                        payload.cliente_id = clienteSelect.value;
                    }

                    const response = await fetch('/app/monitoramento/consulta-avulsa', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        consultaLoteId = data.consulta_lote_id;

                        // Mostrar modal de progresso e iniciar SSE
                        updateProgresso(0, 'Iniciando consulta...');
                        showModal('progresso');
                        iniciarSSE(tabId);

                        if (window.showToast) {
                            window.showToast('success', 'Consulta iniciada!');
                        }
                    } else {
                        throw new Error(data.error || data.message || 'Erro ao realizar consulta');
                    }
                } catch (err) {
                    console.error('[Monitoramento Avulso] Erro:', err);
                    resetarBotao();

                    const erroMsg = document.getElementById('erro-mensagem');
                    if (erroMsg) erroMsg.textContent = err.message || 'Erro ao realizar consulta.';
                    showModal('erro');
                }
            });
        }

        // ==========================================
        // Modal Carousel de Planos
        // ==========================================
        var swiperPlanos = null;
        var modalPlanos = document.getElementById('modal-planos-carousel');

        function showPlanosModal(startIndex) {
            if (!modalPlanos) return;
            modalPlanos.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            setTimeout(function() {
                if (swiperPlanos && !swiperPlanos.destroyed) {
                    swiperPlanos.slideToLoop(startIndex || 0, 0);
                    swiperPlanos.update();
                    updateCounter(startIndex || 0);
                    return;
                }

                swiperPlanos = new Swiper('#swiper-planos', {
                    slidesPerView: 1,
                    spaceBetween: 0,
                    loop: true,
                    initialSlide: startIndex || 0,
                    navigation: {
                        prevEl: '#swiper-planos-prev',
                        nextEl: '#swiper-planos-next',
                    },
                    pagination: {
                        el: '#swiper-planos-pagination',
                        clickable: true,
                    },
                    on: {
                        slideChange: function() {
                            updateCounter(this.realIndex);
                        },
                    },
                });

                updateCounter(startIndex || 0);
            }, 50);
        }

        function hidePlanosModal() {
            if (!modalPlanos) return;
            modalPlanos.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function updateCounter(index) {
            var counter = document.getElementById('carousel-counter');
            if (counter) {
                counter.textContent = (index + 1) + ' / 7';
            }
        }

        // Close modal: overlay click
        if (modalPlanos) {
            modalPlanos.addEventListener('click', function(e) {
                if (e.target === modalPlanos) {
                    hidePlanosModal();
                }
            });
        }

        // Close modal: ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modalPlanos && !modalPlanos.classList.contains('hidden')) {
                hidePlanosModal();
            }
        });

        // Close modal: X button
        document.getElementById('btn-fechar-carousel')?.addEventListener('click', hidePlanosModal);

        // "Ver detalhes" button -> open modal at slide 0
        var btnVerDetalhes = document.getElementById('btn-ver-detalhes-planos');
        if (btnVerDetalhes) {
            btnVerDetalhes.addEventListener('click', function() {
                showPlanosModal(0);
            });
        }

        // Badge clicks -> open modal at specific slide
        document.querySelectorAll('.badge-plano').forEach(function(badge) {
            badge.addEventListener('click', function() {
                var idx = parseInt(this.dataset.slideIndex) || 0;
                showPlanosModal(idx);
            });
        });

        // "Selecionar este plano" buttons
        document.querySelectorAll('.btn-selecionar-plano').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var codigo = this.dataset.planoCodigo;
                var radio = document.querySelector('input[name="plano"][value="' + codigo + '"]');
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                }
                hidePlanosModal();

                if (window.showToast) {
                    window.showToast('success', 'Plano selecionado!');
                }
            });
        });

        // Inicializar calculos
        atualizarCalculos();

        console.log('[Monitoramento Avulso] Inicializacao concluida');
    }

    // Expor globalmente para SPA
    window.initMonitoramentoAvulso = initMonitoramentoAvulso;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMonitoramentoAvulso, { once: true });
    } else {
        initMonitoramentoAvulso();
    }
})();
</script>
