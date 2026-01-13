{{-- RAF - Relatório de Auditoria de Fornecedores --}}
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page title (o layout já tem header global) --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Auditoria de Fornecedores</h1>
                    <p class="mt-1 text-sm text-gray-600">Analise seus fornecedores a partir do SPED e obtenha um relatório completo de regime tributário e situação fiscal.</p>
                </div>
                <a 
                    href="/app/raf/historico" 
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                    data-link
                    id="raf-historico-link"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Ver histórico</span>
                    <span id="raf-pendentes-badge" class="hidden ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-amber-500 rounded-full"></span>
                </a>
            </div>
        </div>

        {{-- Navegação por Pills --}}
        <div class="flex justify-center mb-6">
            <div class="inline-flex items-center gap-1 p-1 rounded-full bg-gray-100 shadow-sm">
                <button 
                    type="button"
                    class="raf-tab px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 bg-white text-gray-900 shadow-sm"
                    data-tab="processar"
                    aria-selected="true"
                >
                    Processar SPED
                </button>
                <button 
                    type="button"
                    class="raf-tab px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 hover:bg-gray-50"
                    data-tab="sobre"
                    aria-selected="false"
                >
                    Sobre a Solução
                </button>
            </div>
        </div>

        {{-- Aba: Processar SPED --}}
        <div id="tab-processar" class="raf-tab-content">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            {{-- Formulário de Upload --}}
            <div id="sped-upload-card" class="bg-white rounded-xl border border-gray-200 shadow-md">
                <div class="p-6 space-y-4">
                    {{-- Formulário normal (visível por padrão) --}}
                    <div id="sped-form-section">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Processar SPED</h2>
                                                   </div>
                    </div>

                    <form id="sped-form" class="space-y-5">
                        <div>
                            {{-- Campos do formulário --}}
                            <div class="space-y-4">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de SPED:</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label class="flex items-start p-3 border-2 border-blue-600 rounded-lg cursor-pointer bg-blue-50 tipo-sped-label" data-tipo="efd-contrib">
                                            <input type="radio" name="tipo-sped" value="efd-contrib" checked class="mt-1 mr-3 w-4 h-4 text-blue-600 flex-shrink-0">
                                            <div class="flex-1 min-w-0">
                                                <div class="font-semibold text-gray-800 text-sm">EFD Contribuições</div>
                                                <div class="text-xs text-gray-600">PIS/COFINS</div>
                                            </div>
                                        </label>
                                        <label class="flex items-start p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 tipo-sped-label" data-tipo="efd-fiscal">
                                            <input type="radio" name="tipo-sped" value="efd-fiscal" class="mt-1 mr-3 w-4 h-4 text-blue-600 flex-shrink-0">
                                            <div class="flex-1 min-w-0">
                                                <div class="font-semibold text-gray-800 text-sm">EFD Fiscal</div>
                                                <div class="text-xs text-gray-600">ICMS/IPI</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de consulta:</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label class="flex items-start p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 tipo-consulta-label" data-consulta="gratuito">
                                            <input type="radio" name="modalidade" value="gratuito" class="mt-1 mr-3 w-4 h-4 text-blue-600 flex-shrink-0">
                                            <div class="flex-1 min-w-0">
                                                <div class="font-semibold text-gray-800 text-sm">Gratuita</div>
                                                <div class="text-xs text-gray-600">Regime + Situação Cadastral</div>
                                            </div>
                                        </label>
                                        <label class="flex items-start p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 tipo-consulta-label" data-consulta="completa">
                                            <input type="radio" name="modalidade" value="completa" class="mt-1 mr-3 w-4 h-4 text-blue-600 flex-shrink-0">
                                            <div class="flex-1 min-w-0">
                                                <div class="font-semibold text-gray-800 text-sm">Completa</div>
                                                <div class="text-xs text-gray-600">Regime + Sit. Cadastral + CND</div>
                                            </div>
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Selecione a modalidade antes de enviar.</p>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Vincular a cliente (opcional):</label>
                                    <select id="cliente-select" name="cliente_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Selecione um cliente...</option>
                                        @forelse($clientes ?? [] as $cliente)
                                            <option value="{{ $cliente->id }}">
                                                {{ $cliente->razao_social ?? $cliente->nome }} 
                                                @if($cliente->documento)
                                                    - {{ $cliente->documento_formatado }}
                                                @endif
                                            </option>
                                        @empty
                                            <option value="" disabled>Nenhum cliente cadastrado</option>
                                        @endforelse
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <div id="sped-dropzone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors cursor-pointer bg-gray-50 hover:bg-blue-50" role="button" tabindex="0" aria-disabled="true">
                                        <div class="mb-2">
                                            <svg class="mx-auto h-10 w-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                        </div>
                                        <p class="text-xs text-gray-700 mb-1">Arraste o arquivo SPED aqui</p>
                                        <p class="text-xs text-gray-500">ou clique para selecionar</p>
                                        <p class="text-xs text-gray-500 mt-1">.txt | Máximo: 10MB</p>
                                        <input
                                            type="file"
                                            id="sped"
                                            name="sped"
                                            accept=".txt"
                                            class="hidden"
                                            disabled
                                        >
                                    </div>
                                </div>

                                <div id="sped-file-meta" class="mb-4 hidden">
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <div>
                                                <div class="text-xs font-medium text-gray-800" id="sped-file-name">arquivo.txt</div>
                                                <div class="text-xs text-gray-500" id="sped-file-size">0 MB</div>
                                            </div>
                                        </div>
                                        <button 
                                            type="button" 
                                            id="sped-change-file" 
                                            class="text-red-500 hover:text-red-700"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Coluna direita: Cards empilhados --}}
            <div class="space-y-6">
                {{-- Resultado simplificado: apenas download --}}
                <div id="csv-generated-card" class="bg-white rounded-xl border border-gray-200 shadow-md">
                    <div class="p-6 space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">CSV gerado</h3>
                                <p class="mt-1 text-sm text-gray-600">Após o processamento, o download ficará disponível.</p>
                            </div>
                            <div id="result-badge" class="hidden px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                Processado
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <div id="timer-wrap" class="hidden inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-700">
                                <svg id="timer-icon" class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span id="timer-value">00:00</span>
                            </div>

                            <div id="csv-download-wrap" class="hidden">
                                <a
                                    id="csv-download-link"
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 text-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-blue-700 transition"
                                    href="#"
                                    download="resultado.csv"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                    </svg>
                                    <span id="csv-download-label">Baixar CSV</span>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Card de Informações da Consulta --}}
                <div id="info-consulta-card" class="bg-white rounded-xl border border-gray-200 shadow-md">
                    <div class="p-6 space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Informações da Consulta</h3>
                            <p class="mt-1 text-sm text-gray-600">Detalhes do processamento da consulta.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Quantidade de participantes</p>
                                <p class="text-2xl font-bold text-gray-900" id="info-qtd-participantes">--</p>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Valor total da consulta</p>
                                <p class="text-2xl font-bold text-gray-900" id="info-valor-total">--</p>
                            </div>
                        </div>

                        {{-- Seção de Estatísticas do Relatório (inicialmente oculta) --}}
                        <div id="info-report-stats" class="hidden space-y-4 pt-4 border-t border-gray-200">
                            {{-- Dados da Empresa --}}
                            <div id="info-empresa-section">
                                <p id="info-razao-social" class="text-sm font-medium text-gray-700"></p>
                                <p id="info-cnpj" class="text-xs text-gray-500"></p>
                                <p id="info-periodo" class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <svg class="inline w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span id="info-periodo-text"></span>
                                </p>
                            </div>

                            {{-- Situação Cadastral --}}
                            <div class="text-sm text-gray-700">
                                <p class="mb-1">
                                    <span class="font-medium text-gray-500">Situação Cadastral:</span>
                                    <span id="info-situacao-nula" class="font-semibold">0</span> nulas · 
                                    <span id="info-situacao-ativa" class="font-semibold">0</span> ativas · 
                                    <span id="info-situacao-suspensa" class="font-semibold">0</span> suspensas · 
                                    <span id="info-situacao-inapta" class="font-semibold">0</span> inaptas · 
                                    <span id="info-situacao-baixada" class="font-semibold">0</span> baixadas
                                </p>
                            </div>

                            {{-- Regime Tributário --}}
                            <div class="text-sm text-gray-700">
                                <p>
                                    <span class="font-medium text-gray-500">Regimes Tributários:</span>
                                    <span id="info-regime-simples" class="font-semibold">0</span> Simples · 
                                    <span id="info-regime-presumido" class="font-semibold">0</span> Presumido · 
                                    <span id="info-regime-real" class="font-semibold">0</span> Real · 
                                    <span id="info-regime-indeterminado" class="font-semibold">0</span> indeterminados
                                </p>
                            </div>

                            {{-- CND - Versão Completa (exibida quando consultant_type = 'completa' ou 'completo') --}}
                            <div id="info-cnd-completa" class="hidden text-sm text-gray-700">
                                <p>
                                    <span class="font-medium text-gray-500">Situação Fiscal:</span>
                                    <span id="info-cnd-regular" class="font-semibold">0</span> Regular(es) · 
                                    <span id="info-cnd-pendencia" class="font-semibold">0</span> Pendente(s)
                                </p>
                            </div>

                            {{-- CND - Alerta para consulta gratuita (exibido quando consultant_type = 'gratuito') --}}
                            <div id="info-cnd-alerta" class="hidden flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm font-medium text-amber-800">Gere um relatório COMPLETO para ver informações da Situação Fiscal (CND) dos Participantes</span>
                            </div>
                        </div>

                        {{-- Botão Enviar SPED --}}
                        <div class="pt-4 border-t border-gray-200">
                            <button
                                type="button"
                                class="btn-primary-solid w-full inline-flex flex-row items-center justify-center gap-2 px-5 py-2.5 font-semibold shadow-sm disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:bg-primary-500"
                                id="sped-submit"
                                disabled
                            >
                                <svg id="sped-submit-spinner" class="hidden h-5 w-5 shrink-0 animate-spin" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                <svg id="sped-submit-icon" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                <span id="sped-submit-label" class="whitespace-nowrap">Enviar SPED</span>
                            </button>
                        </div>

                        {{-- Botão de confirmar créditos (aparece quando necessário) --}}
                        <div id="info-confirm-credits-wrap" class="hidden pt-4 border-t border-gray-200">
                            <div class="flex flex-col-reverse sm:flex-row gap-3">
                                <button
                                    type="button"
                                    id="info-credits-cancel-btn"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="button"
                                    id="info-credits-confirm-btn"
                                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg id="info-credits-confirm-spinner" class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span id="info-credits-confirm-text">Confirmar e processar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card de Alerta de status/erro (independente, abaixo de info-consulta-card) --}}
                <div id="info-alert" class="hidden bg-white rounded-xl border border-gray-200 shadow-md">
                    <div class="p-6">
                        <div class="flex gap-2">
                            <span id="info-alert-icon" class="mt-0.5 shrink-0">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                                </svg>
                            </span>
                            <p id="info-alert-text" class="min-w-0 text-sm"></p>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Fim Coluna direita: space-y-6 --}}
        </div>
        {{-- Fim Grid 2 colunas --}}

        {{-- Card de Tempo Estimado de Espera --}}
        <div class="mt-6 bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Tempo Estimado de Espera</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Modalidade Gratuita --}}
                <div class="rounded-lg border border-gray-200 p-4">
                    <h4 class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-3">Gratuita (Regime + Sit. Cadastral)</h4>
                    <div class="text-sm">
                        <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-900">25</span><span class="text-gray-500">~45s</span></div>
                        <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-900">50</span><span class="text-gray-500">~1min</span></div>
                        <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-900">100</span><span class="text-gray-500">~2min</span></div>
                        <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-900">250</span><span class="text-gray-500">~6min</span></div>
                        <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-900">500</span><span class="text-gray-500">~11min</span></div>
                        <div class="flex justify-between py-2"><span class="font-medium text-gray-900">1.000</span><span class="text-gray-500">~22min</span></div>
                    </div>
                </div>
                
                {{-- Modalidade Completa --}}
                <div class="rounded-lg border border-gray-200 p-4">
                    <h4 class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-3">Completa (Regime + Sit. Cadastral + CND)</h4>
                    <div class="text-sm">
                        <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-900">25</span><span class="text-gray-500">~3min</span></div>
                        <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-900">50</span><span class="text-gray-500">~6min</span></div>
                        <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-900">100</span><span class="text-gray-500">~12min</span></div>
                        <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-900">250</span><span class="text-gray-500">~31min</span></div>
                        <div class="flex justify-between py-2 border-b border-gray-100"><span class="font-medium text-gray-900">500</span><span class="text-gray-500">~1h</span></div>
                        <div class="flex justify-between py-2"><span class="font-medium text-gray-900">1.000</span><span class="text-gray-500">~2h</span></div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        {{-- Fim Aba: Processar SPED --}}

        {{-- Aba: Sobre a Solução --}}
        <div id="tab-sobre" class="raf-tab-content hidden">
            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="p-6 sm:p-8 space-y-8">

                        {{-- Cabeçalho --}}
                        <div class="space-y-2">
                            <h2 class="text-xl font-bold text-gray-900">Auditoria de Fornecedores</h2>
                            <p class="text-gray-600 text-sm">
                                Transformamos os dados do seu SPED em inteligência fiscal, validando o regime tributário de cada parceiro comercial.
                            </p>
                        </div>

                        <hr class="border-gray-100">

                        {{-- Como funciona --}}
                        <div class="space-y-5">
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Como funciona</h3>
                            
                            <div class="space-y-4">
                                {{-- Etapa 1 --}}
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center">
                                        <span class="text-sm font-bold text-primary-600">1</span>
                                    </div>
                                    <div class="pt-0.5">
                                        <h4 class="font-semibold text-gray-900">Mapeamento do Registro 0150</h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            O sistema varre o arquivo SPED e isola todos os participantes do Bloco 0, consolidando a lista de CNPJs dos fornecedores.
                                        </p>
                                    </div>
                                </div>

                                {{-- Etapa 2 --}}
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center">
                                        <span class="text-sm font-bold text-primary-600">2</span>
                                    </div>
                                    <div class="pt-0.5">
                                        <h4 class="font-semibold text-gray-900">Consulta Automática de Regime</h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Para cada CNPJ, consultamos em tempo real o Regime Tributário (Simples, Presumido ou Real) e a Situação Cadastral. Na modalidade completa, também consultamos a Situação Fiscal (CND - Certidão Negativa de Débitos).
                                        </p>
                                    </div>
                                </div>

                                {{-- Nota sobre algoritmo de matriz --}}
                                <div class="bg-blue-50 rounded-lg border border-blue-100 p-4 ml-12">
                                    <div class="flex gap-3">
                                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-sm text-blue-900">
                                                <strong>Algoritmo de Identificação de Matriz:</strong> O sistema utiliza um algoritmo próprio de identificação de CNPJs matriz e faz a pesquisa utilizando a matriz. Isso porque matriz e filiais são a mesma pessoa jurídica, então a maioria das consultas/certidões (como a CND) avalia a regularidade do CNPJ como um todo e aparece centralizada na matriz.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Etapa 3 --}}
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center">
                                        <span class="text-sm font-bold text-primary-600">3</span>
                                    </div>
                                    <div class="pt-0.5">
                                        <h4 class="font-semibold text-gray-900">Geração do Relatório</h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Compilamos todas as informações em um relatório consolidado com o perfil tributário e a regularidade fiscal de cada fornecedor.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        {{-- Benefícios --}}
                        <div class="space-y-4">
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Por que usar</h3>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <h4 class="font-semibold text-gray-900 text-sm">Compliance</h4>
                                    </div>
                                    <p class="text-xs text-gray-500">Cadastro alinhado com a Receita Federal.</p>
                                </div>

                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <h4 class="font-semibold text-gray-900 text-sm">Segurança</h4>
                                    </div>
                                    <p class="text-xs text-gray-500">Evite glosas por créditos indevidos.</p>
                                </div>

                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <h4 class="font-semibold text-gray-900 text-sm">Agilidade</h4>
                                    </div>
                                    <p class="text-xs text-gray-500">Automação de verificação manual.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Dica --}}
                        <div class="bg-primary-50 rounded-lg border border-primary-100 p-4">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-primary-700">
                                    <strong>Importante:</strong> Um fornecedor listado incorretamente pode gerar multas futuras. Este relatório elimina esse risco.
                                </p>
                            </div>
                        </div>

                        {{-- CTA --}}
                        <div class="pt-2 flex justify-center">
                            <button 
                                type="button" 
                                class="raf-go-to-processar inline-flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-3 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                <span>Gerar Relatório Agora</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        {{-- Fim Aba: Sobre a Solução --}}
    </div>
</div>

{{-- Modal de Confirmação de Créditos (Overlay) --}}
<div id="credits-modal-backdrop" class="hidden fixed inset-0 bg-black/50 z-[9999] flex items-center justify-center p-4">
    <div id="credits-confirmation-card" class="bg-white rounded-xl border border-gray-200 shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 space-y-5">
            {{-- Header --}}
            <div class="flex items-start gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar uso de créditos</h3>
                    <p class="mt-1 text-sm text-gray-600">Revise os detalhes da consulta antes de processar.</p>
                </div>
                <button
                    type="button"
                    id="credits-modal-close-btn"
                    class="flex-shrink-0 rounded-lg p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition focus:outline-none focus:ring-2 focus:ring-gray-300"
                    aria-label="Fechar modal"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Info Grid: CNPJs e Créditos --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-4 text-center">
                    <span class="block text-3xl font-bold text-gray-900" id="credits-cnpj-count">--</span>
                    <span class="text-sm text-gray-500">CNPJs encontrados</span>
                </div>
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-4 text-center">
                    <span class="block text-3xl font-bold text-gray-900" id="credits-total">--</span>
                    <span class="text-sm text-gray-500">Créditos necessários</span>
                </div>
            </div>

            {{-- Alerta de créditos insuficientes --}}
            <div id="credits-insufficient-alert" class="hidden rounded-xl border border-red-200 bg-red-50 p-4">
                <div class="flex gap-3">
                    <svg class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-800">Créditos insuficientes</p>
                        <p class="text-sm text-red-700 mt-1">Entre em contato pelo telefone <a href="tel:+5567999844366" class="font-semibold underline hover:no-underline">(67) 99984-4366</a> para adquirir mais créditos.</p>
                    </div>
                </div>
            </div>

            {{-- Botões de ação --}}
            <div class="flex flex-col-reverse sm:flex-row gap-3">
                <button
                    type="button"
                    id="credits-cancel-btn"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    id="credits-confirm-btn"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg id="credits-confirm-spinner" class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span id="credits-confirm-text">Confirmar e processar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    // Inicializar window.disconnectSSE como função vazia para evitar erros
    // Será sobrescrita quando initRaf for executado
    if (!window.disconnectSSE) {
        window.disconnectSSE = function() {
            // Função vazia até que initRaf seja executado
            if (window._rafDisconnectSSE && typeof window._rafDisconnectSSE === 'function') {
                window._rafDisconnectSSE();
            }
        };
    }
    
    function initRafTabs() {
        const tabButtons = document.querySelectorAll('.raf-tab');
        const tabContents = document.querySelectorAll('.raf-tab-content');
        const goToProcessarBtn = document.querySelector('.raf-go-to-processar');

        if (!tabButtons.length || !tabContents.length) return;

        // Previne inicialização dupla
        if (tabButtons[0].dataset.tabInitialized === '1') return;
        tabButtons[0].dataset.tabInitialized = '1';

        const switchTab = (targetTab) => {
            // Atualiza botões
            tabButtons.forEach(btn => {
                const isActive = btn.dataset.tab === targetTab;
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                
                if (isActive) {
                    btn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                    btn.classList.remove('text-gray-600', 'hover:text-gray-900', 'hover:bg-gray-50');
                } else {
                    btn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                    btn.classList.add('text-gray-600', 'hover:text-gray-900', 'hover:bg-gray-50');
                }
            });

            // Atualiza conteúdos
            tabContents.forEach(content => {
                const isActive = content.id === `tab-${targetTab}`;
                content.classList.toggle('hidden', !isActive);
            });
        };

        // Event listeners nos botões de aba
        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetTab = btn.dataset.tab;
                if (targetTab) switchTab(targetTab);
            });
        });

        // Botão "Ir para Processar SPED" na aba Sobre
        if (goToProcessarBtn) {
            goToProcessarBtn.addEventListener('click', () => {
                switchTab('processar');
            });
        }
    }

    function initRaf() {
    // Inicializa as abas primeiro
    initRafTabs();

    const form = document.getElementById('sped-form');
    if (!form) return;
    if (form.dataset.rafInitialized === '1') {
        return;
    }
    form.dataset.rafInitialized = '1';

    const submitBtn = document.getElementById('sped-submit');
    const resultBadge = document.getElementById('result-badge');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const tipoRadios = document.querySelectorAll('input[name="tipo-sped"]');
    const fileInput = document.getElementById('sped');
    const modalidadeRadios = document.querySelectorAll('input[name="modalidade"]');
    const submitLabel = document.getElementById('sped-submit-label');
    const submitSpinner = document.getElementById('sped-submit-spinner');
    const submitIcon = document.getElementById('sped-submit-icon');

    const dropzone = document.getElementById('sped-dropzone');
    const fileMeta = document.getElementById('sped-file-meta');
    const fileNameEl = document.getElementById('sped-file-name');
    const fileSizeEl = document.getElementById('sped-file-size');
    const changeFileBtn = document.getElementById('sped-change-file');

    // Elementos de alerta removidos do card "Processar SPED" - agora só usamos info-alert
    const alertEl = null;
    const alertTextEl = null;
    const alertIconWrap = null;

    const timerWrap = document.getElementById('timer-wrap');
    const timerValue = document.getElementById('timer-value');
    const downloadWrap = document.getElementById('csv-download-wrap');
    const downloadLink = document.getElementById('csv-download-link');
    const downloadLabel = document.getElementById('csv-download-label');
    const timerBaseClasses = 'inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold';
    const timerStateClasses = {
        default: 'border-gray-200 bg-gray-50 text-gray-700',
        success: 'border-blue-200 bg-blue-50 text-blue-600',
    };

    // Elementos do modal de confirmação de créditos
    const creditsModalBackdrop = document.getElementById('credits-modal-backdrop');
    const creditsCard = document.getElementById('credits-confirmation-card');
    const creditsModalCloseBtn = document.getElementById('credits-modal-close-btn');
    const creditsCnpjCount = document.getElementById('credits-cnpj-count');
    const creditsTotal = document.getElementById('credits-total');
    const creditsInsufficientAlert = document.getElementById('credits-insufficient-alert');
    const creditsCancelBtn = document.getElementById('credits-cancel-btn');
    const creditsConfirmBtn = document.getElementById('credits-confirm-btn');
    const creditsConfirmSpinner = document.getElementById('credits-confirm-spinner');
    const creditsConfirmText = document.getElementById('credits-confirm-text');

    // Elementos do card de informações da consulta
    const infoConsultaCard = document.getElementById('info-consulta-card');
    const infoQtdParticipantes = document.getElementById('info-qtd-participantes');
    const infoValorTotal = document.getElementById('info-valor-total');
    const infoAlertEl = document.getElementById('info-alert');
    const infoAlertTextEl = document.getElementById('info-alert-text');
    const infoAlertIconWrap = document.getElementById('info-alert-icon');
    
    // Elementos de estatísticas do relatório
    const infoReportStats = document.getElementById('info-report-stats');
    const infoEmpresaSection = document.getElementById('info-empresa-section');
    const infoRazaoSocial = document.getElementById('info-razao-social');
    const infoCnpj = document.getElementById('info-cnpj');
    const infoPeriodo = document.getElementById('info-periodo');
    const infoPeriodoText = document.getElementById('info-periodo-text');
    // Situação Cadastral
    const infoSituacaoNula = document.getElementById('info-situacao-nula');
    const infoSituacaoAtiva = document.getElementById('info-situacao-ativa');
    const infoSituacaoSuspensa = document.getElementById('info-situacao-suspensa');
    const infoSituacaoInapta = document.getElementById('info-situacao-inapta');
    const infoSituacaoBaixada = document.getElementById('info-situacao-baixada');
    // Regime Tributário
    const infoRegimeSimples = document.getElementById('info-regime-simples');
    const infoRegimePresumido = document.getElementById('info-regime-presumido');
    const infoRegimeReal = document.getElementById('info-regime-real');
    const infoRegimeIndeterminado = document.getElementById('info-regime-indeterminado');
    // CND
    const infoCndCompleta = document.getElementById('info-cnd-completa');
    const infoCndAlerta = document.getElementById('info-cnd-alerta');
    const infoCndRegular = document.getElementById('info-cnd-regular');
    const infoCndPendencia = document.getElementById('info-cnd-pendencia');
    
    // Flag para rastrear se o card já foi preenchido com dados válidos
    // Uma vez preenchido, os dados nunca devem desaparecer
    let infoCardHasValidData = false;
    const infoConfirmCreditsWrap = document.getElementById('info-confirm-credits-wrap');
    const infoCreditsCancelBtn = document.getElementById('info-credits-cancel-btn');
    const infoCreditsConfirmBtn = document.getElementById('info-credits-confirm-btn');
    const infoCreditsConfirmSpinner = document.getElementById('info-credits-confirm-spinner');
    const infoCreditsConfirmText = document.getElementById('info-credits-confirm-text');

    let isLoading = false;
    let isProcessing = false; // Estado de processamento após confirmação de créditos
    let timerInterval = null;
    let timerStart = 0;
    let currentDownloadUrl = null;

    // Dados de confirmação pendente
    let pendingConfirmation = null;
        let isConfirming = false; // Flag para evitar cliques duplos
        let currentRelatorioId = null; // ID do relatório atual sendo aguardado
        let processedResumeUrls = new Set(); // URLs já processadas para evitar reprocessamento
        let asyncProcessingStarted = false; // Flag para indicar se processamento assíncrono foi iniciado
    
    // Identificador único por aba para isolar notificações SSE
    const tabId = crypto.randomUUID ? crypto.randomUUID() : 
        (Date.now().toString(36) + Math.random().toString(36).substr(2));
    
    // Controle de conexão SSE
    let eventSource = null;
    let isConnectingSSE = false;
    
    // Variável global para armazenar a função disconnectSSE (para o SPA poder chamar)
    if (!window._rafDisconnectSSE) {
        window._rafDisconnectSSE = null;
    }

    /**
     * Obtém o valor do tipo de SPED selecionado via radio buttons.
     * @returns {string} Valor do radio selecionado ('efd-contrib' ou 'efd-fiscal') ou string vazia
     */
    const getSelectedTipoSped = () => {
        const selected = Array.from(tipoRadios).find(radio => radio.checked);
        return selected ? selected.value : '';
    };

    /**
     * Mapeia o valor do radio button para o valor esperado pelo backend.
     * @param {string} radioValue - Valor do radio ('efd-contrib' ou 'efd-fiscal')
     * @returns {string} Valor mapeado para o backend ('EFD Contribuições' ou 'EFD Fiscal')
     */
    const mapTipoSpedToBackend = (radioValue) => {
        const mapping = {
            'efd-contrib': 'EFD Contribuições',
            'efd-fiscal': 'EFD Fiscal'
        };
        return mapping[radioValue] || '';
    };

    /**
     * Atualiza o visual dos labels dos radio buttons baseado na seleção.
     */
    const updateTipoSpedLabels = () => {
        const selectedValue = getSelectedTipoSped();
        document.querySelectorAll('.tipo-sped-label').forEach(label => {
            const radio = label.querySelector('input[type="radio"]');
            if (radio && radio.value === selectedValue) {
                label.classList.remove('border-gray-300', 'hover:border-blue-400');
                label.classList.add('border-blue-600', 'bg-blue-50');
            } else {
                label.classList.remove('border-blue-600', 'bg-blue-50');
                label.classList.add('border-gray-300', 'hover:border-blue-400');
            }
        });
    };

    /**
     * Atualiza o visual dos labels dos radio buttons do tipo de consulta baseado na seleção.
     */
    const updateTipoConsultaLabels = () => {
        const selectedValue = getSelectedModalidade();
        document.querySelectorAll('.tipo-consulta-label').forEach(label => {
            const radio = label.querySelector('input[type="radio"]');
            if (radio && radio.value === selectedValue) {
                label.classList.remove('border-gray-300', 'hover:border-blue-400');
                label.classList.add('border-blue-600', 'bg-blue-50');
            } else {
                label.classList.remove('border-blue-600', 'bg-blue-50');
                label.classList.add('border-gray-300', 'hover:border-blue-400');
            }
        });
    };

    /**
     * Reseta o estado para um novo envio de documento.
     * Deve ser chamado no início de cada submit para garantir estado limpo.
     */
    const resetState = () => {
        // Limpar URLs já processadas para permitir novos modais
        processedResumeUrls.clear();

        // Limpar dados de confirmação pendente
        pendingConfirmation = null;
        isConfirming = false;

        // Limpar ID do relatório atual
        currentRelatorioId = null;

        // Resetar estado de processamento
        isProcessing = false;
        asyncProcessingStarted = false;

        // Desconectar SSE anterior para iniciar novo ciclo
        disconnectSSE();

        // Limpar estado persistido ao resetar
        clearPersistedState();
        
        // Resetar estatísticas do relatório anterior
        resetReportStats();
    };

    const formatFileSize = (bytes) => {
        if (!Number.isFinite(bytes)) return '';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${Math.round((bytes / Math.pow(k, i)) * 10) / 10} ${sizes[i]}`;
    };

    const formatCurrency = (value) => {
        if (value === null || value === undefined || isNaN(value)) {
            return '--';
        }
        const formatted = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value);
        return formatted;
    };

    const formatCredits = (value) => {
        if (value === null || value === undefined || isNaN(value)) {
            return '--';
        }
        // Arredondar para o inteiro mais próximo
        const roundedValue = Math.round(parseFloat(value));
        // Formatar com separador de milhar
        const formatted = new Intl.NumberFormat('pt-BR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(roundedValue);
        return formatted;
    };

    const updateInfoCard = (data, needsConfirmation = false) => {
        if (!infoConsultaCard) {
            return;
        }

        const qtdParticipantes = data.qtd_participantes_unicos ?? data.qnt_participantes ?? null;
        const valorTotal = data.valor_total_consulta ?? null;

        // Verificar se há dados válidos para exibir
        // Considera válido se pelo menos um dos valores não for null/undefined
        const hasValidData = (qtdParticipantes !== null && qtdParticipantes !== undefined && qtdParticipantes > 0) ||
                             (valorTotal !== null && valorTotal !== undefined && valorTotal > 0);

        // IMPORTANTE: Se está processando E já tem dados válidos, nunca limpar valores
        // Apenas atualizar se novos dados válidos forem fornecidos
        if (isProcessing && infoCardHasValidData) {
            // Se houver novos dados válidos, atualizar normalmente
            if (hasValidData) {
                // Atualizar valores apenas se forem válidos (não null/undefined)
                if (infoQtdParticipantes && qtdParticipantes !== null && qtdParticipantes !== undefined) {
                    infoQtdParticipantes.textContent = qtdParticipantes.toString();
                }

                if (infoValorTotal && valorTotal !== null && valorTotal !== undefined) {
                    const valorFormatted = formatCredits(valorTotal);
                    infoValorTotal.textContent = valorFormatted;
                }
            }
            // Se não houver dados válidos, manter valores existentes (não fazer nada)
            // Garantir que o card permaneça visível durante processamento
            infoConsultaCard.classList.remove('hidden');
            return; // Retornar cedo para não executar a lógica de limpeza abaixo
        }

        // Se houver dados válidos, marcar o flag e atualizar os valores
        if (hasValidData) {
            infoCardHasValidData = true;
            
            // Atualizar valores apenas se forem válidos (não null/undefined)
            if (infoQtdParticipantes && qtdParticipantes !== null && qtdParticipantes !== undefined) {
                infoQtdParticipantes.textContent = qtdParticipantes.toString();
            }

            if (infoValorTotal && valorTotal !== null && valorTotal !== undefined) {
                const valorFormatted = formatCredits(valorTotal);
                infoValorTotal.textContent = valorFormatted;
            }
        } else if (!infoCardHasValidData) {
            // Só atualizar para '--' se o card ainda não tiver sido preenchido com dados válidos
            // Se já tiver dados válidos, não limpar os valores
            if (infoQtdParticipantes) {
                infoQtdParticipantes.textContent = '--';
            }

            if (infoValorTotal) {
                infoValorTotal.textContent = '--';
            }
        }
        // Se infoCardHasValidData for true e não houver dados válidos agora,
        // não fazer nada - manter os valores existentes

        // IMPORTANTE: Uma vez que o card foi exibido com dados válidos, ele deve permanecer visível
        // e os dados nunca devem desaparecer (exceto se a página for recarregada)
        if (infoCardHasValidData) {
            infoConsultaCard.classList.remove('hidden');
        } else if (hasValidData) {
            // Mostrar o card quando houver dados válidos pela primeira vez
            infoConsultaCard.classList.remove('hidden');
        }
        
        // Garantir que o card permaneça visível durante processamento
        if (isProcessing && infoCardHasValidData && infoConsultaCard) {
            infoConsultaCard.classList.remove('hidden');
        }

        // Mostrar/esconder botão de confirmar créditos
        // O botão aparece quando needsConfirmation é true OU quando há dados válidos com valor total
        if (infoConfirmCreditsWrap) {
            const shouldShowButton = needsConfirmation || (hasValidData && (valorTotal !== null && valorTotal !== undefined && valorTotal > 0));
            if (shouldShowButton) {
                infoConfirmCreditsWrap.classList.remove('hidden');
            } else {
                infoConfirmCreditsWrap.classList.add('hidden');
            }
        }
        
        // Persistir estado quando houver dados válidos
        if (infoCardHasValidData) {
            persistState();
        }
    };

    /**
     * Formata um CNPJ para o padrão XX.XXX.XXX/XXXX-XX
     * @param {string} cnpj - CNPJ sem formatação
     * @returns {string} CNPJ formatado
     */
    const formatCnpj = (cnpj) => {
        if (!cnpj) return '';
        const cleaned = cnpj.replace(/\D/g, '');
        if (cleaned.length !== 14) return cnpj;
        return cleaned.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    };

    /**
     * Formata uma data de YYYY-MM-DD para DD/MM/YYYY
     * @param {string} dateStr - Data no formato YYYY-MM-DD
     * @returns {string} Data formatada DD/MM/YYYY
     */
    const formatDate = (dateStr) => {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    };

    /**
     * Atualiza as estatísticas do relatório no card de informações.
     * Exibe dados da empresa, situação cadastral, regime tributário e CND.
     * @param {object} data - Dados recebidos via SSE (csv_ready notification)
     */
    const updateReportStats = (data) => {
        if (!infoReportStats) {
            console.warn('[RAF] Elemento info-report-stats não encontrado');
            return;
        }

        // Verificar se há dados suficientes para exibir
        const hasAnyStats = data.total_participants || 
                            data.razao_social_empresa || 
                            data.cnpj_empresa_analisada ||
                            data.qnt_situacao_ativa > 0 ||
                            data.qnt_simples > 0;

        if (!hasAnyStats) {
            // Sem dados, manter oculto
            infoReportStats.classList.add('hidden');
            return;
        }

        // Mostrar a seção de estatísticas
        infoReportStats.classList.remove('hidden');

        // Dados da Empresa
        if (infoRazaoSocial) {
            infoRazaoSocial.textContent = data.razao_social_empresa || '';
            infoRazaoSocial.classList.toggle('hidden', !data.razao_social_empresa);
        }

        if (infoCnpj) {
            if (data.cnpj_empresa_analisada) {
                infoCnpj.textContent = 'CNPJ: ' + formatCnpj(data.cnpj_empresa_analisada);
                infoCnpj.classList.remove('hidden');
            } else {
                infoCnpj.classList.add('hidden');
            }
        }

        // Período
        if (infoPeriodo && infoPeriodoText) {
            if (data.data_inicial_analisada && data.data_final_analisada) {
                infoPeriodoText.textContent = `Período de Análise do SPED Informado: ${formatDate(data.data_inicial_analisada)} a ${formatDate(data.data_final_analisada)}`;
                infoPeriodo.classList.remove('hidden');
            } else if (data.data_inicial_analisada) {
                infoPeriodoText.textContent = `A partir de: ${formatDate(data.data_inicial_analisada)}`;
                infoPeriodo.classList.remove('hidden');
            } else if (data.data_final_analisada) {
                infoPeriodoText.textContent = `Até: ${formatDate(data.data_final_analisada)}`;
                infoPeriodo.classList.remove('hidden');
            } else {
                infoPeriodo.classList.add('hidden');
            }
        }

        // Esconder seção de empresa se não tiver nenhum dado
        if (infoEmpresaSection) {
            const hasEmpresaData = data.razao_social_empresa || data.cnpj_empresa_analisada || data.data_inicial_analisada || data.data_final_analisada;
            infoEmpresaSection.classList.toggle('hidden', !hasEmpresaData);
        }

        // Situação Cadastral
        if (infoSituacaoNula) infoSituacaoNula.textContent = (data.qnt_situacao_nula ?? 0).toString();
        if (infoSituacaoAtiva) infoSituacaoAtiva.textContent = (data.qnt_situacao_ativa ?? 0).toString();
        if (infoSituacaoSuspensa) infoSituacaoSuspensa.textContent = (data.qnt_situacao_suspensa ?? 0).toString();
        if (infoSituacaoInapta) infoSituacaoInapta.textContent = (data.qnt_situacao_inapta ?? 0).toString();
        if (infoSituacaoBaixada) infoSituacaoBaixada.textContent = (data.qnt_situacao_baixada ?? 0).toString();

        // Regime Tributário
        if (infoRegimeSimples) infoRegimeSimples.textContent = (data.qnt_simples ?? 0).toString();
        if (infoRegimePresumido) infoRegimePresumido.textContent = (data.qnt_presumido ?? 0).toString();
        if (infoRegimeReal) infoRegimeReal.textContent = (data.qnt_real ?? 0).toString();
        if (infoRegimeIndeterminado) infoRegimeIndeterminado.textContent = (data.qnt_regime_indeterminado ?? 0).toString();

        // CND - Verificar consultant_type (case-insensitive)
        const consultantType = (data.consultant_type || '').toLowerCase();
        const isCompleta = consultantType === 'completa' || consultantType === 'completo';

        if (infoCndCompleta && infoCndAlerta) {
            if (isCompleta) {
                // Consulta completa: mostrar estatísticas de CND
                infoCndCompleta.classList.remove('hidden');
                infoCndAlerta.classList.add('hidden');
                
                if (infoCndRegular) infoCndRegular.textContent = (data.qnt_cnd_regular ?? 0).toString();
                if (infoCndPendencia) infoCndPendencia.textContent = (data.qnt_cnd_pendencia ?? 0).toString();
            } else {
                // Consulta gratuita: não exibir informações de CND
                infoCndCompleta.classList.add('hidden');
                infoCndAlerta.classList.add('hidden');
            }
        }

        console.log('[RAF] Estatísticas do relatório atualizadas', {
            consultant_type: data.consultant_type,
            isCompleta,
            razao_social: data.razao_social_empresa,
            total_participants: data.total_participants,
        });
    };

    /**
     * Reseta as estatísticas do relatório (usado ao iniciar novo processamento)
     */
    const resetReportStats = () => {
        if (infoReportStats) {
            infoReportStats.classList.add('hidden');
        }
        if (infoCndCompleta) {
            infoCndCompleta.classList.add('hidden');
        }
        if (infoCndAlerta) {
            infoCndAlerta.classList.add('hidden');
        }
    };

    const setTimerState = (state) => {
        if (!timerWrap) return;
        const wasHidden = timerWrap.classList.contains('hidden');
        timerWrap.className = `${timerBaseClasses} ${timerStateClasses[state] || timerStateClasses.default}`;
        if (wasHidden) timerWrap.classList.add('hidden');
    };

    // Função auxiliar para atualizar um elemento de alerta
    const updateAlertElement = (alertEl, alertTextEl, alertIconWrap, type, message) => {
        if (!alertEl || !alertTextEl) return;

        // Verificar se é o card independente de informações (tem id info-alert)
        const isInfoCard = alertEl.id === 'info-alert';
        
        if (isInfoCard) {
            // Classes para o card independente
            const baseClasses = 'bg-white rounded-xl border shadow-md mt-6';
            
            // Classes específicas por tipo para o card
            const typeClasses = {
                info: 'border-gray-200',
                success: 'border-green-200 bg-green-50',
                error: 'border-red-200 bg-red-50',
            };
            
            // Classes de texto por tipo
            const textClasses = {
                info: 'text-gray-700',
                success: 'text-green-800',
                error: 'text-red-800',
            };
            
            alertEl.className = `${baseClasses} ${typeClasses[type] || typeClasses.info}`;
            alertTextEl.className = `min-w-0 text-sm ${textClasses[type] || textClasses.info}`;
        } else {
            // Classes base do alert (estrutura antiga)
            const baseClasses = 'rounded-xl border px-4 py-3 text-sm';
            
            // Classes específicas por tipo
            const typeClasses = {
                info: 'border-gray-200 bg-white text-gray-700',
                success: 'border-green-200 bg-green-50 text-green-800',
                error: 'border-red-200 bg-red-50 text-red-800',
            };

            alertEl.className = `${baseClasses} ${typeClasses[type] || typeClasses.info}`;
        }
        
        alertTextEl.textContent = message || '';
        alertEl.classList.toggle('hidden', !message);

        if (alertIconWrap) {
            // Classes de cor para o ícone
            const iconColorClasses = {
                info: 'text-gray-700',
                success: 'text-green-800',
                error: 'text-red-800',
            };
            
            const iconColor = iconColorClasses[type] || iconColorClasses.info;
            
            if (type === 'success') {
                alertIconWrap.innerHTML = `
                    <svg class="h-5 w-5 ${iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                `;
            } else if (type === 'error') {
                alertIconWrap.innerHTML = `
                    <svg class="h-5 w-5 ${iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                `;
            } else {
                alertIconWrap.innerHTML = `
                    <svg class="h-5 w-5 ${iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                    </svg>
                `;
            }
        }
    };

    const showAlert = (type, message) => {
        // Atualizar alerta no card "Processar SPED" (formulário)
        updateAlertElement(alertEl, alertTextEl, alertIconWrap, type, message);
        
        // Atualizar alerta no card "Informações da Consulta" (abaixo do botão)
        updateAlertElement(infoAlertEl, infoAlertTextEl, infoAlertIconWrap, type, message);
    };


    /**
     * Trata erros de autenticação 401 de forma centralizada.
     * Garante que não cause redirecionamento do SPA e mostra mensagem apropriada.
     * @param {string} context - Contexto da requisição (para logs)
     * @param {boolean} silent - Se true, não mostra alerta ao usuário (apenas para polling)
     * @returns {boolean} - true se deve parar a operação, false caso contrário
     */
    const handleAuthError = (context = 'requisição', silent = false) => {
        // Desconectar SSE em caso de erro de autenticação
        disconnectSSE();
        
        // Parar timer se estiver rodando
        stopTimer();
        
        // Resetar estado de loading
        setLoading(false);
        
        // Limpar estado persistido em caso de erro de autenticação
        clearPersistedState();
        
        // Mostrar mensagem apenas se não for silencioso
        if (!silent) {
            showAlert('error', 'Sua sessão expirou. Por favor, recarregue a página e faça login novamente.');
        }
        
        // IMPORTANTE: Não causar redirecionamento ou reset do SPA
        // O usuário pode recarregar manualmente se necessário
        return true; // Indica que a operação deve ser parada
    };

    const startTimer = () => {
        if (!timerWrap || !timerValue) return;
        timerStart = Date.now();
        timerValue.textContent = '00:00';
        setTimerState('default');
        timerWrap.classList.remove('hidden');
        
        // Ativar animação de loading
        if (timerWrap) {
            timerWrap.classList.add('animate-pulse');
        }
        
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            const elapsed = Math.floor((Date.now() - timerStart) / 1000);
            const mm = String(Math.floor(elapsed / 60)).padStart(2, '0');
            const ss = String(elapsed % 60).padStart(2, '0');
            timerValue.textContent = `${mm}:${ss}`;
        }, 1000);
        
        // Persistir estado quando timer iniciar
        persistState();
    };

    const stopTimer = () => {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        
        // Remover animações de loading
        if (timerWrap) {
            timerWrap.classList.remove('animate-pulse');
        }
        
        if (timerWrap && timerValue) {
            setTimerState('default');
            timerWrap.classList.add('hidden');
            timerValue.textContent = '00:00';
        }
        
        // NÃO parar polling quando timer parar - polling é independente
    };

    const freezeTimer = () => {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        
        // Remover animações de loading
        if (timerWrap) {
            timerWrap.classList.remove('animate-pulse');
        }
        
        if (timerWrap) {
            setTimerState('success');
            timerWrap.classList.remove('hidden');
        }
    };

    /**
     * Conecta ao endpoint SSE para receber notificações em tempo real.
     * @param {number|null} relatorioId - ID do relatório específico a aguardar (opcional)
     */
    const connectSSE = (relatorioId = null) => {
        // Verificar se já existe uma conexão ativa ou se já está conectando
        if (isConnectingSSE || (eventSource && (eventSource.readyState === EventSource.OPEN || eventSource.readyState === EventSource.CONNECTING))) {
            return;
        }
        
        // Fechar conexão anterior se existir (estado CLOSED)
        disconnectSSE();
        
        // Setar flag imediatamente para evitar múltiplas chamadas simultâneas
        isConnectingSSE = true;
        
        try {
            // Construir URL do SSE com query parameters relatorio_id e tab_id
            let sseUrl = '/api/data/notifications/stream';
            const params = new URLSearchParams();
            if (relatorioId) {
                params.append('relatorio_id', relatorioId);
            }
            params.append('tab_id', tabId);
            sseUrl += '?' + params.toString();
            
            eventSource = new EventSource(sseUrl);
            
            eventSource.onmessage = async (event) => {
                try {
                    const notification = JSON.parse(event.data);
                    
                    // Validar tab_id para garantir isolamento entre abas
                    if (notification.data && notification.data.tab_id && notification.data.tab_id !== tabId) {
                        console.log('[RAF] Notificação recebida para outra aba. Ignorando. Aguardado:', tabId, 'Recebido:', notification.data.tab_id);
                        return;
                    }
                    
                    // Tratamento de erros do n8n
                    if (notification.type === 'error' && notification.data) {
                        console.error('[RAF] Erro recebido via SSE:', notification.data);
                        
                        const errorData = notification.data;
                        
                        // Validar que é para o relatório atual (se aplicável)
                        // Aceitar erro se: (1) tab_id corresponde OU (2) relatorio_id corresponde OU (3) não temos ID específico
                        // Isso permite receber erros de registros criados pelo receiveError com ID diferente
                        const isForCurrentTab = errorData.tab_id && errorData.tab_id === tabId;
                        const isForCurrentRelatorio = !currentRelatorioId || !errorData.relatorio_id || 
                            errorData.relatorio_id === currentRelatorioId;
                        
                        if (!isForCurrentTab && !isForCurrentRelatorio) {
                            console.log('[RAF] Erro recebido para relatório/aba diferente. Ignorando.', {
                                errorTabId: errorData.tab_id,
                                currentTabId: tabId,
                                errorRelatorioId: errorData.relatorio_id,
                                currentRelatorioId: currentRelatorioId
                            });
                            return;
                        }
                        
                        // Parar todo processamento
                        setProcessing(false);
                        stopTimer();
                        setLoading(false);
                        
                        // Esconder modal e botões de confirmação
                        hideCreditsConfirmation();
                        hideConfirmButtons();
                        
                        // Esconder badge de sucesso
                        updateResultBadge('hidden');
                        
                        // Montar mensagem amigável
                        let userMessage = errorData.message || 'Ocorreu um erro no processamento.';
                        
                        if (errorData.credits_refunded) {
                            userMessage += ' Seus créditos foram reembolsados automaticamente.';
                        }
                        
                        if (errorData.recoverable) {
                            userMessage += ' Você pode tentar novamente.';
                        }
                        
                        // Exibir erro
                        showAlert('error', userMessage);
                        
                        // Limpar estado completamente para permitir novo envio
                        currentRelatorioId = null;
                        processedResumeUrls.clear();
                        asyncProcessingStarted = false;
                        disconnectSSE();
                        
                        // Limpar estado persistido em caso de erro fatal
                        clearPersistedState();
                        
                        return;
                    }
                    
                    if (notification.type === 'csv_ready' && notification.data) {
                        // CSV está disponível - buscar do banco de dados via GET
                        console.log('[RAF] Notificação de CSV disponível recebida', notification.data);
                        
                        const csvData = notification.data;
                        const relatorioId = csvData.relatorio_id;
                        
                        // Validar que temos o ID do relatório
                        if (!relatorioId) {
                            console.warn('[RAF] Notificação csv_ready recebida mas relatorio_id não está presente.');
                            return;
                        }
                        
                        // Se estamos aguardando um relatorio_id específico, validar que corresponde
                        if (currentRelatorioId && relatorioId !== currentRelatorioId) {
                            console.log('[RAF] Notificação csv_ready recebida para relatório diferente. Aguardado:', currentRelatorioId, 'Recebido:', relatorioId);
                            return; // Ignorar notificações de outros relatórios
                        }
                        
                        // Buscar CSV do banco de dados via GET
                        try {
                            const response = await fetch(`/api/data/csv/${relatorioId}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'text/csv',
                                },
                                credentials: 'same-origin',
                            });
                            
                            if (!response.ok) {
                                const errorData = await response.json().catch(() => ({}));
                                throw new Error(errorData.message || `Erro ${response.status} ao buscar CSV`);
                            }
                            
                            // Obter CSV como blob
                            const blob = await response.blob();
                            
                            // Extrair filename do header Content-Disposition ou usar padrão
                            const disposition = response.headers.get('content-disposition');
                            let filename = 'resultado.csv';
                            if (disposition) {
                                const match = disposition.match(/filename="?([^";]+)"?/i);
                                if (match && match[1]) {
                                    filename = match[1];
                                }
                            }
                            
                            // Validar que o blob não está vazio antes de mostrar o botão
                            if (blob.size > 0) {
                                setDownload(blob, filename);
                                freezeTimer();
                                showAlert('success', 'Processado com sucesso. O CSV está pronto para download.');
                                setLoading(false);
                                
                                // Atualizar estatísticas do relatório com dados recebidos via SSE
                                updateReportStats(csvData);
                                
                                // Parar processamento quando CSV estiver disponível via SSE
                                setProcessing(false);
                                
                                // Confirmar recebimento do CSV no resume_url se existir
                                if (csvData.resume_url && csvData.resume_url.trim() !== '') {
                                    await confirmCsvReceived(csvData.resume_url);
                                }
                                
                                // Limpar relatorio_id atual e desconectar SSE quando CSV estiver disponível
                                currentRelatorioId = null;
                                disconnectSSE();
                                
                                // Garantir que o modal esteja fechado
                                hideCreditsConfirmation();
                                
                                // Esconder os botões de confirmação quando o CSV estiver disponível
                                hideConfirmButtons();
                            } else {
                                console.warn('[RAF] CSV recebido mas blob está vazio.');
                            }
                        } catch (err) {
                            console.error('[RAF] Erro ao buscar CSV do banco de dados:', err);
                            showAlert('error', 'Erro ao buscar CSV. Tente novamente.');
                            setLoading(false);
                        }
                        return;
                    }
                    
                    if (notification.type === 'data_ready' && notification.data) {
                        // Se a notificação já tem os dados necessários, usar diretamente
                        if (notification.data.resume_url && notification.data.valor_total_consulta !== null && notification.data.valor_total_consulta !== undefined) {
                            // Usar dados da notificação diretamente
                            const dbData = {
                                resume_url: notification.data.resume_url,
                                valor_total_consulta: notification.data.valor_total_consulta,
                                qtd_participantes_unicos: notification.data.qtd_participantes_unicos || 0,
                            };
                            
                            // Verificar condições para exibir modal
                            const hasResumeUrl = dbData.resume_url && dbData.resume_url.trim() !== '';
                            const hasValorTotal = dbData.valor_total_consulta !== null && dbData.valor_total_consulta !== undefined;
                            
                            if (hasResumeUrl && hasValorTotal) {
                                const modalShown = await showCreditsConfirmation(
                                    dbData.resume_url,
                                    dbData.valor_total_consulta,
                                    dbData.qtd_participantes_unicos
                                );
                                // Só desconectar SSE se o modal foi realmente exibido
                                // Se foi bloqueado (resumeUrl já processado), manter SSE ativo para receber csv_ready
                                if (modalShown) {
                                    disconnectSSE();
                                }
                                return;
                            }
                        }
                    }
                } catch (err) {
                    console.error('[RAF] Erro ao processar notificação SSE:', err);
                }
            };
            
            eventSource.onerror = (error) => {
                // Verificar o estado da conexão
                if (eventSource.readyState === EventSource.CLOSED) {
                    // Conexão foi fechada - o EventSource não reconecta automaticamente neste caso
                    // Limpar flag, sem tentar reconectar manualmente
                    isConnectingSSE = false;
                }
                // Para estados CONNECTING ou OPEN com erro temporário, o EventSource reconecta automaticamente
                // Não precisamos fazer nada - o EventSource gerencia isso automaticamente
            };
            
            eventSource.onopen = () => {
                // Limpar flag quando conexão for estabelecida
                isConnectingSSE = false;
            };
        } catch (err) {
            // Limpar flag em caso de erro fatal
            isConnectingSSE = false;
            console.error('[RAF] Erro ao criar conexão SSE:', err);
        }
    };

    /**
     * Desconecta do endpoint SSE.
     */
    const disconnectSSE = () => {
        if (eventSource) {
            eventSource.close();
            eventSource = null;
        }
        // Limpar flag de conexão
        isConnectingSSE = false;
    };
    
    // Armazenar referência global para o SPA poder chamar
    window._rafDisconnectSSE = disconnectSSE;

    // ========== Funções de Persistência de Estado ==========
    
    /**
     * Chave única para o sessionStorage usando tabId
     */
    const getStorageKey = () => `raf_state_${tabId}`;
    
    /**
     * Salva o estado atual no sessionStorage.
     * Persiste apenas se houver processamento em andamento ou confirmação pendente.
     */
    const persistState = () => {
        // Só persistir se houver processamento ou confirmação pendente
        if (!isProcessing && !pendingConfirmation) {
            clearPersistedState();
            return;
        }
        
        const state = {
            isProcessing,
            currentRelatorioId,
            timerStart,
            pendingConfirmation,
            infoCard: {
                qtdParticipantes: infoQtdParticipantes?.textContent || '--',
                valorTotal: infoValorTotal?.textContent || '--',
                hasValidData: infoCardHasValidData
            },
            processedResumeUrls: Array.from(processedResumeUrls),
            savedAt: Date.now()
        };
        
        try {
            sessionStorage.setItem(getStorageKey(), JSON.stringify(state));
        } catch (e) {
            console.warn('[RAF] Erro ao salvar estado no sessionStorage:', e);
        }
    };
    
    /**
     * Restaura o estado do sessionStorage.
     * @returns {boolean} true se restaurou com sucesso, false caso contrário
     */
    const restoreState = () => {
        const storageKey = getStorageKey();
        const saved = sessionStorage.getItem(storageKey);
        
        if (!saved) {
            return false;
        }
        
        try {
            const state = JSON.parse(saved);
            
            // Validar se não está muito antigo (ex: 2 horas)
            const maxAge = 2 * 60 * 60 * 1000; // 2 horas
            if (Date.now() - state.savedAt > maxAge) {
                console.log('[RAF] Estado salvo muito antigo, ignorando');
                sessionStorage.removeItem(storageKey);
                return false;
            }
            
            // Restaurar variáveis de estado
            currentRelatorioId = state.currentRelatorioId || null;
            pendingConfirmation = state.pendingConfirmation || null;
            infoCardHasValidData = state.infoCard?.hasValidData || false;
            
            // Restaurar processedResumeUrls
            if (state.processedResumeUrls && Array.isArray(state.processedResumeUrls)) {
                processedResumeUrls = new Set(state.processedResumeUrls);
            }
            
            // Restaurar UI do card de informações
            if (state.infoCard) {
                if (infoQtdParticipantes && state.infoCard.qtdParticipantes) {
                    infoQtdParticipantes.textContent = state.infoCard.qtdParticipantes;
                }
                if (infoValorTotal && state.infoCard.valorTotal) {
                    infoValorTotal.textContent = state.infoCard.valorTotal;
                }
                
                // Mostrar card se tiver dados válidos
                if (infoCardHasValidData && infoConsultaCard) {
                    infoConsultaCard.classList.remove('hidden');
                }
            }
            
            // Restaurar timer se estiver processando
            if (state.isProcessing && state.timerStart) {
                timerStart = state.timerStart;
                
                // Calcular tempo decorrido e mostrar
                const elapsed = Math.floor((Date.now() - timerStart) / 1000);
                const mm = String(Math.floor(elapsed / 60)).padStart(2, '0');
                const ss = String(elapsed % 60).padStart(2, '0');
                
                if (timerValue) {
                    timerValue.textContent = `${mm}:${ss}`;
                }
                
                if (timerWrap) {
                    timerWrap.classList.remove('hidden');
                    timerWrap.classList.add('animate-pulse');
                    setTimerState('default');
                }
                
                // Continuar contando
                if (timerInterval) {
                    clearInterval(timerInterval);
                }
                timerInterval = setInterval(() => {
                    const elapsed = Math.floor((Date.now() - timerStart) / 1000);
                    const mm = String(Math.floor(elapsed / 60)).padStart(2, '0');
                    const ss = String(elapsed % 60).padStart(2, '0');
                    if (timerValue) {
                        timerValue.textContent = `${mm}:${ss}`;
                    }
                }, 1000);
            }
            
            // Restaurar estado de processamento apenas se houver relatorioId válido
            // Se não houver relatorioId, o processamento provavelmente já terminou
            if (state.isProcessing && currentRelatorioId) {
                setProcessing(true);
                
                // Reconectar SSE se tiver relatorioId
                console.log('[RAF] Restaurando estado: reconectando SSE para relatorio_id:', currentRelatorioId);
                connectSSE(currentRelatorioId);
                
                showAlert('info', 'Relatório em processamento. Aguarde... ⚠️ Não saia desta página até o processamento ser concluído.');
            } else if (state.isProcessing && !currentRelatorioId) {
                // Se estava processando mas não tem relatorioId, limpar estado inválido
                console.log('[RAF] Estado de processamento sem relatorioId, limpando estado inválido');
                clearPersistedState();
            }
            
            // Se tinha confirmação pendente, mostrar botões de confirmação
            if (state.pendingConfirmation) {
                if (infoConfirmCreditsWrap) {
                    infoConfirmCreditsWrap.classList.remove('hidden');
                }
                if (submitBtn) {
                    submitBtn.style.display = 'none';
                }
            }
            
            console.log('[RAF] Estado restaurado com sucesso');
            return true;
        } catch (e) {
            console.error('[RAF] Erro ao restaurar estado:', e);
            sessionStorage.removeItem(storageKey);
            return false;
        }
    };
    
    /**
     * Limpa o estado persistido do sessionStorage.
     */
    const clearPersistedState = () => {
        try {
            sessionStorage.removeItem(getStorageKey());
        } catch (e) {
            console.warn('[RAF] Erro ao limpar estado do sessionStorage:', e);
        }
    };

    /**
     * Atualiza o badge de status do resultado.
     * @param {string} state - 'processing' (amarelo), 'completed' (verde), ou 'hidden' (escondido)
     */
    const updateResultBadge = (state) => {
        if (!resultBadge) return;
        
        // Remover todas as classes de estado
        resultBadge.classList.remove('bg-green-50', 'text-green-700', 'border-green-200');
        resultBadge.classList.remove('bg-amber-50', 'text-amber-700', 'border-amber-200');
        
        if (state === 'processing') {
            // Estado: Processando (amarelo)
            resultBadge.textContent = 'Processando';
            resultBadge.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-200');
            resultBadge.classList.remove('hidden');
        } else if (state === 'completed') {
            // Estado: Processado (verde)
            resultBadge.textContent = 'Processado';
            resultBadge.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
            resultBadge.classList.remove('hidden');
        } else {
            // Estado: Escondido
            resultBadge.classList.add('hidden');
        }
    };

    const resetDownload = () => {
        if (currentDownloadUrl) {
            URL.revokeObjectURL(currentDownloadUrl);
            currentDownloadUrl = null;
        }
        if (downloadWrap) downloadWrap.classList.add('hidden');
        if (downloadLink) {
            downloadLink.href = '#';
            downloadLink.removeAttribute('download');
        }
        if (downloadLabel) downloadLabel.textContent = 'Baixar CSV';
        // Esconder o badge quando resetar
        updateResultBadge('hidden');
    };

    const setDownload = (blob, filename = 'resultado.csv') => {
        if (!downloadWrap || !downloadLink || !downloadLabel) return;
        
        // Validar que o blob existe e não está vazio antes de mostrar o botão
        if (!blob || blob.size === 0) {
            console.warn('[RAF] Tentativa de exibir download com blob vazio ou inválido');
            return;
        }
        
        resetDownload();
        currentDownloadUrl = URL.createObjectURL(blob);
        downloadLink.href = currentDownloadUrl;
        downloadLink.download = filename;
        downloadLabel.textContent = `Baixar ${filename}`;
        downloadWrap.classList.remove('hidden');
        
        // Garantir que o link não cause redirecionamento
        downloadLink.setAttribute('target', '_self');
        
        // Mostrar badge como "Processado" quando o download estiver disponível
        updateResultBadge('completed');
        
        // Limpar estado persistido quando CSV estiver disponível
        clearPersistedState();
    };

    // ========== Função para Confirmar Recebimento do CSV ==========
    
    /**
     * Confirma o recebimento do CSV no resume_url após o download estar disponível.
     * Esta função é chamada quando o CSV é recebido via SSE/polling.
     */
    const confirmCsvReceived = async (resumeUrl) => {
        if (!resumeUrl || resumeUrl.trim() === '') {
            console.warn('[RAF] resume_url vazio, não é possível confirmar recebimento do CSV');
            return;
        }

        try {
            const currentCsrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || csrf || '';
            
            const response = await fetch('/app/credits/confirm', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': currentCsrf,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    resume_url: resumeUrl,
                    valor_total_consulta: 0, // Valor zero pois já foi confirmado anteriormente
                    confirm_receipt: true, // Flag para indicar que é apenas confirmação de recebimento
                }),
            });

            if (response.ok) {
                console.log('[RAF] Confirmação de recebimento do CSV enviada com sucesso');
            } else {
                const data = await response.json().catch(() => ({}));
                console.warn('[RAF] Erro ao confirmar recebimento do CSV:', data.message || `Erro ${response.status}`);
                // Não mostrar erro ao usuário, apenas logar
            }
        } catch (err) {
            console.error('[RAF] Erro ao confirmar recebimento do CSV:', err);
            // Não mostrar erro ao usuário, apenas logar
        }
    };

    // ========== Funções do Card de Confirmação de Créditos ==========
    
    const showCreditsConfirmation = async (resumeUrl, valorTotalConsulta, qtdParticipantesUnicos) => {
        // Verificar se este resumeUrl já foi processado
        if (processedResumeUrls.has(resumeUrl)) {
            console.log('[RAF] showCreditsConfirmation bloqueado - resumeUrl já foi processado:', resumeUrl);
            return false; // Retornar false para indicar que foi bloqueado
        }
        
        if (!creditsModalBackdrop) {
            console.error('[RAF] ERRO: creditsModalBackdrop não encontrado!');
            return false;
        }
        
        if (!creditsCard) {
            console.error('[RAF] ERRO: creditsCard não encontrado!');
            return false;
        }

        // PAUSAR o relógio quando o modal for exibido
        stopTimer();

        pendingConfirmation = { resumeUrl, valorTotalConsulta };

        // Buscar saldo atual de créditos
        let userBalance = 0;
        try {
            const currentCsrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || csrf || '';
            const balanceResponse = await fetch('/app/credits/balance', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': currentCsrf,
                    'Accept': 'application/json',
                },
                credentials: 'same-origin', // Garantir que cookies de sessão sejam enviados
            });
            if (balanceResponse.ok) {
                const balanceData = await balanceResponse.json();
                userBalance = balanceData.credits || 0;
            }
        } catch (e) {
            console.error('Erro ao buscar saldo de créditos:', e);
        }

        // Verificar se tem créditos suficientes (valores são inteiros)
        const hasEnough = userBalance >= valorTotalConsulta;

        // Atualizar UI do card
        if (creditsCnpjCount) {
            // Trata valores null/undefined, mas permite 0
            const cnpjValue = (qtdParticipantesUnicos !== null && qtdParticipantesUnicos !== undefined) 
                ? qtdParticipantesUnicos.toString() 
                : '--';
            creditsCnpjCount.textContent = cnpjValue;
        } else {
            console.error('[RAF] creditsCnpjCount não encontrado!');
        }
        
        if (creditsTotal) {
            // Garante que valorTotalConsulta seja um número válido (converter string para número se necessário)
            const creditValue = formatCredits(valorTotalConsulta);
            creditsTotal.textContent = creditValue;
        } else {
            console.error('[RAF] creditsTotal não encontrado!');
        }

        // Mostrar/esconder alerta de créditos insuficientes
        if (creditsInsufficientAlert) {
            creditsInsufficientAlert.classList.toggle('hidden', hasEnough);
        }
        
        // Habilitar/desabilitar botão de confirmar
        if (creditsConfirmBtn) {
            creditsConfirmBtn.disabled = !hasEnough;
        }
        if (creditsConfirmText) {
            creditsConfirmText.textContent = hasEnough ? 'Confirmar e processar' : 'Créditos insuficientes';
        }
        
        // Atualizar também os botões do card de informações
        if (infoCreditsConfirmBtn) {
            infoCreditsConfirmBtn.disabled = !hasEnough;
        }
        if (infoCreditsConfirmText) {
            infoCreditsConfirmText.textContent = hasEnough ? 'Confirmar e processar' : 'Créditos insuficientes';
        }
        
        // Atualizar card de informações com os valores recebidos
        updateInfoCard({
            qtd_participantes_unicos: qtdParticipantesUnicos,
            valor_total_consulta: valorTotalConsulta
        }, true); // needsConfirmation = true

        // Mostrar botão de confirmação no card de informações
        if (infoConfirmCreditsWrap) {
            infoConfirmCreditsWrap.classList.remove('hidden');
        }

        // Esconder o botão principal quando o modal aparecer
        if (submitBtn) {
            submitBtn.style.display = 'none';
        }

        // Resetar apenas o estado de loading do botão principal
        // NÃO resetar setProcessing aqui - o processamento continua em andamento
        setLoading(false);

        // Garantir que os botões não estejam em estado de loading quando o modal é exibido
        setCreditsLoading(false);

        // Mostrar o modal overlay
        if (!creditsModalBackdrop) {
            console.error('[RAF] ERRO: creditsModalBackdrop não encontrado! Verifique se o elemento existe no DOM.');
            return false;
        }
        
        if (!creditsCard) {
            console.error('[RAF] ERRO: creditsCard não encontrado! Verifique se o elemento existe no DOM.');
            return false;
        }
        
        // Remover classe hidden do backdrop para exibir o modal
        creditsModalBackdrop.classList.remove('hidden');
        
        // Prevenir scroll do body quando o modal estiver aberto
        document.body.style.overflow = 'hidden';
        
        // Forçar reflow para garantir que o modal seja renderizado
        void creditsModalBackdrop.offsetHeight;
        
        // Se ainda estiver escondido, tentar forçar exibição
        if (creditsModalBackdrop.classList.contains('hidden')) {
            console.error('[RAF] ERRO: Modal ainda está escondido após remover classe hidden!');
            creditsModalBackdrop.style.display = 'flex';
            creditsModalBackdrop.style.visibility = 'visible';
        }
        
        // Persistir estado quando modal de confirmação for exibido
        persistState();
        
        return true; // Modal foi exibido com sucesso
    };

    const hideCreditsConfirmation = () => {
        // Esconder o backdrop do modal
        if (creditsModalBackdrop) {
            creditsModalBackdrop.classList.add('hidden');
            // Limpar estilos inline que podem ter sido definidos ao abrir o modal
            creditsModalBackdrop.style.display = '';
            creditsModalBackdrop.style.visibility = '';
        }
        
        // Restaurar scroll do body
        document.body.style.overflow = '';
        
        // NÃO mostrar o botão principal aqui - ele só deve aparecer quando os botões de confirmação forem escondidos
        // Verificar se os botões de confirmação estão visíveis
        const confirmButtonsVisible = infoConfirmCreditsWrap && !infoConfirmCreditsWrap.classList.contains('hidden');
        
        // Se os botões de confirmação ainda estão visíveis, manter o botão principal escondido
        // Se não estão visíveis, mostrar o botão principal
        if (submitBtn && !confirmButtonsVisible) {
            submitBtn.style.display = '';
        }
        
        // Resetar apenas o estado de loading
        // NÃO resetar setProcessing aqui - o processamento pode continuar em andamento
        setLoading(false);
        
        // NÃO esconder os botões de confirmação aqui - eles devem permanecer visíveis
        // até o usuário realmente confirmar ou cancelar
        // Os botões serão escondidos apenas em handleCancelCredits ou confirmCreditsAndProcess
        
        // TODO: Desconectar recebimento de dados (SSE/WebSocket) quando modal for escondido
        // disconnectDataReceiver();
    };
    
    /**
     * Esconde os botões de confirmação do card de informações.
     * Deve ser chamado apenas quando o usuário realmente confirmar ou cancelar.
     * Quando os botões são escondidos, o botão principal é mostrado novamente.
     * Se o processamento estiver em andamento, o botão será mostrado com spinner girando.
     */
    const hideConfirmButtons = () => {
        if (infoConfirmCreditsWrap) {
            infoConfirmCreditsWrap.classList.add('hidden');
        }
        pendingConfirmation = null;
        
        // Mostrar o botão principal novamente quando os botões de confirmação forem escondidos
        if (submitBtn) {
            submitBtn.style.display = '';
        }
        
        // Se o processamento estiver em andamento, garantir que o spinner esteja visível
        if (isProcessing) {
            submitLabel.textContent = 'Aguarde...';
            submitSpinner.classList.remove('hidden');
            submitIcon.classList.add('hidden');
        }
    };

    // ========== Função Stub para Recebimento de Dados Futuro ==========
    // TODO: Implementar recebimento de dados (SSE/WebSocket)
    // Esta função será chamada quando dados chegarem do n8n via SSE/WebSocket
    const onDataReceived = (data) => {
        // Receber dados do n8n e atualizar UI
        // Exemplo de estrutura esperada:
        // {
        //     resume_url: string,
        //     qtd_participantes_unicos: number,
        //     valor_total_consulta: number,
        //     csv?: string,
        //     filename?: string,
        //     headers?: array,
        //     rows?: array
        // }
        
        // Atualizar card de informações
        if (data.qtd_participantes_unicos || data.valor_total_consulta) {
            updateInfoCard(data, !!pendingConfirmation);
        }
        
        // Se tem CSV, processar e disponibilizar download
        // Validar rigorosamente antes de mostrar o botão
        const hasCsvData = data.csv && typeof data.csv === 'string' && data.csv.trim().length > 0;
        const hasRowsData = data.headers && data.rows && Array.isArray(data.rows) && data.rows.length > 0;
        
        if (hasCsvData || hasRowsData) {
            if (hasCsvData) {
                const blob = new Blob([data.csv], { type: 'text/csv;charset=utf-8;' });
                const filename = data.filename || 'resultado.csv';
                
                // Validar que o blob não está vazio antes de mostrar o botão
                if (blob.size > 0) {
                    setDownload(blob, filename);
                    
                    // Parar processamento quando CSV estiver disponível
                    setProcessing(false);
                    
                    // Confirmar recebimento do CSV no resume_url se existir
                    if (data.resume_url && data.resume_url.trim() !== '') {
                        confirmCsvReceived(data.resume_url).catch(err => {
                            console.error('[RAF] Erro ao confirmar recebimento do CSV em onDataReceived:', err);
                        });
                    }
                    
                    freezeTimer();
                    showAlert('success', 'Processado com sucesso. CSV disponível.');
                    setLoading(false);
                } else {
                    console.warn('[RAF] CSV recebido em onDataReceived mas blob está vazio');
                }
            }
        }
        
        // Se precisa de confirmação e ainda não foi mostrado o modal
        if (data.resume_url && data.valor_total_consulta && creditsModalBackdrop?.classList.contains('hidden')) {
            showCreditsConfirmation(
                data.resume_url,
                data.valor_total_consulta,
                data.qtd_participantes_unicos || 0
            );
        }
    };

    const setCreditsLoading = (loading) => {
        if (creditsConfirmBtn) creditsConfirmBtn.disabled = loading;
        if (creditsCancelBtn) creditsCancelBtn.disabled = loading;
        if (creditsConfirmSpinner) creditsConfirmSpinner.classList.toggle('hidden', !loading);
        if (creditsConfirmText) creditsConfirmText.textContent = loading ? 'Processando...' : 'Confirmar e processar';
        
        // Atualizar também os botões do card de informações
        if (infoCreditsConfirmBtn) infoCreditsConfirmBtn.disabled = loading;
        if (infoCreditsCancelBtn) infoCreditsCancelBtn.disabled = loading;
        if (infoCreditsConfirmSpinner) infoCreditsConfirmSpinner.classList.toggle('hidden', !loading);
        if (infoCreditsConfirmText) infoCreditsConfirmText.textContent = loading ? 'Processando...' : 'Confirmar e processar';
    };

    const confirmCreditsAndProcess = async () => {
        if (!pendingConfirmation) return;
        
        // Proteção contra cliques duplos
        if (isConfirming) {
            console.log('[RAF] Confirmação já em andamento, ignorando clique duplicado');
            return;
        }

        const { resumeUrl, valorTotalConsulta } = pendingConfirmation;
        isConfirming = true;
        
        // Marcar resumeUrl como processado ANTES do fetch para evitar reprocessamento
        processedResumeUrls.add(resumeUrl);
        
        setCreditsLoading(true);

        // AbortController para timeout de 35 segundos
        const abortController = new AbortController();
        let timeoutId = null; // Declarar fora do try para acessar no finally
        
        try {
            timeoutId = setTimeout(() => {
                abortController.abort();
            }, 35000); // 35 segundos
            const currentCsrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || csrf || '';
            
            const response = await fetch('/app/credits/confirm', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': currentCsrf,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json, text/csv',
                },
                credentials: 'same-origin',
                signal: abortController.signal,
                body: JSON.stringify({
                    resume_url: resumeUrl,
                    valor_total_consulta: valorTotalConsulta,
                }),
            });

            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            const contentType = response.headers.get('content-type');

            // Tratar HTTP 409 (Conflict) - Lock otimista detectou processamento duplicado
            if (response.status === 409) {
                const data = await response.json().catch(() => ({}));
                showAlert('info', data.message || 'Processamento já em andamento. Aguarde...');
                hideCreditsConfirmation();
                stopTimer();
                setLoading(false);
                setProcessing(false);
                return;
            }

            if (response.status === 402) {
                // Créditos insuficientes
                const data = await response.json();
                showAlert('error', data.message || 'Créditos insuficientes. Entre em contato pelo telefone (67) 99984-4366.');
                hideCreditsConfirmation();
                stopTimer();
                setLoading(false);
                setProcessing(false);
                return;
            }

            // Tratar HTTP 502 (Bad Gateway) - Timeout no webhook
            if (response.status === 502) {
                console.error('[RAF] HTTP 502 recebido do servidor');
                let errorMessage = 'Timeout ao processar. Os créditos foram reembolsados. Tente novamente.';
                try {
                    const text = await response.text();
                    console.error('[RAF] Resposta 502:', text.substring(0, 500));
                    // Tentar parsear como JSON se possível
                    if (text && text.trim().startsWith('{')) {
                        const data = JSON.parse(text);
                        if (data.message) {
                            errorMessage = data.message;
                        }
                    }
                } catch (parseErr) {
                    console.error('[RAF] Erro ao parsear resposta 502:', parseErr);
                }
                showAlert('error', errorMessage);
                hideCreditsConfirmation();
                stopTimer();
                setLoading(false);
                setProcessing(false);
                return;
            }

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.message || `Erro ${response.status}`);
            }

            // REINICIAR o relógio após confirmar créditos
            startTimer();

            // Verificar se a resposta é JSON (pode ser resposta assíncrona)
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                
                // Armazenar relatorio_id se estiver presente na resposta
                // Preservar currentRelatorioId existente se a resposta não incluir ID
                // (pode acontecer em alguns casos, mas o ID já deveria estar definido)
                if (data.id || data.relatorio_id) {
                    currentRelatorioId = data.id || data.relatorio_id;
                    console.log('[RAF] currentRelatorioId atualizado após confirmação de créditos:', currentRelatorioId);
                } else if (!currentRelatorioId) {
                    // Se não temos ID na resposta E não temos ID anterior, logar aviso
                    console.warn('[RAF] Confirmação de créditos retornou sem ID e não há currentRelatorioId anterior');
                }
                
                    // Se for resposta assíncrona (n8n retornou JSON ao invés de CSV)
                if (data.success && data.async === true) {
                    // Esconder botão de download até o CSV estar realmente disponível
                    resetDownload();
                    
                    // Não exibir mensagem "Workflow was started" ao usuário
                    const message = data.message && !data.message.toLowerCase().includes('workflow was started')
                        ? data.message
                        : 'Créditos confirmados. Aguarde enquanto o relatório final está sendo gerado. ⚠️ Não saia desta página até o processamento ser concluído.';
                    showAlert('info', message);
                    
                    // Conectar SSE para receber notificação quando CSV estiver disponível
                    // O SSE vai notificar quando o CSV estiver pronto, sem necessidade de polling
                    // Garantir que temos um relatorio_id antes de conectar
                    if (currentRelatorioId) {
                        console.log('[RAF] Conectando SSE após confirmação de créditos com relatorio_id:', currentRelatorioId);
                        connectSSE(currentRelatorioId);
                    } else {
                        console.error('[RAF] ERRO: Tentando conectar SSE sem currentRelatorioId após confirmação de créditos');
                        showAlert('error', 'Erro ao processar confirmação. Por favor, tente novamente.');
                    }
                    
                    // Fechar o modal primeiro para mostrar o botão principal
                    hideCreditsConfirmation();
                    
                    // Esconder os botões de confirmação após confirmar
                    hideConfirmButtons();
                    
                    // Preservar valores atuais do card antes de iniciar processamento
                    if (infoCardHasValidData && infoConsultaCard) {
                        // Garantir que o card permaneça visível durante processamento
                        infoConsultaCard.classList.remove('hidden');
                    }
                    
                    // Mudar botão para "Processando..." após confirmar créditos
                    setProcessing(true);
                    asyncProcessingStarted = true; // Marcar que processamento assíncrono foi iniciado
                    
                    return;
                }
            }

            // Sucesso - resposta é CSV (resposta síncrona)
            if (contentType && contentType.includes('text/csv')) {
                const blob = await response.blob();
                const disposition = response.headers.get('content-disposition');
                let filename = 'resultado.csv';
                const match = disposition && disposition.match(/filename=\"?([^\";]+)\"?/i);
                if (match && match[1]) {
                    filename = match[1];
                }

                setDownload(blob, filename);
                freezeTimer();
                showAlert('success', 'Processado com sucesso. O CSV está pronto para download.');
                
                // Parar processamento quando CSV estiver disponível
                setProcessing(false);

                // Atualizar saldo exibido se existir
                const remainingCredits = response.headers.get('X-Credits-Remaining');

                // Reset parcial após sucesso
                form.reset();
                // Resetar radio buttons do tipo de SPED para o primeiro (efd-contrib)
                if (tipoRadios.length > 0) {
                    tipoRadios[0].checked = true;
                }
                updateTipoSpedLabels();
                updateFileUi();
                updateEnablement();
            } else {
                // Se não for CSV nem JSON async, aguardar notificação do n8n via SSE
                // O relógio já foi reiniciado acima e continuará até o CSV estar disponível
                
                // Esconder botão de download até o CSV estar realmente disponível
                resetDownload();
                
                showAlert('info', 'Créditos confirmados. Aguarde enquanto o relatório final está sendo gerado. ⚠️ Não saia desta página até o processamento ser concluído.');
                
                // Conectar SSE para receber notificação quando CSV estiver disponível
                connectSSE(currentRelatorioId);
                
                // Fechar o modal primeiro para mostrar o botão principal
                hideCreditsConfirmation();
                
                // Esconder os botões de confirmação após confirmar
                hideConfirmButtons();
                
                // Preservar valores atuais do card antes de iniciar processamento
                if (infoCardHasValidData && infoConsultaCard) {
                    // Garantir que o card permaneça visível durante processamento
                    infoConsultaCard.classList.remove('hidden');
                }
                
                // Mudar botão para "Processando..." após confirmar créditos
                setProcessing(true);
                asyncProcessingStarted = true; // Marcar que processamento assíncrono foi iniciado
            }
        } catch (err) {
            console.error('[RAF] Erro no confirmCreditsAndProcess:', err);
            // Tratar timeout do AbortController
            if (err.name === 'AbortError') {
                showAlert('error', 'Timeout ao processar. A requisição demorou mais de 35 segundos. Tente novamente.');
            } else if (err.name === 'TypeError' && err.message && err.message.includes('fetch')) {
                // Erro de rede ou conexão
                showAlert('error', 'Erro de conexão. Verifique sua internet e tente novamente.');
            } else {
                showAlert('error', err.message || 'Erro ao processar. Tente novamente.');
            }
            hideCreditsConfirmation();
            stopTimer();
        } finally {
            // Garantir que o modal feche em qualquer circunstância
            if (creditsModalBackdrop) {
                creditsModalBackdrop.classList.add('hidden');
                // Limpar estilos inline que podem ter sido definidos ao abrir o modal
                creditsModalBackdrop.style.display = '';
                creditsModalBackdrop.style.visibility = '';
            }
            // Esconder os botões de confirmação após confirmar (em caso de erro também)
            hideConfirmButtons();
            document.body.style.overflow = '';
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            setCreditsLoading(false);
            setLoading(false);
            // Só parar o processamento se não foi iniciado processamento assíncrono
            // Se o processamento assíncrono foi iniciado, ele deve continuar até o CSV estar disponível
            if (!asyncProcessingStarted) {
                setProcessing(false); // Garantir que processamento seja resetado em caso de erro
                // Limpar estado persistido apenas se não houver processamento assíncrono em andamento
                clearPersistedState();
            }
            asyncProcessingStarted = false; // Resetar flag
            isConfirming = false; // Liberar flag
            pendingConfirmation = null; // Limpar confirmação pendente
        }
    };

    // Event listeners do card de confirmação de créditos
    const handleCancelCredits = async () => {
        if (!pendingConfirmation || !pendingConfirmation.resumeUrl) {
            hideCreditsConfirmation();
            // Esconder os botões de confirmação após cancelar (isso também mostra o botão principal)
            hideConfirmButtons();
            stopTimer();
            setLoading(false);
            setProcessing(false);
            // Esconder o badge "Processando" quando cancelar
            updateResultBadge('hidden');
            // Limpar estado persistido quando usuário cancelar
            clearPersistedState();
            showAlert('info', 'Operação cancelada.');
            return;
        }

        const resumeUrl = pendingConfirmation.resumeUrl;

        try {
            const currentCsrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || csrf || '';
            
            const response = await fetch('/app/credits/cancel', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': currentCsrf,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin', // Garantir que cookies de sessão sejam enviados
                body: JSON.stringify({
                    resume_url: resumeUrl,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                console.error('[RAF] Erro ao cancelar:', data.message || 'Erro desconhecido');
                showAlert('error', data.message || 'Erro ao cancelar operação. Tente novamente.');
            } else {
                showAlert('info', 'Operação cancelada.');
            }
        } catch (err) {
            console.error('[RAF] Erro ao cancelar:', err);
            showAlert('error', 'Erro ao cancelar operação. Tente novamente.');
        } finally {
            hideCreditsConfirmation();
            // Esconder os botões de confirmação após cancelar (isso também mostra o botão principal)
            hideConfirmButtons();
            stopTimer();
            setLoading(false);
            setProcessing(false);
            // Esconder o badge "Processando" quando cancelar
            updateResultBadge('hidden');
            // Limpar estado persistido quando usuário cancelar
            clearPersistedState();
        }
    };

    if (creditsCancelBtn) {
        creditsCancelBtn.addEventListener('click', handleCancelCredits);
    }

    if (creditsConfirmBtn) {
        creditsConfirmBtn.addEventListener('click', () => {
            confirmCreditsAndProcess();
        });
    }

    // Event listeners para fechar o modal
    // Fechar ao clicar no backdrop (mas não no conteúdo do modal)
    if (creditsModalBackdrop) {
        creditsModalBackdrop.addEventListener('click', (e) => {
            // Só fechar se o clique foi diretamente no backdrop, não no conteúdo do modal
            if (e.target === creditsModalBackdrop) {
                hideCreditsConfirmation();
            }
        });
    }

    // Fechar ao clicar no botão X
    if (creditsModalCloseBtn) {
        creditsModalCloseBtn.addEventListener('click', () => {
            hideCreditsConfirmation();
        });
    }

    // Fechar ao pressionar ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && creditsModalBackdrop && !creditsModalBackdrop.classList.contains('hidden')) {
            hideCreditsConfirmation();
        }
    });

    // Event listeners para os botões do card de informações
    if (infoCreditsCancelBtn) {
        infoCreditsCancelBtn.addEventListener('click', handleCancelCredits);
    }

    if (infoCreditsConfirmBtn) {
        infoCreditsConfirmBtn.addEventListener('click', () => {
            confirmCreditsAndProcess();
        });
    }

    
    // Prevenir comportamento padrão do link de download para evitar redirecionamentos
    if (downloadLink) {
        downloadLink.addEventListener('click', (e) => {
            // Se o href for '#' ou vazio, prevenir comportamento padrão
            const href = downloadLink.getAttribute('href');
            if (!href || href === '#' || href === window.location.href + '#') {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    }

    // ========== Fim Funções do Card de Confirmação de Créditos ==========

    const setDropzoneEnabled = (enabled, hasTipo = false, hasModalidade = false) => {
        if (!dropzone) return;
        
        if (enabled) {
            // Dropzone habilitada
            dropzone.className = 'border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors cursor-pointer bg-gray-50 hover:bg-blue-50';
            dropzone.setAttribute('aria-disabled', 'false');
        } else {
            // Dropzone desabilitada
            dropzone.className = 'border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-colors cursor-not-allowed bg-gray-100 opacity-60 pointer-events-none';
            dropzone.setAttribute('aria-disabled', 'true');
        }
    };

    const updateFileUi = () => {
        const file = fileInput.files?.[0];
        if (!file) {
            fileMeta.classList.add('hidden');
            fileNameEl.textContent = '';
            fileSizeEl.textContent = '';
            return;
        }

        const fileName = file.name;
        fileMeta.classList.remove('hidden');
        fileNameEl.textContent = fileName;
        fileSizeEl.textContent = formatFileSize(file.size);
    };

    const getSelectedModalidade = () => {
        const selected = Array.from(modalidadeRadios).find(radio => radio.checked);
        return selected ? selected.value : '';
    };

    const updateEnablement = () => {
        const hasTipo = getSelectedTipoSped() !== '';
        const modalidade = getSelectedModalidade();
        const hasModalidade = modalidade !== '';
        const fileEnabled = hasTipo && hasModalidade && !isLoading && !isProcessing;
        fileInput.disabled = !fileEnabled || isProcessing;
        setDropzoneEnabled(fileEnabled, hasTipo, hasModalidade);
        const hasFile = fileInput.files?.length > 0;
        // Botão desabilitado se: não tem campos preenchidos, está carregando, ou está processando
        submitBtn.disabled = !(hasTipo && hasFile && hasModalidade) || isLoading || isProcessing;
        
        // Atualizar visual dos labels dos radio buttons
        updateTipoSpedLabels();
        updateTipoConsultaLabels();
    };

    // Event listeners para radio buttons do tipo de SPED
    tipoRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            showAlert('info', ''); // Limpar alerta ao mudar opção
            updateEnablement();
            updateTipoSpedLabels();
        });
    });
    
    // Event listeners para radio buttons do tipo de consulta
    modalidadeRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            showAlert('info', ''); // Limpar alerta ao mudar opção
            updateEnablement();
            updateTipoConsultaLabels();
        });
    });
    fileInput.addEventListener('change', () => {
        showAlert('info', ''); // Limpar alerta sempre ao tentar mudar arquivo
        const file = fileInput.files?.[0];
        
        // Validar se o arquivo é .txt
        if (file) {
            const fileName = file.name.toLowerCase();
            const isTxt = fileName.endsWith('.txt') || file.type === 'text/plain';
            
            if (!isTxt) {
                showAlert('error', 'Apenas arquivos .txt são permitidos. Por favor, selecione um arquivo .txt.');
                // Limpar o input
                fileInput.value = '';
                updateFileUi();
                updateEnablement();
                return;
            }
        }
        
        updateFileUi();
        updateEnablement();
    });

    const openFilePicker = () => {
        if (fileInput.disabled || isLoading) return;
        fileInput.click();
    };

    if (dropzone) {
        dropzone.addEventListener('click', openFilePicker);
        dropzone.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openFilePicker();
            }
        });

        const setDragOver = (on) => {
            if (!dropzone) return;
            const isEnabled = dropzone.getAttribute('aria-disabled') === 'false';
            if (!isEnabled) return;
            
            if (on) {
                dropzone.classList.remove('border-gray-300', 'bg-gray-50', 'hover:border-blue-400', 'hover:bg-blue-50');
                dropzone.classList.add('border-blue-500', 'bg-blue-50');
            } else {
                dropzone.classList.remove('border-blue-500', 'bg-blue-50');
                dropzone.classList.add('border-gray-300', 'bg-gray-50', 'hover:border-blue-400', 'hover:bg-blue-50');
            }
        };

        dropzone.addEventListener('dragenter', (e) => {
            if (fileInput.disabled || isLoading) return;
            e.preventDefault();
            setDragOver(true);
        });
        dropzone.addEventListener('dragover', (e) => {
            if (fileInput.disabled || isLoading) return;
            e.preventDefault();
            setDragOver(true);
        });
        dropzone.addEventListener('dragleave', () => setDragOver(false));
        dropzone.addEventListener('drop', (e) => {
            if (fileInput.disabled || isLoading) return;
            e.preventDefault();
            setDragOver(false);

            const file = e.dataTransfer?.files?.[0];
            if (!file) return;
            const isTxt = file.name?.toLowerCase().endsWith('.txt') || file.type === 'text/plain';
            if (!isTxt) {
                showAlert('error', 'Apenas arquivos .txt são permitidos. Por favor, selecione um arquivo .txt.');
                return;
            }

            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    if (changeFileBtn) {
        changeFileBtn.addEventListener('click', openFilePicker);
    }

    // Event listener para o botão Enviar SPED (que está fora do formulário)
    if (submitBtn) {
        submitBtn.addEventListener('click', (e) => {
            e.preventDefault();
            // Disparar o submit do formulário
            if (form) {
                form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
            }
        });
    }

    const setLoading = (loading, message = '') => {
        isLoading = !!loading;
        
        // Verificar se o modal está aberto (não tem classe hidden)
        const isModalOpen = creditsModalBackdrop && !creditsModalBackdrop.classList.contains('hidden');
        
        // Só atualizar o texto do botão se não estiver processando e o modal não estiver aberto
        // Se o modal estiver aberto, o botão principal está escondido
        if (!isProcessing && !isModalOpen) {
            submitLabel.textContent = isLoading ? 'Enviando...' : 'Enviar SPED';
            submitSpinner.classList.toggle('hidden', !isLoading);
            submitIcon.classList.toggle('hidden', isLoading);
        }
        
        // Desabilitar/enabilitar radio buttons do tipo de SPED
        tipoRadios.forEach(radio => {
            radio.disabled = isLoading || isProcessing;
        });
        
        updateEnablement();

        if (message) {
            showAlert('info', message);
        }
    };

    /**
     * Define o estado de processamento após confirmação de créditos.
     * O botão fica em "Processando..." e desabilitado até receber o CSV via SSE.
     */
    const setProcessing = (processing) => {
        isProcessing = !!processing;
        
        // Verificar se o modal está aberto (não tem classe hidden)
        const isModalOpen = creditsModalBackdrop && !creditsModalBackdrop.classList.contains('hidden');
        
        // Só atualizar o texto do botão se o modal não estiver aberto
        // Se o modal estiver aberto, o botão principal está escondido
        if (!isModalOpen) {
            if (isProcessing) {
                submitLabel.textContent = 'Aguarde...';
                submitSpinner.classList.remove('hidden');
                submitIcon.classList.add('hidden');
                // Mostrar badge como "Processando" quando iniciar processamento
                updateResultBadge('processing');
            } else {
                // Voltar ao estado normal (não processando)
                submitLabel.textContent = isLoading ? 'Enviando...' : 'Enviar SPED';
                submitSpinner.classList.toggle('hidden', !isLoading);
                submitIcon.classList.toggle('hidden', isLoading);
                // Não alterar o badge aqui - ele deve manter o estado atual
                // Se já estiver "Processado", manter; se não houver download, manter escondido
            }
        }
        
        // Desabilitar/enabilitar campos do formulário durante processamento
        tipoRadios.forEach(radio => {
            radio.disabled = isLoading || isProcessing;
        });
        
        if (modalidadeRadios && modalidadeRadios.length > 0) {
            modalidadeRadios.forEach(radio => {
                radio.disabled = isLoading || isProcessing;
            });
        }
        
        fileInput.disabled = isLoading || isProcessing;
        
        updateEnablement();
        
        // Persistir estado quando processamento mudar
        persistState();
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Reset estado para novo envio
        resetState();

        const tipoRadioValue = getSelectedTipoSped();
        const tipo = mapTipoSpedToBackend(tipoRadioValue);
        const file = fileInput.files[0];
        const modalidade = getSelectedModalidade();
        const clienteSelect = document.getElementById('cliente-select');
        const clienteId = clienteSelect ? clienteSelect.value : '';

        if (!tipo) {
            showAlert('error', 'Selecione o tipo de SPED.');
            return;
        }

        if (!modalidade) {
            showAlert('error', 'Selecione o tipo de consulta (gratuita ou completa).');
            return;
        }

        if (!file) {
            showAlert('error', 'Selecione um arquivo .txt');
            return;
        }

        // Validar se o arquivo é .txt
        const fileName = file.name.toLowerCase();
        const isTxt = fileName.endsWith('.txt') || file.type === 'text/plain';
        if (!isTxt) {
            showAlert('error', 'Apenas arquivos .txt são permitidos. Por favor, selecione um arquivo .txt.');
            return;
        }

        setLoading(true);
        stopTimer();
        resetDownload();
        startTimer();

        const formData = new FormData();
        formData.append('tipo', tipo);
        formData.append('modalidade', modalidade);
        formData.append('sped', file);
        formData.append('tab_id', tabId); // Identificador único por aba
        
        // Sempre enviar cliente_id (0 se não selecionado)
        const clienteIdToSend = (clienteId && clienteId !== '') ? parseInt(clienteId) : 0;
        formData.append('cliente_id', clienteIdToSend);

        let hasDownloadSuccess = false;
        let hasAsyncStarted = false;

        try {
            // Obter token CSRF atualizado antes de cada requisição
            const currentCsrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || csrf || '';
            
            const response = await fetch('/app/raf/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': currentCsrf
                },
                credentials: 'same-origin', // Garantir que cookies de sessão sejam enviados
                body: formData
            }).catch((fetchError) => {
                // Trata erros de rede/timeout do fetch
                if (fetchError.name === 'TypeError' && fetchError.message.includes('fetch')) {
                    throw new Error('Erro de conexão. O processamento pode estar demorando mais que o esperado. Aguarde alguns minutos e verifique novamente.');
                }
                throw fetchError;
            });

            // Verifica se a resposta é JSON antes de fazer parse
            const contentType = response.headers.get('content-type');
            let data;
            
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();

                // Verifica se precisa de confirmação de créditos
                if (data.success && data.needs_confirmation && data.resume_url && data.valor_total_consulta !== undefined) {
                    // Armazenar relatorio_id se estiver presente na resposta
                    if (data.relatorio_id || data.id) {
                        currentRelatorioId = data.relatorio_id || data.id;
                    }
                    
                    // Atualizar card de informações da consulta
                    updateInfoCard({
                        qtd_participantes_unicos: data.qtd_participantes_unicos,
                        valor_total_consulta: data.valor_total_consulta
                    }, true); // needsConfirmation = true
                    
                    showAlert('info', 'Valores da consulta processados. Aguarde a confirmação de créditos...');
                    
                    // Aguarda a função async completar
                    await showCreditsConfirmation(
                        data.resume_url, 
                        data.valor_total_consulta,
                        data.qtd_participantes_unicos || 0
                    );
                    
                    // IMPORTANTE: Para o loading e timer, mas NÃO continua o processamento
                    setLoading(false);
                    // Não chama stopTimer() aqui porque o timer deve continuar até a confirmação
                    return; // Não continua o fluxo normal, a seção de confirmação vai gerenciar
                }
                
                // Se for resposta assíncrona (modalidade gratuita - sem confirmação de créditos)
                // Isso acontece quando o webhook retorna apenas {"message": "Workflow was started"}
                if (data.success && data.async === true) {
                    // Armazenar relatorio_id se disponível
                    if (data.relatorio_id || data.id) {
                        currentRelatorioId = data.relatorio_id || data.id;
                    }
                    
                    // Marcar que o processamento assíncrono foi iniciado
                    hasAsyncStarted = true;
                    
                    resetDownload();
                    // Não exibir mensagem "Workflow was started" ao usuário
                    // Substituir por mensagem genérica
                    const message = data.message && !data.message.toLowerCase().includes('workflow was started')
                        ? data.message
                        : 'Processamento iniciado. Aguarde enquanto geramos o relatório. ⚠️ Não saia desta página até o processamento ser concluído.';
                    showAlert('info', message);
                    
                    // Conectar SSE para receber notificação quando CSV estiver disponível
                    connectSSE(currentRelatorioId);
                    
                    // Mudar botão para "Processando..." quando processamento assíncrono iniciar
                    setProcessing(true);
                    
                    setLoading(false);
                    return;
                }
                
                // Se não for needs_confirmation nem async, verificar se tem CSV
                if (data.success) {
                    // Se tem CSV nos dados, processar normalmente
                    if (data.csv && data.csv.trim() !== '') {
                        const blob = new Blob([data.csv], { type: 'text/csv;charset=utf-8;' });
                        const filename = data.filename && data.filename.trim() !== '' 
                            ? data.filename 
                            : 'resultado.csv';
                        setDownload(blob, filename);
                        hasDownloadSuccess = true;
                        freezeTimer();
                        showAlert('success', 'Processado com sucesso. O CSV está pronto para download.');
                        
                        // Parar processamento quando CSV estiver disponível
                        setProcessing(false);

                        // Reset parcial após sucesso
                        form.reset();
                        updateFileUi();
                        updateEnablement();
                    } else {
                        // CSV vazio ou não presente - dados serão salvos no banco pelo n8n
                        setLoading(false);
                        
                        // Mostrar mensagem para o usuário aguardar o n8n
                        showAlert('info', 'SPED processado. Aguarde enquanto geramos o relatório final. ⚠️ Não saia desta página até o processamento ser concluído.');
                        
                        // Mostrar card de informações se estiver oculto
                        if (infoConsultaCard && infoConsultaCard.parentElement) {
                            const infoCard = infoConsultaCard.closest('.bg-white.rounded-xl');
                            if (infoCard) {
                                infoCard.classList.remove('hidden');
                            }
                        }
                        
                        // Conectar ao SSE para receber notificações quando o n8n enviar os dados
                        connectSSE();
                    }
                }
            } else if (contentType && contentType.includes('text/csv')) {
                const blob = await response.blob();

                if (!response.ok) {
                    // Verifica se o blob é HTML (página de erro)
                    const text = await blob.text();
                    if (text.includes('<!DOCTYPE html>') || text.includes('<html')) {
                        if (text.includes('Page Expired') || response.status === 419) {
                            throw new Error('Sua sessão expirou. Por favor, recarregue a página e tente novamente.');
                        } else if (response.status === 500) {
                            throw new Error('Erro interno do servidor. Por favor, tente novamente mais tarde.');
                        } else if (response.status === 504 || text.includes('504') || text.includes('Gateway Timeout') || text.includes('Gateway Time-out')) {
                            throw new Error('O processamento está demorando mais que o esperado. O SPED pode estar sendo processado em segundo plano. Aguarde alguns minutos e verifique novamente.');
                        } else {
                            throw new Error(`Erro ${response.status}: ${response.statusText}. Por favor, tente novamente.`);
                        }
                    }
                    throw new Error(text || 'Falha ao processar o SPED.');
                }

                // tenta obter filename do header
                const disposition = response.headers.get('content-disposition');
                let filename = 'resultado.csv';
                const match = disposition && disposition.match(/filename=\"?([^\";]+)\"?/i);
                if (match && match[1]) {
                    filename = match[1];
                }

                setDownload(blob, filename);
                hasDownloadSuccess = true;
                freezeTimer();
                showAlert('success', 'Processado com sucesso. O CSV está pronto para download.');
                
                // Parar processamento quando CSV estiver disponível
                setProcessing(false);

                // TODO: Desconectar recebimento de dados (SSE/WebSocket) quando CSV for recebido
                // disconnectDataReceiver();

                // Reset parcial após sucesso
                form.reset();
                // Resetar radio buttons do tipo de SPED para o primeiro (efd-contrib)
                if (tipoRadios.length > 0) {
                    tipoRadios[0].checked = true;
                }
                updateTipoSpedLabels();
                updateFileUi();
                updateEnablement();
                return;
            } else {
                // Verifica se é uma resposta HTML (página de erro)
                const text = await response.text();
                
                // Detecta erros comuns do Laravel
                if (contentType && contentType.includes('text/html')) {
                    if (text.includes('Page Expired') || response.status === 419) {
                        throw new Error('Sua sessão expirou. Por favor, recarregue a página e tente novamente.');
                    } else if (text.includes('419') || text.includes('CSRF')) {
                        throw new Error('Token de segurança expirado. Por favor, recarregue a página e tente novamente.');
                    } else if (response.status === 500) {
                        throw new Error('Erro interno do servidor. Por favor, tente novamente mais tarde.');
                    } else if (response.status === 504) {
                        throw new Error('O processamento está demorando mais que o esperado. O SPED pode estar sendo processado em segundo plano. Aguarde alguns minutos e verifique novamente.');
                    } else if (response.status === 404) {
                        throw new Error('Página não encontrada. Verifique se a URL está correta.');
                    } else if (!response.ok) {
                        throw new Error(`Erro ${response.status}: ${response.statusText}. Por favor, tente novamente.`);
                    }
                }
                
                // Se não for HTML mas também não for JSON/CSV, mostra erro genérico
                throw new Error('O servidor retornou uma resposta inesperada. Por favor, tente novamente.');
            }

            // Se chegou aqui e data existe, verifica se precisa de confirmação (caso não tenha sido detectado antes)
            if (data && data.success && data.needs_confirmation) {
                return; // Já foi tratado acima, mas garante que não continua
            }

            if (!response.ok || !data || !data.success) {
                throw new Error(data?.message || 'Falha ao processar o SPED.');
            }

            showAlert('success', 'Processado com sucesso.');

            if (data.csv) {
                const blob = new Blob([data.csv], { type: 'text/csv;charset=utf-8;' });
                const filename = data.filename && data.filename.trim() !== '' 
                    ? data.filename 
                    : 'resultado.csv';
                setDownload(blob, filename);
                hasDownloadSuccess = true;
                freezeTimer();
                
                // Parar processamento quando CSV estiver disponível
                setProcessing(false);
                
                // TODO: Desconectar recebimento de dados (SSE/WebSocket) quando CSV for recebido
                // disconnectDataReceiver();
            } else {
                resetDownload();
            }

            // Reset parcial após sucesso
            form.reset();
            // Resetar radio buttons do tipo de SPED para o primeiro (efd-contrib)
            if (tipoRadios.length > 0) {
                tipoRadios[0].checked = true;
            }
            updateTipoSpedLabels();
            updateFileUi();
            updateEnablement();
        } catch (err) {
            // Trata especificamente erros de timeout/gateway
            let errorMessage = err.message || 'Erro inesperado.';
            if (errorMessage.includes('504') || errorMessage.includes('Gateway Timeout') || errorMessage.includes('Gateway Time-out') || errorMessage.includes('timeout') || errorMessage.includes('demorando')) {
                errorMessage = 'O processamento está demorando mais que o esperado. O SPED pode estar sendo processado em segundo plano. Aguarde alguns minutos e verifique novamente.';
            }
            showAlert('error', errorMessage);
            resetDownload();
        } finally {
            setLoading(false);
            // Não parar o timer se o processamento assíncrono foi iniciado
            // O timer deve continuar até o CSV estar disponível via SSE
            if (!hasDownloadSuccess && !hasAsyncStarted) {
                stopTimer();
            }
        }
    });

    // Estado inicial
    updateTipoSpedLabels();
    updateTipoConsultaLabels();
    updateFileUi();
    updateEnablement();
    
    // Tentar restaurar estado salvo (se houver) - DEPOIS de inicializar tudo
    const stateRestored = restoreState();
    if (stateRestored) {
        console.log('[RAF] Estado anterior restaurado');
        // Atualizar habilitação novamente após restaurar estado
        updateEnablement();
    }
    
    // SSE será conectado apenas quando o usuário submeter um SPED ou confirmar créditos
    // Isso evita que o último relatório apareça ao recarregar a página
    // O SSE já é conectado nos momentos corretos:
    // - Após confirmar créditos (linhas 1424 e 1463)
    // - Após submeter SPED que retorna sem CSV imediato (linha 1908)
    
    // Desconectar SSE quando a página for fechada
    window.addEventListener('beforeunload', () => {
        disconnectSSE();
    });
    }

    // Expor para o SPA (resources/js/spa.js chama initRaf ao navegar)
    window.initRaf = initRaf;
    // Expor disconnectSSE para o SPA poder desconectar ao navegar
    // Usar a referência global armazenada
    window.disconnectSSE = function() {
        try {
            if (window._rafDisconnectSSE && typeof window._rafDisconnectSSE === 'function') {
                window._rafDisconnectSSE();
            }
        } catch (error) {
            console.warn('[RAF] Erro ao desconectar SSE:', error);
        }
    };

    // Também rodar na primeira carga (full reload)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRaf, { once: true });
    } else {
        initRaf();
    }

    // Atualizar badge de pendentes no botão do histórico
    async function updatePendentesBadge() {
        const badge = document.getElementById('raf-pendentes-badge');
        if (!badge) return;

        try {
            const response = await fetch('/app/raf/historico', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                credentials: 'same-origin',
            });

            if (response.ok) {
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Contar apenas os cards principais (divs com data-relatorio-id que são containers)
                // Os cards principais têm as classes bg-white rounded-xl e são divs diretos
                const cardsPrincipais = doc.querySelectorAll('div[data-relatorio-id].bg-white.rounded-xl');
                const totalPendentes = cardsPrincipais.length;

                if (totalPendentes > 0) {
                    badge.textContent = totalPendentes;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        } catch (err) {
            console.error('Erro ao atualizar badge de pendentes:', err);
        }
    }

    // Expor função globalmente para ser chamada de outras páginas
    window.updatePendentesBadge = updatePendentesBadge;

    // Atualizar badge ao carregar a página
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updatePendentesBadge, { once: true });
    } else {
        updatePendentesBadge();
    }
})();
</script>

