<style>
    /* Estilos existentes do swiper */
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

    /* Estilos do accordion existentes */
    .solution-accordion-item {
        margin-bottom: 1rem;
    }

    .solution-accordion-header {
        outline: none;
    }

    .solution-accordion-header[aria-expanded="true"] svg {
        transform: rotate(180deg);
    }

    .solution-accordion-content {
        transition: max-height 0.3s ease, opacity 0.3s ease, padding 0.3s ease;
    }

    /* Novos estilos do design de Importação XML */
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

    .section-fade-in {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease;
    }

    .section-fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .feature-card {
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .demo-area {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-radius: 1rem;
        padding: 3rem;
        position: relative;
    }

    @media (max-width: 640px) {
        .hero-gradient h1 {
            font-size: 2rem;
        }
        
        .hero-gradient p {
            font-size: 1rem;
        }

        .demo-area {
            padding: 1.5rem;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero-gradient py-12 md:py-20 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in-up">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 md:mb-6">
                Soluções que Transformam o Dia a Dia Fiscal
            </h1>
            <p class="text-lg sm:text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-6 md:mb-8 px-4">
                Seis funcionalidades principais que automatizam e otimizam processos fiscais e contábeis
            </p>
        </div>
    </div>
</section>

<!-- Seção 2: Demonstração Visual Interativa -->
<section id="solucoes-funcionalidades" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="demo-area">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Veja Nossas Funcionalidades</h2>
                <p class="text-lg text-gray-600">Seis funcionalidades principais que transformam o dia a dia do escritório contábil e das empresas</p>
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
    </div>
</section>

<!-- Seção 3: O que Fazemos -->
<section class="py-16 bg-gray-50 section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">O que Fazemos</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Seis funcionalidades principais que automatizam e otimizam processos fiscais e contábeis, 
                transformando o dia a dia do escritório contábil e das empresas.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="feature-card bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('icone-gif/checklist.gif') }}" alt="Central de Documentos" class="w-16 h-16 mx-auto object-contain">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Central de Documentos</h3>
                <p class="text-gray-600">Upload e organização por empresa, competência e tipo. Versionamento e busca inteligente.</p>
            </div>

            <div class="feature-card bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('icone-gif/analyse.gif') }}" alt="Leitura e Diagnóstico" class="w-16 h-16 mx-auto object-contain">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Leitura e Diagnóstico</h3>
                <p class="text-gray-600">Importação e estruturação de SPED, detecção de inconsistências e semáforo por competência.</p>
            </div>

            <div class="feature-card bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('icone-gif/clipboard-gear.gif') }}" alt="Motor de Regras" class="w-16 h-16 mx-auto object-contain">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Motor de Regras</h3>
                <p class="text-gray-600">Regras parametrizáveis por operação. Classificação automática e evolução contínua.</p>
            </div>

            <div class="feature-card bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('icone-gif/secure-payment.gif') }}" alt="Compliance e Situação Fiscal" class="w-16 h-16 mx-auto object-contain">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Compliance e Situação Fiscal</h3>
                <p class="text-gray-600">Painel de situação por CNPJ, alertas de vencimento e relatório de risco.</p>
            </div>

            <div class="feature-card bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('icone-gif/customer-service.gif') }}" alt="Automação de Comunicação" class="w-16 h-16 mx-auto object-contain">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Automação de Comunicação</h3>
                <p class="text-gray-600">Cobrança automática via WhatsApp e portal. Mensagens com contexto e registro completo.</p>
            </div>

            <div class="feature-card bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('icone-gif/page-optimization.gif') }}" alt="Portal do Cliente" class="w-16 h-16 mx-auto object-contain">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Portal do Cliente</h3>
                <p class="text-gray-600">Checklist do mês, pendências, prazos e histórico. Permissões por perfil.</p>
            </div>
        </div>
    </div>
</section>

<!-- Seção 4: Soluções Detalhadas com Accordion -->
<section id="solucoes-detalhadas" class="bg-gray-50 py-20 section-fade-in">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Soluções <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">Detalhadas</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Conheça em detalhes as soluções que automatizam e otimizam processos fiscais e contábeis
            </p>
        </div>

        <div class="space-y-4">
            <!-- Item RAF -->
            <div id="raf" class="solution-accordion-item bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                <button class="solution-accordion-header w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition-colors cursor-pointer">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center relative">
                            <img src="{{ asset('icone-gif/analyse.gif') }}" alt="RAF" class="w-8 h-8 object-contain">
                            <!-- Ícone de lupa sobre gráfico (overlay visual) -->
                            <svg class="w-5 h-5 absolute -top-1 -right-1 text-white opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">RAF: Relatório de Risco e Inteligência Fiscal</h3>
                            <p class="text-sm text-gray-500 mt-1">Transforme dados brutos em análise consultiva</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="solution-accordion-content overflow-hidden transition-all duration-300" style="max-height: 0; opacity: 0;">
                    <div class="p-6 pt-0">
                        <!-- Subtítulo -->
                        <p class="text-lg font-semibold text-gray-900 mb-4">
                            Transforme dados brutos em análise consultiva e garanta a saúde tributária de todos os seus clientes
                        </p>
                        
                        <!-- Descrição Principal -->
                        <p class="text-gray-600 leading-relaxed mb-6">
                            O Rubi processa as informações coletadas (Notas Fiscais, SPEDs e Consultas na Receita Federal) para gerar o Relatório de Regime Tributário e CND (RAF). Este relatório consolidado oferece, em uma única página, a situação fiscal completa de cada CNPJ.
                        </p>
                        
                        <!-- Destaque Visual Principal - Frase de Poder -->
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-6 text-center mb-6 shadow-lg border-l-4 border-blue-400 transform hover:scale-[1.01] transition-transform duration-200">
                            <p class="text-xl lg:text-2xl font-bold text-white italic">
                                "Use o dado bruto para gerar consultoria."
                            </p>
                            <p class="text-blue-100 mt-2 text-sm font-medium">
                                Eleve seu escritório de operacional para consultivo
                            </p>
                        </div>
                        
                        <!-- Três Pontos de Poder de Consultoria -->
                        <div class="space-y-4 mb-6">
                            <div class="flex items-start gap-4 p-4 bg-blue-50 rounded-lg border-2 border-blue-100 shadow-sm">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">Visão 360º</h4>
                                    <p class="text-gray-700 leading-relaxed">
                                        Visualize rapidamente o Regime Tributário, o status da CND e os principais dados de faturamento de cada cliente. Tenha uma visão consolidada e completa da situação fiscal de toda a sua carteira em um único lugar.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-4 p-4 bg-yellow-50 rounded-lg border-2 border-yellow-100 shadow-sm">
                                <div class="flex-shrink-0 w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">Identificação de Risco</h4>
                                    <p class="text-gray-700 leading-relaxed">
                                        Identifique instantaneamente clientes com pendências fiscais que precisam de ação imediata ou cujo regime tributário pode não ser o mais vantajoso. Receba alertas automáticos e priorize suas ações com base em dados concretos.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-4 p-4 bg-green-50 rounded-lg border-2 border-green-100 shadow-sm">
                                <div class="flex-shrink-0 w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">Atendimento Personalizado</h4>
                                    <p class="text-gray-700 leading-relaxed">
                                        Use o RAF como ferramenta de vendas, mostrando ao seu cliente que você está monitorando a saúde dele com profundidade e tecnologia. Transforme-se de operador em consultor estratégico, oferecendo insights valiosos baseados em dados reais.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mockup Visual do Relatório -->
                        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-lg p-6 mb-6 shadow-xl">
                            <div class="bg-white rounded-lg overflow-hidden border-2 border-blue-400">
                                <!-- Header do mockup -->
                                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-sm font-bold text-white">Relatório RAF</span>
                                    </div>
                                    <span class="px-2 py-1 text-xs bg-white bg-opacity-25 text-white rounded font-semibold">Inteligência Fiscal</span>
                                </div>
                                
                                <!-- Grid de duas colunas destacadas -->
                                <div class="p-4 bg-gray-50">
                                    <div class="grid grid-cols-2 gap-3">
                                        <!-- CNPJ X -->
                                        <div class="bg-white rounded-lg p-3 border-2 border-green-400">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-bold text-gray-900">CNPJ X</p>
                                                        <p class="text-[10px] text-gray-500">12.345.678/0001-90</p>
                                                    </div>
                                                </div>
                                                <span class="px-2 py-0.5 text-[10px] bg-green-500 text-white rounded font-bold">OK</span>
                                            </div>
                                            <div class="space-y-1 mt-2 pt-2 border-t border-gray-100">
                                                <div>
                                                    <p class="text-[10px] text-gray-500">Regime Tributário</p>
                                                    <p class="text-xs font-bold text-gray-900">Lucro Real</p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] text-gray-500">CND</p>
                                                    <p class="text-xs font-bold text-green-600">Regular</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- CNPJ Y -->
                                        <div class="bg-white rounded-lg p-3 border-2 border-yellow-400">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-bold text-gray-900">CNPJ Y</p>
                                                        <p class="text-[10px] text-gray-500">98.765.432/0001-10</p>
                                                    </div>
                                                </div>
                                                <span class="px-2 py-0.5 text-[10px] bg-yellow-500 text-white rounded font-bold">Alerta</span>
                                            </div>
                                            <div class="space-y-1 mt-2 pt-2 border-t border-gray-100">
                                                <div>
                                                    <p class="text-[10px] text-gray-500">Regime Tributário</p>
                                                    <p class="text-xs font-bold text-gray-900">Simples Nacional</p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] text-gray-500">CND</p>
                                                    <p class="text-xs font-bold text-yellow-600">Pendência Detectada</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Funcionalidades Técnicas -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-lg font-bold text-gray-900 mb-4">Como Funciona</h4>
                            <ul class="space-y-2 text-gray-600">
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Processamento automático de Notas Fiscais, SPEDs e consultas na Receita Federal</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Geração automática do relatório consolidado por CNPJ</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Visualização em uma única página com todos os dados relevantes</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Alertas automáticos de pendências e riscos fiscais</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Análise comparativa de regimes tributários</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Inteligência Tributária -->
            <div id="inteligencia-tributaria" class="solution-accordion-item bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                <button class="solution-accordion-header w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition-colors cursor-pointer">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <img src="{{ asset('icone-gif/accounting.gif') }}" alt="Inteligência Tributária" class="w-8 h-8 object-contain">
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Inteligência Tributária: Apuração de Impostos no Piloto Automático</h3>
                            <p class="text-sm text-gray-500 mt-1">Do Simples Nacional ao Lucro Presumido: troque planilhas complexas por precisão absoluta</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="solution-accordion-content overflow-hidden transition-all duration-300" style="max-height: 0; opacity: 0;">
                    <div class="p-6 pt-0">
                        <!-- Subtítulo -->
                        <p class="text-lg font-semibold text-gray-900 mb-4">
                            Automatize o cálculo e a geração de guias de impostos com precisão absoluta e zero erro humano
                        </p>
                        
                        <!-- Descrição Principal -->
                        <p class="text-gray-600 leading-relaxed mb-6">
                            O Rubi analisa o faturamento e as notas de entrada/saída, aplica as regras do regime tributário (incluindo anexos do Simples e retenções) e calcula o imposto devido em segundos. Elimine planilhas complexas e reduza o tempo de apuração de horas para minutos.
                        </p>
                        
                        <!-- Três Pilares -->
                        <div class="space-y-4 mb-6">
                            <div class="flex items-start gap-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">Cálculo à Prova de Falhas</h4>
                                    <p class="text-gray-700 leading-relaxed">
                                        Eliminação completa do erro humano. O sistema aplica automaticamente todas as regras tributárias, considerando anexos do Simples Nacional, retenções, alíquotas progressivas e todas as nuances da legislação vigente. Cada cálculo é auditável e rastreável.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-4 p-4 bg-yellow-50 rounded-lg border border-yellow-100">
                                <div class="flex-shrink-0 w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">Geração Instantânea</h4>
                                    <p class="text-gray-700 leading-relaxed">
                                        Emissão de guias de impostos com um único clique. Todas as guias necessárias são geradas automaticamente no formato correto, prontas para pagamento. Economize horas de trabalho manual e reduza drasticamente o tempo de processamento.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-4 p-4 bg-green-50 rounded-lg border border-green-100">
                                <div class="flex-shrink-0 w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">Entrega Direta</h4>
                                    <p class="text-gray-700 leading-relaxed">
                                        Envio automático das guias geradas diretamente para o cliente através do portal. O cliente recebe tudo organizado, com explicações claras e pode acompanhar o histórico completo. Menos retrabalho e mais transparência.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Comparativo Visual de Tempo -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
                            <h4 class="text-lg font-bold text-gray-900 mb-4 text-center">Redução de Tempo: De Horas para Minutos</h4>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-semibold text-gray-700">Processo Manual</span>
                                        <span class="text-sm font-bold text-red-600">4 horas</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-4">
                                        <div class="bg-red-500 h-4 rounded-full" style="width: 100%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-semibold text-gray-700">Com Rubi</span>
                                        <span class="text-sm font-bold text-green-600">2 minutos</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-4">
                                        <div class="bg-green-500 h-4 rounded-full" style="width: 0.83%"></div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-center text-sm text-gray-600 mt-4">
                                <span class="font-bold text-green-600">99,2% de redução</span> no tempo de processamento
                            </p>
                        </div>
                        
                        <!-- Selo de Atualização Legislativa -->
                        <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg p-6 text-center mb-6 shadow-lg">
                            <div class="flex items-center justify-center gap-3 mb-2">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <p class="text-2xl font-bold text-white">
                                    100% Atualizado com a Legislação
                                </p>
                            </div>
                            <p class="text-green-100 mt-2 text-sm">
                                Nossas regras são atualizadas automaticamente conforme mudanças na legislação tributária
                            </p>
                        </div>
                        
                        <!-- Funcionalidades Técnicas -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-lg font-bold text-gray-900 mb-4">Como Funciona</h4>
                            <ul class="space-y-2 text-gray-600">
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Análise automática do faturamento e notas fiscais de entrada e saída</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Aplicação automática das regras do regime tributário (Simples Nacional, Lucro Presumido, etc.)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Cálculo preciso considerando anexos do Simples, retenções e alíquotas progressivas</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Geração automática de todas as guias de impostos no formato correto</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Envio automático das guias para o cliente através do portal</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Auditoria completa e rastreabilidade de todos os cálculos realizados</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Atualização automática conforme mudanças na legislação tributária</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção 5: Call to Action -->
<section class="hero-gradient py-12 md:py-20 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in-up">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4 md:mb-6">
                Pronto para Transformar seu Escritório?
            </h2>
            <p class="text-lg sm:text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-6 md:mb-8 px-4">
                Descubra como nossas soluções podem automatizar e otimizar seus processos fiscais e contábeis
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#solucoes-detalhadas" class="bg-white text-blue-600 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-50 transition-colors">
                    Conheça Nossas Soluções
                </a>
                <a href="/contato" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 transition-colors border-2 border-white">
                    Fale Conosco
                </a>
            </div>
        </div>
    </div>
</section>

<script>
    // Setup scroll animations
    function setupScrollAnimations() {
        const sections = document.querySelectorAll('.section-fade-in');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        sections.forEach(section => {
            observer.observe(section);
        });
    }

    // Inicializar quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setupScrollAnimations();
        });
    } else {
        setupScrollAnimations();
    }
</script>

<!-- Scripts carregados no layout -->