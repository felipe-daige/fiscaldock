
<style>
/* Solutions Carousel Styles */
.solutions-carousel-container {
    min-height: 700px;
    position: relative;
}

.solutions-cards-wrapper {
    position: relative;
    overflow: hidden;
    margin: 0 8%;
}

.solutions-cards-track {
    display: flex;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform;
    align-items: stretch;
}

.solution-card {
    flex-shrink: 0;
    width: 84%;
    padding: 0 2%;
    transition: opacity 0.3s, transform 0.3s;
    position: relative;
    height: 700px; /* Altura fixa uniforme */
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
}

.solution-card .bg-gray-50 {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}

.solution-card.active {
    opacity: 1;
    transform: scale(1);
    z-index: 10;
}

.solution-card:not(.active) {
    opacity: 0.6;
    transform: scale(0.96);
}

/* Responsividade */
@media (max-width: 1024px) {
    .solutions-cards-wrapper {
        margin: 0 5%;
    }
    
    .solution-card {
        width: 90%;
        padding: 0 2.5%;
    }
}

@media (max-width: 768px) {
    .solutions-cards-wrapper {
        margin: 0 2%;
        overflow: hidden;
    }
    
    .solution-card {
        width: 96%;
        padding: 0 2%;
    }
    
    .solution-card:not(.active) {
        opacity: 0;
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
        min-height: 700px;
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
    background-color: white !important;
    color: #111827 !important;
    border-color: #10b981 !important;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
}

.solution-pill:not(.active) {
    background-color: #f3f4f6;
    color: #374151;
}

.solution-pill:hover:not(.active) {
    background-color: #e5e7eb;
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
<section class="relative bg-gradient-to-br from-[#0b1f3a] via-[#133a73] to-[#1e4fa0] text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Coluna Esquerda: Texto -->
            <div class="lg:col-span-5 xl:col-span-6">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold mb-8">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    Centro Operacional Fiscal
                </div>

                <!-- Título -->
                <h1 class="font-extrabold leading-tight tracking-tight text-4xl sm:text-5xl xl:text-6xl">
                    O HUB Fiscal & Compliance que transforma
                    <span class="block text-emerald-400">SPED em Ações e Relatórios</span>
                </h1>

                <!-- Subtítulo -->
                <p class="mt-6 text-lg sm:text-xl text-white/80 max-w-2xl">
                    Centralize arquivos fiscais, identifique pendências e riscos automaticamente, gere relatórios prontos para decisão e automatize cobranças/entregas pelo WhatsApp — com trilha de evidências.
                </p>

                <!-- CTAs -->
                <div class="mt-10 flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('agendar') }}" data-link class="inline-flex items-center justify-center rounded-xl bg-blue-500 hover:bg-blue-600 px-6 py-4 text-base font-semibold shadow-lg shadow-blue-900/30">
                        Agendar demonstração
                        <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="#relatorios" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/5 hover:bg-white/10 px-6 py-4 text-base font-semibold text-white">
                        <svg class="mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M6 4l12 6-12 6V4z"/></svg>
                        Ver exemplos de relatórios
                    </a>
                </div>

                <!-- Frase de apoio -->
                <p class="mt-6 text-sm text-white/70 max-w-2xl">
                    Feito para escritórios contábeis e empresas que precisam de controle, conformidade e velocidade, sem planilhas infinitas.
                </p>

                <!-- Pílulas de features -->
                <div class="mt-8 flex flex-wrap md:flex-nowrap gap-6 text-white/80">
                    <div class="flex items-center gap-2 whitespace-nowrap"><span class="h-5 w-5 rounded-full flex items-center justify-center" style="background-color:#36d399"><span class="text-white text-xs leading-none">✓</span></span> Conformidade Total</div>
                    <div class="flex items-center gap-2 whitespace-nowrap"><span class="h-5 w-5 rounded-full flex items-center justify-center" style="background-color:#36d399"><span class="text-white text-xs leading-none">✓</span></span> Automação Inteligente</div>
                    <div class="flex items-center gap-2 whitespace-nowrap"><span class="h-5 w-5 rounded-full flex items-center justify-center" style="background-color:#36d399"><span class="text-white text-xs leading-none">✓</span></span> Trilha de Evidências</div>
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
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#f9fafb"></path>
        </svg>
    </div>
</section>

<!-- O Problema Section -->
<section class="bg-white pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                O que hoje <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">consome tempo e cria risco</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Os desafios que escritórios contábeis e empresas enfrentam diariamente
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Arquivos Espalhados</h3>
                <p class="text-gray-600">
                    SPED, XML, PDFs e planilhas sem histórico confiável. Documentos que "sumem" e ninguém sabe onde estão.
                </p>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Pendências no Fim do Mês</h3>
                <p class="text-gray-600">
                    Corrida desesperada para fechar obrigações. Tudo vira urgência quando o prazo está batendo na porta.
                </p>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Cliente "Já Enviou"</h3>
                <p class="text-gray-600">
                    Cliente jura que enviou, mas ninguém acha. O contador vira o culpado e perde tempo procurando o que não existe.
                </p>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Falta de Visão</h3>
                <p class="text-gray-600">
                    O que está certo? O que falta? O que pode dar multa? O que é oportunidade? Ninguém sabe ao certo.
                </p>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Comunicação Manual</h3>
                <p class="text-gray-600">
                    Cobranças repetitivas, mensagens sem contexto e sem rastreabilidade. Ninguém sabe o que foi enviado e quando.
                </p>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Planilhas Infinitas</h3>
                <p class="text-gray-600">
                    Planilhas que ninguém atualiza, versões desencontradas e dados que não batem. O caos organizado.
                </p>
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

<!-- A Solução Section -->
<section class="bg-gray-50 pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Um centro operacional para o <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">ecossistema contábil</span>
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
<section id="solucoes" class="bg-white pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Soluções que <span class="text-emerald-500">transformam</span> a rotina
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Um ecossistema completo para alta performance
            </p>
        </div>

        <!-- Carrossel de Soluções -->
        <div class="solutions-carousel-container relative">
            <!-- Setas de Navegação -->
            <button class="carousel-arrow carousel-arrow-left absolute left-0 top-1/2 -translate-y-1/2 z-10 w-12 h-12 bg-white rounded-full shadow-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-colors" data-direction="prev">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button class="carousel-arrow carousel-arrow-right absolute right-0 top-1/2 -translate-y-1/2 z-10 w-12 h-12 bg-white rounded-full shadow-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-colors" data-direction="next">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <!-- Cards de Soluções -->
            <div class="solutions-cards-wrapper relative overflow-hidden mx-12">
                <div class="solutions-cards-track flex transition-transform duration-500 ease-in-out" style="transform: translateX(0px);">
                    <!-- Card 1: Central de Documentos -->
                    <div class="solution-card flex-shrink-0 w-full px-4" data-index="0" data-solution="documentos">
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden solution-card-inner">
                            <!-- Header do Dashboard -->
                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <img src="{{ asset('icone-gif/checklist.gif') }}" alt="Central de Documentos" class="w-6 h-6 object-contain">
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900">Central de Documentos</h3>
                                </div>
                                <div class="flex gap-2">
                                    <button class="px-4 py-2 text-sm font-semibold text-emerald-600 bg-emerald-50 rounded-lg border border-emerald-200">Dashboard</button>
                                    <button class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">Documentos</button>
                                    <button class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">Relatórios</button>
                                </div>
                            </div>

                            <!-- Conteúdo do Dashboard -->
                            <div class="p-6 bg-gray-50">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                                    <!-- Painel: Documentos por Status -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Documentos por Status</h4>
                                        <div class="flex items-center justify-center">
                                            <div class="relative w-48 h-48">
                                                <svg class="transform -rotate-90" width="192" height="192">
                                                    <circle cx="96" cy="96" r="80" fill="none" stroke="#e5e7eb" stroke-width="16"/>
                                                    <circle cx="96" cy="96" r="80" fill="none" stroke="#10b981" stroke-width="16" stroke-dasharray="180 502" stroke-dashoffset="0"/>
                                                    <circle cx="96" cy="96" r="80" fill="none" stroke="#ef4444" stroke-width="16" stroke-dasharray="251 502" stroke-dashoffset="-180"/>
                                                    <circle cx="96" cy="96" r="80" fill="none" stroke="#f59e0b" stroke-width="16" stroke-dasharray="71 502" stroke-dashoffset="-431"/>
                                                </svg>
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <div class="text-center">
                                                        <div class="text-3xl font-bold text-gray-900">156</div>
                                                        <div class="text-sm text-gray-600">Total</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex justify-center gap-6 mt-4">
                                            <div class="flex items-center gap-2">
                                                <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                                <span class="text-sm text-gray-600">Em dia (36)</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                                <span class="text-sm text-gray-600">Pendentes (48)</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                                <span class="text-sm text-gray-600">Atenção (4)</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Painel: Uploads Recentes -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Uploads Recentes</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">SPED EFD ICMS/IPI</div>
                                                    <div class="text-xs text-gray-500">Empresa ABC - Jan/2024</div>
                                                </div>
                                                <div class="text-xs text-emerald-600 font-semibold">✓</div>
                                            </div>
                                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">XML NF-e</div>
                                                    <div class="text-xs text-gray-500">Empresa XYZ - Jan/2024</div>
                                                </div>
                                                <div class="text-xs text-emerald-600 font-semibold">✓</div>
                                            </div>
                                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">PDF Declaração</div>
                                                    <div class="text-xs text-gray-500">Empresa DEF - Jan/2024</div>
                                                </div>
                                                <div class="text-xs text-amber-600 font-semibold">⏳</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <!-- Painel: Documentos por Empresa -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Documentos por Empresa</h4>
                                        <div class="space-y-4">
                                            <div>
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">Empresa ABC</span>
                                                    <span class="text-sm font-semibold text-gray-900">45</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-3">
                                                    <div class="bg-emerald-500 h-3 rounded-full" style="width: 75%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">Empresa XYZ</span>
                                                    <span class="text-sm font-semibold text-gray-900">32</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-3">
                                                    <div class="bg-blue-500 h-3 rounded-full" style="width: 53%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">Empresa DEF</span>
                                                    <span class="text-sm font-semibold text-gray-900">28</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-3">
                                                    <div class="bg-amber-500 h-3 rounded-full" style="width: 47%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">Empresa GHI</span>
                                                    <span class="text-sm font-semibold text-gray-900">51</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-3">
                                                    <div class="bg-emerald-500 h-3 rounded-full" style="width: 85%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Painel: Estatísticas -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Estatísticas</h4>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                                <div class="text-3xl font-bold text-emerald-600 mb-1">156</div>
                                                <div class="text-sm text-gray-600">Total</div>
                                            </div>
                                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                                <div class="text-3xl font-bold text-red-600 mb-1">48</div>
                                                <div class="text-sm text-gray-600">Pendentes</div>
                                            </div>
                                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                                <div class="text-3xl font-bold text-blue-600 mb-1">108</div>
                                                <div class="text-sm text-gray-600">Entregues</div>
                                            </div>
                                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                                <div class="text-3xl font-bold text-amber-600 mb-1">4</div>
                                                <div class="text-sm text-gray-600">Atenção</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Leitura e Diagnóstico -->
                    <div class="solution-card flex-shrink-0 w-full px-4" data-index="1" data-solution="diagnostico">
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden solution-card-inner">
                            <!-- Header do Dashboard -->
                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <img src="{{ asset('icone-gif/analyse.gif') }}" alt="Leitura e Diagnóstico" class="w-6 h-6 object-contain">
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900">Leitura e Diagnóstico</h3>
                                </div>
                                <div class="flex gap-2">
                                    <button class="px-4 py-2 text-sm font-semibold text-emerald-600 bg-emerald-50 rounded-lg border border-emerald-200">Dashboard</button>
                                    <button class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">SPED</button>
                                    <button class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">Alertas</button>
                                </div>
                            </div>

                            <!-- Conteúdo do Dashboard -->
                            <div class="p-6 bg-gray-50">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                                    <!-- Painel: Status SPED -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Status SPED por Competência</h4>
                                        <div class="space-y-4">
                                            <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                                    <span class="font-medium text-gray-900">Jan/2024</span>
                                                </div>
                                                <span class="text-sm font-semibold text-emerald-600">OK</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg border border-amber-200">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                                    <span class="font-medium text-gray-900">Dez/2023</span>
                                                </div>
                                                <span class="text-sm font-semibold text-amber-600">Atenção</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                                    <span class="font-medium text-gray-900">Nov/2023</span>
                                                </div>
                                                <span class="text-sm font-semibold text-red-600">Pendência</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                                    <span class="font-medium text-gray-900">Out/2023</span>
                                                </div>
                                                <span class="text-sm font-semibold text-emerald-600">OK</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Painel: Inconsistências Detectadas -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Inconsistências Detectadas</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-start gap-3 p-3 bg-red-50 rounded-lg border border-red-200">
                                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">Divergência CFOP</div>
                                                    <div class="text-xs text-gray-500">Empresa ABC - Jan/2024</div>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-3 p-3 bg-amber-50 rounded-lg border border-amber-200">
                                                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">Lacuna em Registros</div>
                                                    <div class="text-xs text-gray-500">Empresa XYZ - Jan/2024</div>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-3 p-3 bg-red-50 rounded-lg border border-red-200">
                                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">Padrão Suspeito</div>
                                                    <div class="text-xs text-gray-500">Empresa DEF - Dez/2023</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <!-- Painel: Análise por Competência -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Análise por Competência</h4>
                                        <div class="space-y-3">
                                            <div>
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">Jan/2024</span>
                                                    <span class="text-sm font-semibold text-emerald-600">100%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-3">
                                                    <div class="bg-emerald-500 h-3 rounded-full" style="width: 100%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">Dez/2023</span>
                                                    <span class="text-sm font-semibold text-amber-600">75%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-3">
                                                    <div class="bg-amber-500 h-3 rounded-full" style="width: 75%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">Nov/2023</span>
                                                    <span class="text-sm font-semibold text-red-600">45%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-3">
                                                    <div class="bg-red-500 h-3 rounded-full" style="width: 45%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Painel: Diagnósticos Recentes -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Diagnósticos Recentes</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">EFD ICMS/IPI Processado</div>
                                                    <div class="text-xs text-gray-500">Há 2 horas</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">EFD Contribuições Validado</div>
                                                    <div class="text-xs text-gray-500">Há 5 horas</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">SPED Analisado</div>
                                                    <div class="text-xs text-gray-500">Ontem</div>
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
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden solution-card-inner">
                            <!-- Header do Dashboard -->
                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <img src="{{ asset('icone-gif/clipboard-gear.gif') }}" alt="Motor de Regras" class="w-6 h-6 object-contain">
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900">Motor de Regras</h3>
                                </div>
                                <div class="flex gap-2">
                                    <button class="px-4 py-2 text-sm font-semibold text-emerald-600 bg-emerald-50 rounded-lg border border-emerald-200">Dashboard</button>
                                    <button class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">Regras</button>
                                    <button class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">Histórico</button>
                                </div>
                            </div>

                            <!-- Conteúdo do Dashboard -->
                            <div class="p-6 bg-gray-50">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                                    <!-- Painel: Regras Ativas -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Regras Ativas</h4>
                                        <div class="text-center mb-4">
                                            <div class="text-5xl font-bold text-emerald-600 mb-2">24</div>
                                            <div class="text-sm text-gray-600">Regras Configuradas</div>
                                        </div>
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <span class="text-sm font-medium text-gray-700">CFOP 5102</span>
                                                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">Ativa</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <span class="text-sm font-medium text-gray-700">CST 00</span>
                                                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">Ativa</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <span class="text-sm font-medium text-gray-700">Cenário Especial</span>
                                                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">Ativa</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Painel: Classificações Automáticas -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Classificações Automáticas</h4>
                                        <div class="flex items-center justify-center mb-4">
                                            <div class="relative w-40 h-40">
                                                <svg class="transform -rotate-90" width="160" height="160">
                                                    <circle cx="80" cy="80" r="65" fill="none" stroke="#e5e7eb" stroke-width="12"/>
                                                    <circle cx="80" cy="80" r="65" fill="none" stroke="#10b981" stroke-width="12" stroke-dasharray="245 408" stroke-dashoffset="0"/>
                                                    <circle cx="80" cy="80" r="65" fill="none" stroke="#f59e0b" stroke-width="12" stroke-dasharray="98 408" stroke-dashoffset="-245"/>
                                                    <circle cx="80" cy="80" r="65" fill="none" stroke="#ef4444" stroke-width="12" stroke-dasharray="65 408" stroke-dashoffset="-343"/>
                                                </svg>
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <div class="text-center">
                                                        <div class="text-2xl font-bold text-gray-900">408</div>
                                                        <div class="text-xs text-gray-600">Total</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex justify-center gap-4 mt-4">
                                            <div class="flex items-center gap-2">
                                                <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                                <span class="text-xs text-gray-600">Gera Crédito (245)</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                                <span class="text-xs text-gray-600">Atenção (98)</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                                <span class="text-xs text-gray-600">Não Gera (65)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <!-- Painel: Evolução de Regras -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Evolução de Regras</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">Nova regra aprendida</div>
                                                    <div class="text-xs text-gray-500">CFOP 5109 - Há 2 dias</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">Regra atualizada</div>
                                                    <div class="text-xs text-gray-500">CST 20 - Há 5 dias</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">Padrão identificado</div>
                                                    <div class="text-xs text-gray-500">Cenário Especial - Há 1 semana</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Painel: Regras por Tipo -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Regras por Tipo</h4>
                                        <div class="space-y-3">
                                            <div>
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">CFOP</span>
                                                    <span class="text-sm font-semibold text-gray-900">12</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-emerald-500 h-2 rounded-full" style="width: 50%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">CST</span>
                                                    <span class="text-sm font-semibold text-gray-900">8</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-500 h-2 rounded-full" style="width: 33%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">Cenários</span>
                                                    <span class="text-sm font-semibold text-gray-900">4</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-amber-500 h-2 rounded-full" style="width: 17%"></div>
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
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden solution-card-inner">
                            <!-- Header do Dashboard -->
                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <img src="{{ asset('icone-gif/secure-payment.gif') }}" alt="Compliance e Situação Fiscal" class="w-6 h-6 object-contain">
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900">Compliance e Situação Fiscal</h3>
                                </div>
                                <div class="flex gap-2">
                                    <button class="px-4 py-2 text-sm font-semibold text-emerald-600 bg-emerald-50 rounded-lg border border-emerald-200">Dashboard</button>
                                    <button class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">Situação</button>
                                    <button class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">Riscos</button>
                                </div>
                            </div>

                            <!-- Conteúdo do Dashboard -->
                            <div class="p-6 bg-gray-50">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                                    <!-- Painel: Situação por CNPJ -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Situação por CNPJ</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                                                <div>
                                                    <div class="font-semibold text-gray-900 text-sm">12.345.678/0001-90</div>
                                                    <div class="text-xs text-gray-500">Empresa ABC</div>
                                                </div>
                                                <span class="text-xs font-semibold text-emerald-600 bg-white px-3 py-1 rounded-full">Regular</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg border border-amber-200">
                                                <div>
                                                    <div class="font-semibold text-gray-900 text-sm">98.765.432/0001-10</div>
                                                    <div class="text-xs text-gray-500">Empresa XYZ</div>
                                                </div>
                                                <span class="text-xs font-semibold text-amber-600 bg-white px-3 py-1 rounded-full">Atenção</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                                                <div>
                                                    <div class="font-semibold text-gray-900 text-sm">11.222.333/0001-44</div>
                                                    <div class="text-xs text-gray-500">Empresa DEF</div>
                                                </div>
                                                <span class="text-xs font-semibold text-red-600 bg-white px-3 py-1 rounded-full">Crítico</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Painel: Alertas de Vencimento -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Alertas de Vencimento</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-start gap-3 p-3 bg-red-50 rounded-lg border border-red-200">
                                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">DCTFWeb vence em 2 dias</div>
                                                    <div class="text-xs text-gray-500">Empresa ABC</div>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-3 p-3 bg-amber-50 rounded-lg border border-amber-200">
                                                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">SPED vence em 5 dias</div>
                                                    <div class="text-xs text-gray-500">Empresa XYZ</div>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-3 p-3 bg-red-50 rounded-lg border border-red-200">
                                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">Certidão vence hoje</div>
                                                    <div class="text-xs text-gray-500">Empresa DEF</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <!-- Painel: Mapa de Riscos -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Mapa de Riscos</h4>
                                        <div class="grid grid-cols-3 gap-3">
                                            <div class="text-center p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                                                <div class="text-2xl font-bold text-emerald-600 mb-1">8</div>
                                                <div class="text-xs text-gray-600">Baixo</div>
                                            </div>
                                            <div class="text-center p-4 bg-amber-50 rounded-lg border border-amber-200">
                                                <div class="text-2xl font-bold text-amber-600 mb-1">3</div>
                                                <div class="text-xs text-gray-600">Médio</div>
                                            </div>
                                            <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
                                                <div class="text-2xl font-bold text-red-600 mb-1">1</div>
                                                <div class="text-xs text-gray-600">Alto</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Painel: Relatórios de Compliance -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Relatórios de Compliance</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">Status Fiscal Mensal</div>
                                                    <div class="text-xs text-gray-500">Jan/2024</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">Riscos & Inconsistências</div>
                                                    <div class="text-xs text-gray-500">Dez/2023</div>
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
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden solution-card-inner">
                            <!-- Header do Dashboard -->
                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <img src="{{ asset('icone-gif/customer-service.gif') }}" alt="Automação de Comunicação" class="w-6 h-6 object-contain">
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900">Automação de Comunicação</h3>
                                </div>
                                <div class="flex gap-2">
                                    <button class="px-4 py-2 text-sm font-semibold text-emerald-600 bg-emerald-50 rounded-lg border border-emerald-200">Dashboard</button>
                                    <button class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">WhatsApp</button>
                                    <button class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">Histórico</button>
                                </div>
                            </div>

                            <!-- Conteúdo do Dashboard -->
                            <div class="p-6 bg-gray-50">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                                    <!-- Painel: Mensagens Enviadas -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Mensagens Enviadas</h4>
                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <div class="text-center p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                                                <div class="text-3xl font-bold text-emerald-600 mb-1">342</div>
                                                <div class="text-xs text-gray-600">Hoje</div>
                                            </div>
                                            <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                                                <div class="text-3xl font-bold text-blue-600 mb-1">1.248</div>
                                                <div class="text-xs text-gray-600">Este Mês</div>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600">Taxa de Resposta</span>
                                                <span class="font-semibold text-gray-900">78%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-emerald-500 h-2 rounded-full" style="width: 78%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Painel: Conversas Ativas -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Conversas Ativas</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">Empresa ABC</div>
                                                    <div class="text-xs text-gray-500">Pendência: SPED Jan/2024</div>
                                                </div>
                                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                            </div>
                                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">Empresa XYZ</div>
                                                    <div class="text-xs text-gray-500">Relatório entregue</div>
                                                </div>
                                                <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                            </div>
                                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-semibold text-gray-900 text-sm">Empresa DEF</div>
                                                    <div class="text-xs text-gray-500">Cobrança automática</div>
                                                </div>
                                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <!-- Painel: Taxa de Resposta -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Taxa de Resposta</h4>
                                        <div class="space-y-4">
                                            <div>
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">Respondidas</span>
                                                    <span class="text-sm font-semibold text-emerald-600">78%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-3">
                                                    <div class="bg-emerald-500 h-3 rounded-full" style="width: 78%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">Pendentes</span>
                                                    <span class="text-sm font-semibold text-amber-600">15%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-3">
                                                    <div class="bg-amber-500 h-3 rounded-full" style="width: 15%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">Não Respondidas</span>
                                                    <span class="text-sm font-semibold text-gray-600">7%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-3">
                                                    <div class="bg-gray-400 h-3 rounded-full" style="width: 7%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Painel: Histórico de Comunicação -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Histórico de Comunicação</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">Mensagem enviada</div>
                                                    <div class="text-xs text-gray-500">Há 2 horas</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">Relatório entregue</div>
                                                    <div class="text-xs text-gray-500">Há 5 horas</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">Cobrança automática</div>
                                                    <div class="text-xs text-gray-500">Ontem</div>
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
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden solution-card-inner">
                            <!-- Header do Dashboard -->
                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <img src="{{ asset('icone-gif/page-optimization.gif') }}" alt="Portal do Cliente" class="w-6 h-6 object-contain">
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900">Portal do Cliente</h3>
                                </div>
                                <div class="flex gap-2">
                                    <button class="px-4 py-2 text-sm font-semibold text-emerald-600 bg-emerald-50 rounded-lg border border-emerald-200">Dashboard</button>
                                    <button class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">Checklist</button>
                                    <button class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">Histórico</button>
                                </div>
                            </div>

                            <!-- Conteúdo do Dashboard -->
                            <div class="p-6 bg-gray-50">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                                    <!-- Painel: Checklist do Mês -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Checklist do Mês</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                                                <div class="flex items-center gap-3">
                                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="font-medium text-gray-900">SPED EFD ICMS/IPI</span>
                                                </div>
                                                <span class="text-xs font-semibold text-emerald-600">Concluído</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg border border-amber-200">
                                                <div class="flex items-center gap-3">
                                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="font-medium text-gray-900">DCTFWeb</span>
                                                </div>
                                                <span class="text-xs font-semibold text-amber-600">Pendente</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                                                <div class="flex items-center gap-3">
                                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="font-medium text-gray-900">XML NF-e</span>
                                                </div>
                                                <span class="text-xs font-semibold text-emerald-600">Concluído</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                                                <div class="flex items-center gap-3">
                                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <span class="font-medium text-gray-900">Certidões</span>
                                                </div>
                                                <span class="text-xs font-semibold text-red-600">Atrasado</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Painel: Pendências por Cliente -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Pendências por Cliente</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <div>
                                                    <div class="font-semibold text-gray-900 text-sm">Empresa ABC</div>
                                                    <div class="text-xs text-gray-500">2 pendências</div>
                                                </div>
                                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                    <span class="text-xs font-bold text-red-600">2</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <div>
                                                    <div class="font-semibold text-gray-900 text-sm">Empresa XYZ</div>
                                                    <div class="text-xs text-gray-500">1 pendência</div>
                                                </div>
                                                <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                                                    <span class="text-xs font-bold text-amber-600">1</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <div>
                                                    <div class="font-semibold text-gray-900 text-sm">Empresa DEF</div>
                                                    <div class="text-xs text-gray-500">Sem pendências</div>
                                                </div>
                                                <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <!-- Painel: Acessos Recentes -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Acessos Recentes</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">Diretor - Empresa ABC</div>
                                                    <div class="text-xs text-gray-500">Há 1 hora</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">Financeiro - Empresa XYZ</div>
                                                    <div class="text-xs text-gray-500">Há 3 horas</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">Analista - Empresa DEF</div>
                                                    <div class="text-xs text-gray-500">Ontem</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Painel: Permissões por Perfil -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                                        <h4 class="text-lg font-bold text-gray-900 mb-4">Permissões por Perfil</h4>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                <div class="text-2xl font-bold text-blue-600 mb-1">4</div>
                                                <div class="text-xs text-gray-600">Diretores</div>
                                            </div>
                                            <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                <div class="text-2xl font-bold text-emerald-600 mb-1">12</div>
                                                <div class="text-xs text-gray-600">Financeiros</div>
                                            </div>
                                            <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                <div class="text-2xl font-bold text-amber-600 mb-1">8</div>
                                                <div class="text-xs text-gray-600">Analistas</div>
                                            </div>
                                            <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                <div class="text-2xl font-bold text-purple-600 mb-1">6</div>
                                                <div class="text-xs text-gray-600">Contadores</div>
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

<!-- Como Funciona Section -->
<section class="bg-white pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Como <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">Funciona</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Um fluxo simples que transforma documentos em ações e relatórios prontos para decisão
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <div class="text-center border border-gray-300 rounded-lg p-6 hover:border-blue-500 transition-colors">
                <div class="w-16 h-16 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-2xl mx-auto mb-6">1</div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Conecta Empresas</h3>
                <p class="text-gray-600 text-sm">
                    CNPJ, responsáveis e permissões configurados. Cada perfil tem acesso ao que precisa.
                </p>
            </div>

            <div class="text-center border border-gray-300 rounded-lg p-6 hover:border-blue-500 transition-colors">
                <div class="w-16 h-16 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-2xl mx-auto mb-6">2</div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Recebe e Organiza</h3>
                <p class="text-gray-600 text-sm">
                    Documentos recebidos com competência automática e checklist. Tudo organizado por empresa e período.
                </p>
            </div>

            <div class="text-center border border-gray-300 rounded-lg p-6 hover:border-blue-500 transition-colors">
                <div class="w-16 h-16 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-2xl mx-auto mb-6">3</div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Valida e Classifica</h3>
                <p class="text-gray-600 text-sm">
                    Semáforo por competência: OK / Atenção / Pendência. Alertas com motivo e evidências.
                </p>
            </div>

            <div class="text-center border border-gray-300 rounded-lg p-6 hover:border-blue-500 transition-colors">
                <div class="w-16 h-16 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-2xl mx-auto mb-6">4</div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Dispara Operacional</h3>
                <p class="text-gray-600 text-sm">
                    Cobranças e entregas automáticas via WhatsApp e portal. Mensagens com contexto e histórico.
                </p>
            </div>

            <div class="text-center border border-gray-300 rounded-lg p-6 hover:border-blue-500 transition-colors">
                <div class="w-16 h-16 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-2xl mx-auto mb-6">5</div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Gera Relatórios</h3>
                <p class="text-gray-600 text-sm">
                    Executivo e técnico prontos para decisão. Tudo registrado no histórico com trilha de evidências.
                </p>
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

<!-- Benefícios Section -->
<section id="beneficios" class="bg-gray-50 pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Por que escolher <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">nossa solução</span>
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

<!-- Relatórios Section -->
<section id="relatorios" class="bg-white pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Relatórios <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">prontos para decisão</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                O que você entrega pronto: gerenciais para decisão e técnicos para auditoria e execução
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Relatórios Gerenciais
                </h3>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">•</span>
                        <span><strong>Status Fiscal Mensal:</strong> O que está OK, o que falta, o que é crítico</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">•</span>
                        <span><strong>Mapa de Pendências:</strong> Por empresa, por competência, por responsável</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">•</span>
                        <span><strong>Riscos & Inconsistências:</strong> Com evidências e impacto</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">•</span>
                        <span><strong>Oportunidades:</strong> Créditos, classificações, pontos de atenção</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">•</span>
                        <span><strong>Resumo Executivo:</strong> Para diretoria — linguagem não contábil</span>
                    </li>
                </ul>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Relatórios Técnicos
                </h3>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">•</span>
                        <span><strong>Trilhas de Evidências:</strong> Por item/alerta com histórico completo</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">•</span>
                        <span><strong>Log de Eventos:</strong> Upload, validação, envio, entrega</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">•</span>
                        <span><strong>Exportações:</strong> PDF/Excel/CSV conforme sua estrutura</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">•</span>
                        <span><strong>Auditoria Completa:</strong> Quem fez o quê, quando e por quê</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">•</span>
                        <span><strong>Histórico de Versões:</strong> Rastreabilidade total de documentos</span>
                    </li>
                </ul>
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

<!-- Para Quem É Section -->
<section class="bg-gray-50 pt-20 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Para <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">quem é</span>
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
                Por que o HUB é <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">diferente</span>
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
                Segurança e <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">LGPD</span>
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
                Números que <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">Impressionam</span>
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
                O que nossos <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">clientes dizem</span>
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
                Perguntas <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">Frequentes</span>
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
                Fale com <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">nossos especialistas</span>
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
document.addEventListener('DOMContentLoaded', function() {
    // Carrossel de Soluções
    const track = document.querySelector('.solutions-cards-track');
    const cards = document.querySelectorAll('.solution-card');
    const pills = document.querySelectorAll('.solution-pill');
    const prevArrow = document.querySelector('.carousel-arrow-left');
    const nextArrow = document.querySelector('.carousel-arrow-right');
    
    let currentIndex = 0;
    const totalCards = cards.length;
    
    // Função para atualizar posição do carrossel
    function updateCarousel(index) {
        if (!track || !cards.length) return;
        
        const wrapper = document.querySelector('.solutions-cards-wrapper');
        if (!wrapper) return;
        
        // Aguardar próximo frame para garantir que o layout está atualizado
        requestAnimationFrame(() => {
            // Obter largura real do primeiro card
            const firstCard = cards[0];
            if (!firstCard) return;
            
            const cardRect = firstCard.getBoundingClientRect();
            const cardWidth = cardRect.width;
            
            // Calcular largura do wrapper
            const wrapperWidth = wrapper.offsetWidth;
            
            // Calcular posição para centralizar o card ativo
            // Cada card ocupa cardWidth, então movemos index * cardWidth para a esquerda
            // e depois compensamos para centralizar
            const centerOffset = (wrapperWidth - cardWidth) / 2;
            const translateX = -(index * cardWidth) + centerOffset;
            
            track.style.transform = `translateX(${translateX}px)`;
            
            // Atualizar classes active
            cards.forEach((card, i) => {
                if (i === index) {
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
            });
            
            // Atualizar pills
            pills.forEach((pill, i) => {
                if (i === index) {
                    pill.classList.add('active');
                } else {
                    pill.classList.remove('active');
                }
            });
        });
    }
    
    // Navegação por setas com loop infinito
    if (prevArrow) {
        prevArrow.addEventListener('click', function() {
            currentIndex = (currentIndex - 1 + totalCards) % totalCards;
            updateCarousel(currentIndex);
        });
    }
    
    if (nextArrow) {
        nextArrow.addEventListener('click', function() {
            currentIndex = (currentIndex + 1) % totalCards;
            updateCarousel(currentIndex);
        });
    }
    
    // Navegação por pills
    pills.forEach((pill, index) => {
        pill.addEventListener('click', function() {
            currentIndex = index;
            updateCarousel(currentIndex);
        });
    });
    
    // Inicializar
    updateCarousel(0);
    
    // Ajustar ao redimensionar
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            updateCarousel(currentIndex);
        }, 250);
    });
});
</script>

<!-- Scripts carregados no layout -->
