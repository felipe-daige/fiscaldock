<!-- Hero Section -->
<section id="precos-hero" class="bg-white pt-12 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-4">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-1">
                Planos e Preços
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                O melhor custo benefício para sua empresa. Escolha o plano ideal para suas necessidades.
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
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Light</h3>
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
                
                <a href="/agendar" data-link class="block w-full btn-primary text-white text-center font-semibold px-6 py-3 rounded-lg transition-colors mb-6">
                    Começar agora
                </a>

                <ul class="space-y-3 mb-6">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Emissão e automação de NFS-e</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Agendamentos recorrentes</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Contas a pagar e receber</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Automação de boletos</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Conciliação bancária inteligente</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Importação automática de extratos</span>
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
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Plus</h3>
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
                
                <a href="/agendar" data-link class="block w-full btn-primary text-white text-center font-semibold px-6 py-3 rounded-lg transition-colors mb-6">
                    Começar agora
                </a>

                <div class="mb-4">
                    <p class="text-sm font-semibold text-gray-900 mb-3">Todos os itens do plano LIGHT +</p>
                </div>

                <ul class="space-y-3 mb-6">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Rateio de despesas e receitas</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Permissão de acesso</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Centros de custo</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Regime de competência</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Análise vertical e horizontal</span>
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
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Premium</h3>
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
                
                <a href="/agendar" data-link class="block w-full btn-primary text-white text-center font-semibold px-6 py-3 rounded-lg transition-colors mb-6">
                    Começar agora
                </a>

                <div class="mb-4">
                    <p class="text-sm font-semibold text-gray-900 mb-3">Todos os itens do plano PLUS +</p>
                </div>

                <ul class="space-y-3 mb-6">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Planejamento orçamentário</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Pagamento reembolsável</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Emissão de boletos em lote</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Emissão de NFS-e em lote</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Central de cobranças</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">CNAB de pagamento bancos Itaú e Inter</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">API Pública</span>
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
                        <th class="text-center py-4 px-4 font-semibold text-gray-900">Light</th>
                        <th class="text-center py-4 px-4 font-semibold text-gray-900 bg-blue-50">Plus</th>
                        <th class="text-center py-4 px-4 font-semibold text-gray-900">Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 text-gray-700">Usuários</td>
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
                        <td class="py-4 px-4 text-gray-700">Contas bancárias automatizadas</td>
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
                        <td class="py-4 px-4 text-gray-700">Conciliação bancária inteligente</td>
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
                        <td class="py-4 px-4 text-gray-700">Histórico de atividades</td>
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
                        <td class="py-4 px-4 text-gray-700">Exportação contábil</td>
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
                        <td class="py-4 px-4 text-gray-700">Emissão de boletos</td>
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
                        <td class="py-4 px-4 text-gray-700">Relatórios gerenciais</td>
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
                        <td class="py-4 px-4 text-gray-700">Caixa de entrada</td>
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
                        <td class="py-4 px-4 text-gray-700">Emissão de NFS-e</td>
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
                        <td class="py-4 px-4 text-gray-700">Agendamentos recorrentes</td>
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
                        <td class="py-4 px-4 text-gray-700">Agendamento de contas a pagar e receber</td>
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
                        <td class="py-4 px-4 text-gray-700">Automação de NFS-e/Boletos</td>
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
                        <td class="py-4 px-4 text-gray-700">Rateio de despesas e receitas</td>
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
                        <td class="py-4 px-4 text-gray-700">Consulta de notas fiscais</td>
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
                        <td class="py-4 px-4 text-gray-700">Relatórios de Análise Horizontal e Vertical</td>
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
                        <td class="py-4 px-4 text-gray-700">Emissão de recibos</td>
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
                        <td class="py-4 px-4 text-gray-700">Permissões de acesso por usuário</td>
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
                        <td class="py-4 px-4 text-gray-700">Centros de custo</td>
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
                        <td class="py-4 px-4 text-gray-700">Regime de competência</td>
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
                        <td class="py-4 px-4 text-gray-700">Controle de agendamento no banco</td>
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
                        <td class="py-4 px-4 text-gray-700">CNAB de pagamentos Banco Itaú</td>
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
                        <td class="py-4 px-4 text-gray-700">CNAB de pagamentos Banco Inter</td>
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
                        <td class="py-4 px-4 text-gray-700">API Pública</td>
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
                        <td class="py-4 px-4 text-gray-700">Planejamento orçamentário</td>
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
                        <td class="py-4 px-4 text-gray-700">Central de cobranças</td>
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
                        <td class="py-4 px-4 text-gray-700">Emissão de faturas</td>
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
                        <td class="py-4 px-4 text-gray-700">Emissão de boletos em lote</td>
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
                        <td class="py-4 px-4 text-gray-700">Emissão de NFS-e em lote</td>
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
                        <td class="py-4 px-4 text-gray-700">Pagamento reembolsável</td>
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
