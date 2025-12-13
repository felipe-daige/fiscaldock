
<style>
/* Solutions Carousel Styles */
.solutions-carousel-container {
    min-height: 780px;
    position: relative;
    width: 100vw;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
}

.solutions-cards-wrapper {
    position: relative;
    overflow: hidden;
    overflow-x: hidden;
    padding-bottom: 2.5rem; /* espaço extra para a sombra do card ativo */
    margin: 0;
    width: 100%;
    z-index: 1;
}

.solutions-cards-track {
    display: flex;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform;
    align-items: stretch;
    padding: 0;
    position: relative;
}

.solution-card {
    flex-shrink: 0;
    width: 75%;
    padding: 0 1%;
    transition: opacity 0.3s, transform 0.3s;
    position: relative;
    height: 780px; /* Altura fixa uniforme com folga para sombra */
    z-index: 1;
}

.solution-card > div {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.solution-card-inner {
    height: 100%;
    display: flex;
    flex-direction: column;
    background: linear-gradient(180deg, var(--color-surface-muted) 0%, var(--color-surface-muted) 40%, var(--color-surface-alt) 100%); /* Fundo claro alinhado ao tema */
}

.solution-card-inner .grid {
    height: 100%;
}

/* Coluna esquerda - descrição */
.solution-card-inner > .grid > div:first-child {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background: var(--color-surface);
}

/* Coluna direita - mockup */
.solution-card-inner > .grid > div:last-child {
    background: linear-gradient(135deg, var(--color-primary-700) 0%, var(--color-primary-500) 100%);
    position: relative;
}

/* Efeito de brilho no mockup */
.solution-card-inner > .grid > div:last-child::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, color-mix(in srgb, var(--color-accent) 25%, transparent) 0%, transparent 70%);
    pointer-events: none;
}

/* Ajustar mockup para ter efeito de profundidade */
.solution-card-inner .bg-white.rounded-xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.solution-card .bg-gray-50 {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}

.solution-card.active {
    opacity: 1 !important;
    transform: scale(1) !important;
    z-index: 10;
}

.solution-card:not(.active) {
    opacity: 0.6;
    transform: scale(0.96);
    z-index: 1;
}

/* Garantir que o primeiro card seja visível mesmo sem classe active inicialmente */
.solution-card[data-index="0"],
.solution-card.active[data-index="0"] {
    opacity: 1 !important;
}

/* Garantir que cards com classe active sempre sejam visíveis */
.solutions-cards-track .solution-card.active {
    opacity: 1 !important;
    transform: scale(1) !important;
}

/* Responsividade */
@media (max-width: 1024px) {
    .solutions-cards-track {
        padding: 0;
    }
    
    .solution-card {
        width: 85%;
        padding: 0 1%;
    }
    
    /* Em tablets, colunas podem empilhar */
    .solution-card-inner .grid {
        grid-template-columns: 1fr;
    }
    
    .solution-card-inner > .grid > div:first-child {
        min-height: 300px;
    }
}

@media (max-width: 768px) {
    .solutions-cards-track {
        padding: 0 2%;
    }
    
    .solution-card {
        width: 96%;
        padding: 0 2%;
    }
    
    .solution-card:not(.active) {
        opacity: 0;
    }
    
    /* Garantir que o primeiro card seja sempre visível em mobile */
    .solution-card[data-index="0"],
    .solution-card.active {
        opacity: 1 !important;
    }
    
    .carousel-arrow {
        width: 40px;
        height: 40px;
    }
    
    .carousel-arrow svg {
        width: 20px;
        height: 20px;
    }
    
    .solutions-carousel-container {
        min-height: 780px;
    }
    
    /* Em mobile, colunas empilhadas - texto primeiro, depois imagem */
    .solution-card-inner .grid {
        grid-template-columns: 1fr;
    }
    
    .solution-card-inner > .grid > div:first-child {
        min-height: 250px;
        order: 2;
        padding: 1rem;
    }
    
    .solution-card-inner > .grid > div:last-child {
        order: 1;
        padding: 1.5rem;
    }
    
    /* Ajustar headline do mockup em mobile */
    .solution-card-inner > .grid > div:first-child .absolute {
        position: relative;
        top: auto;
        right: auto;
        left: auto;
        padding: 1rem 0;
    }
    
    .solution-card-inner > .grid > div:first-child .absolute h4 {
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }
    
    /* Ajustar tamanhos de texto em mobile */
    .solution-card-inner > .grid > div:last-child h3 {
        font-size: 1.75rem;
    }
    
    .solution-card-inner > .grid > div:last-child p {
        font-size: 1rem;
    }
}

/* Setas de navegação */
.carousel-arrow {
    z-index: 20;
    cursor: pointer;
}

/* Pills de navegação */
.solution-pill {
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.solution-pill.active {
    background-color: var(--color-surface) !important;
    color: var(--color-text) !important;
    border-color: var(--color-primary-500) !important;
    box-shadow: 0 2px 4px rgba(30, 79, 160, 0.25);
}

.solution-pill:not(.active) {
    background-color: var(--color-surface-alt);
    color: var(--color-text-muted);
}

.solution-pill:hover:not(.active) {
    background-color: var(--color-border);
}

/* Swiper Styles para Testimonials */
.inicio-testimonials-swiper .swiper-slide {
    width: 320px;
    height: 280px;
    pointer-events: none;
}

.inicio-testimonials-swiper .swiper-slide > div {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.inicio-testimonials-swiper .swiper-slide * {
    pointer-events: none;
}

.inicio-testimonials-swiper .swiper-wrapper {
    transition-timing-function: linear !important;
}
</style>

<!-- Hero Section (refeito) -->
<section class="relative bg-hero-gradient text-white">
    <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center justify-center">
            <!-- Coluna Esquerda: Texto -->
            <div class="lg:col-span-5 xl:col-span-6">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold mb-8">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                    Centro Operacional Fiscal
                </div>

                <!-- Título -->
                <h1 class="font-extrabold leading-tight tracking-tight text-4xl sm:text-5xl xl:text-6xl">
                    O HUB Fiscal & Compliance que transforma
                    <span class="block text-sped-hero">SPED em Ações e Relatórios</span>
                </h1>

                <!-- Subtítulo -->
                <p class="mt-6 text-lg sm:text-xl text-white/80 max-w-2xl">
                    Centralize arquivos fiscais, identifique pendências e riscos automaticamente, gere relatórios prontos para decisão e automatize cobranças/entregas pelo WhatsApp — com trilha de evidências.
                </p>

                <!-- CTAs -->
                <div class="mt-10">
                    <button class="inline-flex items-center justify-center gap-2 rounded-lg px-8 py-4 text-base font-semibold text-gray-900 shadow-lg hover:shadow-xl transition-all duration-200" style="background-color: var(--color-accent); box-shadow: 0 20px 50px -20px rgba(54, 211, 153, 0.5);">
                        Quero conhecer
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </div>

                <!-- Frase de apoio -->
                <p class="mt-6 text-sm text-white/70 max-w-2xl">
                    Feito para escritórios contábeis e empresas que precisam de controle, conformidade e velocidade, sem planilhas infinitas.
                </p>

                <!-- Pílulas de features -->
                <div class="mt-8 mb-8 lg:mb-12 flex flex-wrap md:flex-nowrap gap-6 text-white/80">
                    <div class="flex items-center gap-2 whitespace-nowrap"><span class="h-5 w-5 rounded-full flex items-center justify-center" style="background-color: var(--color-accent)"><span class="text-white text-xs leading-none">✓</span></span> Conformidade Total</div>
                    <div class="flex items-center gap-2 whitespace-nowrap"><span class="h-5 w-5 rounded-full flex items-center justify-center" style="background-color: var(--color-accent)"><span class="text-white text-xs leading-none">✓</span></span> Automação Inteligente</div>
                    <div class="flex items-center gap-2 whitespace-nowrap"><span class="h-5 w-5 rounded-full flex items-center justify-center" style="background-color: var(--color-accent)"><span class="text-white text-xs leading-none">✓</span></span> Trilha de Evidências</div>
                </div>
            </div>

            <!-- Coluna Direita: Mockup -->
            <div class="lg:col-span-7 xl:col-span-6">
                <div class="relative rounded-3xl border border-white/15 bg-white/5 p-4 shadow-lg shadow-blue-950/10">
                    <img
                        src="{{ asset('pictures/dashboard-mockup.jpg') }}"
                        alt="Mockup do dashboard ReformaTax"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                        width="1280" height="720"
                        class="rounded-2xl w-full h-auto object-cover"
                    >
                </div>
            </div>
        </div>
    </div>
    <!-- Curva inferior (côncava) -->
    <div class="pointer-events-none absolute inset-x-0 bottom-[-1px] leading-none">
        <!-- O preenchimento branco cria a sensação de que o azul termina com curvatura -->
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="var(--color-surface-alt)"></path>
        </svg>
    </div>
</section>

<!-- A Solução Section -->
<section class="bg-gray-50 pt-8 lg:pt-12 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Um centro operacional para o <span class="text-brand-gradient">ecossistema contábil</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                O HUB organiza o fluxo entre cliente ↔ escritório ↔ obrigações, automatiza triagens e entrega relatórios com evidência.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg p-6 border border-gray-200 hover:border-blue-500 transition-colors">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Recebe e Organiza</h3>
                <p class="text-gray-600">
                    Documentos organizados por CNPJ e competência automaticamente. Você nunca mais perde "qual foi o último arquivo".
                </p>
            </div>

            <div class="bg-white rounded-lg p-6 border border-gray-200 hover:border-blue-500 transition-colors">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Valida e Classifica</h3>
                <p class="text-gray-600">
                    Aponta pendências automaticamente com semáforo por competência: OK / Atenção / Pendência, com motivo e evidência.
                </p>
            </div>

            <div class="bg-white rounded-lg p-6 border border-gray-200 hover:border-blue-500 transition-colors">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Gera Relatórios</h3>
                <p class="text-gray-600">
                    Status fiscal, riscos, inconsistências e oportunidades. Relatórios prontos para decisão, não planilhas para interpretar.
                </p>
            </div>

            <div class="bg-white rounded-lg p-6 border border-gray-200 hover:border-blue-500 transition-colors">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Automatiza Comunicação</h3>
                <p class="text-gray-600">
                    Cobranças e entregas via WhatsApp e portal com contexto. Mensagens com empresa, pendência, prazo e link de envio.
                </p>
            </div>

            <div class="bg-white rounded-lg p-6 border border-gray-200 hover:border-blue-500 transition-colors">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Trilha de Auditoria</h3>
                <p class="text-gray-600">
                    Registro completo: quem enviou, quando, qual arquivo, qual evidência. Você para de "discutir sensação" e mostra fatos.
                </p>
            </div>

            <div class="bg-white rounded-lg p-6 border border-gray-200 hover:border-blue-500 transition-colors">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Motor de Regras</h3>
                <p class="text-gray-600">
                    Regras parametrizáveis que aprendem seu padrão. Classificação automática: gera crédito / atenção / não gera.
                </p>
            </div>
        </div>
    </div>
    <!-- Divisor ondulado: transição do cinza (#f9fafb) para o branco -->
    <div class="pointer-events-none leading-none mt-6 sm:mt-8">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#ffffff"></path>
        </svg>
    </div>
</section>

<!-- Soluções Section -->
<section id="solucoes" class="bg-white pt-20 pb-0 overflow-x-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Soluções que <span class="text-brand-gradient">transformam</span> a rotina
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Um ecossistema completo para alta performance
            </p>
        </div>

        <!-- Carrossel de Soluções -->
        <div class="solutions-carousel-container relative">
            <!-- Setas de Navegação -->
            <button class="carousel-arrow carousel-arrow-left absolute left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white rounded-full shadow-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-colors" data-direction="prev">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button class="carousel-arrow carousel-arrow-right absolute right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white rounded-full shadow-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-colors" data-direction="next">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <!-- Cards de Soluções -->
            <div class="solutions-cards-wrapper relative overflow-hidden mx-12">
                <div class="solutions-cards-track flex transition-transform duration-500 ease-in-out" style="transform: translateX(0px);">
                    <!-- Card Duplicado: Portal do Cliente (para loop infinito) -->
                    <div class="solution-card solution-card-duplicate flex-shrink-0 w-full px-4" data-index="duplicate" data-solution="portal">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <!-- Coluna Esquerda: Descrição -->
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="flex items-center justify-center gap-3 mb-8">
                                            <div class="w-12 h-12 brand-mark rounded-xl flex items-center justify-center">
                                                <img src="{{ asset('icone-gif/page-optimization.gif') }}" alt="Portal do Cliente" class="w-8 h-8 object-contain">
                                            </div>
                                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900">Portal do Cliente</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-xl lg:text-2xl text-gray-700 mb-6 leading-relaxed">
                                            Área exclusiva do cliente com checklist do mês, pendências e prazos claros. Envio simplificado de documentos.
                                        </p>
                                        
                                        <!-- Descrição Secundária (com bullet visual) -->
                                        <div class="mb-4">
                                            <p class="text-lg text-gray-600 leading-relaxed">
                                                Histórico completo de entregas e relatórios acessíveis. Permissões por perfil garantindo acesso adequado.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-8">
                                        <a href="#" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors text-lg">
                                            Saiba mais
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Headline sobre o mockup -->
                                    <div class="absolute top-8 left-1/2 -translate-x-1/2 z-10 w-full px-4">
                                        <h4 class="text-2xl lg:text-3xl font-bold text-white mb-4 text-center">
                                            Portal transparente e intuitivo
                                        </h4>
                                    </div>
                                    
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-16">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('icone-gif/page-optimization.gif') }}" alt="Portal do Cliente" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Portal do Cliente</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">Dashboard</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Checklist</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Histórico</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup (simplificado) -->
                                            <div class="p-6 bg-gray-50">
                                                <div class="grid grid-cols-2 gap-4 mb-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 1: Central de Documentos -->
                    <div class="solution-card active flex-shrink-0 w-full px-4" data-index="0" data-solution="documentos" style="opacity: 1 !important;">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <!-- Coluna Esquerda: Descrição -->
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="flex items-center justify-center gap-3 mb-8">
                                            <div class="w-12 h-12 brand-mark rounded-xl flex items-center justify-center">
                                                <img src="{{ asset('icone-gif/checklist.gif') }}" alt="Central de Documentos" class="w-8 h-8 object-contain">
                                            </div>
                                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900">Central de Documentos</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-xl lg:text-2xl text-gray-700 mb-6 leading-relaxed">
                                            Centralize todos os documentos contábeis da sua carteira de clientes. Tenha organização por pastas, datas e categorias com acesso seguro.
                                        </p>
                                        
                                        <!-- Descrição Secundária (com bullet visual) -->
                                        <div class="mb-4">
                                            <p class="text-lg text-gray-600 leading-relaxed">
                                                Compartilhamento ágil com sua equipe e com o cliente final. Tudo em nuvem, com backup automático e controle total de acesso.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-8">
                                        <a href="#" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors text-lg">
                                            Saiba mais
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Headline sobre o mockup -->
                                    <div class="absolute top-8 left-1/2 -translate-x-1/2 z-10 w-full px-4">
                                        <h4 class="text-2xl lg:text-3xl font-bold text-white mb-4 text-center">
                                            Documentos mais organizados e seguros
                                        </h4>
                                    </div>
                                    
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-16">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('icone-gif/checklist.gif') }}" alt="Central de Documentos" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Central de Documentos</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">Dashboard</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Documentos</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Relatórios</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup (simplificado) -->
                                            <div class="p-6 bg-gray-50">
                                                <div class="grid grid-cols-2 gap-4 mb-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Leitura e Diagnóstico -->
                    <div class="solution-card flex-shrink-0 w-full px-4" data-index="1" data-solution="diagnostico">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <!-- Coluna Esquerda: Descrição -->
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="flex items-center justify-center gap-3 mb-8">
                                            <div class="w-12 h-12 brand-mark rounded-xl flex items-center justify-center">
                                                <img src="{{ asset('icone-gif/analyse.gif') }}" alt="Leitura e Diagnóstico" class="w-8 h-8 object-contain">
                                            </div>
                                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900">Leitura e Diagnóstico</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-xl lg:text-2xl text-gray-700 mb-6 leading-relaxed">
                                            Importação e estruturação automática de SPED. Detecção inteligente de inconsistências e alertas para lacunas e divergências.
                                        </p>
                                        
                                        <!-- Descrição Secundária (com bullet visual) -->
                                        <div class="mb-4">
                                            <p class="text-lg text-gray-600 leading-relaxed">
                                                Semáforo visual por competência: OK / Atenção / Pendência, com motivo e evidência clara.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-8">
                                        <a href="#" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors text-lg">
                                            Saiba mais
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Headline sobre o mockup -->
                                    <div class="absolute top-8 left-1/2 -translate-x-1/2 z-10 w-full px-4">
                                        <h4 class="text-2xl lg:text-3xl font-bold text-white mb-4 text-center">
                                            Diagnóstico automático e inteligente
                                        </h4>
                                    </div>
                                    
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-16">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('icone-gif/analyse.gif') }}" alt="Leitura e Diagnóstico" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Leitura e Diagnóstico</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">Dashboard</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">SPED</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Alertas</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup (simplificado) -->
                                            <div class="p-6 bg-gray-50">
                                                <div class="grid grid-cols-2 gap-4 mb-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Motor de Regras -->
                    <div class="solution-card flex-shrink-0 w-full px-4" data-index="2" data-solution="regras">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <!-- Coluna Esquerda: Descrição -->
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="flex items-center justify-center gap-3 mb-8">
                                            <div class="w-12 h-12 brand-mark rounded-xl flex items-center justify-center">
                                                <img src="{{ asset('icone-gif/clipboard-gear.gif') }}" alt="Motor de Regras" class="w-8 h-8 object-contain">
                                            </div>
                                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900">Motor de Regras</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-xl lg:text-2xl text-gray-700 mb-6 leading-relaxed">
                                            Regras parametrizáveis por operação. Classificação automática que identifica: gera crédito / atenção / não gera.
                                        </p>
                                        
                                        <!-- Descrição Secundária (com bullet visual) -->
                                        <div class="mb-4">
                                            <p class="text-lg text-gray-600 leading-relaxed">
                                                Regras totalmente auditáveis e evolução contínua: a plataforma aprende seu padrão de escritório.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-8">
                                        <a href="#" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors text-lg">
                                            Saiba mais
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Headline sobre o mockup -->
                                    <div class="absolute top-8 left-1/2 -translate-x-1/2 z-10 w-full px-4">
                                        <h4 class="text-2xl lg:text-3xl font-bold text-white mb-4 text-center">
                                            Classificação que aprende seu padrão
                                        </h4>
                                    </div>
                                    
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-16">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('icone-gif/clipboard-gear.gif') }}" alt="Motor de Regras" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Motor de Regras</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">Dashboard</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Regras</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Histórico</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup (simplificado) -->
                                            <div class="p-6 bg-gray-50">
                                                <div class="grid grid-cols-2 gap-4 mb-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Compliance e Situação Fiscal -->
                    <div class="solution-card flex-shrink-0 w-full px-4" data-index="3" data-solution="compliance">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <!-- Coluna Esquerda: Descrição -->
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="flex items-center justify-center gap-3 mb-8">
                                            <div class="w-12 h-12 brand-mark rounded-xl flex items-center justify-center">
                                                <img src="{{ asset('icone-gif/secure-payment.gif') }}" alt="Compliance e Situação Fiscal" class="w-8 h-8 object-contain">
                                            </div>
                                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900">Compliance e Situação Fiscal</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-xl lg:text-2xl text-gray-700 mb-6 leading-relaxed">
                                            Painel completo de situação por CNPJ com mapa de regularidade e pendências. Alertas automáticos de vencimento.
                                        </p>
                                        
                                        <!-- Descrição Secundária (com bullet visual) -->
                                        <div class="mb-4">
                                            <p class="text-lg text-gray-600 leading-relaxed">
                                                Relatório de risco por empresa/fornecedor com evidências e histórico completo.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-8">
                                        <a href="#" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors text-lg">
                                            Saiba mais
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Headline sobre o mockup -->
                                    <div class="absolute top-8 left-1/2 -translate-x-1/2 z-10 w-full px-4">
                                        <h4 class="text-2xl lg:text-3xl font-bold text-white mb-4 text-center">
                                            Visão completa de compliance
                                        </h4>
                                    </div>
                                    
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-16">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('icone-gif/secure-payment.gif') }}" alt="Compliance e Situação Fiscal" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Compliance e Situação Fiscal</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">Dashboard</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Situação</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Riscos</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup (simplificado) -->
                                            <div class="p-6 bg-gray-50">
                                                <div class="grid grid-cols-2 gap-4 mb-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 5: Automação de Comunicação -->
                    <div class="solution-card flex-shrink-0 w-full px-4" data-index="4" data-solution="comunicacao">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <!-- Coluna Esquerda: Descrição -->
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="flex items-center justify-center gap-3 mb-8">
                                            <div class="w-12 h-12 brand-mark rounded-xl flex items-center justify-center">
                                                <img src="{{ asset('icone-gif/customer-service.gif') }}" alt="Automação de Comunicação" class="w-8 h-8 object-contain">
                                            </div>
                                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900">Automação de Comunicação</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-xl lg:text-2xl text-gray-700 mb-6 leading-relaxed">
                                            Cobrança automática de documentos por competência via WhatsApp e portal. Mensagens inteligentes com contexto completo.
                                        </p>
                                        
                                        <!-- Descrição Secundária (com bullet visual) -->
                                        <div class="mb-4">
                                            <p class="text-lg text-gray-600 leading-relaxed">
                                                Entrega automática de relatórios com confirmação e registro completo da conversa com trilha de auditoria.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-8">
                                        <a href="#" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors text-lg">
                                            Saiba mais
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Headline sobre o mockup -->
                                    <div class="absolute top-8 left-1/2 -translate-x-1/2 z-10 w-full px-4">
                                        <h4 class="text-2xl lg:text-3xl font-bold text-white mb-4 text-center">
                                            Comunicação automática e contextual
                                        </h4>
                                    </div>
                                    
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-16">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('icone-gif/customer-service.gif') }}" alt="Automação de Comunicação" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Automação de Comunicação</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">Dashboard</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">WhatsApp</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Histórico</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup (simplificado) -->
                                            <div class="p-6 bg-gray-50">
                                                <div class="grid grid-cols-2 gap-4 mb-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 6: Portal do Cliente -->
                    <div class="solution-card flex-shrink-0 w-full px-4" data-index="5" data-solution="portal">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <!-- Coluna Esquerda: Descrição -->
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="flex items-center justify-center gap-3 mb-8">
                                            <div class="w-12 h-12 brand-mark rounded-xl flex items-center justify-center">
                                                <img src="{{ asset('icone-gif/page-optimization.gif') }}" alt="Portal do Cliente" class="w-8 h-8 object-contain">
                                            </div>
                                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900">Portal do Cliente</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-xl lg:text-2xl text-gray-700 mb-6 leading-relaxed">
                                            Área exclusiva do cliente com checklist do mês, pendências e prazos claros. Envio simplificado de documentos.
                                        </p>
                                        
                                        <!-- Descrição Secundária (com bullet visual) -->
                                        <div class="mb-4">
                                            <p class="text-lg text-gray-600 leading-relaxed">
                                                Histórico completo de entregas e relatórios acessíveis. Permissões por perfil garantindo acesso adequado.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-8">
                                        <a href="#" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors text-lg">
                                            Saiba mais
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Headline sobre o mockup -->
                                    <div class="absolute top-8 left-1/2 -translate-x-1/2 z-10 w-full px-4">
                                        <h4 class="text-2xl lg:text-3xl font-bold text-white mb-4 text-center">
                                            Portal transparente e intuitivo
                                        </h4>
                                    </div>
                                    
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-16">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('icone-gif/page-optimization.gif') }}" alt="Portal do Cliente" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Portal do Cliente</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">Dashboard</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Checklist</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Histórico</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup (simplificado) -->
                                            <div class="p-6 bg-gray-50">
                                                <div class="grid grid-cols-2 gap-4 mb-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Duplicado: Central de Documentos (para loop infinito - fim) -->
                    <div class="solution-card solution-card-duplicate flex-shrink-0 w-full px-4" data-index="duplicate-end" data-solution="documentos">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <!-- Coluna Esquerda: Descrição -->
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="flex items-center justify-center gap-3 mb-8">
                                            <div class="w-12 h-12 brand-mark rounded-xl flex items-center justify-center">
                                                <img src="{{ asset('icone-gif/checklist.gif') }}" alt="Central de Documentos" class="w-8 h-8 object-contain">
                                            </div>
                                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900">Central de Documentos</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-xl lg:text-2xl text-gray-700 mb-6 leading-relaxed">
                                            Centralize todos os documentos contábeis da sua carteira de clientes. Tenha organização por pastas, datas e categorias com acesso seguro.
                                        </p>
                                        
                                        <!-- Descrição Secundária (com bullet visual) -->
                                        <div class="mb-4">
                                            <p class="text-lg text-gray-600 leading-relaxed">
                                                Compartilhamento ágil com sua equipe e com o cliente final. Tudo em nuvem, com backup automático e controle total de acesso.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-8">
                                        <a href="#" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors text-lg">
                                            Saiba mais
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Headline sobre o mockup -->
                                    <div class="absolute top-8 left-1/2 -translate-x-1/2 z-10 w-full px-4">
                                        <h4 class="text-2xl lg:text-3xl font-bold text-white mb-4 text-center">
                                            Documentos mais organizados e seguros
                                        </h4>
                                    </div>
                                    
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-16">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('icone-gif/checklist.gif') }}" alt="Central de Documentos" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Central de Documentos</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">Dashboard</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Documentos</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Relatórios</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup (simplificado) -->
                                            <div class="p-6 bg-gray-50">
                                                <div class="grid grid-cols-2 gap-4 mb-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-24 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="h-20 bg-gray-100 rounded"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navegação por Pills -->
            <div class="flex justify-center gap-3 mt-8 flex-wrap">
                <button class="solution-pill active px-6 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="documentos">
                    Central de Documentos
                </button>
                <button class="solution-pill px-6 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="diagnostico">
                    Leitura e Diagnóstico
                </button>
                <button class="solution-pill px-6 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="regras">
                    Motor de Regras
                </button>
                <button class="solution-pill px-6 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="compliance">
                    Compliance Fiscal
                </button>
                <button class="solution-pill px-6 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="comunicacao">
                    Automação WhatsApp
                </button>
                <button class="solution-pill px-6 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="portal">
                    Portal do Cliente
                </button>
            </div>
        </div>
    </div>
    <!-- Divisor ondulado: transição do cinza para o branco -->
    <div class="pointer-events-none leading-none mt-6 sm:mt-8">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#ffffff"></path>
        </svg>
    </div>
</section>

<!-- Benefícios Section -->
<section id="beneficios" class="bg-gray-50 pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Por que escolher <span class="text-brand-gradient">nossa solução</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Transforme o caos de documentos e pendências em organização, controle e previsibilidade
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('icone-gif/financial-decline.gif') }}" alt="Financial Decline" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Redução de até 30% na carga tributária com planejamento adequado</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('icone-gif/contract.gif') }}" alt="Contract" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Conformidade total com rastreabilidade e evidências completas</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('icone-gif/process.gif') }}" alt="Process" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Processos automatizados que economizam tempo e recursos</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('icone-gif/budgeting.gif') }}" alt="Budgeting" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Visibilidade completa dos impactos financeiros em tempo real</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('icone-gif/customer-service.gif') }}" alt="Customer Service" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Equipe de especialistas disponível para suporte contínuo</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('icone-gif/investment.gif') }}" alt="Investment" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">ROI comprovado em menos de 6 meses</p>
            </div>
        </div>
    </div>
    <!-- Divisor ondulado: transição do cinza (#f9fafb) para o branco -->
    <div class="pointer-events-none leading-none">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#ffffff"></path>
        </svg>
    </div>
</section>

<!-- IA Financeira Section -->
<section id="relatorios" class="bg-white pt-16 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <div class="text-sm font-semibold text-blue-600 uppercase tracking-wide">
                    Gestão financeira assistida por IA
                </div>
                <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 leading-tight">
                    Deixe nossa IA cuidar da gestão do seu negócio
                </h2>
                <p class="text-lg text-gray-600">
                    Sistema simples, intuitivo e poderoso: acompanhe fluxo de caixa, automatize cobranças, organize contas e ganhe agilidade com inteligência artificial integrada.
                </p>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="#"
                       class="inline-flex items-center justify-center px-6 py-3 rounded-full text-base font-semibold text-blue-900 bg-yellow-400 hover:bg-yellow-300 transition">
                        Teste grátis
                    </a>
                    <a href="#"
                       class="inline-flex items-center justify-center px-6 py-3 rounded-full text-base font-semibold text-blue-600 border-2 border-blue-500 hover:text-white hover:bg-blue-500 transition">
                        Saiba mais
                    </a>
                </div>
            </div>
            <div class="relative">
                <div class="absolute inset-0 -z-10">
                    <div class="w-48 h-48 bg-blue-100 rounded-full blur-3xl opacity-50 absolute top-0 left-6"></div>
                    <div class="w-40 h-40 bg-yellow-100 rounded-full blur-3xl opacity-50 absolute bottom-6 right-0"></div>
                </div>
                <img src="{{ asset('pictures/macbook-mockup.png') }}" alt="Demonstração de fluxo de caixa em um notebook" class="w-full max-w-2xl mx-auto drop-shadow-2xl">
            </div>
        </div>
    </div>
    <!-- Divisor ondulado: transição do branco para o cinza (#f9fafb) -->
    <div class="pointer-events-none leading-none">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#f9fafb"></path>
        </svg>
    </div>
</section>

<!-- Para Quem É Section -->
<section class="bg-gray-50 pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Para <span class="text-brand-gradient">quem é</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                O HUB atende escritórios contábeis, empresas e BPO financeiro que precisam de controle, conformidade e velocidade
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg p-8 border border-gray-200 hover:border-blue-500 transition-colors">
                <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Escritórios Contábeis</h3>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Padroniza o mês, reduz retrabalho e dá previsibilidade</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Menos cobrança manual, mais controle e evidência</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Organização automática de documentos e pendências</span>
                    </li>
                </ul>
            </div>

            <div class="bg-white rounded-lg p-8 border border-gray-200 hover:border-blue-500 transition-colors">
                <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Empresas</h3>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Transparência do que falta e do que foi entregue</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Menos risco, menos surpresa, mais governança</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Portal do cliente com checklist e prazos claros</span>
                    </li>
                </ul>
            </div>

            <div class="bg-white rounded-lg p-8 border border-gray-200 hover:border-blue-500 transition-colors">
                <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">BPO Financeiro</h3>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Rotina de documentos e pendências com rastreabilidade</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Comunicação e evidência para aprovação/entrega</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Controle total com trilha de auditoria completa</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Divisor ondulado: transição do cinza (#f9fafb) para o branco -->
    <div class="pointer-events-none leading-none mt-6 sm:mt-8">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#ffffff"></path>
        </svg>
    </div>
</section>

<!-- Diferenciais Section -->
<section class="bg-white pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Por que o HUB é <span class="text-brand-gradient">diferente</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Não substitui seu sistema contábil/ERP: integra ao ecossistema e resolve o que ninguém quer fazer manualmente
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-blue-500 text-white rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Integra, não substitui</h3>
                        <p class="text-gray-600">
                            Não substitui Domínio/Alterdata/Contmatic. O HUB atua como uma camada de organização, diagnóstico, relatórios e automação, reduzindo caos e retrabalho.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-blue-500 text-white rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Operacional + Inteligência</h3>
                        <p class="text-gray-600">
                            Diagnóstico vira ação automaticamente. Não é só análise: é organização, validação, cobrança e entrega no mesmo lugar.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-blue-500 text-white rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Trilha de Evidência</h3>
                        <p class="text-gray-600">
                            Você para de "discutir sensação" e passa a mostrar fatos. Quem enviou, quando, qual arquivo, qual evidência — tudo registrado.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-blue-500 text-white rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Automação Real</h3>
                        <p class="text-gray-600">
                            Mensagens, cobranças e entregas com histórico. Não é só disparo: é comunicação contextualizada e rastreável.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Divisor ondulado: transição do branco para o cinza (#f9fafb) -->
    <div class="pointer-events-none leading-none mt-6 sm:mt-8">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#f9fafb"></path>
        </svg>
    </div>
</section>

<!-- Segurança e LGPD Section -->
<section class="bg-gray-50 pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Segurança e <span class="text-brand-gradient">LGPD</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Proteção e conformidade para dados fiscais e empresariais sensíveis
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg p-6 border border-gray-200 text-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Controle de Acesso</h3>
                <p class="text-gray-600 text-sm">Por perfil e empresa. Cada usuário acessa apenas o que precisa.</p>
            </div>

            <div class="bg-white rounded-lg p-6 border border-gray-200 text-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Histórico e Auditoria</h3>
                <p class="text-gray-600 text-sm">Registro completo de ações: quem fez o quê, quando e por quê.</p>
            </div>

            <div class="bg-white rounded-lg p-6 border border-gray-200 text-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Armazenamento Seguro</h3>
                <p class="text-gray-600 text-sm">Segregação por cliente com criptografia e backups regulares.</p>
            </div>

            <div class="bg-white rounded-lg p-6 border border-gray-200 text-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Conformidade LGPD</h3>
                <p class="text-gray-600 text-sm">Boas práticas para tratamento de dados fiscais e empresariais.</p>
            </div>
        </div>
    </div>
    <!-- Divisor ondulado: transição do cinza (#f9fafb) para o branco -->
    <div class="pointer-events-none leading-none mt-6 sm:mt-8">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#ffffff"></path>
        </svg>
    </div>
</section>

<!-- Estatísticas Section -->
<section class="bg-white pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Números que <span class="text-brand-gradient">Impressionam</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Resultados reais de empresas que confiaram em nossa solução
            </p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-gray-50 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-8 border border-gray-200 hover:border-blue-500 text-center">
                <div class="text-5xl font-bold text-blue-500 mb-4">500+</div>
                <div class="text-gray-600 font-medium">Empresas Atendidas</div>
            </div>
            <div class="bg-gray-50 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-8 border border-gray-200 hover:border-blue-500 text-center">
                <div class="text-5xl font-bold text-blue-500 mb-4">R$ 2.5M</div>
                <div class="text-gray-600 font-medium">Economizados</div>
            </div>
            <div class="bg-gray-50 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-8 border border-gray-200 hover:border-blue-500 text-center">
                <div class="text-5xl font-bold text-blue-500 mb-4">98%</div>
                <div class="text-gray-600 font-medium">Satisfação</div>
            </div>
            <div class="bg-gray-50 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-8 border border-gray-200 hover:border-blue-500 text-center">
                <div class="text-5xl font-bold text-blue-500 mb-4">24/7</div>
                <div class="text-gray-600 font-medium">Suporte</div>
            </div>
        </div>
    </div>
    <!-- Divisor ondulado: transição do branco para o cinza (#f9fafb) -->
    <div class="pointer-events-none leading-none mt-6 sm:mt-8">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#f9fafb"></path>
        </svg>
    </div>
</section>


<!-- Depoimentos Section -->
<section class="bg-white pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                O que nossos <span class="text-brand-gradient">clientes dizem</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Resultados reais de escritórios contábeis e empresas que transformaram seu dia a dia com o HUB
            </p>
        </div>

        <!-- Swiper Testimonials -->
        <div class="swiper inicio-testimonials-swiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">M</div>
                        <div>
                                <div class="font-semibold text-gray-900">Maria Silva</div>
                                <div class="text-sm text-gray-600">CEO, TechCorp</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "A solução nos ajudou a economizar mais de R$ 500 mil em impostos no primeiro ano. O ROI foi impressionante!"
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">J</div>
                        <div>
                                <div class="font-semibold text-gray-900">João Santos</div>
                                <div class="text-sm text-gray-600">Diretor Financeiro, Inovação Ltda</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "A automação dos processos fiscais reduziu nosso tempo de trabalho em 70%. Altamente recomendado!"
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">A</div>
                        <div>
                                <div class="font-semibold text-gray-900">Ana Costa</div>
                                <div class="text-sm text-gray-600">Contadora, Empresa ABC</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "A plataforma é intuitiva e os especialistas são excepcionais. Nosso compliance está 100% em dia."
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">C</div>
                        <div>
                                <div class="font-semibold text-gray-900">Carlos Mendes</div>
                                <div class="text-sm text-gray-600">Gerente Financeiro, Comércio Plus</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "Os relatórios prontos para decisão são um diferencial. Não preciso mais ficar interpretando planilhas. O semáforo por competência mostra na hora o que está OK, o que precisa atenção e o que é crítico."
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">L</div>
                        <div>
                                <div class="font-semibold text-gray-900">Lucia Ferreira</div>
                                <div class="text-sm text-gray-600">Diretora, BPO Financeiro Integrado</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "A trilha de evidências é perfeita para nosso trabalho. Conseguimos mostrar aos clientes exatamente o que foi enviado, quando e por quem. Isso eliminou 100% das discussões sobre 'quem enviou o quê'."
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">R</div>
                        <div>
                                <div class="font-semibold text-gray-900">Roberto Alves</div>
                                <div class="text-sm text-gray-600">Sócio, Escritório Alves Contabilidade</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "O motor de regras aprendeu nosso padrão e agora classifica automaticamente. Isso nos poupou horas de trabalho manual. E o melhor: não substitui nosso sistema, só adiciona inteligência em cima."
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">F</div>
                        <div>
                                <div class="font-semibold text-gray-900">Fernanda Lima</div>
                                <div class="text-sm text-gray-600">CFO, Indústria Moderna</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "A transição para o novo sistema tributário foi muito mais simples com o suporte da equipe. Recomendo!"
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">P</div>
                        <div>
                                <div class="font-semibold text-gray-900">Paulo Rodrigues</div>
                                <div class="text-sm text-gray-600">Diretor, Varejo Digital</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "O diagnóstico automático de SPED identifica inconsistências que antes passavam despercebidas. Isso nos ajudou a evitar multas e corrigir problemas antes que virassem dor de cabeça."
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <!-- Duplicação para loop contínuo -->
                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">M</div>
                        <div>
                                <div class="font-semibold text-gray-900">Maria Silva</div>
                                <div class="text-sm text-gray-600">CEO, TechCorp</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "A solução nos ajudou a economizar mais de R$ 500 mil em impostos no primeiro ano. O ROI foi impressionante!"
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">J</div>
                        <div>
                                <div class="font-semibold text-gray-900">João Santos</div>
                                <div class="text-sm text-gray-600">Diretor Financeiro, Inovação Ltda</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "A automação dos processos fiscais reduziu nosso tempo de trabalho em 70%. Altamente recomendado!"
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">A</div>
                        <div>
                                <div class="font-semibold text-gray-900">Ana Costa</div>
                                <div class="text-sm text-gray-600">Contadora, Empresa ABC</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "A plataforma é intuitiva e os especialistas são excepcionais. Nosso compliance está 100% em dia."
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <!-- Duplicação para animação CSS contínua -->
                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">M</div>
                        <div>
                                <div class="font-semibold text-gray-900">Maria Silva</div>
                                <div class="text-sm text-gray-600">CEO, TechCorp</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "A solução nos ajudou a economizar mais de R$ 500 mil em impostos no primeiro ano. O ROI foi impressionante!"
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">J</div>
                        <div>
                                <div class="font-semibold text-gray-900">João Santos</div>
                                <div class="text-sm text-gray-600">Diretor Financeiro, Inovação Ltda</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "A automação dos processos fiscais reduziu nosso tempo de trabalho em 70%. Altamente recomendado!"
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">C</div>
                        <div>
                                <div class="font-semibold text-gray-900">Carlos Oliveira</div>
                                <div class="text-sm text-gray-600">Gerente, Comércio & Cia</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "Excelente suporte e resultados comprovados. Nossa empresa está muito mais organizada fiscalmente."
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">F</div>
                        <div>
                                <div class="font-semibold text-gray-900">Fernanda Lima</div>
                                <div class="text-sm text-gray-600">CFO, Indústria Moderna</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "A transição para o novo sistema tributário foi muito mais simples com o suporte da equipe. Recomendo!"
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-lg">A</div>
                        <div>
                                <div class="font-semibold text-gray-900">Ana Costa</div>
                                <div class="text-sm text-gray-600">Contadora, Empresa ABC</div>
                        </div>
                    </div>
                        <p class="text-gray-600 mb-4 italic">
                        "A plataforma é intuitiva e os especialistas são excepcionais. Nosso compliance está 100% em dia."
                    </p>
                        <div class="text-yellow-500 text-lg">★★★★★</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Divisor ondulado: transição do branco para o cinza (#f9fafb) -->
    <div class="pointer-events-none leading-none mt-6 sm:mt-8">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#f9fafb"></path>
        </svg>
    </div>
</section>

<!-- FAQ Section -->
<section id="faq" class="bg-gray-50 pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Perguntas <span class="text-brand-gradient">Frequentes</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Tire suas dúvidas sobre o HUB Fiscal & Compliance e como ele pode transformar seu dia a dia
            </p>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="faq-item border border-gray-200 rounded-lg mb-4 hover:border-blue-500 transition-colors overflow-hidden">
                <button class="faq-question w-full text-left px-6 py-4 font-semibold text-gray-900 hover:text-blue-500 flex justify-between items-center">
                    <span>Como funciona a integração com sistemas existentes?</span>
                    <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer hidden">
                    <div class="px-6 py-4 text-gray-600 bg-white border-t border-gray-100">
                        O HUB trabalha como uma camada acima dos seus sistemas. Você pode importar dados, documentos e SPEDs de qualquer fonte. Ele organiza, valida e gera relatórios sem precisar substituir ou modificar seus sistemas contábeis atuais.
                    </div>
                </div>
            </div>

            <div class="faq-item border border-gray-200 rounded-lg mb-4 hover:border-blue-500 transition-colors overflow-hidden">
                <button class="faq-question w-full text-left px-6 py-4 font-semibold text-gray-900 hover:text-blue-500 flex justify-between items-center">
                    <span>Como sua solução ajuda na adaptação à reforma?</span>
                    <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer hidden">
                    <div class="px-6 py-4 text-gray-600 bg-white border-t border-gray-100">
                        Nossa plataforma oferece análise detalhada de impactos, simulações precisas, automação de processos fiscais e orientação especializada para garantir que sua empresa esteja 100% preparada e em conformidade.
                    </div>
                </div>
            </div>

            <div class="faq-item border border-gray-200 rounded-lg mb-4 hover:border-blue-500 transition-colors overflow-hidden">
                <button class="faq-question w-full text-left px-6 py-4 font-semibold text-gray-900 hover:text-blue-500 flex justify-between items-center">
                    <span>Quais empresas podem se beneficiar?</span>
                    <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer hidden">
                    <div class="px-6 py-4 text-gray-600 bg-white border-t border-gray-100">
                        Empresas de todos os portes e setores podem se beneficiar. A reforma impacta todas as organizações que lidam com tributação, desde pequenos negócios até grandes corporações.
                    </div>
                </div>
            </div>

            <div class="faq-item border border-gray-200 rounded-lg mb-4 hover:border-blue-500 transition-colors overflow-hidden">
                <button class="faq-question w-full text-left px-6 py-4 font-semibold text-gray-900 hover:text-blue-500 flex justify-between items-center">
                    <span>Qual é o prazo ideal para começar a preparação?</span>
                    <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer hidden">
                    <div class="px-6 py-4 text-gray-600 bg-white border-t border-gray-100">
                        O ideal é começar imediatamente. Quanto antes sua empresa iniciar a preparação, mais tempo terá para implementar mudanças necessárias, treinar equipes e otimizar processos antes da entrada em vigor.
                    </div>
                </div>
            </div>

            <div class="faq-item border border-gray-200 rounded-lg mb-4 hover:border-blue-500 transition-colors overflow-hidden">
                <button class="faq-question w-full text-left px-6 py-4 font-semibold text-gray-900 hover:text-blue-500 flex justify-between items-center">
                    <span>Como funciona o processo de implementação?</span>
                    <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer hidden">
                    <div class="px-6 py-4 text-gray-600 bg-white border-t border-gray-100">
                        Nosso processo é dividido em etapas: análise inicial, diagnóstico detalhado, planejamento estratégico, implementação de soluções e acompanhamento contínuo. Todo o processo é personalizado conforme as necessidades de cada empresa.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Divisor ondulado: transição do cinza (#f9fafb) para o branco -->
    <div class="pointer-events-none leading-none mt-6 sm:mt-8">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#ffffff"></path>
        </svg>
    </div>
</section>

<!-- Contato Section -->
<section id="contato" class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Fale com <span class="text-brand-gradient">nossos especialistas</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Agende uma demonstração gratuita e descubra como o HUB pode transformar o dia a dia do seu escritório ou empresa
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div>
                <form id="space-y-6" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="name" placeholder="Nome completo" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <input type="email" name="email" placeholder="Email corporativo" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="tel" name="phone" placeholder="Telefone" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <input type="text" name="company" placeholder="Empresa" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <textarea name="message" placeholder="Como podemos ajudar sua empresa?" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent h-32 resize-none"></textarea>
                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-4 px-6 rounded-lg transition-colors text-lg">
                        Enviar Mensagem
                    </button>
                </form>
            </div>

            <div>
                <div class="bg-gray-50 rounded-lg p-8 border border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Informações de Contato</h3>
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 bg-blue-500 text-white rounded-lg flex items-center justify-center">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 font-medium">Email</div>
                            <div class="text-gray-900 font-semibold">contato@reformatributaria.com</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 bg-blue-500 text-white rounded-lg flex items-center justify-center">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 font-medium">Telefone</div>
                            <div class="text-gray-900 font-semibold">(11) 99999-9999</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 bg-blue-500 text-white rounded-lg flex items-center justify-center">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 font-medium">Localização</div>
                            <div class="text-gray-900 font-semibold">São Paulo, Brasil</div>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-8 mt-8">
                    <h3>Consultoria Gratuita</h3>
                    <p>
                        Agende agora uma demonstração sem compromisso e descubra como o HUB pode organizar seu dia a dia.
                    </p>
                    <a href="#" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors inline-block">Agendar Agora</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.inicio-testimonials-swiper {
    overflow: hidden;
}

.inicio-testimonials-swiper .swiper-wrapper {
    transition-timing-function: linear !important;
    -webkit-transition-timing-function: linear !important;
    animation: scrollContinuo 40s linear infinite;
    animation-play-state: running;
}

.inicio-testimonials-swiper:hover .swiper-wrapper {
    animation-play-state: paused;
}

@keyframes scrollContinuo {
    0% {
        transform: translate3d(0, 0, 0);
    }
    100% {
        transform: translate3d(calc(-100% / 2), 0, 0);
    }
}

.inicio-testimonials-swiper .swiper-slide {
    flex-shrink: 0;
}
</style>

<script>
// Inicializador reutilizável para o carrossel da seção "Soluções que transformam a rotina"
window.initSolucoesCarousel = function() {
    console.log('[Carrossel] Função initSolucoesCarousel chamada');
    
    // Flag global para evitar múltiplas inicializações
    if (window._solucoesCarouselInitialized) {
        console.log('[Carrossel] Já inicializado, ignorando...');
        return;
    }
    
    try {
        let retryCount = 0;
        const maxRetries = 10;
        
        function initCarousel() {
            try {
                const track = document.querySelector('.solutions-cards-track');
                const cards = document.querySelectorAll('.solution-card:not(.solution-card-duplicate)');
                const pills = document.querySelectorAll('.solution-pill');
                const prevArrow = document.querySelector('.carousel-arrow-left');
                const nextArrow = document.querySelector('.carousel-arrow-right');
                const wrapper = document.querySelector('.solutions-cards-wrapper');

                console.log('[Carrossel] Tentativa', retryCount + 1, '- Elementos encontrados:', {
                    track: !!track,
                    cards: cards.length,
                    pills: pills.length,
                    prevArrow: !!prevArrow,
                    nextArrow: !!nextArrow,
                    wrapper: !!wrapper
                });

                if (!track || !cards.length || !wrapper) {
                    if (retryCount < maxRetries) {
                        retryCount++;
                        setTimeout(initCarousel, 150);
                    } else {
                        console.error('[Carrossel] Elementos não encontrados após', maxRetries, 'tentativas');
                    }
                    return;
                }

                // Marcar como inicializado
                window._solucoesCarouselInitialized = true;
                console.log('[Carrossel] Inicializando com', cards.length, 'cards');
                
                const totalCards = cards.length;
                let currentIndex = 0;
                let cardWidth = 0;
                let cardSpacing = 0;
                let firstCardLeftInTrack = 0;

                // Garantir que o primeiro card esteja ativo desde o início
                cards.forEach((card, i) => {
                    if (i === 0) {
                        card.classList.add('active');
                        // Forçar opacidade via estilo inline para garantir visibilidade
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    } else {
                        card.classList.remove('active');
                        card.style.opacity = '';
                        card.style.transform = '';
                    }
                });

                // Garantir que o primeiro pill esteja ativo
                pills.forEach((pill, i) => {
                    if (i === 0) {
                        pill.classList.add('active');
                    } else {
                        pill.classList.remove('active');
                    }
                });

                function updateReferenceValues() {
                    try {
                        track.style.transform = 'translateX(0px)';

                        requestAnimationFrame(() => {
                            requestAnimationFrame(() => {
                                try {
                                    if (cards.length < 2) {
                                        if (cards.length === 1) {
                                            const firstCard = cards[0];
                                            const firstCardRect = firstCard.getBoundingClientRect();
                                            const wrapperRect = wrapper.getBoundingClientRect();
                                            const wrapperCenter = wrapperRect.width / 2;
                                            const cardCenter = firstCardRect.width / 2;
                                            const translateX = wrapperCenter - cardCenter - (firstCardRect.left - wrapperRect.left);
                                            track.style.transform = `translateX(${translateX}px)`;
                                        }
                                        return;
                                    }

                                    const firstCard = cards[0];
                                    const secondCard = cards[1];
                                    if (!firstCard || !secondCard) return;

                                    const firstCardRect = firstCard.getBoundingClientRect();
                                    const secondCardRect = secondCard.getBoundingClientRect();
                                    const trackRect = track.getBoundingClientRect();

                                    cardWidth = firstCardRect.width;
                                    firstCardLeftInTrack = firstCardRect.left - trackRect.left;
                                    cardSpacing = secondCardRect.left - firstCardRect.left;

                                    if (cardWidth > 0 && cardSpacing > 0 && !isNaN(cardSpacing) && !isNaN(firstCardLeftInTrack)) {
                                        updateCarousel(currentIndex);
                                    }
                                } catch (error) {
                                    console.error('Erro em updateReferenceValues:', error);
                                }
                            });
                        });
                    } catch (error) {
                        console.error('Erro ao atualizar valores de referência:', error);
                    }
                }

                function updateCarousel(index) {
                    try {
                        if (cardWidth === 0 || cardSpacing === 0) {
                            setTimeout(() => updateReferenceValues(), 50);
                            return;
                        }

                        const wrapperRect = wrapper.getBoundingClientRect();
                        const activeCardLeftInTrack = firstCardLeftInTrack + (index * cardSpacing);
                        const wrapperCenter = wrapperRect.width / 2;
                        const desiredCardLeft = wrapperCenter - (cardWidth / 2);
                        const translateX = desiredCardLeft - activeCardLeftInTrack;

                        track.style.transform = `translateX(${translateX}px)`;

                        cards.forEach((card, i) => {
                            if (i === index) {
                                card.classList.add('active');
                                // Forçar opacidade via estilo inline
                                card.style.opacity = '1';
                                card.style.transform = 'scale(1)';
                            } else {
                                card.classList.remove('active');
                                card.style.opacity = '';
                                card.style.transform = '';
                            }
                        });

                        pills.forEach((pill, i) => {
                            if (i === index) {
                                pill.classList.add('active');
                            } else {
                                pill.classList.remove('active');
                            }
                        });
                    } catch (error) {
                        console.error('Erro ao atualizar carrossel:', error);
                    }
                }

                // Função para navegar para o próximo
                function goNext() {
                    try {
                        currentIndex = (currentIndex + 1) % totalCards;
                        updateCarousel(currentIndex);
                    } catch (error) {
                        console.error('Erro ao navegar para próximo:', error);
                    }
                }

                // Função para navegar para o anterior
                function goPrev() {
                    try {
                        currentIndex = (currentIndex - 1 + totalCards) % totalCards;
                        updateCarousel(currentIndex);
                    } catch (error) {
                        console.error('Erro ao navegar para anterior:', error);
                    }
                }

                // Configurar setas de navegação
                if (prevArrow) {
                    prevArrow.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        goPrev();
                    };
                }

                if (nextArrow) {
                    nextArrow.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        goNext();
                    };
                }

                // Configurar pills
                pills.forEach((pill, index) => {
                    pill.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        currentIndex = index;
                        updateCarousel(currentIndex);
                    };
                });

                // Inicializar posicionamento
                updateReferenceValues();

                // Resize handler
                let resizeTimer;
                const resizeHandler = function() {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(() => {
                        updateReferenceValues();
                    }, 250);
                };
                
                window.removeEventListener('resize', resizeHandler);
                window.addEventListener('resize', resizeHandler);
            } catch (error) {
                console.error('Erro ao inicializar carrossel:', error);
            }
        }

        // Executar inicialização imediatamente e também após DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                console.log('[Carrossel] DOMContentLoaded - inicializando...');
                initCarousel();
            });
        } else {
            console.log('[Carrossel] DOM já pronto - inicializando...');
            setTimeout(initCarousel, 100);
        }
        
        // Também tentar após um pequeno delay para garantir
        setTimeout(function() {
            if (!window._solucoesCarouselInitialized) {
                console.log('[Carrossel] Tentativa adicional após delay...');
                initCarousel();
            }
        }, 500);
    } catch (error) {
        console.error('[Carrossel] Erro crítico ao inicializar carrossel de soluções:', error);
    }
};

// Tentar inicializar imediatamente se a função já estiver definida
if (typeof window.initSolucoesCarousel === 'function') {
    console.log('[Carrossel] Executando inicialização imediata...');
    window.initSolucoesCarousel();
}
</script>

<!-- Scripts carregados no layout -->
