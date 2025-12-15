<style>
    .upload-area {
        border: 2px dashed #cbd5e0;
        border-radius: 0.5rem;
        padding: 3rem;
        text-align: center;
        transition: all 0.3s ease;
        background: #f7fafc;
    }
    .upload-area.dragover {
        border-color: #4299e1;
        background: #ebf8ff;
    }
    .upload-area.has-files {
        border-color: #48bb78;
        background: #f0fff4;
    }
    .file-preview {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        background: white;
        border-radius: 0.25rem;
        margin-top: 0.5rem;
    }
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-pendente { background: #fed7d7; color: #c53030; }
    .status-processado { background: #bee3f8; color: #2c5282; }
    .status-aceito { background: #c6f6d5; color: #22543d; }
    .status-rejeitado { background: #fed7d7; color: #c53030; }
</style>

<!-- Hero Section -->
<section class="bg-gradient-to-br from-blue-50 to-indigo-100 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Importação e Classificação Automática de XMLs
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Transforme seus XMLs (NF-e/NFS-e) em lançamentos contábeis e fiscais em segundos. 
                O sistema utiliza CFOP e histórico de fornecedores para sugerir a conta contábil automaticamente.
            </p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Área Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Área de Upload -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Upload de Arquivos XML</h2>
                    
                    <div id="upload-area" class="upload-area">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p class="text-lg text-gray-600 mb-2">
                            Arraste e solte arquivos XML aqui
                        </p>
                        <p class="text-sm text-gray-500 mb-4">ou</p>
                        <label for="xml-files" class="btn-accent inline-block cursor-pointer px-6 py-2 rounded-lg">
                            Selecionar Arquivos
                            <input type="file" id="xml-files" name="xmls[]" multiple accept=".xml" class="hidden">
                        </label>
                        <p class="text-xs text-gray-500 mt-4">Formatos aceitos: XML (NF-e, NFS-e)</p>
                    </div>

                    <!-- Preview de Arquivos -->
                    <div id="files-preview" class="mt-4 hidden">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Arquivos Selecionados</h3>
                        <div id="files-list" class="space-y-2"></div>
                        <button id="btn-upload" class="mt-4 btn-accent px-6 py-2 rounded-lg">
                            Enviar Arquivos
                        </button>
                    </div>
                </div>

                <!-- Tabela de Processamento -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold text-gray-900">Documentos Processados</h2>
                        <div class="flex gap-2">
                            <select id="filter-status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="">Todos os Status</option>
                                <option value="pendente">Pendente</option>
                                <option value="processado">Processado</option>
                                <option value="aceito">Aceito</option>
                                <option value="rejeitado">Rejeitado</option>
                            </select>
                            <button id="btn-processar" class="btn-accent px-4 py-2 rounded-lg text-sm">
                                Processar Pendentes
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CFOP</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Natureza</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="documentos-table-body" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        Nenhum documento importado ainda
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="pagination" class="mt-4 flex justify-center"></div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Painel Explicativo -->
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Como Funciona</h3>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500">✓</span>
                            <span>Faça upload de múltiplos arquivos XML</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500">✓</span>
                            <span>O sistema extrai dados automaticamente</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500">✓</span>
                            <span>Regras inteligentes sugerem classificação</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500">✓</span>
                            <span>Revise e aceite ou ajuste manualmente</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500">✓</span>
                            <span>O sistema aprende com seus ajustes</span>
                        </li>
                    </ul>
                </div>

                <!-- Painel de Regras -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Regras de Classificação</h3>
                        <button id="btn-nova-regra" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            + Nova Regra
                        </button>
                    </div>
                    <div id="regras-list" class="space-y-2">
                        <p class="text-sm text-gray-500">Carregando regras...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal de Ajuste -->
<div id="modal-ajuste" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">Ajustar Classificação</h3>
            <button id="btn-fechar-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="form-ajuste">
            <input type="hidden" id="ajuste-lancamento-id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Natureza da Operação</label>
                    <input type="text" id="ajuste-natureza" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Conta Débito</label>
                    <input type="text" id="ajuste-conta-debito" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Conta Crédito</label>
                    <input type="text" id="ajuste-conta-credito" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="ajuste-salvar-regra" class="rounded border-gray-300">
                    <label for="ajuste-salvar-regra" class="ml-2 text-sm text-gray-700">Salvar como nova regra</label>
                </div>
            </div>
            <div class="mt-6 flex gap-2 justify-end">
                <button type="button" id="btn-cancelar-ajuste" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="btn-accent px-4 py-2 rounded-lg">
                    Salvar Ajuste
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Nova Regra -->
<div id="modal-nova-regra" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">Nova Regra de Classificação</h3>
            <button id="btn-fechar-modal-regra" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="form-nova-regra">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Regra</label>
                    <input type="text" name="nome_regra" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ Fornecedor (opcional)</label>
                        <input type="text" name="condicoes[cnpj_fornecedor]" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="00.000.000/0000-00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CFOP (opcional)</label>
                        <input type="text" name="condicoes[cfop]" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Regime Tributário (opcional)</label>
                    <select name="condicoes[regime_tributario]" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Selecione...</option>
                        <option value="mei">MEI</option>
                        <option value="simples_nacional">Simples Nacional</option>
                        <option value="lucro_presumido">Lucro Presumido</option>
                        <option value="lucro_real">Lucro Real</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Natureza da Operação</label>
                    <input type="text" name="acao[natureza_operacao]" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conta Débito</label>
                        <input type="text" name="acao[conta_debito]" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conta Crédito</label>
                        <input type="text" name="acao[conta_credito]" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prioridade (0-1000)</label>
                    <input type="number" name="prioridade" value="50" min="0" max="1000" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
            </div>
            <div class="mt-6 flex gap-2 justify-end">
                <button type="button" id="btn-cancelar-regra" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="btn-accent px-4 py-2 rounded-lg">
                    Criar Regra
                </button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/importacao_xml.js') }}"></script>
