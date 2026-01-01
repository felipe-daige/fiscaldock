{{-- Análise de Risco de Fornecedores --}}
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Análise de Risco de Fornecedores</h1>
            <p class="mt-1 text-sm text-gray-600">Importe um arquivo SPED para analisar os participantes em diversos órgãos públicos</p>
        </div>

        {{-- Navegação por Pills --}}
        <div class="flex justify-center mb-6">
            <div class="inline-flex items-center gap-1 p-1 rounded-full bg-gray-100 shadow-sm">
                <button 
                    type="button"
                    class="analise-tab px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 bg-white text-gray-900 shadow-sm"
                    data-tab="analise"
                    aria-selected="true"
                >
                    Análise de Risco
                </button>
                <button 
                    type="button"
                    class="analise-tab px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 hover:bg-gray-50"
                    data-tab="como-funciona"
                    aria-selected="false"
                >
                    Como Funciona
                </button>
            </div>
        </div>

        {{-- Aba: Análise de Risco --}}
        <div id="tab-analise" class="analise-tab-content">

        {{-- Seção 1: Upload + Resumo lado a lado --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Card Upload --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-md p-6">
                <h2 class="text-sm font-semibold text-gray-800 mb-5">Upload do Arquivo SPED</h2>
                
                <form id="analise-risco-form" class="space-y-4">
                    {{-- Tipo de SPED --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-700">Tipo de SPED</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center p-3 border-2 border-blue-600 rounded-lg cursor-pointer bg-blue-50 hover:bg-blue-100 transition" id="tipo-efd-contrib-label">
                                <input 
                                    type="radio" 
                                    name="tipo_sped" 
                                    value="EFD Contribuições" 
                                    id="tipo-efd-contrib"
                                    class="mr-2 w-4 h-4 text-blue-600 focus:ring-blue-500"
                                    checked
                                >
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">EFD Contribuições</div>
                                    <div class="text-xs text-gray-600">PIS/COFINS</div>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-gray-50 transition" id="tipo-efd-fiscal-label">
                                <input 
                                    type="radio" 
                                    name="tipo_sped" 
                                    value="EFD Fiscal" 
                                    id="tipo-efd-fiscal"
                                    class="mr-2 w-4 h-4 text-blue-600 focus:ring-blue-500"
                                >
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">EFD Fiscal</div>
                                    <div class="text-xs text-gray-600">ICMS/IPI</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Upload de Arquivo --}}
                    <div class="space-y-2">
                        <label for="arquivo_sped" class="block text-xs font-semibold text-gray-700">Arquivo SPED (.txt)</label>

                        <input
                            type="file"
                            id="arquivo_sped"
                            name="arquivo_sped"
                            accept=".txt"
                            class="sr-only"
                        >

                        <div
                            id="arquivo-dropzone"
                            class="w-full rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center transition hover:border-blue-400 hover:bg-blue-50 cursor-pointer"
                            role="button"
                            tabindex="0"
                        >
                            <div class="flex flex-col items-center gap-2">
                                <svg class="h-8 w-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <div class="space-y-1">
                                    <p class="text-sm font-semibold text-gray-700" id="dropzone-title">Arraste arquivo SPED aqui</p>
                                    <p class="text-xs text-gray-500" id="dropzone-subtitle">ou clique para selecionar</p>
                                    <p class="text-xs text-gray-400">.txt | Máx: 50MB</p>
                                </div>
                            </div>
                        </div>

                        <div id="arquivo-file-meta" class="hidden rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1 overflow-hidden">
                                    <p class="text-sm font-semibold text-gray-900 truncate" id="arquivo-file-name"></p>
                                    <p class="text-xs text-gray-500" id="arquivo-file-size"></p>
                                </div>
                                <button 
                                    type="button" 
                                    id="arquivo-change-file" 
                                    class="inline-flex items-center justify-center rounded border border-gray-300 bg-white text-gray-700 transition hover:bg-gray-50 px-2 py-1 text-xs flex-shrink-0"
                                >
                                    Trocar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Vincular a Cliente (opcional) --}}
                    <div class="space-y-2">
                        <label for="cliente_id" class="block text-xs font-semibold text-gray-700">Vincular a cliente (opcional)</label>
                        <select 
                            id="cliente_id" 
                            name="cliente_id" 
                            class="w-full rounded-lg border border-gray-300 bg-white text-gray-800 text-sm shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Selecione um cliente...</option>
                        </select>
                    </div>
                </form>
            </div>

            {{-- Card Resumo --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-md p-6">
                <h2 class="text-sm font-semibold text-gray-800 mb-5">Resumo</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Consultas selecionadas:</span>
                        <span class="text-lg font-bold text-gray-900" id="total-consultas-selecionadas">14</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Custo por fornecedor:</span>
                        <span class="text-lg font-bold text-blue-600" id="custo-por-fornecedor">R$ 7,70</span>
                    </div>
                    <div class="py-2">
                        <p class="text-xs text-gray-500 mb-4">O custo total será calculado após identificar os fornecedores no SPED</p>
                        
                        <button
                            type="submit"
                            form="analise-risco-form"
                            id="iniciar-analise-btn"
                            class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-sm hover:bg-blue-700 transition disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:bg-blue-600"
                            disabled
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Iniciar Análise de Risco</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Seção 2: Órgãos Consultados (selecionável) --}}
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Consultas Disponíveis</h2>
                    <p class="mt-1 text-sm text-gray-600">Selecione as consultas que deseja realizar. Custo: <strong class="text-blue-600">R$ 0,55</strong> por consulta.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" id="selecionar-todas-btn" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Selecionar todas</button>
                    <span class="text-gray-300">|</span>
                    <button type="button" id="desmarcar-todas-btn" class="text-sm text-gray-600 hover:text-gray-800 font-medium">Desmarcar todas</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Card 1: Receita Federal + SEFAZ --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    {{-- Receita Federal --}}
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                            <span class="text-lg">🏛️</span>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Receita Federal</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 w-10"></th>
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700">Consulta</th>
                                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-700 w-20">Preço</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="situacao_cnpj">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="situacao_cnpj" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2.5 px-4 whitespace-nowrap">
                                            <span class="text-gray-800">Situação Cadastral CNPJ</span>
                                        </td>
                                        <td class="py-2.5 px-4 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="qsa">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="qsa" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="text-gray-800">Quadro Societário (QSA)</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="simples_nacional">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="simples_nacional" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="text-gray-800">Optante Simples Nacional</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="cnd_federal">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="cnd_federal" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="text-gray-800">CND Federal (PGFN)</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- SEFAZ --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                            <span class="text-lg">🏢</span>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">SEFAZ / SINTEGRA</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 w-10"></th>
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700">Consulta</th>
                                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-700 w-20">Preço</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="inscricao_estadual">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="inscricao_estadual" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="text-gray-800">Inscrição Estadual</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="cnd_estadual">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="cnd_estadual" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="text-gray-800">CND Estadual (ICMS)</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Card 2: CGU + Prefeitura --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    {{-- CGU --}}
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                            <span class="text-lg">🔍</span>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">CGU</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 w-10"></th>
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700">Consulta</th>
                                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-700 w-20">Preço</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="ceis">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="ceis" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="text-gray-800">CEIS - Empresas Inidôneas</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="cnep">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="cnep" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="text-gray-800">CNEP - Empresas Punidas</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="cepim">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="cepim" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="text-gray-800">CEPIM - Entidades Impedidas</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Prefeitura --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                            <span class="text-lg">🏛️</span>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Prefeitura</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 w-10"></th>
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700">Consulta</th>
                                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-700 w-20">Preço</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="cnd_municipal">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="cnd_municipal" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="text-gray-800">CND Municipal (ISS, IPTU)</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Trabalhista + Protestos --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    {{-- Caixa / FGTS --}}
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                            <span class="text-lg">🏦</span>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Caixa / FGTS</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 w-10"></th>
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700">Consulta</th>
                                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-700 w-20">Preço</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="crf_fgts">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="crf_fgts" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="text-gray-800">CRF - Regularidade FGTS</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Trabalhista --}}
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                            <span class="text-lg">⚖️</span>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Trabalhista</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 w-10"></th>
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700">Consulta</th>
                                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-700 w-20">Preço</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="cndt">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="cndt" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="text-gray-800">CNDT - Débitos Trabalhistas</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="trabalho_escravo">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="trabalho_escravo" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="text-gray-800">Lista de Trabalho Escravo</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Cartórios --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                            <span class="text-lg">📋</span>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Cartórios</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700 w-10"></th>
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-700">Consulta</th>
                                        <th class="text-right py-2 px-3 text-xs font-semibold text-gray-700 w-20">Preço</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors consulta-item" data-consulta-id="protestos">
                                        <td class="py-2 px-3">
                                            <input type="checkbox" name="consultas[]" value="protestos" class="consulta-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="text-gray-800">Protestos em Cartórios</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-gray-700 font-medium">R$ 0,55</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div>
        {{-- Fim Aba: Análise de Risco --}}

        {{-- Aba: Como Funciona --}}
        <div id="tab-como-funciona" class="analise-tab-content hidden">
            <div class="max-w-4xl mx-auto space-y-8">

                {{-- Cabeçalho --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">Análise de Risco de Fornecedores</h2>
                                <p class="text-sm text-gray-600">Due Diligence automatizada via SPED</p>
                            </div>
                        </div>
                        <p class="text-gray-600">
                            A Análise de Risco de Fornecedores é uma ferramenta que cruza os dados do seu arquivo SPED com diversas bases de dados públicas para identificar riscos fiscais, trabalhistas e reputacionais dos seus fornecedores. Cada fornecedor recebe um score de 0 a 100 baseado nas consultas realizadas.
                        </p>
                    </div>
                </div>

                {{-- Como Funciona - Etapas --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Como Funciona</h3>
                    
                    <div class="space-y-6">
                        {{-- Etapa 1 --}}
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">1</div>
                            <div class="pt-1">
                                <h4 class="font-semibold text-gray-900">Upload do Arquivo SPED</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    Você faz o upload do arquivo SPED (EFD Contribuições ou EFD Fiscal) no formato .txt. O sistema aceita arquivos de até 50MB.
                                </p>
                            </div>
                        </div>

                        {{-- Etapa 2 --}}
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">2</div>
                            <div class="pt-1">
                                <h4 class="font-semibold text-gray-900">Extração dos Participantes (Registro 0150)</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    O sistema varre o arquivo SPED e extrai todos os participantes do Bloco 0 (Registro 0150), identificando os CNPJs únicos dos fornecedores que aparecem nas operações fiscais.
                                </p>
                            </div>
                        </div>

                        {{-- Etapa 3 --}}
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">3</div>
                            <div class="pt-1">
                                <h4 class="font-semibold text-gray-900">Consultas em Órgãos Públicos</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    Para cada CNPJ identificado, realizamos consultas automatizadas em diversos órgãos públicos: Receita Federal, SEFAZ, CGU, TST, MTE, Caixa e cartórios de protesto. Você escolhe quais consultas deseja realizar.
                                </p>
                            </div>
                        </div>

                        {{-- Etapa 4 --}}
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">4</div>
                            <div class="pt-1">
                                <h4 class="font-semibold text-gray-900">Geração do Score de Risco</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    Baseado nos resultados das consultas, cada fornecedor recebe um score de risco de 0 a 100. Quanto maior o score, menor o risco. Fornecedores com pendências, restrições ou irregularidades recebem scores mais baixos.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Classificação de Risco --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Classificação de Risco</h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Cada fornecedor é classificado em uma das cinco categorias de risco baseado no score calculado:
                    </p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="text-center p-4 rounded-lg border-2 border-green-500 bg-green-50">
                            <div class="text-2xl mb-2">✅</div>
                            <div class="text-lg font-bold text-green-700 mb-1">80-100</div>
                            <div class="text-sm font-semibold text-gray-800">Baixo</div>
                            <p class="text-xs text-gray-600 mt-2">Fornecedor regular</p>
                        </div>
                        <div class="text-center p-4 rounded-lg border-2 border-amber-500 bg-amber-50">
                            <div class="text-2xl mb-2">⚠️</div>
                            <div class="text-lg font-bold text-amber-700 mb-1">60-79</div>
                            <div class="text-sm font-semibold text-gray-800">Atenção</div>
                            <p class="text-xs text-gray-600 mt-2">Requer monitoramento</p>
                        </div>
                        <div class="text-center p-4 rounded-lg border-2 border-orange-500 bg-orange-50">
                            <div class="text-2xl mb-2">🟠</div>
                            <div class="text-lg font-bold text-orange-700 mb-1">40-59</div>
                            <div class="text-sm font-semibold text-gray-800">Moderado</div>
                            <p class="text-xs text-gray-600 mt-2">Algumas pendências</p>
                        </div>
                        <div class="text-center p-4 rounded-lg border-2 border-red-500 bg-red-50">
                            <div class="text-2xl mb-2">🔴</div>
                            <div class="text-lg font-bold text-red-700 mb-1">20-39</div>
                            <div class="text-sm font-semibold text-gray-800">Alto</div>
                            <p class="text-xs text-gray-600 mt-2">Múltiplas restrições</p>
                        </div>
                        <div class="text-center p-4 rounded-lg border-2 border-red-700 bg-red-100">
                            <div class="text-2xl mb-2">⛔</div>
                            <div class="text-lg font-bold text-red-800 mb-1">0-19</div>
                            <div class="text-sm font-semibold text-gray-800">Crítico</div>
                            <p class="text-xs text-gray-600 mt-2">Risco grave</p>
                        </div>
                    </div>
                </div>

                {{-- Órgãos Consultados --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Órgãos e Consultas Disponíveis</h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Cada consulta custa <strong class="text-blue-600">R$ 0,55</strong> por fornecedor. Você pode selecionar quais consultas deseja realizar:
                    </p>
                    
                    <div class="space-y-4">
                        {{-- Receita Federal --}}
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-xl">🏛️</span>
                                <h4 class="font-semibold text-gray-900">Receita Federal</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <div>
                                        <strong>Situação Cadastral CNPJ</strong>
                                        <p class="text-gray-500 text-xs">Verifica se o CNPJ está ativo, baixado, inapto ou suspenso na Receita Federal.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <div>
                                        <strong>Quadro Societário (QSA)</strong>
                                        <p class="text-gray-500 text-xs">Lista os sócios, administradores e suas participações na empresa.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <div>
                                        <strong>Optante Simples Nacional</strong>
                                        <p class="text-gray-500 text-xs">Verifica se a empresa é optante pelo Simples Nacional ou MEI.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <div>
                                        <strong>CND Federal (PGFN)</strong>
                                        <p class="text-gray-500 text-xs">Certidão de débitos relativos a tributos federais e à dívida ativa da União.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SEFAZ --}}
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-xl">🏢</span>
                                <h4 class="font-semibold text-gray-900">SEFAZ / SINTEGRA</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <div>
                                        <strong>Inscrição Estadual</strong>
                                        <p class="text-gray-500 text-xs">Situação cadastral na SEFAZ do estado (ativa, suspensa, cancelada).</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <div>
                                        <strong>CND Estadual</strong>
                                        <p class="text-gray-500 text-xs">Certidão de débitos estaduais (ICMS).</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CGU --}}
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-xl">🔍</span>
                                <h4 class="font-semibold text-gray-900">CGU (Controladoria-Geral da União)</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <div>
                                        <strong>CEIS</strong>
                                        <p class="text-gray-500 text-xs">Cadastro de Empresas Inidôneas e Suspensas.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <div>
                                        <strong>CNEP</strong>
                                        <p class="text-gray-500 text-xs">Cadastro Nacional de Empresas Punidas.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <div>
                                        <strong>CEPIM</strong>
                                        <p class="text-gray-500 text-xs">Entidades sem fins lucrativos impedidas.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Outros órgãos --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xl">🏛️</span>
                                    <h4 class="font-semibold text-gray-900">Prefeitura</h4>
                                </div>
                                <p class="text-sm text-gray-600"><strong>CND Municipal</strong> - Certidão de débitos municipais (ISS, IPTU, taxas).</p>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xl">🏦</span>
                                    <h4 class="font-semibold text-gray-900">Caixa Econômica</h4>
                                </div>
                                <p class="text-sm text-gray-600"><strong>CRF (FGTS)</strong> - Certificado de Regularidade do FGTS.</p>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xl">⚖️</span>
                                    <h4 class="font-semibold text-gray-900">TST</h4>
                                </div>
                                <p class="text-sm text-gray-600"><strong>CNDT</strong> - Certidão Negativa de Débitos Trabalhistas.</p>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xl">👷</span>
                                    <h4 class="font-semibold text-gray-900">MTE</h4>
                                </div>
                                <p class="text-sm text-gray-600"><strong>Lista de Trabalho Escravo</strong> - Empregadores com trabalho análogo à escravidão.</p>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4 md:col-span-2">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xl">📋</span>
                                    <h4 class="font-semibold text-gray-900">IEPTB (Cartórios de Protesto)</h4>
                                </div>
                                <p class="text-sm text-gray-600"><strong>Protestos</strong> - Consulta títulos protestados em cartórios de todo Brasil.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Benefícios --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Por que usar a Análise de Risco?</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900">Compliance Fiscal</h4>
                            <p class="text-sm text-gray-600">Evite créditos indevidos de fornecedores com CNPJ baixado, inapto ou suspenso que podem gerar glosas em fiscalizações.</p>
                        </div>
                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900">Agilidade</h4>
                            <p class="text-sm text-gray-600">Automatize a verificação de centenas de fornecedores em minutos. O que levaria dias manualmente, fazemos automaticamente.</p>
                        </div>
                        <div class="space-y-2">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900">Due Diligence</h4>
                            <p class="text-sm text-gray-600">Identifique fornecedores em listas restritivas, com trabalho escravo, protestos ou impedimentos legais antes de fechar negócios.</p>
                        </div>
                    </div>
                </div>

                {{-- Alerta --}}
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-amber-800">Importante</h4>
                            <p class="text-sm text-amber-700 mt-1">
                                Operações com fornecedores em situação irregular podem gerar autuações fiscais, glosas de créditos de ICMS/PIS/COFINS e até responsabilização solidária. A análise de risco ajuda a prevenir esses problemas.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- CTA --}}
                <div class="text-center">
                    <button 
                        type="button" 
                        class="go-to-analise inline-flex items-center justify-center gap-2 px-8 py-3 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        <span>Iniciar Análise de Risco</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>
        {{-- Fim Aba: Como Funciona --}}

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== Navegação por Tabs ==========
    const tabs = document.querySelectorAll('.analise-tab');
    const tabContents = document.querySelectorAll('.analise-tab-content');
    const goToAnaliseButtons = document.querySelectorAll('.go-to-analise');

    function switchTab(tabName) {
        // Atualizar estilos dos botões de tab
        tabs.forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.remove('text-gray-600', 'hover:text-gray-900', 'hover:bg-gray-50');
                tab.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                tab.setAttribute('aria-selected', 'true');
            } else {
                tab.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                tab.classList.add('text-gray-600', 'hover:text-gray-900', 'hover:bg-gray-50');
                tab.setAttribute('aria-selected', 'false');
            }
        });

        // Mostrar/ocultar conteúdo das tabs
        tabContents.forEach(content => {
            if (content.id === `tab-${tabName}`) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });
    }

    // Event listeners para tabs
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            switchTab(tab.dataset.tab);
        });
    });

    // Botão "Iniciar Análise" na aba Como Funciona
    goToAnaliseButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            switchTab('analise');
        });
    });

    // ========== Elementos do Formulário ==========
    const fileInput = document.getElementById('arquivo_sped');
    const dropzone = document.getElementById('arquivo-dropzone');
    const dropzoneTitle = document.getElementById('dropzone-title');
    const dropzoneSubtitle = document.getElementById('dropzone-subtitle');
    const fileMeta = document.getElementById('arquivo-file-meta');
    const fileNameEl = document.getElementById('arquivo-file-name');
    const fileSizeEl = document.getElementById('arquivo-file-size');
    const changeFileBtn = document.getElementById('arquivo-change-file');
    const submitBtn = document.getElementById('iniciar-analise-btn');
    const form = document.getElementById('analise-risco-form');
    const tipoEfdContrib = document.getElementById('tipo-efd-contrib');
    const tipoEfdFiscal = document.getElementById('tipo-efd-fiscal');
    const tipoEfdContribLabel = document.getElementById('tipo-efd-contrib-label');
    const tipoEfdFiscalLabel = document.getElementById('tipo-efd-fiscal-label');

    // Consultas
    const consultaCheckboxes = document.querySelectorAll('.consulta-checkbox');
    const totalConsultasEl = document.getElementById('total-consultas-selecionadas');
    const custoPorFornecedorEl = document.getElementById('custo-por-fornecedor');
    const selecionarTodasBtn = document.getElementById('selecionar-todas-btn');
    const desmarcarTodasBtn = document.getElementById('desmarcar-todas-btn');
    
    const CUSTO_POR_CONSULTA = 0.55;

    let selectedFile = null;

    // Função para atualizar contagem de consultas
    function atualizarContagem() {
        const selecionadas = document.querySelectorAll('.consulta-checkbox:checked').length;
        const custo = selecionadas * CUSTO_POR_CONSULTA;
        
        totalConsultasEl.textContent = selecionadas;
        custoPorFornecedorEl.textContent = `R$ ${custo.toFixed(2).replace('.', ',')}`;
        
        // Atualizar estilos das linhas da tabela (opcional - apenas para feedback visual)
        consultaCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr.consulta-item');
            if (row) {
                if (checkbox.checked) {
                    row.classList.add('bg-blue-50');
                } else {
                    row.classList.remove('bg-blue-50');
                }
            }
        });
    }

    // Event listeners para checkboxes
    consultaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', atualizarContagem);
    });

    // Selecionar todas
    selecionarTodasBtn.addEventListener('click', () => {
        consultaCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        atualizarContagem();
    });

    // Desmarcar todas
    desmarcarTodasBtn.addEventListener('click', () => {
        consultaCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        atualizarContagem();
    });

    // Inicializar contagem
    atualizarContagem();

    // Estilização dos radio buttons
    function updateRadioStyles() {
        if (tipoEfdContrib.checked) {
            tipoEfdContribLabel.classList.remove('border-gray-300', 'hover:border-blue-400', 'hover:bg-gray-50');
            tipoEfdContribLabel.classList.add('border-blue-600', 'bg-blue-50');
            tipoEfdFiscalLabel.classList.remove('border-blue-600', 'bg-blue-50');
            tipoEfdFiscalLabel.classList.add('border-gray-300', 'hover:border-blue-400', 'hover:bg-gray-50');
        } else {
            tipoEfdFiscalLabel.classList.remove('border-gray-300', 'hover:border-blue-400', 'hover:bg-gray-50');
            tipoEfdFiscalLabel.classList.add('border-blue-600', 'bg-blue-50');
            tipoEfdContribLabel.classList.remove('border-blue-600', 'bg-blue-50');
            tipoEfdContribLabel.classList.add('border-gray-300', 'hover:border-blue-400', 'hover:bg-gray-50');
        }
    }

    tipoEfdContrib.addEventListener('change', updateRadioStyles);
    tipoEfdFiscal.addEventListener('change', updateRadioStyles);
    updateRadioStyles();

    // Função para formatar tamanho do arquivo
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Função para atualizar UI do arquivo
    function updateFileUI(file) {
        if (!file) {
            dropzoneTitle.textContent = 'Arraste o arquivo SPED aqui';
            dropzoneSubtitle.textContent = 'ou clique para selecionar';
            fileMeta.classList.add('hidden');
            selectedFile = null;
            submitBtn.disabled = true;
            return;
        }

        selectedFile = file;
        dropzoneTitle.textContent = file.name;
        dropzoneSubtitle.textContent = formatFileSize(file.size);
        fileNameEl.textContent = file.name;
        fileSizeEl.textContent = formatFileSize(file.size);
        fileMeta.classList.remove('hidden');
        submitBtn.disabled = false;
    }

    // Drag and drop
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('border-blue-500', 'bg-blue-50');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-blue-500', 'bg-blue-50');
        
        const files = Array.from(e.dataTransfer.files).filter(f => f.name.endsWith('.txt'));
        if (files.length > 0) {
            const file = files[0];
            if (file.size > 50 * 1024 * 1024) {
                alert('Arquivo muito grande. Máximo permitido: 50MB');
                return;
            }
            fileInput.files = e.dataTransfer.files;
            updateFileUI(file);
        }
    });

    dropzone.addEventListener('click', () => {
        fileInput.click();
    });

    dropzone.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            if (file.size > 50 * 1024 * 1024) {
                alert('Arquivo muito grande. Máximo permitido: 50MB');
                fileInput.value = '';
                return;
            }
            updateFileUI(file);
        }
    });

    changeFileBtn.addEventListener('click', () => {
        fileInput.value = '';
        updateFileUI(null);
        fileInput.click();
    });

    // Submit do formulário
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        if (!selectedFile) {
            alert('Por favor, selecione um arquivo SPED');
            return;
        }

        const consultasSelecionadas = Array.from(document.querySelectorAll('.consulta-checkbox:checked')).map(cb => cb.value);
        
        if (consultasSelecionadas.length === 0) {
            alert('Por favor, selecione pelo menos uma consulta');
            return;
        }

        // Por enquanto, apenas mostra mensagem (não faz nada)
        alert(`Formulário enviado!\n\nConsultas selecionadas: ${consultasSelecionadas.length}\nCusto por fornecedor: R$ ${(consultasSelecionadas.length * CUSTO_POR_CONSULTA).toFixed(2).replace('.', ',')}\n\n(Funcionalidade será implementada em breve)`);
        
        // Aqui será implementada a lógica de envio quando o backend estiver pronto
        // const formData = new FormData();
        // formData.append('tipo_sped', document.querySelector('input[name="tipo_sped"]:checked').value);
        // formData.append('arquivo_sped', selectedFile);
        // formData.append('cliente_id', document.getElementById('cliente_id').value);
        // consultasSelecionadas.forEach(c => formData.append('consultas[]', c));
        // 
        // fetch('/app/sped-analise-risco/upload', {
        //     method: 'POST',
        //     body: formData,
        //     headers: {
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        //     }
        // }).then(...)
    });
});
</script>

