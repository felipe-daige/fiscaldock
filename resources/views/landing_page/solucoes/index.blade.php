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
                Inteligência Fiscal para Contadores
            </h1>
            <p class="text-lg sm:text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-6 md:mb-8 px-4">
                Importe SPED, monitore fornecedores e antecipe riscos fiscais antes que virem autuações
            </p>
        </div>
    </div>
</section>

<!-- Seção 2: Demonstração Visual Interativa -->
<section id="solucoes-funcionalidades" class="py-16 bg-white section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="demo-area !bg-white !p-0">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    Como Funciona na Prática
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Em apenas 3 passos, transforme a gestão de riscos e o compliance fiscal no seu escritório.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative mt-10">
                <!-- Linha conectiva visual para telas grandes -->
                <div class="hidden md:block absolute top-[4.5rem] left-[16.6%] right-[16.6%] h-[1px] bg-gray-300 z-0"></div>
                
                <!-- Passo 1 -->
                <div class="relative z-10 bg-white rounded border border-gray-300 p-6 shadow-sm hover:shadow-md hover:border-blue-500 transition-all text-center">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 border-4 border-white">
                        <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                    </div>
                    <div class="bg-gray-800 text-white font-bold w-10 h-10 rounded-full flex items-center justify-center mx-auto absolute -top-5 left-1/2 transform -translate-x-1/2 border-4 border-white shadow-sm ring-1 ring-gray-200">1</div>
                    <h3 class="text-sm font-bold text-gray-900 mb-3 uppercase tracking-wide">Importe</h3>
                    <p class="text-gray-600 text-sm">Envie seu SPED/EFD ou XMLs de notas e deixe a plataforma extrair todos os dados de participantes.</p>
                </div>

                <!-- Passo 2 -->
                <div class="relative z-10 bg-white rounded border border-gray-300 p-6 shadow-sm hover:shadow-md hover:border-blue-500 transition-all text-center">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 border-4 border-white">
                        <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                        </svg>
                    </div>
                    <div class="bg-gray-800 text-white font-bold w-10 h-10 rounded-full flex items-center justify-center mx-auto absolute -top-5 left-1/2 transform -translate-x-1/2 border-4 border-white shadow-sm ring-1 ring-gray-200">2</div>
                    <h3 class="text-sm font-bold text-gray-900 mb-3 uppercase tracking-wide">Analise</h3>
                    <p class="text-gray-600 text-sm">Cruzamentos automáticos e conferência em tempo real com as bases oficiais da Receita.</p>
                </div>

                <!-- Passo 3 -->
                <div class="relative z-10 bg-white rounded border border-gray-300 p-6 shadow-sm hover:shadow-md hover:border-blue-500 transition-all text-center">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 border-4 border-white">
                        <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div class="bg-gray-800 text-white font-bold w-10 h-10 rounded-full flex items-center justify-center mx-auto absolute -top-5 left-1/2 transform -translate-x-1/2 border-4 border-white shadow-sm ring-1 ring-gray-200">3</div>
                    <h3 class="text-sm font-bold text-gray-900 mb-3 uppercase tracking-wide">Proteja</h3>
                    <p class="text-gray-600 text-sm">Score de risco, alertas de IE suspensa ou inidôneo. Tudo no seu radar antes do fisco.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção 3: O que Fazemos -->
<section class="py-16 bg-gray-50 section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Como o FiscalDock Protege seu Escritório</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Da importação do SPED ao alerta de risco fiscal, cada ferramenta foi pensada para
                reduzir o trabalho manual e blindar seus clientes contra autuações.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="feature-card bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('binary_files/icone-gif/checklist.gif') }}" alt="Importação de SPED" class="w-16 h-16 mx-auto object-contain">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Importação de SPED</h3>
                <p class="text-gray-600">Faça upload do EFD ICMS/IPI ou PIS/COFINS e veja participantes, notas e valores extraídos automaticamente.</p>
            </div>

            <div class="feature-card bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('binary_files/icone-gif/analyse.gif') }}" alt="Monitoramento de Participantes" class="w-16 h-16 mx-auto object-contain">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Monitoramento de Participantes</h3>
                <p class="text-gray-600">Saiba em tempo real se algum fornecedor teve CNPJ baixado, IE suspensa ou mudança de regime tributário.</p>
            </div>

            <div class="feature-card bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('binary_files/icone-gif/clipboard-gear.gif') }}" alt="Consultas Tributárias em Lote" class="w-16 h-16 mx-auto object-contain">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Consultas Tributárias em Lote</h3>
                <p class="text-gray-600">Envie uma lista de CNPJs e receba situação cadastral, regime tributário, Simples Nacional e IE de uma só vez.</p>
            </div>

            <div class="feature-card bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('binary_files/icone-gif/secure-payment.gif') }}" alt="Dashboard e BI Fiscal" class="w-16 h-16 mx-auto object-contain">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Dashboard e BI Fiscal</h3>
                <p class="text-gray-600">Visualize faturamento, compras e tributos em painéis interativos filtrados por CFOP, participante e período.</p>
            </div>

            <div class="feature-card bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('binary_files/icone-gif/customer-service.gif') }}" alt="Central de Alertas" class="w-16 h-16 mx-auto object-contain">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Central de Alertas</h3>
                <p class="text-gray-600">Receba alertas automáticos quando um participante apresentar irregularidade cadastral ou fiscal.</p>
            </div>

            <div class="feature-card bg-white rounded-lg p-6 text-center shadow-sm">
                <div class="text-center mb-4">
                    <img src="{{ asset('binary_files/icone-gif/page-optimization.gif') }}" alt="Compliance e Enriquecimento" class="w-16 h-16 mx-auto object-contain">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Compliance e Enriquecimento</h3>
                <p class="text-gray-600">Cruze dados de Receita Federal, SINTEGRA e CEIS para uma due diligence fiscal completa e automatizada.</p>
            </div>
        </div>
    </div>
</section>

<!-- Seção 4: Soluções Detalhadas com Accordion -->
<section id="solucoes-detalhadas" class="bg-gray-50 py-20 section-fade-in">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Solução <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">em Detalhe</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Veja como o FiscalDock transforma arquivos SPED em inteligência fiscal acionável
            </p>
        </div>

        <div class="space-y-4">
            <!-- Item Inteligência Tributária -->
            <div id="inteligencia-tributaria" class="solution-accordion-item bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                <button class="solution-accordion-header w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition-colors cursor-pointer">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <img src="{{ asset('binary_files/icone-gif/accounting.gif') }}" alt="Inteligência Tributária" class="w-8 h-8 object-contain">
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Importação SPED + Monitoramento: do Arquivo ao Alerta em Minutos</h3>
                            <p class="text-sm text-gray-500 mt-1">Importe EFD, extraia participantes e monitore a situação cadastral de cada fornecedor automaticamente</p>
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
                            Pare de verificar fornecedores manualmente e elimine o risco de aceitar notas de CNPJs irregulares
                        </p>

                        <!-- Descrição Principal -->
                        <p class="text-gray-600 leading-relaxed mb-6">
                            O FiscalDock recebe seus arquivos EFD ICMS/IPI e PIS/COFINS, extrai automaticamente todos os participantes, notas fiscais e valores por bloco (A, C, D), e cruza os dados com Receita Federal, SINTEGRA e CEIS. Você recebe alertas instantâneos sobre irregularidades cadastrais que podem gerar autuações.
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
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">Extração Automática do SPED</h4>
                                    <p class="text-gray-700 leading-relaxed">
                                        Faça upload do arquivo EFD e o FiscalDock extrai participantes, notas fiscais e valores organizados por bloco. Sem digitar nada, sem planilhas intermediárias. Tudo fica indexado por CNPJ, CFOP e período, pronto para análise.
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
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">Monitoramento em Tempo Real</h4>
                                    <p class="text-gray-700 leading-relaxed">
                                        Cada participante extraído do SPED é monitorado automaticamente via Receita Federal e SINTEGRA. CNPJ baixado, IE suspensa, mudança de regime tributário ou entrada no CEIS geram alertas instantâneos para que você aja antes da fiscalização.
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
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">BI Fiscal e Dashboards</h4>
                                    <p class="text-gray-700 leading-relaxed">
                                        Todos os dados extraídos alimentam dashboards interativos com análise por CFOP, participante e período. Visualize faturamento, compras e tributos em painéis que ajudam a identificar oportunidades e riscos rapidamente.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Comparativo Visual de Tempo -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
                            <h4 class="text-lg font-bold text-gray-900 mb-4 text-center">Verificação de Fornecedores: De Horas para Minutos</h4>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-semibold text-gray-700">Consulta manual (CNPJ por CNPJ)</span>
                                        <span class="text-sm font-bold text-red-600">4 horas</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-4">
                                        <div class="bg-red-500 h-4 rounded-full" style="width: 100%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-semibold text-gray-700">Com FiscalDock</span>
                                        <span class="text-sm font-bold text-green-600">2 minutos</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-4">
                                        <div class="bg-green-500 h-4 rounded-full" style="width: 0.83%"></div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-center text-sm text-gray-600 mt-4">
                                <span class="font-bold text-green-600">99,2% de redução</span> no tempo de verificação cadastral
                            </p>
                        </div>
                        
                        <!-- Selo de Atualização Legislativa -->
                        <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg p-6 text-center mb-6 shadow-lg">
                            <div class="flex items-center justify-center gap-3 mb-2">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <p class="text-2xl font-bold text-white">
                                    Dados Sempre Atualizados
                                </p>
                            </div>
                            <p class="text-green-100 mt-2 text-sm">
                                Consultas automáticas a Receita Federal, SINTEGRA e CEIS mantêm os dados dos seus participantes sempre em dia
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
                                    <span>Upload de arquivos EFD ICMS/IPI e EFD PIS/COFINS com extração automática de blocos A, C e D</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Identificação automática de todos os participantes (fornecedores e clientes) do arquivo</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Consulta em lote de CNPJs na Receita Federal, SINTEGRA e CEIS</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Alertas automáticos para CNPJ irregular, IE suspensa ou empresa inidônea</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Dashboards com análise de faturamento, compras e tributos por CFOP, participante e período</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Enriquecimento contínuo dos dados cadastrais de cada participante monitorado</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Progresso em tempo real via SSE para acompanhar importações e consultas</span>
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
                Chega de Risco Fiscal no Escuro
            </h2>
            <p class="text-lg sm:text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-6 md:mb-8 px-4">
                Comece a monitorar fornecedores e antecipar irregularidades antes que virem autuações
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#solucoes-detalhadas" class="bg-white text-blue-600 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-50 transition-colors">
                    Veja Como Funciona
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