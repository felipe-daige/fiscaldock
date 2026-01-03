{{-- Consultar Listas Restritivas --}}
<div class="min-h-screen bg-gray-50" id="consultar-listas-restritivas-container">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">
                        Consultar Listas Restritivas
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">
                        Verifique se empresas ou pessoas constam em listas de impedimento
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">💳</span>
                    <span class="text-sm font-semibold text-gray-800">147 créditos</span>
                    <button class="text-xs text-blue-600 hover:text-blue-700 font-semibold ml-1">
                        [+ Comprar]
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="space-y-6">
            {{-- Tabs de Modo de Consulta --}}
            <div class="flex justify-center mb-6">
                <div class="inline-flex items-center gap-1 p-1 rounded-full bg-gray-100 shadow-sm">
                    <button 
                        type="button"
                        class="modo-tab px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 bg-white text-gray-900 shadow-sm"
                        data-modo="unica"
                        aria-selected="true"
                    >
                        📝 Consulta Única
                    </button>
                    <button 
                        type="button"
                        class="modo-tab px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 hover:bg-gray-50"
                        data-modo="lote"
                        aria-selected="false"
                    >
                        📋 Consulta em Lote
                    </button>
                </div>
            </div>

            {{-- Seção: Consulta Única --}}
            <div id="secao-unica" class="modo-secao">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Consulta Individual</h2>

                    {{-- Seleção do tipo de documento --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Documento:</label>
                        <div class="flex flex-row gap-4">
                            {{-- Card CNPJ --}}
                            <label id="card-tipo-cnpj" class="flex-1 flex items-center justify-center p-4 border-2 border-blue-600 rounded-lg cursor-pointer bg-blue-50 hover:bg-blue-100 transition-colors">
                                <input type="radio" name="tipo-documento" value="cnpj" checked class="sr-only" id="radio-cnpj">
                                <div class="text-center">
                                    <div class="text-2xl mb-2">🏢</div>
                                    <div class="font-semibold text-gray-800 text-sm">CNPJ</div>
                                    <div class="text-xs text-gray-600">Empresa</div>
                                </div>
                            </label>

                            {{-- Card CPF --}}
                            <label id="card-tipo-cpf" class="flex-1 flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-gray-50 transition-colors">
                                <input type="radio" name="tipo-documento" value="cpf" class="sr-only" id="radio-cpf">
                                <div class="text-center">
                                    <div class="text-2xl mb-2">👤</div>
                                    <div class="font-semibold text-gray-800 text-sm">CPF</div>
                                    <div class="text-xs text-gray-600">Pessoa</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Campo de entrada --}}
                    <div class="mb-6">
                        <div id="campo-cnpj">
                            <label for="input-documento" class="block text-sm font-medium text-gray-700 mb-2">CNPJ</label>
                            <input 
                                type="text" 
                                id="input-documento" 
                                placeholder="00.000.000/0000-00"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                        <div id="campo-cpf" class="hidden">
                            <label for="input-documento" class="block text-sm font-medium text-gray-700 mb-2">CPF</label>
                            <input 
                                type="text" 
                                id="input-documento-cpf" 
                                placeholder="000.000.000-00"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                    </div>

                    {{-- Seleção de pacote --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-800 mb-4">Escolha o pacote:</h3>
                        
                        <div class="flex flex-row gap-3 md:gap-4 mb-6">
                            {{-- Card Básico --}}
                            <button type="button" class="pacote-btn flex-1 bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 hover:border-gray-300 text-gray-600 transition-colors flex flex-col items-center justify-center text-center relative" data-pacote="basico">
                                <div class="text-3xl mb-2">📋</div>
                                <span class="font-semibold text-sm mb-1">Básico</span>
                                <span class="text-xs text-gray-500">CEIS + CNEP</span>
                                <span class="text-xs text-gray-500 mt-1">2 créditos</span>
                            </button>

                            {{-- Card Completo --}}
                            <button type="button" class="pacote-btn flex-1 bg-blue-50 border-2 border-blue-500 rounded-lg p-4 shadow-sm text-blue-700 transition-colors flex flex-col items-center justify-center text-center relative" data-pacote="completo">
                                <span class="absolute -top-2 -right-2 bg-white text-yellow-600 text-xs px-2 py-0.5 rounded-full font-semibold border-2 border-yellow-500 shadow-sm">Favorito</span>
                                <div class="text-3xl mb-2">📊</div>
                                <span class="font-semibold text-sm mb-1">Completo</span>
                                <span class="text-xs text-blue-600">5 listas</span>
                                <span class="text-xs text-blue-600 mt-1">5 créditos</span>
                            </button>

                            {{-- Card Trabalhista --}}
                            <button type="button" class="pacote-btn flex-1 bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 hover:border-gray-300 text-gray-600 transition-colors flex flex-col items-center justify-center text-center relative" data-pacote="trabalhista">
                                <div class="text-3xl mb-2">👷</div>
                                <span class="font-semibold text-sm mb-1">Trabalhista</span>
                                <span class="text-xs text-gray-500">Trab. Escravo</span>
                                <span class="text-xs text-gray-500 mt-1">1 crédito</span>
                            </button>
                        </div>

                        {{-- Card de detalhes do pacote --}}
                        <div class="bg-gray-50 rounded-xl p-6 border border-gray-100" id="detalhes-pacote">
                            <div class="flex items-center mb-4">
                                <span class="text-2xl mr-2">📋</span>
                                <h4 class="text-lg font-bold text-gray-800">Listas incluídas neste pacote:</h4>
                            </div>
                            <div id="lista-basico" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">CEIS - Empresas Inidôneas e Suspensas (CGU)</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">CNEP - Empresas Punidas (CGU)</span>
                                </div>
                            </div>
                            <div id="lista-completo" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 hidden">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">CEIS - Empresas Inidôneas e Suspensas (CGU)</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">CNEP - Empresas Punidas (CGU)</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">CEPIM - Entidades Sem Fins Lucrativos Impedidas (CGU)</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Trabalho Escravo - Lista Suja (MTE)</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Acordo de Leniência (CGU)</span>
                                </div>
                            </div>
                            <div id="lista-trabalhista" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 hidden">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Trabalho Escravo - Lista Suja (MTE)</span>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <span class="text-sm font-semibold text-gray-700">💳 Custo: <span id="custo-pacote">5</span> créditos</span>
                            </div>
                        </div>
                    </div>

                    {{-- Botão de Consultar --}}
                    <button type="button" id="btn-consultar-unica" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                        🔍 Consultar
                    </button>
                </div>
            </div>

            {{-- Seção: Consulta em Lote --}}
            <div id="secao-lote" class="modo-secao hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Consulta em Lote</h2>

                    {{-- Seleção do tipo de documento --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Documento:</label>
                        <div class="flex flex-row gap-4">
                            <label id="card-tipo-cnpj-lote" class="flex-1 flex items-center justify-center p-4 border-2 border-blue-600 rounded-lg cursor-pointer bg-blue-50 hover:bg-blue-100 transition-colors">
                                <input type="radio" name="tipo-documento-lote" value="cnpj" checked class="sr-only" id="radio-cnpj-lote">
                                <div class="text-center">
                                    <div class="text-2xl mb-2">🏢</div>
                                    <div class="font-semibold text-gray-800 text-sm">CNPJ</div>
                                </div>
                            </label>
                            <label id="card-tipo-cpf-lote" class="flex-1 flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-gray-50 transition-colors">
                                <input type="radio" name="tipo-documento-lote" value="cpf" class="sr-only" id="radio-cpf-lote">
                                <div class="text-center">
                                    <div class="text-2xl mb-2">👤</div>
                                    <div class="font-semibold text-gray-800 text-sm">CPF</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Seleção de pacote --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-800 mb-4">Escolha o pacote:</h3>
                        <div class="flex flex-row gap-3 md:gap-4">
                            <button type="button" class="pacote-btn-lote flex-1 bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 hover:border-gray-300 text-gray-600 transition-colors flex flex-col items-center justify-center text-center relative" data-pacote-lote="basico">
                                <div class="text-3xl mb-2">📋</div>
                                <span class="font-semibold text-sm mb-1">Básico</span>
                                <span class="text-xs text-gray-500">2 créditos</span>
                            </button>
                            <button type="button" class="pacote-btn-lote flex-1 bg-blue-50 border-2 border-blue-500 rounded-lg p-4 shadow-sm text-blue-700 transition-colors flex flex-col items-center justify-center text-center relative" data-pacote-lote="completo">
                                <span class="absolute -top-2 -right-2 bg-white text-yellow-600 text-xs px-2 py-0.5 rounded-full font-semibold border-2 border-yellow-500 shadow-sm">Favorito</span>
                                <div class="text-3xl mb-2">📊</div>
                                <span class="font-semibold text-sm mb-1">Completo</span>
                                <span class="text-xs text-blue-600">5 créditos</span>
                            </button>
                            <button type="button" class="pacote-btn-lote flex-1 bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 hover:border-gray-300 text-gray-600 transition-colors flex flex-col items-center justify-center text-center relative" data-pacote-lote="trabalhista">
                                <div class="text-3xl mb-2">👷</div>
                                <span class="font-semibold text-sm mb-1">Trabalhista</span>
                                <span class="text-xs text-gray-500">1 crédito</span>
                            </button>
                        </div>
                    </div>

                    {{-- Textarea ou upload --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Lista de <span id="tipo-doc-lote-texto">CNPJs</span> (máximo 100, um por linha):
                        </label>
                        <textarea 
                            id="textarea-lote" 
                            rows="8"
                            placeholder="12.345.678/0001-90&#10;23.456.789/0001-01&#10;34.567.890/0001-12"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                        ></textarea>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-xs text-gray-500">Ou:</span>
                            <button type="button" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">
                                📁 Importar arquivo .txt/.csv
                            </button>
                        </div>
                    </div>

                    {{-- Resumo de documentos e custo --}}
                    <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-semibold text-gray-700">📊 Documentos detectados:</span>
                                <span id="contador-documentos" class="text-sm font-bold text-gray-900 ml-2">0</span>
                            </div>
                            <div>
                                <span class="text-sm font-semibold text-gray-700">💳 Custo total:</span>
                                <span id="custo-total-lote" class="text-sm font-bold text-gray-900 ml-2">0 créditos</span>
                            </div>
                        </div>
                    </div>

                    {{-- Botão de Consultar Todos --}}
                    <button type="button" id="btn-consultar-lote" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                        🔍 Consultar Todos
                    </button>
                </div>
            </div>

            {{-- Card de Resultado (Consulta Única) --}}
            <div id="card-resultado-unica" class="hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 id="resultado-nome" class="text-lg font-bold text-gray-900 mb-1"></h3>
                            <p id="resultado-documento" class="text-sm text-gray-600"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="btn-favoritar text-2xl hover:scale-110 transition-transform" title="Favoritar">
                                ⭐
                            </button>
                            <button type="button" class="btn-monitorar text-2xl hover:scale-110 transition-transform" title="Monitorar">
                                🔔
                            </button>
                        </div>
                    </div>

                    {{-- Score de Compliance --}}
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-8 mb-6 border border-gray-200">
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-600 mb-4">SCORE DE COMPLIANCE</p>
                            <div id="score-circle" class="inline-flex items-center justify-center w-32 h-32 rounded-full text-4xl font-bold text-white mb-4 shadow-lg">
                                100
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                <div id="score-bar" class="h-3 rounded-full transition-all duration-500" style="width: 100%"></div>
                            </div>
                            <p id="score-texto" class="text-lg font-semibold text-gray-800">NENHUMA RESTRIÇÃO</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">CEIS</span>
                                <span id="status-ceis" class="text-sm font-semibold text-green-600">✅ Nada consta</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">CNEP</span>
                                <span id="status-cnep" class="text-sm font-semibold text-green-600">✅ Nada consta</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">CEPIM</span>
                                <span id="status-cepim" class="text-sm font-semibold text-green-600">✅ Nada consta</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Trabalho Escravo</span>
                                <div class="flex items-center gap-2">
                                    <span id="status-trabalho" class="text-sm font-semibold text-green-600">✅ Nada consta</span>
                                    <button id="btn-detalhes-trabalho" type="button" class="hidden text-xs text-blue-600 hover:text-blue-700 font-semibold">[Detalhes]</button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Acordo de Leniência</span>
                                <div class="flex items-center gap-2">
                                    <span id="status-leniencia" class="text-sm font-semibold text-green-600">✅ Nada consta</span>
                                    <button id="btn-detalhes-leniencia" type="button" class="hidden text-xs text-blue-600 hover:text-blue-700 font-semibold">[Detalhes]</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card de detalhes da ocorrência --}}
                    <div id="card-ocorrencia" class="hidden bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
                        <h4 id="ocorrencia-titulo" class="text-lg font-bold text-red-800 mb-4">⚠️ DETALHES DA OCORRÊNCIA</h4>
                        <div id="ocorrencia-conteudo" class="space-y-2 text-sm text-gray-800">
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <p class="text-xs text-gray-500">
                            Consultado em: <span id="data-consulta"></span>
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition-colors">
                            📄 Exportar PDF
                        </button>
                        <button type="button" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors">
                            📊 Exportar Excel
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tabela de Resultados (Consulta em Lote) --}}
            <div id="tabela-resultados-lote" class="hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">
                            RESULTADOS (<span id="total-resultados">0</span> documentos consultados)
                        </h3>
                        <div class="flex gap-2">
                            <button type="button" class="px-3 py-1.5 bg-gray-600 text-white rounded-lg text-xs font-semibold hover:bg-gray-700 transition-colors">
                                📄 PDF
                            </button>
                            <button type="button" class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-semibold hover:bg-green-700 transition-colors">
                                📊 Excel
                            </button>
                            <button type="button" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition-colors">
                                ⭐ Salvar Todos
                            </button>
                        </div>
                    </div>

                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-700">
                            Resumo: 
                            <span class="font-semibold text-green-600">🟢 <span id="resumo-limpos">0</span> limpos</span> | 
                            <span class="font-semibold text-yellow-600">🟡 <span id="resumo-atencao">0</span> atenção</span> | 
                            <span class="font-semibold text-red-600">🔴 <span id="resumo-restricao">0</span> com restrição</span>
                        </p>
                    </div>

                    <div class="mb-4 flex items-center justify-between">
                        <input 
                            type="text" 
                            placeholder="Buscar..." 
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="todos">Todos</option>
                            <option value="limpos">Nada consta (🟢)</option>
                            <option value="atencao">Atenção (🟡)</option>
                            <option value="restricao">Com restrição (🔴)</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Score</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Documento</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Nome</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-resultados-lote" class="divide-y divide-gray-200">
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-gray-600">Mostrando <span id="pagina-inicio">1</span>-<span id="pagina-fim">10</span> de <span id="pagina-total">0</span></p>
                        <div class="flex gap-2">
                            <button type="button" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">‹</button>
                            <button type="button" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">1</button>
                            <button type="button" class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">›</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cards de Favoritos e Histórico --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Card de Favoritos --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">⭐ FAVORITOS</h3>
                        <button type="button" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">[Ver todos]</button>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🟢</span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">ACME Comércio</p>
                                    <p class="text-xs text-gray-500">12.345.678/0001-90</p>
                                </div>
                            </div>
                            <button type="button" class="text-blue-600 hover:text-blue-700 text-lg">🔍</button>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🟢</span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">XYZ Indústria</p>
                                    <p class="text-xs text-gray-500">23.456.789/0001-01</p>
                                </div>
                            </div>
                            <button type="button" class="text-blue-600 hover:text-blue-700 text-lg">🔍</button>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🔴</span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Empresa ABC</p>
                                    <p class="text-xs text-gray-500">34.567.890/0001-12</p>
                                </div>
                            </div>
                            <button type="button" class="text-blue-600 hover:text-blue-700 text-lg">🔍</button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">Clique em 🔍 para reconsultar</p>
                </div>

                {{-- Card de Histórico --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">📋 CONSULTAS RECENTES</h3>
                        <button type="button" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">[Ver todas]</button>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🟢</span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">12.345.678/0001-90</p>
                                    <p class="text-xs text-gray-500">CNPJ | Completo | há 5 min</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🔴</span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">99.888.777/0001-66</p>
                                    <p class="text-xs text-gray-500">CNPJ | Completo | há 1 hora</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🟢</span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">123.456.789-00</p>
                                    <p class="text-xs text-gray-500">CPF | Básico | ontem</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🟡</span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">45.678.901/0001-23</p>
                                    <p class="text-xs text-gray-500">CNPJ | Completo | 2 dias</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🟢</span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">56.789.012/0001-34</p>
                                    <p class="text-xs text-gray-500">CNPJ | Trabalhista | 3 dias</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Monitoramento --}}
<div id="modal-monitoramento" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">🔔 MONITORAR LISTAS RESTRITIVAS</h3>
            <button type="button" id="btn-fechar-modal" class="text-gray-400 hover:text-gray-600 text-2xl">✕</button>
        </div>
        
        <div class="p-6 space-y-6">
            <div>
                <h4 id="modal-nome" class="text-lg font-semibold text-gray-800 mb-1"></h4>
                <p id="modal-documento" class="text-sm text-gray-600"></p>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <label class="block text-sm font-semibold text-gray-700 mb-4">Frequência de verificação:</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex flex-col items-center p-4 border-2 border-blue-600 rounded-lg cursor-pointer bg-blue-50 hover:bg-blue-100 transition-colors">
                        <input type="radio" name="frequencia" value="semanal" checked class="sr-only">
                        <span class="text-2xl mb-2">📅</span>
                        <span class="font-semibold text-sm text-gray-800 mb-1">Semanal</span>
                        <span class="text-xs text-gray-600">16 créditos/mês</span>
                        <span class="text-xs text-green-600 font-semibold mt-1">✓ 20% desconto</span>
                    </label>
                    <label class="flex flex-col items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-gray-50 transition-colors">
                        <input type="radio" name="frequencia" value="diario" class="sr-only">
                        <span class="text-2xl mb-2">📆</span>
                        <span class="font-semibold text-sm text-gray-800 mb-1">Diário</span>
                        <span class="text-xs text-gray-600">120 créditos/mês</span>
                        <span class="text-xs text-green-600 font-semibold mt-1">✓ 20% desconto</span>
                    </label>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Notificar quando houver mudança:</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" checked class="mr-2 w-4 h-4 text-blue-600">
                        <span class="text-sm text-gray-700">E-mail</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" disabled class="mr-2 w-4 h-4 text-gray-400">
                        <span class="text-sm text-gray-500">WhatsApp (em breve)</span>
                    </label>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <p class="text-sm font-semibold text-gray-700">📊 Resumo:</p>
                    <p class="text-sm text-gray-600">• Custo mensal estimado: <span id="modal-custo-mensal">16</span> créditos</p>
                    <p class="text-sm text-gray-600">• Seu saldo atual: 147 créditos</p>
                    <p class="text-sm text-gray-600">• Duração estimada: <span id="modal-duracao">~9</span> meses</p>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <p class="text-sm text-gray-600 mb-2">
                    O sistema verificará automaticamente todas as 5 listas e te alertará se a empresa entrar em qualquer lista restritiva.
                </p>
                <p class="text-sm text-yellow-600 font-semibold">
                    ⚠️ Se seu saldo zerar, o monitoramento será pausado automaticamente e voltará quando você recarregar.
                </p>
            </div>

            <div class="border-t border-gray-200 pt-4 flex justify-end gap-3">
                <button type="button" id="btn-cancelar-modal" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="button" id="btn-ativar-monitoramento" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors">
                    🔔 Ativar Monitoramento
                </button>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
(function() {
    'use strict';

    // Variáveis globais
    let modoAtual = 'unica';
    let tipoDocumento = 'cnpj';
    let pacoteSelecionado = 'completo';
    let pacoteLoteSelecionado = 'completo';
    let tipoDocumentoLote = 'cnpj';

    // Dados mockados
    const resultadosMockados = {
        '12.345.678/0001-90': {
            nome: 'ACME COMÉRCIO LTDA',
            documento: '12.345.678/0001-90',
            score: 100,
            status: {
                ceis: { consta: false },
                cnep: { consta: false },
                cepim: { consta: false },
                trabalho: { consta: false },
                leniencia: { consta: false }
            }
        },
        '99.888.777/0001-66': {
            nome: 'EMPRESA PROBLEMÁTICA LTDA',
            documento: '99.888.777/0001-66',
            score: 20,
            status: {
                ceis: { consta: false },
                cnep: { consta: false },
                cepim: { consta: false },
                trabalho: { 
                    consta: true,
                    detalhes: {
                        empregador: 'EMPRESA PROBLEMÁTICA LTDA',
                        cnpj: '99.888.777/0001-66',
                        uf: 'PA',
                        anoFiscalizacao: '2023',
                        trabalhadores: '12',
                        dataInclusao: '15/03/2023'
                    }
                },
                leniencia: { consta: false }
            }
        },
        '45.678.901/0001-23': {
            nome: 'CONSTRUTORA XYZ S/A',
            documento: '45.678.901/0001-23',
            score: 80,
            status: {
                ceis: { consta: false },
                cnep: { consta: false },
                cepim: { consta: false },
                trabalho: { consta: false },
                leniencia: { 
                    consta: true,
                    detalhes: {
                        dataAcordo: '10/06/2022',
                        orgao: 'CGU',
                        situacao: 'Em cumprimento'
                    }
                }
            }
        }
    };

    // Função para aplicar máscara de CNPJ
    function maskCNPJ(value) {
        return value
            .replace(/\D/g, '')
            .replace(/^(\d{2})(\d)/, '$1.$2')
            .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1/$2')
            .replace(/(\d{4})(\d)/, '$1-$2')
            .substring(0, 18);
    }

    // Função para aplicar máscara de CPF
    function maskCPF(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
            .substring(0, 14);
    }

    // Função para alternar modo
    function switchModo(modo) {
        modoAtual = modo;
        
        // Atualizar tabs
        document.querySelectorAll('.modo-tab').forEach(tab => {
            tab.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
            tab.classList.add('text-gray-600');
            tab.setAttribute('aria-selected', 'false');
        });
        
        const activeTab = document.querySelector(`[data-modo="${modo}"]`);
        if (activeTab) {
            activeTab.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
            activeTab.classList.remove('text-gray-600');
            activeTab.setAttribute('aria-selected', 'true');
        }
        
        // Mostrar/ocultar seções
        document.querySelectorAll('.modo-secao').forEach(sec => {
            sec.classList.add('hidden');
        });
        
        if (modo === 'unica') {
            document.getElementById('secao-unica').classList.remove('hidden');
        } else {
            document.getElementById('secao-lote').classList.remove('hidden');
        }
    }

    // Função para alternar tipo de documento (consulta única)
    function toggleTipoDocumento(tipo) {
        tipoDocumento = tipo;
        const cardCNPJ = document.getElementById('card-tipo-cnpj');
        const cardCPF = document.getElementById('card-tipo-cpf');
        const campoCNPJ = document.getElementById('campo-cnpj');
        const campoCPF = document.getElementById('campo-cpf');
        const inputDoc = document.getElementById('input-documento');
        const inputDocCPF = document.getElementById('input-documento-cpf');

        if (tipo === 'cnpj') {
            cardCNPJ.classList.remove('border-gray-300', 'hover:bg-gray-50');
            cardCNPJ.classList.add('border-blue-600', 'bg-blue-50');
            cardCPF.classList.remove('border-blue-600', 'bg-blue-50');
            cardCPF.classList.add('border-gray-300', 'hover:bg-gray-50');
            campoCNPJ.classList.remove('hidden');
            campoCPF.classList.add('hidden');
            if (inputDocCPF) inputDocCPF.value = '';
        } else {
            cardCPF.classList.remove('border-gray-300', 'hover:bg-gray-50');
            cardCPF.classList.add('border-blue-600', 'bg-blue-50');
            cardCNPJ.classList.remove('border-blue-600', 'bg-blue-50');
            cardCNPJ.classList.add('border-gray-300', 'hover:bg-gray-50');
            campoCPF.classList.remove('hidden');
            campoCNPJ.classList.add('hidden');
            if (inputDoc) inputDoc.value = '';
        }
        
        validarBotaoConsultar();
    }

    // Função para alternar tipo de documento (lote)
    function toggleTipoDocumentoLote(tipo) {
        tipoDocumentoLote = tipo;
        const cardCNPJ = document.getElementById('card-tipo-cnpj-lote');
        const cardCPF = document.getElementById('card-tipo-cpf-lote');
        const textoLote = document.getElementById('tipo-doc-lote-texto');
        const textarea = document.getElementById('textarea-lote');

        if (tipo === 'cnpj') {
            cardCNPJ.classList.remove('border-gray-300', 'hover:bg-gray-50');
            cardCNPJ.classList.add('border-blue-600', 'bg-blue-50');
            cardCPF.classList.remove('border-blue-600', 'bg-blue-50');
            cardCPF.classList.add('border-gray-300', 'hover:bg-gray-50');
            if (textoLote) textoLote.textContent = 'CNPJs';
            if (textarea) textarea.placeholder = '12.345.678/0001-90\n23.456.789/0001-01\n34.567.890/0001-12';
        } else {
            cardCPF.classList.remove('border-gray-300', 'hover:bg-gray-50');
            cardCPF.classList.add('border-blue-600', 'bg-blue-50');
            cardCNPJ.classList.remove('border-blue-600', 'bg-blue-50');
            cardCNPJ.classList.add('border-gray-300', 'hover:bg-gray-50');
            if (textoLote) textoLote.textContent = 'CPFs';
            if (textarea) textarea.placeholder = '123.456.789-00\n234.567.890-11\n345.678.901-22';
        }
        
        atualizarContadorLote();
    }

    // Função para selecionar pacote (consulta única)
    function selecionarPacote(pacote) {
        pacoteSelecionado = pacote;
        
        document.querySelectorAll('.pacote-btn').forEach(btn => {
            btn.classList.remove('bg-blue-50', 'border-2', 'border-blue-500', 'shadow-sm', 'text-blue-700');
            btn.classList.add('bg-white', 'border', 'border-gray-200', 'text-gray-600');
        });
        
        const activeBtn = document.querySelector(`[data-pacote="${pacote}"]`);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'border', 'border-gray-200', 'text-gray-600');
            activeBtn.classList.add('bg-blue-50', 'border-2', 'border-blue-500', 'shadow-sm', 'text-blue-700');
        }
        
        // Atualizar detalhes do pacote
        document.querySelectorAll('[id^="lista-"]').forEach(lista => {
            lista.classList.add('hidden');
        });
        
        const listaPacote = document.getElementById(`lista-${pacote}`);
        if (listaPacote) {
            listaPacote.classList.remove('hidden');
        }
        
        // Atualizar custo
        const custos = { basico: 2, completo: 5, trabalhista: 1 };
        const custoElement = document.getElementById('custo-pacote');
        if (custoElement) {
            custoElement.textContent = custos[pacote];
        }
    }

    // Função para selecionar pacote (lote)
    function selecionarPacoteLote(pacote) {
        pacoteLoteSelecionado = pacote;
        
        document.querySelectorAll('.pacote-btn-lote').forEach(btn => {
            btn.classList.remove('bg-blue-50', 'border-2', 'border-blue-500', 'shadow-sm', 'text-blue-700');
            btn.classList.add('bg-white', 'border', 'border-gray-200', 'text-gray-600');
        });
        
        const activeBtn = document.querySelector(`[data-pacote-lote="${pacote}"]`);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'border', 'border-gray-200', 'text-gray-600');
            activeBtn.classList.add('bg-blue-50', 'border-2', 'border-blue-500', 'shadow-sm', 'text-blue-700');
        }
        
        atualizarContadorLote();
    }

    // Função para atualizar contador de lote
    function atualizarContadorLote() {
        const textarea = document.getElementById('textarea-lote');
        if (!textarea) return;
        
        const texto = textarea.value.trim();
        const linhas = texto.split('\n').filter(linha => linha.trim().length > 0);
        const documentos = linhas.length;
        
        const contador = document.getElementById('contador-documentos');
        const custoTotal = document.getElementById('custo-total-lote');
        const btnConsultar = document.getElementById('btn-consultar-lote');
        
        if (contador) {
            contador.textContent = documentos;
        }
        
        if (custoTotal) {
            const custos = { basico: 2, completo: 5, trabalhista: 1 };
            const custo = documentos * custos[pacoteLoteSelecionado];
            custoTotal.textContent = `${custo} créditos (${documentos} × ${custos[pacoteLoteSelecionado]})`;
        }
        
        if (btnConsultar) {
            btnConsultar.disabled = documentos === 0 || documentos > 100;
        }
    }

    // Função para validar botão consultar
    function validarBotaoConsultar() {
        const inputDoc = document.getElementById('input-documento');
        const inputDocCPF = document.getElementById('input-documento-cpf');
        const btnConsultar = document.getElementById('btn-consultar-unica');
        
        if (!btnConsultar) return;
        
        const valor = tipoDocumento === 'cnpj' 
            ? (inputDoc ? inputDoc.value.replace(/\D/g, '') : '')
            : (inputDocCPF ? inputDocCPF.value.replace(/\D/g, '') : '');
        
        btnConsultar.disabled = valor.length < (tipoDocumento === 'cnpj' ? 14 : 11);
    }

    // Função para obter cor do score
    function getScoreColor(score) {
        if (score === 100) return { bg: 'bg-green-500', bar: 'bg-green-500', texto: 'NENHUMA RESTRIÇÃO' };
        if (score >= 80) return { bg: 'bg-yellow-500', bar: 'bg-yellow-500', texto: 'ATENÇÃO - ACORDO DE LENIÊNCIA' };
        if (score >= 50) return { bg: 'bg-orange-500', bar: 'bg-orange-500', texto: 'RESTRIÇÃO ENCONTRADA' };
        if (score >= 20) return { bg: 'bg-red-500', bar: 'bg-red-500', texto: 'RISCO ALTO' };
        return { bg: 'bg-red-700', bar: 'bg-red-700', texto: 'RISCO CRÍTICO' };
    }

    // Função para exibir resultado
    function exibirResultado(documento) {
        const resultado = resultadosMockados[documento];
        if (!resultado) {
            // Resultado padrão limpo
            exibirResultadoLimpo(documento);
            return;
        }
        
        const cardResultado = document.getElementById('card-resultado-unica');
        if (!cardResultado) return;
        
        cardResultado.classList.remove('hidden');
        cardResultado.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Preencher dados básicos
        document.getElementById('resultado-nome').textContent = resultado.nome;
        document.getElementById('resultado-documento').textContent = `${tipoDocumento.toUpperCase()}: ${resultado.documento}`;
        
        // Score
        const scoreColor = getScoreColor(resultado.score);
        const scoreCircle = document.getElementById('score-circle');
        const scoreBar = document.getElementById('score-bar');
        const scoreTexto = document.getElementById('score-texto');
        
        if (scoreCircle) {
            scoreCircle.textContent = resultado.score;
            scoreCircle.className = `inline-flex items-center justify-center w-32 h-32 rounded-full text-4xl font-bold text-white mb-4 shadow-lg ${scoreColor.bg}`;
        }
        
        if (scoreBar) {
            scoreBar.style.width = `${resultado.score}%`;
            scoreBar.className = `h-3 rounded-full transition-all duration-500 ${scoreColor.bar}`;
        }
        
        if (scoreTexto) {
            scoreTexto.textContent = scoreColor.texto;
        }
        
        // Status das listas
        const statusMap = {
            ceis: { id: 'status-ceis', nome: 'CEIS' },
            cnep: { id: 'status-cnep', nome: 'CNEP' },
            cepim: { id: 'status-cepim', nome: 'CEPIM' },
            trabalho: { id: 'status-trabalho', nome: 'Trabalho Escravo', btn: 'btn-detalhes-trabalho' },
            leniencia: { id: 'status-leniencia', nome: 'Acordo de Leniência', btn: 'btn-detalhes-leniencia' }
        };
        
        Object.keys(statusMap).forEach(key => {
            const status = resultado.status[key];
            const elemento = document.getElementById(statusMap[key].id);
            if (elemento) {
                if (status.consta) {
                    elemento.textContent = '❌ CONSTA ⚠️';
                    elemento.className = 'text-sm font-semibold text-red-600';
                    
                    // Mostrar botão de detalhes
                    const btnDetalhes = document.getElementById(statusMap[key].btn);
                    if (btnDetalhes) {
                        btnDetalhes.classList.remove('hidden');
                        btnDetalhes.onclick = () => mostrarDetalhesOcorrencia(key, status.detalhes);
                    }
                } else {
                    elemento.textContent = '✅ Nada consta';
                    elemento.className = 'text-sm font-semibold text-green-600';
                    
                    const btnDetalhes = document.getElementById(statusMap[key].btn);
                    if (btnDetalhes) {
                        btnDetalhes.classList.add('hidden');
                    }
                }
            }
        });
        
        // Ocultar card de ocorrência inicialmente
        const cardOcorrencia = document.getElementById('card-ocorrencia');
        if (cardOcorrencia) {
            cardOcorrencia.classList.add('hidden');
        }
        
        // Data da consulta
        const dataConsulta = document.getElementById('data-consulta');
        if (dataConsulta) {
            const agora = new Date();
            dataConsulta.textContent = agora.toLocaleDateString('pt-BR') + ' às ' + agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        }
    }

    // Função para exibir resultado limpo
    function exibirResultadoLimpo(documento) {
        const cardResultado = document.getElementById('card-resultado-unica');
        if (!cardResultado) return;
        
        cardResultado.classList.remove('hidden');
        cardResultado.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Dados padrão
        const nome = tipoDocumento === 'cnpj' ? 'EMPRESA CONSULTADA LTDA' : 'PESSOA CONSULTADA';
        document.getElementById('resultado-nome').textContent = nome;
        document.getElementById('resultado-documento').textContent = `${tipoDocumento.toUpperCase()}: ${documento}`;
        
        // Score 100
        const scoreCircle = document.getElementById('score-circle');
        const scoreBar = document.getElementById('score-bar');
        const scoreTexto = document.getElementById('score-texto');
        
        if (scoreCircle) {
            scoreCircle.textContent = '100';
            scoreCircle.className = 'inline-flex items-center justify-center w-32 h-32 rounded-full text-4xl font-bold text-white mb-4 shadow-lg bg-green-500';
        }
        
        if (scoreBar) {
            scoreBar.style.width = '100%';
            scoreBar.className = 'h-3 rounded-full transition-all duration-500 bg-green-500';
        }
        
        if (scoreTexto) {
            scoreTexto.textContent = 'NENHUMA RESTRIÇÃO';
        }
        
        // Todas as listas limpas
        ['status-ceis', 'status-cnep', 'status-cepim', 'status-trabalho', 'status-leniencia'].forEach(id => {
            const elemento = document.getElementById(id);
            if (elemento) {
                elemento.textContent = '✅ Nada consta';
                elemento.className = 'text-sm font-semibold text-green-600';
            }
        });
        
        // Ocultar botões de detalhes
        document.getElementById('btn-detalhes-trabalho')?.classList.add('hidden');
        document.getElementById('btn-detalhes-leniencia')?.classList.add('hidden');
        
        // Ocultar card de ocorrência
        document.getElementById('card-ocorrencia')?.classList.add('hidden');
        
        // Data da consulta
        const dataConsulta = document.getElementById('data-consulta');
        if (dataConsulta) {
            const agora = new Date();
            dataConsulta.textContent = agora.toLocaleDateString('pt-BR') + ' às ' + agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        }
    }

    // Função para mostrar detalhes da ocorrência
    function mostrarDetalhesOcorrencia(tipo, detalhes) {
        const cardOcorrencia = document.getElementById('card-ocorrencia');
        const titulo = document.getElementById('ocorrencia-titulo');
        const conteudo = document.getElementById('ocorrencia-conteudo');
        
        if (!cardOcorrencia || !titulo || !conteudo) return;
        
        const titulos = {
            trabalho: '⚠️ DETALHES DA OCORRÊNCIA - TRABALHO ESCRAVO',
            leniencia: '⚠️ DETALHES DA OCORRÊNCIA - ACORDO DE LENIÊNCIA'
        };
        
        titulo.textContent = titulos[tipo] || '⚠️ DETALHES DA OCORRÊNCIA';
        
        let html = '';
        if (tipo === 'trabalho') {
            html = `
                <p><strong>Empregador:</strong> ${detalhes.empregador}</p>
                <p><strong>CNPJ:</strong> ${detalhes.cnpj}</p>
                <p><strong>UF:</strong> ${detalhes.uf}</p>
                <p><strong>Ano fiscalização:</strong> ${detalhes.anoFiscalizacao}</p>
                <p><strong>Trabalhadores envolvidos:</strong> ${detalhes.trabalhadores}</p>
                <p><strong>Data inclusão na lista:</strong> ${detalhes.dataInclusao}</p>
            `;
        } else if (tipo === 'leniencia') {
            html = `
                <p><strong>Data acordo:</strong> ${detalhes.dataAcordo}</p>
                <p><strong>Órgão:</strong> ${detalhes.orgao}</p>
                <p><strong>Situação:</strong> ${detalhes.situacao}</p>
            `;
        }
        
        conteudo.innerHTML = html;
        cardOcorrencia.classList.remove('hidden');
    }

    // Função para abrir modal de monitoramento
    function abrirModalMonitoramento() {
        const modal = document.getElementById('modal-monitoramento');
        const nome = document.getElementById('resultado-nome')?.textContent || '';
        const documento = document.getElementById('resultado-documento')?.textContent || '';
        
        if (modal) {
            document.getElementById('modal-nome').textContent = nome;
            document.getElementById('modal-documento').textContent = documento;
            modal.classList.remove('hidden');
        }
    }

    // Função para fechar modal
    function fecharModal() {
        const modal = document.getElementById('modal-monitoramento');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Função para atualizar custo do modal
    function atualizarCustoModal() {
        const frequencia = document.querySelector('input[name="frequencia"]:checked')?.value || 'semanal';
        const custos = {
            semanal: { mensal: 16, duracao: '~9' },
            diario: { mensal: 120, duracao: '~1' }
        };
        
        const custo = custos[frequencia];
        document.getElementById('modal-custo-mensal').textContent = custo.mensal;
        document.getElementById('modal-duracao').textContent = custo.duracao;
    }

    // Inicialização
    function init() {
        // Event listeners para tabs de modo
        document.querySelectorAll('.modo-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const modo = this.getAttribute('data-modo');
                switchModo(modo);
            });
        });

        // Event listeners para tipo de documento (consulta única)
        document.getElementById('radio-cnpj')?.addEventListener('change', function() {
            if (this.checked) toggleTipoDocumento('cnpj');
        });
        document.getElementById('radio-cpf')?.addEventListener('change', function() {
            if (this.checked) toggleTipoDocumento('cpf');
        });

        // Event listeners para tipo de documento (lote)
        document.getElementById('radio-cnpj-lote')?.addEventListener('change', function() {
            if (this.checked) toggleTipoDocumentoLote('cnpj');
        });
        document.getElementById('radio-cpf-lote')?.addEventListener('change', function() {
            if (this.checked) toggleTipoDocumentoLote('cpf');
        });

        // Event listeners para pacotes (consulta única)
        document.querySelectorAll('.pacote-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const pacote = this.getAttribute('data-pacote');
                selecionarPacote(pacote);
            });
        });

        // Event listeners para pacotes (lote)
        document.querySelectorAll('.pacote-btn-lote').forEach(btn => {
            btn.addEventListener('click', function() {
                const pacote = this.getAttribute('data-pacote-lote');
                selecionarPacoteLote(pacote);
            });
        });

        // Máscaras nos inputs
        const inputDoc = document.getElementById('input-documento');
        const inputDocCPF = document.getElementById('input-documento-cpf');
        
        if (inputDoc) {
            inputDoc.addEventListener('input', function() {
                this.value = maskCNPJ(this.value);
                validarBotaoConsultar();
            });
        }
        
        if (inputDocCPF) {
            inputDocCPF.addEventListener('input', function() {
                this.value = maskCPF(this.value);
                validarBotaoConsultar();
            });
        }

        // Textarea do lote
        const textareaLote = document.getElementById('textarea-lote');
        if (textareaLote) {
            textareaLote.addEventListener('input', function() {
                atualizarContadorLote();
            });
        }

        // Botão consultar única
        document.getElementById('btn-consultar-unica')?.addEventListener('click', function() {
            const inputDoc = tipoDocumento === 'cnpj' 
                ? document.getElementById('input-documento')
                : document.getElementById('input-documento-cpf');
            
            if (inputDoc) {
                const documento = inputDoc.value;
                exibirResultado(documento);
            }
        });

        // Botão consultar lote
        document.getElementById('btn-consultar-lote')?.addEventListener('click', function() {
            // Implementar lógica de consulta em lote
            const textarea = document.getElementById('textarea-lote');
            if (textarea) {
                const linhas = textarea.value.trim().split('\n').filter(l => l.trim());
                console.log('Consultando lote:', linhas);
                // Aqui seria feita a consulta real
            }
        });

        // Botão monitorar
        document.querySelector('.btn-monitorar')?.addEventListener('click', abrirModalMonitoramento);

        // Modal
        document.getElementById('btn-fechar-modal')?.addEventListener('click', fecharModal);
        document.getElementById('btn-cancelar-modal')?.addEventListener('click', fecharModal);
        document.querySelectorAll('input[name="frequencia"]').forEach(radio => {
            radio.addEventListener('change', atualizarCustoModal);
        });

        // Fechar modal ao clicar fora
        document.getElementById('modal-monitoramento')?.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModal();
            }
        });

        // Inicializar com pacote Completo selecionado
        selecionarPacote('completo');
        selecionarPacoteLote('completo');
    }

    // Aguardar DOM estar pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-inicializar se a página for carregada via SPA
    if (typeof window !== 'undefined') {
        window.addEventListener('load', function() {
            setTimeout(init, 100);
        });
    }
})();
</script>

