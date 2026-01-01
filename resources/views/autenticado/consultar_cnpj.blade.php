{{-- Consultar CNPJ --}}
<div class="min-h-screen bg-gray-50" id="consultar-cnpj-container">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">
                        Consultar CNPJ
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">
                        Verifique dados cadastrais de empresas na Receita Federal
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">💳</span>
                    <span class="text-sm font-semibold text-gray-800" id="saldo-creditos">147 créditos</span>
                    <button class="text-xs text-blue-600 hover:text-blue-700 font-semibold ml-2">
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
                    <div class="space-y-4">
                        <div>
                            <label for="input-cnpj-unica" class="block text-sm font-medium text-gray-700 mb-2">CNPJ:</label>
                            <input 
                                type="text" 
                                id="input-cnpj-unica" 
                                placeholder="00.000.000/0000-00"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center gap-2 text-sm text-gray-700">
                                <span>💳</span>
                                <span>Custo: <strong>1 crédito</strong></span>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button 
                                type="button" 
                                id="btn-consultar-unica" 
                                disabled
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold cursor-not-allowed opacity-50 transition-opacity"
                            >
                                🔍 Consultar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Seção: Consulta em Lote --}}
            <div id="secao-lote" class="modo-secao hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="space-y-4">
                        <div>
                            <label for="textarea-cnpjs" class="block text-sm font-medium text-gray-700 mb-2">Lista de CNPJs (máximo 100, um por linha):</label>
                            <textarea 
                                id="textarea-cnpjs" 
                                rows="8"
                                placeholder="12.345.678/0001-90&#10;23.456.789/0001-01&#10;34.567.890/0001-12&#10;...&#10;(um CNPJ por linha)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                            ></textarea>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-600">Ou:</span>
                            <label class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg cursor-pointer transition-colors">
                                <input type="file" id="file-upload" accept=".txt,.csv" class="hidden">
                                <span>📁</span>
                                <span class="text-sm font-medium text-gray-700">Importar arquivo .txt/.csv</span>
                            </label>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2 text-gray-700">
                                    <span>📊</span>
                                    <span>CNPJs detectados: <strong id="cnpjs-count">0</strong></span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-700">
                                    <span>💳</span>
                                    <span>Custo total: <strong id="custo-total">0 créditos</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button 
                                type="button" 
                                id="btn-consultar-lote" 
                                disabled
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold cursor-not-allowed opacity-50 transition-opacity"
                            >
                                🔍 Consultar Todos
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card de Resultado (Consulta Única) --}}
            <div id="resultado-unica" class="hidden">
                <div class="bg-white rounded-lg shadow-md p-6 border-2 border-gray-200">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div id="status-badge" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold mb-3 border-l-4">
                                <span id="status-icon">🟢</span>
                                <span id="status-text">ATIVA</span>
                            </div>
                            <h3 id="razao-social" class="text-xl font-bold text-gray-800 mb-1"></h3>
                            <p id="nome-fantasia" class="text-sm text-gray-600 mb-4"></p>
                            <p id="result-cnpj" class="text-sm font-mono text-gray-600"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button 
                                type="button" 
                                id="btn-favoritar"
                                class="p-2 text-gray-400 hover:text-yellow-500 transition-colors"
                                title="Salvar nos favoritos"
                            >
                                <span id="icon-favoritar">⭐</span>
                            </button>
                            <button 
                                type="button" 
                                id="btn-monitorar"
                                class="p-2 text-gray-400 hover:text-blue-500 transition-colors"
                                title="Monitorar"
                            >
                                🔔
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="border-t border-gray-200 pt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3">DADOS CADASTRAIS</h4>
                                    <div class="space-y-2 text-sm">
                                        <div>
                                            <span class="text-gray-600">Abertura:</span>
                                            <div class="font-semibold text-gray-800" id="result-abertura"></div>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Capital:</span>
                                            <div class="font-semibold text-gray-800" id="result-capital"></div>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Porte:</span>
                                            <div class="font-semibold text-gray-800" id="result-porte"></div>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Natureza Jurídica:</span>
                                            <div class="font-semibold text-gray-800" id="result-natureza"></div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3">ENDEREÇO</h4>
                                    <div class="space-y-2 text-sm">
                                        <div>
                                            <span class="text-gray-600">Logradouro:</span>
                                            <div class="font-semibold text-gray-800" id="result-endereco"></div>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Bairro:</span>
                                            <div class="font-semibold text-gray-800" id="result-bairro"></div>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Cidade:</span>
                                            <div class="font-semibold text-gray-800" id="result-cidade"></div>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">CEP:</span>
                                            <div class="font-semibold text-gray-800" id="result-cep"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">CONTATO</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">📞 Telefone:</span>
                                    <div class="font-semibold text-gray-800" id="result-telefone"></div>
                                </div>
                                <div>
                                    <span class="text-gray-600">📧 Email:</span>
                                    <div class="font-semibold text-gray-800" id="result-email"></div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">ATIVIDADE ECONÔMICA</h4>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <span class="text-gray-600 font-semibold">Principal:</span>
                                    <div class="text-gray-800 mt-1" id="result-cnae-principal"></div>
                                </div>
                                <div>
                                    <span class="text-gray-600 font-semibold">Secundárias:</span>
                                    <ul class="list-disc list-inside text-gray-800 mt-1 space-y-1" id="result-cnaes-secundarios"></ul>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <button 
                                type="button" 
                                class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition-colors flex items-center gap-2"
                            >
                                📄 Exportar PDF
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Card de Upsell --}}
                <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200 shadow-md p-6">
                    <div class="text-center mb-4">
                        <span class="text-3xl mb-2 block">💡</span>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">QUER SABER MAIS SOBRE ESSA EMPRESA?</h3>
                        <p class="text-sm text-gray-700">Com a Análise de Risco Completa você descobre:</p>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div class="flex items-start gap-3">
                            <span class="text-red-500 text-lg">❌</span>
                            <div>
                                <div class="font-semibold text-gray-800">Quadro de Sócios (QSA)</div>
                                <div class="text-xs text-gray-600">Quem são os sócios e administradores</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-red-500 text-lg">❌</span>
                            <div>
                                <div class="font-semibold text-gray-800">Simples Nacional</div>
                                <div class="text-xs text-gray-600">Se é optante e desde quando</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-red-500 text-lg">❌</span>
                            <div>
                                <div class="font-semibold text-gray-800">Inscrição Estadual</div>
                                <div class="text-xs text-gray-600">Situação em cada estado</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-red-500 text-lg">❌</span>
                            <div>
                                <div class="font-semibold text-gray-800">Listas Restritivas</div>
                                <div class="text-xs text-gray-600">CEIS, CNEP, Trabalho Escravo, etc</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-red-500 text-lg">❌</span>
                            <div>
                                <div class="font-semibold text-gray-800">Score de Risco (0-100)</div>
                                <div class="text-xs text-gray-600">Avaliação automática de confiabilidade</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-red-500 text-lg">❌</span>
                            <div>
                                <div class="font-semibold text-gray-800">Relatório PDF Completo</div>
                                <div class="text-xs text-gray-600">Documento profissional para anexar</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <a 
                            href="/app/novo_cliente" 
                            id="btn-upsell-analise"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm"
                        >
                            🔍 Fazer Análise de Risco Completa
                            <span class="text-xs opacity-90">10 créditos</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Tabela de Resultados (Consulta em Lote) --}}
            <div id="resultado-lote" class="hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">
                            RESULTADOS (<span id="total-resultados">0</span> CNPJs consultados)
                        </h3>
                        <div class="flex gap-2">
                            <button 
                                type="button" 
                                class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition-colors flex items-center gap-2"
                            >
                                📄 Exportar PDF
                            </button>
                            <button 
                                type="button" 
                                class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors flex items-center gap-2"
                            >
                                📊 Exportar Excel
                            </button>
                            <button 
                                type="button" 
                                class="px-3 py-1.5 bg-yellow-500 text-white rounded-lg text-sm font-semibold hover:bg-yellow-600 transition-colors flex items-center gap-2"
                            >
                                ⭐ Salvar Todos
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <div class="text-sm text-gray-700">
                                <span class="font-semibold">Resumo:</span>
                                <span id="resumo-status"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 flex flex-col sm:flex-row gap-3">
                        <input 
                            type="text" 
                            id="busca-tabela" 
                            placeholder="Buscar..."
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        <select 
                            id="filtro-status" 
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Filtrar: Todos</option>
                            <option value="ATIVA">🟢 Ativa</option>
                            <option value="SUSPENSA">🟡 Suspensa</option>
                            <option value="INAPTA">🔴 Inapta</option>
                            <option value="BAIXADA">⚫ Baixada</option>
                            <option value="NULA">🟣 Nula</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CNPJ</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razão Social</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cidade</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-resultados" class="bg-white divide-y divide-gray-200">
                                {{-- Preenchido via JavaScript --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Mostrando <span id="mostrando-de">1</span>-<span id="mostrando-ate">10</span> de <span id="total-pagina">0</span>
                        </div>
                        <div class="flex gap-2" id="paginacao">
                            {{-- Preenchido via JavaScript --}}
                        </div>
                    </div>
                </div>

                {{-- Card de Upsell para Lote --}}
                <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200 shadow-md p-6">
                    <div class="text-center">
                        <span class="text-3xl mb-2 block">💡</span>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Quer análise completa de todos?</h3>
                        <a 
                            href="/app/novo_cliente" 
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm"
                        >
                            🔍 Análise de Risco em Lote
                            <span class="text-xs opacity-90" id="custo-lote-upsell">0 créditos</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Cards de Favoritos e Histórico --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Card de Favoritos --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">⭐ FAVORITOS</h3>
                        <button class="text-sm text-blue-600 hover:text-blue-700 font-semibold">Ver todos</button>
                    </div>
                    <div id="lista-favoritos" class="space-y-2">
                        {{-- Preenchido via JavaScript --}}
                    </div>
                </div>

                {{-- Card de Histórico --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">📋 CONSULTAS RECENTES</h3>
                        <button class="text-sm text-blue-600 hover:text-blue-700 font-semibold">Ver todas</button>
                    </div>
                    <div id="lista-historico" class="space-y-2">
                        {{-- Preenchido via JavaScript --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Monitoramento --}}
<div id="modal-monitoramento-backdrop" class="hidden fixed inset-0 bg-black/50 z-[9999] flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-gray-200 shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 space-y-5">
            {{-- Header --}}
            <div class="flex items-start justify-between gap-4">
                <h3 class="text-lg font-semibold text-gray-900">🔔 MONITORAR CNPJ</h3>
                <button
                    type="button"
                    id="modal-monitoramento-close"
                    class="flex-shrink-0 rounded-lg p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition focus:outline-none focus:ring-2 focus:ring-gray-300"
                    aria-label="Fechar modal"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <div id="modal-empresa-info" class="text-sm text-gray-700 mb-4">
                    {{-- Preenchido via JavaScript --}}
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <label class="block text-sm font-medium text-gray-700 mb-3">Frequência de verificação:</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex flex-col items-center p-4 border-2 border-blue-600 rounded-lg cursor-pointer bg-blue-50 hover:bg-blue-100 transition-colors">
                        <input type="radio" name="frequencia" value="semanal" checked class="sr-only">
                        <div class="text-2xl mb-2">📅</div>
                        <div class="font-semibold text-gray-800 text-sm mb-1">Semanal</div>
                        <div class="text-xs text-gray-600">3 créditos/mês</div>
                        <div class="text-xs text-green-600 font-semibold mt-1">✓ 20% desconto</div>
                    </label>
                    <label class="flex flex-col items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-gray-50 transition-colors">
                        <input type="radio" name="frequencia" value="diario" class="sr-only">
                        <div class="text-2xl mb-2">📆</div>
                        <div class="font-semibold text-gray-800 text-sm mb-1">Diário</div>
                        <div class="text-xs text-gray-600">24 créditos/mês</div>
                        <div class="text-xs text-green-600 font-semibold mt-1">✓ 20% desconto</div>
                    </label>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <label class="block text-sm font-medium text-gray-700 mb-3">Notificar quando houver mudança:</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" checked class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700">E-mail</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" disabled class="w-4 h-4 text-gray-400 border-gray-300 rounded">
                        <span class="text-sm text-gray-500">WhatsApp (em breve)</span>
                    </label>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-sm">
                    <div class="font-semibold text-gray-800">📊 Resumo:</div>
                    <div class="text-gray-700">• Custo mensal estimado: <span id="modal-custo-mensal">3 créditos</span></div>
                    <div class="text-gray-700">• Seu saldo atual: <span id="modal-saldo-atual">147 créditos</span></div>
                    <div class="text-gray-700">• Duração estimada: <span id="modal-duracao">~49 meses</span></div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <div class="text-sm text-gray-700 space-y-2">
                    <p>O sistema verificará automaticamente e te alertará se:</p>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                        <li>Situação cadastral mudar (ex: Ativa → Inapta)</li>
                        <li>Empresa for baixada</li>
                        <li>Endereço ou razão social mudar</li>
                    </ul>
                </div>
                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                    ⚠️ Se seu saldo zerar, o monitoramento será pausado automaticamente e voltará quando você recarregar.
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <div class="flex flex-col-reverse sm:flex-row gap-3">
                    <button
                        type="button"
                        id="modal-monitoramento-cancelar"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        id="modal-monitoramento-ativar"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                    >
                        🔔 Ativar Monitoramento
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
(function() {
    'use strict';

    // Estado global
    let currentModo = 'unica';
    let favoritos = new Set();
    let historico = [];
    let resultadosLote = [];
    let currentPage = 1;
    const itemsPerPage = 10;
    let cnpjAtual = '';

    // Dados mockados
    const favoritosMockados = [
        { cnpj: '12.345.678/0001-90', nome: 'ACME Comércio', status: 'ATIVA' },
        { cnpj: '23.456.789/0001-01', nome: 'XYZ Indústria', status: 'ATIVA' },
        { cnpj: '34.567.890/0001-12', nome: 'Empresa ABC', status: 'INAPTA' }
    ];

    const historicoMockado = [
        { cnpj: '12.345.678/0001-90', nome: 'ACME Comércio', status: 'ATIVA', tempo: 'há 5 min' },
        { cnpj: '23.456.789/0001-01', nome: 'Empresa Inapta', status: 'INAPTA', tempo: 'há 1 hora' },
        { cnpj: '34.567.890/0001-12', nome: 'XYZ Indústria', status: 'ATIVA', tempo: 'ontem' },
        { cnpj: '45.678.901/0001-23', nome: 'Empresa Baixada', status: 'BAIXADA', tempo: '2 dias' },
        { cnpj: '56.789.012/0001-34', nome: 'Distribuidora', status: 'ATIVA', tempo: '3 dias' }
    ];

    const resultadoMockadoAtiva = {
        status: 'ATIVA',
        razaoSocial: 'ACME COMÉRCIO LTDA',
        nomeFantasia: 'ACME STORE',
        cnpj: '12.345.678/0001-90',
        abertura: '15/03/2018',
        capital: 'R$ 100.000,00',
        porte: 'ME (Microempresa)',
        natureza: '206-2 - Sociedade Empresária Limitada',
        endereco: 'Rua das Flores, 123, Sala 45',
        bairro: 'Centro',
        cidade: 'Sao Paulo - MS',
        cep: '79800-000',
        telefone: '(67) 3421-1234',
        email: 'contato@acmestore.com.br',
        cnaePrincipal: '4711-3/02 - Comércio varejista de mercadorias em geral, com predominância de prod. alimentícios - supermercados',
        cnaesSecundarios: [
            '4729-6/99 - Comércio varejista de produtos alimentícios em geral',
            '4723-7/00 - Comércio varejista de bebidas'
        ]
    };

    const resultadoMockadoInapta = {
        status: 'INAPTA',
        razaoSocial: 'EMPRESA INAPTA LTDA',
        nomeFantasia: '',
        cnpj: '23.456.789/0001-01',
        abertura: '10/01/2015',
        capital: 'R$ 50.000,00',
        porte: 'ME',
        natureza: '206-2 - Sociedade Empresária Limitada',
        endereco: 'Av. Principal, 456',
        bairro: 'Centro',
        cidade: 'São Paulo - SP',
        cep: '01000-000',
        telefone: '(11) 1234-5678',
        email: 'contato@inapta.com.br',
        cnaePrincipal: '4711-3/02 - Comércio varejista',
        cnaesSecundarios: []
    };

    const resultadoMockadoBaixada = {
        status: 'BAIXADA',
        razaoSocial: 'EMPRESA BAIXADA LTDA',
        nomeFantasia: '',
        cnpj: '45.678.901/0001-23',
        abertura: '20/05/2010',
        capital: 'R$ 200.000,00',
        porte: 'EPP',
        natureza: '206-2 - Sociedade Empresária Limitada',
        endereco: 'Rua Antiga, 789',
        bairro: 'Industrial',
        cidade: 'Curitiba - PR',
        cep: '80000-000',
        telefone: '(41) 9876-5432',
        email: 'contato@baixada.com.br',
        cnaePrincipal: '2511-0/00 - Fabricação de estruturas metálicas',
        cnaesSecundarios: []
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

    // Função para validar CNPJ
    function validarCNPJ(cnpj) {
        const clean = cnpj.replace(/\D/g, '');
        return clean.length === 14;
    }

    // Função para extrair CNPJs de texto
    function extrairCNPJs(texto) {
        const regex = /(\d{2}\.?\d{3}\.?\d{3}\/?\d{4}-?\d{2})/g;
        const matches = texto.match(regex) || [];
        const cnpjs = matches.map(cnpj => {
            const clean = cnpj.replace(/\D/g, '');
            if (clean.length === 14) {
                return maskCNPJ(clean);
            }
            return null;
        }).filter(cnpj => cnpj !== null);
        return [...new Set(cnpjs)]; // Remove duplicatas
    }

    // Função para alternar modo
    function switchModo(modo) {
        currentModo = modo;
        
        // Atualizar botões de modo
        document.querySelectorAll('.modo-tab').forEach(btn => {
            if (btn.dataset.modo === modo) {
                btn.classList.remove('text-gray-600', 'hover:text-gray-900', 'hover:bg-gray-50');
                btn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                btn.setAttribute('aria-selected', 'true');
            } else {
                btn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                btn.classList.add('text-gray-600', 'hover:text-gray-900', 'hover:bg-gray-50');
                btn.setAttribute('aria-selected', 'false');
            }
        });

        // Mostrar/esconder conteúdo
        document.querySelectorAll('.modo-secao').forEach(secao => {
            secao.classList.add('hidden');
        });
        document.getElementById(`secao-${modo}`).classList.remove('hidden');

        // Esconder resultados ao trocar de modo
        document.getElementById('resultado-unica').classList.add('hidden');
        document.getElementById('resultado-lote').classList.add('hidden');
    }

    // Função para atualizar botão de consulta única
    function atualizarBtnConsultaUnica() {
        const cnpj = document.getElementById('input-cnpj-unica').value;
        const btn = document.getElementById('btn-consultar-unica');
        
        if (cnpj && validarCNPJ(cnpj)) {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            btn.classList.add('opacity-100', 'hover:bg-blue-700', 'cursor-pointer');
        } else {
            btn.disabled = true;
            btn.classList.remove('opacity-100', 'hover:bg-blue-700', 'cursor-pointer');
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Função para atualizar contador de CNPJs em lote
    function atualizarContadorLote() {
        const texto = document.getElementById('textarea-cnpjs').value;
        const cnpjs = extrairCNPJs(texto);
        const count = Math.min(cnpjs.length, 100);
        
        document.getElementById('cnpjs-count').textContent = count;
        document.getElementById('custo-total').textContent = `${count} créditos`;
        
        const btn = document.getElementById('btn-consultar-lote');
        
        if (count > 0) {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            btn.classList.add('opacity-100', 'hover:bg-blue-700', 'cursor-pointer');
        } else {
            btn.disabled = true;
            btn.classList.remove('opacity-100', 'hover:bg-blue-700', 'cursor-pointer');
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Função para obter classes de status
    function getStatusClasses(status) {
        const classes = {
            'ATIVA': 'bg-green-100 text-green-800 border-green-500',
            'SUSPENSA': 'bg-amber-100 text-amber-800 border-amber-500',
            'INAPTA': 'bg-red-100 text-red-800 border-red-500',
            'BAIXADA': 'bg-gray-100 text-gray-800 border-gray-500',
            'NULA': 'bg-purple-100 text-purple-800 border-purple-500'
        };
        return classes[status] || classes['ATIVA'];
    }

    function getStatusIcon(status) {
        const icons = {
            'ATIVA': '🟢',
            'SUSPENSA': '🟡',
            'INAPTA': '🔴',
            'BAIXADA': '⚫',
            'NULA': '🟣'
        };
        return icons[status] || '🟢';
    }

    // Função para exibir resultado único
    function exibirResultadoUnica(dados) {
        const card = document.getElementById('resultado-unica');
        const statusBadge = document.getElementById('status-badge');
        const statusIcon = document.getElementById('status-icon');
        const statusText = document.getElementById('status-text');
        
        cnpjAtual = dados.cnpj;
        
        // Atualizar status
        const statusClasses = getStatusClasses(dados.status);
        statusBadge.className = `inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold mb-3 border-l-4 ${statusClasses}`;
        statusIcon.textContent = getStatusIcon(dados.status);
        statusText.textContent = dados.status;
        
        // Preencher dados
        document.getElementById('razao-social').textContent = dados.razaoSocial;
        document.getElementById('nome-fantasia').textContent = dados.nomeFantasia ? `Nome Fantasia: ${dados.nomeFantasia}` : '';
        document.getElementById('result-cnpj').textContent = `CNPJ: ${dados.cnpj}`;
        document.getElementById('result-abertura').textContent = dados.abertura;
        document.getElementById('result-capital').textContent = dados.capital;
        document.getElementById('result-porte').textContent = dados.porte;
        document.getElementById('result-natureza').textContent = dados.natureza;
        document.getElementById('result-endereco').textContent = dados.endereco;
        document.getElementById('result-bairro').textContent = dados.bairro;
        document.getElementById('result-cidade').textContent = dados.cidade;
        document.getElementById('result-cep').textContent = dados.cep;
        document.getElementById('result-telefone').textContent = dados.telefone;
        document.getElementById('result-email').textContent = dados.email;
        document.getElementById('result-cnae-principal').textContent = dados.cnaePrincipal;
        
        // CNAEs secundários
        const cnaesSec = document.getElementById('result-cnaes-secundarios');
        if (dados.cnaesSecundarios && dados.cnaesSecundarios.length > 0) {
            cnaesSec.innerHTML = dados.cnaesSecundarios.map(cnae => `<li>${cnae}</li>`).join('');
        } else {
            cnaesSec.innerHTML = '<li class="text-gray-400">Nenhuma atividade secundária</li>';
        }
        
        // Verificar se está nos favoritos
        const iconFavoritar = document.getElementById('icon-favoritar');
        if (favoritos.has(dados.cnpj)) {
            iconFavoritar.textContent = '⭐';
            iconFavoritar.parentElement.classList.add('text-yellow-500');
        } else {
            iconFavoritar.textContent = '☆';
            iconFavoritar.parentElement.classList.remove('text-yellow-500');
        }
        
        // Atualizar link do upsell
        const btnUpsell = document.getElementById('btn-upsell-analise');
        const cleanCNPJ = dados.cnpj.replace(/\D/g, '');
        btnUpsell.href = `/app/novo_cliente?cnpj=${cleanCNPJ}`;
        
        // Mostrar card com animação
        card.classList.remove('hidden');
        card.style.opacity = '0';
        card.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 10);
        
        // Adicionar ao histórico
        adicionarAoHistorico(dados.cnpj, dados.razaoSocial, dados.status);
        atualizarHistorico();
    }

    // Função para adicionar ao histórico
    function adicionarAoHistorico(cnpj, nome, status) {
        historico.unshift({
            cnpj: cnpj,
            nome: nome,
            status: status,
            tempo: 'há poucos segundos'
        });
        if (historico.length > 20) {
            historico.pop();
        }
    }

    // Função para atualizar exibição do histórico
    function atualizarHistorico() {
        const container = document.getElementById('lista-historico');
        const ultimos = historico.length > 0 ? historico.slice(0, 5) : historicoMockado;
        
        container.innerHTML = ultimos.map(item => {
            const statusIcon = getStatusIcon(item.status);
            return `
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3 flex-1 text-sm">
                        <span>${statusIcon}</span>
                        <span class="font-mono text-gray-600">${item.cnpj}</span>
                        <span class="font-semibold text-gray-800">${item.nome}</span>
                        <span class="text-gray-400">${item.tempo}</span>
                    </div>
                    <button 
                        type="button" 
                        class="p-1 text-blue-600 hover:text-blue-700"
                        onclick="reconsultarCNPJ('${item.cnpj}')"
                    >
                        🔍
                    </button>
                </div>
            `;
        }).join('');
    }

    // Função para atualizar favoritos
    function atualizarFavoritos() {
        const container = document.getElementById('lista-favoritos');
        const lista = favoritosMockados.map(item => {
            const statusIcon = getStatusIcon(item.status);
            return `
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3 flex-1 text-sm">
                        <span>${statusIcon}</span>
                        <span class="font-semibold text-gray-800">${item.nome}</span>
                        <span class="font-mono text-gray-600">${item.cnpj}</span>
                    </div>
                    <button 
                        type="button" 
                        class="p-1 text-blue-600 hover:text-blue-700"
                        onclick="reconsultarCNPJ('${item.cnpj}')"
                    >
                        🔍
                    </button>
                </div>
            `;
        }).join('');
        container.innerHTML = lista;
    }

    // Função para gerar resultados em lote (mockado)
    function gerarResultadosLote(cnpjs) {
        const nomes = ['ACME Comércio', 'XYZ Indústria', 'Empresa ABC', 'Comercial 123', 'Distribuidora', 'Importadora', 'Exportadora', 'Varejista', 'Atacadista', 'Serviços'];
        const cidades = ['Sao Paulo - MS', 'São Paulo - SP', 'Curitiba - PR', 'Rio de Janeiro - RJ', 'Belo Horizonte - MG', 'Porto Alegre - RS', 'Salvador - BA', 'Recife - PE', 'Fortaleza - CE', 'Brasília - DF'];
        const statuses = ['ATIVA', 'ATIVA', 'INAPTA', 'SUSPENSA', 'ATIVA', 'ATIVA', 'BAIXADA', 'ATIVA', 'NULA', 'ATIVA'];
        
        return cnpjs.map((cnpj, index) => {
            return {
                cnpj: cnpj,
                razaoSocial: nomes[index % nomes.length],
                cidade: cidades[index % cidades.length],
                status: statuses[index % statuses.length]
            };
        });
    }

    // Função para calcular resumo de status
    function calcularResumoStatus() {
        const resumo = {
            ATIVA: 0,
            SUSPENSA: 0,
            INAPTA: 0,
            BAIXADA: 0,
            NULA: 0
        };
        
        resultadosLote.forEach(r => {
            if (resumo[r.status] !== undefined) {
                resumo[r.status]++;
            }
        });
        
        const partes = [];
        if (resumo.ATIVA > 0) partes.push(`🟢 ${resumo.ATIVA} ativos`);
        if (resumo.SUSPENSA > 0) partes.push(`🟡 ${resumo.SUSPENSA} suspensos`);
        if (resumo.INAPTA > 0) partes.push(`🔴 ${resumo.INAPTA} inaptos`);
        if (resumo.BAIXADA > 0) partes.push(`⚫ ${resumo.BAIXADA} baixados`);
        if (resumo.NULA > 0) partes.push(`🟣 ${resumo.NULA} nulas`);
        
        return partes.join(' | ') || 'Nenhum resultado';
    }

    // Função para renderizar tabela de resultados
    function renderizarTabelaResultados() {
        const tbody = document.getElementById('tbody-resultados');
        const filtro = document.getElementById('filtro-status').value;
        const busca = document.getElementById('busca-tabela').value.toLowerCase();
        
        let resultadosFiltrados = resultadosLote;
        
        // Aplicar filtro de status
        if (filtro) {
            resultadosFiltrados = resultadosFiltrados.filter(r => r.status === filtro);
        }
        
        // Aplicar busca
        if (busca) {
            resultadosFiltrados = resultadosFiltrados.filter(r => 
                r.razaoSocial.toLowerCase().includes(busca) || 
                r.cnpj.includes(busca) ||
                r.cidade.toLowerCase().includes(busca)
            );
        }
        
        // Paginação
        const total = resultadosFiltrados.length;
        const inicio = (currentPage - 1) * itemsPerPage;
        const fim = inicio + itemsPerPage;
        const resultadosPagina = resultadosFiltrados.slice(inicio, fim);
        
        // Atualizar contadores
        document.getElementById('total-resultados').textContent = resultadosLote.length;
        document.getElementById('mostrando-de').textContent = total > 0 ? inicio + 1 : 0;
        document.getElementById('mostrando-ate').textContent = Math.min(fim, total);
        document.getElementById('total-pagina').textContent = total;
        document.getElementById('resumo-status').textContent = calcularResumoStatus();
        
        // Atualizar custo do upsell em lote
        const custoLote = resultadosLote.length * 10;
        document.getElementById('custo-lote-upsell').textContent = `${custoLote} créditos`;
        
        // Renderizar linhas
        tbody.innerHTML = resultadosPagina.map(item => {
            const statusIcon = getStatusIcon(item.status);
            return `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="text-lg">${statusIcon}</span>
                    </td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-600">${item.cnpj}</td>
                    <td class="px-4 py-3 text-sm text-gray-800">${item.razaoSocial}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">${item.cidade}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="relative inline-block">
                            <button 
                                type="button" 
                                class="p-1 text-gray-400 hover:text-gray-600"
                                onclick="mostrarMenuAcoes(event, '${item.cnpj}', '${item.razaoSocial}')"
                            >
                                ⋮
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
        
        // Renderizar paginação
        renderizarPaginacao(total);
    }

    // Função para renderizar paginação
    function renderizarPaginacao(total) {
        const totalPages = Math.ceil(total / itemsPerPage);
        const container = document.getElementById('paginacao');
        
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }
        
        let html = '';
        
        // Botão anterior
        html += `<button 
            type="button" 
            onclick="mudarPagina(${currentPage - 1})"
            ${currentPage === 1 ? 'disabled class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-400 cursor-not-allowed"' : 'class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50"'}
        >&lt;</button>`;
        
        // Números de página
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += `<button 
                    type="button" 
                    onclick="mudarPagina(${i})"
                    class="px-3 py-1 border border-gray-300 rounded text-sm ${i === currentPage ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-50'}"
                >${i}</button>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += `<span class="px-3 py-1 text-sm text-gray-400">...</span>`;
            }
        }
        
        // Botão próximo
        html += `<button 
            type="button" 
            onclick="mudarPagina(${currentPage + 1})"
            ${currentPage === totalPages ? 'disabled class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-400 cursor-not-allowed"' : 'class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50"'}
        >&gt;</button>`;
        
        container.innerHTML = html;
    }

    // Funções globais para eventos
    window.mudarPagina = function(pagina) {
        const totalPages = Math.ceil(resultadosLote.length / itemsPerPage);
        if (pagina >= 1 && pagina <= totalPages) {
            currentPage = pagina;
            renderizarTabelaResultados();
        }
    };

    window.reconsultarCNPJ = function(cnpj) {
        // Preencher formulário de consulta única
        switchModo('unica');
        document.getElementById('input-cnpj-unica').value = cnpj;
        atualizarBtnConsultaUnica();
        
        // Simular consulta
        setTimeout(() => {
            let dados;
            if (cnpj === '12.345.678/0001-90') {
                dados = resultadoMockadoAtiva;
            } else if (cnpj === '23.456.789/0001-01') {
                dados = resultadoMockadoInapta;
            } else if (cnpj === '45.678.901/0001-23') {
                dados = resultadoMockadoBaixada;
            } else {
                dados = { ...resultadoMockadoAtiva, cnpj: cnpj };
            }
            exibirResultadoUnica(dados);
        }, 500);
    };

    window.mostrarMenuAcoes = function(event, cnpj, razaoSocial) {
        // Criar menu dropdown simples (pode ser melhorado)
        const cleanCNPJ = cnpj.replace(/\D/g, '');
        const opcoes = [
            `Ver detalhes de ${razaoSocial}`,
            `Fazer Análise de Risco (10 créditos)`,
            'Favoritar',
            'Monitorar'
        ];
        const escolha = prompt(`Ações para ${cnpj}:\n\n1. Ver detalhes\n2. Fazer Análise de Risco\n3. Favoritar\n4. Monitorar\n\nDigite o número:`);
        if (escolha === '2') {
            window.location.href = `/app/novo_cliente?cnpj=${cleanCNPJ}`;
        }
    };

    // Função para abrir modal de monitoramento
    function abrirModalMonitoramento(cnpj, razaoSocial) {
        const modal = document.getElementById('modal-monitoramento-backdrop');
        const info = document.getElementById('modal-empresa-info');
        
        info.innerHTML = `
            <div class="font-semibold text-gray-800">${razaoSocial}</div>
            <div class="text-gray-600">CNPJ: ${cnpj}</div>
        `;
        
        // Atualizar resumo baseado na frequência selecionada
        atualizarResumoModal();
        
        modal.classList.remove('hidden');
    }

    // Função para atualizar resumo do modal
    function atualizarResumoModal() {
        const frequencia = document.querySelector('input[name="frequencia"]:checked').value;
        const custoMensal = frequencia === 'semanal' ? 3 : 24;
        const saldoAtual = 147;
        const duracao = Math.floor(saldoAtual / custoMensal);
        
        document.getElementById('modal-custo-mensal').textContent = `${custoMensal} créditos`;
        document.getElementById('modal-saldo-atual').textContent = `${saldoAtual} créditos`;
        document.getElementById('modal-duracao').textContent = `~${duracao} meses`;
    }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Tabs de modo
        document.querySelectorAll('.modo-tab').forEach(btn => {
            btn.addEventListener('click', function() {
                switchModo(this.dataset.modo);
            });
        });

        // Máscara CNPJ - Consulta Única
        const inputCNPJUnica = document.getElementById('input-cnpj-unica');
        inputCNPJUnica.addEventListener('input', function(e) {
            this.value = maskCNPJ(this.value);
            atualizarBtnConsultaUnica();
        });

        // Botão consultar única
        document.getElementById('btn-consultar-unica').addEventListener('click', function() {
            const cnpj = inputCNPJUnica.value;
            if (!validarCNPJ(cnpj)) {
                alert('CNPJ inválido');
                return;
            }
            
            // Simular consulta
            this.disabled = true;
            this.textContent = 'Consultando...';
            
            setTimeout(() => {
                let dados;
                if (cnpj === '12.345.678/0001-90') {
                    dados = resultadoMockadoAtiva;
                } else if (cnpj === '23.456.789/0001-01') {
                    dados = resultadoMockadoInapta;
                } else if (cnpj === '45.678.901/0001-23') {
                    dados = resultadoMockadoBaixada;
                } else {
                    dados = { ...resultadoMockadoAtiva, cnpj: cnpj };
                }
                exibirResultadoUnica(dados);
                this.disabled = false;
                this.textContent = '🔍 Consultar';
            }, 1000);
        });

        // Textarea CNPJs em lote
        const textareaCNPJs = document.getElementById('textarea-cnpjs');
        textareaCNPJs.addEventListener('input', function() {
            atualizarContadorLote();
        });

        // Upload de arquivo
        document.getElementById('file-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                textareaCNPJs.value = e.target.result;
                atualizarContadorLote();
            };
            reader.readAsText(file);
        });

        // Botão consultar lote
        document.getElementById('btn-consultar-lote').addEventListener('click', function() {
            const texto = textareaCNPJs.value;
            const cnpjs = extrairCNPJs(texto);
            
            if (cnpjs.length === 0) {
                alert('Nenhum CNPJ válido encontrado');
                return;
            }
            
            // Simular consulta
            this.disabled = true;
            this.textContent = 'Consultando...';
            
            setTimeout(() => {
                resultadosLote = gerarResultadosLote(cnpjs);
                currentPage = 1;
                renderizarTabelaResultados();
                document.getElementById('resultado-lote').classList.remove('hidden');
                this.disabled = false;
                this.textContent = '🔍 Consultar Todos';
            }, 1500);
        });

        // Filtros e busca da tabela
        document.getElementById('filtro-status').addEventListener('change', renderizarTabelaResultados);
        document.getElementById('busca-tabela').addEventListener('input', function() {
            currentPage = 1;
            renderizarTabelaResultados();
        });

        // Botão favoritar
        document.getElementById('btn-favoritar').addEventListener('click', function() {
            if (favoritos.has(cnpjAtual)) {
                favoritos.delete(cnpjAtual);
                document.getElementById('icon-favoritar').textContent = '☆';
                this.classList.remove('text-yellow-500');
            } else {
                favoritos.add(cnpjAtual);
                document.getElementById('icon-favoritar').textContent = '⭐';
                this.classList.add('text-yellow-500');
            }
        });

        // Botão monitorar
        document.getElementById('btn-monitorar').addEventListener('click', function() {
            const razaoSocial = document.getElementById('razao-social').textContent;
            abrirModalMonitoramento(cnpjAtual, razaoSocial);
        });

        // Modal de monitoramento
        document.getElementById('modal-monitoramento-close').addEventListener('click', function() {
            document.getElementById('modal-monitoramento-backdrop').classList.add('hidden');
        });

        document.getElementById('modal-monitoramento-cancelar').addEventListener('click', function() {
            document.getElementById('modal-monitoramento-backdrop').classList.add('hidden');
        });

        document.getElementById('modal-monitoramento-backdrop').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        document.querySelectorAll('input[name="frequencia"]').forEach(radio => {
            radio.addEventListener('change', atualizarResumoModal);
        });

        document.getElementById('modal-monitoramento-ativar').addEventListener('click', function() {
            alert('Monitoramento ativado com sucesso!');
            document.getElementById('modal-monitoramento-backdrop').classList.add('hidden');
        });

        // Inicializar favoritos e histórico
        atualizarFavoritos();
        atualizarHistorico();
    });
})();
</script>

