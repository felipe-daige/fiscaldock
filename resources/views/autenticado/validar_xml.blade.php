{{-- Validação de XMLs - Autenticado --}}
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Validar XML
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Valide notas fiscais e consulte dados do emissor
                </p>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- SEÇÃO 1: Upload de Arquivos --}}
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Enviar Arquivos XML</h2>
                    <p class="text-sm text-gray-600">Arraste e solte seus arquivos XML ou clique para selecionar</p>
                </div>

                {{-- Área de Drag & Drop --}}
                <div id="upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-blue-400 transition-colors cursor-pointer bg-gray-50 hover:bg-blue-50">
                    <div class="mb-4">
                        <svg class="mx-auto h-16 w-16 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <p class="text-lg text-gray-700 mb-2 font-semibold">
                        📄 Arraste arquivos XML aqui
                    </p>
                    <p class="text-sm text-gray-500 mb-4">ou clique para selecionar</p>
                    <label for="xml-files" class="inline-block cursor-pointer px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                        Selecionar Arquivos
                        <input type="file" id="xml-files" name="xmls[]" multiple accept=".xml" class="hidden">
                    </label>
                    <p class="text-xs text-gray-500 mt-4">Aceita XML individual ou múltiplos arquivos</p>
                </div>

                {{-- Lista de Arquivos Selecionados --}}
                <div id="files-list" class="mt-6 hidden">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Arquivos selecionados:</h3>
                    <div id="files-container" class="space-y-2"></div>
                </div>
            </div>
        </div>

        {{-- SEÇÃO 2: Tipos de Validação --}}
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Selecione as validações desejadas:</h2>
            <p class="text-sm text-gray-600 mb-6">Escolha os tipos de validação que deseja realizar nas notas fiscais</p>
            
            <div id="validations-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Cards serão criados via JavaScript --}}
            </div>
        </div>

        {{-- SEÇÃO 3: Resumo e Ação --}}
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <div class="max-w-2xl mx-auto">
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        📊 Resumo da Validação
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-700">Arquivos:</span>
                            <span class="font-semibold text-gray-800" id="summary-files-count">0 XMLs</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">Validações selecionadas:</span>
                            <span class="font-semibold text-gray-800" id="summary-validations-count">1</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">Custo por nota:</span>
                            <span class="font-semibold text-blue-600" id="summary-cost-per-note">R$ 0,05</span>
                        </div>
                        <div class="border-t border-amber-300 pt-2 mt-2">
                            <div class="flex justify-between items-center">
                                <span class="text-base font-semibold text-gray-800">CUSTO TOTAL ESTIMADO:</span>
                                <span class="text-xl font-bold text-amber-600" id="summary-total-cost">R$ 0,00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button id="start-validation-btn" disabled class="px-8 py-3 bg-gray-300 text-gray-500 rounded-lg font-semibold cursor-not-allowed transition-colors">
                        🚀 Iniciar Validação
                    </button>
                </div>
            </div>
        </div>

        {{-- SEÇÃO 4: Resultados --}}
        <div id="results-section" class="bg-white rounded-lg shadow-md p-8 hidden">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Resultados da Validação</h2>
                <div class="flex gap-3">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        📥 Baixar Relatório
                    </button>
                    <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        💾 Salvar Resultado
                    </button>
                </div>
            </div>

            <div id="results-container" class="space-y-6">
                {{-- Cards de resultado serão inseridos aqui via JavaScript --}}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dados das validações
    const validations = [
        { id: 'sefaz_nfe', label: 'Situação NFe SEFAZ', price: 0.05, required: true, selected: true },
        { id: 'cnpj_emissor', label: 'CNPJ Emissor', price: 0.10, required: false, selected: false },
        { id: 'ie_emissor', label: 'Inscrição Estadual', price: 0.08, required: false, selected: false },
        { id: 'simples_nacional', label: 'Simples Nacional', price: 0.08, required: false, selected: false },
        { id: 'cnd_federal', label: 'CND Federal', price: 0.15, required: false, selected: false },
        { id: 'cnd_estadual', label: 'CND Estadual', price: 0.12, required: false, selected: false },
        { id: 'cnd_municipal', label: 'CND Municipal', price: 0.12, required: false, selected: false },
        { id: 'ceis_cnep', label: 'CEIS/CNEP', price: 0.12, required: false, selected: false },
        { id: 'protestos', label: 'Protestos', price: 0.20, required: false, selected: false },
        { id: 'fgts', label: 'FGTS (CRF)', price: 0.10, required: false, selected: false },
        { id: 'trabalho_escravo', label: 'Trabalho Escravo', price: 0.08, required: false, selected: false }
    ];

    // Estado da aplicação
    let selectedFiles = [];
    let isProcessing = false;

    // Elementos DOM
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('xml-files');
    const filesList = document.getElementById('files-list');
    const filesContainer = document.getElementById('files-container');
    const validationsGrid = document.getElementById('validations-grid');
    const startValidationBtn = document.getElementById('start-validation-btn');
    const resultsSection = document.getElementById('results-section');
    const resultsContainer = document.getElementById('results-container');

    // Inicializar cards de validação
    function initValidationCards() {
        validationsGrid.innerHTML = '';
        validations.forEach(validation => {
            const card = document.createElement('div');
            card.className = `validation-card p-4 border-2 rounded-lg cursor-pointer transition-all ${
                validation.selected 
                    ? 'border-blue-500 bg-blue-50' 
                    : 'border-gray-200 bg-white hover:border-gray-300'
            }`;
            card.dataset.validationId = validation.id;
            
            const isRequired = validation.required ? 'required' : '';
            const checkIcon = validation.selected 
                ? '<svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
                : '<svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0 2c5.523 0 10-4.477 10-10S15.523 0 10 0 0 4.477 0 10s4.477 10 10 10z" clip-rule="evenodd"></path></svg>';
            
            card.innerHTML = `
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                        ${checkIcon}
                        <span class="font-semibold text-gray-800 ${validation.required ? 'text-sm' : ''}">${validation.label}</span>
                    </div>
                </div>
                <div class="text-sm text-gray-600 mt-1">
                    ${validation.required ? '<span class="text-xs text-blue-600">(Obrigatório)</span>' : ''}
                </div>
                <div class="mt-3 text-right">
                    <span class="text-base font-semibold text-blue-600">R$ ${validation.price.toFixed(2).replace('.', ',')}</span>
                </div>
            `;
            
            card.addEventListener('click', () => {
                if (!validation.required) {
                    validation.selected = !validation.selected;
                    updateValidationCards();
                    updateSummary();
                }
            });
            
            validationsGrid.appendChild(card);
        });
    }

    // Atualizar visual dos cards de validação
    function updateValidationCards() {
        validations.forEach(validation => {
            const card = document.querySelector(`[data-validation-id="${validation.id}"]`);
            if (card) {
                if (validation.selected) {
                    card.className = 'validation-card p-4 border-2 rounded-lg cursor-pointer transition-all border-blue-500 bg-blue-50';
                    const checkIcon = '<svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
                    card.querySelector('.flex.items-start').innerHTML = `
                        <div class="flex items-center gap-2">
                            ${checkIcon}
                            <span class="font-semibold text-gray-800 ${validation.required ? 'text-sm' : ''}">${validation.label}</span>
                        </div>
                    `;
                } else {
                    card.className = 'validation-card p-4 border-2 rounded-lg cursor-pointer transition-all border-gray-200 bg-white hover:border-gray-300';
                    const checkIcon = '<svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0 2c5.523 0 10-4.477 10-10S15.523 0 10 0 0 4.477 0 10s4.477 10 10 10z" clip-rule="evenodd"></path></svg>';
                    card.querySelector('.flex.items-start').innerHTML = `
                        <div class="flex items-center gap-2">
                            ${checkIcon}
                            <span class="font-semibold text-gray-800 ${validation.required ? 'text-sm' : ''}">${validation.label}</span>
                        </div>
                    `;
                }
            }
        });
    }

    // Atualizar resumo
    function updateSummary() {
        const filesCount = selectedFiles.length;
        const selectedValidations = validations.filter(v => v.selected);
        const costPerNote = selectedValidations.reduce((sum, v) => sum + v.price, 0);
        const totalCost = filesCount * costPerNote;

        document.getElementById('summary-files-count').textContent = filesCount === 0 ? 'Nenhum arquivo selecionado' : `${filesCount} XML${filesCount > 1 ? 's' : ''}`;
        document.getElementById('summary-validations-count').textContent = selectedValidations.length;
        document.getElementById('summary-cost-per-note').textContent = `R$ ${costPerNote.toFixed(2).replace('.', ',')}`;
        document.getElementById('summary-total-cost').textContent = `R$ ${totalCost.toFixed(2).replace('.', ',')}`;

        // Habilitar/desabilitar botão
        if (filesCount > 0 && !isProcessing) {
            startValidationBtn.disabled = false;
            startValidationBtn.className = 'px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors';
        } else {
            startValidationBtn.disabled = true;
            startValidationBtn.className = 'px-8 py-3 bg-gray-300 text-gray-500 rounded-lg font-semibold cursor-not-allowed transition-colors';
        }
    }

    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('border-blue-500', 'bg-blue-50');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('border-blue-500', 'bg-blue-50');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('border-blue-500', 'bg-blue-50');
        const files = Array.from(e.dataTransfer.files).filter(f => f.name.endsWith('.xml'));
        handleFiles(files);
    });

    // File input change
    fileInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files).filter(f => f.name.endsWith('.xml'));
        handleFiles(files);
    });

    // Click na área de upload
    uploadArea.addEventListener('click', () => {
        fileInput.click();
    });

    // Handle files
    function handleFiles(files) {
        selectedFiles = [...selectedFiles, ...files];
        updateFilesList();
        updateSummary();
    }

    // Update files list
    function updateFilesList() {
        filesContainer.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
            
            const fileSize = file.size < 1024 * 1024 
                ? `${(file.size / 1024).toFixed(2)} KB`
                : `${(file.size / (1024 * 1024)).toFixed(2)} MB`;
            
            fileItem.innerHTML = `
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="text-sm text-gray-700">${file.name}</span>
                    <span class="text-xs text-gray-500">(${fileSize})</span>
                </div>
                <button onclick="removeFile(${index})" class="text-red-500 hover:text-red-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            filesContainer.appendChild(fileItem);
        });
        
        if (selectedFiles.length > 0) {
            filesList.classList.remove('hidden');
        } else {
            filesList.classList.add('hidden');
        }
    }

    // Remove file
    window.removeFile = function(index) {
        selectedFiles.splice(index, 1);
        updateFilesList();
        updateSummary();
    };

    // Iniciar validação
    startValidationBtn.addEventListener('click', () => {
        if (selectedFiles.length === 0 || isProcessing) return;
        
        isProcessing = true;
        startValidationBtn.disabled = true;
        startValidationBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processando...
        `;
        
        // Simular processamento
        setTimeout(() => {
            showMockResults();
            isProcessing = false;
            startValidationBtn.innerHTML = '🚀 Iniciar Validação';
            updateSummary();
        }, 2000);
    });

    // Mostrar resultados mockados
    function showMockResults() {
        resultsSection.classList.remove('hidden');
        resultsContainer.innerHTML = '';

        // Resultado 1: OK
        const result1 = createResultCard({
            numero: '12345',
            chave: '35240112345678000199550010000123451234567890',
            emissor: 'EMPRESA EXEMPLO LTDA',
            cnpj: '12.345.678/0001-99',
            valor: '1.500,00',
            status: 'ok',
            validations: [
                { id: 'sefaz_nfe', label: 'NFe SEFAZ', status: 'ok', message: 'Autorizada' },
                { id: 'cnpj_emissor', label: 'CNPJ Ativo', status: 'ok', message: 'Regular' },
                { id: 'ie_emissor', label: 'IE', status: 'warning', message: 'Suspensa' }
            ],
            divergencias: []
        });
        resultsContainer.appendChild(result1);

        // Resultado 2: Problema
        const result2 = createResultCard({
            numero: '12346',
            chave: '35240112345678000199550010000123461234567891',
            emissor: 'OUTRA EMPRESA LTDA',
            cnpj: '98.765.432/0001-10',
            valor: '3.200,00',
            status: 'error',
            validations: [
                { id: 'sefaz_nfe', label: 'NFe SEFAZ', status: 'error', message: 'Cancelada' },
                { id: 'cnpj_emissor', label: 'CNPJ', status: 'error', message: 'Baixado' },
                { id: 'ie_emissor', label: 'IE', status: 'error', message: 'Cancelada' }
            ],
            divergencias: [
                'Nota cancelada na SEFAZ em 15/01/2024',
                'CNPJ do emissor foi baixado em 10/01/2024'
            ]
        });
        resultsContainer.appendChild(result2);

        // Scroll suave para resultados
        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Criar card de resultado
    function createResultCard(data) {
        const card = document.createElement('div');
        card.className = 'border-2 rounded-lg p-6 bg-white';
        
        const statusConfig = {
            ok: { 
                border: 'border-green-200', 
                bg: 'bg-green-50', 
                icon: '✅', 
                statusText: 'OK',
                statusColor: 'text-green-600'
            },
            warning: { 
                border: 'border-amber-200', 
                bg: 'bg-amber-50', 
                icon: '⚠️', 
                statusText: 'Atenção',
                statusColor: 'text-amber-600'
            },
            error: { 
                border: 'border-red-200', 
                bg: 'bg-red-50', 
                icon: '❌', 
                statusText: 'Problema',
                statusColor: 'text-red-600'
            }
        };

        const config = statusConfig[data.status];
        card.className = `border-2 ${config.border} rounded-lg p-6 ${config.bg}`;

        let validationsHtml = '';
        data.validations.forEach(validation => {
            const valStatusConfig = {
                ok: { bg: 'bg-green-100', text: 'text-green-600', icon: '✅' },
                warning: { bg: 'bg-amber-100', text: 'text-amber-600', icon: '⚠️' },
                error: { bg: 'bg-red-100', text: 'text-red-600', icon: '❌' },
                not_checked: { bg: 'bg-gray-100', text: 'text-gray-400', icon: '○' }
            };
            const valConfig = valStatusConfig[validation.status] || valStatusConfig.not_checked;
            
            validationsHtml += `
                <div class="${valConfig.bg} ${valConfig.text} p-3 rounded-lg text-sm">
                    <div class="font-semibold mb-1">${valConfig.icon} ${validation.label}</div>
                    <div class="text-xs">${validation.message || 'Não consultado'}</div>
                </div>
            `;
        });

        let divergenciasHtml = '';
        if (data.divergencias && data.divergencias.length > 0) {
            divergenciasHtml = `
                <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="font-semibold text-red-800 mb-2">⚠️ DIVERGÊNCIAS ENCONTRADAS:</div>
                    <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                        ${data.divergencias.map(d => `<li>${d}</li>`).join('')}
                    </ul>
                </div>
            `;
        }

        card.innerHTML = `
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">NF ${data.numero}</h3>
                    <p class="text-xs text-gray-500 mt-1">Chave: ${data.chave}</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl mb-1">${config.icon}</div>
                    <div class="text-sm font-semibold ${config.statusColor}">${config.statusText}</div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 text-sm">
                <div>
                    <span class="text-gray-600">Emissor:</span>
                    <span class="font-semibold text-gray-800 ml-2">${data.emissor}</span>
                </div>
                <div>
                    <span class="text-gray-600">CNPJ:</span>
                    <span class="font-semibold text-gray-800 ml-2">${data.cnpj}</span>
                </div>
                <div>
                    <span class="text-gray-600">Valor:</span>
                    <span class="font-semibold text-gray-800 ml-2">R$ ${data.valor}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                ${validationsHtml}
            </div>

            ${divergenciasHtml}
        `;

        return card;
    }

    // Inicializar
    initValidationCards();
    updateSummary();
});
</script>

