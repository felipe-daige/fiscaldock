{{-- Consultar Cliente - Análise de Risco --}}
<div class="min-h-screen bg-gray-50" id="consultar-cliente-container">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">
                    Consultar Cliente
                </h1>
                <p class="text-xs text-gray-500 mt-1">
                    Realize análises de risco consultando CNPJ ou CPF
                </p>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="space-y-6">
            {{-- Seção: Análise de Risco --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-6">Análise de Risco</h2>

                {{-- Seleção do tipo de consulta --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Consulta:</label>
                    <div class="flex flex-row gap-4">
                        {{-- Card CNPJ --}}
                        <label id="card-tipo-cnpj" class="flex-1 flex items-center justify-center p-4 border-2 border-blue-600 rounded-lg cursor-pointer bg-blue-50 hover:bg-blue-100 transition-colors">
                            <input type="radio" name="tipo-consulta" value="cnpj" checked class="sr-only" id="radio-cnpj">
                            <div class="text-center">
                                <div class="text-2xl mb-2">🏢</div>
                                <div class="font-semibold text-gray-800 text-sm">CNPJ</div>
                                <div class="text-xs text-gray-600">Empresa</div>
                            </div>
                        </label>

                        {{-- Card CPF --}}
                        <label id="card-tipo-cpf" class="flex-1 flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-gray-50 transition-colors">
                            <input type="radio" name="tipo-consulta" value="cpf" class="sr-only" id="radio-cpf">
                            <div class="text-center">
                                <div class="text-2xl mb-2">👤</div>
                                <div class="font-semibold text-gray-800 text-sm">CPF</div>
                                <div class="text-xs text-gray-600">Pessoa Física</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Campos de entrada condicionais --}}
                <div class="mb-6">
                    {{-- Campos CNPJ --}}
                    <div id="campos-cnpj" class="space-y-4">
                        {{-- Seletor de Cliente --}}
                        <div>
                            <label for="select-cliente" class="block text-sm font-medium text-gray-700 mb-2">Selecionar Cliente Cadastrado</label>
                            <select 
                                id="select-cliente" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">-- Selecione um cliente ou digite manualmente --</option>
                                @foreach($clientes ?? [] as $cliente)
                                    <option value="{{ $cliente->documento }}" data-nome="{{ $cliente->nome }}">
                                        {{ $cliente->nome }} - {{ $cliente->documento_formatado }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Indicador de Cliente Selecionado --}}
                        <div id="cliente-selecionado-info" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-blue-900">Cliente selecionado:</p>
                                    <p class="text-sm text-blue-700" id="cliente-selecionado-nome"></p>
                                    <p class="text-xs text-blue-600 mt-1" id="cliente-selecionado-cnpj"></p>
                                </div>
                                <button 
                                    type="button" 
                                    id="btn-limpar-cliente"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    Limpar
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="input-cnpj" class="block text-sm font-medium text-gray-700 mb-2">CNPJ</label>
                            <input 
                                type="text" 
                                id="input-cnpj" 
                                placeholder="00.000.000/0000-00"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                    </div>

                    {{-- Campos CPF --}}
                    <div id="campos-cpf" class="space-y-4 hidden">
                        <div>
                            <label for="input-cpf" class="block text-sm font-medium text-gray-700 mb-2">CPF</label>
                            <input 
                                type="text" 
                                id="input-cpf" 
                                placeholder="000.000.000-00"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                        <div>
                            <label for="input-data-nascimento" class="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento</label>
                            <input 
                                type="text" 
                                id="input-data-nascimento" 
                                placeholder="00/00/0000"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                    </div>
                </div>

                {{-- Tabs de Planos --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">Escolha o tipo de relatório:</h3>
                    
                    {{-- Tabs como Cards --}}
                    <div class="mb-6">
                        <div class="flex flex-row gap-3 md:gap-4" id="tabs-container">
                            {{-- Card Rápida --}}
                            <button type="button" class="tab-btn flex-1 bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 hover:border-gray-300 text-gray-600 transition-colors flex flex-col items-center justify-center text-center relative" data-tab="rapida">
                                <div class="text-3xl mb-2">🔍</div>
                                <span class="font-semibold text-sm mb-1">Rápida</span>
                                <span class="text-xs text-gray-500" data-price-rapida>0 créditos</span>
                            </button>

                            {{-- Card Básico --}}
                            <button type="button" class="tab-btn flex-1 bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 hover:border-gray-300 text-gray-600 transition-colors flex flex-col items-center justify-center text-center relative" data-tab="basico">
                                <div class="text-3xl mb-2">📋</div>
                                <span class="font-semibold text-sm mb-1">Básico</span>
                                <span class="text-xs text-gray-500" data-price-basico-cnpj>15 créditos</span>
                                <span class="text-xs text-gray-500 hidden" data-price-basico-cpf>10 créditos</span>
                            </button>

                            {{-- Card Completo --}}
                            <button type="button" class="tab-btn flex-1 bg-blue-50 border-2 border-blue-500 rounded-lg p-4 shadow-sm text-blue-700 transition-colors flex flex-col items-center justify-center text-center relative" data-tab="completo">
                                <span class="absolute -top-2 -right-2 bg-white text-yellow-600 text-xs px-2 py-0.5 rounded-full font-semibold border-2 border-yellow-500 shadow-sm">Favorito</span>
                                <div class="text-3xl mb-2">📊</div>
                                <span class="font-semibold text-sm mb-1">Completo</span>
                                <span class="text-xs text-blue-600" data-price-completo-cnpj>30 créditos</span>
                                <span class="text-xs text-blue-600 hidden" data-price-completo-cpf>20 créditos</span>
                            </button>

                            {{-- Card Monitor --}}
                            <button type="button" class="tab-btn flex-1 bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50 hover:border-gray-300 text-gray-600 transition-colors flex flex-col items-center justify-center text-center relative" data-tab="monitor">
                                <div class="text-3xl mb-2">🔔</div>
                                <span class="font-semibold text-sm mb-1">Monitor</span>
                                <span class="text-xs text-gray-500" data-price-monitor-cnpj>50 créditos/mês</span>
                                <span class="text-xs text-gray-500 hidden" data-price-monitor-cpf>30 créditos/mês</span>
                            </button>
                        </div>
                    </div>

                    {{-- Área de Detalhes --}}
                    <div class="bg-gray-50 rounded-xl p-6 mb-6 border border-gray-100">
                        {{-- Conteúdo Tab Rápida --}}
                        <div id="content-rapida" class="hidden">
                            <div class="flex items-center mb-4">
                                <span class="text-2xl mr-2">🔍</span>
                                <h4 class="text-lg font-bold text-gray-800">Consulta Rápida</h4>
                            </div>
                            <div id="content-rapida-cnpj" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Situação Cadastral CNPJ</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Quadro Societário (QSA)</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Monitoramento mensal automático</span>
                                </div>
                            </div>
                            <div id="content-rapida-cpf" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 hidden">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Situação CPF</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Nome completo</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Monitoramento mensal automático</span>
                                </div>
                            </div>
                        </div>

                        {{-- Conteúdo Tab Básico --}}
                        <div id="content-basico" class="hidden">
                            <div class="flex items-center mb-4">
                                <span class="text-2xl mr-2">📋</span>
                                <h4 class="text-lg font-bold text-gray-800">Relatório Básico</h4>
                            </div>
                            <div id="content-basico-cnpj" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Situação Cadastral</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Quadro Societário</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Simples Nacional</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Inscrição Estadual</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Listas Restritivas (CEIS, CNEP, Trabalho Escravo)</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Score de Risco (0-100)</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Relatório PDF</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Salvo no histórico</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Monitoramento mensal automático</span>
                                </div>
                            </div>
                            <div id="content-basico-cpf" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 hidden">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Situação CPF</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Dados cadastrais</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Participação em empresas (QSA reverso)</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Verificação de óbito</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Score de Risco (0-100)</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Relatório PDF</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Salvo no histórico</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Monitoramento mensal automático</span>
                                </div>
                            </div>
                        </div>

                        {{-- Conteúdo Tab Completo --}}
                        <div id="content-completo">
                            <div class="flex items-center mb-4">
                                <span class="text-2xl mr-2">📊</span>
                                <h4 class="text-lg font-bold text-gray-800">Relatório Completo</h4>
                            </div>
                            <div id="content-completo-cnpj" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Tudo do Básico +</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">CND Federal</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">CND Estadual</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">CND Municipal</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">CNDT (Trabalhista)</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">CRF (FGTS)</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Protestos em Cartórios</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Análise detalhada</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Recomendações automáticas</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Monitoramento mensal automático</span>
                                </div>
                            </div>
                            <div id="content-completo-cpf" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 hidden">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Tudo do Básico +</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Protestos em Cartórios</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Processos judiciais</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Benefícios INSS</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Análise detalhada</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Recomendações automáticas</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Monitoramento mensal automático</span>
                                </div>
                            </div>
                        </div>

                        {{-- Conteúdo Tab Monitoramento --}}
                        <div id="content-monitor" class="hidden">
                            <div class="flex items-center mb-4">
                                <span class="text-2xl mr-2">🔔</span>
                                <h4 class="text-lg font-bold text-gray-800">Monitoramento</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Tudo do Completo +</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Reconsulta automática semanal (mais frequente)</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Alerta por e-mail se situação mudar</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-800 text-base">Histórico de alterações</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botão de Ação --}}
                    <button type="button" id="action-button" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors">
                        Gerar Relatório
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

    // Variáveis globais
    let currentTab = 'completo';
    let currentTipo = 'cnpj';

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

    // Função para aplicar máscara de data
    function maskData(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{2})(\d)/, '$1/$2')
            .replace(/(\d{2})\/(\d{2})(\d)/, '$1/$2/$3')
            .substring(0, 10);
    }

    // Função para alternar tabs
    function switchTab(tabName) {
        currentTab = tabName;
        
        // Remover active de todas as tabs (estado inativo)
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-blue-50', 'border-2', 'border-blue-500', 'shadow-sm', 'text-blue-700');
            btn.classList.add('bg-white', 'border', 'border-gray-200', 'text-gray-600');
            
            // Atualizar cores dos preços para inativo
            const priceElements = btn.querySelectorAll('[data-price-basico-cnpj], [data-price-basico-cpf], [data-price-completo-cnpj], [data-price-completo-cpf], [data-price-monitor-cnpj], [data-price-monitor-cpf], [data-price-rapida]');
            priceElements.forEach(el => {
                el.classList.remove('text-blue-600');
                el.classList.add('text-gray-500');
            });
        });
        
        // Adicionar active na tab clicada (estado ativo)
        const activeTab = document.querySelector(`[data-tab="${tabName}"]`);
        if (activeTab) {
            activeTab.classList.remove('bg-white', 'border', 'border-gray-200', 'text-gray-600');
            activeTab.classList.add('bg-blue-50', 'border-2', 'border-blue-500', 'shadow-sm', 'text-blue-700');
            
            // Atualizar cores dos preços para ativo
            const priceElements = activeTab.querySelectorAll('[data-price-basico-cnpj], [data-price-basico-cpf], [data-price-completo-cnpj], [data-price-completo-cpf], [data-price-monitor-cnpj], [data-price-monitor-cpf], [data-price-rapida]');
            priceElements.forEach(el => {
                el.classList.remove('text-gray-500');
                el.classList.add('text-blue-600');
            });
        }
        
        // Esconder todos os conteúdos
        document.querySelectorAll('[id^="content-"]').forEach(content => {
            if (!content.id.includes('-cnpj') && !content.id.includes('-cpf')) {
                content.classList.add('hidden');
            }
        });
        
        // Mostrar conteúdo da tab selecionada
        const content = document.getElementById(`content-${tabName}`);
        if (content) {
            content.classList.remove('hidden');
        }
        
        // Atualizar conteúdo conforme CNPJ/CPF
        updateContent();
        updateButton();
    }

    // Função para atualizar preços conforme CNPJ/CPF
    function updatePrices() {
        const isCNPJ = currentTipo === 'cnpj';
        
        // Preço Básico
        const priceBasicoCNPJ = document.querySelector('[data-price-basico-cnpj]');
        const priceBasicoCPF = document.querySelector('[data-price-basico-cpf]');
        if (priceBasicoCNPJ && priceBasicoCPF) {
            priceBasicoCNPJ.classList.toggle('hidden', !isCNPJ);
            priceBasicoCPF.classList.toggle('hidden', isCNPJ);
        }
        
        // Preço Completo
        const priceCompletoCNPJ = document.querySelector('[data-price-completo-cnpj]');
        const priceCompletoCPF = document.querySelector('[data-price-completo-cpf]');
        if (priceCompletoCNPJ && priceCompletoCPF) {
            priceCompletoCNPJ.classList.toggle('hidden', !isCNPJ);
            priceCompletoCPF.classList.toggle('hidden', isCNPJ);
        }
        
        // Preço Monitoramento
        const priceMonitorCNPJ = document.querySelector('[data-price-monitor-cnpj]');
        const priceMonitorCPF = document.querySelector('[data-price-monitor-cpf]');
        if (priceMonitorCNPJ && priceMonitorCPF) {
            priceMonitorCNPJ.classList.toggle('hidden', !isCNPJ);
            priceMonitorCPF.classList.toggle('hidden', isCNPJ);
        }
    }

    // Função para atualizar conteúdo conforme CNPJ/CPF
    function updateContent() {
        const isCNPJ = currentTipo === 'cnpj';
        
        // Atualizar conteúdo de cada tab
        ['rapida', 'basico', 'completo'].forEach(tab => {
            const contentCNPJ = document.getElementById(`content-${tab}-cnpj`);
            const contentCPF = document.getElementById(`content-${tab}-cpf`);
            
            if (contentCNPJ && contentCPF) {
                contentCNPJ.classList.toggle('hidden', !isCNPJ);
                contentCPF.classList.toggle('hidden', isCNPJ);
            }
        });
    }

    // Função para atualizar botão de ação
    function updateButton() {
        const button = document.getElementById('action-button');
        if (!button) return;
        
        if (currentTab === 'rapida') {
            button.textContent = 'Consultar';
            button.className = 'w-full px-6 py-3 bg-gray-600 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition-colors';
        } else if (currentTab === 'monitor') {
            button.textContent = 'Assinar Monitoramento';
            button.className = 'w-full px-6 py-3 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors';
        } else {
            button.textContent = 'Gerar Relatório';
            button.className = 'w-full px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors';
        }
    }

    // Função para alternar entre CNPJ e CPF
    function toggleTipoConsulta(tipo) {
        currentTipo = tipo;
        const cardCNPJ = document.getElementById('card-tipo-cnpj');
        const cardCPF = document.getElementById('card-tipo-cpf');
        const camposCNPJ = document.getElementById('campos-cnpj');
        const camposCPF = document.getElementById('campos-cpf');
        const inputCNPJ = document.getElementById('input-cnpj');
        const inputCPF = document.getElementById('input-cpf');
        const inputDataNasc = document.getElementById('input-data-nascimento');

        if (tipo === 'cnpj') {
            // Atualizar cards de seleção
            cardCNPJ.classList.remove('border-gray-300', 'hover:bg-gray-50');
            cardCNPJ.classList.add('border-blue-600', 'bg-blue-50');
            cardCPF.classList.remove('border-blue-600', 'bg-blue-50');
            cardCPF.classList.add('border-gray-300', 'hover:bg-gray-50');

            // Mostrar campos CNPJ e esconder CPF
            camposCNPJ.classList.remove('hidden');
            camposCPF.classList.add('hidden');

            // Limpar campos CPF
            if (inputCPF) inputCPF.value = '';
            if (inputDataNasc) inputDataNasc.value = '';
        } else {
            // Atualizar cards de seleção
            cardCPF.classList.remove('border-gray-300', 'hover:bg-gray-50');
            cardCPF.classList.add('border-blue-600', 'bg-blue-50');
            cardCNPJ.classList.remove('border-blue-600', 'bg-blue-50');
            cardCNPJ.classList.add('border-gray-300', 'hover:bg-gray-50');

            // Mostrar campos CPF e esconder CNPJ
            camposCPF.classList.remove('hidden');
            camposCNPJ.classList.add('hidden');

            // Limpar campo CNPJ
            if (inputCNPJ) inputCNPJ.value = '';
        }
        
        // Atualizar preços e conteúdo
        updatePrices();
        updateContent();
    }

    // Função para exibir informações do cliente selecionado
    function mostrarClienteSelecionado(documento, nome) {
        const infoDiv = document.getElementById('cliente-selecionado-info');
        const nomeSpan = document.getElementById('cliente-selecionado-nome');
        const cnpjSpan = document.getElementById('cliente-selecionado-cnpj');
        
        if (infoDiv && nomeSpan && cnpjSpan) {
            nomeSpan.textContent = nome;
            cnpjSpan.textContent = maskCNPJ(documento);
            infoDiv.classList.remove('hidden');
        }
    }

    // Função para ocultar informações do cliente selecionado
    function ocultarClienteSelecionado() {
        const infoDiv = document.getElementById('cliente-selecionado-info');
        if (infoDiv) {
            infoDiv.classList.add('hidden');
        }
    }

    // Inicialização quando o DOM estiver pronto
    function init() {
        // Event listeners para os radio buttons
        const radioCNPJ = document.getElementById('radio-cnpj');
        const radioCPF = document.getElementById('radio-cpf');
        const inputCNPJ = document.getElementById('input-cnpj');
        const inputCPF = document.getElementById('input-cpf');
        const inputDataNasc = document.getElementById('input-data-nascimento');
        const selectCliente = document.getElementById('select-cliente');
        const btnLimparCliente = document.getElementById('btn-limpar-cliente');

        // Toggle ao clicar nos cards
        if (radioCNPJ) {
            radioCNPJ.addEventListener('change', function() {
                if (this.checked) {
                    toggleTipoConsulta('cnpj');
                }
            });
        }

        if (radioCPF) {
            radioCPF.addEventListener('change', function() {
                if (this.checked) {
                    toggleTipoConsulta('cpf');
                }
            });
        }

        // Event listeners para as tabs
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                switchTab(tabName);
            });
        });

        // Máscaras nos inputs
        if (inputCNPJ) {
            inputCNPJ.addEventListener('input', function(e) {
                this.value = maskCNPJ(this.value);
            });
        }

        if (inputCPF) {
            inputCPF.addEventListener('input', function(e) {
                this.value = maskCPF(this.value);
            });
        }

        if (inputDataNasc) {
            inputDataNasc.addEventListener('input', function(e) {
                this.value = maskData(this.value);
            });
        }

        // Tentar usar jQuery Mask se disponível
        if (typeof $ !== 'undefined' && typeof $.fn.mask !== 'undefined') {
            if (inputCNPJ) $('#input-cnpj').mask('00.000.000/0000-00');
            if (inputCPF) $('#input-cpf').mask('000.000.000-00');
            if (inputDataNasc) $('#input-data-nascimento').mask('00/00/0000');
        }

        // Event listener para seleção de cliente
        if (selectCliente) {
            selectCliente.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const documento = this.value;
                const nome = selectedOption ? selectedOption.getAttribute('data-nome') : null;

                if (documento && inputCNPJ) {
                    // Preencher o campo CNPJ com máscara
                    inputCNPJ.value = maskCNPJ(documento);
                    // Exibir informações do cliente selecionado
                    if (nome) {
                        mostrarClienteSelecionado(documento, nome);
                    }
                } else {
                    // Limpar campo e ocultar informações
                    if (inputCNPJ) {
                        inputCNPJ.value = '';
                    }
                    ocultarClienteSelecionado();
                }
            });
        }

        // Event listener para botão limpar cliente
        if (btnLimparCliente) {
            btnLimparCliente.addEventListener('click', function() {
                if (selectCliente) {
                    selectCliente.value = '';
                }
                if (inputCNPJ) {
                    inputCNPJ.value = '';
                }
                ocultarClienteSelecionado();
            });
        }

        // Limpar seleção de cliente quando o usuário digitar manualmente no campo CNPJ
        if (inputCNPJ) {
            inputCNPJ.addEventListener('input', function() {
                // Se o usuário começar a digitar, limpar a seleção do select
                if (selectCliente && selectCliente.value) {
                    const valorAtual = this.value.replace(/\D/g, '');
                    const valorSelecionado = selectCliente.value.replace(/\D/g, '');
                    
                    // Se o valor digitado for diferente do selecionado, limpar a seleção
                    if (valorAtual !== valorSelecionado) {
                        selectCliente.value = '';
                        ocultarClienteSelecionado();
                    }
                }
            });
        }
        
        // Inicializar com tab Completo selecionada
        switchTab('completo');
        updatePrices();
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
