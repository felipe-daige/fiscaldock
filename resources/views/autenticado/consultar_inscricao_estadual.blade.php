{{-- Consultar Inscrição Estadual --}}
<div class="min-h-screen bg-gray-50" id="consultar-ie-container">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">
                        Consultar Inscrição Estadual
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">
                        Verifique a situação cadastral de empresas na SEFAZ/SINTEGRA
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
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex flex-row gap-4 mb-6">
                    <button 
                        type="button" 
                        id="tab-consulta-unica" 
                        class="tab-consulta flex-1 flex items-center justify-center p-4 border-2 border-blue-600 rounded-lg cursor-pointer bg-blue-50 hover:bg-blue-100 transition-colors"
                        data-tab="unica"
                    >
                        <div class="text-center">
                            <div class="text-2xl mb-2">📝</div>
                            <div class="font-semibold text-gray-800 text-sm">Consulta Única</div>
                        </div>
                    </button>
                    <button 
                        type="button" 
                        id="tab-consulta-lote" 
                        class="tab-consulta flex-1 flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-gray-50 transition-colors"
                        data-tab="lote"
                    >
                        <div class="text-center">
                            <div class="text-2xl mb-2">📋</div>
                            <div class="font-semibold text-gray-800 text-sm">Consulta em Lote</div>
                        </div>
                    </button>
                </div>

                {{-- Modo: Consulta Única --}}
                <div id="content-unica" class="consulta-content">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="input-cnpj-unica" class="block text-sm font-medium text-gray-700 mb-2">CNPJ:</label>
                                <input 
                                    type="text" 
                                    id="input-cnpj-unica" 
                                    placeholder="00.000.000/0000-00"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>
                            <div>
                                <label for="select-uf-unica" class="block text-sm font-medium text-gray-700 mb-2">UF:</label>
                                <select 
                                    id="select-uf-unica" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Selecione o estado</option>
                                    <option value="AC">AC - Acre</option>
                                    <option value="AL">AL - Alagoas</option>
                                    <option value="AP">AP - Amapá</option>
                                    <option value="AM">AM - Amazonas</option>
                                    <option value="BA">BA - Bahia</option>
                                    <option value="CE">CE - Ceará</option>
                                    <option value="DF">DF - Distrito Federal</option>
                                    <option value="ES">ES - Espírito Santo</option>
                                    <option value="GO">GO - Goiás</option>
                                    <option value="MA">MA - Maranhão</option>
                                    <option value="MT">MT - Mato Grosso</option>
                                    <option value="MS">MS - Mato Grosso do Sul</option>
                                    <option value="MG">MG - Minas Gerais</option>
                                    <option value="PA">PA - Pará</option>
                                    <option value="PB">PB - Paraíba</option>
                                    <option value="PR">PR - Paraná</option>
                                    <option value="PE">PE - Pernambuco</option>
                                    <option value="PI">PI - Piauí</option>
                                    <option value="RJ">RJ - Rio de Janeiro</option>
                                    <option value="RN">RN - Rio Grande do Norte</option>
                                    <option value="RS">RS - Rio Grande do Sul</option>
                                    <option value="RO">RO - Rondônia</option>
                                    <option value="RR">RR - Roraima</option>
                                    <option value="SC">SC - Santa Catarina</option>
                                    <option value="SP">SP - São Paulo</option>
                                    <option value="SE">SE - Sergipe</option>
                                    <option value="TO">TO - Tocantins</option>
                                </select>
                            </div>
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

                {{-- Modo: Consulta em Lote --}}
                <div id="content-lote" class="consulta-content hidden">
                    <div class="space-y-4">
                        <div>
                            <label for="select-uf-lote" class="block text-sm font-medium text-gray-700 mb-2">UF para todas as consultas:</label>
                            <select 
                                id="select-uf-lote" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Selecione o estado</option>
                                <option value="AC">AC - Acre</option>
                                <option value="AL">AL - Alagoas</option>
                                <option value="AP">AP - Amapá</option>
                                <option value="AM">AM - Amazonas</option>
                                <option value="BA">BA - Bahia</option>
                                <option value="CE">CE - Ceará</option>
                                <option value="DF">DF - Distrito Federal</option>
                                <option value="ES">ES - Espírito Santo</option>
                                <option value="GO">GO - Goiás</option>
                                <option value="MA">MA - Maranhão</option>
                                <option value="MT">MT - Mato Grosso</option>
                                <option value="MS">MS - Mato Grosso do Sul</option>
                                <option value="MG">MG - Minas Gerais</option>
                                <option value="PA">PA - Pará</option>
                                <option value="PB">PB - Paraíba</option>
                                <option value="PR">PR - Paraná</option>
                                <option value="PE">PE - Pernambuco</option>
                                <option value="PI">PI - Piauí</option>
                                <option value="RJ">RJ - Rio de Janeiro</option>
                                <option value="RN">RN - Rio Grande do Norte</option>
                                <option value="RS">RS - Rio Grande do Sul</option>
                                <option value="RO">RO - Rondônia</option>
                                <option value="RR">RR - Roraima</option>
                                <option value="SC">SC - Santa Catarina</option>
                                <option value="SP">SP - São Paulo</option>
                                <option value="SE">SE - Sergipe</option>
                                <option value="TO">TO - Tocantins</option>
                            </select>
                        </div>

                        <div>
                            <label for="textarea-cnpjs" class="block text-sm font-medium text-gray-700 mb-2">Lista de CNPJs (máximo 100):</label>
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
                            <div id="status-badge" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold mb-3">
                                <span id="status-icon">🟢</span>
                                <span id="status-text">ATIVA</span>
                            </div>
                            <h3 id="razao-social" class="text-xl font-bold text-gray-800 mb-4"></h3>
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">CNPJ</span>
                                    <div class="font-semibold text-gray-800" id="result-cnpj"></div>
                                </div>
                                <div>
                                    <span class="text-gray-600">Inscrição</span>
                                    <div class="font-semibold text-gray-800" id="result-ie"></div>
                                </div>
                                <div>
                                    <span class="text-gray-600">UF</span>
                                    <div class="font-semibold text-gray-800" id="result-uf"></div>
                                </div>
                                <div>
                                    <span class="text-gray-600">Regime</span>
                                    <div class="font-semibold text-gray-800" id="result-regime"></div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Endereço</span>
                                    <div class="font-semibold text-gray-800" id="result-endereco"></div>
                                </div>
                                <div>
                                    <span class="text-gray-600">Cidade</span>
                                    <div class="font-semibold text-gray-800" id="result-cidade"></div>
                                </div>
                                <div>
                                    <span class="text-gray-600">CEP</span>
                                    <div class="font-semibold text-gray-800" id="result-cep"></div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Início Atividade</span>
                                    <div class="font-semibold text-gray-800" id="result-inicio"></div>
                                </div>
                                <div>
                                    <span class="text-gray-600">CNAE Principal</span>
                                    <div class="font-semibold text-gray-800" id="result-cnae"></div>
                                </div>
                                <div>
                                    <span class="text-gray-600">Última atualização</span>
                                    <div class="font-semibold text-gray-800" id="result-atualizacao"></div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex gap-3">
                                <button 
                                    type="button" 
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition-colors flex items-center gap-2"
                                >
                                    📄 Exportar PDF
                                </button>
                                <button 
                                    type="button" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors flex items-center gap-2"
                                >
                                    📊 Exportar Excel
                                </button>
                            </div>
                        </div>
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
                            <option value="ATIVA">Ativa</option>
                            <option value="SUSPENSA">Suspensa</option>
                            <option value="CANCELADA">Cancelada</option>
                            <option value="BAIXADA">Baixada</option>
                            <option value="NULA">Nula</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razão Social</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CNPJ</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IE</th>
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
            <div class="flex items-start gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">🔔 MONITORAR INSCRIÇÃO ESTADUAL</h3>
                </div>
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
                        <li>Status mudar (ex: Ativa → Suspensa)</li>
                        <li>Inscrição for cancelada ou baixada</li>
                        <li>Qualquer alteração cadastral</li>
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
    let currentTab = 'unica';
    let favoritos = new Set();
    let historico = [];
    let resultadosLote = [];
    let currentPage = 1;
    const itemsPerPage = 10;

    // Dados mockados
    const favoritosMockados = [
        { cnpj: '12.345.678/0001-90', nome: 'ACME Comércio', uf: 'MS', status: 'ATIVA' },
        { cnpj: '23.456.789/0001-01', nome: 'XYZ Indústria', uf: 'SP', status: 'ATIVA' },
        { cnpj: '34.567.890/0001-12', nome: 'Empresa ABC', uf: 'PR', status: 'CANCELADA' }
    ];

    const historicoMockado = [
        { cnpj: '12.345.678/0001-90', uf: 'MS', status: 'ATIVA', tempo: 'há 5 min' },
        { cnpj: '23.456.789/0001-01', uf: 'SP', status: 'CANCELADA', tempo: 'há 2 horas' },
        { cnpj: '34.567.890/0001-12', uf: 'PR', status: 'ATIVA', tempo: 'ontem' },
        { cnpj: '45.678.901/0001-23', uf: 'RJ', status: 'SUSPENSA', tempo: '3 dias atrás' },
        { cnpj: '56.789.012/0001-34', uf: 'MG', status: 'ATIVA', tempo: '5 dias atrás' }
    ];

    const resultadoMockado = {
        status: 'ATIVA',
        razaoSocial: 'ACME COMÉRCIO LTDA',
        cnpj: '12.345.678/0001-90',
        ie: '28.361.489-4',
        uf: 'Mato Grosso do Sul (MS)',
        regime: 'Normal',
        endereco: 'Rua das Flores, 123',
        cidade: 'Dourados - MS',
        cep: '79800-000',
        inicioAtividade: '15/03/2018',
        cnae: '4711-3/02 - Comércio varejista de mercadorias em geral',
        ultimaAtualizacao: '02/01/2025'
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

    // Função para alternar tabs
    function switchTab(tabName) {
        currentTab = tabName;
        
        // Atualizar botões de tab
        document.querySelectorAll('.tab-consulta').forEach(btn => {
            if (btn.dataset.tab === tabName) {
                btn.classList.remove('border-gray-300', 'hover:border-blue-400', 'hover:bg-gray-50');
                btn.classList.add('border-blue-600', 'bg-blue-50', 'hover:bg-blue-100');
            } else {
                btn.classList.remove('border-blue-600', 'bg-blue-50', 'hover:bg-blue-100');
                btn.classList.add('border-gray-300', 'hover:border-blue-400', 'hover:bg-gray-50');
            }
        });

        // Mostrar/esconder conteúdo
        document.querySelectorAll('.consulta-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById(`content-${tabName}`).classList.remove('hidden');
    }

    // Função para atualizar botão de consulta única
    function atualizarBtnConsultaUnica() {
        const cnpj = document.getElementById('input-cnpj-unica').value;
        const uf = document.getElementById('select-uf-unica').value;
        const btn = document.getElementById('btn-consultar-unica');
        
        if (cnpj && uf && validarCNPJ(cnpj)) {
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
        const uf = document.getElementById('select-uf-lote').value;
        
        if (count > 0 && uf) {
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
            'CANCELADA': 'bg-red-100 text-red-800 border-red-500',
            'BAIXADA': 'bg-gray-100 text-gray-800 border-gray-500',
            'NULA': 'bg-purple-100 text-purple-800 border-purple-500'
        };
        return classes[status] || classes['ATIVA'];
    }

    function getStatusIcon(status) {
        const icons = {
            'ATIVA': '🟢',
            'SUSPENSA': '🟡',
            'CANCELADA': '🔴',
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
        
        // Atualizar status
        const statusClasses = getStatusClasses(dados.status);
        statusBadge.className = `inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold mb-3 border ${statusClasses}`;
        statusIcon.textContent = getStatusIcon(dados.status);
        statusText.textContent = dados.status;
        
        // Preencher dados
        document.getElementById('razao-social').textContent = dados.razaoSocial;
        document.getElementById('result-cnpj').textContent = dados.cnpj;
        document.getElementById('result-ie').textContent = dados.ie;
        document.getElementById('result-uf').textContent = dados.uf;
        document.getElementById('result-regime').textContent = dados.regime;
        document.getElementById('result-endereco').textContent = dados.endereco;
        document.getElementById('result-cidade').textContent = dados.cidade;
        document.getElementById('result-cep').textContent = dados.cep;
        document.getElementById('result-inicio').textContent = dados.inicioAtividade;
        document.getElementById('result-cnae').textContent = dados.cnae;
        document.getElementById('result-atualizacao').textContent = dados.ultimaAtualizacao;
        
        // Verificar se está nos favoritos
        const iconFavoritar = document.getElementById('icon-favoritar');
        if (favoritos.has(dados.cnpj)) {
            iconFavoritar.textContent = '⭐';
            iconFavoritar.parentElement.classList.add('text-yellow-500');
        } else {
            iconFavoritar.textContent = '☆';
            iconFavoritar.parentElement.classList.remove('text-yellow-500');
        }
        
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
        adicionarAoHistorico(dados.cnpj, dados.uf.split('(')[1].replace(')', ''), dados.status);
        atualizarHistorico();
    }

    // Função para adicionar ao histórico
    function adicionarAoHistorico(cnpj, uf, status) {
        historico.unshift({
            cnpj: cnpj,
            uf: uf,
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
        const ultimos = historico.slice(0, 5);
        
        if (ultimos.length === 0 && historicoMockado.length > 0) {
            // Usar dados mockados se não houver histórico real
            ultimos.push(...historicoMockado);
        }
        
        container.innerHTML = ultimos.map(item => {
            const statusIcon = getStatusIcon(item.status);
            return `
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3 flex-1 text-sm">
                        <span class="font-mono text-gray-600">${item.cnpj}</span>
                        <span class="text-gray-500">${item.uf}</span>
                        <span>${statusIcon}</span>
                        <span class="text-gray-600">${item.status}</span>
                        <span class="text-gray-400">${item.tempo}</span>
                    </div>
                    <button 
                        type="button" 
                        class="p-1 text-blue-600 hover:text-blue-700"
                        onclick="reconsultarIE('${item.cnpj}', '${item.uf}')"
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
        const lista = favoritosMockado.map(item => {
            const statusIcon = getStatusIcon(item.status);
            const isFavorito = favoritos.has(item.cnpj);
            if (!isFavorito && favoritos.size === 0) {
                // Mostrar todos se não houver favoritos selecionados
            }
            return `
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3 flex-1 text-sm">
                        <span>${statusIcon}</span>
                        <span class="font-semibold text-gray-800">${item.nome}</span>
                        <span class="font-mono text-gray-600">${item.cnpj}</span>
                        <span class="text-gray-500">${item.uf}</span>
                    </div>
                    <button 
                        type="button" 
                        class="p-1 text-blue-600 hover:text-blue-700"
                        onclick="reconsultarIE('${item.cnpj}', '${item.uf}')"
                    >
                        🔍
                    </button>
                </div>
            `;
        }).join('');
        container.innerHTML = lista;
    }

    // Função para gerar resultados em lote (mockado)
    function gerarResultadosLote(cnpjs, uf) {
        const nomes = ['ACME Comércio', 'XYZ Indústria', 'Empresa ABC', 'Comercial 123', 'Distribuidora', 'Importadora', 'Exportadora', 'Varejista', 'Atacadista', 'Serviços'];
        const statuses = ['ATIVA', 'ATIVA', 'CANCELADA', 'SUSPENSA', 'ATIVA', 'ATIVA', 'BAIXADA', 'ATIVA', 'NULA', 'ATIVA'];
        
        return cnpjs.map((cnpj, index) => {
            const cleanCNPJ = cnpj.replace(/\D/g, '');
            const ie = `${Math.floor(Math.random() * 90 + 10)}.${Math.floor(Math.random() * 900 + 100)}.${Math.floor(Math.random() * 900 + 100)}-${Math.floor(Math.random() * 90 + 10)}`;
            return {
                cnpj: cnpj,
                razaoSocial: nomes[index % nomes.length],
                ie: ie,
                status: statuses[index % statuses.length],
                uf: uf
            };
        });
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
                r.cnpj.includes(busca)
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
        
        // Renderizar linhas
        tbody.innerHTML = resultadosPagina.map(item => {
            const statusIcon = getStatusIcon(item.status);
            return `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="text-lg">${statusIcon}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-800">${item.razaoSocial}</td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-600">${item.cnpj}</td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-600">${item.ie}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="relative inline-block">
                            <button 
                                type="button" 
                                class="p-1 text-gray-400 hover:text-gray-600"
                                onclick="mostrarMenuAcoes(event, '${item.cnpj}', '${item.uf}')"
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

    window.reconsultarIE = function(cnpj, uf) {
        // Preencher formulário de consulta única
        switchTab('unica');
        document.getElementById('input-cnpj-unica').value = cnpj;
        document.getElementById('select-uf-unica').value = uf;
        atualizarBtnConsultaUnica();
        
        // Simular consulta
        setTimeout(() => {
            const dados = { ...resultadoMockado, cnpj: cnpj, uf: `${uf} - ${getNomeEstado(uf)}` };
            exibirResultadoUnica(dados);
        }, 500);
    };

    window.mostrarMenuAcoes = function(event, cnpj, uf) {
        // Criar menu dropdown simples (pode ser melhorado)
        alert(`Ações para ${cnpj}\n\n- Ver detalhes\n- Favoritar\n- Monitorar`);
    };

    function getNomeEstado(sigla) {
        const estados = {
            'AC': 'Acre', 'AL': 'Alagoas', 'AP': 'Amapá', 'AM': 'Amazonas',
            'BA': 'Bahia', 'CE': 'Ceará', 'DF': 'Distrito Federal', 'ES': 'Espírito Santo',
            'GO': 'Goiás', 'MA': 'Maranhão', 'MT': 'Mato Grosso', 'MS': 'Mato Grosso do Sul',
            'MG': 'Minas Gerais', 'PA': 'Pará', 'PB': 'Paraíba', 'PR': 'Paraná',
            'PE': 'Pernambuco', 'PI': 'Piauí', 'RJ': 'Rio de Janeiro', 'RN': 'Rio Grande do Norte',
            'RS': 'Rio Grande do Sul', 'RO': 'Rondônia', 'RR': 'Roraima', 'SC': 'Santa Catarina',
            'SP': 'São Paulo', 'SE': 'Sergipe', 'TO': 'Tocantins'
        };
        return estados[sigla] || sigla;
    }

    // Função para abrir modal de monitoramento
    function abrirModalMonitoramento(cnpj, razaoSocial, ie, uf) {
        const modal = document.getElementById('modal-monitoramento-backdrop');
        const info = document.getElementById('modal-empresa-info');
        
        info.innerHTML = `
            <div class="font-semibold text-gray-800">${razaoSocial}</div>
            <div class="text-gray-600">CNPJ: ${cnpj} | IE: ${ie} | ${uf}</div>
        `;
        
        // Atualizar resumo baseado na frequência selecionada
        atualizarResumoModal();
        
        modal.classList.remove('hidden');
    }

    // Função para atualizar resumo do modal
    function atualizarResumoModal() {
        const frequencia = document.querySelector('input[name="frequencia"]:checked').value;
        const custoMensal = frequencia === 'semanal' ? 3 : 24;
        const saldo = 147;
        const duracao = Math.floor(saldo / custoMensal);
        
        document.getElementById('modal-custo-mensal').textContent = `${custoMensal} créditos`;
        document.getElementById('modal-saldo-atual').textContent = `${saldo} créditos`;
        document.getElementById('modal-duracao').textContent = `~${duracao} meses`;
    }

    // Função para fechar modal
    function fecharModalMonitoramento() {
        document.getElementById('modal-monitoramento-backdrop').classList.add('hidden');
    }

    // Inicialização
    function init() {
        // Event listeners para tabs
        document.querySelectorAll('.tab-consulta').forEach(btn => {
            btn.addEventListener('click', function() {
                switchTab(this.dataset.tab);
            });
        });

        // Máscara de CNPJ - Consulta Única
        const inputCNPJUnica = document.getElementById('input-cnpj-unica');
        if (inputCNPJUnica) {
            inputCNPJUnica.addEventListener('input', function() {
                this.value = maskCNPJ(this.value);
                atualizarBtnConsultaUnica();
            });
        }

        // Validação de UF - Consulta Única
        const selectUFUnica = document.getElementById('select-uf-unica');
        if (selectUFUnica) {
            selectUFUnica.addEventListener('change', atualizarBtnConsultaUnica);
        }

        // Botão consultar única
        const btnConsultarUnica = document.getElementById('btn-consultar-unica');
        if (btnConsultarUnica) {
            btnConsultarUnica.addEventListener('click', function() {
                const cnpj = inputCNPJUnica.value;
                const uf = selectUFUnica.value;
                const nomeUF = getNomeEstado(uf);
                
                // Simular consulta
                const dados = {
                    ...resultadoMockado,
                    cnpj: cnpj,
                    uf: `${nomeUF} (${uf})`
                };
                exibirResultadoUnica(dados);
            });
        }

        // Textarea de CNPJs em lote
        const textareaCNPJs = document.getElementById('textarea-cnpjs');
        if (textareaCNPJs) {
            textareaCNPJs.addEventListener('input', atualizarContadorLote);
        }

        // Upload de arquivo
        const fileUpload = document.getElementById('file-upload');
        if (fileUpload) {
            fileUpload.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        textareaCNPJs.value = e.target.result;
                        atualizarContadorLote();
                    };
                    reader.readAsText(file);
                }
            });
        }

        // Select UF - Lote
        const selectUFLote = document.getElementById('select-uf-lote');
        if (selectUFLote) {
            selectUFLote.addEventListener('change', atualizarContadorLote);
        }

        // Botão consultar lote
        const btnConsultarLote = document.getElementById('btn-consultar-lote');
        if (btnConsultarLote) {
            btnConsultarLote.addEventListener('click', function() {
                const texto = textareaCNPJs.value;
                const cnpjs = extrairCNPJs(texto).slice(0, 100);
                const uf = selectUFLote.value;
                
                if (cnpjs.length > 0 && uf) {
                    resultadosLote = gerarResultadosLote(cnpjs, uf);
                    currentPage = 1;
                    renderizarTabelaResultados();
                    
                    // Mostrar tabela de resultados
                    document.getElementById('resultado-lote').classList.remove('hidden');
                    
                    // Adicionar ao histórico
                    cnpjs.forEach(cnpj => {
                        adicionarAoHistorico(cnpj, uf, 'ATIVA');
                    });
                    atualizarHistorico();
                }
            });
        }

        // Filtro e busca na tabela
        const filtroStatus = document.getElementById('filtro-status');
        if (filtroStatus) {
            filtroStatus.addEventListener('change', renderizarTabelaResultados);
        }

        const buscaTabela = document.getElementById('busca-tabela');
        if (buscaTabela) {
            buscaTabela.addEventListener('input', renderizarTabelaResultados);
        }

        // Botão favoritar
        const btnFavoritar = document.getElementById('btn-favoritar');
        if (btnFavoritar) {
            btnFavoritar.addEventListener('click', function() {
                const cnpj = document.getElementById('result-cnpj').textContent;
                const icon = document.getElementById('icon-favoritar');
                
                if (favoritos.has(cnpj)) {
                    favoritos.delete(cnpj);
                    icon.textContent = '☆';
                    this.classList.remove('text-yellow-500');
                } else {
                    favoritos.add(cnpj);
                    icon.textContent = '⭐';
                    this.classList.add('text-yellow-500');
                }
                atualizarFavoritos();
            });
        }

        // Botão monitorar
        const btnMonitorar = document.getElementById('btn-monitorar');
        if (btnMonitorar) {
            btnMonitorar.addEventListener('click', function() {
                const cnpj = document.getElementById('result-cnpj').textContent;
                const razaoSocial = document.getElementById('razao-social').textContent;
                const ie = document.getElementById('result-ie').textContent;
                const uf = document.getElementById('result-uf').textContent;
                abrirModalMonitoramento(cnpj, razaoSocial, ie, uf);
            });
        }

        // Modal de monitoramento
        const modalClose = document.getElementById('modal-monitoramento-close');
        if (modalClose) {
            modalClose.addEventListener('click', fecharModalMonitoramento);
        }

        const modalCancelar = document.getElementById('modal-monitoramento-cancelar');
        if (modalCancelar) {
            modalCancelar.addEventListener('click', fecharModalMonitoramento);
        }

        const modalAtivar = document.getElementById('modal-monitoramento-ativar');
        if (modalAtivar) {
            modalAtivar.addEventListener('click', function() {
                alert('Monitoramento ativado com sucesso!');
                fecharModalMonitoramento();
            });
        }

        // Frequência de monitoramento
        document.querySelectorAll('input[name="frequencia"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Atualizar visual dos cards
                document.querySelectorAll('input[name="frequencia"]').forEach(r => {
                    const label = r.closest('label');
                    if (r.checked) {
                        label.classList.remove('border-gray-300', 'hover:border-blue-400', 'hover:bg-gray-50');
                        label.classList.add('border-blue-600', 'bg-blue-50', 'hover:bg-blue-100');
                    } else {
                        label.classList.remove('border-blue-600', 'bg-blue-50', 'hover:bg-blue-100');
                        label.classList.add('border-gray-300', 'hover:border-blue-400', 'hover:bg-gray-50');
                    }
                });
                atualizarResumoModal();
            });
        });

        // Fechar modal ao clicar no backdrop
        const modalBackdrop = document.getElementById('modal-monitoramento-backdrop');
        if (modalBackdrop) {
            modalBackdrop.addEventListener('click', function(e) {
                if (e.target === this) {
                    fecharModalMonitoramento();
                }
            });
        }

        // Inicializar favoritos e histórico
        atualizarFavoritos();
        atualizarHistorico();
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

