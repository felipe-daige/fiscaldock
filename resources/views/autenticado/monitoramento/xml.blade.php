{{-- Monitoramento - Importar XMLs --}}
<div class="min-h-screen bg-gray-50" id="monitoramento-xml-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Importar XMLs de Notas Fiscais</h1>
                    <p class="mt-1 text-sm text-gray-600">Adicione CNPJs a sua lista de monitoramento a partir de arquivos XML de NF-e, NFS-e ou CT-e.</p>
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
                        Importe XMLs de notas fiscais para extrair automaticamente os CNPJs de fornecedores e clientes. Os participantes serao adicionados a sua lista de monitoramento.
                    </p>
                </div>
            </div>
        </div>

        {{-- Upload Section --}}
        <div id="upload-section" class="mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Card Upload (Esquerdo) --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">Enviar Arquivos XML</h3>
                    </div>
                    <div class="p-6">
                        {{-- Selecao Tipo Documento --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Documento:</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 tipo-doc-label" data-tipo="NFE">
                                    <input type="radio" name="tipo-documento" value="NFE" class="mr-2 w-4 h-4 text-blue-600 flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-800 text-sm">NF-e</div>
                                        <div class="text-xs text-gray-500">Nota Fiscal</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 tipo-doc-label" data-tipo="NFSE">
                                    <input type="radio" name="tipo-documento" value="NFSE" class="mr-2 w-4 h-4 text-blue-600 flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-800 text-sm">NFS-e</div>
                                        <div class="text-xs text-gray-500">Servicos</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 tipo-doc-label" data-tipo="CTE">
                                    <input type="radio" name="tipo-documento" value="CTE" class="mr-2 w-4 h-4 text-blue-600 flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-800 text-sm">CT-e</div>
                                        <div class="text-xs text-gray-500">Transporte</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Selecao Modo de Envio --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Modo de Envio:</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-purple-400 modo-envio-label" data-modo="zip">
                                    <input type="radio" name="modo-envio" value="zip" class="mr-3 w-4 h-4 text-purple-600 flex-shrink-0">
                                    <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-800 text-sm">Arquivo ZIP</div>
                                            <div class="text-xs text-gray-500">Um ZIP com multiplas notas</div>
                                        </div>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 modo-envio-label" data-modo="xml">
                                    <input type="radio" name="modo-envio" value="xml" class="mr-3 w-4 h-4 text-blue-600 flex-shrink-0">
                                    <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-800 text-sm">XMLs Avulsos</div>
                                            <div class="text-xs text-gray-500">Varios arquivos XML</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Selecao de Cliente (Opcional) --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Cliente (sua empresa): <span class="text-gray-400 font-normal">(opcional)</span>
                            </label>
                            <select
                                id="cliente-select"
                                name="cliente_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                            >
                                <option value="">Importar emit e dest de todas as notas</option>
                                @foreach($clientes ?? [] as $cliente)
                                    <option value="{{ $cliente->id }}" data-cnpj="{{ $cliente->documento }}">
                                        {{ $cliente->razao_social ?? $cliente->nome }}
                                        ({{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cliente->documento) }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                Se selecionado, importa apenas o parceiro (fornecedor ou cliente) de cada nota.
                            </p>
                        </div>

                        {{-- Dropzone --}}
                        <div class="mb-4">
                            <div id="xml-dropzone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 min-h-[180px] flex flex-col items-center justify-center transition-colors cursor-not-allowed bg-gray-100 opacity-60 pointer-events-none" role="button" tabindex="0" aria-disabled="true">
                                <div class="mb-4" id="xml-dropzone-icon">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <div class="space-y-1 text-center">
                                    <p class="text-sm font-medium text-gray-500" id="xml-dropzone-main-text">Selecione o tipo e modo de envio</p>
                                    <p class="text-xs text-gray-400" id="xml-dropzone-sub-text">Escolha as opcoes acima para habilitar o envio</p>
                                    <p class="text-xs text-gray-400 mt-2">Max: 50MB/arquivo | 200MB total</p>
                                </div>
                                <input
                                    type="file"
                                    id="xml-file-input"
                                    name="xml_files"
                                    accept=".xml,.zip"
                                    multiple
                                    class="hidden"
                                    disabled
                                >
                            </div>
                        </div>

                        {{-- Lista de Arquivos --}}
                        <div id="xml-files-list" class="mb-4 hidden">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Arquivos selecionados:</span>
                                <button type="button" id="xml-clear-all" class="text-xs text-red-600 hover:text-red-700">Limpar todos</button>
                            </div>
                            <div id="xml-files-container" class="space-y-2 max-h-[200px] overflow-y-auto">
                                {{-- Files will be added here --}}
                            </div>
                            <div class="mt-2 pt-2 border-t border-gray-200 flex items-center justify-between text-xs text-gray-500">
                                <span id="xml-files-count">0 arquivos</span>
                                <span id="xml-files-size">0 MB</span>
                            </div>
                        </div>

                        {{-- Error Message --}}
                        <div id="xml-error-message" class="mb-4 hidden">
                            <div class="flex items-start gap-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <svg class="w-4 h-4 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-xs text-red-800" id="xml-error-text"></p>
                            </div>
                        </div>

                        {{-- Botao Importar --}}
                        <div class="flex justify-end">
                            <button
                                type="button"
                                id="xml-importar-btn"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                <span class="btn-text">Importar</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Card Informacoes (Direito) --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="text-base font-semibold text-gray-900">Informacoes</h3>
                        </div>
                    </div>
                    <div class="p-6 space-y-6">
                        {{-- Secao Como Funciona --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Como Funciona</h4>
                            <div class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">1</div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Selecione o Tipo</p>
                                        <p class="text-xs text-gray-500">Escolha entre NF-e, NFS-e ou CT-e.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">2</div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Escolha o Modo</p>
                                        <p class="text-xs text-gray-500">ZIP com multiplas notas ou XMLs avulsos.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">3</div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Envie os Arquivos</p>
                                        <p class="text-xs text-gray-500">Arraste ou clique para selecionar.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">4</div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Extracao Automatica</p>
                                        <p class="text-xs text-gray-500">O sistema extrai CNPJs do emitente e destinatario de cada nota.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold">5</div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Monitoramento</p>
                                        <p class="text-xs text-gray-500">Configure alertas e consultas periodicas para os participantes importados.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Secao Deduplicacao --}}
                        <div class="pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Deduplicacao Inteligente</h4>
                            <div class="space-y-2 text-xs text-gray-600">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Notas com mesma chave de acesso sao processadas apenas uma vez</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>CNPJs duplicados atualizam dados existentes</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>CPFs sao registrados mas nao entram em monitoramento</span>
                                </div>
                            </div>
                        </div>

                        {{-- Formatos Aceitos --}}
                        <div class="pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Formatos Aceitos</h4>
                            <div class="space-y-3">
                                <div class="flex items-start gap-3 p-3 bg-purple-50 rounded-lg border border-purple-100">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-purple-900">Arquivo ZIP</p>
                                        <p class="text-xs text-purple-700">Ate 5.000 XMLs dentro de um unico ZIP</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-blue-900">XMLs Avulsos</p>
                                        <p class="text-xs text-blue-700">Ate 100 arquivos XML individuais</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Limites --}}
                        <div class="pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Limites</h4>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="p-2 bg-gray-50 rounded-lg text-center">
                                    <span class="font-semibold text-gray-900">50 MB</span>
                                    <span class="text-gray-500 block">por arquivo</span>
                                </div>
                                <div class="p-2 bg-gray-50 rounded-lg text-center">
                                    <span class="font-semibold text-gray-900">200 MB</span>
                                    <span class="text-gray-500 block">total</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Secao de Progresso (inicialmente oculta) --}}
        <div id="importacao-progresso" class="hidden">
            <div id="progresso-card" class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                {{-- Header --}}
                <div class="flex items-start gap-3 mb-4">
                    <div id="progresso-icon" class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 id="progresso-titulo" class="font-semibold text-gray-900 truncate">
                            Processando XMLs...
                        </h3>
                        <p id="progresso-subtitulo" class="text-sm text-gray-500">
                            Aguarde enquanto os arquivos sao processados.
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

                {{-- Stats em tempo real --}}
                <div id="progresso-stats" class="grid grid-cols-4 gap-3 mb-4 hidden">
                    <div class="text-center p-2 bg-gray-50 rounded-lg">
                        <p class="text-lg font-bold text-gray-900" id="stat-xmls-processados">0</p>
                        <p class="text-xs text-gray-500">Processados</p>
                    </div>
                    <div class="text-center p-2 bg-green-50 rounded-lg">
                        <p class="text-lg font-bold text-green-600" id="stat-participantes-novos">0</p>
                        <p class="text-xs text-gray-500">Novos</p>
                    </div>
                    <div class="text-center p-2 bg-blue-50 rounded-lg">
                        <p class="text-lg font-bold text-blue-600" id="stat-participantes-atualizados">0</p>
                        <p class="text-xs text-gray-500">Atualizados</p>
                    </div>
                    <div class="text-center p-2 bg-red-50 rounded-lg">
                        <p class="text-lg font-bold text-red-600" id="stat-erros">0</p>
                        <p class="text-xs text-gray-500">Erros</p>
                    </div>
                </div>

                {{-- Secao de Erro --}}
                <div id="progresso-erro" class="hidden pt-3 border-t border-red-100">
                    <p id="progresso-erro-msg" class="text-sm text-gray-700 mb-3">
                        Ocorreu um erro durante o processamento.
                    </p>
                    <div class="flex gap-3">
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

            {{-- Secao de Resultados (aparece apos importacao concluida) --}}
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
                                    <h3 class="font-semibold text-gray-900">Importacao Concluida</h3>
                                    <p class="text-sm text-gray-600" id="resultado-resumo">-</p>
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
                                Nova Importacao
                            </button>
                        </div>
                    </div>

                    {{-- Estatisticas --}}
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-2xl font-bold text-gray-900" id="resultado-xmls">0</p>
                                <p class="text-xs text-gray-500">XMLs Processados</p>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <p class="text-2xl font-bold text-green-600" id="resultado-novos">0</p>
                                <p class="text-xs text-gray-500">Novos Participantes</p>
                            </div>
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <p class="text-2xl font-bold text-blue-600" id="resultado-atualizados">0</p>
                                <p class="text-xs text-gray-500">Atualizados</p>
                            </div>
                            <div class="text-center p-3 bg-amber-50 rounded-lg">
                                <p class="text-2xl font-bold text-amber-600" id="resultado-ignorados">0</p>
                                <p class="text-xs text-gray-500">Ignorados/Erros</p>
                            </div>
                        </div>
                    </div>

                    {{-- Erros detalhados (se houver) --}}
                    <div id="resultado-erros-container" class="hidden px-6 py-4 border-b border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Erros encontrados:</h4>
                        <div id="resultado-erros-lista" class="max-h-[150px] overflow-y-auto space-y-1 text-xs">
                            {{-- Erros serao listados aqui --}}
                        </div>
                    </div>

                    {{-- Acoes --}}
                    <div class="px-6 py-4 bg-gray-50">
                        <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                            <a
                                href="/app/monitoramento"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm hover:bg-blue-700 transition"
                                data-link
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                Ver Participantes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    function initMonitoramentoXml() {
        const container = document.getElementById('monitoramento-xml-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Monitoramento XML] Inicializando...');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Identificador unico por aba
        const tabId = crypto.randomUUID ? crypto.randomUUID() :
            (Date.now().toString(36) + Math.random().toString(36).substr(2));

        // Elementos
        const dropzone = document.getElementById('xml-dropzone');
        const fileInput = document.getElementById('xml-file-input');
        const filesList = document.getElementById('xml-files-list');
        const filesContainer = document.getElementById('xml-files-container');
        const filesCount = document.getElementById('xml-files-count');
        const filesSize = document.getElementById('xml-files-size');
        const clearAllBtn = document.getElementById('xml-clear-all');
        const importarBtn = document.getElementById('xml-importar-btn');
        const errorMessage = document.getElementById('xml-error-message');
        const errorText = document.getElementById('xml-error-text');
        const tipoDocRadios = document.querySelectorAll('input[name="tipo-documento"]');
        const modoEnvioRadios = document.querySelectorAll('input[name="modo-envio"]');
        const uploadSection = document.getElementById('upload-section');
        const progressoContainer = document.getElementById('importacao-progresso');

        // Limites
        const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB
        const MAX_TOTAL_SIZE = 200 * 1024 * 1024; // 200MB
        const MAX_FILES = 100;

        // Estado
        // File object structure: { file: File, status: 'pending'|'validating'|'valid'|'error', totalXmls: number|null, tipoDoc: string|null, error: string|null }
        let selectedFiles = [];
        let eventSource = null;
        let importacaoEmAndamento = false;

        // Funcao para obter tipo de documento selecionado
        function getSelectedTipoDoc() {
            const selected = Array.from(tipoDocRadios).find(radio => radio.checked);
            return selected ? selected.value : '';
        }

        // Funcao para obter modo de envio selecionado
        function getSelectedModoEnvio() {
            const selected = Array.from(modoEnvioRadios).find(radio => radio.checked);
            return selected ? selected.value : '';
        }

        // Atualizar visual dos labels de tipo documento
        function updateTipoDocLabels() {
            const selectedValue = getSelectedTipoDoc();
            document.querySelectorAll('.tipo-doc-label').forEach(function(label) {
                const radio = label.querySelector('input[type="radio"]');
                if (radio && radio.value === selectedValue) {
                    label.classList.remove('border-gray-300', 'hover:border-blue-400');
                    label.classList.add('border-blue-600', 'bg-blue-50');
                } else {
                    label.classList.remove('border-blue-600', 'bg-blue-50');
                    label.classList.add('border-gray-300', 'hover:border-blue-400');
                }
            });
        }

        // Atualizar visual dos labels de modo de envio
        function updateModoEnvioLabels() {
            const selectedValue = getSelectedModoEnvio();
            document.querySelectorAll('.modo-envio-label').forEach(function(label) {
                const radio = label.querySelector('input[type="radio"]');
                const isZip = label.dataset.modo === 'zip';
                if (radio && radio.value === selectedValue) {
                    label.classList.remove('border-gray-300', isZip ? 'hover:border-purple-400' : 'hover:border-blue-400');
                    label.classList.add(isZip ? 'border-purple-600' : 'border-blue-600', isZip ? 'bg-purple-50' : 'bg-blue-50');
                } else {
                    label.classList.remove('border-purple-600', 'border-blue-600', 'bg-purple-50', 'bg-blue-50');
                    label.classList.add('border-gray-300', isZip ? 'hover:border-purple-400' : 'hover:border-blue-400');
                }
            });
        }

        // Atualizar estado do dropzone
        function updateDropzoneState() {
            const hasTipoDoc = getSelectedTipoDoc() !== '';
            const modoEnvio = getSelectedModoEnvio();
            const hasModoEnvio = modoEnvio !== '';
            const isReady = hasTipoDoc && hasModoEnvio;
            const mainText = document.getElementById('xml-dropzone-main-text');
            const subText = document.getElementById('xml-dropzone-sub-text');
            const iconContainer = document.getElementById('xml-dropzone-icon');

            if (dropzone && fileInput) {
                if (isReady) {
                    // Habilitar dropzone
                    dropzone.classList.remove('border-gray-300', 'bg-gray-100', 'opacity-60', 'cursor-not-allowed', 'pointer-events-none');
                    dropzone.setAttribute('aria-disabled', 'false');
                    fileInput.disabled = false;

                    // Configurar accept e multiple baseado no modo
                    if (modoEnvio === 'zip') {
                        fileInput.accept = '.zip';
                        fileInput.multiple = false;
                        dropzone.classList.add('border-purple-300', 'bg-purple-50', 'hover:border-purple-400', 'hover:bg-purple-100', 'cursor-pointer');

                        if (iconContainer) {
                            iconContainer.innerHTML = '<svg class="mx-auto h-12 w-12 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>';
                        }
                        if (mainText) {
                            mainText.textContent = 'Arraste seu arquivo ZIP aqui';
                            mainText.classList.remove('text-gray-500');
                            mainText.classList.add('text-purple-700', 'font-medium');
                        }
                        if (subText) {
                            subText.textContent = 'ou clique para selecionar (1 arquivo ZIP)';
                        }
                    } else {
                        fileInput.accept = '.xml';
                        fileInput.multiple = true;
                        dropzone.classList.add('border-blue-300', 'bg-blue-50', 'hover:border-blue-400', 'hover:bg-blue-100', 'cursor-pointer');

                        if (iconContainer) {
                            iconContainer.innerHTML = '<svg class="mx-auto h-12 w-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>';
                        }
                        if (mainText) {
                            mainText.textContent = 'Arraste seus arquivos XML aqui';
                            mainText.classList.remove('text-gray-500');
                            mainText.classList.add('text-blue-700', 'font-medium');
                        }
                        if (subText) {
                            subText.textContent = 'ou clique para selecionar (multiplos arquivos)';
                        }
                    }
                } else {
                    // Desabilitar dropzone
                    dropzone.classList.remove('border-purple-300', 'border-blue-300', 'bg-purple-50', 'bg-blue-50',
                        'hover:border-purple-400', 'hover:border-blue-400', 'hover:bg-purple-100', 'hover:bg-blue-100', 'cursor-pointer');
                    dropzone.classList.add('border-gray-300', 'bg-gray-100', 'opacity-60', 'cursor-not-allowed', 'pointer-events-none');
                    dropzone.setAttribute('aria-disabled', 'true');
                    fileInput.disabled = true;
                    fileInput.accept = '.xml,.zip';
                    fileInput.multiple = true;

                    if (iconContainer) {
                        iconContainer.innerHTML = '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>';
                    }
                    if (mainText) {
                        mainText.textContent = 'Selecione o tipo e modo de envio';
                        mainText.classList.remove('text-purple-700', 'text-blue-700', 'font-medium');
                        mainText.classList.add('text-gray-500');
                    }
                    if (subText) {
                        subText.textContent = 'Escolha as opcoes acima para habilitar o envio';
                    }
                }
            }
        }

        // Atualizar botao importar
        function updateImportButtonState() {
            const hasTipoDoc = getSelectedTipoDoc() !== '';
            const hasModoEnvio = getSelectedModoEnvio() !== '';
            const hasFiles = selectedFiles.length > 0;
            const validFiles = selectedFiles.filter(f => f.status === 'valid').length;
            const isValidating = selectedFiles.some(f => f.status === 'validating');
            const totalXmls = selectedFiles.reduce((sum, f) => sum + (f.totalXmls || 0), 0);

            if (importarBtn) {
                // Disable if: no tipo doc, no modo envio, no files, still validating, or no valid files
                importarBtn.disabled = !(hasTipoDoc && hasModoEnvio && hasFiles && validFiles > 0 && !isValidating);
                const btnText = importarBtn.querySelector('.btn-text');
                if (btnText) {
                    if (isValidating) {
                        btnText.textContent = 'Validando...';
                    } else if (totalXmls > 0) {
                        btnText.textContent = 'Importar ' + totalXmls + ' doc' + (totalXmls > 1 ? 's' : '');
                    } else if (hasFiles) {
                        btnText.textContent = 'Importar ' + selectedFiles.length + ' arquivo(s)';
                    } else {
                        btnText.textContent = 'Importar';
                    }
                }
            }
        }

        // Mostrar erro
        function mostrarErro(mensagem) {
            if (errorText) errorText.textContent = mensagem;
            if (errorMessage) errorMessage.classList.remove('hidden');
        }

        // Ocultar erro
        function ocultarErro() {
            if (errorMessage) errorMessage.classList.add('hidden');
        }

        // Formatar tamanho
        function formatSize(bytes) {
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
            if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
            return bytes + ' bytes';
        }

        // Validar tipo de arquivo (verificacao local rapida)
        function validarTipoArquivo(file) {
            const fileName = file.name.toLowerCase();
            const isValid = fileName.endsWith('.xml') || fileName.endsWith('.zip');

            if (!isValid) {
                return { valid: false, error: 'Tipo de arquivo nao permitido: ' + file.name };
            }

            if (file.size > MAX_FILE_SIZE) {
                return { valid: false, error: 'Arquivo muito grande: ' + file.name + ' (' + formatSize(file.size) + ')' };
            }

            return { valid: true };
        }

        // Converter File para base64
        function fileToBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => {
                    const base64 = reader.result.split(',')[1];
                    resolve(base64);
                };
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        }

        // Validar arquivo via API (conta XMLs em ZIPs, detecta tipo)
        async function validarArquivoApi(fileObj, index) {
            fileObj.status = 'validating';
            renderFilesList();
            updateImportButtonState();

            try {
                const base64 = await fileToBase64(fileObj.file);

                const response = await fetch('/app/monitoramento/xml/validar', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        arquivo: {
                            nome: fileObj.file.name,
                            conteudo_base64: base64
                        }
                    })
                });

                const data = await response.json();

                if (data.success) {
                    fileObj.status = 'valid';
                    fileObj.totalXmls = data.total_xmls || 1;
                    fileObj.tipoDoc = data.tipo_documento || null;

                    // Warn if ZIP has 0 XMLs
                    if (data.tipo === 'zip' && data.total_xmls === 0) {
                        fileObj.error = null; // Still valid but will show 0 XMLs
                    }
                } else {
                    fileObj.status = 'error';
                    fileObj.error = data.error || 'Erro na validacao';
                    fileObj.totalXmls = 0;
                }
            } catch (err) {
                console.error('[Monitoramento XML] Erro ao validar arquivo:', err);
                fileObj.status = 'error';
                fileObj.error = 'Erro de conexao';
                fileObj.totalXmls = 0;
            }

            renderFilesList();
            updateImportButtonState();
        }

        // Renderizar lista de arquivos
        function renderFilesList() {
            if (!filesContainer) return;

            if (selectedFiles.length === 0) {
                filesList.classList.add('hidden');
                return;
            }

            filesList.classList.remove('hidden');
            filesContainer.innerHTML = '';

            let totalSize = 0;
            let totalXmls = 0;

            selectedFiles.forEach((fileObj, index) => {
                const file = fileObj.file;
                totalSize += file.size;
                if (fileObj.status === 'valid') {
                    totalXmls += fileObj.totalXmls || 0;
                }

                const div = document.createElement('div');
                const isError = fileObj.status === 'error';
                div.className = 'flex items-center justify-between p-2 rounded-lg border ' +
                    (isError ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200');

                // Build status indicator HTML
                let statusHtml = '';
                if (fileObj.status === 'validating') {
                    statusHtml = `
                        <svg class="w-4 h-4 text-blue-500 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>`;
                } else if (fileObj.status === 'valid') {
                    const isZip = file.name.toLowerCase().endsWith('.zip');
                    if (isZip) {
                        const xmlCount = fileObj.totalXmls || 0;
                        if (xmlCount === 0) {
                            statusHtml = `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700 flex-shrink-0">0 XMLs</span>`;
                        } else {
                            statusHtml = `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 flex-shrink-0">${xmlCount} XML${xmlCount > 1 ? 's' : ''}</span>`;
                        }
                    } else if (fileObj.tipoDoc) {
                        const tipoLabels = { 'NFE': 'NF-e', 'NFSE': 'NFS-e', 'CTE': 'CT-e' };
                        statusHtml = `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 flex-shrink-0">${tipoLabels[fileObj.tipoDoc] || fileObj.tipoDoc}</span>`;
                    } else {
                        statusHtml = `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700 flex-shrink-0">XML</span>`;
                    }
                } else if (fileObj.status === 'error') {
                    statusHtml = `<span class="text-xs text-red-600 font-medium flex-shrink-0">${fileObj.error || 'Erro'}</span>`;
                }

                // Determine file icon based on type
                const isZipFile = file.name.toLowerCase().endsWith('.zip');
                let fileIconHtml = '';
                if (isError) {
                    fileIconHtml = `<svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>`;
                } else if (isZipFile) {
                    // Archive/ZIP icon
                    fileIconHtml = `<svg class="w-4 h-4 text-purple-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>`;
                } else {
                    // XML document icon
                    fileIconHtml = `<svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>`;
                }

                div.innerHTML = `
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        ${fileIconHtml}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium ${isError ? 'text-red-800' : 'text-gray-800'} truncate">${file.name}</span>
                                ${statusHtml}
                            </div>
                            <div class="text-xs text-gray-500">${formatSize(file.size)}</div>
                        </div>
                    </div>
                    <button type="button" class="remove-file ${isError ? 'text-red-500 hover:text-red-700' : 'text-gray-400 hover:text-red-500'} p-1 flex-shrink-0" data-index="${index}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;
                filesContainer.appendChild(div);
            });

            // Atualizar contadores
            const validCount = selectedFiles.filter(f => f.status === 'valid').length;
            if (filesCount) {
                if (totalXmls > 0) {
                    filesCount.textContent = selectedFiles.length + ' arquivo(s) · ' + totalXmls + ' XMLs';
                } else {
                    filesCount.textContent = selectedFiles.length + ' arquivo(s)';
                }
            }
            if (filesSize) filesSize.textContent = formatSize(totalSize);

            // Event listeners para remover
            filesContainer.querySelectorAll('.remove-file').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idx = parseInt(this.dataset.index);
                    selectedFiles.splice(idx, 1);
                    renderFilesList();
                    updateImportButtonState();
                });
            });
        }

        // Adicionar arquivos
        function addFiles(files) {
            ocultarErro();

            let totalSize = selectedFiles.reduce((sum, f) => sum + f.file.size, 0);
            let errors = [];
            const filesToValidate = [];

            for (const file of files) {
                // Verificar limite de arquivos
                if (selectedFiles.length >= MAX_FILES) {
                    errors.push('Limite de ' + MAX_FILES + ' arquivos atingido.');
                    break;
                }

                // Validar tipo de arquivo localmente
                const validation = validarTipoArquivo(file);
                if (!validation.valid) {
                    errors.push(validation.error);
                    continue;
                }

                // Verificar limite total
                if (totalSize + file.size > MAX_TOTAL_SIZE) {
                    errors.push('Limite de 200MB total excedido.');
                    break;
                }

                // Verificar duplicata
                const exists = selectedFiles.some(f => f.file.name === file.name && f.file.size === file.size);
                if (!exists) {
                    const fileObj = {
                        file: file,
                        status: 'pending',
                        totalXmls: null,
                        tipoDoc: null,
                        error: null
                    };
                    selectedFiles.push(fileObj);
                    filesToValidate.push(fileObj);
                    totalSize += file.size;
                }
            }

            if (errors.length > 0) {
                mostrarErro(errors[0]);
            }

            renderFilesList();
            updateImportButtonState();

            // Trigger validation for new files
            filesToValidate.forEach((fileObj, idx) => {
                const fileIndex = selectedFiles.indexOf(fileObj);
                validarArquivoApi(fileObj, fileIndex);
            });
        }

        // Limpar todos os arquivos
        function clearFiles() {
            selectedFiles = [];
            if (fileInput) fileInput.value = '';
            renderFilesList();
            updateImportButtonState();
            ocultarErro();
        }

        // Event listeners tipo documento
        tipoDocRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                updateTipoDocLabels();
                updateDropzoneState();
                updateImportButtonState();
            });
        });

        // Event listeners modo de envio
        modoEnvioRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                updateModoEnvioLabels();
                // Limpar arquivos ao trocar modo (ZIP vs XML sao incompativeis)
                clearFiles();
                updateDropzoneState();
                updateImportButtonState();
            });
        });

        // Dropzone click
        if (dropzone && fileInput) {
            dropzone.addEventListener('click', function() {
                if (!fileInput.disabled) fileInput.click();
            });

            dropzone.addEventListener('dragover', function(e) {
                if (fileInput.disabled) return;
                e.preventDefault();
                dropzone.classList.remove('border-gray-300', 'bg-gray-50');
                dropzone.classList.add('border-blue-500', 'bg-blue-50');
            });

            dropzone.addEventListener('dragleave', function() {
                if (fileInput.disabled) return;
                dropzone.classList.remove('border-blue-500', 'bg-blue-50');
                dropzone.classList.add('border-gray-300', 'bg-gray-50');
            });

            dropzone.addEventListener('drop', function(e) {
                if (fileInput.disabled) return;
                e.preventDefault();
                dropzone.classList.remove('border-blue-500', 'bg-blue-50');
                dropzone.classList.add('border-gray-300', 'bg-gray-50');

                if (e.dataTransfer?.files) {
                    addFiles(Array.from(e.dataTransfer.files));
                }
            });
        }

        // File input change
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                if (e.target.files) {
                    addFiles(Array.from(e.target.files));
                }
            });
        }

        // Clear all
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', clearFiles);
        }

        // Elementos de progresso
        const barraProgresso = document.getElementById('barra-progresso');
        const progressoPorcentagem = document.getElementById('progresso-porcentagem');
        const progressoMensagem = document.getElementById('progresso-mensagem');
        const progressoTitulo = document.getElementById('progresso-titulo');
        const progressoIcon = document.getElementById('progresso-icon');
        const progressoStats = document.getElementById('progresso-stats');
        const progressoErro = document.getElementById('progresso-erro');
        const progressoErroMsg = document.getElementById('progresso-erro-msg');
        const resultadoContainer = document.getElementById('resultado-importacao');

        // Atualizar icone de status
        function atualizarIconeStatus(status) {
            const card = document.getElementById('progresso-card');
            if (!progressoIcon || !card) return;

            card.className = 'bg-white border rounded-lg p-4 shadow-sm';

            switch (status) {
                case 'concluido':
                    progressoIcon.className = 'w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0';
                    progressoIcon.innerHTML = '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                    card.classList.add('border-green-200');
                    if (barraProgresso) barraProgresso.className = 'bg-green-600 h-full rounded-full transition-all duration-500 ease-out';
                    if (progressoErro) progressoErro.classList.add('hidden');
                    break;
                case 'erro':
                case 'timeout':
                    progressoIcon.className = 'w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0';
                    progressoIcon.innerHTML = '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                    card.classList.add('border-red-200');
                    if (barraProgresso) barraProgresso.className = 'bg-red-600 h-full rounded-full transition-all duration-500 ease-out';
                    if (progressoErro) progressoErro.classList.remove('hidden');
                    break;
                default:
                    progressoIcon.className = 'w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0';
                    progressoIcon.innerHTML = '<svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>';
                    card.classList.add('border-gray-200');
                    if (barraProgresso) barraProgresso.className = 'bg-blue-600 h-full rounded-full transition-all duration-500 ease-out';
                    if (progressoErro) progressoErro.classList.add('hidden');
            }
        }

        // Atualizar progresso
        function atualizarProgresso(payload) {
            const dados = payload.dados || {};
            const progresso = parseInt(payload.progresso) || 0;
            const status = payload.status || 'processando';
            const mensagem = payload.mensagem || 'Processando...';

            if (barraProgresso) barraProgresso.style.width = progresso + '%';
            if (progressoPorcentagem) progressoPorcentagem.textContent = progresso + '%';
            if (progressoMensagem) progressoMensagem.textContent = mensagem;

            // Stats
            if (dados.total_xmls !== undefined) {
                if (progressoStats) progressoStats.classList.remove('hidden');
                const statXmls = document.getElementById('stat-xmls-processados');
                const statNovos = document.getElementById('stat-participantes-novos');
                const statAtualizados = document.getElementById('stat-participantes-atualizados');
                const statErros = document.getElementById('stat-erros');

                if (statXmls) statXmls.textContent = dados.xmls_processados || 0;
                if (statNovos) statNovos.textContent = dados.participantes_novos || 0;
                if (statAtualizados) statAtualizados.textContent = dados.participantes_atualizados || 0;
                if (statErros) statErros.textContent = (dados.erros && dados.erros.length) || 0;
            }

            atualizarIconeStatus(status);

            if (status === 'erro' && progressoErroMsg) {
                progressoErroMsg.textContent = payload.error_message || payload.mensagem || 'Erro durante o processamento.';
            }
        }

        // Mostrar progresso
        function mostrarProgresso() {
            if (progressoContainer) progressoContainer.classList.remove('hidden');
            if (uploadSection) uploadSection.classList.add('hidden');
        }

        // Ocultar progresso e voltar ao upload
        function voltarAoUpload() {
            if (progressoContainer) progressoContainer.classList.add('hidden');
            if (uploadSection) uploadSection.classList.remove('hidden');
            if (resultadoContainer) resultadoContainer.classList.add('hidden');
        }

        // Resetar progresso
        function resetarProgresso() {
            if (barraProgresso) {
                barraProgresso.style.width = '0%';
                barraProgresso.className = 'bg-blue-600 h-full rounded-full transition-all duration-500 ease-out';
            }
            if (progressoPorcentagem) progressoPorcentagem.textContent = '0%';
            if (progressoMensagem) progressoMensagem.textContent = 'Iniciando...';
            if (progressoTitulo) progressoTitulo.textContent = 'Processando XMLs...';
            if (progressoStats) progressoStats.classList.add('hidden');
            if (progressoErro) progressoErro.classList.add('hidden');
            if (resultadoContainer) resultadoContainer.classList.add('hidden');
            atualizarIconeStatus('processando');
        }

        // Mostrar resultados
        function mostrarResultados(dados) {
            if (!resultadoContainer) return;

            const resumo = document.getElementById('resultado-resumo');
            const xmls = document.getElementById('resultado-xmls');
            const novos = document.getElementById('resultado-novos');
            const atualizados = document.getElementById('resultado-atualizados');
            const ignorados = document.getElementById('resultado-ignorados');
            const errosContainer = document.getElementById('resultado-erros-container');
            const errosLista = document.getElementById('resultado-erros-lista');

            if (resumo) resumo.textContent = (dados.xmls_processados || 0) + ' XMLs processados';
            if (xmls) xmls.textContent = dados.xmls_processados || 0;
            if (novos) novos.textContent = dados.participantes_novos || 0;
            if (atualizados) atualizados.textContent = dados.participantes_atualizados || 0;
            if (ignorados) ignorados.textContent = (dados.participantes_ignorados || 0) + ((dados.erros && dados.erros.length) || 0);

            // Erros detalhados
            if (dados.erros && dados.erros.length > 0 && errosContainer && errosLista) {
                errosContainer.classList.remove('hidden');
                errosLista.innerHTML = dados.erros.map(e =>
                    '<div class="p-2 bg-red-50 rounded text-red-700">' +
                    '<span class="font-medium">' + (e.arquivo || 'XML') + ':</span> ' +
                    (e.motivo || 'Erro desconhecido') +
                    '</div>'
                ).join('');
            } else if (errosContainer) {
                errosContainer.classList.add('hidden');
            }

            resultadoContainer.classList.remove('hidden');
            resultadoContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Conectar SSE
        function conectarSSE() {
            if (eventSource) eventSource.close();

            const sseUrl = '/app/monitoramento/xml/progresso/stream?tab_id=' + encodeURIComponent(tabId);
            console.log('[Monitoramento XML] Conectando ao SSE:', sseUrl);
            eventSource = new EventSource(sseUrl);

            eventSource.onopen = function() {
                console.log('[Monitoramento XML] SSE conectado');
            };

            eventSource.onmessage = function(event) {
                try {
                    const dados = JSON.parse(event.data);
                    console.log('[Monitoramento XML] Dados SSE:', dados);
                    atualizarProgresso(dados);

                    if (dados.status === 'concluido') {
                        eventSource.close();
                        eventSource = null;
                        importacaoEmAndamento = false;

                        if (window.showToast) {
                            window.showToast(dados.mensagem || 'Importacao concluida!', 'success');
                        }

                        mostrarResultados(dados.dados || {});
                    } else if (dados.status === 'erro' || dados.status === 'timeout') {
                        eventSource.close();
                        eventSource = null;
                        importacaoEmAndamento = false;
                    }
                } catch (e) {
                    console.error('[Monitoramento XML] Erro ao parsear SSE:', e);
                }
            };

            eventSource.onerror = function(err) {
                console.error('[Monitoramento XML] Erro SSE:', err);
                eventSource.close();
                eventSource = null;

                if (importacaoEmAndamento) {
                    importacaoEmAndamento = false;
                    atualizarProgresso({
                        status: 'erro',
                        progresso: 0,
                        mensagem: 'Erro na conexao',
                        error_message: 'Erro na conexao com o servidor.'
                    });
                }
            };
        }

        // Botao importar
        if (importarBtn) {
            importarBtn.addEventListener('click', async function() {
                const tipoDoc = getSelectedTipoDoc();
                if (!tipoDoc) {
                    if (window.showToast) window.showToast('Selecione o tipo de documento.', 'error');
                    return;
                }

                const modoEnvioSelecionado = getSelectedModoEnvio();
                if (!modoEnvioSelecionado) {
                    if (window.showToast) window.showToast('Selecione o modo de envio.', 'error');
                    return;
                }

                if (selectedFiles.length === 0) {
                    if (window.showToast) window.showToast('Selecione ao menos um arquivo.', 'error');
                    return;
                }

                if (importacaoEmAndamento) {
                    if (window.showToast) window.showToast('Aguarde a importacao em andamento.', 'warning');
                    return;
                }

                // Desabilitar botao
                importarBtn.disabled = true;
                const btnText = importarBtn.querySelector('.btn-text');
                if (btnText) btnText.textContent = 'Enviando...';

                try {
                    // Filter only valid files
                    const validFileObjs = selectedFiles.filter(f => f.status === 'valid');

                    if (validFileObjs.length === 0) {
                        throw new Error('Nenhum arquivo valido para importar.');
                    }

                    // Converter arquivos para base64
                    const arquivos = await Promise.all(validFileObjs.map(async fileObj => {
                        const file = fileObj.file;
                        const buffer = await file.arrayBuffer();
                        const base64 = btoa(String.fromCharCode(...new Uint8Array(buffer)));
                        return {
                            nome: file.name,
                            tipo: file.type || (file.name.endsWith('.zip') ? 'application/zip' : 'application/xml'),
                            conteudo_base64: base64
                        };
                    }));

                    const clienteSelect = document.getElementById('cliente-select');
                    const modoEnvio = getSelectedModoEnvio();
                    const payload = {
                        tipo_documento: tipoDoc,
                        modo_envio: modoEnvio,
                        tab_id: tabId,
                        cliente_id: clienteSelect ? clienteSelect.value || null : null,
                        arquivos: arquivos
                    };

                    const response = await fetch('/app/monitoramento/xml/importar', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.error || data.message || 'Erro ao enviar arquivos');
                    }

                    console.log('[Monitoramento XML] Importacao iniciada:', data);

                    importacaoEmAndamento = true;
                    resetarProgresso();
                    mostrarProgresso();
                    conectarSSE();

                } catch (err) {
                    console.error('[Monitoramento XML] Erro:', err);
                    if (window.showToast) {
                        window.showToast('Erro: ' + err.message, 'error');
                    } else {
                        alert('Erro: ' + err.message);
                    }
                    importarBtn.disabled = false;
                    const totalXmls = selectedFiles.reduce((sum, f) => sum + (f.totalXmls || 0), 0);
                    if (btnText) btnText.textContent = totalXmls > 0 ? 'Importar ' + totalXmls + ' docs' : 'Importar';
                }
            });
        }

        // Botao tentar novamente
        const btnTentarNovamente = document.getElementById('btn-tentar-novamente');
        if (btnTentarNovamente) {
            btnTentarNovamente.addEventListener('click', function() {
                importacaoEmAndamento = false;
                if (eventSource) {
                    eventSource.close();
                    eventSource = null;
                }
                voltarAoUpload();
                resetarProgresso();
                updateImportButtonState();
            });
        }

        // Botao nova importacao
        const btnNovaImportacao = document.getElementById('btn-nova-importacao');
        if (btnNovaImportacao) {
            btnNovaImportacao.addEventListener('click', function() {
                importacaoEmAndamento = false;
                if (eventSource) {
                    eventSource.close();
                    eventSource = null;
                }
                clearFiles();
                voltarAoUpload();
                resetarProgresso();
            });
        }

        // Cleanup
        if (typeof window._cleanupFunctions === 'undefined') {
            window._cleanupFunctions = [];
        }
        window._cleanupFunctions.push(function() {
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }
        });

        console.log('[Monitoramento XML] Inicializado com tab_id:', tabId);
    }

    // Inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMonitoramentoXml);
    } else {
        initMonitoramentoXml();
    }

    // Expor para SPA
    window.initMonitoramentoXml = initMonitoramentoXml;
})();
</script>
