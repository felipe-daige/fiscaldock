<style>
    /* Estilos existentes */
    .upload-area {
        border: 2px dashed #cbd5e0;
        border-radius: 0.5rem;
        padding: 3rem;
        text-align: center;
        transition: all 0.3s ease;
        background: #f7fafc;
        position: relative;
        overflow: hidden;
    }
    .upload-area.dragover {
        border-color: #4299e1;
        background: #ebf8ff;
        transform: scale(1.02);
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

    /* Novos estilos para landing page */
    .hero-gradient {
        background: linear-gradient(135deg, #0b1f3a 0%, #1e4fa0 50%, #133a73 100%);
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .checkmark-item {
        opacity: 0;
        transform: translateX(-20px);
        transition: all 0.5s ease;
    }

    .checkmark-item.show {
        opacity: 1;
        transform: translateX(0);
    }

    .time-counter {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1e4fa0;
        transition: all 0.3s ease;
    }

    .time-counter.animating {
        transform: scale(1.1);
    }

    .cloud-icon {
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    .file-absorb {
        animation: absorb 1s ease-out forwards;
    }

    @keyframes absorb {
        0% {
            transform: scale(1) translateY(0);
            opacity: 1;
        }
        50% {
            transform: scale(0.8) translateY(-20px);
            opacity: 0.7;
        }
        100% {
            transform: scale(0.3) translateY(-40px);
            opacity: 0;
        }
    }

    .feature-card {
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .section-fade-in {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease;
    }

    .section-fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .demo-area {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-radius: 1rem;
        padding: 3rem;
        position: relative;
    }

    .process-steps {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 2rem;
        margin: 2rem 0;
        flex-wrap: wrap;
    }

    @media (max-width: 640px) {
        .process-steps {
            gap: 1rem;
        }
        
        .time-counter {
            font-size: 1.75rem;
        }
        
        .hero-gradient h1 {
            font-size: 2rem;
        }
        
        .hero-gradient p {
            font-size: 1rem;
        }
    }

    .process-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        opacity: 0;
    }

    .process-step.show {
        opacity: 1;
        animation: stepAppear 0.5s ease-out forwards;
    }

    @keyframes stepAppear {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .step-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #48bb78;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
    }

    .step-label {
        font-size: 0.875rem;
        color: #374151;
        font-weight: 600;
    }
</style>

<!-- Hero Section -->
<section class="hero-gradient py-12 md:py-20 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in-up">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 md:mb-6">
                O Fim da Digitação Manual de Notas Fiscais
            </h1>
            <p class="text-lg sm:text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-6 md:mb-8 px-4">
                Arraste seus XMLs e veja a mágica acontecer. Classificação fiscal e contábil em segundos.
            </p>
        </div>
    </div>
</section>

<!-- Seção 2: Demonstração Visual Interativa -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="demo-area">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Veja Como Funciona</h2>
                <p class="text-lg text-gray-600">Arraste seus arquivos XML e acompanhe o processamento em tempo real</p>
            </div>

            <!-- Área de Upload Melhorada -->
            <div id="upload-area" class="upload-area max-w-2xl mx-auto">
                <div class="cloud-icon mb-4">
                    <svg class="mx-auto h-16 w-16 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                </div>
                <p class="text-xl text-gray-700 mb-2 font-semibold">
                    Arraste e solte arquivos XML aqui
                </p>
                <p class="text-sm text-gray-500 mb-4">ou</p>
                <label for="xml-files" class="btn-accent inline-block cursor-pointer px-8 py-3 rounded-lg text-lg font-semibold">
                    Selecionar Arquivos
                    <input type="file" id="xml-files" name="xmls[]" multiple accept=".xml" class="hidden">
                </label>
                <p class="text-xs text-gray-500 mt-4">Formatos aceitos: XML (NF-e, NFS-e, CT-e)</p>
            </div>

            <!-- Process Steps (aparecem quando arquivo é solto) -->
            <div id="process-steps" class="process-steps hidden">
                <div class="process-step" id="step-classificado">
                    <div class="step-icon">✓</div>
                    <span class="step-label">Classificado</span>
                </div>
                <div class="process-step" id="step-lancado">
                    <div class="step-icon">✓</div>
                    <span class="step-label">Lançado</span>
                </div>
                <div class="process-step" id="step-validado">
                    <div class="step-icon">✓</div>
                    <span class="step-label">Validado</span>
                </div>
            </div>

            <!-- Contador de Tempo -->
            <div id="time-counter-container" class="text-center mt-8 hidden">
                <p class="text-gray-600 mb-2">Tempo estimado manual:</p>
                <div class="time-counter" id="time-manual">4 horas</div>
                <p class="text-gray-600 mt-4 mb-2">Tempo Rubi:</p>
                <div class="time-counter text-green-600" id="time-rubi">30 segundos</div>
            </div>

            <!-- Preview de Arquivos -->
            <div id="files-preview" class="mt-6 hidden max-w-2xl mx-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Arquivos Selecionados</h3>
                <div id="files-list" class="space-y-2"></div>
                <button id="btn-upload" class="mt-4 btn-accent px-8 py-3 rounded-lg text-lg font-semibold w-full">
                    Enviar Arquivos
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Seção 3: O que Fazemos -->
<section class="py-16 bg-gray-50 section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">O que Fazemos</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Eliminamos completamente a necessidade de digitação manual de notas fiscais. 
                Nosso sistema processa automaticamente seus documentos XML e transforma-os em 
                lançamentos contábeis e fiscais prontos para uso.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div class="bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-4xl mb-4">📄</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">NF-e</h3>
                <p class="text-gray-600">Notas Fiscais Eletrônicas</p>
            </div>
            <div class="bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-4xl mb-4">📋</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">NFS-e</h3>
                <p class="text-gray-600">Notas Fiscais de Serviços</p>
            </div>
            <div class="bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-4xl mb-4">🚚</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">CT-e</h3>
                <p class="text-gray-600">Conhecimentos de Transporte</p>
            </div>
        </div>

        <div class="bg-white rounded-lg p-8 shadow-sm max-w-4xl mx-auto">
            <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center">Processo Automático Simplificado</h3>
            <div class="space-y-4 text-gray-700">
                <div class="flex items-start gap-3">
                    <span class="text-green-500 text-2xl font-bold">1.</span>
                    <p class="text-lg">Você faz upload dos arquivos XML (pode ser em lote)</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-green-500 text-2xl font-bold">2.</span>
                    <p class="text-lg">O sistema extrai automaticamente todos os dados relevantes</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-green-500 text-2xl font-bold">3.</span>
                    <p class="text-lg">Classificação inteligente baseada em CFOP e histórico de fornecedores</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-green-500 text-2xl font-bold">4.</span>
                    <p class="text-lg">Lançamentos contábeis e fiscais gerados automaticamente</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-green-500 text-2xl font-bold">5.</span>
                    <p class="text-lg">Você apenas revisa e aprova - sem digitação!</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção 4: Automação Inteligente -->
<section class="py-16 bg-white section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Automação Inteligente</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Tecnologia que trabalha para você, aprendendo e melhorando a cada uso
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            <!-- Card 1: Classificação Automática -->
            <div class="feature-card bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-6 md:p-8 shadow-sm">
                <div class="text-4xl md:text-5xl mb-4">🤖</div>
                <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 md:mb-4">Classificação Automática</h3>
                <p class="text-gray-700 mb-4 text-sm md:text-base">
                    O sistema analisa o CFOP e o histórico de transações com cada fornecedor 
                    para sugerir automaticamente a conta contábil correta.
                </p>
                <p class="text-base md:text-lg font-semibold text-blue-600">
                    Sem você precisar tocar no teclado
                </p>
            </div>

            <!-- Card 2: Aprendizado Contínuo -->
            <div class="feature-card bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-6 md:p-8 shadow-sm">
                <div class="text-4xl md:text-5xl mb-4">🧠</div>
                <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 md:mb-4">Aprendizado Contínuo</h3>
                <p class="text-gray-700 mb-4 text-sm md:text-base">
                    Quando você faz um ajuste manual, o sistema aprende com essa decisão. 
                    Na próxima vez que encontrar uma situação similar, já sugerirá a classificação correta.
                </p>
                <p class="text-base md:text-lg font-semibold text-green-600">
                    Quanto mais você usa, mais autônomo ele fica
                </p>
            </div>

            <!-- Card 3: Importação em Lote -->
            <div class="feature-card bg-gradient-to-br from-yellow-50 to-amber-50 rounded-lg p-6 md:p-8 shadow-sm">
                <div class="text-4xl md:text-5xl mb-4">⚡</div>
                <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 md:mb-4">Importação em Lote</h3>
                <p class="text-gray-700 mb-4 text-sm md:text-base">
                    Processe centenas ou milhares de notas fiscais de uma só vez. 
                    O sistema trabalha em paralelo, otimizando o tempo de processamento.
                </p>
                <p class="text-base md:text-lg font-semibold text-amber-600">
                    O que levava dias de digitação agora é resolvido enquanto você toma um café
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Seção 5: Funcionalidade Técnica (Menor destaque) -->
<section class="py-12 bg-gray-50 section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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

        <!-- Painel de Regras (Secundário) -->
        <div class="mt-6 bg-white rounded-lg shadow-sm p-6">
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
