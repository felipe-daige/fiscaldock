<style>
    /* Estilos para landing page */
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

    @media (max-width: 640px) {
        .hero-gradient h1 {
            font-size: 2rem;
        }
        
        .hero-gradient p {
            font-size: 1rem;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero-gradient py-12 md:py-20 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in-up">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 md:mb-6">
                RAF: Relatório de Risco e Inteligência Fiscal
            </h1>
            <p class="text-lg sm:text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-6 md:mb-8 px-4">
                Transforme dados brutos em análise consultiva
            </p>
        </div>
    </div>
</section>

<!-- Seção 2: Descrição Principal -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Transforme dados brutos em análise consultiva e garanta a saúde tributária de todos os seus clientes
            </h2>
            <p class="text-lg md:text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                O Rubi processa as informações coletadas (Notas Fiscais, SPEDs e Consultas na Receita Federal) para gerar o Relatório de Regime Tributário e CND (RAF). Este relatório consolidado oferece, em uma única página, a situação fiscal completa de cada CNPJ.
            </p>
        </div>
    </div>
</section>

<!-- Seção 3: Destaque Visual Principal - Frase de Poder -->
<section class="py-16 bg-gray-50 section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-8 md:p-12 text-center shadow-lg border-l-4 border-blue-400 transform hover:scale-[1.01] transition-transform duration-200">
            <p class="text-2xl md:text-3xl lg:text-4xl font-bold text-white italic mb-4">
                "Use o dado bruto para gerar consultoria."
            </p>
            <p class="text-blue-100 text-lg md:text-xl font-medium">
                Eleve seu escritório de operacional para consultivo
            </p>
        </div>
    </div>
</section>

<!-- Seção 4: Três Pontos de Poder de Consultoria -->
<section class="py-16 bg-white section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Os Três Pilares da Consultoria Fiscal</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Transforme-se de operador em consultor estratégico com dados reais e insights valiosos
            </p>
        </div>

        <div class="space-y-6">
            <!-- Pilar 1: Visão 360º -->
            <div class="feature-card flex flex-col md:flex-row items-start gap-6 p-6 bg-blue-50 rounded-lg border-2 border-blue-100 shadow-sm">
                <div class="flex-shrink-0 w-16 h-16 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Visão 360º</h3>
                    <p class="text-gray-700 leading-relaxed text-lg">
                        Visualize rapidamente o Regime Tributário, o status da CND e os principais dados de faturamento de cada cliente. Tenha uma visão consolidada e completa da situação fiscal de toda a sua carteira em um único lugar.
                    </p>
                </div>
            </div>
            
            <!-- Pilar 2: Identificação de Risco -->
            <div class="feature-card flex flex-col md:flex-row items-start gap-6 p-6 bg-yellow-50 rounded-lg border-2 border-yellow-100 shadow-sm">
                <div class="flex-shrink-0 w-16 h-16 bg-yellow-500 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Identificação de Risco</h3>
                    <p class="text-gray-700 leading-relaxed text-lg">
                        Identifique instantaneamente clientes com pendências fiscais que precisam de ação imediata ou cujo regime tributário pode não ser o mais vantajoso. Receba alertas automáticos e priorize suas ações com base em dados concretos.
                    </p>
                </div>
            </div>
            
            <!-- Pilar 3: Atendimento Personalizado -->
            <div class="feature-card flex flex-col md:flex-row items-start gap-6 p-6 bg-green-50 rounded-lg border-2 border-green-100 shadow-sm">
                <div class="flex-shrink-0 w-16 h-16 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Atendimento Personalizado</h3>
                    <p class="text-gray-700 leading-relaxed text-lg">
                        Use o RAF como ferramenta de vendas, mostrando ao seu cliente que você está monitorando a saúde dele com profundidade e tecnologia. Transforme-se de operador em consultor estratégico, oferecendo insights valiosos baseados em dados reais.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção 5: Mockup Visual do Relatório -->
<section class="py-16 bg-gray-50 section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Visualize a Situação Fiscal em Tempo Real</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Um relatório consolidado que mostra tudo o que você precisa saber sobre cada cliente
            </p>
        </div>

        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-lg p-6 md:p-8 shadow-xl">
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
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
    </div>
</section>

<!-- Seção 6: Funcionalidades Técnicas -->
<section class="py-16 bg-white section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Como Funciona</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Um processo automatizado que transforma dados brutos em insights consultivos valiosos
            </p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-8 md:p-12 max-w-4xl mx-auto">
            <ul class="space-y-4 text-gray-700">
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Processamento automático de Notas Fiscais, SPEDs e consultas na Receita Federal</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Geração automática do relatório consolidado por CNPJ</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Visualização em uma única página com todos os dados relevantes</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Alertas automáticos de pendências e riscos fiscais</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Análise comparativa de regimes tributários</span>
                </li>
            </ul>
        </div>
    </div>
</section>

<!-- Seção 7: Call to Action -->
<section class="hero-gradient py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
            Pronto para transformar dados em consultoria?
        </h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
            Agende uma demonstração e veja como o RAF pode elevar seu escritório 
            de operacional para consultivo.
        </p>
        <a href="/agendar" data-link class="inline-block bg-white text-blue-600 font-bold px-8 py-4 rounded-lg hover:bg-gray-100 transition-colors shadow-lg text-lg">
            Agendar Demonstração
        </a>
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

    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupScrollAnimations);
    } else {
        setupScrollAnimations();
    }
</script>
