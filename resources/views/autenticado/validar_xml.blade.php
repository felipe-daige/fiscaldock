{{-- Validacao de XMLs - Autenticado --}}
<div class="min-h-screen bg-gray-50" id="validar-xml-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Validar XML</h1>
                    <p class="mt-1 text-sm text-gray-600">Valide notas fiscais e consulte dados dos emissores.</p>
                </div>
                <a
                    href="/app"
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
                        Envie arquivos XML de notas fiscais para extrair automaticamente os CNPJs emissores e consultar a situacao cadastral e fiscal de cada um.
                    </p>
                </div>
            </div>
        </div>

        {{-- Grid: Upload/Plano + Resumo/Dicas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 items-stretch">
            {{-- Card Esquerdo: Upload + Selecao de Plano --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm h-full flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Enviar Arquivos XML</h2>
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    {{-- Area de Drag & Drop --}}
                    <div id="upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors cursor-pointer bg-gray-50 hover:bg-blue-50 mb-4">
                        <div class="mb-3">
                            <svg class="mx-auto h-10 w-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-700 mb-3">Arraste arquivos XML aqui ou clique para selecionar</p>
                        <label for="xml-files" class="inline-block cursor-pointer px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors">
                            Selecionar Arquivos
                            <input type="file" id="xml-files" name="xmls[]" multiple accept=".xml" class="hidden">
                        </label>
                    </div>

                    {{-- Lista de Arquivos Selecionados --}}
                    <div id="files-list" class="hidden mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium text-gray-700">Arquivos selecionados:</h3>
                            <button type="button" id="btn-limpar-arquivos" class="text-xs text-red-600 hover:text-red-700 font-medium">
                                Limpar todos
                            </button>
                        </div>
                        <div id="files-container" class="space-y-1.5 max-h-32 overflow-y-auto"></div>
                    </div>

                    {{-- Selecao do Plano --}}
                    <div class="border-t border-gray-200 pt-4 mt-auto">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de consulta:
                        </label>
                        <div class="space-y-2" id="planos-grid">
                            @php
                                $planosDisponiveis = [
                                    ['codigo' => 'basico', 'nome' => 'Basico', 'creditos' => 0, 'gratuito' => true, 'descricao' => 'Dados cadastrais + Simples/MEI', 'principal' => true],
                                    ['codigo' => 'cadastral_plus', 'nome' => 'Cadastral+', 'creditos' => 3, 'gratuito' => false, 'descricao' => 'Basico + SINTEGRA + TCU Consolidada', 'principal' => true],
                                    ['codigo' => 'fiscal_federal', 'nome' => 'Fiscal Federal', 'creditos' => 6, 'gratuito' => false, 'descricao' => 'Cadastral+ + CND Federal + CRF FGTS', 'principal' => false],
                                    ['codigo' => 'fiscal_completo', 'nome' => 'Fiscal Completo', 'creditos' => 12, 'gratuito' => false, 'descricao' => 'Fiscal Federal + CND Estadual + CNDT', 'principal' => true],
                                    ['codigo' => 'due_diligence', 'nome' => 'Due Diligence', 'creditos' => 16, 'gratuito' => false, 'descricao' => 'Fiscal Completo + Lista Devedores PGFN', 'principal' => false],
                                    ['codigo' => 'esg', 'nome' => 'ESG', 'creditos' => 6, 'gratuito' => false, 'descricao' => 'Trabalho Escravo + IBAMA Autuacoes', 'principal' => false],
                                    ['codigo' => 'completo', 'nome' => 'Completo', 'creditos' => 22, 'gratuito' => false, 'descricao' => 'Todas as consultas disponiveis', 'principal' => true],
                                ];
                            @endphp

                            {{-- Planos principais (sempre visiveis) --}}
                            @foreach($planosDisponiveis as $index => $plano)
                                @if($plano['principal'])
                                    <label class="plano-option relative cursor-pointer block">
                                        <input
                                            type="radio"
                                            name="plano"
                                            value="{{ $plano['codigo'] }}"
                                            data-creditos="{{ $plano['creditos'] }}"
                                            data-nome="{{ $plano['nome'] }}"
                                            class="sr-only peer"
                                            {{ $index === 0 ? 'checked' : '' }}
                                        >
                                        <div class="p-3 rounded-lg border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-gray-900">{{ $plano['nome'] }}</span>
                                                    <span class="text-xs text-gray-500">- {{ $plano['descricao'] }}</span>
                                                </div>
                                                @if($plano['gratuito'])
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                                        Gratis
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                                        {{ $plano['creditos'] }} cred.
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endif
                            @endforeach

                            {{-- Link para ver todos os planos --}}
                            <button type="button" id="btn-ver-todos-planos" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1 mt-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                                Ver todos os planos
                            </button>

                            {{-- Planos extras (ocultos inicialmente) --}}
                            <div id="planos-extras" class="hidden space-y-2">
                                @foreach($planosDisponiveis as $plano)
                                    @if(!$plano['principal'])
                                        <label class="plano-option relative cursor-pointer block">
                                            <input
                                                type="radio"
                                                name="plano"
                                                value="{{ $plano['codigo'] }}"
                                                data-creditos="{{ $plano['creditos'] }}"
                                                data-nome="{{ $plano['nome'] }}"
                                                class="sr-only peer"
                                            >
                                            <div class="p-3 rounded-lg border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition-colors">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-semibold text-gray-900">{{ $plano['nome'] }}</span>
                                                        <span class="text-xs text-gray-500">- {{ $plano['descricao'] }}</span>
                                                    </div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                                        {{ $plano['creditos'] }} cred.
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Direito: Resumo + Dicas --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm h-full flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Resumo</h2>
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    {{-- Resumo da validacao --}}
                    <div class="space-y-3 text-sm mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Arquivos XML:</span>
                            <span class="font-semibold text-gray-900" id="summary-files-count">0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">CNPJs unicos:</span>
                            <span class="font-semibold text-gray-900" id="summary-cnpjs-count">0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Plano selecionado:</span>
                            <span class="font-semibold text-blue-600" id="summary-plano">Basico</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Custo por CNPJ:</span>
                            <span class="font-semibold text-gray-900" id="summary-cost-per-cnpj">Gratis</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 mt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold text-gray-900">TOTAL ESTIMADO:</span>
                                <span class="text-lg font-bold text-amber-600" id="summary-total-cost">Gratis</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">* O total final depende dos CNPJs unicos encontrados</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-gray-600">Seu saldo:</span>
                            <span class="font-semibold text-gray-900">{{ number_format($credits ?? 0, 0, ',', '.') }} creditos</span>
                        </div>
                    </div>

                    <button type="button" id="start-validation-btn" disabled class="w-full px-4 py-3 bg-gray-300 text-gray-500 rounded-lg text-sm font-semibold cursor-not-allowed transition-colors">
                        Iniciar Validacao
                    </button>

                    {{-- Dicas --}}
                    <div class="mt-6 pt-6 border-t border-gray-200 flex-1">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Passo a passo</h4>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">1</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Envie os XMLs</p>
                                    <p class="text-xs text-gray-500">Arraste ou selecione os arquivos XML das notas fiscais</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">2</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Escolha o plano</p>
                                    <p class="text-xs text-gray-500">Selecione o tipo de consulta desejado</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">3</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Extracao automatica</p>
                                    <p class="text-xs text-gray-500">Os CNPJs emissores sao extraidos automaticamente</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold">4</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Relatorio completo</p>
                                    <p class="text-xs text-gray-500">Receba o resultado de validacao de cada CNPJ</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Secao: Resultados (full width abaixo) --}}
        <div id="results-section" class="hidden">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Resultados da Validacao</h2>
                            <p class="text-sm text-gray-500 mt-1">
                                <span id="result-cnpjs-count">0</span> CNPJs consultados |
                                <span id="result-creditos-used">0</span> creditos utilizados
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" id="btn-download-relatorio" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Baixar Relatorio
                            </button>
                            <button type="button" id="btn-nova-validacao" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors">
                                Nova Validacao
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Resumo por Status --}}
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600" id="result-ok-count">0</div>
                            <div class="text-xs text-gray-600">Regulares</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-amber-600" id="result-warning-count">0</div>
                            <div class="text-xs text-gray-600">Atencao</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600" id="result-error-count">0</div>
                            <div class="text-xs text-gray-600">Irregulares</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-600" id="result-pending-count">0</div>
                            <div class="text-xs text-gray-600">Pendentes</div>
                        </div>
                    </div>
                </div>

                {{-- Lista de Resultados --}}
                <div id="results-container" class="divide-y divide-gray-200">
                    {{-- Cards de resultado serao inseridos aqui via JavaScript --}}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    function initValidarXml() {
        const container = document.getElementById('validar-xml-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Validar XML] Inicializando...');

        // Estado
        let selectedFiles = [];
        let uniqueCnpjs = new Set();
        let isProcessing = false;
        let planosExpanded = false;

        // Elementos
        const uploadArea = document.getElementById('upload-area');
        const fileInput = document.getElementById('xml-files');
        const filesList = document.getElementById('files-list');
        const filesContainer = document.getElementById('files-container');
        const btnLimparArquivos = document.getElementById('btn-limpar-arquivos');
        const startValidationBtn = document.getElementById('start-validation-btn');
        const resultsSection = document.getElementById('results-section');
        const resultsContainer = document.getElementById('results-container');
        const btnNovaValidacao = document.getElementById('btn-nova-validacao');
        const btnVerTodosPlanos = document.getElementById('btn-ver-todos-planos');
        const planosExtras = document.getElementById('planos-extras');

        // Toggle planos extras
        if (btnVerTodosPlanos && planosExtras) {
            btnVerTodosPlanos.addEventListener('click', function() {
                planosExpanded = !planosExpanded;
                if (planosExpanded) {
                    planosExtras.classList.remove('hidden');
                    btnVerTodosPlanos.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> Ocultar planos extras';
                } else {
                    planosExtras.classList.add('hidden');
                    btnVerTodosPlanos.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg> Ver todos os planos';
                }
            });
        }

        // Funcao para atualizar resumo
        function updateSummary() {
            const filesCount = selectedFiles.length;
            const cnpjsCount = uniqueCnpjs.size;
            const planoSelecionado = document.querySelector('input[name="plano"]:checked');
            const creditos = planoSelecionado ? parseInt(planoSelecionado.dataset.creditos || 0) : 0;
            const planoNome = planoSelecionado ? planoSelecionado.dataset.nome : 'Basico';
            const totalCost = cnpjsCount * creditos;

            document.getElementById('summary-files-count').textContent = filesCount;
            document.getElementById('summary-cnpjs-count').textContent = cnpjsCount;
            document.getElementById('summary-plano').textContent = planoNome;
            document.getElementById('summary-cost-per-cnpj').textContent = creditos === 0 ? 'Gratis' : creditos + ' cred.';
            document.getElementById('summary-total-cost').textContent = totalCost === 0 ? 'Gratis' : totalCost + ' creditos';

            // Habilitar/desabilitar botao
            if (filesCount > 0 && !isProcessing) {
                startValidationBtn.disabled = false;
                startValidationBtn.className = 'w-full px-4 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors';
            } else {
                startValidationBtn.disabled = true;
                startValidationBtn.className = 'w-full px-4 py-3 bg-gray-300 text-gray-500 rounded-lg text-sm font-semibold cursor-not-allowed transition-colors';
            }
        }

        // Extrair CNPJ do nome do arquivo ou conteudo (simplificado)
        function extractCnpjFromFilename(filename) {
            // Tenta extrair CNPJ do nome do arquivo (padrao comum: chave da NFe contem CNPJ)
            const match = filename.match(/(\d{14})/);
            if (match) {
                return match[1];
            }
            return null;
        }

        // Handle files
        function handleFiles(files) {
            files.forEach(file => {
                if (!selectedFiles.some(f => f.name === file.name)) {
                    selectedFiles.push(file);
                    // Tentar extrair CNPJ do nome do arquivo
                    const cnpj = extractCnpjFromFilename(file.name);
                    if (cnpj) {
                        uniqueCnpjs.add(cnpj);
                    }
                }
            });
            updateFilesList();
            updateSummary();
        }

        // Atualizar lista de arquivos
        function updateFilesList() {
            filesContainer.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center justify-between py-1.5 px-2 bg-gray-50 rounded text-xs';

                fileItem.innerHTML = `
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-gray-700 truncate">${file.name}</span>
                    </div>
                    <button type="button" class="remove-file text-red-500 hover:text-red-700 transition-colors flex-shrink-0 ml-2 p-1" data-index="${index}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

            // Event listeners para remover arquivos
            document.querySelectorAll('.remove-file').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    selectedFiles.splice(index, 1);
                    // Recalcular CNPJs unicos
                    uniqueCnpjs.clear();
                    selectedFiles.forEach(f => {
                        const cnpj = extractCnpjFromFilename(f.name);
                        if (cnpj) uniqueCnpjs.add(cnpj);
                    });
                    updateFilesList();
                    updateSummary();
                });
            });
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
            const files = Array.from(e.dataTransfer.files).filter(f => f.name.toLowerCase().endsWith('.xml'));
            handleFiles(files);
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files).filter(f => f.name.toLowerCase().endsWith('.xml'));
            handleFiles(files);
            e.target.value = ''; // Reset para permitir selecionar o mesmo arquivo novamente
        });

        // Click na area de upload
        uploadArea.addEventListener('click', (e) => {
            if (e.target.tagName !== 'INPUT') {
                fileInput.click();
            }
        });

        // Limpar todos os arquivos
        if (btnLimparArquivos) {
            btnLimparArquivos.addEventListener('click', () => {
                selectedFiles = [];
                uniqueCnpjs.clear();
                updateFilesList();
                updateSummary();
            });
        }

        // Mudanca de plano
        document.querySelectorAll('input[name="plano"]').forEach(radio => {
            radio.addEventListener('change', updateSummary);
        });

        // Nova validacao
        if (btnNovaValidacao) {
            btnNovaValidacao.addEventListener('click', () => {
                resultsSection.classList.add('hidden');
                selectedFiles = [];
                uniqueCnpjs.clear();
                updateFilesList();
                updateSummary();
            });
        }

        // Iniciar validacao (placeholder - sera implementado com backend)
        startValidationBtn.addEventListener('click', async () => {
            if (selectedFiles.length === 0 || isProcessing) return;

            isProcessing = true;
            startValidationBtn.disabled = true;
            startValidationBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processando...
            `;

            // Simular processamento (substituir por chamada real ao backend)
            setTimeout(() => {
                showMockResults();
                isProcessing = false;
                startValidationBtn.innerHTML = 'Iniciar Validacao';
                updateSummary();
            }, 2000);
        });

        // Mostrar resultados mockados (substituir por resultados reais)
        function showMockResults() {
            const planoSelecionado = document.querySelector('input[name="plano"]:checked');
            const creditos = planoSelecionado ? parseInt(planoSelecionado.dataset.creditos || 0) : 0;
            const cnpjsCount = Math.max(uniqueCnpjs.size, selectedFiles.length);

            resultsSection.classList.remove('hidden');

            // Atualizar contadores
            document.getElementById('result-cnpjs-count').textContent = cnpjsCount;
            document.getElementById('result-creditos-used').textContent = cnpjsCount * creditos;
            document.getElementById('result-ok-count').textContent = Math.floor(cnpjsCount * 0.7);
            document.getElementById('result-warning-count').textContent = Math.floor(cnpjsCount * 0.2);
            document.getElementById('result-error-count').textContent = Math.floor(cnpjsCount * 0.1);
            document.getElementById('result-pending-count').textContent = 0;

            // Gerar resultados mockados
            resultsContainer.innerHTML = '';

            const mockResults = [
                {
                    cnpj: '12.345.678/0001-99',
                    razaoSocial: 'EMPRESA EXEMPLO LTDA',
                    situacao: 'ATIVA',
                    status: 'ok',
                    consultas: {
                        situacao_cadastral: { status: 'ok', valor: 'ATIVA' },
                        simples_nacional: { status: 'ok', valor: 'Optante' },
                        cnd_federal: { status: 'ok', valor: 'Negativa' },
                    }
                },
                {
                    cnpj: '98.765.432/0001-10',
                    razaoSocial: 'OUTRA EMPRESA SA',
                    situacao: 'INAPTA',
                    status: 'error',
                    consultas: {
                        situacao_cadastral: { status: 'error', valor: 'INAPTA' },
                        simples_nacional: { status: 'warning', valor: 'Nao Optante' },
                        cnd_federal: { status: 'error', valor: 'Positiva' },
                    }
                }
            ];

            mockResults.forEach(result => {
                const card = createResultCard(result);
                resultsContainer.appendChild(card);
            });

            // Scroll para resultados
            resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Criar card de resultado
        function createResultCard(data) {
            const card = document.createElement('div');

            const statusConfig = {
                ok: { border: 'border-l-green-500', bg: 'bg-green-50', icon: 'text-green-600', badge: 'bg-green-100 text-green-700' },
                warning: { border: 'border-l-amber-500', bg: 'bg-amber-50', icon: 'text-amber-600', badge: 'bg-amber-100 text-amber-700' },
                error: { border: 'border-l-red-500', bg: 'bg-red-50', icon: 'text-red-600', badge: 'bg-red-100 text-red-700' }
            };
            const config = statusConfig[data.status] || statusConfig.warning;

            let consultasHtml = '';
            if (data.consultas) {
                Object.entries(data.consultas).forEach(([key, value]) => {
                    const consultaConfig = statusConfig[value.status] || statusConfig.warning;
                    const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    consultasHtml += `
                        <div class="flex items-center justify-between py-1">
                            <span class="text-xs text-gray-600">${label}</span>
                            <span class="text-xs font-medium px-2 py-0.5 rounded ${consultaConfig.badge}">${value.valor}</span>
                        </div>
                    `;
                });
            }

            card.className = `p-4 border-l-4 ${config.border} ${config.bg}`;
            card.innerHTML = `
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-sm font-mono font-semibold text-gray-900">${data.cnpj}</span>
                            <span class="text-xs font-medium px-2 py-0.5 rounded ${config.badge}">${data.situacao}</span>
                        </div>
                        <p class="text-sm text-gray-700">${data.razaoSocial}</p>
                    </div>
                    <button type="button" class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                        Ver detalhes
                    </button>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        ${consultasHtml}
                    </div>
                </div>
            `;

            return card;
        }

        // Inicializar
        updateSummary();

        console.log('[Validar XML] Inicializacao concluida');
    }

    // Expor globalmente para SPA
    window.initValidarXml = initValidarXml;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initValidarXml, { once: true });
    } else {
        initValidarXml();
    }
})();
</script>
