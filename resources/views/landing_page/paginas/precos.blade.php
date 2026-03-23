<!-- Hero Section -->
<section id="precos-hero" class="bg-white pt-12 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-4">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-1">
                Planos e Preços
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Pare de perder horas verificando fornecedores manualmente. Escolha o plano ideal para blindar seu escritório contra riscos fiscais.
            </p>
        </div>
    </div>
    <!-- Divisor ondulado: transição do branco para o cinza (#f9fafb) -->
    <div class="pointer-events-none leading-none">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#f9fafb"></path>
        </svg>
    </div>
</section>

<!-- Seletor de Período -->
<section id="precos-periodo" class="bg-gray-50 py-2">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-center mb-2">
            <div class="inline-flex bg-white rounded-lg p-1 shadow-sm border border-gray-200">
                <button 
                    id="period-anual" 
                    class="period-btn px-6 py-2 rounded-md font-semibold transition-all duration-200 active"
                    data-period="anual"
                >
                    Anual
                </button>
                <button 
                    id="period-semestral" 
                    class="period-btn px-6 py-2 rounded-md font-semibold transition-all duration-200"
                    data-period="semestral"
                >
                    Semestral
                </button>
                <button 
                    id="period-mensal" 
                    class="period-btn px-6 py-2 rounded-md font-semibold transition-all duration-200"
                    data-period="mensal"
                >
                    Mensal
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Cards de Planos -->
<section id="precos-planos" class="bg-gray-50 py-4 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Plano Light -->
            <div class="plan-card bg-white rounded-xl shadow-sm border-2 border-gray-200 p-8 hover:shadow-lg transition-all duration-300" data-plan="light">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Essencial</h3>
                    <div class="mb-4">
                        <span class="plan-price text-4xl font-bold text-gray-900" data-period="anual">R$ 159</span>
                        <span class="plan-price text-4xl font-bold text-gray-900 hidden" data-period="semestral">R$ 189</span>
                        <span class="plan-price text-4xl font-bold text-gray-900 hidden" data-period="mensal">R$ 199</span>
                        <span class="text-gray-600 text-lg">/mês*</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-2">* Valor de referência</p>
                    <div class="plan-savings text-sm font-semibold text-green-600 hidden" data-period="anual">
                        Pagamento único de R$ 1.908/ano<br>
                        <span class="text-green-700">Você economiza R$ 480</span>
                    </div>
                    <div class="plan-savings text-sm font-semibold text-green-600 hidden" data-period="semestral">
                        Pagamento único de R$ 1.134/semestre<br>
                        <span class="text-green-700">Você economiza R$ 60</span>
                    </div>
                    <div class="plan-savings text-sm text-gray-500" data-period="mensal">
                        pagamento mensal<br>
                        sem desconto
                    </div>
                </div>
                
                <a href="/agendar" data-link data-button="cta" class="btn-primary-solid mb-6 block w-full px-6 py-3 text-center font-semibold">
                    Começar agora
                </a>

                <ul class="space-y-3 mb-6">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Importação de SPED EFD ICMS/IPI</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Importação de SPED EFD PIS/COFINS</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Consultas CNPJ na Receita Federal</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Dashboard interativo com BI fiscal</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Alertas fiscais automatizados</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">50 créditos de consulta inclusos/mês</span>
                    </li>
                </ul>

                <a href="#compare" class="text-blue-500 hover:text-blue-600 text-sm font-medium flex items-center">
                    Confira todos os itens e compare os planos
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>

            <!-- Plano Plus -->
            <div class="plan-card bg-white rounded-xl shadow-lg border-2 border-blue-500 p-8 hover:shadow-xl transition-all duration-300 relative" data-plan="plus">
                <div class="absolute top-0 right-0 bg-blue-500 text-white px-4 py-1 rounded-bl-lg rounded-tr-xl text-sm font-semibold">
                    Mais Popular
                </div>
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Profissional</h3>
                    <div class="mb-4">
                        <span class="plan-price text-4xl font-bold text-gray-900" data-period="anual">R$ 239</span>
                        <span class="plan-price text-4xl font-bold text-gray-900 hidden" data-period="semestral">R$ 284</span>
                        <span class="plan-price text-4xl font-bold text-gray-900 hidden" data-period="mensal">R$ 299</span>
                        <span class="text-gray-600 text-lg">/mês*</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-2">* Valor de referência</p>
                    <div class="plan-savings text-sm font-semibold text-green-600 hidden" data-period="anual">
                        Pagamento único de R$ 2.868/ano<br>
                        <span class="text-green-700">Você economiza R$ 720</span>
                    </div>
                    <div class="plan-savings text-sm font-semibold text-green-600 hidden" data-period="semestral">
                        Pagamento único de R$ 1.704/semestre<br>
                        <span class="text-green-700">Você economiza R$ 90</span>
                    </div>
                    <div class="plan-savings text-sm text-gray-500" data-period="mensal">
                        pagamento mensal<br>
                        sem desconto
                    </div>
                </div>
                
                <a href="/agendar" data-link data-button="cta" class="btn-primary-solid mb-6 block w-full px-6 py-3 text-center font-semibold">
                    Começar agora
                </a>

                <div class="mb-4">
                    <p class="text-sm font-semibold text-gray-900 mb-3">Todos os itens do plano ESSENCIAL +</p>
                </div>

                <ul class="space-y-3 mb-6">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Monitoramento contínuo de participantes</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Consultas em lote (CNPJ em massa)</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Consulta SINTEGRA (IE e situação cadastral)</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Detecção de Simples Nacional em fornecedores</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">200 créditos de consulta inclusos/mês</span>
                    </li>
                </ul>

                <a href="#compare" class="text-blue-500 hover:text-blue-600 text-sm font-medium flex items-center">
                    Confira todos os itens e compare os planos
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>

            <!-- Plano Premium -->
            <div class="plan-card bg-white rounded-xl shadow-sm border-2 border-gray-200 p-8 hover:shadow-lg transition-all duration-300" data-plan="premium">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Compliance</h3>
                    <div class="mb-4">
                        <span class="plan-price text-4xl font-bold text-gray-900" data-period="anual">R$ 367</span>
                        <span class="plan-price text-4xl font-bold text-gray-900 hidden" data-period="semestral">R$ 436</span>
                        <span class="plan-price text-4xl font-bold text-gray-900 hidden" data-period="mensal">R$ 459</span>
                        <span class="text-gray-600 text-lg">/mês*</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-2">* Valor de referência</p>
                    <div class="plan-savings text-sm font-semibold text-green-600 hidden" data-period="anual">
                        Pagamento único de R$ 4.404/ano<br>
                        <span class="text-green-700">Você economiza R$ 1.104</span>
                    </div>
                    <div class="plan-savings text-sm font-semibold text-green-600 hidden" data-period="semestral">
                        Pagamento único de R$ 2.616/semestre<br>
                        <span class="text-green-700">Você economiza R$ 138</span>
                    </div>
                    <div class="plan-savings text-sm text-gray-500" data-period="mensal">
                        pagamento mensal<br>
                        sem desconto
                    </div>
                </div>
                
                <a href="/agendar" data-link data-button="cta" class="btn-primary-solid mb-6 block w-full px-6 py-3 text-center font-semibold">
                    Começar agora
                </a>

                <div class="mb-4">
                    <p class="text-sm font-semibold text-gray-900 mb-3">Todos os itens do plano PROFISSIONAL +</p>
                </div>

                <ul class="space-y-3 mb-6">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Enriquecimento CEIS (empresas inidôneas)</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Consulta CND Federal (PGFN)</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Verificação de protestos em cartório</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Regularidade FGTS e CND Estadual</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Validação de NFe na SEFAZ (nota fria)</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Suporte prioritário</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">500 créditos de consulta inclusos/mês</span>
                    </li>
                </ul>

                <a href="#compare" class="text-blue-500 hover:text-blue-600 text-sm font-medium flex items-center">
                    Confira todos os itens e compare os planos
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Tabela Comparativa -->
<section id="precos-compare" class="bg-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-6">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                Compare os planos
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="text-left py-4 px-4 font-semibold text-gray-900">Recursos</th>
                        <th class="text-center py-4 px-4 font-semibold text-gray-900">Essencial</th>
                        <th class="text-center py-4 px-4 font-semibold text-gray-900 bg-blue-50">Profissional</th>
                        <th class="text-center py-4 px-4 font-semibold text-gray-900">Compliance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Clientes cadastrados</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-green-500 font-semibold">Ilimitado</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <span class="text-green-500 font-semibold">Ilimitado</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-green-500 font-semibold">Ilimitado</span>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Importações SPED por mês</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-green-500 font-semibold">Ilimitado</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <span class="text-green-500 font-semibold">Ilimitado</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-green-500 font-semibold">Ilimitado</span>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Importação EFD ICMS/IPI</td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Importação EFD PIS/COFINS</td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Consulta CNPJ na Receita Federal</td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Dashboard interativo com BI fiscal</td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Alertas fiscais automatizados</td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Progresso em tempo real (SSE)</td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Análise por CFOP e participante</td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Histórico de consultas e importações</td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Gestão de múltiplos clientes</td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Créditos de consulta inclusos</td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Monitoramento contínuo de participantes</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Consultas CNPJ em lote</td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Consulta SINTEGRA (IE e situação cadastral)</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Detecção de Simples Nacional</td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Grupos de monitoramento</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Ranking de risco por participante</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Resumo tributário por bloco (A, C, D)</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Importação de participantes via SPED</td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Enriquecimento CEIS (empresas inidôneas)</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Consulta CND Federal (PGFN)</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Verificação de protestos em cartório</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Regularidade FGTS e CND Estadual</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Validação de NFe na SEFAZ (nota fria)</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Cruzamento CTe declarados vs autorizados</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Verificação de trabalho escravo</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Suporte prioritário</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-4 px-4 text-gray-700">500 créditos de consulta inclusos/mês</td>
                        <td class="py-4 px-4 text-center">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center bg-blue-50">
                            <span class="text-gray-400">—</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<style>
.period-btn {
    color: var(--color-text-muted);
    background: transparent;
}

.period-btn.active {
    background-color: var(--color-primary-500);
    color: white;
}

.plan-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.plan-card:hover {
    transform: translateY(-4px);
}
</style>
