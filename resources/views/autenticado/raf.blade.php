{{-- RAF - Relatório de Auditoria de Fornecedores --}}
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page title (o layout já tem header global) --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Auditoria de Fornecedores</h1>
            <p class="mt-1 text-sm text-gray-600">Analise seus fornecedores a partir do SPED e obtenha um relatório completo de regime tributário e situação fiscal.</p>
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
            <div class="bg-white rounded-xl border border-gray-200 shadow-md">
                <div class="p-6 space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Processar SPED</h2>
                        <p class="mt-1 text-sm text-gray-600">1) Escolha o tipo de SPED. 2) Escolha o tipo de consulta. 3) Selecione/arraste o arquivo .txt. 4) Envie.</p>
                    </div>

                    <form id="sped-form" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                            {{-- Coluna Esquerda: Tipo de SPED + Arquivo SPED --}}
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label for="tipo" class="block text-sm font-semibold text-gray-800">Tipo de SPED</label>
                                    <select 
                                        id="tipo" 
                                        name="tipo" 
                                        class="w-full rounded-lg border border-gray-300 bg-white text-gray-800 shadow-sm transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 focus:ring-offset-gray-50 disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed"
                                    >
                                        <option value="" selected disabled>Selecione o tipo</option>
                                        <option value="EFD Contribuições">EFD Contribuições</option>
                                        <option value="EFD Fiscal">EFD Fiscal</option>
                                    </select>
                                    <p id="tipo-hint" class="text-xs text-gray-500">Selecione o tipo de SPED e a consulta para liberar o upload.</p>
                                </div>

                                <div class="space-y-2">
                                    <label for="sped" class="block text-sm font-semibold text-gray-800">Arquivo SPED (.txt)</label>

                                    <div class="sr-only" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0;">
                                        <input
                                            type="file"
                                            id="sped"
                                            name="sped"
                                            accept=".txt,text/plain"
                                            style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0; opacity: 0;"
                                            disabled
                                        >
                                    </div>

                                    <div
                                        id="sped-dropzone"
                                        class="w-full rounded-xl border-2 border-dashed px-4 py-6 text-center transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                                        role="button"
                                        tabindex="0"
                                        aria-disabled="true"
                                        aria-describedby="sped-file-help"
                                    >
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="h-7 w-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            <div class="space-y-0.5 w-full px-2 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 truncate max-w-full" id="sped-dropzone-title" title="" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Nenhum arquivo selecionado</p>
                                                <p class="text-xs text-gray-500" id="sped-dropzone-subtitle">Selecione o tipo de SPED e o tipo de consulta para liberar o upload.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="sped-file-meta" class="hidden rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0 flex-1 overflow-hidden">
                                                <p class="text-sm font-semibold text-gray-900 truncate" id="sped-file-name" title="" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></p>
                                                <p class="text-xs text-gray-500" id="sped-file-size"></p>
                                            </div>
                                            <button 
                                                type="button" 
                                                id="sped-change-file" 
                                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 transition hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 active:bg-gray-100 px-3 py-1.5 text-sm flex-shrink-0"
                                            >
                                                Trocar arquivo
                                            </button>
                                        </div>
                                    </div>

                                    <p id="sped-file-help" class="text-xs text-gray-500">Máximo 10 MB.</p>
                                </div>
                            </div>

                            {{-- Coluna Direita: Tipo de consulta --}}
                            <div class="space-y-3">
                                <span class="block text-sm font-semibold text-gray-800">Tipo de consulta</span>
                                <div class="space-y-2" role="radiogroup" aria-labelledby="raf-modalidade-label">
                                    <div class="flex items-start gap-3">
                                        <input 
                                            id="modalidade-regime" 
                                            name="modalidade" 
                                            type="radio" 
                                            value="regime"
                                            class="mt-1 h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-500"
                                        >
                                        <label for="modalidade-regime" class="flex-1">
                                            <span class="block text-sm font-semibold text-gray-900">Gratuita — Regime Tributário</span>
                                            <span class="block text-xs text-gray-600">Consulta apenas o regime tributário (Simples, Presumido ou Real).</span>
                                        </label>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <input 
                                            id="modalidade-completa" 
                                            name="modalidade" 
                                            type="radio" 
                                            value="completa"
                                            class="mt-1 h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-500"
                                        >
                                        <label for="modalidade-completa" class="flex-1">
                                            <span class="block text-sm font-semibold text-gray-900">Completa — Regime + CND (Receita Federal)</span>
                                            <span class="block text-xs text-gray-600">Consulta o regime tributário e a Certidão de Regularidade Fiscal (CND).</span>
                                        </label>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">Selecione a modalidade antes de enviar.</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    type="submit"
                                    class="btn-primary-solid inline-flex flex-row items-center justify-center gap-2 px-5 py-2.5 font-semibold shadow-sm disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:bg-primary-500"
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

                            <div id="sped-alert" class="hidden rounded-xl border border-gray-200 bg-white text-gray-700 px-4 py-3 text-sm" role="status" aria-live="polite">
                                <div class="flex gap-2">
                                    <span id="sped-alert-icon" class="mt-0.5">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                                        </svg>
                                    </span>
                                    <p id="sped-alert-text" class="min-w-0"></p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Resultado simplificado: apenas download --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-md">
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
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                </svg>
                                <span id="csv-download-label">Baixar CSV</span>
                            </a>
                        </div>
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
                                            Para cada CNPJ, consultamos em tempo real o Regime Tributário (Simples, Presumido ou Real) e a Situação Fiscal (CND - Certidão Negativa de Débitos).
                                        </p>
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
                        <div class="pt-2">
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


<script>
(() => {
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
    if (form.dataset.rafInitialized === '1') return;
    form.dataset.rafInitialized = '1';

    const submitBtn = document.getElementById('sped-submit');
    const resultBadge = document.getElementById('result-badge');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const tipoSelect = document.getElementById('tipo');
    const fileInput = document.getElementById('sped');
    const modalidadeRadios = document.querySelectorAll('input[name="modalidade"]');
    const submitLabel = document.getElementById('sped-submit-label');
    const submitSpinner = document.getElementById('sped-submit-spinner');
    const submitIcon = document.getElementById('sped-submit-icon');

    const dropzone = document.getElementById('sped-dropzone');
    const dropzoneTitle = document.getElementById('sped-dropzone-title');
    const dropzoneSubtitle = document.getElementById('sped-dropzone-subtitle');
    const fileMeta = document.getElementById('sped-file-meta');
    const fileNameEl = document.getElementById('sped-file-name');
    const fileSizeEl = document.getElementById('sped-file-size');
    const changeFileBtn = document.getElementById('sped-change-file');

    const alertEl = document.getElementById('sped-alert');
    const alertTextEl = document.getElementById('sped-alert-text');
    const alertIconWrap = document.getElementById('sped-alert-icon');

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

    let isLoading = false;
    let timerInterval = null;
    let timerStart = 0;
    let currentDownloadUrl = null;

    const formatFileSize = (bytes) => {
        if (!Number.isFinite(bytes)) return '';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${Math.round((bytes / Math.pow(k, i)) * 10) / 10} ${sizes[i]}`;
    };

    const setTimerState = (state) => {
        if (!timerWrap) return;
        const wasHidden = timerWrap.classList.contains('hidden');
        timerWrap.className = `${timerBaseClasses} ${timerStateClasses[state] || timerStateClasses.default}`;
        if (wasHidden) timerWrap.classList.add('hidden');
    };

    const showAlert = (type, message) => {
        if (!alertEl || !alertTextEl) return;

        // Classes base do alert
        const baseClasses = 'rounded-xl border px-4 py-3 text-sm';
        
        // Classes específicas por tipo
        const typeClasses = {
            info: 'border-gray-200 bg-white text-gray-700',
            success: 'border-green-200 bg-green-50 text-green-800',
            error: 'border-red-200 bg-red-50 text-red-800',
        };

        alertEl.className = `${baseClasses} ${typeClasses[type] || typeClasses.info}`;
        alertTextEl.textContent = message || '';
        alertEl.classList.toggle('hidden', !message);

        if (alertIconWrap) {
            if (type === 'success') {
                alertIconWrap.innerHTML = `
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                `;
            } else if (type === 'error') {
                alertIconWrap.innerHTML = `
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                `;
            } else {
                alertIconWrap.innerHTML = `
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                    </svg>
                `;
            }
        }
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
    };

    const setDownload = (blob, filename = 'resultado.csv') => {
        if (!downloadWrap || !downloadLink || !downloadLabel) return;
        resetDownload();
        currentDownloadUrl = URL.createObjectURL(blob);
        downloadLink.href = currentDownloadUrl;
        downloadLink.download = filename;
        downloadLabel.textContent = `Baixar ${filename}`;
        downloadWrap.classList.remove('hidden');
    };

    const setDropzoneEnabled = (enabled, hasTipo = false, hasModalidade = false) => {
        if (!dropzone) return;
        
        // Classes base da dropzone
        const baseClasses = 'w-full rounded-xl border-2 border-dashed px-4 py-6 text-center transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2';
        
        if (enabled) {
            // Dropzone habilitada
            dropzone.className = `${baseClasses} border-gray-300 bg-white hover:border-primary-400 hover:bg-primary-50/30 cursor-pointer`;
            dropzone.setAttribute('aria-disabled', 'false');
            dropzoneSubtitle.textContent = 'Arraste e solte aqui, ou clique para selecionar.';
        } else {
            // Dropzone desabilitada
            dropzone.className = `${baseClasses} border-gray-300 bg-gray-100 pointer-events-none cursor-not-allowed opacity-60`;
            dropzone.setAttribute('aria-disabled', 'true');
            
            // Mensagem específica baseada no que está faltando
            if (!hasTipo && !hasModalidade) {
                dropzoneSubtitle.textContent = 'Selecione o tipo de SPED e o tipo de consulta para liberar o upload.';
            } else if (!hasTipo) {
                dropzoneSubtitle.textContent = 'Selecione o tipo de SPED para liberar o upload.';
            } else if (!hasModalidade) {
                dropzoneSubtitle.textContent = 'Selecione o tipo de consulta para liberar o upload.';
            }
        }
    };

    const updateFileUi = () => {
        const file = fileInput.files?.[0];
        if (!file) {
            dropzoneTitle.textContent = 'Nenhum arquivo selecionado';
            dropzoneTitle.removeAttribute('title');
            fileMeta.classList.add('hidden');
            fileNameEl.textContent = '';
            fileNameEl.removeAttribute('title');
            fileSizeEl.textContent = '';
            return;
        }

        const fileName = file.name;
        dropzoneTitle.textContent = fileName;
        dropzoneTitle.setAttribute('title', fileName);
        fileMeta.classList.remove('hidden');
        fileNameEl.textContent = fileName;
        fileNameEl.setAttribute('title', fileName);
        fileSizeEl.textContent = `${formatFileSize(file.size)} • ${file.type || 'text/plain'}`;
    };

    const getSelectedModalidade = () => {
        const checked = Array.from(modalidadeRadios).find(r => r.checked);
        return checked?.value || '';
    };

    const updateEnablement = () => {
        const hasTipo = tipoSelect.value !== '';
        const modalidade = getSelectedModalidade();
        const hasModalidade = modalidade !== '';
        const fileEnabled = hasTipo && hasModalidade && !isLoading;
        fileInput.disabled = !fileEnabled;
        setDropzoneEnabled(fileEnabled, hasTipo, hasModalidade);
        const hasFile = fileInput.files?.length > 0;
        submitBtn.disabled = !(hasTipo && hasFile && hasModalidade) || isLoading;
    };

    tipoSelect.addEventListener('change', updateEnablement);
    modalidadeRadios.forEach(radio => {
        radio.addEventListener('change', updateEnablement);
    });
    fileInput.addEventListener('change', () => {
        updateFileUi();
        updateEnablement();
        if (fileInput.files?.length) {
            showAlert('info', '');
        }
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
                dropzone.classList.remove('border-gray-300', 'bg-white', 'hover:border-primary-400', 'hover:bg-primary-50/30');
                dropzone.classList.add('border-primary-500', 'bg-primary-50/50');
            } else {
                dropzone.classList.remove('border-primary-500', 'bg-primary-50/50');
                dropzone.classList.add('border-gray-300', 'bg-white', 'hover:border-primary-400', 'hover:bg-primary-50/30');
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
                showAlert('error', 'Selecione um arquivo .txt');
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

    const setLoading = (loading, message = '') => {
        isLoading = !!loading;
        submitLabel.textContent = isLoading ? 'Enviando...' : 'Enviar SPED';
        submitSpinner.classList.toggle('hidden', !isLoading);
        submitIcon.classList.toggle('hidden', isLoading);
        tipoSelect.disabled = isLoading;
        updateEnablement();

        if (message) {
            showAlert('info', message);
        }
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const tipo = tipoSelect.value;
        const file = fileInput.files[0];
        const modalidade = getSelectedModalidade();

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

        showAlert('info', 'Enviado corretamente e sendo processado');
        setLoading(true);
        stopTimer();
        resetDownload();
        startTimer();

        const formData = new FormData();
        formData.append('tipo', tipo);
        formData.append('modalidade', modalidade);
        formData.append('sped', file);

        let hasDownloadSuccess = false;

        try {
            // Obter token CSRF atualizado antes de cada requisição
            const currentCsrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || csrf || '';
            
            const response = await fetch('/app/solucoes/raf/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': currentCsrf
                },
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
                resultBadge.classList.remove('hidden');

                // Reset parcial após sucesso
                form.reset();
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

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Falha ao processar o SPED.');
            }

            showAlert('success', 'Processado com sucesso.');
            resultBadge.classList.remove('hidden');

            if (data.csv) {
                const blob = new Blob([data.csv], { type: 'text/csv;charset=utf-8;' });
                const filename = data.filename && data.filename.trim() !== '' 
                    ? data.filename 
                    : 'resultado.csv';
                setDownload(blob, filename);
                hasDownloadSuccess = true;
                freezeTimer();
            } else {
                resetDownload();
            }

            // Reset parcial após sucesso
            form.reset();
            updateFileUi();
            updateEnablement();
        } catch (err) {
            // Trata especificamente erros de timeout/gateway
            let errorMessage = err.message || 'Erro inesperado.';
            if (errorMessage.includes('504') || errorMessage.includes('Gateway Timeout') || errorMessage.includes('Gateway Time-out') || errorMessage.includes('timeout') || errorMessage.includes('demorando')) {
                errorMessage = 'O processamento está demorando mais que o esperado. O SPED pode estar sendo processado em segundo plano. Aguarde alguns minutos e verifique novamente.';
            }
            showAlert('error', errorMessage);
            resultBadge.classList.add('hidden');
            resetDownload();
        } finally {
            setLoading(false);
            if (!hasDownloadSuccess) {
                stopTimer();
            }
        }
    });

    // Estado inicial
    updateFileUi();
    updateEnablement();
    }

    // Expor para o SPA (resources/js/spa.js chama initRaf ao navegar)
    window.initRaf = initRaf;

    // Também rodar na primeira carga (full reload)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRaf, { once: true });
    } else {
        initRaf();
    }
})();
</script>

