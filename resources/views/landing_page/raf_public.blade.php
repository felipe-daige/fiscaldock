<style>
    /* Estilos para landing page */
    .hero-gradient {
        background: linear-gradient(135deg, #0b1f3a 0%, #1e4fa0 50%, #133a73 100%);
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .feature-card {
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .section-fade-in {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease;
    }

    .section-fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }

    @media (max-width: 640px) {
        .hero-gradient h1 {
            font-size: 2rem;
        }
        
        .hero-gradient p {
            font-size: 1rem;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero-gradient py-12 md:py-20 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in-up">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 md:mb-6">
                RAF: Relatório de Auditoria de Fornecedores
            </h1>
            <p class="text-lg sm:text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-6 md:mb-8 px-4">
                Transforme dados brutos em análise consultiva
            </p>
        </div>
    </div>
</section>

<!-- Seção 2: Descrição Principal -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Transforme dados brutos em análise consultiva e garanta a saúde tributária de todos os seus clientes
            </h2>
            <p class="text-lg md:text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                O Rubi processa as informações coletadas (Notas Fiscais, SPEDs e Consultas na Receita Federal) para gerar o Relatório de Auditoria de Fornecedores (RAF). Este relatório consolidado oferece, em uma única página, a situação fiscal completa de cada CNPJ.
            </p>
        </div>
    </div>
</section>

<!-- Seção 3: Destaque Visual Principal - Frase de Poder -->
<section class="py-16 bg-gray-50 section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-8 md:p-12 text-center shadow-lg border-l-4 border-blue-400 transform hover:scale-[1.01] transition-transform duration-200">
            <p class="text-2xl md:text-3xl lg:text-4xl font-bold text-white italic mb-4">
                "Use o dado bruto para gerar consultoria."
            </p>
            <p class="text-blue-100 text-lg md:text-xl font-medium">
                Eleve seu escritório de operacional para consultivo
            </p>
        </div>
    </div>
</section>

<!-- Seção 4: Três Pontos de Poder de Consultoria -->
<section class="py-16 bg-white section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Os Três Pilares da Consultoria Fiscal</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Transforme-se de operador em consultor estratégico com dados reais e insights valiosos
            </p>
        </div>

        <div class="space-y-6">
            <!-- Pilar 1: Visão 360º -->
            <div class="feature-card flex flex-col md:flex-row items-start gap-6 p-6 bg-blue-50 rounded-lg border-2 border-blue-100 shadow-sm">
                <div class="flex-shrink-0 w-16 h-16 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Visão 360º</h3>
                    <p class="text-gray-700 leading-relaxed text-lg">
                        Visualize rapidamente o Regime Tributário, o status da CND e os principais dados de faturamento de cada cliente. Tenha uma visão consolidada e completa da situação fiscal de toda a sua carteira em um único lugar.
                    </p>
                </div>
            </div>
            
            <!-- Pilar 2: Identificação de Risco -->
            <div class="feature-card flex flex-col md:flex-row items-start gap-6 p-6 bg-yellow-50 rounded-lg border-2 border-yellow-100 shadow-sm">
                <div class="flex-shrink-0 w-16 h-16 bg-yellow-500 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Identificação de Risco</h3>
                    <p class="text-gray-700 leading-relaxed text-lg">
                        Identifique instantaneamente clientes com pendências fiscais que precisam de ação imediata ou cujo regime tributário pode não ser o mais vantajoso. Receba alertas automáticos e priorize suas ações com base em dados concretos.
                    </p>
                </div>
            </div>
            
            <!-- Pilar 3: Atendimento Personalizado -->
            <div class="feature-card flex flex-col md:flex-row items-start gap-6 p-6 bg-green-50 rounded-lg border-2 border-green-100 shadow-sm">
                <div class="flex-shrink-0 w-16 h-16 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Atendimento Personalizado</h3>
                    <p class="text-gray-700 leading-relaxed text-lg">
                        Use o RAF como ferramenta de vendas, mostrando ao seu cliente que você está monitorando a saúde dele com profundidade e tecnologia. Transforme-se de operador em consultor estratégico, oferecendo insights valiosos baseados em dados reais.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção 5: Mockup Visual do Relatório -->
<section class="py-16 bg-gray-50 section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Visualize a Situação Fiscal em Tempo Real</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Um relatório consolidado que mostra tudo o que você precisa saber sobre cada cliente
            </p>
        </div>

        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-lg p-6 md:p-8 shadow-xl">
            <div class="bg-white rounded-lg overflow-hidden border-2 border-blue-400">
                <!-- Header do mockup -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-sm font-bold text-white">Relatório RAF</span>
                    </div>
                    <span class="px-2 py-1 text-xs bg-white bg-opacity-25 text-white rounded font-semibold">Inteligência Fiscal</span>
                </div>
                
                <!-- Grid de duas colunas destacadas -->
                <div class="p-4 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- CNPJ X -->
                        <div class="bg-white rounded-lg p-3 border-2 border-green-400">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">CNPJ X</p>
                                        <p class="text-[10px] text-gray-500">12.345.678/0001-90</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 text-[10px] bg-green-500 text-white rounded font-bold">OK</span>
                            </div>
                            <div class="space-y-1 mt-2 pt-2 border-t border-gray-100">
                                <div>
                                    <p class="text-[10px] text-gray-500">Regime Tributário</p>
                                    <p class="text-xs font-bold text-gray-900">Lucro Real</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500">CND</p>
                                    <p class="text-xs font-bold text-green-600">Regular</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- CNPJ Y -->
                        <div class="bg-white rounded-lg p-3 border-2 border-yellow-400">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">CNPJ Y</p>
                                        <p class="text-[10px] text-gray-500">98.765.432/0001-10</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 text-[10px] bg-yellow-500 text-white rounded font-bold">Alerta</span>
                            </div>
                            <div class="space-y-1 mt-2 pt-2 border-t border-gray-100">
                                <div>
                                    <p class="text-[10px] text-gray-500">Regime Tributário</p>
                                    <p class="text-xs font-bold text-gray-900">Simples Nacional</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500">CND</p>
                                    <p class="text-xs font-bold text-yellow-600">Pendência Detectada</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção 6: Funcionalidades Técnicas -->
<section class="py-16 bg-white section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Como Funciona</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Um processo automatizado que transforma dados brutos em insights consultivos valiosos
            </p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-8 md:p-12 max-w-4xl mx-auto">
            <ul class="space-y-4 text-gray-700">
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Processamento automático de Notas Fiscais, SPEDs e consultas na Receita Federal</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Geração automática do relatório consolidado por CNPJ</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Visualização em uma única página com todos os dados relevantes</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Alertas automáticos de pendências e riscos fiscais</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Análise comparativa de regimes tributários</span>
                </li>
            </ul>
        </div>
    </div>
</section>

<!-- Seção 7: Upload do SPED e Visualização do CSV -->
<section class="py-16 bg-gray-50 section-fade-in" id="raf-upload">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-10 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Envie o EFD Contribuições e veja o CSV</h2>
            <p class="text-lg text-gray-600">O arquivo é enviado para a inteligência fiscal e o CSV retornado é exibido e disponível para download.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <form id="raf-upload-form" class="space-y-5">
                    <div class="space-y-2">
                        <label for="raf-tipo" class="block text-sm font-semibold text-gray-800">Tipo de SPED</label>
                        <select id="raf-tipo" name="tipo" class="w-full rounded-lg border border-gray-300 bg-white text-gray-800 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="EFD Contribuições" selected>EFD Contribuições</option>
                            <option value="EFD Fiscal">EFD Fiscal</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-800">Arquivo (.txt)</label>
                        <div class="sr-only" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0;">
                            <input type="file" id="raf-sped" name="sped" accept=".txt,text/plain" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0; opacity: 0;">
                        </div>
                        <div id="raf-dropzone" class="border-2 border-dashed border-gray-300 rounded-lg px-4 py-6 text-center cursor-pointer bg-white hover:border-blue-400 hover:bg-blue-50/40 transition">
                            <p class="text-sm font-semibold text-gray-900 truncate w-full px-2 min-w-0" id="raf-dropzone-title" title="" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Arraste o arquivo ou clique para selecionar</p>
                            <p class="text-xs text-gray-500" id="raf-dropzone-subtitle">Máximo 10 MB - somente .txt</p>
                        </div>
                        <div id="raf-file-meta" class="hidden rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                            <p class="text-sm font-semibold text-gray-900 truncate" id="raf-file-name" title="" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></p>
                            <p class="text-xs text-gray-500" id="raf-file-size"></p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <button type="submit" id="raf-submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 text-white font-semibold px-4 py-2.5 shadow-sm hover:bg-blue-700 transition disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg id="raf-submit-spinner" class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span id="raf-submit-label">Enviar SPED</span>
                        </button>
                        <p class="text-xs text-gray-500 text-center" id="raf-status-hint">O CSV será exibido abaixo quando o processamento terminar.</p>
                    </div>

                    <div id="raf-alert" class="hidden rounded-lg border px-3 py-2 text-sm"></div>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-6">
                {{-- Card de informações do processamento --}}
                <div id="raf-info-card" class="hidden bg-blue-50 rounded-xl border border-blue-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-semibold text-gray-900">Informações do Processamento</h4>
                        <button
                            type="button"
                            id="raf-cancel-process-btn"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-red-300 bg-white text-red-700 text-sm font-semibold shadow-sm transition hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span id="raf-cancel-process-text">Cancelar</span>
                            <svg id="raf-cancel-process-spinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-white rounded-lg p-4 border border-blue-100">
                            <p class="text-xs text-gray-600 mb-1">CNPJs encontrados</p>
                            <p id="raf-info-cnpjs" class="text-2xl font-bold text-gray-900">--</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-blue-100">
                            <p class="text-xs text-gray-600 mb-1">Valor total</p>
                            <p id="raf-info-valor" class="text-2xl font-bold text-gray-900">--</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-blue-100">
                            <p class="text-xs text-gray-600 mb-1">Custo unitário</p>
                            <p id="raf-info-custo" class="text-2xl font-bold text-gray-900">--</p>
                        </div>
                    </div>
                </div>

                {{-- Área de resultados do CSV --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Resultado do CSV</h3>
                        <p class="text-sm text-gray-600">Será preenchido após o processamento.</p>
                    </div>
                    <div id="raf-download-wrap" class="hidden">
                        <a id="raf-download-link" class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition" href="#" download="resultado.csv">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                            </svg>
                            <span id="raf-download-label">Baixar CSV</span>
                        </a>
                    </div>
                </div>

                    <div id="raf-result-empty" class="text-sm text-gray-600">Nenhum resultado ainda.</div>
                    <div id="raf-table-container" class="hidden overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50" id="raf-thead"></thead>
                            <tbody class="divide-y divide-gray-200" id="raf-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção 8: Call to Action -->
<section class="hero-gradient py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
            Pronto para transformar dados em consultoria?
        </h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
            Agende uma demonstração e veja como o RAF pode elevar seu escritório 
            de operacional para consultivo.
        </p>
        <a href="/agendar" data-link class="inline-block bg-white text-blue-600 font-bold px-8 py-4 rounded-lg hover:bg-gray-100 transition-colors shadow-lg text-lg">
            Agendar Demonstração
        </a>
    </div>
</section>

<script>
    // Setup scroll animations
    function setupScrollAnimations() {
        const sections = document.querySelectorAll('.section-fade-in');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        sections.forEach(section => {
            observer.observe(section);
        });
    }

    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupScrollAnimations);
    } else {
        setupScrollAnimations();
    }

    // Upload RAF (público)
    function initRafUpload() {
        const form = document.getElementById('raf-upload-form');
        if (!form || form.dataset.initRafUpload === '1') return;
        form.dataset.initRafUpload = '1';

        const tipoSelect = document.getElementById('raf-tipo');
        const fileInput = document.getElementById('raf-sped');
        const dropzone = document.getElementById('raf-dropzone');
        const dropzoneTitle = document.getElementById('raf-dropzone-title');
        const dropzoneSubtitle = document.getElementById('raf-dropzone-subtitle');
        const fileMeta = document.getElementById('raf-file-meta');
        const fileNameEl = document.getElementById('raf-file-name');
        const fileSizeEl = document.getElementById('raf-file-size');
        const submitBtn = document.getElementById('raf-submit');
        const submitLabel = document.getElementById('raf-submit-label');
        const submitSpinner = document.getElementById('raf-submit-spinner');
        const alertEl = document.getElementById('raf-alert');
        const statusHint = document.getElementById('raf-status-hint');
        const resultEmpty = document.getElementById('raf-result-empty');
        const tableContainer = document.getElementById('raf-table-container');
        const theadEl = document.getElementById('raf-thead');
        const tbodyEl = document.getElementById('raf-tbody');
        const downloadWrap = document.getElementById('raf-download-wrap');
        const downloadLink = document.getElementById('raf-download-link');
        const downloadLabel = document.getElementById('raf-download-label');
        const infoCard = document.getElementById('raf-info-card');
        const infoCnpjs = document.getElementById('raf-info-cnpjs');
        const infoValor = document.getElementById('raf-info-valor');
        const infoCusto = document.getElementById('raf-info-custo');
        const cancelProcessBtn = document.getElementById('raf-cancel-process-btn');
        const cancelProcessText = document.getElementById('raf-cancel-process-text');
        const cancelProcessSpinner = document.getElementById('raf-cancel-process-spinner');
        const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content');

        let currentDownloadUrl = null;
        let isLoading = false;
        let pollingInterval = null;
        let currentResumeUrl = null;

        const formatFileSize = (bytes) => {
            if (!Number.isFinite(bytes)) return '';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return `${Math.round((bytes / Math.pow(k, i)) * 10) / 10} ${sizes[i]}`;
        };

        const showAlert = (type, message) => {
            if (!alertEl) return;
            const base = 'rounded-lg border px-3 py-2 text-sm';
            const map = {
                info: 'border-gray-200 bg-gray-50 text-gray-800',
                success: 'border-green-200 bg-green-50 text-green-800',
                error: 'border-red-200 bg-red-50 text-red-800',
            };
            alertEl.className = `${base} ${map[type] || map.info}`;
            alertEl.textContent = message || '';
            alertEl.classList.toggle('hidden', !message);
        };

        const setDownload = (csvString, filename = 'resultado.csv') => {
            if (!downloadWrap || !downloadLink || !downloadLabel) return;
            if (currentDownloadUrl) {
                URL.revokeObjectURL(currentDownloadUrl);
            }
            const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            currentDownloadUrl = URL.createObjectURL(blob);
            downloadLink.href = currentDownloadUrl;
            downloadLink.download = filename;
            downloadLabel.textContent = `Baixar ${filename}`;
            downloadWrap.classList.remove('hidden');
        };

        const clearDownload = () => {
            if (currentDownloadUrl) {
                URL.revokeObjectURL(currentDownloadUrl);
                currentDownloadUrl = null;
            }
            downloadWrap?.classList.add('hidden');
        };

        const stopPolling = () => {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
            currentResumeUrl = null;
        };

        const cancelProcess = async () => {
            if (!currentResumeUrl) {
                // Se não há resume_url, apenas parar polling e esconder card
                stopPolling();
                if (infoCard) {
                    infoCard.classList.add('hidden');
                }
                showAlert('info', 'Processamento cancelado.');
                return;
            }

            if (!confirm('Tem certeza que deseja cancelar o processamento?')) {
                return;
            }

            if (cancelProcessBtn) {
                cancelProcessBtn.disabled = true;
                if (cancelProcessText) cancelProcessText.classList.add('hidden');
                if (cancelProcessSpinner) cancelProcessSpinner.classList.remove('hidden');
            }

            try {
                const response = await fetch('/raf/cancel-public', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        resume_url: currentResumeUrl,
                    }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showAlert('info', 'Processamento cancelado com sucesso.');
                } else {
                    // Mesmo se houver erro, parar polling e esconder card
                    showAlert('info', 'Processamento cancelado.');
                }
            } catch (err) {
                console.error('[RAF Public] Erro ao cancelar:', err);
                // Mesmo se houver erro, parar polling e esconder card
                showAlert('info', 'Processamento cancelado.');
            } finally {
                // Sempre parar polling e esconder card
                stopPolling();
                if (infoCard) {
                    infoCard.classList.add('hidden');
                }
                if (cancelProcessBtn) {
                    cancelProcessBtn.disabled = false;
                    if (cancelProcessText) cancelProcessText.classList.remove('hidden');
                    if (cancelProcessSpinner) cancelProcessSpinner.classList.add('hidden');
                }
                // Limpar estado
                clearDownload();
                tableContainer.classList.add('hidden');
                resultEmpty.classList.remove('hidden');
            }
        };

        const startPolling = (resumeUrl) => {
            stopPolling();
            
            if (!resumeUrl) return;
            
            currentResumeUrl = resumeUrl;
            console.log('[RAF Public] Iniciando polling para:', resumeUrl);
            
            // Mostrar card de informações
            if (infoCard) {
                infoCard.classList.remove('hidden');
            }
            
            // Função que busca dados atualizados
            const fetchUpdatedData = async () => {
                try {
                    const encodedUrl = encodeURIComponent(resumeUrl);
                    const response = await fetch(`/api/data/receive-public/${encodedUrl}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });
                    
                    if (response.status === 404) {
                        // Dados ainda não recebidos, continua tentando
                        console.log('[RAF Public] Dados ainda não disponíveis no cache');
                        return;
                    }
                    
                    if (!response.ok) {
                        console.warn('[RAF Public] Erro ao buscar dados atualizados:', response.status);
                        return;
                    }
                    
                    const data = await response.json();
                    
                    if (data.success && data.data) {
                        const updatedData = data.data;
                        console.log('[RAF Public] Dados atualizados recebidos:', updatedData);
                        
                        // Atualizar valores no card
                        const qtdParticipantes = updatedData.qtd_participantes_unicos ?? updatedData.qnt_participantes ?? 0;
                        const valorTotal = updatedData.valor_total_consulta ?? 0;
                        const custoUnitario = updatedData.custo_unitario ?? 0;
                        
                        if (infoCnpjs) {
                            infoCnpjs.textContent = qtdParticipantes.toString();
                        }
                        
                        if (infoValor) {
                            infoValor.textContent = `R$ ${valorTotal.toFixed(2)}`;
                        }
                        
                        if (infoCusto) {
                            infoCusto.textContent = `R$ ${custoUnitario.toFixed(2)}`;
                        }
                    }
                } catch (err) {
                    console.error('[RAF Public] Erro no polling:', err);
                }
            };
            
            // Buscar imediatamente
            fetchUpdatedData();
            
            // Configurar polling a cada 4 segundos
            pollingInterval = setInterval(fetchUpdatedData, 4000);
        };

        const renderTable = (headers, rows) => {
            if (!headers?.length || !rows?.length) {
                tableContainer.classList.add('hidden');
                resultEmpty.classList.remove('hidden');
                return;
            }

            theadEl.innerHTML = `
                <tr>
                    ${headers.map(h => `<th class=\"px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 whitespace-nowrap\">${h}</th>`).join('')}
                </tr>
            `;

            tbodyEl.innerHTML = rows.map(row => `
                <tr class=\"odd:bg-white even:bg-gray-50/50\">
                    ${row.map(cell => `<td class=\"px-4 py-3 text-gray-700 whitespace-normal break-words align-top\">${cell ?? ''}</td>`).join('')}
                </tr>
            `).join('');

            resultEmpty.classList.add('hidden');
            tableContainer.classList.remove('hidden');
        };

        const updateFileUi = () => {
            const file = fileInput.files?.[0];
            if (!file) {
                dropzoneTitle.textContent = 'Arraste o arquivo ou clique para selecionar';
                dropzoneTitle.removeAttribute('title');
                fileMeta.classList.add('hidden');
                fileNameEl.textContent = '';
                fileNameEl.removeAttribute('title');
                fileSizeEl.textContent = '';
                submitBtn.disabled = true;
                return;
            }

            const fileName = file.name;
            dropzoneTitle.textContent = fileName;
            dropzoneTitle.setAttribute('title', fileName);
            fileMeta.classList.remove('hidden');
            fileNameEl.textContent = fileName;
            fileNameEl.setAttribute('title', fileName);
            fileSizeEl.textContent = `${formatFileSize(file.size)} • ${file.type || 'text/plain'}`;
            submitBtn.disabled = false;
        };

        const setLoading = (loading) => {
            isLoading = !!loading;
            submitLabel.textContent = isLoading ? 'Enviando...' : 'Enviar SPED';
            submitSpinner.classList.toggle('hidden', !isLoading);
            submitBtn.disabled = isLoading || !fileInput.files?.length;
            tipoSelect.disabled = isLoading;
            if (statusHint) statusHint.textContent = isLoading ? 'Processando arquivo...' : 'O CSV será exibido abaixo quando o processamento terminar.';
        };

        const openPicker = () => {
            if (isLoading) return;
            fileInput.click();
        };

        if (dropzone) {
            dropzone.addEventListener('click', openPicker);
            dropzone.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    openPicker();
                }
            });

            const setDragOver = (on) => {
                if (!dropzone) return;
                if (on) {
                    dropzone.classList.add('border-blue-500', 'bg-blue-50/60');
                } else {
                    dropzone.classList.remove('border-blue-500', 'bg-blue-50/60');
                }
            };

            dropzone.addEventListener('dragenter', (e) => { e.preventDefault(); if (!isLoading) setDragOver(true); });
            dropzone.addEventListener('dragover', (e) => { e.preventDefault(); if (!isLoading) setDragOver(true); });
            dropzone.addEventListener('dragleave', () => setDragOver(false));
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                setDragOver(false);
                if (isLoading) return;
                const file = e.dataTransfer?.files?.[0];
                if (!file) return;
                const isTxt = file.name?.toLowerCase().endsWith('.txt') || file.type === 'text/plain';
                if (!isTxt) {
                    showAlert('error', 'Selecione um arquivo .txt');
                    return;
                }
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        fileInput.addEventListener('change', updateFileUi);

        // Event listener para botão de cancelar processamento
        if (cancelProcessBtn) {
            cancelProcessBtn.addEventListener('click', cancelProcess);
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const file = fileInput.files?.[0];
            const tipo = tipoSelect.value || 'EFD Contribuições';

            if (!file) {
                showAlert('error', 'Selecione um arquivo .txt');
                return;
            }

            showAlert('info', 'Enviando arquivo...');
            stopPolling();
            if (infoCard) {
                infoCard.classList.add('hidden');
            }
            clearDownload();
            tableContainer.classList.add('hidden');
            resultEmpty.classList.remove('hidden');
            setLoading(true);

            const formData = new FormData();
            formData.append('tipo', tipo);
            formData.append('sped', file);

            try {
                const response = await fetch('/raf/upload-public', {
                    method: 'POST',
                    headers: {
                        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                    },
                    body: formData,
                }).catch((fetchError) => {
                    // Trata erros de rede/timeout do fetch
                    if (fetchError.name === 'TypeError' && fetchError.message.includes('fetch')) {
                        throw new Error('Erro de conexão. O processamento pode estar demorando mais que o esperado. Aguarde alguns minutos e verifique novamente.');
                    }
                    throw fetchError;
                });

                // Trata erro 504 antes de fazer parse
                if (response.status === 504) {
                    throw new Error('O processamento está demorando mais que o esperado. O SPED pode estar sendo processado em segundo plano. Aguarde alguns minutos e verifique novamente.');
                }

                const contentType = response.headers.get('content-type') || '';
                const isJson = contentType.includes('application/json');
                let data;
                
                try {
                    data = isJson ? await response.json() : { success: false, message: await response.text() };
                } catch (parseError) {
                    // Se não conseguir fazer parse e for 504, já tratamos acima
                    if (response.status === 504) {
                        throw new Error('O processamento está demorando mais que o esperado. O SPED pode estar sendo processado em segundo plano. Aguarde alguns minutos e verifique novamente.');
                    }
                    throw new Error('Erro ao processar resposta do servidor. Por favor, tente novamente.');
                }

                if (!response.ok || !data.success) {
                    // Verifica se a mensagem contém referência a timeout/gateway
                    const errorMsg = data.message || 'Falha ao processar o SPED.';
                    if (errorMsg.includes('504') || errorMsg.includes('Gateway Timeout') || errorMsg.includes('Gateway Time-out') || errorMsg.includes('timeout')) {
                        throw new Error('O processamento está demorando mais que o esperado. O SPED pode estar sendo processado em segundo plano. Aguarde alguns minutos e verifique novamente.');
                    }
                    throw new Error(errorMsg);
                }

                // Verifica se precisa de confirmação e tem resume_url para polling
                if (data.needs_confirmation && data.resume_url) {
                    console.log('[RAF Public] Processamento em andamento, iniciando polling');
                    showAlert('info', 'Processamento em andamento. Aguarde...');
                    
                    // Iniciar polling para buscar dados atualizados
                    startPolling(data.resume_url);
                    
                    // Não renderizar tabela ainda, aguardar CSV final
                    return;
                }

                // Se chegou aqui, tem CSV pronto
                stopPolling();
                if (infoCard) {
                    infoCard.classList.add('hidden');
                }
                
                renderTable(data.headers || [], data.rows || []);
                setDownload(data.csv || '', data.filename || 'resultado.csv');
                showAlert('success', 'Processado com sucesso. CSV disponível.');
                form.reset();
                updateFileUi();
            } catch (err) {
                // Trata especificamente erros de timeout/gateway
                let errorMessage = err.message || 'Erro inesperado.';
                if (errorMessage.includes('504') || errorMessage.includes('Gateway Timeout') || errorMessage.includes('Gateway Time-out') || errorMessage.includes('timeout') || errorMessage.includes('demorando')) {
                    errorMessage = 'O processamento está demorando mais que o esperado. O SPED pode estar sendo processado em segundo plano. Aguarde alguns minutos e verifique novamente.';
                }
                showAlert('error', errorMessage);
                stopPolling();
                if (infoCard) {
                    infoCard.classList.add('hidden');
                }
                clearDownload();
                tableContainer.classList.add('hidden');
                resultEmpty.classList.remove('hidden');
            } finally {
                setLoading(false);
            }
        });

        // Estado inicial
        updateFileUi();
        clearDownload();
        tableContainer.classList.add('hidden');
        resultEmpty.classList.remove('hidden');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRafUpload);
    } else {
        initRafUpload();
    }
</script>
