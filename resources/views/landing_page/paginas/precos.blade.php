<!-- Hero Section -->
<section class="bg-white pt-12 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-4">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-3">
                Planos que cabem no seu escritório
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Comece gratuitamente. Escale conforme sua demanda.
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
<section class="bg-gray-50 py-2">
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
<section class="bg-gray-50 py-4 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Plano Essencial -->
            <div class="plan-card bg-white rounded-xl shadow-sm border-2 border-gray-200 p-8 hover:shadow-lg transition-all duration-300" data-plan="essencial">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">Essencial</h3>
                    <p class="text-sm text-gray-500 mb-4">Ideal para contadores autônomos</p>
                    <div class="mb-4">
                        <span class="plan-price text-4xl font-bold text-gray-900" data-period="anual">R$ 97</span>
                        <span class="plan-price text-4xl font-bold text-gray-900 hidden" data-period="semestral">R$ 112</span>
                        <span class="plan-price text-4xl font-bold text-gray-900 hidden" data-period="mensal">R$ 127</span>
                        <span class="text-gray-600 text-lg">/mês</span>
                    </div>
                    <div class="plan-savings text-sm font-semibold text-green-600" data-period="anual">
                        Pagamento único de R$ 1.164/ano<br>
                        <span class="text-green-700">Você economiza R$ 360</span>
                    </div>
                    <div class="plan-savings text-sm font-semibold text-green-600 hidden" data-period="semestral">
                        Pagamento único de R$ 672/semestre<br>
                        <span class="text-green-700">Você economiza R$ 90</span>
                    </div>
                    <div class="plan-savings text-sm text-gray-500 hidden" data-period="mensal">
                        pagamento mensal<br>
                        sem desconto
                    </div>
                </div>

                <a href="/agendar" data-link class="btn-cta btn-cta--block mb-6">
                    Começar Agora
                </a>

                <ul class="space-y-3 mb-6">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Até 3 importações SPED/mês</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">50 consultas tributárias/mês</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Dashboard básico</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">1 usuário</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Suporte por e-mail</span>
                    </li>
                </ul>
            </div>

            <!-- Plano Profissional -->
            <div class="plan-card bg-white rounded-xl shadow-lg border-2 border-blue-500 p-8 hover:shadow-xl transition-all duration-300 relative ring-2 ring-blue-500" data-plan="profissional">
                <div class="absolute top-0 right-0 bg-blue-500 text-white px-4 py-1 rounded-bl-lg rounded-tr-xl text-sm font-semibold">
                    Mais Popular
                </div>
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">Profissional</h3>
                    <p class="text-sm text-gray-500 mb-4">Ideal para escritórios contábeis</p>
                    <div class="mb-4">
                        <span class="plan-price text-4xl font-bold text-gray-900" data-period="anual">R$ 197</span>
                        <span class="plan-price text-4xl font-bold text-gray-900 hidden" data-period="semestral">R$ 222</span>
                        <span class="plan-price text-4xl font-bold text-gray-900 hidden" data-period="mensal">R$ 247</span>
                        <span class="text-gray-600 text-lg">/mês</span>
                    </div>
                    <div class="plan-savings text-sm font-semibold text-green-600" data-period="anual">
                        Pagamento único de R$ 2.364/ano<br>
                        <span class="text-green-700">Você economiza R$ 600</span>
                    </div>
                    <div class="plan-savings text-sm font-semibold text-green-600 hidden" data-period="semestral">
                        Pagamento único de R$ 1.332/semestre<br>
                        <span class="text-green-700">Você economiza R$ 150</span>
                    </div>
                    <div class="plan-savings text-sm text-gray-500 hidden" data-period="mensal">
                        pagamento mensal<br>
                        sem desconto
                    </div>
                </div>

                <a href="/agendar" data-link class="btn-cta btn-cta--block mb-6">
                    Começar Agora
                </a>

                <ul class="space-y-3 mb-6">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Importações SPED ilimitadas</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">500 consultas tributárias/mês</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Dashboard completo + BI</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Central de Alertas</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Até 5 usuários</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Suporte prioritário</span>
                    </li>
                </ul>
            </div>

            <!-- Plano Enterprise -->
            <div class="plan-card bg-white rounded-xl shadow-sm border-2 border-gray-200 p-8 hover:shadow-lg transition-all duration-300" data-plan="enterprise">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">Enterprise</h3>
                    <p class="text-sm text-gray-500 mb-4">Ideal para grandes escritórios e empresas</p>
                    <div class="mb-4">
                        <span class="text-4xl font-bold text-gray-900">Sob consulta</span>
                    </div>
                    <div class="text-sm text-gray-500">
                        Plano personalizado para<br>
                        sua operação
                    </div>
                </div>

                <a href="/agendar" data-link class="btn-cta btn-cta--block mb-6">
                    Falar com Vendas
                </a>

                <div class="mb-4">
                    <p class="text-sm font-semibold text-gray-900 mb-3">Tudo do Profissional +</p>
                </div>

                <ul class="space-y-3 mb-6">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Consultas ilimitadas</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">API dedicada</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Usuários ilimitados</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">Gerente de conta dedicado</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-600">SLA garantido</span>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</section>

<!-- Mini FAQ -->
<section class="bg-white py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-10">Dúvidas sobre os planos</h2>

        <div class="space-y-6">
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Posso trocar de plano a qualquer momento?</h3>
                <p class="text-gray-600">Sim, upgrade ou downgrade quando quiser. Ajustamos o valor proporcional.</p>
            </div>
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Preciso de cartão de crédito para testar?</h3>
                <p class="text-gray-600">Não. Oferecemos período de teste gratuito sem compromisso.</p>
            </div>
            <div class="pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Como funciona o sistema de créditos?</h3>
                <p class="text-gray-600">Cada consulta tributária consome 1 crédito. Créditos não utilizados acumulam para o próximo mês.</p>
            </div>
        </div>
    </div>
</section>
