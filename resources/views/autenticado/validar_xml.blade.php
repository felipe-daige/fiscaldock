{{-- Validação de XMLs - Autenticado --}}
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">
                    Validar XML
                </h1>
                <p class="text-xs text-gray-500 mt-1">
                    Valide notas fiscais e consulte dados do emissor
                </p>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Coluna 1: Upload --}}
            <div class="w-full lg:w-80">
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h2 class="text-sm font-semibold text-gray-800 mb-3">Enviar Arquivos XML</h2>
                    
                    {{-- Área de Drag & Drop --}}
                    <div id="upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors cursor-pointer bg-gray-50 hover:bg-blue-50">
                        <div class="mb-3">
                            <svg class="mx-auto h-10 w-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-700 mb-4">
                            Arraste arquivos XML aqui ou clique para selecionar
                        </p>
                        <label for="xml-files" class="inline-block cursor-pointer px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors">
                            Selecionar Arquivos
                            <input type="file" id="xml-files" name="xmls[]" multiple accept=".xml" class="hidden">
                        </label>
                    </div>

                    {{-- Lista de Arquivos Selecionados --}}
                    <div id="files-list" class="mt-3 hidden">
                        <h3 class="text-xs font-semibold text-gray-700 mb-2">Arquivos selecionados:</h3>
                        <div id="files-container" class="space-y-1.5"></div>
                    </div>
                </div>
            </div>

            {{-- Coluna 2: Validações --}}
            <div class="flex-1">
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h2 class="text-sm font-semibold text-gray-800 mb-3">Validações</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 w-12"></th>
                                    <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700">Validação</th>
                                    <th class="text-right py-2 px-3 text-xs font-semibold text-gray-700 w-24">Preço</th>
                                </tr>
                            </thead>
                            <tbody id="validations-table-body">
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-validation-id="sefaz_nfe" data-price="0.55" data-required="true">
                                    <td class="py-2 px-3">
                                        <input type="checkbox" class="validation-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked disabled>
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="text-gray-800">Situação SEFAZ</span>
                                        <span class="text-xs text-blue-600 ml-1">(Obrigatório)</span>
                                    </td>
                                    <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-validation-id="sefaz_receita" data-price="0.55" data-required="false">
                                    <td class="py-2 px-3">
                                        <input type="checkbox" class="validation-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="text-gray-800">Situação Receita Federal</span>
                                    </td>
                                    <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-validation-id="cnpj_emissor" data-price="0.55" data-required="false">
                                    <td class="py-2 px-3">
                                        <input type="checkbox" class="validation-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="text-gray-800">CNPJ Emissor</span>
                                    </td>
                                    <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-validation-id="ie_emissor" data-price="0.55" data-required="false">
                                    <td class="py-2 px-3">
                                        <input type="checkbox" class="validation-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="text-gray-800">Inscrição Estadual</span>
                                    </td>
                                    <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-validation-id="simples_nacional" data-price="0.55" data-required="false">
                                    <td class="py-2 px-3">
                                        <input type="checkbox" class="validation-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="text-gray-800">Simples Nacional</span>
                                    </td>
                                    <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-validation-id="cnd_federal" data-price="0.55" data-required="false">
                                    <td class="py-2 px-3">
                                        <input type="checkbox" class="validation-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="text-gray-800">CND Federal</span>
                                    </td>
                                    <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-validation-id="cnd_estadual" data-price="0.55" data-required="false">
                                    <td class="py-2 px-3">
                                        <input type="checkbox" class="validation-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="text-gray-800">CND Estadual</span>
                                    </td>
                                    <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-validation-id="cnd_municipal" data-price="0.55" data-required="false">
                                    <td class="py-2 px-3">
                                        <input type="checkbox" class="validation-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="text-gray-800">CND Municipal</span>
                                    </td>
                                    <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-validation-id="ceis_cnep" data-price="0.55" data-required="false">
                                    <td class="py-2 px-3">
                                        <input type="checkbox" class="validation-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="text-gray-800">CEIS/CNEP</span>
                                    </td>
                                    <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-validation-id="protestos" data-price="0.65" data-required="false">
                                    <td class="py-2 px-3">
                                        <input type="checkbox" class="validation-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="text-gray-800">Protestos</span>
                                    </td>
                                    <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,65</td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-validation-id="fgts" data-price="0.55" data-required="false">
                                    <td class="py-2 px-3">
                                        <input type="checkbox" class="validation-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="text-gray-800">FGTS (CRF)</span>
                                    </td>
                                    <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-validation-id="trabalho_escravo" data-price="0.55" data-required="false">
                                    <td class="py-2 px-3">
                                        <input type="checkbox" class="validation-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="text-gray-800">Trabalho Escravo</span>
                                    </td>
                                    <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Coluna 3: Resumo --}}
            <div class="w-full lg:w-72">
                <div class="sticky top-4">
                    <div class="bg-white rounded-lg shadow-md p-4">
                        <h3 class="text-sm font-semibold text-gray-800 mb-3">Resumo</h3>
                        <div class="space-y-2 text-sm mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Arquivos:</span>
                                <span class="font-semibold text-gray-800" id="summary-files-count">0 XMLs</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Validações:</span>
                                <span class="font-semibold text-gray-800" id="summary-validations-count">1</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Custo/nota:</span>
                                <span class="font-semibold text-blue-600" id="summary-cost-per-note">R$ 0,55</span>
                            </div>
                            <div class="border-t border-gray-200 pt-2 mt-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-semibold text-gray-800">TOTAL:</span>
                                    <span class="text-lg font-bold text-amber-600" id="summary-total-cost">R$ 0,00</span>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="start-validation-btn" disabled class="w-full px-4 py-2.5 bg-gray-300 text-gray-500 rounded-lg text-sm font-semibold cursor-not-allowed transition-colors">
                            Iniciar Validação
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- SEÇÃO: Resultados (full width abaixo das 3 colunas) --}}
        <div id="results-section" class="mt-4 bg-white rounded-lg shadow-md p-4 hidden">
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Resultados da Validação</h2>
                <div class="flex gap-2">
                    <button class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Baixar Relatório
                    </button>
                    <button class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Salvar Resultado
                    </button>
                </div>
            </div>

            <div id="results-container" class="space-y-4">
                {{-- Cards de resultado serão inseridos aqui via JavaScript --}}
            </div>
        </div>
    </div>
</div>

<script>
// Estado da aplicação (global)
window.selectedFiles = window.selectedFiles || [];
window.isProcessing = window.isProcessing || false;

// Função global para atualizar resumo
window.updateSummary = function() {
    const filesCount = window.selectedFiles ? window.selectedFiles.length : 0;
    const validationsTableBody = document.getElementById('validations-table-body');
    const startValidationBtn = document.getElementById('start-validation-btn');
    
    if (!validationsTableBody) return;
    
    // Ler checkboxes marcados diretamente do DOM
    const checkedBoxes = validationsTableBody.querySelectorAll('.validation-checkbox:checked');
    let costPerNote = 0;
    checkedBoxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const price = parseFloat(row.dataset.price);
        costPerNote += price;
    });
    
    const totalCost = filesCount * costPerNote;

    const summaryFilesCount = document.getElementById('summary-files-count');
    const summaryValidationsCount = document.getElementById('summary-validations-count');
    const summaryCostPerNote = document.getElementById('summary-cost-per-note');
    const summaryTotalCost = document.getElementById('summary-total-cost');

    if (summaryFilesCount) summaryFilesCount.textContent = filesCount === 0 ? '0 XMLs' : `${filesCount} XML${filesCount > 1 ? 's' : ''}`;
    if (summaryValidationsCount) summaryValidationsCount.textContent = checkedBoxes.length;
    if (summaryCostPerNote) summaryCostPerNote.textContent = `R$ ${costPerNote.toFixed(2).replace('.', ',')}`;
    if (summaryTotalCost) summaryTotalCost.textContent = `R$ ${totalCost.toFixed(2).replace('.', ',')}`;

    // Habilitar/desabilitar botão
    if (startValidationBtn) {
        if (filesCount > 0 && !window.isProcessing) {
            startValidationBtn.disabled = false;
            startValidationBtn.className = 'w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors';
        } else {
            startValidationBtn.disabled = true;
            startValidationBtn.className = 'w-full px-4 py-2.5 bg-gray-300 text-gray-500 rounded-lg text-sm font-semibold cursor-not-allowed transition-colors';
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {

    // Elementos DOM
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('xml-files');
    const filesList = document.getElementById('files-list');
    const filesContainer = document.getElementById('files-container');
    const validationsTableBody = document.getElementById('validations-table-body');
    const startValidationBtn = document.getElementById('start-validation-btn');
    const resultsSection = document.getElementById('results-section');
    const resultsContainer = document.getElementById('results-container');

    // Inicializar event listeners dos checkboxes
    function initValidationCheckboxes() {
        const checkboxes = validationsTableBody.querySelectorAll('.validation-checkbox');
        checkboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const isRequired = row.dataset.required === 'true';
            
            if (isRequired) {
                checkbox.disabled = true;
            }
            
            checkbox.addEventListener('change', () => {
                if (!isRequired) {
                    updateSummary();
                }
            });
        });
    }

    // Atualizar resumo (wrapper local que chama a função global)
    function updateSummary() {
        window.updateSummary();
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
        window.selectedFiles = [...window.selectedFiles, ...files];
        updateFilesList();
        updateSummary();
    }

    // Update files list
    function updateFilesList() {
        filesContainer.innerHTML = '';
        window.selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'flex items-center justify-between py-1.5 text-xs';
            
            fileItem.innerHTML = `
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="text-gray-700 truncate text-xs">${file.name}</span>
                </div>
                <button onclick="removeFile(${index})" class="text-red-500 hover:text-red-700 transition-colors flex-shrink-0 ml-2 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            filesContainer.appendChild(fileItem);
        });
        
        if (window.selectedFiles.length > 0) {
            filesList.classList.remove('hidden');
        } else {
            filesList.classList.add('hidden');
        }
    }

    // Remove file
    window.removeFile = function(index) {
        window.selectedFiles.splice(index, 1);
        updateFilesList();
        updateSummary();
    };

    // Iniciar validação (funcionalidade removida por enquanto)
    // startValidationBtn.addEventListener('click', () => {
    //     if (window.selectedFiles.length === 0 || isProcessing) return;
    //     
    //     isProcessing = true;
    //     startValidationBtn.disabled = true;
    //     startValidationBtn.innerHTML = `
    //         <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    //             <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    //             <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    //         </svg>
    //         Processando...
    //     `;
    //     
    //     // Simular processamento
    //     setTimeout(() => {
    //         showMockResults();
    //         isProcessing = false;
    //         startValidationBtn.innerHTML = 'Iniciar Validação';
    //         updateSummary();
    //     }, 2000);
    // });

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
        card.className = 'border-2 rounded-lg p-4 bg-white';
        
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
        card.className = `border-2 ${config.border} rounded-lg p-4 ${config.bg}`;

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
                <div class="${valConfig.bg} ${valConfig.text} p-2 rounded-lg text-xs">
                    <div class="font-semibold mb-0.5">${valConfig.icon} ${validation.label}</div>
                    <div class="text-xs opacity-90">${validation.message || 'Não consultado'}</div>
                </div>
            `;
        });

        let divergenciasHtml = '';
        if (data.divergencias && data.divergencias.length > 0) {
            divergenciasHtml = `
                <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="font-semibold text-red-800 mb-1.5 text-sm">⚠️ DIVERGÊNCIAS ENCONTRADAS:</div>
                    <ul class="list-disc list-inside space-y-0.5 text-xs text-red-700">
                        ${data.divergencias.map(d => `<li>${d}</li>`).join('')}
                    </ul>
                </div>
            `;
        }

        card.innerHTML = `
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="text-base font-bold text-gray-800">NF ${data.numero}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Chave: ${data.chave}</p>
                </div>
                <div class="text-right">
                    <div class="text-xl mb-0.5">${config.icon}</div>
                    <div class="text-xs font-semibold ${config.statusColor}">${config.statusText}</div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-3 text-xs">
                <div>
                    <span class="text-gray-600">Emissor:</span>
                    <span class="font-semibold text-gray-800 ml-1">${data.emissor}</span>
                </div>
                <div>
                    <span class="text-gray-600">CNPJ:</span>
                    <span class="font-semibold text-gray-800 ml-1">${data.cnpj}</span>
                </div>
                <div>
                    <span class="text-gray-600">Valor:</span>
                    <span class="font-semibold text-gray-800 ml-1">R$ ${data.valor}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-3">
                ${validationsHtml}
            </div>

            ${divergenciasHtml}
        `;

        return card;
    }

    // Inicializar
    initValidationCheckboxes();
    updateSummary();
});

// Função para inicializar quando a página for carregada via SPA
function initValidationPage() {
    const validationsTableBody = document.getElementById('validations-table-body');
    if (!validationsTableBody) return;
    
    // Se já foi inicializado, não fazer nada
    if (validationsTableBody.dataset.initialized === 'true') return;
    validationsTableBody.dataset.initialized = 'true';
    
    const checkboxes = validationsTableBody.querySelectorAll('.validation-checkbox');
    checkboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const isRequired = row.dataset.required === 'true';
        
        if (isRequired) {
            checkbox.disabled = true;
        }
        
        // Adicionar event listener se ainda não tiver
        if (!checkbox.dataset.hasListener) {
            checkbox.dataset.hasListener = 'true';
            checkbox.addEventListener('change', function() {
                if (!isRequired && window.updateSummary) {
                    window.updateSummary();
                }
            });
        }
    });
    
    // Atualizar resumo na inicialização
    if (window.updateSummary) {
        window.updateSummary();
    }
}

// Executar imediatamente se o DOM já estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initValidationPage);
} else {
    initValidationPage();
}

// Expor função globalmente para ser chamada pelo SPA quando necessário
window.initValidationPage = initValidationPage;
</script>

