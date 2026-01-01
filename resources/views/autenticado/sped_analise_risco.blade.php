{{-- Análise de Risco SPED - Autenticado --}}
<div class="min-h-screen bg-gray-50" id="analise-risco-container">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">
                    Análise de Risco - SPED
                </h1>
                <p class="text-xs text-gray-500 mt-1">
                    Importe um SPED e analise o risco dos fornecedores
                </p>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        
        {{-- ETAPA ÚNICA: Configuração da Análise --}}
        <div id="etapa-upload" class="etapa">
            <div class="space-y-6">
                {{-- Grid: Card de Importação + Card de Resumo --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Card de Importação --}}
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-sm font-semibold text-gray-800 mb-5">Upload do Arquivo SPED</h2>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de SPED:</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-start p-3 border-2 border-blue-600 rounded-lg cursor-pointer bg-blue-50">
                                    <input type="radio" name="tipo-sped" value="efd-contrib" checked class="mt-1 mr-2 w-4 h-4 text-blue-600">
                                    <div>
                                        <div class="font-semibold text-gray-800 text-sm">EFD Contribuições</div>
                                        <div class="text-xs text-gray-600">PIS/COFINS</div>
                                    </div>
                                </label>
                                <label class="flex items-start p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400">
                                    <input type="radio" name="tipo-sped" value="efd-fiscal" class="mt-1 mr-2 w-4 h-4 text-blue-600">
                                    <div>
                                        <div class="font-semibold text-gray-800 text-sm">EFD Fiscal</div>
                                        <div class="text-xs text-gray-600">ICMS/IPI</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div id="upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors cursor-pointer bg-gray-50 hover:bg-blue-50">
                                <div class="mb-2">
                                    <svg class="mx-auto h-10 w-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <p class="text-xs text-gray-700 mb-1">Arraste o arquivo SPED aqui</p>
                                <p class="text-xs text-gray-500">ou clique para selecionar</p>
                                <p class="text-xs text-gray-500 mt-1">.txt | Máximo: 5MB</p>
                                <input type="file" id="sped-file" name="sped_file" accept=".txt" class="hidden">
                            </div>
                        </div>

                        <div id="file-selected" class="mb-4 hidden">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <div>
                                        <div class="text-xs font-medium text-gray-800" id="file-name">arquivo.txt</div>
                                        <div class="text-xs text-gray-500" id="file-size">0 MB</div>
                                    </div>
                                </div>
                                <button type="button" id="remove-file" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Vincular a cliente (opcional):</label>
                            <select id="cliente-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione um cliente...</option>
                                <option value="1">Cliente Exemplo 1</option>
                                <option value="2">Cliente Exemplo 2</option>
                            </select>
                        </div>
                    </div>

                    {{-- Card de Resumo do Custo --}}
                    <div class="bg-white rounded-lg shadow-md p-6 h-fit lg:sticky lg:top-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">RESUMO DO CUSTO</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Consultas selecionadas:</span>
                                    <span class="font-semibold text-gray-800" id="resumo-consultas">3</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Custo por consulta:</span>
                                    <span class="font-semibold text-gray-800" id="resumo-custo-consulta">R$ 0,26</span>
                                </div>
                                <div class="border-t border-gray-300 pt-3 mt-3">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-gray-800">CUSTO TOTAL:</span>
                                        <span class="text-2xl font-bold text-amber-600" id="resumo-custo-total">R$ 0,78</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="executar-analise-btn" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center justify-center gap-2" disabled>
                            <span>Executar Análise</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Consultas Disponíveis --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-sm font-semibold text-gray-800 mb-5">SELECIONE AS CONSULTAS DESEJADAS</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="consultas-container">
                        {{-- Grupo 1: CADASTRO BÁSICO --}}
                        <div id="card-cadastro-basico" class="bg-white rounded-xl border border-gray-200 shadow-md" data-grupo="cadastro-basico">
                            <div class="flex items-center gap-3 p-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                                <input type="checkbox" class="grupo-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded" data-grupo="cadastro-basico">
                                <span class="text-lg">📋</span>
                                <h4 class="text-sm font-semibold text-gray-800 flex-1">CADASTRO BÁSICO</h4>
                                <span class="text-xs text-gray-600 grupo-contador" data-grupo="cadastro-basico">7 consultas</span>
                            </div>
                            <div class="p-3">
                                <div class="space-y-2">
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="cadastro-basico" data-orgao="receita" data-consulta="situacao-cnpj" data-preco="0.10" checked>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Situação CNPJ (Receita Federal)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="cadastro-basico" data-orgao="receita" data-consulta="qsa" data-preco="0.15">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Quadro Societário - QSA (Receita Federal)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="cadastro-basico" data-orgao="sefaz" data-consulta="inscricao-estadual" data-preco="0.08" checked>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Inscrição Estadual (SEFAZ)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="cadastro-basico" data-orgao="receita" data-consulta="simples" data-preco="0.08" checked>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Simples Nacional (Receita Federal)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="cadastro-basico" data-orgao="receita" data-consulta="cnae" data-preco="0.10">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">CNAE Principal/Secundários (Receita Federal)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="cadastro-basico" data-orgao="receita" data-consulta="data-abertura" data-preco="0.08">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Data de Abertura (Receita Federal)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="cadastro-basico" data-orgao="receita" data-consulta="capital-social" data-preco="0.08">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Capital Social (Receita Federal)</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Grupo 2: CERTIDÕES NEGATIVAS --}}
                        <div id="card-certidoes-negativas" class="bg-white rounded-xl border border-gray-200 shadow-md" data-grupo="certidoes-negativas">
                            <div class="flex items-center gap-3 p-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                                <input type="checkbox" class="grupo-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded" data-grupo="certidoes-negativas">
                                <span class="text-lg">📜</span>
                                <h4 class="text-sm font-semibold text-gray-800 flex-1">CERTIDÕES NEGATIVAS</h4>
                                <span class="text-xs text-gray-600 grupo-contador" data-grupo="certidoes-negativas">5 consultas</span>
                            </div>
                            <div class="p-3">
                                <div class="space-y-2">
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="certidoes-negativas" data-orgao="receita" data-consulta="cnd-federal" data-preco="0.15">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">CND Federal (PGFN)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="certidoes-negativas" data-orgao="sefaz" data-consulta="cnd-estadual" data-preco="0.12">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">CND Estadual (SEFAZ)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="certidoes-negativas" data-orgao="prefeitura" data-consulta="cnd-municipal" data-preco="0.12">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">CND Municipal (Prefeitura)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="certidoes-negativas" data-orgao="tst" data-consulta="cndt" data-preco="0.10">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">CND Trabalhista - CNDT (TST)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="certidoes-negativas" data-orgao="caixa" data-consulta="crf" data-preco="0.10">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Regularidade FGTS - CRF (Caixa)</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Grupo 3: LISTAS RESTRITIVAS --}}
                        <div id="card-listas-restritivas" class="bg-white rounded-xl border border-gray-200 shadow-md" data-grupo="listas-restritivas">
                            <div class="flex items-center gap-3 p-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                                <input type="checkbox" class="grupo-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded" data-grupo="listas-restritivas">
                                <span class="text-lg">⚠️</span>
                                <h4 class="text-sm font-semibold text-gray-800 flex-1">LISTAS RESTRITIVAS</h4>
                                <span class="text-xs text-gray-600 grupo-contador" data-grupo="listas-restritivas">5 consultas</span>
                            </div>
                            <div class="p-3">
                                <div class="space-y-2">
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="listas-restritivas" data-orgao="cgu" data-consulta="ceis" data-preco="0.10" checked>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">CEIS - Inidôneas e Suspensas (CGU)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="listas-restritivas" data-orgao="cgu" data-consulta="cnep" data-preco="0.10" checked>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">CNEP - Empresas Punidas (CGU)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="listas-restritivas" data-orgao="cgu" data-consulta="cepim" data-preco="0.10">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">CEPIM - Entidades Impedidas (CGU)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="listas-restritivas" data-orgao="mte" data-consulta="trabalho-escravo" data-preco="0.08" checked>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Lista Trabalho Escravo (MTE)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="listas-restritivas" data-orgao="cgu" data-consulta="acordo-leniencia" data-preco="0.10">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Acordo de Leniência (CGU)</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Grupo 4: RESTRIÇÕES FINANCEIRAS --}}
                        <div id="card-restricoes-financeiras" class="bg-white rounded-xl border border-gray-200 shadow-md" data-grupo="restricoes-financeiras">
                            <div class="flex items-center gap-3 p-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                                <input type="checkbox" class="grupo-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded" data-grupo="restricoes-financeiras">
                                <span class="text-lg">💰</span>
                                <h4 class="text-sm font-semibold text-gray-800 flex-1">RESTRIÇÕES FINANCEIRAS</h4>
                                <span class="text-xs text-gray-600 grupo-contador" data-grupo="restricoes-financeiras">2 consultas</span>
                            </div>
                            <div class="p-3">
                                <div class="space-y-2">
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="restricoes-financeiras" data-orgao="ieptb" data-consulta="protestos" data-preco="0.20">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Protestos (Cartórios/IEPTB)</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="restricoes-financeiras" data-orgao="pgfn" data-consulta="divida-ativa-federal" data-preco="0.15">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Dívida Ativa Federal (PGFN)</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Grupo 5: PROCESSOS E AÇÕES --}}
                        <div id="card-processos-acoes" class="bg-white rounded-xl border border-gray-200 shadow-md" data-grupo="processos-acoes">
                            <div class="flex items-center gap-3 p-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                                <input type="checkbox" class="grupo-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded" data-grupo="processos-acoes">
                                <span class="text-lg">⚖️</span>
                                <h4 class="text-sm font-semibold text-gray-800 flex-1">PROCESSOS E AÇÕES</h4>
                                <span class="text-xs text-gray-600 grupo-contador" data-grupo="processos-acoes">2 consultas</span>
                            </div>
                            <div class="p-3">
                                <div class="space-y-2">
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="processos-acoes" data-orgao="tcu" data-consulta="processos-tcu" data-preco="0.12">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Processos TCU</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="processos-acoes" data-orgao="tribunais" data-consulta="falencia-recuperacao" data-preco="0.15">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Falência/Recuperação Judicial</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Grupo 6: VALIDAÇÃO CRUZADA --}}
                        <div id="card-validacao-cruzada" class="bg-white rounded-xl border border-gray-200 shadow-md" data-grupo="validacao-cruzada">
                            <div class="flex items-center gap-3 p-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                                <input type="checkbox" class="grupo-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded" data-grupo="validacao-cruzada">
                                <span class="text-lg">🔗</span>
                                <h4 class="text-sm font-semibold text-gray-800 flex-1">VALIDAÇÃO CRUZADA</h4>
                                <span class="text-xs text-gray-600 grupo-contador" data-grupo="validacao-cruzada">4 consultas</span>
                            </div>
                            <div class="p-3">
                                <div class="space-y-2">
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="validacao-cruzada" data-orgao="interno" data-consulta="validacao-cnpj-registro" data-preco="0.05">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">CNPJ vs Registro 0150</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="validacao-cruzada" data-orgao="interno" data-consulta="validacao-ie-uf" data-preco="0.05">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">IE vs UF do Participante</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="validacao-cruzada" data-orgao="interno" data-consulta="validacao-cnae-cfop" data-preco="0.05">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">CNAE vs CFOP</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" class="consulta-checkbox mr-2 w-4 h-4 text-blue-600" data-grupo="validacao-cruzada" data-orgao="sefaz" data-consulta="validacao-participante-notas" data-preco="0.10">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-800">Participante vs Notas</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ETAPA RESULTADOS: Resultados --}}
        <div id="etapa-resultados" class="etapa hidden">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">RESULTADOS DA ANÁLISE</h2>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <div class="font-semibold text-green-800">Análise concluída com sucesso</div>
                            <div class="text-sm text-green-700">42 fornecedores analisados em 6 órgãos</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Resumo Geral --}}
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">RESUMO GERAL</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="text-2xl font-bold text-gray-800">42</div>
                        <div class="text-sm text-gray-600 mt-1">Total Fornecedores</div>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200">
                        <div class="text-2xl font-bold text-green-600">31</div>
                        <div class="text-sm text-green-700 mt-1 flex items-center justify-center gap-1">
                            <span>✅</span> Baixo Risco
                        </div>
                    </div>
                    <div class="text-center p-4 bg-amber-50 rounded-lg border border-amber-200">
                        <div class="text-2xl font-bold text-amber-600">7</div>
                        <div class="text-sm text-amber-700 mt-1 flex items-center justify-center gap-1">
                            <span>⚠️</span> Médio Risco
                        </div>
                    </div>
                    <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
                        <div class="text-2xl font-bold text-red-600">4</div>
                        <div class="text-sm text-red-700 mt-1 flex items-center justify-center gap-1">
                            <span>🔴</span> Alto Risco
                        </div>
                    </div>
                </div>
            </div>

            {{-- Órgãos Consultados --}}
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">ÓRGÃOS CONSULTADOS</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="text-sm font-semibold text-gray-800 mb-1">Receita Federal</div>
                        <div class="text-xs text-gray-600 mb-2">Situação CNPJ, Simples</div>
                        <div class="text-lg font-bold text-blue-600">✅ 42</div>
                    </div>
                    <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="text-sm font-semibold text-gray-800 mb-1">SEFAZ SINTEGRA</div>
                        <div class="text-xs text-gray-600 mb-2">Inscrição Estadual</div>
                        <div class="text-lg font-bold text-blue-600">✅ 42</div>
                    </div>
                    <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="text-sm font-semibold text-gray-800 mb-1">CGU</div>
                        <div class="text-xs text-gray-600 mb-2">CEIS/CNEP</div>
                        <div class="text-lg font-bold text-blue-600">✅ 42</div>
                    </div>
                    <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="text-sm font-semibold text-gray-800 mb-1">MTE</div>
                        <div class="text-xs text-gray-600 mb-2">Trab. Escravo</div>
                        <div class="text-lg font-bold text-blue-600">✅ 42</div>
                    </div>
                </div>
            </div>

            {{-- Exposição Financeira por Risco --}}
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">EXPOSIÇÃO FINANCEIRA POR RISCO</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span>✅</span>
                                <span class="font-medium text-gray-800">Baixo Risco</span>
                            </div>
                            <div class="text-sm font-semibold text-gray-800">R$ 850.000 (72%)</div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-green-500 h-3 rounded-full" style="width: 72%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span>⚠️</span>
                                <span class="font-medium text-gray-800">Médio Risco</span>
                            </div>
                            <div class="text-sm font-semibold text-gray-800">R$ 215.000 (18%)</div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-amber-500 h-3 rounded-full" style="width: 18%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span>🔴</span>
                                <span class="font-medium text-gray-800">Alto Risco</span>
                            </div>
                            <div class="text-sm font-semibold text-gray-800">R$ 118.500 (10%)</div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-red-500 h-3 rounded-full" style="width: 10%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alertas Críticos --}}
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <span>🚨</span>
                    ALERTAS CRÍTICOS (4 fornecedores)
                </h3>
                <div class="space-y-4" id="alertas-criticos">
                    <!-- Alertas serão renderizados aqui -->
                </div>
                <button type="button" id="ver-todos-alertas" class="mt-4 text-sm text-blue-600 hover:text-blue-800 font-medium">
                    Ver todos os 4 alertas →
                </button>
            </div>

            {{-- Lista Completa de Fornecedores --}}
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">LISTA COMPLETA DE FORNECEDORES</h3>
                
                <div class="mb-4 flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <input type="text" id="buscar-resultado" placeholder="Buscar..." class="w-full px-4 py-2.5 pl-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <select id="filtro-risco" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="todos">Todos</option>
                        <option value="baixo">Baixo Risco</option>
                        <option value="medio">Médio Risco</option>
                        <option value="alto">Alto Risco</option>
                        <option value="critico">Crítico</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="resultados-cards-container">
                    <!-- Cards serão renderizados aqui -->
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Mostrando <span id="pagina-inicio">1</span>-<span id="pagina-fim">10</span> de <span id="total-resultados">42</span>
                    </div>
                    <div class="flex gap-2" id="paginacao">
                        <!-- Paginação será renderizada aqui -->
                    </div>
                </div>
            </div>

            {{-- Ações --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">AÇÕES</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button type="button" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Baixar Relatório PDF
                    </button>
                    <button type="button" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Salvar Análise
                    </button>
                    <button type="button" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Enviar para Cliente
                    </button>
                    <button type="button" id="nova-analise-btn" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Nova Análise
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Detalhes do Fornecedor --}}
    <div id="modal-detalhes" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800" id="modal-titulo">DETALHES DO FORNECEDOR</h3>
                <button type="button" id="fechar-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6" id="modal-conteudo">
                <!-- Conteúdo do modal será renderizado aqui -->
            </div>
        </div>
    </div>
</div>

<script>
// Estado global
window.analiseRiscoState = {
    arquivoSelecionado: null,
    consultasSelecionadas: [],
    fornecedores: [],
    fornecedoresSelecionados: [],
    resultados: [],
    paginaAtual: 1,
    itensPorPagina: 10
};

// Dados mockados
const fornecedoresMock = [
    { id: 1, nome: 'Distribuidora ABC', cnpj: '11.222.333/0001-44', notas: 28, score: 35, risco: 'alto', valor: 89000 },
    { id: 2, nome: 'Atacado Premium', cnpj: '22.333.444/0001-55', notas: 15, score: 92, risco: 'baixo', valor: 234000 },
    { id: 3, nome: 'Comercial Brasil', cnpj: '33.444.555/0001-66', notas: 12, score: 88, risco: 'baixo', valor: 156000 },
    { id: 4, nome: 'Fornecedor XYZ', cnpj: '44.555.666/0001-77', notas: 8, score: 48, risco: 'medio', valor: 52000 },
    { id: 5, nome: 'Indústria 123', cnpj: '55.666.777/0001-88', notas: 6, score: 65, risco: 'medio', valor: 78000 },
    { id: 6, nome: 'Transportadora Fast', cnpj: '66.777.888/0001-99', notas: 5, score: 75, risco: 'medio', valor: 45000 },
    { id: 7, nome: 'Empresa Fantasma LTDA', cnpj: '12.345.678/0001-90', notas: 5, score: 8, risco: 'critico', valor: 67800, problemas: ['CNPJ Baixado', 'IE Cancelada', 'Lista CEIS'] },
    { id: 8, nome: 'Distribuidora Suspeita', cnpj: '98.765.432/0001-10', notas: 12, score: 22, risco: 'alto', valor: 145200, problemas: ['Empresa nova', 'IE Suspensa'] }
];

// Preencher lista completa com mais fornecedores
for (let i = 9; i <= 42; i++) {
    const scores = [85, 90, 95, 70, 75, 80, 55, 60, 25, 30, 35, 40];
    const riscos = ['baixo', 'baixo', 'baixo', 'medio', 'medio', 'medio', 'medio', 'medio', 'alto', 'alto', 'alto', 'alto'];
    const idx = (i - 9) % 12;
    fornecedoresMock.push({
        id: i,
        nome: `Fornecedor ${i}`,
        cnpj: `${String(i).padStart(2, '0')}.${String(i*3).padStart(3, '0')}.${String(i*5).padStart(3, '0')}/0001-${String(i*7).padStart(2, '0')}`,
        notas: Math.floor(Math.random() * 20) + 1,
        score: scores[idx],
        risco: riscos[idx],
        valor: Math.floor(Math.random() * 200000) + 10000
    });
}

window.analiseRiscoState.fornecedores = fornecedoresMock;
window.analiseRiscoState.fornecedoresSelecionados = fornecedoresMock.map(f => f.id);

// Função para validar se todos os campos estão preenchidos
function validarCampos() {
    const arquivoSelecionado = window.analiseRiscoState.arquivoSelecionado !== null;
    const tipoSpedSelecionado = document.querySelector('input[name="tipo-sped"]:checked') !== null;
    const consultasSelecionadas = document.querySelectorAll('.consulta-checkbox:checked').length > 0;
    
    return arquivoSelecionado && tipoSpedSelecionado && consultasSelecionadas;
}

// Função para atualizar estado do botão
function atualizarEstadoBotao() {
    const executarBtn = document.getElementById('executar-analise-btn');
    if (executarBtn) {
        executarBtn.disabled = !validarCampos();
    }
}

// Função para mostrar resultados
function mostrarResultados() {
    document.getElementById('etapa-upload').classList.add('hidden');
    document.getElementById('etapa-resultados').classList.remove('hidden');
    renderizarResultados();
}

// Upload de arquivo e inicialização
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('sped-file');
    const fileSelected = document.getElementById('file-selected');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const removeFile = document.getElementById('remove-file');
    const executarBtn = document.getElementById('executar-analise-btn');


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
        const files = Array.from(e.dataTransfer.files).filter(f => f.name.endsWith('.txt'));
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    uploadArea.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFile(e.target.files[0]);
        }
    });

    function handleFile(file) {
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            alert(`O arquivo excede o limite de 5MB`);
            return;
        }
        window.analiseRiscoState.arquivoSelecionado = file;
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        fileSelected.classList.remove('hidden');
        atualizarEstadoBotao();
    }

    removeFile.addEventListener('click', () => {
        window.analiseRiscoState.arquivoSelecionado = null;
        fileInput.value = '';
        fileSelected.classList.add('hidden');
        atualizarEstadoBotao();
    });

    // Tipo de SPED - atualizar validação
    document.querySelectorAll('input[name="tipo-sped"]').forEach(radio => {
        radio.addEventListener('change', () => {
            atualizarEstadoBotao();
        });
    });

    // Executar análise
    executarBtn.addEventListener('click', () => {
        if (validarCampos()) {
            mostrarResultados();
        }
    });

    document.getElementById('nova-analise-btn').addEventListener('click', () => {
        window.analiseRiscoState.arquivoSelecionado = null;
        document.getElementById('file-selected').classList.add('hidden');
        document.getElementById('sped-file').value = '';
        document.querySelector('input[name="tipo-sped"][value="efd-contrib"]').checked = true;
        document.querySelectorAll('.consulta-checkbox').forEach(cb => {
            cb.checked = false;
        });
        document.querySelectorAll('.consulta-checkbox[data-consulta="situacao-cnpj"]').forEach(cb => {
            cb.checked = true;
        });
        document.querySelectorAll('.consulta-checkbox[data-consulta="simples"]').forEach(cb => {
            cb.checked = true;
        });
        document.querySelectorAll('.consulta-checkbox[data-consulta="inscricao-estadual"]').forEach(cb => {
            cb.checked = true;
        });
        document.querySelectorAll('.consulta-checkbox[data-consulta="ceis"]').forEach(cb => {
            cb.checked = true;
        });
        document.querySelectorAll('.consulta-checkbox[data-consulta="cnep"]').forEach(cb => {
            cb.checked = true;
        });
        document.querySelectorAll('.consulta-checkbox[data-consulta="trabalho-escravo"]').forEach(cb => {
            cb.checked = true;
        });
        document.getElementById('etapa-resultados').classList.add('hidden');
        document.getElementById('etapa-upload').classList.remove('hidden');
        atualizarResumoCusto();
        atualizarEstadoBotao();
        atualizarGruposCheckboxes();
    });


    // Função para atualizar checkboxes dos grupos
    function atualizarGruposCheckboxes() {
        document.querySelectorAll('.grupo-checkbox').forEach(grupoCb => {
            const grupo = grupoCb.dataset.grupo;
            const checkboxes = document.querySelectorAll(`.consulta-checkbox[data-grupo="${grupo}"]`);
            const checked = document.querySelectorAll(`.consulta-checkbox[data-grupo="${grupo}"]:checked`);
            
            // Atualizar checkbox do grupo
            grupoCb.checked = checkboxes.length > 0 && checkboxes.length === checked.length;
            
            // Atualizar contador (apenas mostra total, não precisa atualizar dinamicamente)
            // O contador já está definido no HTML
        });
    }

    // Toggle grupo - selecionar/remover todas as consultas do grupo
    document.querySelectorAll('.grupo-checkbox').forEach(grupoCb => {
        grupoCb.addEventListener('change', () => {
            const grupo = grupoCb.dataset.grupo;
            const checkboxes = document.querySelectorAll(`.consulta-checkbox[data-grupo="${grupo}"]`);
            
            checkboxes.forEach(cb => {
                cb.checked = grupoCb.checked;
            });
            
            atualizarResumoCusto();
            atualizarEstadoBotao();
            atualizarGruposCheckboxes();
        });
    });

    // Quando uma consulta individual muda, atualizar o grupo
    document.querySelectorAll('.consulta-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            atualizarResumoCusto();
            atualizarEstadoBotao();
            atualizarGruposCheckboxes();
        });
    });

    // Inicializar checkboxes dos grupos
    atualizarGruposCheckboxes();

    // Busca de resultados
    document.getElementById('buscar-resultado').addEventListener('input', (e) => {
        window.analiseRiscoState.paginaAtual = 1;
        renderizarResultados(e.target.value);
    });

    document.getElementById('filtro-risco').addEventListener('change', (e) => {
        window.analiseRiscoState.paginaAtual = 1;
        renderizarResultados(null, e.target.value);
    });

    // Modal
    document.getElementById('fechar-modal').addEventListener('click', () => {
        document.getElementById('modal-detalhes').classList.add('hidden');
    });

    document.getElementById('modal-detalhes').addEventListener('click', (e) => {
        if (e.target.id === 'modal-detalhes') {
            document.getElementById('modal-detalhes').classList.add('hidden');
        }
    });

    // Inicializar
    atualizarResumoCusto();
    atualizarEstadoBotao();
});

function atualizarResumoCusto() {
    const consultasSelecionadas = Array.from(document.querySelectorAll('.consulta-checkbox:checked'));
    const totalConsultas = consultasSelecionadas.length;
    const custoTotal = consultasSelecionadas.reduce((sum, cb) => sum + parseFloat(cb.dataset.preco), 0);
    const custoMedio = totalConsultas > 0 ? custoTotal / totalConsultas : 0;

    document.getElementById('resumo-consultas').textContent = totalConsultas;
    document.getElementById('resumo-custo-consulta').textContent = `R$ ${custoMedio.toFixed(2).replace('.', ',')}`;
    document.getElementById('resumo-custo-total').textContent = `R$ ${custoTotal.toFixed(2).replace('.', ',')}`;
}

function renderizarResultados(busca = '', filtroRisco = 'todos') {
    let resultados = window.analiseRiscoState.fornecedores;
    
    if (busca) {
        const buscaLower = busca.toLowerCase();
        resultados = resultados.filter(f => 
            f.nome.toLowerCase().includes(buscaLower) || 
            f.cnpj.includes(busca)
        );
    }
    
    if (filtroRisco !== 'todos') {
        resultados = resultados.filter(f => f.risco === filtroRisco);
    }

    // Ordenar por score (menor primeiro - mais crítico primeiro)
    resultados.sort((a, b) => a.score - b.score);

    // Paginação
    const inicio = (window.analiseRiscoState.paginaAtual - 1) * window.analiseRiscoState.itensPorPagina;
    const fim = inicio + window.analiseRiscoState.itensPorPagina;
    const resultadosPagina = resultados.slice(inicio, fim);
    const totalPaginas = Math.ceil(resultados.length / window.analiseRiscoState.itensPorPagina);

    // Renderizar cards
    const cardsContainer = document.getElementById('resultados-cards-container');
    cardsContainer.innerHTML = '';
    
    resultadosPagina.forEach(fornecedor => {
        const riscoConfig = {
            critico: { icon: '⛔', cor: 'text-red-700', bg: 'bg-red-100', border: 'border-red-300' },
            alto: { icon: '🔴', cor: 'text-red-600', bg: 'bg-red-50', border: 'border-red-200' },
            medio: { icon: '🟠', cor: 'text-orange-600', bg: 'bg-orange-50', border: 'border-orange-200' },
            baixo: { icon: '✅', cor: 'text-green-600', bg: 'bg-green-50', border: 'border-green-200' }
        };
        const config = riscoConfig[fornecedor.risco] || riscoConfig.baixo;
        
        const card = document.createElement('div');
        card.className = `border-2 ${config.border} rounded-lg p-4 ${config.bg} hover:shadow-md transition-shadow`;
        
        let problemasHtml = '';
        if (fornecedor.problemas && fornecedor.problemas.length > 0) {
            problemasHtml = `
                <div class="mt-3 grid grid-cols-1 gap-2">
                    ${fornecedor.problemas.map(p => `
                        <div class="bg-white border border-gray-200 rounded p-2 text-xs">
                            <div class="font-semibold text-gray-800">❌ ${p}</div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        card.innerHTML = `
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xl">${config.icon}</span>
                        <span class="font-bold text-gray-800">${fornecedor.nome}</span>
                    </div>
                    <div class="text-sm text-gray-600 mb-2">CNPJ: ${fornecedor.cnpj}</div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${config.bg} ${config.cor} border ${config.border}">
                            Score: ${fornecedor.score}
                        </span>
                    </div>
                </div>
            </div>
            ${problemasHtml}
            <div class="mt-3 flex items-center gap-4 text-sm">
                <span class="text-gray-600">📄 ${fornecedor.notas} notas</span>
                <span class="text-gray-600">💰 R$ ${(fornecedor.valor / 1000).toFixed(0)}k</span>
            </div>
            <div class="mt-3">
                <button type="button" class="text-sm text-blue-600 hover:text-blue-800 font-medium ver-detalhes" data-id="${fornecedor.id}">
                    Ver Detalhes
                </button>
            </div>
        `;
        
        card.querySelector('.ver-detalhes').addEventListener('click', () => {
            abrirModalDetalhes(fornecedor);
        });
        
        cardsContainer.appendChild(card);
    });

    // Atualizar paginação
    document.getElementById('pagina-inicio').textContent = resultados.length > 0 ? inicio + 1 : 0;
    document.getElementById('pagina-fim').textContent = Math.min(fim, resultados.length);
    document.getElementById('total-resultados').textContent = resultados.length;

    const paginacao = document.getElementById('paginacao');
    paginacao.innerHTML = '';
    
    if (totalPaginas > 1) {
        if (window.analiseRiscoState.paginaAtual > 1) {
            const btnPrev = document.createElement('button');
            btnPrev.className = 'px-3 py-1.5 border border-gray-300 rounded-lg text-sm hover:bg-gray-50';
            btnPrev.textContent = '<';
            btnPrev.addEventListener('click', () => {
                window.analiseRiscoState.paginaAtual--;
                renderizarResultados(busca, filtroRisco);
            });
            paginacao.appendChild(btnPrev);
        }

        for (let i = 1; i <= totalPaginas; i++) {
            if (i === 1 || i === totalPaginas || (i >= window.analiseRiscoState.paginaAtual - 1 && i <= window.analiseRiscoState.paginaAtual + 1)) {
                const btn = document.createElement('button');
                btn.className = `px-3 py-1.5 border rounded-lg text-sm ${
                    i === window.analiseRiscoState.paginaAtual 
                        ? 'bg-blue-600 text-white border-blue-600' 
                        : 'border-gray-300 hover:bg-gray-50'
                }`;
                btn.textContent = i;
                btn.addEventListener('click', () => {
                    window.analiseRiscoState.paginaAtual = i;
                    renderizarResultados(busca, filtroRisco);
                });
                paginacao.appendChild(btn);
            } else if (i === window.analiseRiscoState.paginaAtual - 2 || i === window.analiseRiscoState.paginaAtual + 2) {
                const span = document.createElement('span');
                span.className = 'px-2 text-gray-500';
                span.textContent = '...';
                paginacao.appendChild(span);
            }
        }

        if (window.analiseRiscoState.paginaAtual < totalPaginas) {
            const btnNext = document.createElement('button');
            btnNext.className = 'px-3 py-1.5 border border-gray-300 rounded-lg text-sm hover:bg-gray-50';
            btnNext.textContent = '>';
            btnNext.addEventListener('click', () => {
                window.analiseRiscoState.paginaAtual++;
                renderizarResultados(busca, filtroRisco);
            });
            paginacao.appendChild(btnNext);
        }
    }

    // Renderizar alertas críticos
    const alertasCriticos = resultados.filter(f => f.risco === 'critico' || f.risco === 'alto').slice(0, 4);
    const alertasContainer = document.getElementById('alertas-criticos');
    alertasContainer.innerHTML = '';
    
    alertasCriticos.forEach(fornecedor => {
        const card = document.createElement('div');
        card.className = 'border-2 border-red-200 rounded-lg p-4 bg-red-50';
        
        const riscoLabel = fornecedor.risco === 'critico' ? 'CRÍTICO' : 'ALTO RISCO';
        const riscoIcon = fornecedor.risco === 'critico' ? '⛔' : '🔴';
        
        let problemasHtml = '';
        if (fornecedor.problemas) {
            problemasHtml = `
                <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-2">
                    ${fornecedor.problemas.map(p => `
                        <div class="bg-red-100 border border-red-200 rounded p-2 text-xs">
                            <div class="font-semibold text-red-800">${p.includes('❌') ? '' : '❌'} ${p}</div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        card.innerHTML = `
            <div class="flex items-start justify-between mb-2">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xl">${riscoIcon}</span>
                        <span class="font-bold text-gray-800">${fornecedor.nome}</span>
                        <span class="text-sm text-gray-600">Score: ${fornecedor.score}</span>
                    </div>
                    <div class="text-sm text-gray-600">CNPJ: ${fornecedor.cnpj}</div>
                </div>
            </div>
            ${problemasHtml}
            <div class="mt-3 flex items-center gap-4 text-sm">
                <span class="text-gray-600">📄 ${fornecedor.notas} notas</span>
                <span class="text-gray-600">💰 R$ ${(fornecedor.valor / 1000).toFixed(0)}k em operações</span>
            </div>
            <div class="mt-3">
                <button type="button" class="text-sm text-blue-600 hover:text-blue-800 font-medium ver-detalhes-alerta" data-id="${fornecedor.id}">
                    Ver Detalhes
                </button>
            </div>
        `;
        
        card.querySelector('.ver-detalhes-alerta').addEventListener('click', () => {
            abrirModalDetalhes(fornecedor);
        });
        
        alertasContainer.appendChild(card);
    });
}

function abrirModalDetalhes(fornecedor) {
    const modal = document.getElementById('modal-detalhes');
    const titulo = document.getElementById('modal-titulo');
    const conteudo = document.getElementById('modal-conteudo');
    
    titulo.textContent = `DETALHES: ${fornecedor.nome}`;
    
    const riscoConfig = {
        critico: { label: 'CRÍTICO', icon: '⛔', cor: 'text-red-700', bg: 'bg-red-100', border: 'border-red-300' },
        alto: { label: 'ALTO RISCO', icon: '🔴', cor: 'text-red-600', bg: 'bg-red-50', border: 'border-red-200' },
        medio: { label: 'MÉDIO RISCO', icon: '🟠', cor: 'text-orange-600', bg: 'bg-orange-50', border: 'border-orange-200' },
        baixo: { label: 'BAIXO RISCO', icon: '✅', cor: 'text-green-600', bg: 'bg-green-50', border: 'border-green-200' }
    };
    const config = riscoConfig[fornecedor.risco] || riscoConfig.baixo;
    
    conteudo.innerHTML = `
        <div class="mb-6">
            <div class="flex items-start gap-4">
                <div class="text-center p-4 ${config.bg} ${config.border} border-2 rounded-lg">
                    <div class="text-3xl font-bold ${config.cor}">${fornecedor.score}</div>
                    <div class="text-sm font-semibold ${config.cor} mt-1">${config.label}</div>
                    <div class="text-2xl mt-2">${config.icon}</div>
                </div>
                <div class="flex-1">
                    <div class="space-y-2 text-sm">
                        <div><span class="text-gray-600">CNPJ:</span> <span class="font-semibold text-gray-800">${fornecedor.cnpj}</span></div>
                        <div><span class="text-gray-600">IE:</span> <span class="font-semibold text-gray-800">123.456.789.001</span></div>
                        <div><span class="text-gray-600">Abertura:</span> <span class="font-semibold text-gray-800">15/08/2024 (5 meses)</span></div>
                        <div><span class="text-gray-600">Capital:</span> <span class="font-semibold text-gray-800">R$ 1.000,00</span></div>
                        <div><span class="text-gray-600">CNAE:</span> <span class="font-semibold text-gray-800">4693-1/00 - Comércio atacadista</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 my-6"></div>

        <div class="mb-6">
            <h4 class="font-semibold text-gray-800 mb-3">RESULTADO POR ÓRGÃO CONSULTADO</h4>
            <div class="space-y-3">
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="font-semibold text-gray-800 mb-2">RECEITA FEDERAL</div>
                    <div class="text-sm text-gray-700">${fornecedor.problemas && fornecedor.problemas.some(p => p.includes('CNPJ')) ? '❌ CNPJ: Baixado em 02/01/2025' : '✅ CNPJ: Ativo'}</div>
                    <div class="text-sm text-gray-700 mt-1">${fornecedor.problemas && fornecedor.problemas.some(p => p.includes('Simples')) ? '⚠️ Simples: Não optante' : '✅ Simples: Optante'}</div>
                </div>
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="font-semibold text-gray-800 mb-2">SEFAZ / SINTEGRA</div>
                    <div class="text-sm text-gray-700">${fornecedor.problemas && fornecedor.problemas.some(p => p.includes('IE')) ? '❌ IE: Cancelada em 28/12/2024' : '✅ IE: Ativa'}</div>
                </div>
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="font-semibold text-gray-800 mb-2">CGU</div>
                    <div class="text-sm text-gray-700">${fornecedor.problemas && fornecedor.problemas.some(p => p.includes('CEIS')) ? '❌ CEIS: Consta na lista desde 15/11/2024' : '✅ CEIS: Não consta'}</div>
                    <div class="text-sm text-gray-700 mt-1">✅ CNEP: Não consta</div>
                </div>
                <div class="border border-gray-200 rounded-lg p-3">
                    <div class="font-semibold text-gray-800 mb-2">MTE</div>
                    <div class="text-sm text-gray-700">✅ Lista de Trabalho Escravo: Não consta</div>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 my-6"></div>

        <div class="mb-6">
            <h4 class="font-semibold text-gray-800 mb-3">IMPACTO NAS OPERAÇÕES</h4>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-600">Notas com este fornecedor:</span> <span class="font-semibold text-gray-800">${fornecedor.notas}</span></div>
                <div><span class="text-gray-600">Valor total:</span> <span class="font-semibold text-gray-800">R$ ${fornecedor.valor.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span></div>
                <div><span class="text-gray-600">ICMS creditado:</span> <span class="font-semibold text-gray-800">R$ ${(fornecedor.valor * 0.18).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span></div>
                <div><span class="text-gray-600">PIS/COFINS creditado:</span> <span class="font-semibold text-gray-800">R$ ${(fornecedor.valor * 0.092).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span></div>
            </div>
            ${fornecedor.risco === 'critico' || fornecedor.risco === 'alto' ? `
                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="text-sm font-semibold text-amber-800">⚠️ ALERTA: Créditos em risco de glosa</div>
                </div>
            ` : ''}
        </div>

        <div class="border-t border-gray-200 my-6"></div>

        <div class="mb-6">
            <h4 class="font-semibold text-gray-800 mb-3">📋 RECOMENDAÇÕES</h4>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                ${fornecedor.risco === 'critico' || fornecedor.risco === 'alto' ? `
                    <li>Estornar créditos de ICMS das notas deste fornecedor</li>
                    <li>Verificar se mercadorias foram efetivamente recebidas</li>
                    <li>Solicitar documentação comprobatória</li>
                    <li>Considerar denúncia espontânea se houver irregularidade</li>
                ` : `
                    <li>Manter monitoramento regular</li>
                    <li>Revalidar periodicamente</li>
                `}
            </ul>
        </div>

        <div class="flex gap-3">
            <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Exportar Análise
            </button>
            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Notificar Cliente
            </button>
        </div>
    `;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
</script>
