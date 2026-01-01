{{-- Novo Cliente - Análise de Risco --}}
<div class="min-h-screen bg-gray-50" id="novo-cliente-container">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">
                    Novo Cliente
                </h1>
                <p class="text-xs text-gray-500 mt-1">
                    Cadastre um novo cliente e realize análises de risco
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
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Card CNPJ --}}
                        <label id="card-tipo-cnpj" class="flex items-center justify-center p-4 border-2 border-blue-600 rounded-lg cursor-pointer bg-blue-50 hover:bg-blue-100 transition-colors">
                            <input type="radio" name="tipo-consulta" value="cnpj" checked class="sr-only" id="radio-cnpj">
                            <div class="text-center">
                                <div class="text-2xl mb-2">🏢</div>
                                <div class="font-semibold text-gray-800 text-sm">CNPJ</div>
                                <div class="text-xs text-gray-600">Empresa</div>
                            </div>
                        </label>

                        {{-- Card CPF --}}
                        <label id="card-tipo-cpf" class="flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-gray-50 transition-colors">
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

                {{-- Cards de Planos --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">Escolha o tipo de relatório:</h3>
                    
                    {{-- Planos CNPJ --}}
                    <div id="planos-cnpj" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {{-- Card 1: Consulta Rápida CNPJ --}}
                        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 hover:shadow-lg transition-all flex flex-col">
                            <div class="text-center mb-4">
                                <h4 class="text-lg font-bold text-gray-900 mb-2">Consulta Rápida</h4>
                                <div class="mb-2">
                                    <span class="text-3xl font-bold text-green-600">Grátis</span>
                                </div>
                                <p class="text-xs text-gray-600 mb-2">Situação básica do CNPJ + Sócios</p>
                                <p class="text-xs text-red-600 font-semibold">Sem relatório • Sem histórico</p>
                            </div>
                            
                            <ul class="space-y-2 mb-4 text-sm">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Situação Cadastral CNPJ</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Quadro Societário (QSA)</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Reconsulta automática mensal</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Alerta por e-mail se situação mudar</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Histórico de alterações</span>
                                </li>
                            </ul>

                            <button type="button" class="w-full px-6 py-3 bg-gray-600 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition-colors mt-auto">
                                Consultar
                            </button>
                        </div>

                        {{-- Card 2: Relatório Básico CNPJ --}}
                        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 hover:shadow-lg transition-all flex flex-col">
                            <div class="text-center mb-4">
                                <h4 class="text-lg font-bold text-gray-900 mb-2">Relatório Básico</h4>
                                <div class="mb-2">
                                    <span class="text-3xl font-bold text-gray-900">R$ 14,90</span>
                                </div>
                            </div>
                            
                            <ul class="space-y-2 mb-4 text-sm">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Situação Cadastral</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Quadro Societário</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Simples Nacional</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Inscrição Estadual</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Listas Restritivas (CEIS, CNEP, Trabalho Escravo)</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Score de Risco (0-100)</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Relatório PDF</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Salvo no histórico</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Reconsulta automática mensal</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Alerta por e-mail se situação mudar</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Histórico de alterações</span>
                                </li>
                            </ul>

                            <button type="button" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors mt-auto">
                                Gerar Relatório
                            </button>
                        </div>

                        {{-- Card 3: Relatório Completo CNPJ --}}
                        <div class="bg-white rounded-xl shadow-lg border-2 border-blue-500 p-6 hover:shadow-xl transition-all relative flex flex-col">
                            <div class="absolute top-0 right-0 bg-blue-500 text-white px-4 py-1 rounded-bl-lg rounded-tr-xl text-sm font-semibold">
                                Popular
                            </div>
                            <div class="text-center mb-4">
                                <h4 class="text-lg font-bold text-gray-900 mb-2">Relatório Completo</h4>
                                <div class="mb-2">
                                    <span class="text-3xl font-bold text-gray-900">R$ 29,90</span>
                                </div>
                            </div>
                            
                            <ul class="space-y-2 mb-4 text-sm">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Tudo do Básico +</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">CND Federal</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">CND Estadual</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">CND Municipal</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">CNDT (Trabalhista)</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">CRF (FGTS)</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Protestos em Cartórios</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Análise detalhada</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Recomendações automáticas</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Reconsulta automática mensal</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Alerta por e-mail se situação mudar</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Histórico de alterações</span>
                                </li>
                            </ul>

                            <button type="button" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors mt-auto">
                                Gerar Relatório
                            </button>
                        </div>

                        {{-- Card 4: Monitoramento CNPJ --}}
                        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 hover:shadow-lg transition-all flex flex-col">
                            <div class="text-center mb-4">
                                <h4 class="text-lg font-bold text-gray-900 mb-2">Monitoramento</h4>
                                <div class="mb-2">
                                    <span class="text-3xl font-bold text-gray-900">R$ 49,90</span>
                                    <span class="text-gray-600 text-sm">/mês</span>
                                </div>
                            </div>
                            
                            <ul class="space-y-2 mb-4 text-sm">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Tudo do Completo +</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Reconsulta automática mensal</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Alerta por e-mail se situação mudar</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Histórico de alterações</span>
                                </li>
                            </ul>

                            <button type="button" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors mt-auto">
                                Assinar
                            </button>
                        </div>
                    </div>

                    {{-- Planos CPF --}}
                    <div id="planos-cpf" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 hidden">
                        {{-- Card 1: Consulta Rápida CPF --}}
                        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 hover:shadow-lg transition-all flex flex-col">
                            <div class="text-center mb-4">
                                <h4 class="text-lg font-bold text-gray-900 mb-2">Consulta Rápida</h4>
                                <div class="mb-2">
                                    <span class="text-3xl font-bold text-green-600">Grátis</span>
                                </div>
                                <p class="text-xs text-gray-600 mb-2">Situação do CPF + Nome completo</p>
                                <p class="text-xs text-red-600 font-semibold">Sem relatório • Sem histórico</p>
                            </div>
                            
                            <ul class="space-y-2 mb-4 text-sm">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Situação CPF</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Nome completo</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Reconsulta automática mensal</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Alerta por e-mail se situação mudar</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Histórico de alterações</span>
                                </li>
                            </ul>

                            <button type="button" class="w-full px-6 py-3 bg-gray-600 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition-colors mt-auto">
                                Consultar
                            </button>
                        </div>

                        {{-- Card 2: Relatório Básico CPF --}}
                        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 hover:shadow-lg transition-all flex flex-col">
                            <div class="text-center mb-4">
                                <h4 class="text-lg font-bold text-gray-900 mb-2">Relatório Básico</h4>
                                <div class="mb-2">
                                    <span class="text-3xl font-bold text-gray-900">R$ 9,90</span>
                                </div>
                            </div>
                            
                            <ul class="space-y-2 mb-4 text-sm">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Situação CPF</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Dados cadastrais</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Participação em empresas (QSA reverso)</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Verificação de óbito</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Score de Risco (0-100)</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Relatório PDF</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Salvo no histórico</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Reconsulta automática mensal</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Alerta por e-mail se situação mudar</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Histórico de alterações</span>
                                </li>
                            </ul>

                            <button type="button" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors mt-auto">
                                Gerar Relatório
                            </button>
                        </div>

                        {{-- Card 3: Relatório Completo CPF --}}
                        <div class="bg-white rounded-xl shadow-lg border-2 border-blue-500 p-6 hover:shadow-xl transition-all relative flex flex-col">
                            <div class="absolute top-0 right-0 bg-blue-500 text-white px-4 py-1 rounded-bl-lg rounded-tr-xl text-sm font-semibold">
                                Popular
                            </div>
                            <div class="text-center mb-4">
                                <h4 class="text-lg font-bold text-gray-900 mb-2">Relatório Completo</h4>
                                <div class="mb-2">
                                    <span class="text-3xl font-bold text-gray-900">R$ 19,90</span>
                                </div>
                            </div>
                            
                            <ul class="space-y-2 mb-4 text-sm">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Tudo do Básico +</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Protestos em Cartórios</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Processos judiciais</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Benefícios INSS</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Análise detalhada</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Recomendações automáticas</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Reconsulta automática mensal</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Alerta por e-mail se situação mudar</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Histórico de alterações</span>
                                </li>
                            </ul>

                            <button type="button" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors mt-auto">
                                Gerar Relatório
                            </button>
                        </div>

                        {{-- Card 4: Monitoramento CPF --}}
                        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 hover:shadow-lg transition-all flex flex-col">
                            <div class="text-center mb-4">
                                <h4 class="text-lg font-bold text-gray-900 mb-2">Monitoramento</h4>
                                <div class="mb-2">
                                    <span class="text-3xl font-bold text-gray-900">R$ 29,90</span>
                                    <span class="text-gray-600 text-sm">/mês</span>
                                </div>
                            </div>
                            
                            <ul class="space-y-2 mb-4 text-sm">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Tudo do Completo +</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Reconsulta automática mensal</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Alerta por e-mail se situação mudar</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">Histórico de alterações</span>
                                </li>
                            </ul>

                            <button type="button" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors mt-auto">
                                Assinar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
(function() {
    'use strict';

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

    // Função para alternar entre CNPJ e CPF
    function toggleTipoConsulta(tipo) {
        const cardCNPJ = document.getElementById('card-tipo-cnpj');
        const cardCPF = document.getElementById('card-tipo-cpf');
        const camposCNPJ = document.getElementById('campos-cnpj');
        const camposCPF = document.getElementById('campos-cpf');
        const planosCNPJ = document.getElementById('planos-cnpj');
        const planosCPF = document.getElementById('planos-cpf');
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

            // Mostrar planos CNPJ e esconder CPF
            planosCNPJ.classList.remove('hidden');
            planosCPF.classList.add('hidden');

            // Limpar campos CPF
            inputCPF.value = '';
            inputDataNasc.value = '';
        } else {
            // Atualizar cards de seleção
            cardCPF.classList.remove('border-gray-300', 'hover:bg-gray-50');
            cardCPF.classList.add('border-blue-600', 'bg-blue-50');
            cardCNPJ.classList.remove('border-blue-600', 'bg-blue-50');
            cardCNPJ.classList.add('border-gray-300', 'hover:bg-gray-50');

            // Mostrar campos CPF e esconder CNPJ
            camposCPF.classList.remove('hidden');
            camposCNPJ.classList.add('hidden');

            // Mostrar planos CPF e esconder CNPJ
            planosCPF.classList.remove('hidden');
            planosCNPJ.classList.add('hidden');

            // Limpar campo CNPJ
            inputCNPJ.value = '';
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
            $('#input-cnpj').mask('00.000.000/0000-00');
            $('#input-cpf').mask('000.000.000-00');
            $('#input-data-nascimento').mask('00/00/0000');
        }
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

