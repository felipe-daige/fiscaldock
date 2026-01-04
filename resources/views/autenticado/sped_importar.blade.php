{{-- Importar SPED - Autenticado --}}
<div class="min-h-screen bg-gray-50" id="sped-importar-container">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">
                    Importar SPED
                </h1>
                <p class="text-xs text-gray-500 mt-1">
                    Importe um arquivo SPED e visualize analytics completos
                </p>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="space-y-6">
            {{-- Grid: Card Upload + Card Valor Relatório --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Card: Upload --}}
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
                        <p class="text-xs text-gray-500 mt-1">.txt | Máximo: 50MB</p>
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

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vincular a cliente (opcional):</label>
                    <select id="cliente-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione um cliente...</option>
                        <option value="1">Cliente Exemplo 1</option>
                        <option value="2">Cliente Exemplo 2</option>
                    </select>
                </div>

                <button type="button" id="processar-sped-btn" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center justify-center gap-2" disabled>
                    <span>Processar SPED</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </button>
                </div>

                {{-- Card: Valor do Relatório --}}
                <div class="bg-white rounded-lg shadow-md p-6 h-fit lg:sticky lg:top-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">VALOR DO RELATÓRIO</h3>
                    <div id="relatorio-placeholder" class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-sm text-gray-500">O preço será calculado após a importação do documento SPED</p>
                    </div>
                    <div id="relatorio-valores" class="hidden">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Fornecedores encontrados:</span>
                                    <span class="font-semibold text-gray-800" id="relatorio-fornecedores">—</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Custo por consulta:</span>
                                    <span class="font-semibold text-gray-800" id="relatorio-custo-consulta">R$ 0,26</span>
                                </div>
                                <div class="border-t border-gray-300 pt-3 mt-3">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-gray-800">VALOR TOTAL:</span>
                                        <span class="text-2xl font-bold text-amber-600" id="relatorio-valor-total">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 text-center">
                            Valor calculado automaticamente após processar o SPED
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Dados da Empresa --}}
            <div id="card-dados-empresa" class="bg-white rounded-lg shadow-md p-6 hidden">
                <h2 class="text-sm font-semibold text-gray-800 mb-5">DADOS DA EMPRESA</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Razão Social</label>
                        <div class="text-sm font-semibold text-gray-800" id="empresa-razao-social">—</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">CNPJ</label>
                        <div class="text-sm font-semibold text-gray-800" id="empresa-cnpj">—</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Período</label>
                        <div class="text-sm font-semibold text-gray-800" id="empresa-periodo">—</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Tipo</label>
                        <div class="text-sm font-semibold text-gray-800" id="empresa-tipo">—</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Finalidade</label>
                        <div class="text-sm font-semibold text-gray-800" id="empresa-finalidade">—</div>
                    </div>
                </div>
            </div>

            {{-- Cards de Resumo --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Card: Notas Fiscais --}}
                <div id="card-total-notas" class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-800">Notas Fiscais</h3>
                        <div class="text-2xl">📄</div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-600 mb-1">Total</p>
                            <p class="text-xl font-bold text-gray-800" id="notas-total">—</p>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs text-gray-600">Saída</span>
                                <span class="text-sm font-semibold text-gray-800" id="notas-saida">—</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-600">Entrada</span>
                                <span class="text-sm font-semibold text-gray-800" id="notas-entrada">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Fornecedores (Ranking) --}}
                <div id="card-total-fornecedores" class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-800">Top Fornecedores</h3>
                        <div class="text-2xl">🏢</div>
                    </div>
                    <div class="space-y-2" id="ranking-fornecedores-resumo">
                        <div class="text-xs text-gray-500 text-center py-2">—</div>
                    </div>
                </div>

                {{-- Card: Receita Total --}}
                <div id="card-receita-total" class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-800">Receita Total</h3>
                        <div class="text-2xl">💰</div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-600 mb-1">Total</p>
                            <p class="text-xl font-bold text-gray-800" id="receita-total-valor">—</p>
                        </div>
                        <div class="border-t border-gray-200 pt-2 space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-600">ICMS</span>
                                <span class="text-xs font-semibold text-gray-800" id="tributo-icms">—</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-600">PIS/COFINS</span>
                                <span class="text-xs font-semibold text-gray-800" id="tributo-pis-cofins">—</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-600">IPI</span>
                                <span class="text-xs font-semibold text-gray-800" id="tributo-ipi">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Produtos --}}
                <div id="card-total-produtos" class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-800">Top Produtos</h3>
                        <div class="text-2xl">📦</div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-600 mb-2">Mais Vendidos</p>
                            <div class="space-y-1" id="produtos-mais-vendidos">
                                <div class="text-xs text-gray-500 text-center py-1">—</div>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <p class="text-xs text-gray-600 mb-2">Mais Retorno</p>
                            <div class="space-y-1" id="produtos-mais-retorno">
                                <div class="text-xs text-gray-500 text-center py-1">—</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cards de Rankings --}}
            <div id="cards-rankings" class="grid grid-cols-1 md:grid-cols-2 gap-6 hidden">
                {{-- Card 1: Top Fornecedores por Volume --}}
                <div id="ranking-fornecedores-volume" class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">Top Fornecedores por Volume (R$)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 px-2 text-gray-600 font-medium">#</th>
                                    <th class="text-left py-2 px-2 text-gray-600 font-medium">Fornecedor</th>
                                    <th class="text-right py-2 px-2 text-gray-600 font-medium">Valor</th>
                                </tr>
                            </thead>
                            <tbody id="ranking-fornecedores-volume-body">
                                <!-- Dados serão inseridos via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Card 2: Top Fornecedores por Qtd Notas --}}
                <div id="ranking-fornecedores-qtd" class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">Top Fornecedores por Qtd Notas</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 px-2 text-gray-600 font-medium">#</th>
                                    <th class="text-left py-2 px-2 text-gray-600 font-medium">Fornecedor</th>
                                    <th class="text-right py-2 px-2 text-gray-600 font-medium">Notas</th>
                                </tr>
                            </thead>
                            <tbody id="ranking-fornecedores-qtd-body">
                                <!-- Dados serão inseridos via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Card 3: Top Produtos por Receita --}}
                <div id="ranking-produtos-receita" class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">Top Produtos por Receita</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 px-2 text-gray-600 font-medium">#</th>
                                    <th class="text-left py-2 px-2 text-gray-600 font-medium">Produto</th>
                                    <th class="text-right py-2 px-2 text-gray-600 font-medium">Valor</th>
                                </tr>
                            </thead>
                            <tbody id="ranking-produtos-receita-body">
                                <!-- Dados serão inseridos via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Card 4: CFOPs mais utilizados --}}
                <div id="ranking-cfops" class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">CFOPs mais utilizados</h3>
                    <div class="space-y-3" id="ranking-cfops-body">
                        <!-- Dados serão inseridos via JS -->
                    </div>
                </div>
            </div>

            {{-- Card: Listas Detalhadas (com tabs) --}}
            <div id="card-listas-detalhadas" class="bg-white rounded-lg shadow-md p-6 hidden">
                <h2 class="text-sm font-semibold text-gray-800 mb-5">LISTAS DETALHADAS</h2>
                
                {{-- Tabs --}}
                <div class="border-b border-gray-200 mb-4">
                    <div class="flex gap-4">
                        <button type="button" class="tab-btn active px-4 py-2 text-sm font-medium text-blue-600 border-b-2 border-blue-600" data-tab="fornecedores">
                            Fornecedores
                        </button>
                        <button type="button" class="tab-btn px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 border-b-2 border-transparent" data-tab="notas">
                            Notas Fiscais
                        </button>
                        <button type="button" class="tab-btn px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 border-b-2 border-transparent" data-tab="produtos">
                            Produtos
                        </button>
                    </div>
                </div>

                {{-- Tab Content: Fornecedores --}}
                <div id="tab-fornecedores" class="tab-content">
                    <div class="mb-4">
                        <input type="text" id="buscar-fornecedor" placeholder="Buscar por nome ou CNPJ..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" id="tabela-fornecedores">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 px-3 text-gray-600 font-medium">Fornecedor</th>
                                    <th class="text-left py-2 px-3 text-gray-600 font-medium">CNPJ</th>
                                    <th class="text-right py-2 px-3 text-gray-600 font-medium">Qtd Notas</th>
                                    <th class="text-right py-2 px-3 text-gray-600 font-medium">Valor Total</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-fornecedores-body">
                                <!-- Dados serão inseridos via JS -->
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Mostrando <span id="fornecedores-inicio">0</span>-<span id="fornecedores-fim">0</span> de <span id="fornecedores-total">0</span>
                        </div>
                        <div class="flex gap-2" id="fornecedores-paginacao">
                            <!-- Paginação será inserida via JS -->
                        </div>
                    </div>
                </div>

                {{-- Tab Content: Notas Fiscais --}}
                <div id="tab-notas" class="tab-content hidden">
                    <div class="mb-4">
                        <input type="text" id="buscar-nota" placeholder="Buscar por número, fornecedor..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" id="tabela-notas">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 px-3 text-gray-600 font-medium">Nº Nota</th>
                                    <th class="text-left py-2 px-3 text-gray-600 font-medium">Fornecedor</th>
                                    <th class="text-left py-2 px-3 text-gray-600 font-medium">Data</th>
                                    <th class="text-left py-2 px-3 text-gray-600 font-medium">CFOP</th>
                                    <th class="text-right py-2 px-3 text-gray-600 font-medium">Valor</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-notas-body">
                                <!-- Dados serão inseridos via JS -->
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Mostrando <span id="notas-inicio">0</span>-<span id="notas-fim">0</span> de <span id="notas-total">0</span>
                        </div>
                        <div class="flex gap-2" id="notas-paginacao">
                            <!-- Paginação será inserida via JS -->
                        </div>
                    </div>
                </div>

                {{-- Tab Content: Produtos --}}
                <div id="tab-produtos" class="tab-content hidden">
                    <div class="mb-4">
                        <input type="text" id="buscar-produto" placeholder="Buscar por código ou descrição..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" id="tabela-produtos">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 px-3 text-gray-600 font-medium">Código</th>
                                    <th class="text-left py-2 px-3 text-gray-600 font-medium">Descrição</th>
                                    <th class="text-right py-2 px-3 text-gray-600 font-medium">Qtd Vendida</th>
                                    <th class="text-right py-2 px-3 text-gray-600 font-medium">Receita</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-produtos-body">
                                <!-- Dados serão inseridos via JS -->
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Mostrando <span id="produtos-inicio">0</span>-<span id="produtos-fim">0</span> de <span id="produtos-total">0</span>
                        </div>
                        <div class="flex gap-2" id="produtos-paginacao">
                            <!-- Paginação será inserida via JS -->
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Ações --}}
            <div id="card-acoes" class="bg-white rounded-lg shadow-md p-6 hidden">
                <h2 class="text-sm font-semibold text-gray-800 mb-5">AÇÕES</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button type="button" id="salvar-importacao-btn" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                        <span>💾</span>
                        Salvar Importação
                    </button>
                    <button type="button" id="analisar-risco-btn" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                        <span>📊</span>
                        Analisar Risco
                    </button>
                    <button type="button" id="exportar-relatorio-btn" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors flex items-center justify-center gap-2">
                        <span>📥</span>
                        Exportar Relatório
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Estado global
window.spedImportarState = {
    arquivoSelecionado: null,
    dadosProcessados: false,
    paginaFornecedores: 1,
    paginaNotas: 1,
    paginaProdutos: 1,
    itensPorPagina: 10,
    tabAtiva: 'fornecedores'
};

// Dados mockados
const dadosMockados = {
    empresa: {
        razaoSocial: 'ACME COMÉRCIO LTDA',
        cnpj: '12.345.678/0001-90',
        periodo: '01/01/2025 a 31/01/2025',
        tipo: 'EFD Contribuições',
        finalidade: 'Original'
    },
    resumo: {
        totalNotas: 156,
        notasSaida: 98,
        notasEntrada: 58,
        totalFornecedores: 242,
        receitaTotal: 1245890,
        tributos: {
            icms: 224260,
            pisCofins: 114621,
            ipi: 62300
        },
        totalProdutos: 4847
    },
    rankingFornecedoresVolume: [
        { nome: 'Distribuidora ABC', valor: 234500 },
        { nome: 'Atacado Premium', valor: 156800 },
        { nome: 'Comercial Brasil', valor: 98200 },
        { nome: 'Indústria XYZ', valor: 87600 },
        { nome: 'Fornecedor 123', valor: 65400 }
    ],
    rankingFornecedoresQtd: [
        { nome: 'Distribuidora ABC', notas: 28 },
        { nome: 'Atacado Premium', notas: 15 },
        { nome: 'Comercial Brasil', notas: 12 },
        { nome: 'Indústria XYZ', notas: 8 },
        { nome: 'Fornecedor 123', notas: 6 }
    ],
    rankingProdutos: [
        { nome: 'Produto Alpha', valor: 89500 },
        { nome: 'Produto Beta', valor: 67200 },
        { nome: 'Produto Gamma', valor: 54800 },
        { nome: 'Produto Delta', valor: 43100 },
        { nome: 'Produto Epsilon', valor: 38900 }
    ],
    produtosMaisVendidos: [
        { nome: 'Produto Alpha', qtd: 1250 },
        { nome: 'Produto Beta', qtd: 890 },
        { nome: 'Produto Gamma', qtd: 756 },
        { nome: 'Produto Delta', qtd: 623 },
        { nome: 'Produto Epsilon', qtd: 512 }
    ],
    produtosMaisRetorno: [
        { nome: 'Produto Alpha', retorno: 89500 },
        { nome: 'Produto Beta', retorno: 67200 },
        { nome: 'Produto Gamma', retorno: 54800 },
        { nome: 'Produto Delta', retorno: 43100 },
        { nome: 'Produto Epsilon', retorno: 38900 }
    ],
    cfops: [
        { codigo: '1102', descricao: 'Compra p/ comercialização', percentual: 45 },
        { codigo: '1403', descricao: 'Compra p/ comercialização em ST', percentual: 30 },
        { codigo: '2102', descricao: 'Compra p/ comercialização (interestadual)', percentual: 15 },
        { codigo: 'Outros', descricao: 'Outros CFOPs', percentual: 10 }
    ],
    fornecedores: [
        { id: 1, nome: 'Distribuidora ABC', cnpj: '11.222.333/0001-44', notas: 28, valor: 234500 },
        { id: 2, nome: 'Atacado Premium', cnpj: '22.333.444/0001-55', notas: 15, valor: 156800 },
        { id: 3, nome: 'Comercial Brasil', cnpj: '33.444.555/0001-66', notas: 12, valor: 98200 },
        { id: 4, nome: 'Indústria XYZ', cnpj: '44.555.666/0001-77', notas: 8, valor: 87600 },
        { id: 5, nome: 'Fornecedor 123', cnpj: '55.666.777/0001-88', notas: 6, valor: 65400 },
        { id: 6, nome: 'Comércio Nacional', cnpj: '66.777.888/0001-99', notas: 5, valor: 45200 },
        { id: 7, nome: 'Importadora Brasil', cnpj: '77.888.999/0001-00', notas: 4, valor: 38900 },
        { id: 8, nome: 'Distribuidora Sul', cnpj: '88.999.000/0001-11', notas: 3, valor: 32100 },
        { id: 9, nome: 'Atacado Norte', cnpj: '99.000.111/0001-22', notas: 2, valor: 28700 },
        { id: 10, nome: 'Comercial Oeste', cnpj: '00.111.222/0001-33', notas: 1, valor: 15400 }
    ],
    notas: [
        { id: 1, numero: '1234', fornecedor: 'Distribuidora ABC', data: '05/01/2025', cfop: '1102', valor: 5200 },
        { id: 2, numero: '1235', fornecedor: 'Atacado Premium', data: '05/01/2025', cfop: '1403', valor: 8900 },
        { id: 3, numero: '1236', fornecedor: 'Comercial Brasil', data: '06/01/2025', cfop: '1102', valor: 3100 },
        { id: 4, numero: '1237', fornecedor: 'Distribuidora ABC', data: '08/01/2025', cfop: '1102', valor: 7800 },
        { id: 5, numero: '1238', fornecedor: 'Indústria XYZ', data: '10/01/2025', cfop: '2102', valor: 4500 },
        { id: 6, numero: '1239', fornecedor: 'Fornecedor 123', data: '12/01/2025', cfop: '1102', valor: 3200 },
        { id: 7, numero: '1240', fornecedor: 'Comércio Nacional', data: '15/01/2025', cfop: '1403', valor: 2800 },
        { id: 8, numero: '1241', fornecedor: 'Importadora Brasil', data: '18/01/2025', cfop: '1102', valor: 2100 },
        { id: 9, numero: '1242', fornecedor: 'Distribuidora Sul', data: '20/01/2025', cfop: '2102', valor: 1900 },
        { id: 10, numero: '1243', fornecedor: 'Atacado Norte', data: '22/01/2025', cfop: '1102', valor: 1500 }
    ],
    produtos: [
        { id: 1, codigo: '001', descricao: 'Produto Alpha', qtd: 1250, receita: 89500 },
        { id: 2, codigo: '002', descricao: 'Produto Beta', qtd: 890, receita: 67200 },
        { id: 3, codigo: '003', descricao: 'Produto Gamma', qtd: 756, receita: 54800 },
        { id: 4, codigo: '004', descricao: 'Produto Delta', qtd: 623, receita: 43100 },
        { id: 5, codigo: '005', descricao: 'Produto Epsilon', qtd: 512, receita: 38900 },
        { id: 6, codigo: '006', descricao: 'Produto Zeta', qtd: 445, receita: 32100 },
        { id: 7, codigo: '007', descricao: 'Produto Eta', qtd: 389, receita: 28700 },
        { id: 8, codigo: '008', descricao: 'Produto Theta', qtd: 334, receita: 25400 },
        { id: 9, codigo: '009', descricao: 'Produto Iota', qtd: 278, receita: 19800 },
        { id: 10, codigo: '010', descricao: 'Produto Kappa', qtd: 223, receita: 15400 }
    ]
};

// Função para validar arquivo
function validarArquivo(file) {
    const maxSize = 50 * 1024 * 1024; // 50MB
    if (file.size > maxSize) {
        alert('O arquivo excede o limite de 50MB');
        return false;
    }
    if (!file.name.endsWith('.txt')) {
        alert('Apenas arquivos .txt são permitidos');
        return false;
    }
    return true;
}

// Função para atualizar estado do botão
function atualizarEstadoBotao() {
    const processarBtn = document.getElementById('processar-sped-btn');
    if (processarBtn) {
        processarBtn.disabled = window.spedImportarState.arquivoSelecionado === null;
    }
}

// Função para atualizar valor do relatório
function atualizarValorRelatorio() {
    if (!window.spedImportarState.dadosProcessados) {
        return;
    }

    const totalFornecedores = dadosMockados.resumo.totalFornecedores;
    const custoPorConsulta = 0.26; // R$ 0,26 por consulta
    const valorTotal = totalFornecedores * custoPorConsulta;

    // Esconder placeholder e mostrar valores
    document.getElementById('relatorio-placeholder').classList.add('hidden');
    document.getElementById('relatorio-valores').classList.remove('hidden');

    document.getElementById('relatorio-fornecedores').textContent = totalFornecedores;
    document.getElementById('relatorio-custo-consulta').textContent = `R$ ${custoPorConsulta.toFixed(2).replace('.', ',')}`;
    document.getElementById('relatorio-valor-total').textContent = `R$ ${valorTotal.toFixed(2).replace('.', ',')}`;
}

// Função para processar SPED
function processarSPED() {
    if (!window.spedImportarState.arquivoSelecionado) {
        return;
    }

    // Simular processamento
    setTimeout(() => {
        window.spedImportarState.dadosProcessados = true;
        
        // Preencher dados da empresa
        document.getElementById('empresa-razao-social').textContent = dadosMockados.empresa.razaoSocial;
        document.getElementById('empresa-cnpj').textContent = dadosMockados.empresa.cnpj;
        document.getElementById('empresa-periodo').textContent = dadosMockados.empresa.periodo;
        document.getElementById('empresa-tipo').textContent = dadosMockados.empresa.tipo;
        document.getElementById('empresa-finalidade').textContent = dadosMockados.empresa.finalidade;
        document.getElementById('card-dados-empresa').classList.remove('hidden');

        // Preencher card de Notas Fiscais
        document.getElementById('notas-total').textContent = dadosMockados.resumo.totalNotas;
        document.getElementById('notas-saida').textContent = dadosMockados.resumo.notasSaida;
        document.getElementById('notas-entrada').textContent = dadosMockados.resumo.notasEntrada;

        // Preencher card de Fornecedores (ranking)
        preencherRankingFornecedoresResumo();

        // Preencher card de Receita Total
        document.getElementById('receita-total-valor').textContent = 'R$ ' + dadosMockados.resumo.receitaTotal.toLocaleString('pt-BR');
        document.getElementById('tributo-icms').textContent = 'R$ ' + dadosMockados.resumo.tributos.icms.toLocaleString('pt-BR');
        document.getElementById('tributo-pis-cofins').textContent = 'R$ ' + dadosMockados.resumo.tributos.pisCofins.toLocaleString('pt-BR');
        document.getElementById('tributo-ipi').textContent = 'R$ ' + dadosMockados.resumo.tributos.ipi.toLocaleString('pt-BR');

        // Preencher card de Produtos
        preencherProdutosResumo();

        // Preencher rankings
        preencherRankings();
        
        // Mostrar cards de rankings
        document.getElementById('cards-rankings').classList.remove('hidden');
        
        // Preencher listas detalhadas
        renderizarTabelaFornecedores();
        renderizarTabelaNotas();
        renderizarTabelaProdutos();
        
        // Mostrar card de listas detalhadas
        document.getElementById('card-listas-detalhadas').classList.remove('hidden');
        
        // Mostrar card de ações
        document.getElementById('card-acoes').classList.remove('hidden');
        
        // Atualizar valor do relatório
        atualizarValorRelatorio();
    }, 500);
}

// Função para preencher ranking de fornecedores no card de resumo
function preencherRankingFornecedoresResumo() {
    const container = document.getElementById('ranking-fornecedores-resumo');
    const topFornecedores = dadosMockados.rankingFornecedoresVolume.slice(0, 5);
    
    container.innerHTML = topFornecedores.map((f, idx) => `
        <div class="flex items-center justify-between text-xs py-1">
            <div class="flex items-center gap-2">
                <span class="text-gray-500">${idx + 1}.</span>
                <span class="text-gray-800 font-medium truncate">${f.nome}</span>
            </div>
            <span class="text-gray-600">R$ ${(f.valor / 1000).toFixed(0)}k</span>
        </div>
    `).join('');
}

// Função para preencher produtos no card de resumo
function preencherProdutosResumo() {
    // Top 5 mais vendidos
    const containerVendidos = document.getElementById('produtos-mais-vendidos');
    containerVendidos.innerHTML = dadosMockados.produtosMaisVendidos.map((p, idx) => `
        <div class="flex items-center justify-between text-xs py-0.5">
            <span class="text-gray-500">${idx + 1}.</span>
            <span class="text-gray-800 font-medium flex-1 ml-1 truncate">${p.nome}</span>
            <span class="text-gray-600 ml-2">${p.qtd.toLocaleString('pt-BR')}</span>
        </div>
    `).join('');

    // Top 5 mais retorno
    const containerRetorno = document.getElementById('produtos-mais-retorno');
    containerRetorno.innerHTML = dadosMockados.produtosMaisRetorno.map((p, idx) => `
        <div class="flex items-center justify-between text-xs py-0.5">
            <span class="text-gray-500">${idx + 1}.</span>
            <span class="text-gray-800 font-medium flex-1 ml-1 truncate">${p.nome}</span>
            <span class="text-gray-600 ml-2">R$ ${(p.retorno / 1000).toFixed(0)}k</span>
        </div>
    `).join('');
}

// Função para preencher rankings
function preencherRankings() {
    // Top Fornecedores por Volume
    const tbodyVolume = document.getElementById('ranking-fornecedores-volume-body');
    tbodyVolume.innerHTML = dadosMockados.rankingFornecedoresVolume.map((f, idx) => `
        <tr class="border-b border-gray-100">
            <td class="py-2 px-2 text-gray-800">${idx + 1}</td>
            <td class="py-2 px-2 text-gray-800">${f.nome}</td>
            <td class="py-2 px-2 text-right text-gray-800">R$ ${f.valor.toLocaleString('pt-BR')}</td>
        </tr>
    `).join('');

    // Top Fornecedores por Qtd Notas
    const tbodyQtd = document.getElementById('ranking-fornecedores-qtd-body');
    tbodyQtd.innerHTML = dadosMockados.rankingFornecedoresQtd.map((f, idx) => `
        <tr class="border-b border-gray-100">
            <td class="py-2 px-2 text-gray-800">${idx + 1}</td>
            <td class="py-2 px-2 text-gray-800">${f.nome}</td>
            <td class="py-2 px-2 text-right text-gray-800">${f.notas}</td>
        </tr>
    `).join('');

    // Top Produtos por Receita
    const tbodyProdutos = document.getElementById('ranking-produtos-receita-body');
    tbodyProdutos.innerHTML = dadosMockados.rankingProdutos.map((p, idx) => `
        <tr class="border-b border-gray-100">
            <td class="py-2 px-2 text-gray-800">${idx + 1}</td>
            <td class="py-2 px-2 text-gray-800">${p.nome}</td>
            <td class="py-2 px-2 text-right text-gray-800">R$ ${p.valor.toLocaleString('pt-BR')}</td>
        </tr>
    `).join('');

    // CFOPs
    const cfopsBody = document.getElementById('ranking-cfops-body');
    cfopsBody.innerHTML = dadosMockados.cfops.map(cfop => `
        <div class="space-y-1">
            <div class="flex items-center justify-between text-sm">
                <div>
                    <span class="font-semibold text-gray-800">${cfop.codigo}</span>
                    <span class="text-gray-600 ml-2">${cfop.descricao}</span>
                </div>
                <span class="font-semibold text-gray-800">${cfop.percentual}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: ${cfop.percentual}%"></div>
            </div>
        </div>
    `).join('');
}

// Função para renderizar tabela de fornecedores
function renderizarTabelaFornecedores(busca = '') {
    let fornecedores = dadosMockados.fornecedores;
    
    if (busca) {
        const buscaLower = busca.toLowerCase();
        fornecedores = fornecedores.filter(f => 
            f.nome.toLowerCase().includes(buscaLower) || 
            f.cnpj.includes(busca)
        );
    }

    const inicio = (window.spedImportarState.paginaFornecedores - 1) * window.spedImportarState.itensPorPagina;
    const fim = inicio + window.spedImportarState.itensPorPagina;
    const fornecedoresPagina = fornecedores.slice(inicio, fim);
    const totalPaginas = Math.ceil(fornecedores.length / window.spedImportarState.itensPorPagina);

    const tbody = document.getElementById('tabela-fornecedores-body');
    tbody.innerHTML = fornecedoresPagina.map(f => `
        <tr class="border-b border-gray-100 hover:bg-gray-50">
            <td class="py-2 px-3 text-gray-800">${f.nome}</td>
            <td class="py-2 px-3 text-gray-600">${f.cnpj}</td>
            <td class="py-2 px-3 text-right text-gray-800">${f.notas}</td>
            <td class="py-2 px-3 text-right text-gray-800">R$ ${f.valor.toLocaleString('pt-BR')}</td>
        </tr>
    `).join('');

    document.getElementById('fornecedores-inicio').textContent = fornecedores.length > 0 ? inicio + 1 : 0;
    document.getElementById('fornecedores-fim').textContent = Math.min(fim, fornecedores.length);
    document.getElementById('fornecedores-total').textContent = fornecedores.length;

    renderizarPaginacao('fornecedores-paginacao', window.spedImportarState.paginaFornecedores, totalPaginas, 'fornecedores');
}

// Função para renderizar tabela de notas
function renderizarTabelaNotas(busca = '') {
    let notas = dadosMockados.notas;
    
    if (busca) {
        const buscaLower = busca.toLowerCase();
        notas = notas.filter(n => 
            n.numero.includes(busca) ||
            n.fornecedor.toLowerCase().includes(buscaLower)
        );
    }

    const inicio = (window.spedImportarState.paginaNotas - 1) * window.spedImportarState.itensPorPagina;
    const fim = inicio + window.spedImportarState.itensPorPagina;
    const notasPagina = notas.slice(inicio, fim);
    const totalPaginas = Math.ceil(notas.length / window.spedImportarState.itensPorPagina);

    const tbody = document.getElementById('tabela-notas-body');
    tbody.innerHTML = notasPagina.map(n => `
        <tr class="border-b border-gray-100 hover:bg-gray-50">
            <td class="py-2 px-3 text-gray-800">${n.numero}</td>
            <td class="py-2 px-3 text-gray-600">${n.fornecedor}</td>
            <td class="py-2 px-3 text-gray-600">${n.data}</td>
            <td class="py-2 px-3 text-gray-600">${n.cfop}</td>
            <td class="py-2 px-3 text-right text-gray-800">R$ ${n.valor.toLocaleString('pt-BR')}</td>
        </tr>
    `).join('');

    document.getElementById('notas-inicio').textContent = notas.length > 0 ? inicio + 1 : 0;
    document.getElementById('notas-fim').textContent = Math.min(fim, notas.length);
    document.getElementById('notas-total').textContent = notas.length;

    renderizarPaginacao('notas-paginacao', window.spedImportarState.paginaNotas, totalPaginas, 'notas');
}

// Função para renderizar tabela de produtos
function renderizarTabelaProdutos(busca = '') {
    let produtos = dadosMockados.produtos;
    
    if (busca) {
        const buscaLower = busca.toLowerCase();
        produtos = produtos.filter(p => 
            p.codigo.includes(busca) ||
            p.descricao.toLowerCase().includes(buscaLower)
        );
    }

    const inicio = (window.spedImportarState.paginaProdutos - 1) * window.spedImportarState.itensPorPagina;
    const fim = inicio + window.spedImportarState.itensPorPagina;
    const produtosPagina = produtos.slice(inicio, fim);
    const totalPaginas = Math.ceil(produtos.length / window.spedImportarState.itensPorPagina);

    const tbody = document.getElementById('tabela-produtos-body');
    tbody.innerHTML = produtosPagina.map(p => `
        <tr class="border-b border-gray-100 hover:bg-gray-50">
            <td class="py-2 px-3 text-gray-800">${p.codigo}</td>
            <td class="py-2 px-3 text-gray-600">${p.descricao}</td>
            <td class="py-2 px-3 text-right text-gray-800">${p.qtd.toLocaleString('pt-BR')}</td>
            <td class="py-2 px-3 text-right text-gray-800">R$ ${p.receita.toLocaleString('pt-BR')}</td>
        </tr>
    `).join('');

    document.getElementById('produtos-inicio').textContent = produtos.length > 0 ? inicio + 1 : 0;
    document.getElementById('produtos-fim').textContent = Math.min(fim, produtos.length);
    document.getElementById('produtos-total').textContent = produtos.length;

    renderizarPaginacao('produtos-paginacao', window.spedImportarState.paginaProdutos, totalPaginas, 'produtos');
}

// Função para renderizar paginação
function renderizarPaginacao(containerId, paginaAtual, totalPaginas, tipo) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';

    if (totalPaginas <= 1) return;

    if (paginaAtual > 1) {
        const btnPrev = document.createElement('button');
        btnPrev.className = 'px-3 py-1.5 border border-gray-300 rounded-lg text-sm hover:bg-gray-50';
        btnPrev.textContent = '<';
        btnPrev.addEventListener('click', () => {
            window.spedImportarState[`pagina${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`]--;
            if (tipo === 'fornecedores') {
                renderizarTabelaFornecedores(document.getElementById('buscar-fornecedor').value);
            } else if (tipo === 'notas') {
                renderizarTabelaNotas(document.getElementById('buscar-nota').value);
            } else if (tipo === 'produtos') {
                renderizarTabelaProdutos(document.getElementById('buscar-produto').value);
            }
        });
        container.appendChild(btnPrev);
    }

    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaAtual - 1 && i <= paginaAtual + 1)) {
            const btn = document.createElement('button');
            btn.className = `px-3 py-1.5 border rounded-lg text-sm ${
                i === paginaAtual 
                    ? 'bg-blue-600 text-white border-blue-600' 
                    : 'border-gray-300 hover:bg-gray-50'
            }`;
            btn.textContent = i;
            btn.addEventListener('click', () => {
                window.spedImportarState[`pagina${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`] = i;
                if (tipo === 'fornecedores') {
                    renderizarTabelaFornecedores(document.getElementById('buscar-fornecedor').value);
                } else if (tipo === 'notas') {
                    renderizarTabelaNotas(document.getElementById('buscar-nota').value);
                } else if (tipo === 'produtos') {
                    renderizarTabelaProdutos(document.getElementById('buscar-produto').value);
                }
            });
            container.appendChild(btn);
        } else if (i === paginaAtual - 2 || i === paginaAtual + 2) {
            const span = document.createElement('span');
            span.className = 'px-2 text-gray-500';
            span.textContent = '...';
            container.appendChild(span);
        }
    }

    if (paginaAtual < totalPaginas) {
        const btnNext = document.createElement('button');
        btnNext.className = 'px-3 py-1.5 border border-gray-300 rounded-lg text-sm hover:bg-gray-50';
        btnNext.textContent = '>';
        btnNext.addEventListener('click', () => {
            window.spedImportarState[`pagina${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`]++;
            if (tipo === 'fornecedores') {
                renderizarTabelaFornecedores(document.getElementById('buscar-fornecedor').value);
            } else if (tipo === 'notas') {
                renderizarTabelaNotas(document.getElementById('buscar-nota').value);
            } else if (tipo === 'produtos') {
                renderizarTabelaProdutos(document.getElementById('buscar-produto').value);
            }
        });
        container.appendChild(btnNext);
    }
}

// Função para trocar tabs
function trocarTab(tab) {
    // Atualizar botões das tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active', 'text-blue-600', 'border-blue-600');
        btn.classList.add('text-gray-600', 'border-transparent');
    });
    
    const btnAtivo = document.querySelector(`.tab-btn[data-tab="${tab}"]`);
    if (btnAtivo) {
        btnAtivo.classList.add('active', 'text-blue-600', 'border-blue-600');
        btnAtivo.classList.remove('text-gray-600', 'border-transparent');
    }

    // Atualizar conteúdo das tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    const contentAtivo = document.getElementById(`tab-${tab}`);
    if (contentAtivo) {
        contentAtivo.classList.remove('hidden');
    }

    window.spedImportarState.tabAtiva = tab;
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('sped-file');
    const fileSelected = document.getElementById('file-selected');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const removeFile = document.getElementById('remove-file');
    const processarBtn = document.getElementById('processar-sped-btn');

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
        if (!validarArquivo(file)) {
            return;
        }
        window.spedImportarState.arquivoSelecionado = file;
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        fileSelected.classList.remove('hidden');
        atualizarEstadoBotao();
    }

    removeFile.addEventListener('click', () => {
        window.spedImportarState.arquivoSelecionado = null;
        fileInput.value = '';
        fileSelected.classList.add('hidden');
        atualizarEstadoBotao();
    });

    // Processar SPED
    processarBtn.addEventListener('click', () => {
        processarSPED();
    });

    // Tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            trocarTab(tab);
        });
    });

    // Busca
    document.getElementById('buscar-fornecedor').addEventListener('input', (e) => {
        window.spedImportarState.paginaFornecedores = 1;
        renderizarTabelaFornecedores(e.target.value);
    });

    document.getElementById('buscar-nota').addEventListener('input', (e) => {
        window.spedImportarState.paginaNotas = 1;
        renderizarTabelaNotas(e.target.value);
    });

    document.getElementById('buscar-produto').addEventListener('input', (e) => {
        window.spedImportarState.paginaProdutos = 1;
        renderizarTabelaProdutos(e.target.value);
    });

    // Botões de ação
    document.getElementById('analisar-risco-btn').addEventListener('click', () => {
        // Redirecionar para análise de risco
        window.location.href = '/app/sped_analise_risco';
    });

    document.getElementById('salvar-importacao-btn').addEventListener('click', () => {
        alert('Funcionalidade de salvar importação será implementada no backend');
    });

    document.getElementById('exportar-relatorio-btn').addEventListener('click', () => {
        alert('Funcionalidade de exportar relatório será implementada no backend');
    });

    // Inicializar estado
    atualizarEstadoBotao();
});
</script>

