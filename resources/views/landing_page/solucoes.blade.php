
<style>
.solutions-swiper .swiper-slide {
    width: 320px;
    height: 280px;
    pointer-events: none;
}

.solutions-swiper .swiper-slide > div {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.solutions-swiper .swiper-slide * {
    pointer-events: none;
}

.solutions-swiper {
    overflow: hidden;
}

.solutions-swiper .swiper-wrapper {
    transition-timing-function: linear !important;
}
</style>

<section id="solucoes-funcionalidades" class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Funcionalidades do 
                <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">HUB</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Seis funcionalidades principais que transformam o dia a dia do escritório contábil e das empresas
            </p>
        </div>

        <!-- Swiper -->
        <div class="swiper solutions-swiper">
            <div class="swiper-wrapper">
                <!-- Primeira sequência -->
                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="text-center mb-4">
                            <img src="{{ asset('icone-gif/checklist.gif') }}" alt="Central de Documentos" class="w-16 h-16 mx-auto object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Central de Documentos</h3>
                        <p class="text-gray-600 text-center">Upload e organização por empresa, competência e tipo. Versionamento e busca inteligente com histórico e evidência.</p>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="text-center mb-4">
                            <img src="{{ asset('icone-gif/analyse.gif') }}" alt="Leitura e Diagnóstico" class="w-16 h-16 mx-auto object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Leitura e Diagnóstico</h3>
                        <p class="text-gray-600 text-center">Importação e estruturação de SPED, detecção de inconsistências e semáforo por competência com alertas.</p>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="text-center mb-4">
                            <img src="{{ asset('icone-gif/clipboard-gear.gif') }}" alt="Motor de Regras" class="w-16 h-16 mx-auto object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Motor de Regras</h3>
                        <p class="text-gray-600 text-center">Regras parametrizáveis por operação. Classificação automática e evolução contínua que aprende seu padrão.</p>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="text-center mb-4">
                            <img src="{{ asset('icone-gif/secure-payment.gif') }}" alt="Compliance e Situação Fiscal" class="w-16 h-16 mx-auto object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Compliance e Situação Fiscal</h3>
                        <p class="text-gray-600 text-center">Painel de situação por CNPJ, alertas de vencimento e relatório de risco com evidências e histórico.</p>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="text-center mb-4">
                            <img src="{{ asset('icone-gif/customer-service.gif') }}" alt="Automação de Comunicação" class="w-16 h-16 mx-auto object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Automação de Comunicação</h3>
                        <p class="text-gray-600 text-center">Cobrança automática via WhatsApp e portal. Mensagens com contexto e registro completo da conversa.</p>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="text-center mb-4">
                            <img src="{{ asset('icone-gif/page-optimization.gif') }}" alt="Portal do Cliente" class="w-16 h-16 mx-auto object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Portal do Cliente</h3>
                        <p class="text-gray-600 text-center">Checklist do mês, pendências, prazos e histórico. Permissões por perfil para menos atrito e mais previsibilidade.</p>
                    </div>
                </div>

                <!-- Segunda sequência (duplicação para loop infinito) -->
                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="text-center mb-4">
                            <img src="{{ asset('icone-gif/checklist.gif') }}" alt="Central de Documentos" class="w-16 h-16 mx-auto object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Central de Documentos</h3>
                        <p class="text-gray-600 text-center">Upload e organização por empresa, competência e tipo. Versionamento e busca inteligente com histórico e evidência.</p>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="text-center mb-4">
                            <img src="{{ asset('icone-gif/analyse.gif') }}" alt="Leitura e Diagnóstico" class="w-16 h-16 mx-auto object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Leitura e Diagnóstico</h3>
                        <p class="text-gray-600 text-center">Importação e estruturação de SPED, detecção de inconsistências e semáforo por competência com alertas.</p>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="text-center mb-4">
                            <img src="{{ asset('icone-gif/clipboard-gear.gif') }}" alt="Motor de Regras" class="w-16 h-16 mx-auto object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Motor de Regras</h3>
                        <p class="text-gray-600 text-center">Regras parametrizáveis por operação. Classificação automática e evolução contínua que aprende seu padrão.</p>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="text-center mb-4">
                            <img src="{{ asset('icone-gif/secure-payment.gif') }}" alt="Compliance e Situação Fiscal" class="w-16 h-16 mx-auto object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Compliance e Situação Fiscal</h3>
                        <p class="text-gray-600 text-center">Painel de situação por CNPJ, alertas de vencimento e relatório de risco com evidências e histórico.</p>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="text-center mb-4">
                            <img src="{{ asset('icone-gif/customer-service.gif') }}" alt="Automação de Comunicação" class="w-16 h-16 mx-auto object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Automação de Comunicação</h3>
                        <p class="text-gray-600 text-center">Cobrança automática via WhatsApp e portal. Mensagens com contexto e registro completo da conversa.</p>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="text-center mb-4">
                            <img src="{{ asset('icone-gif/page-optimization.gif') }}" alt="Portal do Cliente" class="w-16 h-16 mx-auto object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Portal do Cliente</h3>
                        <p class="text-gray-600 text-center">Checklist do mês, pendências, prazos e histórico. Permissões por perfil para menos atrito e mais previsibilidade.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Scripts carregados no layout -->