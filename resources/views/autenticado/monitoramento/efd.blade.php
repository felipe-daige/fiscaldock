{{-- Monitoramento - Importar EFD --}}
<div class="min-h-screen bg-gray-50" id="importacao-efd-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <style>
            @keyframes card-slide-in {
                from { opacity: 0; transform: translateY(60px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .efd-animate {
                opacity: 0;
                animation: card-slide-in 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            @media (prefers-reduced-motion: reduce) {
                .efd-animate { opacity: 1; animation: none; }
            }
        </style>

        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Importar Participantes</h1>
                    <p class="mt-1 text-sm text-gray-600">Adicione CNPJs à sua lista de monitoramento a partir de arquivos EFD ou relatórios RAF.</p>
                </div>
                <a
                    href="/app/dashboard"
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
        <div class="efd-animate bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-blue-900">Como funciona?</h3>
                    <p class="text-sm text-blue-800 mt-1">
                        Faça upload do seu arquivo EFD (.txt) e o sistema extrairá automaticamente todos os CNPJs de fornecedores e clientes registrados no bloco de participantes (registro 0150). Os CNPJs extraídos são adicionados à sua base de participantes, ficando disponíveis para consultas, monitoramento e análises de compliance.
                    </p>
                </div>
            </div>
        </div>

        {{-- Seção Importar de Arquivo .txt --}}
        <div class="mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Card Upload (Esquerdo) --}}
                <div class="efd-animate bg-white rounded-xl border border-gray-200 shadow-sm" style="animation-delay: 0.1s">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">Enviar Arquivo</h3>
                    </div>
                    <div class="p-6">
                        {{-- Seleção Tipo EFD --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de EFD:</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition tipo-efd-label border-gray-200 hover:border-gray-300 hover:bg-gray-500/8" data-tipo="efd-fiscal">
                                    <input type="radio" name="tipo-efd" value="efd-fiscal" class="w-4 h-4 text-gray-600 border-gray-300 flex-shrink-0">
                                    <div class="flex-shrink-0 w-7 h-7 rounded-md bg-blue-100 flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900">EFD Fiscal</span>
                                            <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">Grátis</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5 truncate">ICMS/IPI - Escrituração Fiscal Digital</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition tipo-efd-label border-gray-200 hover:border-gray-300 hover:bg-gray-500/8" data-tipo="efd-contrib">
                                    <input type="radio" name="tipo-efd" value="efd-contrib" class="w-4 h-4 text-gray-600 border-gray-300 flex-shrink-0">
                                    <div class="flex-shrink-0 w-7 h-7 rounded-md bg-purple-100 flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900">EFD Contribuições</span>
                                            <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">Grátis</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5 truncate">PIS/COFINS - Contribuições Federais</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Opção Extrair Notas Fiscais --}}
                        <div class="relative mb-4 group">
                            <label class="flex items-start p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 transition">
                                <input type="checkbox"
                                       id="extrair-notas"
                                       name="extrair_notas"
                                       class="mt-1 mr-3 w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-800 text-sm">Extrair Notas Fiscais</span>
                                    </div>
                                    <div class="text-xs text-gray-600 mt-0.5">
                                        Importa também as notas fiscais para análise de BI Fiscal
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center gap-1 text-xs text-green-600">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Gratuito
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        {{-- Instruções --}}
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start gap-2 mb-3">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h4 class="text-sm font-semibold text-blue-900">Instruções</h4>
                            </div>
                            <div class="space-y-2 text-xs text-blue-800">
                                <div>
                                    <strong class="text-blue-900">1. Selecione o tipo:</strong> Escolha entre EFD Fiscal ou EFD Contribuições.
                                </div>
                                <div>
                                    <strong class="text-blue-900">2. Faça upload:</strong> Envie o arquivo .txt exportado do seu sistema contábil (EFD válido).
                                </div>
                                <div>
                                    <strong class="text-blue-900">3. Aguarde o processamento:</strong> O progresso será exibido em tempo real. Ao finalizar, você verá o resumo com participantes novos e duplicados.
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div id="txt-dropzone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 min-h-[180px] flex flex-col items-center justify-center transition-colors cursor-not-allowed bg-gray-100 opacity-60 pointer-events-none" role="button" tabindex="0" aria-disabled="true">
                                <div class="mb-4">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <div class="space-y-1 text-center">
                                    <p class="text-sm font-medium text-gray-500" id="txt-dropzone-main-text">Selecione o tipo de EFD primeiro</p>
                                    <p class="text-xs text-gray-400" id="txt-dropzone-sub-text">Depois arraste o arquivo .txt aqui ou clique para selecionar</p>
                                    <p class="text-xs text-gray-400 mt-2">.txt | Máximo: 10MB</p>
                                </div>
                                <input
                                    type="file"
                                    id="txt-file-input"
                                    name="txt_file"
                                    accept=".txt"
                                    class="hidden"
                                    disabled
                                >
                            </div>
                        </div>

                        <div id="txt-file-meta" class="mb-4 hidden">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <div>
                                        <div class="text-xs font-medium text-gray-800" id="txt-file-name">arquivo.txt</div>
                                        <div class="text-xs text-gray-500" id="txt-file-size">0 MB</div>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    id="txt-change-file"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div id="txt-error-message" class="mb-4 hidden">
                            <div class="flex items-start gap-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <svg class="w-4 h-4 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-xs text-red-800" id="txt-error-text"></p>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="button"
                                id="txt-importar-btn"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled
                                title="Funcionalidade em desenvolvimento"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Importar
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Card Informações (Direito) --}}
                <div class="efd-animate bg-white rounded-xl border border-gray-200 shadow-sm" style="animation-delay: 0.2s">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="text-base font-semibold text-gray-900">Como Funciona</h3>
                        </div>
                    </div>
                <div class="p-6 space-y-6">
                    {{-- Seção Como Funciona --}}
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Como Funciona</h4>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">1</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Importação</p>
                                    <p class="text-xs text-gray-500">Adicione CNPJs via arquivo EFD .txt ou a partir de relatórios RAF já processados.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">2</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Consultas</p>
                                    <p class="text-xs text-gray-500">Execute consultas avulsas ou configure frequência automática (semanal, mensal ou trimestral).</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">3</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Alertas</p>
                                    <p class="text-xs text-gray-500">Receba notificações sobre alterações na situação cadastral ou fiscal dos CNPJs.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold">4</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Histórico</p>
                                    <p class="text-xs text-gray-500">Consulte o histórico completo de cada CNPJ monitorado.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Planos disponíveis - Badges dinâmicos --}}
                    @php
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
                                'creditos_original' => null,
                                'promo' => $meta['promo'] ?? false,
                                'gratuito' => $p->is_gratuito,
                                'descricao' => $p->descricao,
                                'cor' => $meta['cor'],
                                'icone' => $meta['icone'],
                                'consultas' => $meta['consultas_display'],
                                'casos_uso' => $meta['casos_uso'],
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

                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-900">Planos disponíveis</h4>
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
                                @if($pd['promo'] ?? false)
                                    <button
                                        type="button"
                                        class="badge-plano group w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg
                                               border border-amber-200 bg-amber-50 hover:bg-amber-100 hover:border-amber-300
                                               transition-colors cursor-pointer text-left"
                                        data-slide-index="{{ $idx }}"
                                    >
                                        <div class="flex-1 min-w-0">
                                            <span class="text-xs font-semibold text-gray-800 group-hover:text-gray-900 transition-colors">{{ $pd['nome'] }}</span>
                                            <p class="text-xs text-gray-400 group-hover:text-gray-500 transition-colors truncate">{{ $pd['descricao'] }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-700 whitespace-nowrap flex-shrink-0">
                                            {{ $pd['creditos'] }} cred.
                                        </span>
                                    </button>
                                @else
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
                                            {{ $pd['gratuito'] ? 'Grátis' : $pd['creditos'] . ' cred.' }}
                                        </span>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Clique para ver detalhes.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Histórico de Importações EFD --}}
        <div id="historico-importacoes" class="efd-animate bg-white rounded-xl border border-gray-200 shadow-sm mt-6 mb-6" style="animation-delay: 0.3s">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">Histórico de Importações</h3>
                <a href="/app/importacao/historico" data-link class="text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1">
                    Ver tudo
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>

            @if($importacoes->isNotEmpty())
            <div class="divide-y divide-gray-100">
                @foreach($importacoes as $imp)
                @php
                    [$statusClass, $statusLabel] = match($imp->status) {
                        'concluido'   => ['bg-green-100 text-green-700', 'Concluído'],
                        'processando' => ['bg-blue-100 text-blue-700', 'Processando'],
                        'erro'        => ['bg-red-100 text-red-700', 'Erro'],
                        default       => ['bg-gray-100 text-gray-700', 'Pendente'],
                    };
                    $tipoLabel = $imp->tipo_efd === 'EFD PIS/COFINS' ? 'PIS/COFINS' : 'ICMS/IPI';
                    $tipoBadge = $imp->tipo_efd === 'EFD PIS/COFINS'
                        ? 'bg-purple-100 text-purple-700'
                        : 'bg-blue-100 text-blue-700';
                    $filename = $imp->filename ?? 'Importação #'.$imp->id;
                    $clienteNome = $imp->cliente->razao_social ?? null;
                    $totalPart = ($imp->novos ?? 0) + ($imp->duplicados ?? 0);
                @endphp
                <a
                    href="/app/importacao/efd/{{ $imp->id }}"
                    data-link
                    class="group flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 transition-colors"
                >
                    {{-- Ícone --}}
                    <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center group-hover:bg-blue-50 transition-colors">
                        <svg class="w-4.5 h-4.5 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>

                    {{-- Conteúdo --}}
                    <div class="flex-1 min-w-0">
                        {{-- Linha 1: arquivo + badges --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-medium text-gray-900 truncate max-w-[200px] sm:max-w-[300px] group-hover:text-blue-600 transition-colors">{{ $filename }}</span>
                            <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded {{ $tipoBadge }}">{{ $tipoLabel }}</span>
                            <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                        {{-- Linha 2: meta --}}
                        <div class="flex items-center gap-1.5 mt-0.5 text-xs text-gray-400 flex-wrap">
                            @if($clienteNome)
                                <span class="text-gray-500 truncate max-w-[150px]">{{ $clienteNome }}</span>
                                <span>&middot;</span>
                            @endif
                            <span>{{ $totalPart }} participante{{ $totalPart !== 1 ? 's' : '' }}</span>
                            <span>&middot;</span>
                            <span>{{ $imp->tempo_processamento }}</span>
                            <span>&middot;</span>
                            <span>{{ $imp->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    {{-- Seta --}}
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition-colors flex-shrink-0 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @endforeach
            </div>

            {{-- Paginação --}}
            @if($importacoes->hasPages())
            <div class="px-6 py-3 flex items-center justify-between gap-4 text-sm border-t border-gray-100">
                <span class="text-gray-500 text-xs">
                    Mostrando {{ $importacoes->firstItem() }}–{{ $importacoes->lastItem() }} de {{ $importacoes->total() }}
                </span>
                <div class="flex items-center gap-1">
                    @if($importacoes->onFirstPage())
                        <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 text-xs cursor-not-allowed">Anterior</span>
                    @else
                        <a href="{{ $importacoes->previousPageUrl() }}" data-link class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 transition">Anterior</a>
                    @endif

                    <span class="px-3 py-1.5 text-xs text-gray-500">{{ $importacoes->currentPage() }} / {{ $importacoes->lastPage() }}</span>

                    @if($importacoes->hasMorePages())
                        <a href="{{ $importacoes->nextPageUrl() }}" data-link class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 transition">Próxima</a>
                    @else
                        <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 text-xs cursor-not-allowed">Próxima</span>
                    @endif
                </div>
            </div>
            @endif

            @else
            {{-- Zero state --}}
            <div class="px-6 py-12 text-center">
                <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                <p class="text-sm text-gray-500">Nenhuma importação EFD realizada ainda.</p>
                <p class="text-xs text-gray-400 mt-1">Suas importações aparecerão aqui.</p>
            </div>
            @endif
        </div>

        {{-- Modal: Carousel de Planos --}}
        <div id="modal-planos-carousel" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
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
                        <span id="carousel-counter" class="text-xs text-gray-400">1 / {{ count($planosDetalhados) }}</span>
                        <button type="button" id="btn-fechar-carousel" class="p-1 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Navigation arrows (overlay) --}}
                <button type="button" id="swiper-planos-prev" class="absolute -left-5 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm border border-gray-200 shadow-md flex items-center justify-center text-gray-500 hover:bg-white hover:text-gray-700 hover:shadow-lg transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button type="button" id="swiper-planos-next" class="absolute -right-5 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm border border-gray-200 shadow-md flex items-center justify-center text-gray-500 hover:bg-white hover:text-gray-700 hover:shadow-lg transition-all cursor-pointer">
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
                                                        {{ $pd['gratuito'] ? 'Gratuito' : $pd['creditos'] . ' créditos/CNPJ' }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($pd['promo'] ?? false)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">{{ $pd['creditos'] }} cred.</span>
                                            @elseif($pd['gratuito'])
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Grátis</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $cores['badge'] }}">{{ $pd['creditos'] }} cred.</span>
                                            @endif
                                        </div>

                                        {{-- Description --}}
                                        <p class="text-sm text-gray-600 mb-3">{{ $pd['descricao'] }}</p>

                                        @if($pd['promo'] ?? false)
                                            <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-lg mb-3">
                                                <p class="text-xs font-semibold text-amber-800">&#x1f3f7;&#xfe0e; Oferta por tempo limitado</p>
                                                <p class="text-xs text-amber-700 mt-0.5">Todas as CNDs por {{ $pd['creditos'] }} créd./CNPJ — aproveite antes do reajuste.</p>
                                            </div>
                                        @endif

                                        {{-- Consultas incluidas --}}
                                        <div class="mb-3">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Consultas incluídas</p>
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

                {{-- Pagination dots --}}
                <div class="px-6 py-3 border-t border-gray-100 flex-shrink-0">
                    <div id="swiper-planos-pagination" class="flex justify-center"></div>
                </div>
            </div>
        </div>

        {{-- Seção de Progresso de Importação (inicialmente oculta) --}}
        <div id="importacao-progresso" class="hidden">
            <div id="progresso-card" class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                {{-- Header: Empresa e documento --}}
                <div class="flex items-start gap-3 mb-4">
                    <div id="progresso-icon" class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 id="progresso-empresa" class="font-semibold text-gray-900 truncate">
                            Aguardando dados...
                        </h3>
                        <p id="progresso-documento" class="text-sm text-gray-500 hidden">
                            {{-- Tipo EFD • Período --}}
                        </p>
                    </div>
                </div>

                {{-- Barra de progresso --}}
                <div class="mb-3">
                    <div class="flex justify-between text-sm mb-1">
                        <span id="progresso-mensagem" class="text-gray-600">Iniciando...</span>
                        <span id="progresso-porcentagem" class="font-medium text-gray-900">0%</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div id="barra-progresso" class="bg-blue-600 h-full rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
                    </div>
                </div>

                {{-- Mensagem de erro (só aparece em caso de erro) --}}
                <div id="progresso-erro" class="hidden pt-3 border-t border-red-100">
                    <p id="progresso-erro-msg" class="text-sm text-gray-700 mb-3">
                        Ocorreu um erro interno durante o processamento.
                    </p>
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

            {{-- Strip horizontal de etapas EFD --}}
            <div id="etapas-notas-card" class="hidden mt-3 flex items-center gap-1.5 flex-wrap">
                <div class="etapa-item inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-400" data-etapa="participantes">
                    <span class="etapa-icon flex items-center justify-center w-3.5 h-3.5"></span>
                    <span>Participantes</span>
                </div>
                <svg class="etapa-sep w-3 h-3 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <div class="etapa-item inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-400" data-etapa="A">
                    <span class="etapa-icon flex items-center justify-center w-3.5 h-3.5"></span>
                    <span>Bloco A</span>
                </div>
                <svg class="etapa-sep w-3 h-3 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <div class="etapa-item inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-400" data-etapa="C">
                    <span class="etapa-icon flex items-center justify-center w-3.5 h-3.5"></span>
                    <span>Bloco C</span>
                </div>
                <svg class="etapa-sep w-3 h-3 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <div class="etapa-item inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-400" data-etapa="D">
                    <span class="etapa-icon flex items-center justify-center w-3.5 h-3.5"></span>
                    <span>Bloco D</span>
                </div>
            </div>

            {{-- Seção de Resultados da Importação (aparece após importação concluída) --}}
            <div id="resultado-importacao" class="hidden mt-4">
                <div class="bg-white border border-green-200 rounded-lg shadow-sm">
                    {{-- Header dos Resultados --}}
                    <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Importação Concluída</h3>
                                    <p class="text-sm text-gray-600" id="resultado-empresa">-</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                id="btn-nova-importacao"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Nova Importação
                            </button>
                        </div>
                    </div>

                    {{-- Estatísticas da Importação --}}
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-2xl font-bold text-gray-900" id="resultado-total-participantes">0</p>
                                <p class="text-xs text-gray-500">Total CNPJs</p>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <p class="text-2xl font-bold text-green-600" id="resultado-novos">0</p>
                                <p class="text-xs text-gray-500">Novos</p>
                            </div>
                            <div class="text-center p-3 bg-yellow-50 rounded-lg">
                                <p class="text-2xl font-bold text-yellow-600" id="resultado-duplicados">0</p>
                                <p class="text-xs text-gray-500">Duplicados</p>
                            </div>
                        </div>
                        {{-- Notas Fiscais Extraídas (aparece apenas se extrair_notas=true) --}}
                        <div id="resultado-notas" class="hidden mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-purple-700" id="notas-extraidas-count">0</p>
                                        <p class="text-xs text-purple-600">Notas Fiscais Extraídas</p>
                                    </div>
                                </div>
                                <a href="/app/bi/dashboard" class="text-sm text-purple-700 hover:text-purple-800 font-medium hover:underline" data-link>
                                    Ver no BI Fiscal →
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Mini-painel Resumo Final de Notas EFD (aparece quando n8n envia resumo_final) --}}
                    <div id="resumo-final-notas" class="hidden px-6 py-4 border-b border-gray-200">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Resumo de Notas Importadas</p>
                        <div id="resumo-final-notas-content" class="space-y-1 text-sm font-mono bg-gray-50 rounded-lg p-3 border border-gray-200">
                            {{-- Preenchido via JS --}}
                        </div>
                    </div>

                    {{-- Cliente Associado (visível apenas quando cliente_id é informado via API) --}}
                    <div id="resultado-cliente" class="hidden px-6 py-4 border-b border-gray-200">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Cliente Associado</p>
                        <div class="flex items-center gap-6 flex-wrap">
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Razão Social</p>
                                <a id="resultado-cliente-nome-link" href="#" data-link
                                   class="text-sm font-semibold text-blue-700 hover:text-blue-800 hover:underline cursor-pointer">
                                    <span id="resultado-cliente-nome">—</span>
                                </a>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5" id="resultado-cliente-doc-label">Documento</p>
                                <p class="text-sm font-mono text-gray-900" id="resultado-cliente-doc">—</p>
                            </div>
                            <div class="ml-auto">
                                <a id="resultado-cliente-link" href="#" data-link
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-gray-700 text-xs font-semibold hover:bg-gray-50 transition">
                                    Ver no cadastro
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Lista de Participantes Importados --}}
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-semibold text-gray-900">Participantes Importados</h4>
                            <button
                                type="button"
                                id="btn-carregar-participantes"
                                class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                            >
                                Carregar lista
                            </button>
                        </div>

                        {{-- Container da lista (inicialmente mostra placeholder) --}}
                        <div id="lista-participantes-container">
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="text-sm">Clique em "Carregar lista" para ver os participantes importados</p>
                            </div>
                        </div>

                        {{-- Loading state --}}
                        <div id="lista-participantes-loading" class="hidden text-center py-8">
                            <svg class="w-8 h-8 mx-auto text-blue-600 animate-spin mb-3" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <p class="text-sm text-gray-500">Carregando participantes...</p>
                        </div>

                        {{-- Tabela de participantes (preenchida via JS) --}}
                        <div id="lista-participantes-tabela" class="hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">CNPJ</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Razão Social</th>
                                            <th class="px-2 py-2 text-center text-xs font-semibold text-gray-500 uppercase w-12">UF</th>
                                            <th class="px-2 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Notas</th>
                                            <th class="px-2 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Entradas</th>
                                            <th class="px-2 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Saídas</th>
                                            <th class="px-2 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="participantes-tbody-resultado" class="divide-y divide-gray-200">
                                        {{-- Preenchido via JS --}}
                                    </tbody>
                                </table>
                            </div>
                            <div id="participantes-pagination" class="mt-6 py-2 flex items-center justify-between text-sm text-gray-500">
                                <span id="participantes-info">Mostrando 0 de 0</span>
                                <div class="flex gap-3">
                                    <button type="button" id="btn-prev-page" class="px-3 py-1 rounded border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50" disabled>Anterior</button>
                                    <button type="button" id="btn-next-page" class="px-3 py-1 rounded border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50" disabled>Próximo</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Ações --}}
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                            <a
                                href="/app/dashboard"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition"
                                data-link
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                Ver Todos os Participantes
                            </a>
                            <a
                                id="link-filtrar-importacao"
                                href="/app/importacao/efd/"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm hover:bg-blue-700 transition"
                                data-link
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Ver Detalhes da Importação
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modal Monitorar Participante Individual (EFD) --}}
<div id="modal-monitorar-individual-efd" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Configurar Monitoramento</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="px-6 py-4">
            {{-- Info do participante --}}
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 mb-1">Participante</p>
                <p class="text-sm font-mono font-semibold text-gray-900" id="modal-monitorar-cnpj-efd">00.000.000/0001-00</p>
                <p class="text-sm text-gray-600" id="modal-monitorar-razao-efd">Razão Social</p>
            </div>

            {{-- Selecao de plano --}}
            <p class="text-sm font-medium text-gray-700 mb-3">Selecione o plano de monitoramento:</p>
            <div class="space-y-2">
                @php
                    $planosDisponiveis = [
                        ['id' => 'basico', 'nome' => 'Básico', 'creditos' => 0, 'gratuito' => true, 'descricao' => 'Dados cadastrais + Simples/MEI'],
                        ['id' => 'cadastral_plus', 'nome' => 'Cadastral+', 'creditos' => 3, 'gratuito' => false, 'descricao' => 'Básico + SINTEGRA + TCU Consolidada'],
                        ['id' => 'fiscal_federal', 'nome' => 'Fiscal Federal', 'creditos' => 6, 'gratuito' => false, 'descricao' => 'Cadastral+ + CND Federal + CRF FGTS'],
                        ['id' => 'fiscal_completo', 'nome' => 'Fiscal Completo', 'creditos' => 12, 'gratuito' => false, 'descricao' => 'Fiscal Federal + CND Estadual + CNDT'],
                        ['id' => 'due_diligence', 'nome' => 'Due Diligence', 'creditos' => 16, 'gratuito' => false, 'descricao' => 'Fiscal Completo + Lista Devedores PGFN'],
                        ['id' => 'esg', 'nome' => 'ESG', 'creditos' => 6, 'gratuito' => false, 'descricao' => 'Trabalho Escravo + IBAMA Autuações'],
                        ['id' => 'completo', 'nome' => 'Completo', 'creditos' => 22, 'gratuito' => false, 'descricao' => 'Todas as consultas disponíveis'],
                    ];
                @endphp
                @foreach($planosDisponiveis as $plano)
                    <label class="plano-option flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="plano_selecionado_efd" value="{{ $plano['id'] }}" data-creditos="{{ $plano['creditos'] }}" class="text-blue-600 focus:ring-blue-500">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900">{{ $plano['nome'] }}</span>
                                @if($plano['gratuito'])
                                    <span class="px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Grátis</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ $plano['creditos'] }} cred.</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $plano['descricao'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>

            {{-- Resumo --}}
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Frequência:</span>
                    <span class="font-medium text-gray-900">Mensal (30 dias)</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-gray-600">Custo por consulta:</span>
                    <span class="font-semibold text-blue-600" id="modal-monitorar-custo-efd">0 créditos</span>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
            <button type="button" class="modal-close px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                Cancelar
            </button>
            <button type="button" id="btn-confirmar-monitorar-efd" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                Ativar Monitoramento
            </button>
        </div>
    </div>
</div>
<input type="hidden" id="modal-monitorar-participante-id-efd" value="">

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
    .etapa-item {
        transition: opacity 300ms ease;
    }
    .etapa-icon {
        transition: background-color 300ms ease, color 300ms ease;
    }
</style>

<script>
(function() {
    'use strict';

    function initImportacaoEfd() {
        const container = document.getElementById('importacao-efd-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Monitoramento EFD] Inicializando...');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Identificador único por aba para isolar notificações SSE
        // Usa 'let' para permitir regeneração ao tentar novamente
        let tabId = crypto.randomUUID ? crypto.randomUUID() :
            (Date.now().toString(36) + Math.random().toString(36).substr(2));

        // Função para gerar novo tabId
        function regenerarTabId() {
            tabId = crypto.randomUUID ? crypto.randomUUID() :
                (Date.now().toString(36) + Math.random().toString(36).substr(2));
            console.log('[Monitoramento EFD] Novo tabId gerado:', tabId);
        }

        // ===== Funcionalidade de Upload de Arquivo .txt =====
        const txtDropzone = document.getElementById('txt-dropzone');
        const txtFileInput = document.getElementById('txt-file-input');
        const txtFileMeta = document.getElementById('txt-file-meta');
        const txtFileName = document.getElementById('txt-file-name');
        const txtFileSize = document.getElementById('txt-file-size');
        const txtChangeFile = document.getElementById('txt-change-file');
        const txtImportarBtn = document.getElementById('txt-importar-btn');
        const txtErrorMessage = document.getElementById('txt-error-message');
        const txtErrorText = document.getElementById('txt-error-text');
        const tipoEfdRadios = document.querySelectorAll('input[name="tipo-efd"]');

        const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

        // Função para obter tipo EFD selecionado
        function getSelectedTipoEfd() {
            const selected = Array.from(tipoEfdRadios).find(radio => radio.checked);
            return selected ? selected.value : '';
        }

        // Função para atualizar visual dos labels do tipo EFD
        function updateTipoEfdLabels() {
            const selectedValue = getSelectedTipoEfd();
            document.querySelectorAll('.tipo-efd-label').forEach(function(label) {
                const radio = label.querySelector('input[type="radio"]');
                if (radio && radio.value === selectedValue) {
                    label.classList.remove('border-gray-200', 'hover:border-gray-300', 'hover:bg-gray-500/8');
                    label.classList.add('border-blue-500', 'bg-blue-50/60', 'ring-2', 'ring-blue-100');
                } else {
                    label.classList.remove('border-blue-500', 'bg-blue-50/60', 'ring-2', 'ring-blue-100');
                    label.classList.add('border-gray-200', 'hover:border-gray-300', 'hover:bg-gray-500/8');
                }
            });
        }

        // Função para atualizar estado do dropzone
        function updateDropzoneState() {
            const hasTipoEfd = getSelectedTipoEfd() !== '';
            const dropzoneMainText = document.getElementById('txt-dropzone-main-text');
            const dropzoneSubText = document.getElementById('txt-dropzone-sub-text');
            
            if (txtDropzone && txtFileInput) {
                if (hasTipoEfd) {
                    // Habilitar dropzone
                    txtDropzone.classList.remove('border-gray-300', 'bg-gray-100', 'opacity-60', 'cursor-not-allowed', 'pointer-events-none');
                    txtDropzone.classList.add('border-gray-300', 'bg-gray-50', 'hover:border-blue-400', 'hover:bg-blue-50', 'cursor-pointer');
                    txtDropzone.setAttribute('aria-disabled', 'false');
                    txtFileInput.disabled = false;
                    
                    // Atualizar ícone
                    const svg = txtDropzone.querySelector('svg');
                    if (svg) {
                        svg.classList.remove('text-gray-400');
                        svg.classList.add('text-blue-400');
                    }
                    
                    // Atualizar textos
                    if (dropzoneMainText) {
                        dropzoneMainText.textContent = 'Arraste o arquivo .txt aqui';
                        dropzoneMainText.classList.remove('text-gray-500');
                        dropzoneMainText.classList.add('text-gray-700', 'font-medium');
                    }
                    if (dropzoneSubText) {
                        dropzoneSubText.textContent = 'ou clique para selecionar';
                        dropzoneSubText.classList.remove('text-gray-400');
                        dropzoneSubText.classList.add('text-gray-500');
                    }
                } else {
                    // Desabilitar dropzone
                    txtDropzone.classList.remove('border-gray-300', 'bg-gray-50', 'hover:border-blue-400', 'hover:bg-blue-50', 'cursor-pointer');
                    txtDropzone.classList.add('border-gray-300', 'bg-gray-100', 'opacity-60', 'cursor-not-allowed', 'pointer-events-none');
                    txtDropzone.setAttribute('aria-disabled', 'true');
                    txtFileInput.disabled = true;
                    
                    // Atualizar ícone
                    const svg = txtDropzone.querySelector('svg');
                    if (svg) {
                        svg.classList.remove('text-blue-400');
                        svg.classList.add('text-gray-400');
                    }
                    
                    // Atualizar textos
                    if (dropzoneMainText) {
                        dropzoneMainText.textContent = 'Selecione o tipo de EFD primeiro';
                        dropzoneMainText.classList.remove('text-gray-700', 'font-medium');
                        dropzoneMainText.classList.add('text-gray-500');
                    }
                    if (dropzoneSubText) {
                        dropzoneSubText.textContent = 'Depois arraste o arquivo .txt aqui ou clique para selecionar';
                        dropzoneSubText.classList.remove('text-gray-500');
                        dropzoneSubText.classList.add('text-gray-400');
                    }
                }
            }
        }

        // Função para atualizar habilitação do botão
        function updateImportButtonState() {
            const hasTipoEfd = getSelectedTipoEfd() !== '';
            const hasFile = txtFileInput && txtFileInput.files && txtFileInput.files.length > 0;
            
            if (txtImportarBtn) {
                txtImportarBtn.disabled = !(hasTipoEfd && hasFile);
            }
        }

        // Função para validar arquivo
        function validarArquivoTxt(file) {
            if (!file) return false;

            // Validar extensão
            const fileName = file.name.toLowerCase();
            const isTxt = fileName.endsWith('.txt') || file.type === 'text/plain';
            if (!isTxt) {
                mostrarErroTxt('Apenas arquivos .txt são permitidos. Por favor, selecione um arquivo .txt.');
                return false;
            }

            // Validar tamanho
            if (file.size > MAX_FILE_SIZE) {
                mostrarErroTxt('O arquivo excede o limite de 10MB. Por favor, selecione um arquivo menor.');
                return false;
            }

            return true;
        }

        // Função para mostrar erro
        function mostrarErroTxt(mensagem) {
            if (txtErrorText) txtErrorText.textContent = mensagem;
            if (txtErrorMessage) txtErrorMessage.classList.remove('hidden');
        }

        // Função para ocultar erro
        function ocultarErroTxt() {
            if (txtErrorMessage) txtErrorMessage.classList.add('hidden');
        }

        // Função para atualizar UI do arquivo
        function atualizarUITxt(file) {
            if (!file) {
                if (txtFileMeta) txtFileMeta.classList.add('hidden');
                updateImportButtonState();
                return;
            }

            if (txtFileName) txtFileName.textContent = file.name;
            if (txtFileSize) {
                const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                txtFileSize.textContent = sizeMB + ' MB';
            }
            if (txtFileMeta) txtFileMeta.classList.remove('hidden');
            updateImportButtonState();
        }

        // Função para limpar arquivo
        function limparArquivoTxt() {
            if (txtFileInput) txtFileInput.value = '';
            atualizarUITxt(null);
            ocultarErroTxt();
            updateImportButtonState();
        }

        // Função para processar arquivo selecionado
        function processarArquivoTxt(file) {
            ocultarErroTxt();

            if (!validarArquivoTxt(file)) {
                limparArquivoTxt();
                return;
            }

            atualizarUITxt(file);
        }

        // Click no dropzone
        if (txtDropzone && txtFileInput) {
            txtDropzone.addEventListener('click', function() {
                if (txtFileInput.disabled) return;
                txtFileInput.click();
            });

            // Drag and drop
            txtDropzone.addEventListener('dragover', function(e) {
                if (txtFileInput.disabled) return;
                e.preventDefault();
                txtDropzone.classList.remove('border-gray-300', 'bg-gray-50', 'hover:border-blue-400', 'hover:bg-blue-50');
                txtDropzone.classList.add('border-blue-500', 'bg-blue-50');
            });

            txtDropzone.addEventListener('dragleave', function() {
                if (txtFileInput.disabled) return;
                txtDropzone.classList.remove('border-blue-500', 'bg-blue-50');
                txtDropzone.classList.add('border-gray-300', 'bg-gray-50', 'hover:border-blue-400', 'hover:bg-blue-50');
            });

            txtDropzone.addEventListener('drop', function(e) {
                if (txtFileInput.disabled) return;
                e.preventDefault();
                txtDropzone.classList.remove('border-blue-500', 'bg-blue-50');
                txtDropzone.classList.add('border-gray-300', 'bg-gray-50', 'hover:border-blue-400', 'hover:bg-blue-50');

                const file = e.dataTransfer?.files?.[0];
                if (file) {
                    processarArquivoTxt(file);
                    // Atualizar input file
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    txtFileInput.files = dt.files;
                }
            });
        }

        // Change no input file
        if (txtFileInput) {
            txtFileInput.addEventListener('change', function(e) {
                const file = e.target.files?.[0];
                if (file) {
                    processarArquivoTxt(file);
                } else {
                    limparArquivoTxt();
                }
            });
        }

        // Botão trocar arquivo
        if (txtChangeFile) {
            txtChangeFile.addEventListener('click', function(e) {
                e.stopPropagation();
                limparArquivoTxt();
                if (txtFileInput) txtFileInput.click();
            });
        }

        // Event listeners para radio buttons do tipo EFD
        if (tipoEfdRadios && tipoEfdRadios.length > 0) {
            tipoEfdRadios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    updateTipoEfdLabels();
                    updateDropzoneState();
                    updateImportButtonState();
                });
            });
        }

        // Variáveis para controle de importação
        let eventSourceTxt = null;
        let importacaoEmAndamento = false;
        let importandoComNotas = false;
        let reconnectTimer = null;
        let reconnectAttempts = 0;
        let toastConcluidoMostrado = false; // evita toast duplicado quando SSE mantido aberto
        const MAX_RECONEXOES = 3;
        const DELAY_RECONEXAO_BASE = 3000;

        // Animação suave da barra de progresso
        let currentProgress = 0;   // valor atualmente exibido na barra
        let targetProgress  = 0;   // valor alvo recebido do SSE
        let animFrameId     = null; // handle do requestAnimationFrame ativo
        let blocoAtualProgresso = null; // rastreia mudança de bloco para reset do contador

        function animarProgresso() {
            if (currentProgress < targetProgress) {
                currentProgress = Math.min(currentProgress + 0.4, targetProgress);
                const pct = Math.round(currentProgress);
                if (barraProgresso)       barraProgresso.style.width = pct + '%';
                if (progressoPorcentagem) progressoPorcentagem.textContent = pct + '%';
                animFrameId = requestAnimationFrame(animarProgresso);
            } else if (currentProgress > targetProgress) {
                // Snap imediato para baixo (não animar regressão)
                currentProgress = targetProgress;
                const pct = Math.round(currentProgress);
                if (barraProgresso)       barraProgresso.style.width = pct + '%';
                if (progressoPorcentagem) progressoPorcentagem.textContent = pct + '%';
                animFrameId = null;
            } else {
                animFrameId = null;
            }
        }

        // Elementos de progresso (nova UI minimalista)
        const progressoContainer = document.getElementById('importacao-progresso');
        const progressoCard = document.getElementById('progresso-card');
        const barraProgresso = document.getElementById('barra-progresso');
        const progressoPorcentagem = document.getElementById('progresso-porcentagem');
        const progressoMensagem = document.getElementById('progresso-mensagem');
        const progressoEmpresa = document.getElementById('progresso-empresa');
        const progressoDocumento = document.getElementById('progresso-documento');
        const progressoIcon = document.getElementById('progresso-icon');

        // Elementos de erro
        const progressoErro = document.getElementById('progresso-erro');
        const progressoErroMsg = document.getElementById('progresso-erro-msg');

        // Função para atualizar ícone de status
        function atualizarIconeStatus(status, errorMessage) {
            if (!progressoIcon || !progressoCard) return;

            // Reset classes do card
            progressoCard.className = 'bg-white border rounded-lg p-4 shadow-sm';

            switch (status) {
                case 'concluido':
                    progressoIcon.className = 'w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0';
                    progressoIcon.innerHTML = '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                    progressoCard.classList.add('border-green-200');
                    if (barraProgresso) barraProgresso.className = 'bg-green-600 h-full rounded-full transition-all duration-500 ease-out';
                    // Ocultar seção de erro, manter stats
                    if (progressoErro) progressoErro.classList.add('hidden');
                    break;
                case 'erro':
                case 'timeout':
                    progressoIcon.className = 'w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0';
                    progressoIcon.innerHTML = '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                    progressoCard.classList.add('border-red-200');
                    if (barraProgresso) barraProgresso.className = 'bg-red-600 h-full rounded-full transition-all duration-500 ease-out';
                    // Mostrar seção de erro
                    if (progressoErro) {
                        progressoErro.classList.remove('hidden');
                        // Atualizar mensagem de erro se fornecida
                        if (progressoErroMsg && errorMessage) {
                            progressoErroMsg.textContent = errorMessage;
                        } else if (progressoErroMsg) {
                            progressoErroMsg.textContent = status === 'timeout'
                                ? 'O processamento demorou mais do que o esperado.'
                                : 'Ocorreu um erro interno durante o processamento.';
                        }
                    }
                    break;
                default:
                    progressoIcon.className = 'w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0';
                    progressoIcon.innerHTML = '<svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>';
                    progressoCard.classList.add('border-gray-200');
                    if (barraProgresso) barraProgresso.className = 'bg-blue-600 h-full rounded-full transition-all duration-500 ease-out';
                    // Ocultar seção de erro
                    if (progressoErro) progressoErro.classList.add('hidden');
            }
        }

        // Função para atualizar UI de progresso
        function atualizarProgresso(payload) {
            const dados = payload.dados || {};
            const progresso = parseInt(payload.progresso) || 0;
            const status = payload.status || 'processando';
            const mensagem = payload.mensagem || 'Processando...';
            const errorMessage = payload.error_message || payload.mensagem || null;

            // Reset do contador ao mudar de bloco
            const blocoPayload = payload.bloco || null;
            if (blocoPayload !== blocoAtualProgresso) {
                blocoAtualProgresso = blocoPayload;
                // Reset imediato (sem animação de retrocesso)
                if (animFrameId !== null) { cancelAnimationFrame(animFrameId); animFrameId = null; }
                currentProgress = 0;
                if (barraProgresso)       barraProgresso.style.width = '0%';
                if (progressoPorcentagem) progressoPorcentagem.textContent = '0%';
            }

            // Barra de progresso (animação suave)
            targetProgress = progresso;
            if (animFrameId === null) {
                animFrameId = requestAnimationFrame(animarProgresso);
            }
            if (progressoMensagem) progressoMensagem.textContent = mensagem;

            // Empresa
            if (progressoEmpresa && dados.nome_empresa) {
                progressoEmpresa.textContent = dados.nome_empresa;
            }

            // Documento (tipo e período)
            if (progressoDocumento) {
                const tipo = dados.tipo_documento || '';
                const periodo = dados.data_inicial_do_documento && dados.data_final_do_documento
                    ? dados.data_inicial_do_documento + ' - ' + dados.data_final_do_documento
                    : '';
                const docText = [tipo, periodo].filter(Boolean).join(' • ');
                if (docText) {
                    progressoDocumento.textContent = docText;
                    progressoDocumento.classList.remove('hidden');
                }
            }

            // Status visual (passa mensagem de erro se for erro/timeout)
            const isError = status === 'erro' || status === 'timeout';
            atualizarIconeStatus(status, isError ? errorMessage : null);

            // Etapas de notas
            atualizarEtapasNotas(payload);
        }

        function atualizarEtapasNotas(payload) {
            const card = document.getElementById('etapas-notas-card');
            if (!card) return;

            card.classList.remove('hidden'); // sempre visível durante importação

            const blocos = Object.assign({}, payload.notas_blocos || {});

            // Inferência de skip: bloco posterior iniciou mas anterior não tem dados
            if ((blocos.C || blocos.D) && !blocos.A) blocos.A = { status: 'skip' };
            if (blocos.D && !blocos.C)               blocos.C = { status: 'skip' };

            // Participantes (bloco 0): usar status real se presente, senão inferir
            const isFinalConcluido = payload.status === 'concluido';
            const bloco0 = blocos['0'];
            if (bloco0) {
                const p0Status = (bloco0.status === 'concluido' || bloco0.progresso === 100 || isFinalConcluido)
                    ? 'concluido' : bloco0.status;
                renderEtapa('participantes', p0Status, null);
            } else {
                // Retrocompatibilidade: se não há bloco 0 mas outros blocos existem, participantes já terminou
                const temOutroBloco = Object.keys(blocos).some(function(k) { return k !== '0'; });
                renderEtapa('participantes', temOutroBloco ? 'concluido' : 'processando', null);
            }

            const ordemBlocos = ['A', 'C', 'D'];
            ordemBlocos.forEach(function(b) {
                if (blocos[b]) {
                    const statusEfetivo = (blocos[b].status !== 'skip' && (isFinalConcluido || blocos[b].status === 'concluido' || blocos[b].progresso === 100))
                        ? 'concluido'
                        : blocos[b].status;
                    renderEtapa(b, statusEfetivo, null);
                } else {
                    renderEtapa(b, 'pendente', null);
                }
            });
        }

        function renderEtapa(etapa, status, mensagem) {
            const item = document.querySelector('.etapa-item[data-etapa="' + etapa + '"]');
            if (!item) return;

            const iconEl = item.querySelector('.etapa-icon');

            const svgSpinner = '<svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>';
            const svgCheck   = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
            const svgDash    = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>';

            const estados = {
                pendente:    { pill: 'bg-gray-100 text-gray-400',   icon: svgDash },
                processando: { pill: 'bg-blue-100 text-blue-600',   icon: svgSpinner },
                inicio:      { pill: 'bg-blue-100 text-blue-600',   icon: svgSpinner },
                concluido:   { pill: 'bg-green-100 text-green-700', icon: svgCheck },
                skip:        { pill: 'bg-gray-100 text-gray-400',   icon: svgDash },
            };

            const estado = estados[status] || estados.pendente;
            item.className = 'etapa-item inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium ' + estado.pill;
            iconEl.innerHTML = estado.icon;
        }

        // Função para mostrar UI de progresso
        function mostrarProgresso() {
            if (progressoContainer) progressoContainer.classList.remove('hidden');
            // Ocultar cards de upload
            const uploadSection = document.querySelector('.grid.grid-cols-1.lg\\:grid-cols-2.gap-6');
            if (uploadSection) uploadSection.classList.add('hidden');
            document.getElementById('historico-importacoes')?.classList.add('hidden');
        }

        // Função para ocultar UI de progresso
        function ocultarProgresso() {
            if (progressoContainer) progressoContainer.classList.add('hidden');
            // Mostrar cards de upload
            const uploadSection = document.querySelector('.grid.grid-cols-1.lg\\:grid-cols-2.gap-6');
            if (uploadSection) uploadSection.classList.remove('hidden');
            document.getElementById('historico-importacoes')?.classList.remove('hidden');
        }

        // Função para resetar UI de progresso
        function resetarProgresso() {
            // Cancelar animação em andamento e resetar estado
            if (animFrameId !== null) { cancelAnimationFrame(animFrameId); animFrameId = null; }
            currentProgress = 0;
            targetProgress  = 0;
            // Resetar barra de progresso
            if (barraProgresso) {
                barraProgresso.style.width = '0%';
                barraProgresso.className = 'bg-blue-600 h-full rounded-full transition-all duration-500 ease-out';
            }
            if (progressoPorcentagem) progressoPorcentagem.textContent = '0%';
            if (progressoMensagem) progressoMensagem.textContent = 'Iniciando...';

            // Resetar header
            if (progressoEmpresa) progressoEmpresa.textContent = 'Aguardando dados...';
            if (progressoDocumento) {
                progressoDocumento.textContent = '';
                progressoDocumento.classList.add('hidden');
            }

            // Resetar ícone e card para estado inicial (processando)
            atualizarIconeStatus('processando');

            // Ocultar seção de erro
            if (progressoErro) progressoErro.classList.add('hidden');

            // Ocultar seção de resultados
            const resultadoImportacao = document.getElementById('resultado-importacao');
            if (resultadoImportacao) resultadoImportacao.classList.add('hidden');

            // Resetar card de etapas de notas
            const etapasCard = document.getElementById('etapas-notas-card');
            if (etapasCard) etapasCard.classList.add('hidden');
            document.querySelectorAll('.etapa-item').forEach(function(item) {
                item.className = 'etapa-item inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-400';
                const iconEl = item.querySelector('.etapa-icon');
                if (iconEl) iconEl.innerHTML = '';
            });
            importandoComNotas = false;
            blocoAtualProgresso = null;
        }

        // Elementos da seção de resultados
        const resultadoContainer = document.getElementById('resultado-importacao');
        const resultadoEmpresa = document.getElementById('resultado-empresa');
        const resultadoTotalParticipantes = document.getElementById('resultado-total-participantes');
        const resultadoNovos = document.getElementById('resultado-novos');
        const resultadoDuplicados = document.getElementById('resultado-duplicados');
        const btnNovaImportacao = document.getElementById('btn-nova-importacao');
        const btnCarregarParticipantes = document.getElementById('btn-carregar-participantes');
        const linkFiltrarImportacao = document.getElementById('link-filtrar-importacao');
        const listaParticipantesContainer = document.getElementById('lista-participantes-container');
        const listaParticipantesLoading = document.getElementById('lista-participantes-loading');
        const listaParticipantesTabela = document.getElementById('lista-participantes-tabela');
        const participantesTbody = document.getElementById('participantes-tbody-resultado');

        // Variável para guardar o ID da importação atual e IDs dos participantes
        let importacaoAtualId = null;
        let participanteIdsFromSSE = null; // Array de IDs recebidos do n8n via SSE
        let novosIdsFromSSE = null; // Array de IDs dos participantes NOVOS
        let duplicadosIdsFromSSE = null; // Array de IDs dos participantes DUPLICADOS/ATUALIZADOS
        let participantesPage = 1;
        let participantesTotal = 0;
        // Mapa participante_id → dados de resumo (nota_ids, total_notas, entradas, saidas, bi)
        let participantesResumoMap = {};
        // Cache de notas carregadas por participante (participante_id → array de notas)
        let notasCache = {};

        // Função para mostrar seção de resultados após importação concluída
        function mostrarResultadoImportacao(dados) {
            console.log('[Monitoramento EFD] mostrarResultadoImportacao - dados recebidos:', dados);
            const resultadoEl = resultadoContainer || document.getElementById('resultado-importacao');
            console.log('[Monitoramento EFD] resultadoContainer existe?', !!resultadoEl);

            if (!resultadoEl) {
                console.error('[Monitoramento EFD] resultadoContainer NAO ENCONTRADO no DOM!');
                return;
            }

            // Preencher dados
            console.log('[Monitoramento EFD] Preenchendo cards...');
            console.log('[Monitoramento EFD] total_participantes:', dados.total_participantes);
            console.log('[Monitoramento EFD] duplicados_identificados:', dados.duplicados_identificados);
            console.log('[Monitoramento EFD] participante_ids:', dados.participante_ids);

            if (resultadoEmpresa) {
                resultadoEmpresa.textContent = dados.cliente_nome || 'Importação concluída';
            }
            if (resultadoTotalParticipantes) {
                const valor = dados.total_participantes || dados.total_processados || 0;
                console.log('[Monitoramento EFD] Setando Total Participantes para:', valor);
                resultadoTotalParticipantes.textContent = valor;
            }
            if (resultadoNovos) {
                const valor = dados.novos_salvos || dados.novos || 0;
                console.log('[Monitoramento EFD] Setando Novos para:', valor);
                resultadoNovos.textContent = valor;
            }
            if (resultadoDuplicados) {
                const valor = dados.duplicados_identificados || 0;
                console.log('[Monitoramento EFD] Setando Duplicados para:', valor);
                resultadoDuplicados.textContent = valor;
            }

            // Exibir notas fiscais extraídas (se houver)
            const resultadoNotas = document.getElementById('resultado-notas');
            const notasExtraidasCount = document.getElementById('notas-extraidas-count');
            const totalNotas = dados.notas_extraidas || dados.total_notas || 0;

            if (totalNotas > 0 && resultadoNotas && notasExtraidasCount) {
                notasExtraidasCount.textContent = totalNotas;
                resultadoNotas.classList.remove('hidden');
                console.log('[Monitoramento EFD] Notas extraídas:', totalNotas);
            } else if (resultadoNotas) {
                resultadoNotas.classList.add('hidden');
            }

            // Guardar ID da importação se disponível nos dados do SSE
            if (dados.importacao_id) {
                importacaoAtualId = dados.importacao_id;
                console.log('[Monitoramento EFD] importacaoAtualId setado para:', importacaoAtualId);
            }

            // Guardar IDs dos participantes se disponível (enviados pelo n8n)
            // Aceita participante_lita_geral_ids (novo) ou participante_ids (legado)
            // Aceita tanto array quanto string separada por vírgulas
            const idsGeral = dados.participante_lita_geral_ids || dados.participante_ids;
            if (idsGeral) {
                if (Array.isArray(idsGeral)) {
                    participanteIdsFromSSE = idsGeral;
                } else if (typeof idsGeral === 'string') {
                    participanteIdsFromSSE = idsGeral.split(',').map(id => parseInt(id.trim(), 10)).filter(id => !isNaN(id));
                }
                if (participanteIdsFromSSE && participanteIdsFromSSE.length > 0) {
                    console.log('[Monitoramento EFD] participanteIdsFromSSE setado, total:', participanteIdsFromSSE.length);
                }
            }

            // Guardar IDs dos participantes NOVOS (pode ser null)
            if (dados.participante_novos_ids) {
                if (Array.isArray(dados.participante_novos_ids)) {
                    novosIdsFromSSE = dados.participante_novos_ids;
                } else if (typeof dados.participante_novos_ids === 'string') {
                    novosIdsFromSSE = dados.participante_novos_ids.split(',').map(id => parseInt(id.trim(), 10)).filter(id => !isNaN(id));
                }
                if (novosIdsFromSSE && novosIdsFromSSE.length > 0) {
                    console.log('[Monitoramento EFD] novosIdsFromSSE setado, total:', novosIdsFromSSE.length);
                }
            }

            // Guardar IDs dos participantes DUPLICADOS/ATUALIZADOS
            if (dados.participante_repetido_ids) {
                if (Array.isArray(dados.participante_repetido_ids)) {
                    duplicadosIdsFromSSE = dados.participante_repetido_ids;
                } else if (typeof dados.participante_repetido_ids === 'string') {
                    duplicadosIdsFromSSE = dados.participante_repetido_ids.split(',').map(id => parseInt(id.trim(), 10)).filter(id => !isNaN(id));
                }
                if (duplicadosIdsFromSSE && duplicadosIdsFromSSE.length > 0) {
                    console.log('[Monitoramento EFD] duplicadosIdsFromSSE setado, total:', duplicadosIdsFromSSE.length);
                }
            }

            // Cliente Associado
            const resultadoCliente         = document.getElementById('resultado-cliente');
            const resultadoClienteNome     = document.getElementById('resultado-cliente-nome');
            const resultadoClienteDocLabel = document.getElementById('resultado-cliente-doc-label');
            const resultadoClienteDoc      = document.getElementById('resultado-cliente-doc');
            const resultadoClienteLink     = document.getElementById('resultado-cliente-link');

            if (dados.cliente_id && resultadoCliente) {
                if (resultadoClienteNome)     resultadoClienteNome.textContent     = dados.cliente_nome || '—';
                if (resultadoClienteDocLabel) resultadoClienteDocLabel.textContent = dados.cliente_tipo_pessoa === 'PJ' ? 'CNPJ' : 'CPF';
                if (resultadoClienteDoc)      resultadoClienteDoc.textContent      = dados.cliente_documento || '—';

                // Link direto ao perfil do cliente
                const clientePerfilUrl = '/app/cliente/' + dados.cliente_id;
                const resultadoClienteNomeLink = document.getElementById('resultado-cliente-nome-link');
                if (resultadoClienteNomeLink) {
                    resultadoClienteNomeLink.href = clientePerfilUrl;
                }
                if (resultadoClienteLink) {
                    resultadoClienteLink.href = clientePerfilUrl;
                }
                resultadoCliente.classList.remove('hidden');
            } else if (resultadoCliente) {
                resultadoCliente.classList.add('hidden');
            }

            // Atualizar link para detalhes da importação
            if (importacaoAtualId && linkFiltrarImportacao) {
                linkFiltrarImportacao.href = '/app/importacao/efd/' + importacaoAtualId;
            }

            // Mini-painel Resumo Final de Notas
            const resumoFinalEl = document.getElementById('resumo-final-notas');
            const resumoFinalContent = document.getElementById('resumo-final-notas-content');
            // dados pode ser o próprio resumo_final (n8n envia JSON.stringify) ou conter subchave
            const rf = dados.resumo_final || (dados.blocos ? dados : null);
            if (rf && resumoFinalEl && resumoFinalContent) {
                resumoFinalContent.innerHTML = renderResumoFinal(rf);
                resumoFinalEl.classList.remove('hidden');

                // Indexar participantes_resumo por participante_id
                if (Array.isArray(rf.participantes_resumo)) {
                    participantesResumoMap = {};
                    rf.participantes_resumo.forEach(pr => {
                        participantesResumoMap[pr.participante_id] = pr;
                    });
                }
            } else if (resumoFinalEl) {
                resumoFinalEl.classList.add('hidden');
            }

            // Resetar lista de participantes
            if (listaParticipantesContainer) listaParticipantesContainer.classList.remove('hidden');
            if (listaParticipantesLoading) listaParticipantesLoading.classList.add('hidden');
            if (listaParticipantesTabela) listaParticipantesTabela.classList.add('hidden');
            participantesPage = 1;

            // Mostrar seção de resultados
            resultadoEl.classList.remove('hidden');

            // Scroll para a seção de resultados
            resultadoEl.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Atualizar histórico de importações após conclusão
            fetch('/monitoramento/efd', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const novaSecao = doc.getElementById('historico-importacoes');
                const secaoAtual = document.getElementById('historico-importacoes');
                if (novaSecao && secaoAtual) {
                    secaoAtual.replaceWith(novaSecao);
                }
            })
            .catch(() => {});

            // Carregar participantes automaticamente (carregarParticipantes tem guard próprio)
            carregarParticipantes();
        }

        // Função para carregar lista de participantes
        async function carregarParticipantes() {
            // Verificar se temos IDs dos participantes (via SSE) ou ID da importação
            if (!participanteIdsFromSSE && !importacaoAtualId) {
                console.warn('[Monitoramento EFD] Nenhum ID disponível para carregar participantes');
                return;
            }

            // Mostrar loading
            if (listaParticipantesContainer) listaParticipantesContainer.classList.add('hidden');
            if (listaParticipantesLoading) listaParticipantesLoading.classList.remove('hidden');
            if (listaParticipantesTabela) listaParticipantesTabela.classList.add('hidden');

            try {
                let response;

                // Priorizar uso de participante_ids se disponível (mais direto)
                if (participanteIdsFromSSE && participanteIdsFromSSE.length > 0) {
                    response = await fetch('/app/participantes/por-ids', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({
                            ids: participanteIdsFromSSE,
                            page: participantesPage,
                            importacao_id: importacaoAtualId,
                        }),
                    });
                } else {
                    // Fallback: buscar por ID da importação
                    response = await fetch('/app/participantes/por-importacao/' + importacaoAtualId + '?page=' + participantesPage, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                }

                if (!response.ok) {
                    throw new Error('Erro ao carregar participantes: HTTP ' + response.status);
                }

                const data = await response.json();

                // Preencher tabela
                preencherTabelaParticipantes(data.participantes || []);
                participantesTotal = data.total || 0;

                // Retry após 800ms se não encontrou nada na primeira chamada (absorver race condition)
                if (participantesTotal === 0 && participantesPage === 1 && !participanteIdsFromSSE) {
                    setTimeout(() => {
                        if (participantesTotal === 0) carregarParticipantes();
                    }, 800);
                }

                // Atualizar paginação
                atualizarPaginacao(data);

                // Mostrar tabela
                if (listaParticipantesLoading) listaParticipantesLoading.classList.add('hidden');
                if (listaParticipantesTabela) listaParticipantesTabela.classList.remove('hidden');

            } catch (err) {
                console.error('[Monitoramento EFD] Erro ao carregar participantes:', err);
                if (listaParticipantesLoading) listaParticipantesLoading.classList.add('hidden');
                if (listaParticipantesContainer) {
                    listaParticipantesContainer.classList.remove('hidden');
                    listaParticipantesContainer.innerHTML = '<div class="text-center py-8 text-red-500"><p class="text-sm">Erro ao carregar participantes. Tente novamente.</p></div>';
                }
            }
        }

        // Helper: formata valor em BRL
        function formatBRL(valor) {
            return 'R$ ' + Number(valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function formatDateBR(val) {
            if (!val) return '—';
            var p = val.split('T')[0].split('-');
            return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : val;
        }

        // Helper: renderiza o mini-painel resumo_final
        function renderResumoFinal(rf) {
            if (!rf) return '';
            const blocos = rf.blocos || {};
            const ordemBlocos = ['A', 'C', 'D'];
            const nomeBloco = { A: 'Bloco A (PIS/COFINS)', C: 'Bloco C (ICMS/IPI — NF-e)', D: 'Bloco D (CT-e)' };

            let html = '<div class="space-y-1">';

            // Participantes — normaliza tanto rf.participantes (spec) quanto rf.estatisticas (n8n atual)
            const partRaw = rf.participantes || rf.estatisticas || {};
            const part = {
                total:      partRaw.total      ?? (partRaw.total_participantes_processados ?? 0),
                novos:      partRaw.novos      ?? (partRaw.participantes_novos      ?? 0),
                duplicados: partRaw.duplicados ?? (partRaw.participantes_repetidos  ?? 0),
            };
            html += `<div class="flex items-center gap-2 py-1">
                <span class="text-green-600 font-bold w-4">✓</span>
                <span class="w-44 whitespace-nowrap text-gray-700">Participantes</span>
                <span class="text-gray-900 font-medium">${part.total || 0} registros</span>
                <span class="text-gray-400 text-xs ml-2">${part.novos || 0} novos · ${part.duplicados || 0} já existentes</span>
            </div>`;

            // Blocos
            ordemBlocos.forEach(b => {
                const bd = blocos[b];
                if (!bd) return;
                const isSkip = bd.total_notas === 0 && bd.valor_total === 0;
                const icon = isSkip ? '<span class="text-gray-400 w-4">—</span>' : '<span class="text-green-600 font-bold w-4">✓</span>';
                const valor = isSkip ? '<span class="text-gray-400 text-xs">Vazio</span>' : `<span class="text-gray-900 font-medium">${(bd.total_notas || 0)} notas</span><span class="text-gray-500 text-xs ml-2">${formatBRL(bd.valor_total)}</span>`;
                html += `<div class="flex items-center gap-2 py-1">
                    ${icon}
                    <span class="w-44 whitespace-nowrap text-gray-700">${nomeBloco[b] || 'Bloco ' + b}</span>
                    ${valor}
                </div>`;
            });

            // Separador + Totais
            const tot = rf.totais || {};
            html += `<div class="border-t border-gray-300 pt-1 mt-1 flex items-center gap-2 py-1">
                <span class="w-4"></span>
                <span class="w-44 whitespace-nowrap text-gray-700 font-semibold">Total</span>
                <span class="text-gray-900 font-bold">${(tot.notas || 0)} notas</span>
                <span class="text-gray-500 text-xs ml-2">${formatBRL(tot.valor)}</span>
            </div>`;

            html += '</div>';
            return html;
        }

        // Função para preencher tabela de participantes
        function preencherTabelaParticipantes(participantes) {
            if (!participantesTbody) return;

            participantesTbody.innerHTML = '';

            if (participantes.length === 0) {
                participantesTbody.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500 text-sm">Nenhum participante encontrado.</td></tr>';
                return;
            }

            // Helper para badge de status da importação (Novo/Atualizado)
            function getStatusImportacaoBadge(participanteId) {
                if (novosIdsFromSSE && novosIdsFromSSE.includes(participanteId)) {
                    return '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700 ml-2">Novo</span>';
                }
                if (duplicadosIdsFromSSE && duplicadosIdsFromSSE.includes(participanteId)) {
                    return '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700 ml-2">Já Registrado</span>';
                }
                return '';
            }

            // Ordenar por quantidade de notas (desc) usando dados do resumo SSE
            participantes.sort((a, b) => {
                const aNotas = (participantesResumoMap[a.id] || {}).total_notas || 0;
                const bNotas = (participantesResumoMap[b.id] || {}).total_notas || 0;
                return bNotas - aNotas;
            });

            participantes.forEach(p => {
                const cnpjFormatado = p.cnpj ? p.cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5') : '-';
                const resumo = participantesResumoMap[p.id] || null;
                const temNotas = resumo && resumo.nota_ids && resumo.nota_ids.length > 0;

                const totalNotas = resumo ? (resumo.total_notas || 0) : null;
                const entradas = resumo ? (resumo.entradas || {}) : null;
                const saidas = resumo ? (resumo.saidas || {}) : null;

                const tdNotas    = totalNotas !== null ? `<span class="font-medium text-gray-900">${totalNotas}</span>` : '<span class="text-gray-400">—</span>';
                const tdEntradas = entradas   !== null ? `<span class="text-green-700">${entradas.count || 0}</span><span class="text-xs text-gray-400 ml-1">${formatBRL(entradas.valor)}</span>` : '<span class="text-gray-400">—</span>';
                const tdSaidas   = saidas     !== null ? `<span class="text-amber-700">${saidas.count || 0}</span><span class="text-xs text-gray-400 ml-1">${formatBRL(saidas.valor)}</span>` : '<span class="text-gray-400">—</span>';

                const btnExpand = temNotas
                    ? `<button type="button" class="btn-expand-notas text-blue-600 hover:text-blue-800 text-xs font-medium px-1.5 py-0.5 rounded border border-blue-200 hover:bg-blue-50 transition" data-participante-id="${p.id}" data-expanded="0" title="Ver notas">▶</button>`
                    : '';

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';
                tr.dataset.participanteId = p.id;
                tr.innerHTML = `
                    <td class="px-3 py-2 text-xs font-mono text-gray-900 whitespace-nowrap">${cnpjFormatado}${getStatusImportacaoBadge(p.id)}</td>
                    <td class="px-3 py-2 text-sm text-gray-900 max-w-[200px] truncate" title="${p.razao_social || ''}">${p.razao_social || '-'}</td>
                    <td class="px-2 py-2 text-center text-xs text-gray-600 w-12">${p.uf || '-'}</td>
                    <td class="px-2 py-2 text-right text-xs">${tdNotas}</td>
                    <td class="px-2 py-2 text-right text-xs">${tdEntradas}</td>
                    <td class="px-2 py-2 text-right text-xs">${tdSaidas}</td>
                    <td class="px-2 py-2 text-right">
                        <div class="flex items-center justify-end gap-2">
                            ${btnExpand}
                            <button
                                type="button"
                                class="btn-monitorar-participante inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors"
                                data-participante-id="${p.id}"
                                data-participante-cnpj="${p.cnpj || ''}"
                                data-tem-plano="${p.monitoramento_ativo ? '1' : '0'}"
                                title="${p.monitoramento_ativo ? 'Consultar agora' : 'Configurar monitoramento'}"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                                ${p.monitoramento_ativo ? 'Consultar' : 'Monitorar'}
                            </button>
                            <a href="/app/participante/${p.id}" class="text-xs font-medium hover:underline" style="color: #2563eb;" data-link>Ver</a>
                        </div>
                    </td>
                `;
                participantesTbody.appendChild(tr);
            });

            // Handler de expansão inline
            participantesTbody.querySelectorAll('.btn-expand-notas').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const pid = parseInt(this.dataset.participanteId);
                    const expanded = this.dataset.expanded === '1';
                    const parentTr = this.closest('tr');

                    // Fechar se já aberto
                    const existingExpandRow = parentTr.nextElementSibling;
                    if (existingExpandRow && existingExpandRow.classList.contains('expand-notas-row')) {
                        existingExpandRow.remove();
                        this.textContent = '▶';
                        this.dataset.expanded = '0';
                        return;
                    }

                    this.textContent = '▼';
                    this.dataset.expanded = '1';

                    const resumo = participantesResumoMap[pid];
                    if (!resumo) return;

                    const expandTr = document.createElement('tr');
                    expandTr.className = 'expand-notas-row bg-blue-50';
                    expandTr.innerHTML = `<td colspan="7" class="px-4 py-3">
                        <div class="expand-notas-content text-sm">
                            <div class="text-gray-500 text-xs">Carregando notas...</div>
                        </div>
                    </td>`;
                    parentTr.after(expandTr);

                    const contentDiv = expandTr.querySelector('.expand-notas-content');

                    // Dados BI
                    let biHtml = '';
                    if (resumo.bi && Object.keys(resumo.bi).length > 0) {
                        biHtml = '<div class="flex flex-wrap gap-4 mb-2">' +
                            Object.entries(resumo.bi).map(([k, v]) =>
                                `<span class="text-xs text-gray-600"><span class="font-medium text-gray-700">${k.replace(/_/g,' ')}:</span> ${v}</span>`
                            ).join('') + '</div>';
                    }

                    // Carregar notas (com cache)
                    if (!notasCache[pid] && resumo.nota_ids && resumo.nota_ids.length > 0) {
                        try {
                            const params = resumo.nota_ids.map(id => 'ids[]=' + id).join('&');
                            const resp = await fetch('/app/importacao/efd/notas?' + params, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            notasCache[pid] = resp.ok ? await resp.json() : [];
                        } catch (e) {
                            notasCache[pid] = [];
                        }
                    }

                    const notas = notasCache[pid] || [];
                    let notasHtml = '';
                    if (notas.length > 0) {
                        notasHtml = `<div class="overflow-x-auto mt-2">
                            <table class="w-full text-xs border border-gray-200 rounded">
                                <thead class="bg-gray-100"><tr>
                                    <th class="px-2 py-1 text-left text-gray-500">Nº Doc</th>
                                    <th class="px-2 py-1 text-left text-gray-500">Série</th>
                                    <th class="px-2 py-1 text-left text-gray-500">Modelo</th>
                                    <th class="px-2 py-1 text-left text-gray-500">Emissão</th>
                                    <th class="px-2 py-1 text-center text-gray-500">Tipo</th>
                                    <th class="px-2 py-1 text-right text-gray-500">Valor</th>
                                </tr></thead>
                                <tbody class="divide-y divide-gray-200">` +
                                notas.slice(0, 50).map(n => `<tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='/app/notas-fiscais/efd/${n.id}'">
                                    <td class="px-2 py-1 font-mono">${n.numero || '—'}</td>
                                    <td class="px-2 py-1">${n.serie || '—'}</td>
                                    <td class="px-2 py-1">${n.modelo || '—'}</td>
                                    <td class="px-2 py-1">${formatDateBR(n.data_emissao)}</td>
                                    <td class="px-2 py-1 text-center">${n.tipo_operacao === '1' ? '<span class="text-green-700">E</span>' : '<span class="text-amber-700">S</span>'}</td>
                                    <td class="px-2 py-1 text-right">${formatBRL(n.valor_total)}</td>
                                </tr>`).join('') +
                                `</tbody></table>` +
                                (notas.length > 50 ? `<p class="text-xs text-gray-400 mt-1">Mostrando 50 de ${notas.length} notas.</p>` : '') +
                            `</div>`;
                    } else {
                        notasHtml = '<p class="text-xs text-gray-400 mt-2">Nenhuma nota disponível.</p>';
                    }

                    contentDiv.innerHTML = biHtml + notasHtml;
                });
            });
        }

        // Função para atualizar paginação
        function atualizarPaginacao(data) {
            const infoEl = document.getElementById('participantes-info');
            const btnPrev = document.getElementById('btn-prev-page');
            const btnNext = document.getElementById('btn-next-page');

            if (infoEl) {
                const start = ((data.current_page || 1) - 1) * (data.per_page || 10) + 1;
                const end = Math.min(start + (data.participantes?.length || 0) - 1, data.total || 0);
                infoEl.textContent = 'Mostrando ' + start + '-' + end + ' de ' + (data.total || 0);
            }

            if (btnPrev) {
                btnPrev.disabled = !data.prev_page_url;
                btnPrev.onclick = function() {
                    if (data.prev_page_url) {
                        participantesPage--;
                        carregarParticipantes();
                    }
                };
            }

            if (btnNext) {
                btnNext.disabled = !data.next_page_url;
                btnNext.onclick = function() {
                    if (data.next_page_url) {
                        participantesPage++;
                        carregarParticipantes();
                    }
                };
            }
        }

        // Event listeners para seção de resultados
        if (btnNovaImportacao) {
            btnNovaImportacao.addEventListener('click', function() {
                // Resetar flag de importação em andamento (CRÍTICO)
                importacaoEmAndamento = false;
                // Fechar SSE se ainda estiver aberto
                if (eventSourceTxt) {
                    eventSourceTxt.close();
                    eventSourceTxt = null;
                }
                // Ocultar seção de resultados
                if (resultadoContainer) resultadoContainer.classList.add('hidden');
                // Ocultar seção de progresso
                ocultarProgresso();
                // Resetar formulário
                resetarProgresso();
                // Limpar IDs armazenados
                importacaoAtualId = null;
                participanteIdsFromSSE = null;
                novosIdsFromSSE = null;
                duplicadosIdsFromSSE = null;
                // Limpar arquivo selecionado
                if (txtFileInput) txtFileInput.value = '';
                const txtFileMeta = document.getElementById('txt-file-meta');
                if (txtFileMeta) txtFileMeta.classList.add('hidden');
                const txtDropzone = document.getElementById('txt-dropzone');
                if (txtDropzone) txtDropzone.classList.remove('hidden');
                // Habilitar botão importar
                if (txtImportarBtn) {
                    txtImportarBtn.disabled = true;
                    txtImportarBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> Importar';
                }
            });
        }

        if (btnCarregarParticipantes) {
            btnCarregarParticipantes.addEventListener('click', carregarParticipantes);
        }

        // Função para conectar ao SSE (novo formato com tab_id)
        function conectarSSE() {
            if (eventSourceTxt) {
                eventSourceTxt.close();
            }

            const sseUrl = '/app/importacao/efd/progresso/stream?tab_id=' + encodeURIComponent(tabId);
            console.log('[Monitoramento EFD] Conectando ao SSE:', sseUrl);
            eventSourceTxt = new EventSource(sseUrl);

            eventSourceTxt.onopen = function() {
                reconnectAttempts = 0;
                console.log('[Monitoramento EFD] SSE conectado');
            };

            eventSourceTxt.onmessage = function(event) {
                let statusConcluido = false;
                try {
                    const dados = JSON.parse(event.data);
                    console.log('[Monitoramento EFD] Dados SSE:', dados);
                    atualizarProgresso(dados);

                    if (dados.status === 'concluido') {
                        // Usa mensagem do n8n ou monta mensagem com dados
                        const dadosN8n = dados.dados || {};
                        // resumo_final vem no top-level do SSE (gravado por receiveNotasEfdProgress)
                        if (dados.resumo_final && !dadosN8n.resumo_final) {
                            dadosN8n.resumo_final = dados.resumo_final;
                        }

                        const temResumoFinal = !!(dados.resumo_final || dadosN8n.resumo_final);

                        // Sem resumo_final: fase 1 concluída mas notas ainda em andamento.
                        // NÃO fechar SSE, NÃO setar importacaoEmAndamento=false, NÃO mostrar resultados.
                        if (!temResumoFinal) {
                            console.log('[Monitoramento EFD] Status concluido sem resumo_final — aguardando fase de notas');
                            return;
                        }

                        // Conclusão real — temos todos os dados
                        statusConcluido = true;
                        importacaoEmAndamento = false;

                        // Popular campos flat de stats a partir de resumo_final.estatisticas
                        // quando os campos diretos estão ausentes (fase 2 é a fonte de verdade)
                        if (dadosN8n.resumo_final?.estatisticas) {
                            const s = dadosN8n.resumo_final.estatisticas;
                            if (!dadosN8n.total_participantes && !dadosN8n.total_processados) {
                                dadosN8n.total_participantes = s.total_participantes_processados || 0;
                            }
                            if (!dadosN8n.novos_salvos && !dadosN8n.novos) {
                                dadosN8n.novos_salvos = s.participantes_novos || 0;
                            }
                            if (!dadosN8n.duplicados_identificados) {
                                dadosN8n.duplicados_identificados = s.participantes_repetidos || 0;
                            }
                        }
                        // Fallback: stats via resumo_final.participantes (spec canônico)
                        if (dadosN8n.resumo_final?.participantes) {
                            const p = dadosN8n.resumo_final.participantes;
                            if (!dadosN8n.total_participantes && !dadosN8n.total_processados) {
                                dadosN8n.total_participantes = p.total || 0;
                            }
                            if (!dadosN8n.novos_salvos && !dadosN8n.novos) {
                                dadosN8n.novos_salvos = p.novos || 0;
                            }
                            if (!dadosN8n.duplicados_identificados) {
                                dadosN8n.duplicados_identificados = p.duplicados || 0;
                            }
                        }
                        // Fallback: estatisticas no nível do payload SSE (não dentro de resumo_final)
                        if (!dadosN8n.resumo_final?.estatisticas && dadosN8n.estatisticas) {
                            const s = dadosN8n.estatisticas;
                            if (!dadosN8n.total_participantes && !dadosN8n.total_processados) {
                                dadosN8n.total_participantes = s.total_participantes_processados || 0;
                            }
                            if (!dadosN8n.novos_salvos && !dadosN8n.novos) {
                                dadosN8n.novos_salvos = s.participantes_novos || 0;
                            }
                            if (!dadosN8n.duplicados_identificados) {
                                dadosN8n.duplicados_identificados = s.participantes_repetidos || 0;
                            }
                        }
                        // Fallback: notas extraídas a partir de resumo_final.totais
                        if (!dadosN8n.notas_extraidas && !dadosN8n.total_notas && dadosN8n.resumo_final?.totais?.notas) {
                            dadosN8n.notas_extraidas = dadosN8n.resumo_final.totais.notas;
                        }

                        // Fechar SSE — conclusão real com resumo_final
                        if (eventSourceTxt) {
                            eventSourceTxt.close();
                            eventSourceTxt = null;
                        }

                        console.log('[Monitoramento EFD] Status concluido - dadosN8n:', dadosN8n);

                        // Toast apenas na primeira vez (SSE pode enviar vários concluido)
                        if (!toastConcluidoMostrado) {
                            toastConcluidoMostrado = true;
                            const totalImportados = dadosN8n.novos_salvos || dadosN8n.total_a_analisar || 0;
                            const mensagemSucesso = dados.mensagem || ('Importação concluída! ' + totalImportados + ' novos participantes adicionados.');
                            if (window.showToast) {
                                window.showToast(mensagemSucesso, 'success');
                            }
                        }

                        // Mostrar seção de resultados em vez de redirecionar
                        console.log('[Monitoramento EFD] Chamando mostrarResultadoImportacao com:', dadosN8n);
                        mostrarResultadoImportacao(dadosN8n);
                    } else if (dados.status === 'erro' || dados.status === 'timeout') {
                        if (eventSourceTxt) {
                            eventSourceTxt.close();
                            eventSourceTxt = null;
                        }
                        importacaoEmAndamento = false;

                        // Erro/timeout é tratado pelo atualizarProgresso que mostra a seção de erro
                        // Não redireciona automaticamente - usuário decide via botão "Tentar Novamente"
                    }
                } catch (e) {
                    console.error('[Monitoramento EFD] Erro ao parsear SSE:', e);
                }
                // Safety net FORA do try/catch — garante exibição mesmo se mostrarResultadoImportacao lançar exceção
                if (statusConcluido) {
                    const safeResult = document.getElementById('resultado-importacao');
                    if (safeResult) safeResult.classList.remove('hidden');
                }
            };

            eventSourceTxt.onerror = function() {
                const tentativas = reconnectAttempts;

                eventSourceTxt.close();
                eventSourceTxt = null;

                if (!importacaoEmAndamento) return;

                if (tentativas < MAX_RECONEXOES) {
                    reconnectAttempts++;
                    const delay = DELAY_RECONEXAO_BASE * Math.pow(2, tentativas);
                    console.warn('[EFD] SSE desconectado, tentativa ' + reconnectAttempts + '/' + MAX_RECONEXOES + ' em ' + delay + 'ms');

                    reconnectTimer = setTimeout(() => {
                        reconnectTimer = null;
                        if (importacaoEmAndamento) conectarSSE();
                    }, delay);
                } else {
                    reconnectAttempts = 0;
                    importacaoEmAndamento = false;
                    atualizarProgresso({
                        status: 'erro',
                        progresso: 0,
                        mensagem: 'Erro na conexão',
                        error_message: 'Não foi possível manter conexão com o servidor após ' + MAX_RECONEXOES + ' tentativas. Verifique sua internet.'
                    });
                }
            };
        }

        // Reconectar SSE ao voltar à aba se importação ainda estiver em andamento
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && importacaoEmAndamento) {
                if (!eventSourceTxt || eventSourceTxt.readyState === EventSource.CLOSED) {
                    console.log('[Monitoramento EFD] Aba reativada — reconectando SSE');
                    reconnectAttempts = 0;
                    conectarSSE();
                }
            }
        });

        // Botão importar - funcionalidade real
        if (txtImportarBtn) {
            txtImportarBtn.addEventListener('click', async function() {
                const tipoEfd = getSelectedTipoEfd();
                if (!tipoEfd) {
                    if (window.showToast) {
                        window.showToast('Selecione o tipo de EFD antes de importar.', 'error');
                    } else {
                        alert('Selecione o tipo de EFD antes de importar.');
                    }
                    return;
                }

                if (!txtFileInput || !txtFileInput.files || txtFileInput.files.length === 0) {
                    if (window.showToast) {
                        window.showToast('Selecione um arquivo .txt para importar.', 'error');
                    } else {
                        alert('Selecione um arquivo .txt para importar.');
                    }
                    return;
                }

                if (importacaoEmAndamento) {
                    if (window.showToast) {
                        window.showToast('Aguarde a importação em andamento terminar.', 'warning');
                    }
                    return;
                }

                // Desabilitar botão e mostrar loading
                txtImportarBtn.disabled = true;
                txtImportarBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg> Enviando...';

                try {
                    const formData = new FormData();
                    formData.append('arquivo', txtFileInput.files[0]);
                    formData.append('tipo_efd', tipoEfd === 'efd-fiscal' ? 'EFD ICMS/IPI' : 'EFD PIS/COFINS');
                    formData.append('tab_id', tabId);

                    // Opção de extração de notas fiscais
                    const extrairNotasCheckbox = document.getElementById('extrair-notas');
                    importandoComNotas = extrairNotasCheckbox?.checked === true;
                    if (importandoComNotas) {
                        formData.append('extrair_notas', '1');
                    }

                    const response = await fetch('/app/importacao/efd/importar-txt', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: formData,
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.error || data.message || 'Erro ao enviar arquivo');
                    }

                    console.log('[Monitoramento EFD] Arquivo enviado com tab_id:', tabId);

                    // Guardar ID da importação retornado pelo n8n
                    if (data.importacao_id) {
                        importacaoAtualId = data.importacao_id;
                        console.log('[Monitoramento EFD] Importação ID:', importacaoAtualId);
                    }

                    // Marcar como em andamento
                    importacaoEmAndamento = true;
                    toastConcluidoMostrado = false;

                    // Mostrar UI de progresso
                    resetarProgresso();
                    mostrarProgresso();

                    // Conectar ao SSE para receber atualizações (usa tabId do escopo)
                    conectarSSE();

                } catch (err) {
                    console.error('[Monitoramento EFD] Erro ao enviar arquivo:', err);
                    if (window.showToast) {
                        window.showToast(err.message || 'Erro ao enviar arquivo.', 'error');
                    } else {
                        alert(err.message || 'Erro ao enviar arquivo.');
                    }
                } finally {
                    // Restaurar botão
                    txtImportarBtn.disabled = false;
                    txtImportarBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> Importar';
                    updateImportButtonState();
                }
            });
        }

        // Cleanup ao sair da página (para SPA)
        window._cleanupFunctions = window._cleanupFunctions || {};
        window._cleanupFunctions.initImportacaoEfd = function() {
            if (reconnectTimer !== null) {
                clearTimeout(reconnectTimer);
                reconnectTimer = null;
            }
            if (eventSourceTxt) {
                eventSourceTxt.close();
                eventSourceTxt = null;
            }
        };

        // Botão "Tentar Novamente" na seção de erro
        const btnTentarNovamente = document.getElementById('btn-tentar-novamente');
        if (btnTentarNovamente) {
            btnTentarNovamente.addEventListener('click', function() {
                // Resetar flag de importação em andamento (CRÍTICO)
                importacaoEmAndamento = false;
                // Cancelar timer de reconexão pendente
                if (reconnectTimer !== null) {
                    clearTimeout(reconnectTimer);
                    reconnectTimer = null;
                }
                reconnectAttempts = 0;
                // Fechar SSE se ainda estiver aberto
                if (eventSourceTxt) {
                    eventSourceTxt.close();
                    eventSourceTxt = null;
                }
                // CRÍTICO: Regenerar tabId para evitar receber dados de erro do cache anterior
                regenerarTabId();
                ocultarProgresso();
                limparArquivoTxt();
                resetarProgresso();
                // Limpar IDs armazenados
                importacaoAtualId = null;
                participanteIdsFromSSE = null;
                novosIdsFromSSE = null;
                duplicadosIdsFromSSE = null;
            });
        }

        // Inicializar estado inicial
        updateTipoEfdLabels();
        updateDropzoneState();
        updateImportButtonState();

        // =====================================================
        // MONITORAR PARTICIPANTE INDIVIDUAL (delegação de eventos)
        // =====================================================

        const modalMonitorarIndividualEfd = document.getElementById('modal-monitorar-individual-efd');

        // Event delegation para botões dinâmicos
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-monitorar-participante');
            if (!btn) return;

            const participanteId = btn.dataset.participanteId;
            const cnpj = btn.dataset.participanteCnpj;
            const temPlano = btn.dataset.temPlano === '1';
            const row = btn.closest('tr');
            const razaoSocial = row ? row.querySelector('td:nth-child(2)')?.textContent?.trim() : '';

            // Formatar CNPJ
            const cnpjFormatado = cnpj ? cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5') : '';

            if (temPlano) {
                // Já tem plano - executar consulta
                if (confirm('Executar consulta agora para este participante?\n\nCNPJ: ' + cnpjFormatado)) {
                    executarConsultaEfd(participanteId);
                }
            } else {
                // Não tem plano - abrir modal
                if (modalMonitorarIndividualEfd) {
                    document.getElementById('modal-monitorar-cnpj-efd').textContent = cnpjFormatado;
                    document.getElementById('modal-monitorar-razao-efd').textContent = razaoSocial || '-';
                    document.getElementById('modal-monitorar-participante-id-efd').value = participanteId;
                    document.getElementById('modal-monitorar-custo-efd').textContent = '0 créditos';
                    document.getElementById('btn-confirmar-monitorar-efd').disabled = true;

                    // Limpar seleção anterior
                    document.querySelectorAll('input[name="plano_selecionado_efd"]').forEach(r => r.checked = false);

                    modalMonitorarIndividualEfd.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            }
        });

        // Atualizar custo quando selecionar plano
        document.querySelectorAll('input[name="plano_selecionado_efd"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                document.getElementById('modal-monitorar-custo-efd').textContent = radio.dataset.creditos + ' créditos';
                document.getElementById('btn-confirmar-monitorar-efd').disabled = false;
            });
        });

        // Fechar modal
        if (modalMonitorarIndividualEfd) {
            modalMonitorarIndividualEfd.addEventListener('click', function(e) {
                if (e.target === modalMonitorarIndividualEfd || e.target.closest('.modal-close')) {
                    modalMonitorarIndividualEfd.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        }

        // Confirmar monitoramento
        const btnConfirmarMonitorarEfd = document.getElementById('btn-confirmar-monitorar-efd');
        if (btnConfirmarMonitorarEfd) {
            btnConfirmarMonitorarEfd.addEventListener('click', async function() {
                const participanteId = document.getElementById('modal-monitorar-participante-id-efd').value;
                const planoSelecionado = document.querySelector('input[name="plano_selecionado_efd"]:checked');

                if (!participanteId || !planoSelecionado) {
                    alert('Selecione um plano de monitoramento.');
                    return;
                }

                try {
                    btnConfirmarMonitorarEfd.disabled = true;
                    btnConfirmarMonitorarEfd.textContent = 'Ativando...';

                    const response = await fetch('/app/participante/' + participanteId + '/ativar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ plano: planoSelecionado.value }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        modalMonitorarIndividualEfd.classList.add('hidden');
                        document.body.style.overflow = '';
                        alert('Monitoramento ativado com sucesso!');
                        // Atualizar botão na tabela
                        const btn = document.querySelector('.btn-monitorar-participante[data-participante-id="' + participanteId + '"]');
                        if (btn) {
                            btn.dataset.temPlano = '1';
                            btn.title = 'Consultar agora';
                            btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg> Consultar';
                        }
                    } else {
                        alert(data.error || 'Erro ao ativar monitoramento.');
                    }
                } catch (error) {
                    console.error('Erro ao ativar monitoramento:', error);
                    alert('Erro ao ativar monitoramento. Tente novamente.');
                } finally {
                    btnConfirmarMonitorarEfd.disabled = false;
                    btnConfirmarMonitorarEfd.textContent = 'Ativar Monitoramento';
                }
            });
        }

        // Função para executar consulta
        async function executarConsultaEfd(participanteId) {
            try {
                const response = await fetch('/app/participante/' + participanteId + '/consultar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    alert('Consulta iniciada com sucesso! Os resultados serão atualizados em breve.');
                } else {
                    alert(data.error || 'Erro ao executar consulta.');
                }
            } catch (error) {
                console.error('Erro ao executar consulta:', error);
                alert('Erro ao executar consulta. Tente novamente.');
            }
        }

        console.log('[Monitoramento EFD] Inicializacao concluida');
    }

    // ==========================================
    // Modal Carousel de Planos (funcao separada)
    // ==========================================
    function initCarouselPlanos() {
        var totalPlanos = {{ count($planosDetalhados) }};
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
                    updateCarouselCounter(startIndex || 0);
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
                            updateCarouselCounter(this.realIndex);
                        },
                    },
                });

                updateCarouselCounter(startIndex || 0);
            }, 50);
        }

        function hidePlanosModal() {
            if (!modalPlanos) return;
            modalPlanos.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function updateCarouselCounter(index) {
            var counter = document.getElementById('carousel-counter');
            if (counter) {
                counter.textContent = (index + 1) + ' / ' + totalPlanos;
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

        console.log('[Monitoramento EFD] Carousel de planos inicializado');
    }

    // Auto-inicializar (funcoes independentes com try-catch)
    function _initAll() {
        try { initImportacaoEfd(); } catch(e) { console.error('[EFD] Erro init:', e); }
        try { initCarouselPlanos(); } catch(e) { console.error('[EFD] Erro carousel:', e); }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', _initAll, { once: true });
    } else {
        _initAll();
    }

    // Expor globalmente para SPA (chama ambas as funcoes)
    window.initImportacaoEfd = function() {
        try { initImportacaoEfd(); } catch(e) { console.error('[EFD] Erro init:', e); }
        try { initCarouselPlanos(); } catch(e) { console.error('[EFD] Erro carousel:', e); }
    };
})();
</script>
