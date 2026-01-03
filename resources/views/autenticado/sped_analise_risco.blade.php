{{-- Análise de Risco SPED - Autenticado --}}
<div class="min-h-screen bg-gray-50" id="analise-risco-container">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">
                        Análise de Risco - SPED
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">
                        Analise os fornecedores extraídos do seu arquivo SPED
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                        <span class="text-lg">💳</span>
                        <span class="font-semibold text-blue-800" id="saldo-creditos">147</span>
                        <span class="text-sm text-blue-600">créditos</span>
                    </div>
                    <button type="button" id="btn-comprar-creditos" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <span>+</span>
                        <span>Comprar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        {{-- SEÇÃO 1: FUNCIONALIDADE --}}
        <div id="secao-funcionalidade" class="space-y-6">
            
            {{-- Card: Arquivo SPED Selecionado --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-md p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-2xl">📁</span>
                    <h2 class="text-lg font-semibold text-gray-800">ARQUIVO SPED</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-500">Cliente</p>
                        <p class="font-semibold text-gray-800">ACME COMÉRCIO LTDA</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Arquivo</p>
                        <p class="font-semibold text-gray-800">SPED_EFD_202312.txt</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Período</p>
                        <p class="font-semibold text-gray-800">01/12/2023 a 31/12/2023</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Importado em</p>
                        <p class="font-semibold text-gray-800">02/01/2025 às 14:32</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="text-2xl font-bold text-gray-800">42</div>
                        <div class="text-xs text-gray-600 mt-1">Fornecedores</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="text-2xl font-bold text-gray-800">156</div>
                        <div class="text-xs text-gray-600 mt-1">Notas</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="text-2xl font-bold text-gray-800">847</div>
                        <div class="text-xs text-gray-600 mt-1">Produtos</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="text-2xl font-bold text-gray-800">R$ 1.24M</div>
                        <div class="text-xs text-gray-600 mt-1">Volume</div>
                    </div>
                </div>
                
                <button type="button" id="btn-trocar-arquivo" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2">
                    <span>🔄</span>
                    <span>Trocar arquivo</span>
                </button>
            </div>

            {{-- Card: Selecionar Fornecedores --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-md p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-2xl">👥</span>
                    <h2 class="text-lg font-semibold text-gray-800">FORNECEDORES PARA ANÁLISE</h2>
                </div>
                
                {{-- Radio: Analisar todos ou selecionar --}}
                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex flex-col gap-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="modo-selecao" value="todos" checked class="w-4 h-4 text-blue-600">
                            <span class="font-medium text-gray-800">Analisar todos (<span id="total-fornecedores-radio">42</span> fornecedores)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="modo-selecao" value="manual" class="w-4 h-4 text-blue-600">
                            <span class="font-medium text-gray-800">Selecionar manualmente</span>
                        </label>
                    </div>
                </div>
                
                {{-- Busca e Filtros --}}
                <div class="mb-4 flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <input type="text" id="buscar-fornecedor" placeholder="Buscar fornecedor..." class="w-full px-4 py-2.5 pl-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <select id="filtro-fornecedores" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="todos">Todos</option>
                        <option value="maior-volume">Maior volume primeiro</option>
                        <option value="ja-analisados">Já analisados</option>
                        <option value="nunca-analisados">Nunca analisados</option>
                    </select>
                </div>
                
                {{-- Tabela de Fornecedores --}}
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-12 px-4 py-3 text-left">
                                    <input type="checkbox" id="selecionar-todos" class="w-4 h-4 text-blue-600 border-gray-300 rounded" checked>
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">CNPJ</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Razão Social</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Volume</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-fornecedores-body" class="divide-y divide-gray-200">
                            {{-- Dados via JS --}}
                        </tbody>
                    </table>
                </div>
                
                {{-- Paginação --}}
                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Mostrando <span id="fornec-pagina-inicio">1</span>-<span id="fornec-pagina-fim">10</span> de <span id="fornec-total">42</span>
                    </div>
                    <div class="flex gap-2" id="fornec-paginacao">
                        {{-- Paginação via JS --}}
                    </div>
                </div>
                
                {{-- Contador de selecionados --}}
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-blue-600">☑</span>
                        <span class="font-medium text-blue-800"><span id="qtd-selecionados">42</span> selecionados</span>
                    </div>
                    <button type="button" id="btn-limpar-selecao" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Limpar seleção
                    </button>
                </div>
            </div>

            {{-- Card: Nível de Análise --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-md p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-2xl">📊</span>
                    <h2 class="text-lg font-semibold text-gray-800">NÍVEL DE ANÁLISE</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    {{-- Nível Rápido --}}
                    <div class="nivel-card cursor-pointer rounded-xl border-2 border-gray-200 p-6 hover:border-blue-400 transition-all" data-nivel="rapido" data-creditos="2">
                        <div class="text-center mb-4">
                            <span class="text-3xl">⚡</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 text-center mb-2">Rápido</h3>
                        <div class="text-center mb-4">
                            <span class="text-2xl font-bold text-blue-600">2</span>
                            <span class="text-sm text-gray-600"> créd/fornec</span>
                        </div>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Cadastro básico</li>
                            <li>• Listas restritivas</li>
                            <li>• Validação cruzada</li>
                        </ul>
                    </div>
                    
                    {{-- Nível Básico (Recomendado) --}}
                    <div class="nivel-card cursor-pointer rounded-xl border-2 border-blue-500 bg-blue-50 p-6 transition-all selected relative" data-nivel="basico" data-creditos="8">
                        <span class="absolute -top-2 -right-2 bg-white text-yellow-600 text-xs px-2 py-0.5 rounded-full font-semibold border-2 border-yellow-500 shadow-sm">Favorito</span>
                        <div class="text-center mb-4">
                            <span class="text-3xl">📋</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 text-center mb-2">Básico</h3>
                        <div class="text-center mb-4">
                            <span class="text-2xl font-bold text-blue-600">8</span>
                            <span class="text-sm text-gray-600"> créd/fornec</span>
                        </div>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• + Protestos</li>
                            <li>• + Dívida ativa</li>
                            <li>• + QSA completo</li>
                        </ul>
                    </div>
                    
                    {{-- Nível Completo --}}
                    <div class="nivel-card cursor-pointer rounded-xl border-2 border-gray-200 p-6 hover:border-blue-400 transition-all" data-nivel="completo" data-creditos="20">
                        <div class="text-center mb-4">
                            <span class="text-3xl">🔍</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 text-center mb-2">Completo</h3>
                        <div class="text-center mb-4">
                            <span class="text-2xl font-bold text-blue-600">20</span>
                            <span class="text-sm text-gray-600"> créd/fornec</span>
                        </div>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• + Certidões</li>
                            <li>• + Processos</li>
                            <li>• + Tudo</li>
                        </ul>
                    </div>
                </div>
                
                <p class="text-sm text-gray-500 text-center">
                    Veja detalhes de cada nível em <a href="#secao-sobre" class="text-blue-600 hover:underline">"Sobre a Solução"</a> abaixo ↓
                </p>
            </div>

            {{-- Card: Resumo do Pedido --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-md p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-2xl">💳</span>
                    <h2 class="text-lg font-semibold text-gray-800">RESUMO DO PEDIDO</h2>
                </div>
                
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Fornecedores selecionados:</span>
                            <span class="font-semibold text-gray-800" id="resumo-fornecedores">42</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nível de análise:</span>
                            <span class="font-semibold text-gray-800" id="resumo-nivel">Básico</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Preço por fornecedor:</span>
                            <span class="font-semibold text-gray-800"><span id="resumo-preco-unitario">8</span> créditos</span>
                        </div>
                        
                        <div class="border-t border-gray-300 pt-3 mt-3"></div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-semibold text-gray-800"><span id="resumo-subtotal">336</span> créditos</span>
                        </div>
                        <div class="flex justify-between" id="linha-desconto">
                            <span class="text-green-600">Desconto volume (10+):</span>
                            <span class="font-semibold text-green-600" id="resumo-desconto">-20%</span>
                        </div>
                        
                        <div class="border-t border-gray-300 pt-3 mt-3"></div>
                        
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-800 text-lg">TOTAL:</span>
                            <span class="text-2xl font-bold text-blue-600" id="resumo-total">269 créditos</span>
                        </div>
                        
                        <div class="border-t border-gray-300 pt-3 mt-3"></div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Seu saldo atual:</span>
                            <span class="font-semibold text-gray-800"><span id="resumo-saldo-atual">147</span> créditos</span>
                        </div>
                        <div class="flex justify-between" id="linha-saldo-apos">
                            <span class="text-gray-600">Saldo após análise:</span>
                            <span class="font-semibold" id="resumo-saldo-apos">
                                <span class="text-red-600">⚠️ -122 créditos (insuficiente)</span>
                            </span>
                        </div>
                    </div>
                </div>
                
                {{-- Botão condicional --}}
                <div id="container-btn-acao">
                    <button type="button" id="btn-comprar-mais" class="w-full px-6 py-3 bg-amber-500 text-white rounded-lg text-sm font-semibold hover:bg-amber-600 transition-colors flex items-center justify-center gap-2">
                        <span>💳</span>
                        <span>Comprar mais créditos</span>
                    </button>
                    <p class="text-sm text-gray-500 text-center mt-3">
                        OU reduza a quantidade de fornecedores / mude o nível
                    </p>
                </div>
                
                <button type="button" id="btn-iniciar-analise" class="hidden w-full px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                    <span>🚀</span>
                    <span>Iniciar Análise de Risco</span>
                </button>
            </div>

            {{-- Card: Resultado da Análise (hidden inicialmente) --}}
            <div id="card-resultado-analise" class="hidden bg-white rounded-xl border border-gray-200 shadow-md p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-2xl">✅</span>
                    <h2 class="text-lg font-semibold text-gray-800">ANÁLISE CONCLUÍDA</h2>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="text-2xl font-bold text-gray-800" id="resultado-total">42</div>
                        <div class="text-xs text-gray-600 mt-1">Analisados</div>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200">
                        <div class="text-2xl font-bold text-green-600" id="resultado-baixo">35</div>
                        <div class="text-xs text-green-700 mt-1">🟢 Baixo</div>
                    </div>
                    <div class="text-center p-4 bg-amber-50 rounded-lg border border-amber-200">
                        <div class="text-2xl font-bold text-amber-600" id="resultado-medio">5</div>
                        <div class="text-xs text-amber-700 mt-1">🟡 Médio</div>
                    </div>
                    <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
                        <div class="text-2xl font-bold text-red-600" id="resultado-alto">2</div>
                        <div class="text-xs text-red-700 mt-1">🔴 Alto</div>
                    </div>
                </div>
            </div>

            {{-- Card: Lista de Resultados (hidden inicialmente) --}}
            <div id="card-lista-resultados" class="hidden bg-white rounded-xl border border-gray-200 shadow-md p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-2xl">📋</span>
                    <h2 class="text-lg font-semibold text-gray-800">RESULTADOS POR FORNECEDOR</h2>
                </div>
                
                {{-- Busca e Filtros --}}
                <div class="mb-4 flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <input type="text" id="buscar-resultado" placeholder="Buscar..." class="w-full px-4 py-2.5 pl-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <select id="filtro-risco" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="todos">Todos</option>
                        <option value="alto">🔴 Risco Alto</option>
                        <option value="medio">🟡 Risco Médio</option>
                        <option value="baixo">🟢 Risco Baixo</option>
                        <option value="alertas">Com alertas</option>
                    </select>
                </div>
                
                {{-- Tabela de Resultados --}}
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Score</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Razão Social</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">CNPJ</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600">Alertas</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-resultados-body" class="divide-y divide-gray-200">
                            {{-- Dados via JS --}}
                        </tbody>
                    </table>
                </div>
                
                {{-- Paginação --}}
                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Mostrando <span id="result-pagina-inicio">1</span>-<span id="result-pagina-fim">10</span> de <span id="result-total">42</span>
                    </div>
                    <div class="flex gap-2" id="result-paginacao">
                        {{-- Paginação via JS --}}
                    </div>
                </div>
                
                {{-- Botões de Exportação --}}
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="button" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <span>📄</span>
                        <span>Exportar Relatório PDF</span>
                    </button>
                    <button type="button" class="px-4 py-2.5 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors flex items-center gap-2">
                        <span>📊</span>
                        <span>Exportar Excel</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- SEPARADOR: SOBRE A SOLUÇÃO --}}
        <div id="secao-sobre" class="my-12">
            <div class="bg-blue-50 border border-blue-200 rounded-xl py-6 px-8 text-center">
                <div class="flex items-center justify-center gap-3">
                    <span class="text-2xl">📘</span>
                    <h2 class="text-xl font-bold text-blue-800">ANÁLISE DE RISCO · SOBRE A SOLUÇÃO</h2>
                </div>
            </div>
        </div>

        {{-- SEÇÃO 2: SOBRE A SOLUÇÃO --}}
        <div class="space-y-6">
            
            {{-- Card: Como Funciona --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-md p-6">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-2xl">🔄</span>
                    <h2 class="text-lg font-semibold text-gray-800">COMO FUNCIONA</h2>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto bg-blue-100 rounded-full flex items-center justify-center text-2xl font-bold text-blue-600 mb-3">1</div>
                        <h4 class="font-semibold text-gray-800 mb-1">Importa SPED</h4>
                        <p class="text-xs text-gray-500">(grátis)</p>
                    </div>
                    <div class="text-center relative">
                        <div class="hidden md:block absolute top-8 -left-4 text-gray-300 text-2xl">→</div>
                        <div class="w-16 h-16 mx-auto bg-blue-100 rounded-full flex items-center justify-center text-2xl font-bold text-blue-600 mb-3">2</div>
                        <h4 class="font-semibold text-gray-800 mb-1">Extrai Fornecedores</h4>
                        <p class="text-xs text-gray-500">Automático</p>
                    </div>
                    <div class="text-center relative">
                        <div class="hidden md:block absolute top-8 -left-4 text-gray-300 text-2xl">→</div>
                        <div class="w-16 h-16 mx-auto bg-blue-100 rounded-full flex items-center justify-center text-2xl font-bold text-blue-600 mb-3">3</div>
                        <h4 class="font-semibold text-gray-800 mb-1">Analisa Dados</h4>
                        <p class="text-xs text-gray-500">Por créditos</p>
                    </div>
                    <div class="text-center relative">
                        <div class="hidden md:block absolute top-8 -left-4 text-gray-300 text-2xl">→</div>
                        <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center text-2xl font-bold text-green-600 mb-3">4</div>
                        <h4 class="font-semibold text-gray-800 mb-1">Score de Risco</h4>
                        <p class="text-xs text-gray-500">Resultado</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600 space-y-2">
                    <p><strong>1.</strong> Importe seu arquivo SPED (gratuito)</p>
                    <p><strong>2.</strong> O sistema extrai automaticamente todos os fornecedores</p>
                    <p><strong>3.</strong> Escolha quais fornecedores analisar e o nível de análise</p>
                    <p><strong>4.</strong> Receba o score de risco e alertas de cada fornecedor</p>
                </div>
            </div>

            {{-- Cards dos Níveis --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Nível Rápido --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">⚡</span>
                            <h3 class="text-lg font-bold text-gray-800">NÍVEL RÁPIDO</h3>
                        </div>
                        <span class="text-sm font-semibold text-blue-600">2 créd/fornec</span>
                    </div>
                    
                    <p class="text-sm text-gray-600 mb-4">
                        <strong>Ideal para:</strong> Verificação rápida de situação básica
                    </p>
                    
                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">📋 Cadastro Básico</h4>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>├── Situação CNPJ</li>
                                    <li>├── Inscrição Estadual</li>
                                    <li>└── Simples Nacional</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">⚠️ Listas Restritivas</h4>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>├── CEIS (Inidôneas)</li>
                                    <li>├── CNEP (Punidas)</li>
                                    <li>└── Trabalho Escravo</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">🔗 Validação Cruzada</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>├── CNPJ vs Registro 0150</li>
                                <li>└── IE vs UF do Participante</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-3 text-sm">
                        <h4 class="font-semibold text-gray-700 mb-2">💰 Custo com desconto:</h4>
                        <ul class="text-gray-600 space-y-1">
                            <li>• 1-9 fornecedores: <strong>2 créditos</strong> cada</li>
                            <li>• 10-49 fornecedores: <strong>1.6 créditos</strong> cada (-20%)</li>
                            <li>• 50+ fornecedores: <strong>1.4 créditos</strong> cada (-30%)</li>
                        </ul>
                    </div>
                </div>
                
                {{-- Nível Básico --}}
                <div class="bg-white rounded-xl border-2 border-blue-500 shadow-md p-6 relative">
                    <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-amber-400 text-amber-900 text-xs font-bold px-3 py-1 rounded-full">
                        ⭐ RECOMENDADO
                    </div>
                    
                    <div class="flex items-center justify-between mb-4 mt-2">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📋</span>
                            <h3 class="text-lg font-bold text-gray-800">NÍVEL BÁSICO</h3>
                        </div>
                        <span class="text-sm font-semibold text-blue-600">8 créd/fornec</span>
                    </div>
                    
                    <p class="text-sm text-gray-600 mb-4">
                        <strong>Ideal para:</strong> Análise completa de compliance e restrições
                    </p>
                    
                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <p class="text-sm text-green-600 font-semibold mb-3">✅ Tudo do Rápido +</p>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">📋 Cadastro Completo</h4>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>├── Quadro Societário (QSA)</li>
                                    <li>├── CNAE Principal/Secundários</li>
                                    <li>├── Data de Abertura</li>
                                    <li>└── Capital Social</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">⚠️ Listas Completas</h4>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>├── CEPIM (Entidades)</li>
                                    <li>└── Acordo de Leniência</li>
                                </ul>
                                
                                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2 mt-3">💰 Restrições</h4>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>├── Protestos em Cartório</li>
                                    <li>└── Dívida Ativa Federal</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">🔗 Validação Cruzada</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>└── CNAE vs CFOP</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-3 text-sm">
                        <h4 class="font-semibold text-gray-700 mb-2">💰 Custo com desconto:</h4>
                        <ul class="text-gray-600 space-y-1">
                            <li>• 1-9 fornecedores: <strong>8 créditos</strong> cada</li>
                            <li>• 10-49 fornecedores: <strong>6.4 créditos</strong> cada (-20%)</li>
                            <li>• 50+ fornecedores: <strong>5.6 créditos</strong> cada (-30%)</li>
                        </ul>
                    </div>
                </div>
                
                {{-- Nível Completo --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🔍</span>
                            <h3 class="text-lg font-bold text-gray-800">NÍVEL COMPLETO</h3>
                        </div>
                        <span class="text-sm font-semibold text-blue-600">20 créd/fornec</span>
                    </div>
                    
                    <p class="text-sm text-gray-600 mb-4">
                        <strong>Ideal para:</strong> Due diligence completo e relatório para auditoria
                    </p>
                    
                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <p class="text-sm text-green-600 font-semibold mb-3">✅ Tudo do Básico +</p>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">📜 Certidões Negativas</h4>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>├── CND Federal (PGFN)</li>
                                    <li>├── CND Estadual (SEFAZ)</li>
                                    <li>├── CND Municipal</li>
                                    <li>├── CNDT Trabalhista (TST)</li>
                                    <li>└── CRF FGTS (Caixa)</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">⚖️ Processos e Ações</h4>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>├── Processos TCU</li>
                                    <li>└── Falência/Recuperação</li>
                                </ul>
                                
                                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2 mt-3">🔗 Validação Cruzada</h4>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>└── Participante vs Notas</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-3 text-sm">
                        <h4 class="font-semibold text-gray-700 mb-2">💰 Custo com desconto:</h4>
                        <ul class="text-gray-600 space-y-1">
                            <li>• 1-9 fornecedores: <strong>20 créditos</strong> cada</li>
                            <li>• 10-49 fornecedores: <strong>16 créditos</strong> cada (-20%)</li>
                            <li>• 50+ fornecedores: <strong>14 créditos</strong> cada (-30%)</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Card: Tabela Comparativa --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-md p-6">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-2xl">📊</span>
                    <h2 class="text-lg font-semibold text-gray-800">COMPARATIVO DOS NÍVEIS</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Recurso</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600">Rápido</th>
                                <th class="px-4 py-3 text-center font-medium text-blue-600 bg-blue-50">Básico</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600">Completo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="px-4 py-3 text-gray-800">Situação CNPJ</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                                <td class="px-4 py-3 text-center text-green-600 bg-blue-50">✅</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">Inscrição Estadual</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                                <td class="px-4 py-3 text-center text-green-600 bg-blue-50">✅</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">Simples Nacional</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                                <td class="px-4 py-3 text-center text-green-600 bg-blue-50">✅</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">CEIS/CNEP/Trab.Escravo</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                                <td class="px-4 py-3 text-center text-green-600 bg-blue-50">✅</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">Validação CNPJ/IE</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                                <td class="px-4 py-3 text-center text-green-600 bg-blue-50">✅</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-3 text-gray-800 font-medium" colspan="4"></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">Quadro Societário (QSA)</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-green-600 bg-blue-50">✅</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">CNAE/Data/Capital</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-green-600 bg-blue-50">✅</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">CEPIM/Acordo Leniência</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-green-600 bg-blue-50">✅</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">Protestos</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-green-600 bg-blue-50">✅</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">Dívida Ativa</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-green-600 bg-blue-50">✅</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">Validação CNAE vs CFOP</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-green-600 bg-blue-50">✅</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-3 text-gray-800 font-medium" colspan="4"></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">CND Federal</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-red-400 bg-blue-50">❌</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">CND Estadual</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-red-400 bg-blue-50">❌</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">CND Municipal</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-red-400 bg-blue-50">❌</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">CNDT Trabalhista</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-red-400 bg-blue-50">❌</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">CRF FGTS</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-red-400 bg-blue-50">❌</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">Processos TCU</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-red-400 bg-blue-50">❌</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">Falência/Recuperação</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-red-400 bg-blue-50">❌</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-800">Validação Participante×Nota</td>
                                <td class="px-4 py-3 text-center text-red-400">❌</td>
                                <td class="px-4 py-3 text-center text-red-400 bg-blue-50">❌</td>
                                <td class="px-4 py-3 text-center text-green-600">✅</td>
                            </tr>
                            <tr class="bg-gray-100 font-semibold">
                                <td class="px-4 py-3 text-gray-800">Preço/fornecedor</td>
                                <td class="px-4 py-3 text-center text-gray-800">2 créd</td>
                                <td class="px-4 py-3 text-center text-blue-600 bg-blue-50">8 créd</td>
                                <td class="px-4 py-3 text-center text-gray-800">20 créd</td>
                            </tr>
                            <tr class="font-semibold">
                                <td class="px-4 py-3 text-gray-800">Desconto 10+ fornecedores</td>
                                <td class="px-4 py-3 text-center text-green-600">-20%</td>
                                <td class="px-4 py-3 text-center text-green-600 bg-blue-50">-20%</td>
                                <td class="px-4 py-3 text-center text-green-600">-20%</td>
                            </tr>
                            <tr class="font-semibold">
                                <td class="px-4 py-3 text-gray-800">Desconto 50+ fornecedores</td>
                                <td class="px-4 py-3 text-center text-green-600">-30%</td>
                                <td class="px-4 py-3 text-center text-green-600 bg-blue-50">-30%</td>
                                <td class="px-4 py-3 text-center text-green-600">-30%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Card: Score de Risco Explicado --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-md p-6">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-2xl">🎯</span>
                    <h2 class="text-lg font-semibold text-gray-800">ENTENDENDO O SCORE DE RISCO</h2>
                </div>
                
                <p class="text-sm text-gray-600 mb-6">
                    O score varia de 0 a 100 e indica o nível de confiabilidade do fornecedor baseado em todos os dados consultados.
                </p>
                
                <div class="space-y-3 mb-6">
                    <div class="flex items-center gap-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <span class="text-2xl">🟢</span>
                        <div class="flex-1">
                            <div class="font-semibold text-green-800">80-100 · BAIXO RISCO</div>
                            <div class="text-sm text-green-700">Fornecedor regular, sem alertas críticos</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <span class="text-2xl">🟡</span>
                        <div class="flex-1">
                            <div class="font-semibold text-amber-800">50-79 · MÉDIO RISCO</div>
                            <div class="text-sm text-amber-700">Alguns alertas, requer atenção</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                        <span class="text-2xl">🟠</span>
                        <div class="flex-1">
                            <div class="font-semibold text-orange-800">30-49 · ALTO RISCO</div>
                            <div class="text-sm text-orange-700">Múltiplos alertas, avaliar com cuidado</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <span class="text-2xl">🔴</span>
                        <div class="flex-1">
                            <div class="font-semibold text-red-800">0-29 · CRÍTICO</div>
                            <div class="text-sm text-red-700">Problemas graves, considerar substituição</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                    <h4 class="font-semibold text-gray-700 mb-2">O score é calculado automaticamente considerando:</h4>
                    <ul class="space-y-1">
                        <li>• Situação cadastral (CNPJ, IE)</li>
                        <li>• Presença em listas restritivas (peso alto)</li>
                        <li>• Certidões negativas (se consultadas)</li>
                        <li>• Protestos e dívidas (se consultados)</li>
                        <li>• Validações cruzadas com o SPED</li>
                    </ul>
                </div>
            </div>

            {{-- Card: FAQ --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-md p-6">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-2xl">❓</span>
                    <h2 class="text-lg font-semibold text-gray-800">PERGUNTAS FREQUENTES</h2>
                </div>
                
                <div class="space-y-4">
                    <div class="border-b border-gray-200 pb-4">
                        <h4 class="font-semibold text-gray-800 mb-2">▸ Posso analisar apenas alguns fornecedores?</h4>
                        <p class="text-sm text-gray-600">Sim! Selecione manualmente quais deseja analisar.</p>
                    </div>
                    <div class="border-b border-gray-200 pb-4">
                        <h4 class="font-semibold text-gray-800 mb-2">▸ O desconto por volume é automático?</h4>
                        <p class="text-sm text-gray-600">Sim, aplicado automaticamente a partir de 10 fornecedores.</p>
                    </div>
                    <div class="border-b border-gray-200 pb-4">
                        <h4 class="font-semibold text-gray-800 mb-2">▸ Posso mudar o nível depois?</h4>
                        <p class="text-sm text-gray-600">Você pode fazer uma nova análise em nível superior a qualquer momento, pagando a diferença.</p>
                    </div>
                    <div class="border-b border-gray-200 pb-4">
                        <h4 class="font-semibold text-gray-800 mb-2">▸ Os dados ficam salvos?</h4>
                        <p class="text-sm text-gray-600">Sim, no histórico do cliente por 12 meses.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-2">▸ Posso monitorar os fornecedores?</h4>
                        <p class="text-sm text-gray-600">Sim! Após a análise, ative o monitoramento por fornecedor.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Detalhes do Fornecedor --}}
    <div id="modal-detalhes" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h3 class="text-lg font-semibold text-gray-800" id="modal-titulo">DETALHES DO FORNECEDOR</h3>
                <button type="button" id="fechar-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6" id="modal-conteudo">
                {{-- Conteúdo via JS --}}
            </div>
        </div>
    </div>
</div>

<script>
// ========================================
// ESTADO GLOBAL
// ========================================
window.analiseRiscoState = {
    saldoCreditos: 147,
    nivelSelecionado: 'basico',
    modoSelecao: 'todos',
    fornecedoresSelecionados: [],
    paginaFornecedores: 1,
    paginaResultados: 1,
    itensPorPagina: 10,
    analiseExecutada: false,
    buscaFornecedor: '',
    buscaResultado: '',
    filtroRisco: 'todos'
};

// ========================================
// DADOS MOCKADOS
// ========================================
const dadosSPED = {
    cliente: 'ACME COMÉRCIO LTDA',
    arquivo: 'SPED_EFD_202312.txt',
    periodo: '01/12/2023 a 31/12/2023',
    importadoEm: '02/01/2025 às 14:32',
    totalFornecedores: 42,
    totalNotas: 156,
    totalProdutos: 847,
    volumeTotal: 1245890
};

const fornecedoresMock = [
    { id: 1, cnpj: '11.111.111/0001-11', nome: 'Fornecedor Alpha', volume: 245000, score: 92, risco: 'baixo', alertas: 0, analisado: true },
    { id: 2, cnpj: '22.222.222/0001-22', nome: 'Distribuidora Beta', volume: 189500, score: 88, risco: 'baixo', alertas: 0, analisado: true },
    { id: 3, cnpj: '33.333.333/0001-33', nome: 'Indústria Gamma', volume: 156200, score: 65, risco: 'medio', alertas: 2, analisado: true },
    { id: 4, cnpj: '44.444.444/0001-44', nome: 'Atacado Delta', volume: 98750, score: 58, risco: 'medio', alertas: 3, analisado: true },
    { id: 5, cnpj: '55.555.555/0001-55', nome: 'Comércio Epsilon', volume: 67300, score: 23, risco: 'alto', alertas: 7, analisado: true, problemas: ['Consta na lista CEIS (CGU)', 'Consta na lista de Trabalho Escravo (MTE)', 'CND Federal vencida', '3 protestos em cartório (total R$ 45.000)', 'Dívida ativa federal (R$ 128.000)', 'CNAE incompatível com CFOP utilizado', 'IE suspensa no estado do participante'] },
    { id: 6, cnpj: '66.666.666/0001-66', nome: 'Empresa Problemática', volume: 45100, score: 15, risco: 'alto', alertas: 9, analisado: true, problemas: ['CNPJ Baixado', 'IE Cancelada', 'Lista CEIS', 'Lista Trabalho Escravo', 'CND Federal vencida', 'CND Estadual vencida', 'Protestos', 'Dívida Ativa', 'Falência decretada'] }
];

// Gerar mais fornecedores mock
for (let i = 7; i <= 42; i++) {
    const scores = [85, 90, 95, 70, 75, 80, 55, 60, 88, 92, 78, 82];
    const riscos = ['baixo', 'baixo', 'baixo', 'medio', 'medio', 'medio', 'medio', 'medio', 'baixo', 'baixo', 'medio', 'baixo'];
    const idx = (i - 7) % 12;
    fornecedoresMock.push({
        id: i,
        cnpj: `${String(i).padStart(2, '0')}.${String(i*3).padStart(3, '0')}.${String(i*5).padStart(3, '0')}/0001-${String((i*7)%100).padStart(2, '0')}`,
        nome: `Fornecedor ${i}`,
        volume: Math.floor(Math.random() * 150000) + 10000,
        score: scores[idx],
        risco: riscos[idx],
        alertas: riscos[idx] === 'baixo' ? 0 : Math.floor(Math.random() * 3) + 1,
        analisado: false
    });
}

// Inicializar todos selecionados
window.analiseRiscoState.fornecedoresSelecionados = fornecedoresMock.map(f => f.id);

// ========================================
// CONFIGURAÇÃO DE NÍVEIS E PREÇOS
// ========================================
const niveisConfig = {
    rapido: { nome: 'Rápido', creditos: 2 },
    basico: { nome: 'Básico', creditos: 8 },
    completo: { nome: 'Completo', creditos: 20 }
};

function calcularDesconto(qtdFornecedores) {
    if (qtdFornecedores >= 50) return 0.30;
    if (qtdFornecedores >= 10) return 0.20;
    return 0;
}

function calcularCreditos(qtdFornecedores, nivel) {
    const creditosPorFornec = niveisConfig[nivel].creditos;
    const desconto = calcularDesconto(qtdFornecedores);
    const subtotal = qtdFornecedores * creditosPorFornec;
    const total = Math.round(subtotal * (1 - desconto) * 10) / 10;
    return { subtotal, desconto, total };
}

// ========================================
// FUNÇÕES DE RENDERIZAÇÃO
// ========================================
function renderizarTabelaFornecedores() {
    const tbody = document.getElementById('tabela-fornecedores-body');
    if (!tbody) return;
    
    let dados = [...fornecedoresMock];
    const busca = window.analiseRiscoState.buscaFornecedor.toLowerCase();
    
    if (busca) {
        dados = dados.filter(f => f.nome.toLowerCase().includes(busca) || f.cnpj.includes(busca));
    }
    
    const filtro = document.getElementById('filtro-fornecedores')?.value || 'todos';
    if (filtro === 'maior-volume') {
        dados.sort((a, b) => b.volume - a.volume);
    } else if (filtro === 'ja-analisados') {
        dados = dados.filter(f => f.analisado);
    } else if (filtro === 'nunca-analisados') {
        dados = dados.filter(f => !f.analisado);
    }
    
    const inicio = (window.analiseRiscoState.paginaFornecedores - 1) * window.analiseRiscoState.itensPorPagina;
    const fim = inicio + window.analiseRiscoState.itensPorPagina;
    const dadosPagina = dados.slice(inicio, fim);
    
    tbody.innerHTML = dadosPagina.map(f => {
        const checked = window.analiseRiscoState.fornecedoresSelecionados.includes(f.id) ? 'checked' : '';
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <input type="checkbox" class="fornec-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded" data-id="${f.id}" ${checked}>
                </td>
                <td class="px-4 py-3 text-gray-600 font-mono text-xs">${f.cnpj}</td>
                <td class="px-4 py-3 text-gray-800 font-medium">${f.nome}</td>
                <td class="px-4 py-3 text-right text-gray-800">R$ ${f.volume.toLocaleString('pt-BR')}</td>
            </tr>
        `;
    }).join('');
    
    // Event listeners para checkboxes
    document.querySelectorAll('.fornec-checkbox').forEach(cb => {
        cb.addEventListener('change', (e) => {
            const id = parseInt(e.target.dataset.id);
            if (e.target.checked) {
                if (!window.analiseRiscoState.fornecedoresSelecionados.includes(id)) {
                    window.analiseRiscoState.fornecedoresSelecionados.push(id);
                }
            } else {
                window.analiseRiscoState.fornecedoresSelecionados = window.analiseRiscoState.fornecedoresSelecionados.filter(x => x !== id);
            }
            atualizarContadores();
            atualizarResumo();
        });
    });
    
    // Atualizar info de paginação
    document.getElementById('fornec-pagina-inicio').textContent = dados.length > 0 ? inicio + 1 : 0;
    document.getElementById('fornec-pagina-fim').textContent = Math.min(fim, dados.length);
    document.getElementById('fornec-total').textContent = dados.length;
    
    renderizarPaginacao('fornec-paginacao', window.analiseRiscoState.paginaFornecedores, Math.ceil(dados.length / window.analiseRiscoState.itensPorPagina), 'fornecedores');
}

function renderizarTabelaResultados() {
    const tbody = document.getElementById('tabela-resultados-body');
    if (!tbody) return;
    
    let dados = fornecedoresMock.filter(f => window.analiseRiscoState.fornecedoresSelecionados.includes(f.id));
    const busca = window.analiseRiscoState.buscaResultado.toLowerCase();
    
    if (busca) {
        dados = dados.filter(f => f.nome.toLowerCase().includes(busca) || f.cnpj.includes(busca));
    }
    
    const filtro = window.analiseRiscoState.filtroRisco;
    if (filtro === 'alto') {
        dados = dados.filter(f => f.risco === 'alto');
    } else if (filtro === 'medio') {
        dados = dados.filter(f => f.risco === 'medio');
    } else if (filtro === 'baixo') {
        dados = dados.filter(f => f.risco === 'baixo');
    } else if (filtro === 'alertas') {
        dados = dados.filter(f => f.alertas > 0);
    }
    
    // Ordenar por score (menor primeiro)
    dados.sort((a, b) => a.score - b.score);
    
    const inicio = (window.analiseRiscoState.paginaResultados - 1) * window.analiseRiscoState.itensPorPagina;
    const fim = inicio + window.analiseRiscoState.itensPorPagina;
    const dadosPagina = dados.slice(inicio, fim);
    
    const riscoConfig = {
        baixo: { icon: '🟢', bg: 'bg-green-100', text: 'text-green-700' },
        medio: { icon: '🟡', bg: 'bg-amber-100', text: 'text-amber-700' },
        alto: { icon: '🔴', bg: 'bg-red-100', text: 'text-red-700' }
    };
    
    tbody.innerHTML = dadosPagina.map(f => {
        const cfg = riscoConfig[f.risco] || riscoConfig.baixo;
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold ${cfg.bg} ${cfg.text}">
                        ${cfg.icon} ${f.score}
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-800 font-medium">${f.nome}</td>
                <td class="px-4 py-3 text-gray-600 font-mono text-xs">${f.cnpj}</td>
                <td class="px-4 py-3 text-center">
                    ${f.alertas > 0 ? `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs font-semibold">${f.alertas}</span>` : '<span class="text-gray-400">-</span>'}
                </td>
                <td class="px-4 py-3 text-center">
                    <button type="button" class="btn-ver-detalhes text-blue-600 hover:text-blue-800 text-sm font-medium" data-id="${f.id}">
                        Ver detalhes
                    </button>
                </td>
            </tr>
        `;
    }).join('');
    
    // Event listeners
    document.querySelectorAll('.btn-ver-detalhes').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = parseInt(e.target.dataset.id);
            const fornecedor = fornecedoresMock.find(f => f.id === id);
            if (fornecedor) abrirModalDetalhes(fornecedor);
        });
    });
    
    // Atualizar info de paginação
    document.getElementById('result-pagina-inicio').textContent = dados.length > 0 ? inicio + 1 : 0;
    document.getElementById('result-pagina-fim').textContent = Math.min(fim, dados.length);
    document.getElementById('result-total').textContent = dados.length;
    
    renderizarPaginacao('result-paginacao', window.analiseRiscoState.paginaResultados, Math.ceil(dados.length / window.analiseRiscoState.itensPorPagina), 'resultados');
}

function renderizarPaginacao(containerId, paginaAtual, totalPaginas, tipo) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';
    
    if (totalPaginas <= 1) return;
    
    const criarBotao = (texto, pagina, ativo = false) => {
        const btn = document.createElement('button');
        btn.className = `px-3 py-1.5 border rounded-lg text-sm ${ativo ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50'}`;
        btn.textContent = texto;
        btn.addEventListener('click', () => {
            if (tipo === 'fornecedores') {
                window.analiseRiscoState.paginaFornecedores = pagina;
                renderizarTabelaFornecedores();
            } else {
                window.analiseRiscoState.paginaResultados = pagina;
                renderizarTabelaResultados();
            }
        });
        return btn;
    };
    
    if (paginaAtual > 1) {
        container.appendChild(criarBotao('<', paginaAtual - 1));
    }
    
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaAtual - 1 && i <= paginaAtual + 1)) {
            container.appendChild(criarBotao(String(i), i, i === paginaAtual));
        } else if (i === paginaAtual - 2 || i === paginaAtual + 2) {
            const span = document.createElement('span');
            span.className = 'px-2 text-gray-500';
            span.textContent = '...';
            container.appendChild(span);
        }
    }
    
    if (paginaAtual < totalPaginas) {
        container.appendChild(criarBotao('>', paginaAtual + 1));
    }
}

function atualizarContadores() {
    const qtd = window.analiseRiscoState.fornecedoresSelecionados.length;
    document.getElementById('qtd-selecionados').textContent = qtd;
    document.getElementById('total-fornecedores-radio').textContent = fornecedoresMock.length;
    
    // Atualizar checkbox "selecionar todos"
    const selectAll = document.getElementById('selecionar-todos');
    if (selectAll) {
        selectAll.checked = qtd === fornecedoresMock.length;
    }
}

function atualizarResumo() {
    const qtdFornecedores = window.analiseRiscoState.fornecedoresSelecionados.length;
    const nivel = window.analiseRiscoState.nivelSelecionado;
    const { subtotal, desconto, total } = calcularCreditos(qtdFornecedores, nivel);
    const saldoAtual = window.analiseRiscoState.saldoCreditos;
    const saldoApos = saldoAtual - total;
    
    document.getElementById('resumo-fornecedores').textContent = qtdFornecedores;
    document.getElementById('resumo-nivel').textContent = niveisConfig[nivel].nome;
    document.getElementById('resumo-preco-unitario').textContent = niveisConfig[nivel].creditos;
    document.getElementById('resumo-subtotal').textContent = subtotal;
    document.getElementById('resumo-total').textContent = `${total} créditos`;
    document.getElementById('resumo-saldo-atual').textContent = saldoAtual;
    
    // Desconto
    const linhaDesconto = document.getElementById('linha-desconto');
    if (desconto > 0) {
        linhaDesconto.classList.remove('hidden');
        const percentual = Math.round(desconto * 100);
        const faixa = qtdFornecedores >= 50 ? '50+' : '10+';
        document.getElementById('resumo-desconto').textContent = `-${percentual}%`;
        linhaDesconto.querySelector('span:first-child').textContent = `Desconto volume (${faixa}):`;
    } else {
        linhaDesconto.classList.add('hidden');
    }
    
    // Saldo após
    const saldoAposEl = document.getElementById('resumo-saldo-apos');
    if (saldoApos >= 0) {
        saldoAposEl.innerHTML = `<span class="text-green-600">${saldoApos} créditos ✅</span>`;
        document.getElementById('container-btn-acao').classList.add('hidden');
        document.getElementById('btn-iniciar-analise').classList.remove('hidden');
    } else {
        saldoAposEl.innerHTML = `<span class="text-red-600">⚠️ ${saldoApos} créditos (insuficiente)</span>`;
        document.getElementById('container-btn-acao').classList.remove('hidden');
        document.getElementById('btn-iniciar-analise').classList.add('hidden');
    }
}

function abrirModalDetalhes(fornecedor) {
    const modal = document.getElementById('modal-detalhes');
    const titulo = document.getElementById('modal-titulo');
    const conteudo = document.getElementById('modal-conteudo');
    
    titulo.textContent = fornecedor.nome;
    
    const riscoConfig = {
        baixo: { label: 'BAIXO RISCO', icon: '🟢', cor: 'text-green-700', bg: 'bg-green-100', border: 'border-green-300' },
        medio: { label: 'MÉDIO RISCO', icon: '🟡', cor: 'text-amber-700', bg: 'bg-amber-100', border: 'border-amber-300' },
        alto: { label: 'ALTO RISCO', icon: '🔴', cor: 'text-red-700', bg: 'bg-red-100', border: 'border-red-300' }
    };
    const cfg = riscoConfig[fornecedor.risco] || riscoConfig.baixo;
    
    let alertasHtml = '';
    if (fornecedor.problemas && fornecedor.problemas.length > 0) {
        alertasHtml = `
            <div class="mb-6">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <span>⚠️</span>
                    ALERTAS (${fornecedor.problemas.length})
                </h4>
                <div class="space-y-2 bg-red-50 border border-red-200 rounded-lg p-4">
                    ${fornecedor.problemas.map(p => `<div class="text-sm text-red-800">🔴 ${p}</div>`).join('')}
                </div>
            </div>
        `;
    }
    
    conteudo.innerHTML = `
        <div class="flex items-start gap-4 mb-6">
            <div class="text-center p-4 ${cfg.bg} ${cfg.border} border-2 rounded-lg">
                <div class="text-3xl font-bold ${cfg.cor}">${fornecedor.score}</div>
                <div class="text-sm font-semibold ${cfg.cor} mt-1">${cfg.label}</div>
                <div class="text-2xl mt-2">${cfg.icon}</div>
            </div>
            <div class="flex-1">
                <div class="space-y-2 text-sm">
                    <div><span class="text-gray-600">CNPJ:</span> <span class="font-semibold text-gray-800">${fornecedor.cnpj}</span></div>
                    <div><span class="text-gray-600">Volume:</span> <span class="font-semibold text-gray-800">R$ ${fornecedor.volume.toLocaleString('pt-BR')}</span></div>
                </div>
            </div>
        </div>
        
        ${alertasHtml}
        
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <h4 class="font-semibold text-gray-800 mb-2">📋 CADASTRO</h4>
                <div class="space-y-1 text-sm">
                    <div>${fornecedor.risco === 'alto' ? '❌' : '✅'} CNPJ ${fornecedor.risco === 'alto' ? 'Baixado' : 'Ativo'}</div>
                    <div>✅ QSA consultado</div>
                    <div>${fornecedor.risco === 'alto' ? '⚠️ IE Suspensa' : '✅ IE Ativa'}</div>
                    <div>✅ Simples: Não optante</div>
                </div>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800 mb-2">📜 CERTIDÕES</h4>
                <div class="space-y-1 text-sm">
                    <div>${fornecedor.risco === 'alto' ? '❌ CND Federal (vencida)' : '✅ CND Federal'}</div>
                    <div>✅ CND Estadual</div>
                    <div>✅ CND Municipal</div>
                    <div>✅ CNDT</div>
                    <div>✅ CRF FGTS</div>
                </div>
            </div>
        </div>
        
        <div class="flex gap-3">
            <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2">
                <span>📄</span> Exportar PDF
            </button>
            <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2">
                <span>🔔</span> Monitorar
            </button>
        </div>
    `;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function executarAnalise() {
    window.analiseRiscoState.analiseExecutada = true;
    
    // Atualizar saldo
    const qtdFornecedores = window.analiseRiscoState.fornecedoresSelecionados.length;
    const nivel = window.analiseRiscoState.nivelSelecionado;
    const { total } = calcularCreditos(qtdFornecedores, nivel);
    window.analiseRiscoState.saldoCreditos -= total;
    document.getElementById('saldo-creditos').textContent = window.analiseRiscoState.saldoCreditos;
    
    // Calcular resultados
    const selecionados = fornecedoresMock.filter(f => window.analiseRiscoState.fornecedoresSelecionados.includes(f.id));
    const baixo = selecionados.filter(f => f.risco === 'baixo').length;
    const medio = selecionados.filter(f => f.risco === 'medio').length;
    const alto = selecionados.filter(f => f.risco === 'alto').length;
    
    document.getElementById('resultado-total').textContent = selecionados.length;
    document.getElementById('resultado-baixo').textContent = baixo;
    document.getElementById('resultado-medio').textContent = medio;
    document.getElementById('resultado-alto').textContent = alto;
    
    // Mostrar cards de resultado
    document.getElementById('card-resultado-analise').classList.remove('hidden');
    document.getElementById('card-lista-resultados').classList.remove('hidden');
    
    // Renderizar tabela de resultados
    renderizarTabelaResultados();
    
    // Scroll para os resultados
    document.getElementById('card-resultado-analise').scrollIntoView({ behavior: 'smooth' });
}

// ========================================
// INICIALIZAÇÃO
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // Renderizar tabela inicial
    renderizarTabelaFornecedores();
    atualizarContadores();
    atualizarResumo();
    
    // Event: Seleção de nível
    document.querySelectorAll('.nivel-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.nivel-card').forEach(c => {
                c.classList.remove('selected', 'border-blue-500', 'bg-blue-50');
                c.classList.add('border-gray-200');
            });
            card.classList.add('selected', 'border-blue-500', 'bg-blue-50');
            card.classList.remove('border-gray-200');
            window.analiseRiscoState.nivelSelecionado = card.dataset.nivel;
            atualizarResumo();
        });
    });
    
    // Event: Modo de seleção
    document.querySelectorAll('input[name="modo-selecao"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            window.analiseRiscoState.modoSelecao = e.target.value;
            if (e.target.value === 'todos') {
                window.analiseRiscoState.fornecedoresSelecionados = fornecedoresMock.map(f => f.id);
            }
            renderizarTabelaFornecedores();
            atualizarContadores();
            atualizarResumo();
        });
    });
    
    // Event: Selecionar todos
    document.getElementById('selecionar-todos')?.addEventListener('change', (e) => {
        if (e.target.checked) {
            window.analiseRiscoState.fornecedoresSelecionados = fornecedoresMock.map(f => f.id);
        } else {
            window.analiseRiscoState.fornecedoresSelecionados = [];
        }
        renderizarTabelaFornecedores();
        atualizarContadores();
        atualizarResumo();
    });
    
    // Event: Limpar seleção
    document.getElementById('btn-limpar-selecao')?.addEventListener('click', () => {
        window.analiseRiscoState.fornecedoresSelecionados = [];
        document.querySelector('input[name="modo-selecao"][value="manual"]').checked = true;
        window.analiseRiscoState.modoSelecao = 'manual';
        renderizarTabelaFornecedores();
        atualizarContadores();
        atualizarResumo();
    });
    
    // Event: Busca fornecedores
    document.getElementById('buscar-fornecedor')?.addEventListener('input', (e) => {
        window.analiseRiscoState.buscaFornecedor = e.target.value;
        window.analiseRiscoState.paginaFornecedores = 1;
        renderizarTabelaFornecedores();
    });
    
    // Event: Filtro fornecedores
    document.getElementById('filtro-fornecedores')?.addEventListener('change', () => {
        window.analiseRiscoState.paginaFornecedores = 1;
        renderizarTabelaFornecedores();
    });
    
    // Event: Busca resultados
    document.getElementById('buscar-resultado')?.addEventListener('input', (e) => {
        window.analiseRiscoState.buscaResultado = e.target.value;
        window.analiseRiscoState.paginaResultados = 1;
        renderizarTabelaResultados();
    });
    
    // Event: Filtro risco
    document.getElementById('filtro-risco')?.addEventListener('change', (e) => {
        window.analiseRiscoState.filtroRisco = e.target.value;
        window.analiseRiscoState.paginaResultados = 1;
        renderizarTabelaResultados();
    });
    
    // Event: Iniciar análise
    document.getElementById('btn-iniciar-analise')?.addEventListener('click', executarAnalise);
    
    // Event: Trocar arquivo
    document.getElementById('btn-trocar-arquivo')?.addEventListener('click', () => {
        window.location.href = '/app/sped_importar';
    });
    
    // Event: Comprar créditos
    document.getElementById('btn-comprar-creditos')?.addEventListener('click', () => {
        alert('Funcionalidade de compra de créditos será implementada.');
    });
    document.getElementById('btn-comprar-mais')?.addEventListener('click', () => {
        alert('Funcionalidade de compra de créditos será implementada.');
    });
    
    // Event: Fechar modal
    document.getElementById('fechar-modal')?.addEventListener('click', () => {
        document.getElementById('modal-detalhes').classList.add('hidden');
        document.getElementById('modal-detalhes').classList.remove('flex');
    });
    
    document.getElementById('modal-detalhes')?.addEventListener('click', (e) => {
        if (e.target.id === 'modal-detalhes') {
            document.getElementById('modal-detalhes').classList.add('hidden');
            document.getElementById('modal-detalhes').classList.remove('flex');
        }
    });
});
</script>
