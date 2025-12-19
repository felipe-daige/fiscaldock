
<style>
/* Solutions Carousel Styles */
.solutions-carousel-container {
    min-height: 600px;
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
    height: 600px; /* Altura fixa uniforme com folga para sombra */
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
        min-height: 600px;
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

/* Navegação em pills - faixa leve e sutil */
.solution-pill-group {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 999px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
}

.solution-pill {
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.25s ease;
    color: var(--color-text-muted);
    background-color: transparent;
    border-radius: 999px;
}

.solution-pill.active {
    background-color: #ffffff !important;
    color: #0f3d8e !important;
    border-color: #0f3d8e !important;
    box-shadow: 0 6px 18px rgba(15, 61, 142, 0.12);
}

.solution-pill:not(.active) {
    color: var(--color-text-muted);
}

.solution-pill:hover:not(.active) {
    background-color: #edf2f7;
    color: var(--color-text);
}

/* Estilos para cards de depoimentos */

/* Estilos criativos para cards de depoimentos */
.testimonial-card {
    transform: translateY(0);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    animation: fadeInUp 0.6s ease-out;
}

.testimonial-card:hover {
    transform: translateY(-8px) scale(1.02);
    border-color: rgb(59, 130, 246);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.quote-decoration {
    user-select: none;
    pointer-events: none;
    opacity: 0.15;
    animation: pulse 3s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 0.15;
    }
    50% {
        opacity: 0.25;
    }
}

.avatar-gradient {
    transition: all 0.3s ease;
    position: relative;
}

.avatar-gradient::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 50%;
    padding: 2px;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.testimonial-card:hover .avatar-gradient::before {
    opacity: 1;
    animation: rotate 2s linear infinite;
}

@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.testimonial-metric span {
    animation: scaleIn 0.5s ease-out 0.2s both;
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.stars {
    position: relative;
    display: inline-block;
}

.stars::after {
    content: '★★★★★';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    color: rgb(250, 204, 21);
    animation: fillStars 1s ease-out 0.5s both;
}

@keyframes fillStars {
    from {
        width: 0;
    }
    to {
        width: 100%;
    }
}

.verified-badge {
    animation: slideIn 0.4s ease-out 0.3s both;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Responsividade melhorada */
@media (max-width: 640px) {
    .testimonial-card {
        padding: 1.5rem;
    }
    
    .quote-decoration {
        font-size: 4rem;
        top: 0.5rem;
        right: 0.5rem;
    }
    
    .avatar-gradient {
        width: 3rem;
        height: 3rem;
        font-size: 1.125rem;
    }
}

/* Hero fix: força gradiente mesmo se classes tailwind falharem */
#hero {
    background: linear-gradient(135deg, #0b1f3a 0%, #1e4fa0 100%) !important;
    color: #fff;
}
</style>

<!-- Hero Section (refeito) -->
<section id="hero" class="relative bg-gradient-to-br from-primary-700 to-primary-500 text-white" style="background: linear-gradient(135deg, #0b1f3a 0%, #1e4fa0 100%);">
    <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center justify-center">
            <!-- Coluna Esquerda: Texto -->
            <div class="lg:col-span-5 xl:col-span-6">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold mb-4">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                    Ambiente de desenvolvimento ativo - FiscalDock
                </div>

                <!-- Título -->
                <h1 class="font-extrabold leading-tight tracking-tight text-3xl sm:text-4xl xl:text-5xl">
                    O FiscalDock que transforma
                    <span class="block text-white [text-shadow:0_1px_4px_rgba(0,0,0,0.45),0_0_1px_rgba(0,0,0,0.35),0_0_8px_rgba(0,0,0,0.25)]">SPED em Ações e Relatórios</span>
                </h1>

                <!-- Subtítulo -->
                <p class="mt-4 text-base sm:text-lg text-white/80 max-w-2xl">
                    Centralize arquivos fiscais, identifique pendências e riscos automaticamente, gere relatórios prontos para decisão e automatize cobranças/entregas pelo WhatsApp — com trilha de evidências.
                </p>

                <!-- CTAs -->
                <div class="mt-5">
                    <a href="/agendar" data-link data-button="cta" class="btn-cta btn-cta--nav inline-flex items-center gap-2 px-5 py-3 rounded-lg border-2 border-[#facc15] bg-[#facc15] text-[#0b1f3a] font-semibold shadow-[0_20px_50px_-20px_rgba(250,204,21,0.5)] hover:bg-[#eab308] hover:border-[#eab308] transition" style="background-color:#facc15; border-color:#facc15; color:#0b1f3a; box-shadow:0 20px 50px -20px rgba(250,204,21,0.5);">
                        <span class="whitespace-nowrap">Quero conhecer</span>
                        <svg class="h-5 w-5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>

                <!-- Frase de apoio -->
                <p class="mt-3 text-sm text-white/70 max-w-2xl">
                    Feito para escritórios contábeis e empresas que precisam de controle, conformidade e velocidade, sem planilhas infinitas.
                </p>

                <!-- Social Proof: Avatares + Avaliação -->
                <div class="mt-6 mb-6 lg:mb-8 flex items-center gap-4 flex-wrap">
                    <!-- Grupo de Avatares -->
                    <div class="flex items-center -space-x-2">
                        <img src="{{ asset('binary_files/people-pictures/random_person-1.jpg') }}" alt="Avaliador" class="w-12 h-12 rounded-full border-2 border-white object-cover">
                        <img src="{{ asset('binary_files/people-pictures/random_person-2.jpg') }}" alt="Avaliador" class="w-12 h-12 rounded-full border-2 border-white object-cover">
                        <img src="{{ asset('binary_files/people-pictures/random_person-3.jpg') }}" alt="Avaliador" class="w-12 h-12 rounded-full border-2 border-white object-cover">
                        <img src="{{ asset('binary_files/people-pictures/random_person-4.jpg') }}" alt="Avaliador" class="w-12 h-12 rounded-full border-2 border-white object-cover">
                        <img src="{{ asset('binary_files/people-pictures/random_person-5.jpg') }}" alt="Avaliador" class="w-12 h-12 rounded-full border-2 border-white object-cover">
                    </div>

                    <!-- Avaliação -->
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <!-- Estrelas -->
                            <div class="flex items-center gap-0.5">
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </div>
                            <!-- Nota -->
                            <span class="text-white font-bold text-lg">5.0</span>
                        </div>
                        <!-- Texto de contagem -->
                        <p class="text-white/80 text-sm mt-1">+ de 440k empresas</p>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita: Mockup -->
            <div class="lg:col-span-7 xl:col-span-6">
                <div class="relative rounded-3xl border border-white/15 bg-white/5 p-2 shadow-lg shadow-blue-950/10">
                    <img
                        src="{{ asset('binary_files/mockups/dashboard-mockup.jpg') }}"
                        alt="Mockup do dashboard ReformaTax"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                        width="1280" height="720"
                        class="rounded-2xl w-full h-auto object-cover max-h-[400px]"
                    >
                </div>
            </div>
        </div>
    </div>
    <!-- Curva inferior (côncava) -->
    <div class="pointer-events-none absolute inset-x-0 bottom-[-1px] leading-none">
        <!-- O preenchimento branco cria a sensação de que o azul termina com curvatura -->
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[60px] sm:h-[80px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#f9fafb"></path>
        </svg>
    </div>
</section>

<!-- Soluções Section -->
<section id="funcionalidades" class="bg-gray-50 pt-4 pb-0 overflow-x-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Soluções que <span class="text-brand">transformam</span> a rotina
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
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900 shadow-md">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="text-center mb-8">
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
                                                        <img src="{{ asset('binary_files/icone-gif/page-optimization.gif') }}" alt="Portal do Cliente" class="w-5 h-5 object-contain">
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
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900 shadow-md">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="text-center mb-6">
                                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900">Central de Documentos</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-lg text-gray-700 mb-4 leading-relaxed">
                                            Upload e organização por empresa, competência e tipo. Versionamento e busca inteligente com histórico e evidência.
                                        </p>
                                        
                                        <!-- Lista simples -->
                                        <ul class="mb-4 space-y-2 text-gray-600">
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Organização automática por empresa, competência e tipo</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Versionamento completo com histórico</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Busca inteligente e compartilhamento seguro</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-24">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('binary_files/icone-gif/checklist.gif') }}" alt="Central de Documentos" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Central de Documentos</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">Por Empresa</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Por Competência</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Histórico</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup (simplificado) -->
                                            <div class="p-6 bg-gray-50">
                                                <div class="grid grid-cols-2 gap-4 mb-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                                            <span class="text-xs font-semibold text-gray-700">Empresa A - Jan/2024</span>
                                                        </div>
                                                        <div class="h-16 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                                            <span class="text-xs font-semibold text-gray-700">Empresa B - Jan/2024</span>
                                                        </div>
                                                        <div class="h-16 bg-gray-100 rounded mb-2"></div>
                                                        <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="text-xs text-gray-500 mb-2">Versões</div>
                                                        <div class="h-12 bg-gray-100 rounded"></div>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="text-xs text-gray-500 mb-2">Busca Inteligente</div>
                                                        <div class="h-12 bg-gray-100 rounded"></div>
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
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900 shadow-md">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="text-center mb-6">
                                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900">Leitura e Diagnóstico</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-lg text-gray-700 mb-4 leading-relaxed">
                                            Importação e estruturação de SPED, detecção de inconsistências e semáforo por competência com alertas.
                                        </p>
                                        
                                        <!-- Lista simples -->
                                        <ul class="mb-4 space-y-2 text-gray-600">
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Importação e estruturação automática de SPED</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Detecção automática de inconsistências e lacunas</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Semáforo visual: OK / Atenção / Pendência</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-24">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('binary_files/icone-gif/analyse.gif') }}" alt="Leitura e Diagnóstico" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Leitura e Diagnóstico</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">SPED</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Semáforo</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Alertas</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup (simplificado) -->
                                            <div class="p-6 bg-gray-50">
                                                <div class="space-y-3 mb-4">
                                                    <!-- Competência OK -->
                                                    <div class="bg-white rounded-lg p-4 border-2 border-green-400">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <span class="text-sm font-semibold text-gray-900">Jan/2024</span>
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                                                                <span class="text-xs font-bold text-green-600">OK</span>
                                                            </div>
                                                        </div>
                                                        <p class="text-xs text-gray-600">SPED importado e estruturado corretamente</p>
                                                    </div>
                                                    <!-- Competência Atenção -->
                                                    <div class="bg-white rounded-lg p-4 border-2 border-yellow-400">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <span class="text-sm font-semibold text-gray-900">Fev/2024</span>
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-4 h-4 bg-yellow-500 rounded-full"></div>
                                                                <span class="text-xs font-bold text-yellow-600">ATENÇÃO</span>
                                                            </div>
                                                        </div>
                                                        <p class="text-xs text-gray-600">Inconsistências detectadas - requer revisão</p>
                                                    </div>
                                                    <!-- Competência Pendência -->
                                                    <div class="bg-white rounded-lg p-4 border-2 border-red-400">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <span class="text-sm font-semibold text-gray-900">Mar/2024</span>
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                                                                <span class="text-xs font-bold text-red-600">PENDÊNCIA</span>
                                                            </div>
                                                        </div>
                                                        <p class="text-xs text-gray-600">Lacunas identificadas - ação necessária</p>
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
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900 shadow-md">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="text-center mb-6">
                                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900">Motor de Regras</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-lg text-gray-700 mb-4 leading-relaxed">
                                            Regras parametrizáveis por operação. Classificação automática e evolução contínua que aprende seu padrão.
                                        </p>
                                        
                                        <!-- Lista simples -->
                                        <ul class="mb-4 space-y-2 text-gray-600">
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Regras parametrizáveis por tipo de operação</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Classificação automática: gera crédito / atenção / não gera</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Evolução contínua que aprende seu padrão</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-24">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('binary_files/icone-gif/clipboard-gear.gif') }}" alt="Motor de Regras" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Motor de Regras</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">Classificação</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Regras</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Histórico</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup (simplificado) -->
                                            <div class="p-6 bg-gray-50">
                                                <div class="space-y-3 mb-4">
                                                    <!-- Operação: Gera Crédito -->
                                                    <div class="bg-white rounded-lg p-4 border-2 border-green-400">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <span class="text-sm font-semibold text-gray-900">Operação A</span>
                                                            <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded font-bold">GERA CRÉDITO</span>
                                                        </div>
                                                        <p class="text-xs text-gray-600">Regra aplicada automaticamente</p>
                                                    </div>
                                                    <!-- Operação: Atenção -->
                                                    <div class="bg-white rounded-lg p-4 border-2 border-yellow-400">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <span class="text-sm font-semibold text-gray-900">Operação B</span>
                                                            <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded font-bold">ATENÇÃO</span>
                                                        </div>
                                                        <p class="text-xs text-gray-600">Requer revisão manual</p>
                                                    </div>
                                                    <!-- Operação: Não Gera -->
                                                    <div class="bg-white rounded-lg p-4 border-2 border-gray-400">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <span class="text-sm font-semibold text-gray-900">Operação C</span>
                                                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded font-bold">NÃO GERA</span>
                                                        </div>
                                                        <p class="text-xs text-gray-600">Classificação automática aplicada</p>
                                                    </div>
                                                </div>
                                                <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                                                    <p class="text-xs text-blue-700 font-semibold">✓ Aprendizado contínuo ativo</p>
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
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900 shadow-md">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="text-center mb-6">
                                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900">Compliance e Situação Fiscal</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-lg text-gray-700 mb-4 leading-relaxed">
                                            Painel de situação por CNPJ, alertas de vencimento e relatório de risco com evidências e histórico.
                                        </p>
                                        
                                        <!-- Lista simples -->
                                        <ul class="mb-4 space-y-2 text-gray-600">
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Painel de situação por CNPJ com mapa visual</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Alertas automáticos de vencimento</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Relatório de risco com evidências e histórico</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-24">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('binary_files/icone-gif/secure-payment.gif') }}" alt="Compliance e Situação Fiscal" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Compliance e Situação Fiscal</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">Por CNPJ</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Alertas</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Riscos</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup (simplificado) -->
                                            <div class="p-6 bg-gray-50">
                                                <div class="space-y-3 mb-4">
                                                    <!-- CNPJ Regular -->
                                                    <div class="bg-white rounded-lg p-4 border-2 border-green-400">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <div>
                                                                <p class="text-sm font-semibold text-gray-900">12.345.678/0001-90</p>
                                                                <p class="text-xs text-gray-500">Empresa A</p>
                                                            </div>
                                                            <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded font-bold">REGULAR</span>
                                                        </div>
                                                        <div class="text-xs text-gray-600">Sem pendências • CND válida</div>
                                                    </div>
                                                    <!-- CNPJ com Alerta -->
                                                    <div class="bg-white rounded-lg p-4 border-2 border-yellow-400">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <div>
                                                                <p class="text-sm font-semibold text-gray-900">98.765.432/0001-10</p>
                                                                <p class="text-xs text-gray-500">Empresa B</p>
                                                            </div>
                                                            <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded font-bold">ALERTA</span>
                                                        </div>
                                                        <div class="text-xs text-gray-600">Vencimento em 5 dias • DAS a pagar</div>
                                                    </div>
                                                    <!-- CNPJ com Risco -->
                                                    <div class="bg-white rounded-lg p-4 border-2 border-red-400">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <div>
                                                                <p class="text-sm font-semibold text-gray-900">11.222.333/0001-44</p>
                                                                <p class="text-xs text-gray-500">Empresa C</p>
                                                            </div>
                                                            <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded font-bold">RISCO</span>
                                                        </div>
                                                        <div class="text-xs text-gray-600">Pendências detectadas • Ação necessária</div>
                                                    </div>
                                                </div>
                                                <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                                                    <p class="text-xs text-blue-700 font-semibold">📊 Relatório de risco disponível com evidências</p>
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
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900 shadow-md">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="text-center mb-6">
                                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900">Automação de Comunicação</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-lg text-gray-700 mb-4 leading-relaxed">
                                            Cobrança automática via WhatsApp e portal. Mensagens com contexto e registro completo da conversa.
                                        </p>
                                        
                                        <!-- Lista simples -->
                                        <ul class="mb-4 space-y-2 text-gray-600">
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Cobrança automática por competência via WhatsApp e portal</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Mensagens inteligentes com contexto completo</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Registro completo com trilha de auditoria</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-24">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('binary_files/icone-gif/customer-service.gif') }}" alt="Automação de Comunicação" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Automação de Comunicação</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">WhatsApp</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Portal</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Histórico</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup (simplificado) -->
                                            <div class="p-6 bg-gray-50">
                                                <!-- Simulação de mensagem WhatsApp -->
                                                <div class="bg-green-50 rounded-lg p-4 mb-3 border border-green-200">
                                                    <div class="flex items-start gap-3">
                                                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                            <span class="text-white text-xs font-bold">WA</span>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="flex items-center gap-2 mb-1">
                                                                <span class="text-xs font-semibold text-gray-900">Mensagem Automática</span>
                                                                <span class="text-xs text-gray-500">Hoje, 14:30</span>
                                                            </div>
                                                            <p class="text-sm text-gray-700 mb-1">Olá! Lembramos que os documentos de <strong>Janeiro/2024</strong> estão pendentes.</p>
                                                            <p class="text-xs text-gray-500">Contexto: SPED não recebido • Prazo: 5 dias</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Simulação de entrega automática -->
                                                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span class="text-xs font-semibold text-blue-700">Relatório entregue automaticamente</span>
                                                    </div>
                                                    <p class="text-xs text-gray-600">Confirmação recebida • Registrado em trilha de auditoria</p>
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
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900 shadow-md">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="text-center mb-6">
                                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900">Portal do Cliente</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-lg text-gray-700 mb-4 leading-relaxed">
                                            Checklist do mês, pendências, prazos e histórico. Permissões por perfil para menos atrito e mais previsibilidade.
                                        </p>
                                        
                                        <!-- Lista simples -->
                                        <ul class="mb-4 space-y-2 text-gray-600">
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Checklist mensal com pendências e prazos claros</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Histórico completo de entregas e relatórios</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Permissões por perfil garantindo acesso adequado</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup/Imagem Interativa -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Mockup do Dashboard -->
                                    <div class="relative w-full h-full max-w-5xl mt-24">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('binary_files/icone-gif/page-optimization.gif') }}" alt="Portal do Cliente" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Portal do Cliente</span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">Dashboard</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Checklist</span>
                                                    <span class="px-3 py-1 text-xs text-gray-600 rounded">Histórico</span>
                                                </div>
                                            </div>
                                            <!-- Conteúdo do mockup -->
                                            <div class="p-6 bg-gray-50">
                                                <!-- Checklist do Mês -->
                                                <div class="bg-white rounded-lg p-4 border border-gray-200 mb-4">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <h5 class="text-sm font-semibold text-gray-900">Checklist Janeiro/2024</h5>
                                                        <span class="text-xs text-gray-500">3/5 concluído</span>
                                                    </div>
                                                    <div class="space-y-2">
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            <span class="text-xs text-gray-700">SPED Fiscal</span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            <span class="text-xs text-gray-700">Notas Fiscais</span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <span class="text-xs text-gray-700">DAS a pagar</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Pendências e Histórico -->
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                                            <span class="text-xs font-semibold text-gray-900">Pendências</span>
                                                        </div>
                                                        <p class="text-xs text-gray-600">2 documentos aguardando</p>
                                                    </div>
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <span class="text-xs font-semibold text-gray-900">Histórico</span>
                                                        </div>
                                                        <p class="text-xs text-gray-600">Última entrega: 15/01</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 7: RAF - Relatório de Risco e Inteligência Fiscal -->
                    <div class="solution-card flex-shrink-0 w-full px-4" data-index="6" data-solution="raf">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <!-- Coluna Esquerda: Descrição -->
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900 shadow-md">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="text-center mb-6">
                                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900">Relatório de Fornecedores</h3>
                                        </div>
                                        
                                        <!-- Descrição Principal -->
                                        <p class="text-lg text-gray-700 mb-4 leading-relaxed">
                                            O Relatório de Fornecedores (RAF) transforma dados brutos em análise consultiva. Relatório consolidado com situação fiscal completa de cada CNPJ em uma única página.
                                        </p>
                                        
                                        <!-- Lista simples -->
                                        <ul class="mb-4 space-y-2 text-gray-600">
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Visão 360º: Regime Tributário, CND e faturamento por cliente</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Identificação automática de riscos e pendências fiscais</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Ferramenta de consultoria para elevar seu escritório</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Call-to-Action -->
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Coluna Direita: Mockup do Relatório RAF -->
                                <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-6 lg:p-8 flex items-center justify-center overflow-hidden">
                                    <!-- Mockup do Relatório RAF -->
                                    <div class="relative w-full h-full max-w-5xl mt-24">
                                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-700" style="transform: scale(0.85);">
                                            <!-- Header do mockup -->
                                            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-surface-alt rounded-lg flex items-center justify-center">
                                                        <img src="{{ asset('binary_files/icone-gif/analyse.gif') }}" alt="RAF" class="w-5 h-5 object-contain">
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">Relatório de Fornecedores</span>
                                                </div>
                                                <span class="px-3 py-1 text-xs bg-blue-50 text-blue-600 rounded font-semibold">RAF</span>
                                            </div>
                                            
                                            <!-- Conteúdo do mockup -->
                                            <div class="p-6 bg-gray-50">
                                                <!-- Grid de duas colunas -->
                                                <div class="grid grid-cols-2 gap-4">
                                                    <!-- CNPJ X - OK -->
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200 overflow-hidden">
                                                        <div class="flex items-center justify-between mb-3">
                                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                                <div class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0"></div>
                                                                <div class="min-w-0 flex-1">
                                                                    <p class="text-sm font-semibold text-gray-900 truncate">CNPJ X</p>
                                                                    <p class="text-xs text-gray-500 truncate">12.345.678/0001-90</p>
                                                                </div>
                                                            </div>
                                                            <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded font-semibold flex-shrink-0 ml-2">OK</span>
                                                        </div>
                                                        <div class="space-y-2 pt-3 border-t border-gray-100">
                                                            <div>
                                                                <p class="text-xs text-gray-500 mb-1">Regime Tributário</p>
                                                                <p class="text-xs font-semibold text-gray-900 truncate">Lucro Real</p>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs text-gray-500 mb-1">CND</p>
                                                                <p class="text-xs font-semibold text-green-600 flex items-center gap-1">
                                                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                                                    <span class="truncate">Regular</span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- CNPJ Y - Alerta -->
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200 overflow-hidden">
                                                        <div class="flex items-center justify-between mb-3">
                                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                                <div class="w-2 h-2 bg-yellow-500 rounded-full flex-shrink-0"></div>
                                                                <div class="min-w-0 flex-1">
                                                                    <p class="text-sm font-semibold text-gray-900 truncate">CNPJ Y</p>
                                                                    <p class="text-xs text-gray-500 truncate">98.765.432/0001-10</p>
                                                                </div>
                                                            </div>
                                                            <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded font-semibold flex-shrink-0 ml-2">Alerta</span>
                                                        </div>
                                                        <div class="space-y-2 pt-3 border-t border-gray-100">
                                                            <div>
                                                                <p class="text-xs text-gray-500 mb-1">Regime Tributário</p>
                                                                <p class="text-xs font-semibold text-gray-900 truncate">Simples Nacional</p>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs text-gray-500 mb-1">CND</p>
                                                                <p class="text-xs font-semibold text-yellow-600 flex items-center gap-1">
                                                                    <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span>
                                                                    <span class="truncate">Pendência Detectada</span>
                                                                </p>
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

                    <!-- Card Duplicado: Central de Documentos (para loop infinito - fim) -->
                    <div class="solution-card solution-card-duplicate flex-shrink-0 w-full px-4" data-index="duplicate-end" data-solution="documentos">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <!-- Coluna Esquerda: Descrição -->
                                <div class="flex flex-col justify-between p-8 lg:p-12 bg-white text-gray-900 shadow-md">
                                    <!-- Logo e Título -->
                                    <div>
                                        <div class="text-center mb-8">
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
                                                        <img src="{{ asset('binary_files/icone-gif/checklist.gif') }}" alt="Central de Documentos" class="w-5 h-5 object-contain">
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
            <div class="w-full flex justify-center mt-6 overflow-x-auto">
                <div class="solution-pill-group inline-flex flex-nowrap items-center justify-center whitespace-nowrap gap-1.5 sm:gap-[0.625rem] px-3.5 sm:px-5 py-2.5 rounded-full bg-gray-50 shadow-sm min-w-fit">
                    <button class="solution-pill active px-4 sm:px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="documentos">
                        Central de Documentos
                    </button>
                    <button class="solution-pill px-4 sm:px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="diagnostico">
                        Leitura e Diagnóstico
                    </button>
                    <button class="solution-pill px-4 sm:px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="regras">
                        Motor de Regras
                    </button>
                    <button class="solution-pill px-4 sm:px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="compliance">
                        Compliance Fiscal
                    </button>
                    <button class="solution-pill px-4 sm:px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="comunicacao">
                        Automação WhatsApp
                    </button>
                    <button class="solution-pill px-4 sm:px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="portal">
                        Portal do Cliente
                    </button>
                    <button class="solution-pill px-4 sm:px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="raf">
                        RAF: Inteligência Fiscal
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Divisor ondulado: transição do cinza (#f9fafb) para o branco -->
    <div class="pointer-events-none leading-none mt-4 sm:mt-6">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#ffffff"></path>
        </svg>
    </div>
</section>

<!-- Benefícios Section -->
<section id="beneficios" class="bg-white pt-4 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Por que escolher <span class="text-brand">nossa solução</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Transforme o caos de documentos e pendências em organização, controle e previsibilidade
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('binary_files/icone-gif/financial-decline.gif') }}" alt="Financial Decline" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Redução de até 30% na carga tributária com planejamento adequado</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('binary_files/icone-gif/contract.gif') }}" alt="Contract" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Conformidade total com rastreabilidade e evidências completas</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('binary_files/icone-gif/process.gif') }}" alt="Process" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Processos automatizados que economizam tempo e recursos</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('binary_files/icone-gif/budgeting.gif') }}" alt="Budgeting" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Visibilidade completa dos impactos financeiros em tempo real</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('binary_files/icone-gif/customer-service.gif') }}" alt="Customer Service" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Equipe de especialistas disponível para suporte contínuo</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('binary_files/icone-gif/investment.gif') }}" alt="Investment" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">ROI comprovado em menos de 6 meses</p>
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

<!-- IA Financeira Section -->
<section id="notebook" class="bg-gray-50 pt-4 pb-0">
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
                <img src="{{ asset('binary_files/mockups/macbook-mockup.png') }}" alt="Demonstração de fluxo de caixa em um notebook" class="w-full max-w-2xl mx-auto drop-shadow-2xl">
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

<!-- Para Quem É Section -->
<section id="para-quem-e" class="bg-white pt-4 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Para <span class="text-brand">quem é</span>
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
    <!-- Divisor ondulado: transição do branco para o cinza (#f9fafb) -->
    <div class="pointer-events-none leading-none mt-6 sm:mt-8">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#f9fafb"></path>
        </svg>
    </div>
</section>

<!-- Diferenciais Section -->
<section id="diferenciais" class="bg-gray-50 pt-4 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Por que o HUB é <span class="text-brand">diferente</span>
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
    <!-- Divisor ondulado: transição do cinza (#f9fafb) para o branco -->
    <div class="pointer-events-none leading-none mt-6 sm:mt-8">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#ffffff"></path>
        </svg>
    </div>
</section>

<!-- Segurança e LGPD Section -->
<section id="seguranca-lgpd" class="bg-white pt-4 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Segurança e <span class="text-brand">LGPD</span>
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
    <!-- Divisor ondulado: transição do branco para o cinza (#f9fafb) -->
    <div class="pointer-events-none leading-none mt-6 sm:mt-8">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#f9fafb"></path>
        </svg>
    </div>
</section>

{{-- Estatísticas Section removida a pedido do cliente --}}
<!-- Depoimentos Section -->
<section id="depoimentos" class="bg-gray-50 pt-4 pb-0">
    <div class="w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-16">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    O que nossos <span class="text-brand">clientes dizem</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Resultados reais de escritórios contábeis e empresas que transformaram seu dia a dia com o HUB
                </p>
            </div>
        </div>

        <!-- Grid de Depoimentos -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="testimonial-card bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-blue-200 relative overflow-hidden">
                    <div class="quote-decoration absolute top-4 right-4 text-8xl font-serif text-blue-50 leading-none">"</div>
                    <div class="testimonial-header flex items-center gap-4 mb-6 relative z-10">
                        <img src="{{ asset('binary_files/people-pictures/random_person-1.jpg') }}" alt="Maria Silva" class="avatar-gradient w-14 h-14 rounded-full object-cover shadow-lg ring-4 ring-purple-100">
                        <div class="flex-1">
                            <div class="font-bold text-gray-900 text-lg">Maria Silva</div>
                            <div class="text-sm text-gray-600">CEO, TechCorp</div>
                            <div class="verified-badge inline-flex items-center gap-1 mt-1 px-2 py-0.5 bg-green-50 text-green-600 rounded-full text-xs font-semibold">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                Verificado
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-metric mb-4 relative z-10">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-md">R$ 500k economizados</span>
                    </div>
                    <p class="testimonial-text text-gray-700 mb-6 leading-relaxed relative z-10 text-base">
                        "A solução nos ajudou a economizar mais de R$ 500 mil em impostos no primeiro ano. O ROI foi impressionante!"
                    </p>
                    <div class="testimonial-footer flex items-center justify-between relative z-10">
                        <div class="stars text-yellow-400 text-xl">★★★★★</div>
                        <div class="date text-xs text-gray-400">Há 2 meses</div>
                    </div>
                </div>

                <div class="testimonial-card bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-blue-200 relative overflow-hidden">
                    <div class="quote-decoration absolute top-4 right-4 text-8xl font-serif text-blue-50 leading-none">"</div>
                    <div class="testimonial-header flex items-center gap-4 mb-6 relative z-10">
                        <img src="{{ asset('binary_files/people-pictures/random_person-2.jpg') }}" alt="João Santos" class="avatar-gradient w-14 h-14 rounded-full object-cover shadow-lg ring-4 ring-blue-100">
                        <div class="flex-1">
                            <div class="font-bold text-gray-900 text-lg">João Santos</div>
                            <div class="text-sm text-gray-600">Diretor Financeiro, Inovação Ltda</div>
                            <div class="verified-badge inline-flex items-center gap-1 mt-1 px-2 py-0.5 bg-green-50 text-green-600 rounded-full text-xs font-semibold">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                Verificado
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-metric mb-4 relative z-10">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-md">70% redução</span>
                    </div>
                    <p class="testimonial-text text-gray-700 mb-6 leading-relaxed relative z-10 text-base">
                        "A automação dos processos fiscais reduziu nosso tempo de trabalho em 70%. Altamente recomendado!"
                    </p>
                    <div class="testimonial-footer flex items-center justify-between relative z-10">
                        <div class="stars text-yellow-400 text-xl">★★★★★</div>
                        <div class="date text-xs text-gray-400">Há 1 mês</div>
                    </div>
                </div>

                <div class="testimonial-card bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-blue-200 relative overflow-hidden">
                    <div class="quote-decoration absolute top-4 right-4 text-8xl font-serif text-blue-50 leading-none">"</div>
                    <div class="testimonial-header flex items-center gap-4 mb-6 relative z-10">
                        <img src="{{ asset('binary_files/people-pictures/random_person-3.jpg') }}" alt="Ana Costa" class="avatar-gradient w-14 h-14 rounded-full object-cover shadow-lg ring-4 ring-green-100">
                        <div class="flex-1">
                            <div class="font-bold text-gray-900 text-lg">Ana Costa</div>
                            <div class="text-sm text-gray-600">Contadora, Empresa ABC</div>
                            <div class="verified-badge inline-flex items-center gap-1 mt-1 px-2 py-0.5 bg-green-50 text-green-600 rounded-full text-xs font-semibold">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                Verificado
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-metric mb-4 relative z-10">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gradient-to-r from-green-500 to-teal-500 text-white shadow-md">100% compliance</span>
                    </div>
                    <p class="testimonial-text text-gray-700 mb-6 leading-relaxed relative z-10 text-base">
                        "A plataforma é intuitiva e os especialistas são excepcionais. Nosso compliance está 100% em dia."
                    </p>
                    <div class="testimonial-footer flex items-center justify-between relative z-10">
                        <div class="stars text-yellow-400 text-xl">★★★★★</div>
                        <div class="date text-xs text-gray-400">Há 3 meses</div>
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

<!-- FAQ Section -->
<section id="faq" class="bg-white pt-4 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Perguntas <span class="text-brand">Frequentes</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Tire suas dúvidas sobre o FiscalDock e como ele pode transformar seu dia a dia
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
    <!-- Divisor ondulado: transição do branco para o cinza (#f9fafb) -->
    <div class="pointer-events-none leading-none mt-6 sm:mt-8">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#f9fafb"></path>
        </svg>
    </div>
</section>

<!-- Contato Section -->
<section id="contato" class="bg-gray-50 pt-4 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Fale com <span class="text-brand">nossos especialistas</span>
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


<script>
// Armazenar referências aos handlers do carrossel para limpeza
window._solucoesCarouselHandlers = {
    prevArrowHandler: null,
    nextArrowHandler: null,
    pillHandlers: [],
    resizeHandler: null,
    resizeTimer: null,
    resizeObserver: null,
    prevArrow: null,
    nextArrow: null,
    pills: []
};

// Função de cleanup do carrossel
window.cleanupSolucoesCarousel = function() {
    try {
        // Verificar se os elementos ainda existem no DOM antes de limpar
        // Remover handlers das setas apenas se os elementos ainda existirem
        if (window._solucoesCarouselHandlers.prevArrow) {
            // Verificar se o elemento ainda está no DOM
            if (document.contains(window._solucoesCarouselHandlers.prevArrow) && window._solucoesCarouselHandlers.prevArrowHandler) {
                window._solucoesCarouselHandlers.prevArrow.onclick = null;
            }
        }
        if (window._solucoesCarouselHandlers.nextArrow) {
            // Verificar se o elemento ainda está no DOM
            if (document.contains(window._solucoesCarouselHandlers.nextArrow) && window._solucoesCarouselHandlers.nextArrowHandler) {
                window._solucoesCarouselHandlers.nextArrow.onclick = null;
            }
        }
        
        // Remover handlers dos pills apenas se ainda existirem no DOM
        window._solucoesCarouselHandlers.pills.forEach((pill, index) => {
            if (pill && document.contains(pill) && window._solucoesCarouselHandlers.pillHandlers[index]) {
                pill.onclick = null;
            }
        });
        
        // Remover resize handler
        if (window._solucoesCarouselHandlers.resizeHandler) {
            window.removeEventListener('resize', window._solucoesCarouselHandlers.resizeHandler);
        }
        
        // Limpar timer de resize
        if (window._solucoesCarouselHandlers.resizeTimer) {
            clearTimeout(window._solucoesCarouselHandlers.resizeTimer);
        }
        // Desconectar ResizeObserver
        if (window._solucoesCarouselHandlers.resizeObserver) {
            try {
                window._solucoesCarouselHandlers.resizeObserver.disconnect();
            } catch (error) {
                console.error('Erro ao desconectar ResizeObserver do carrossel:', error);
            }
        }
        
        // Resetar referências
        window._solucoesCarouselHandlers = {
            prevArrowHandler: null,
            nextArrowHandler: null,
            pillHandlers: [],
            resizeHandler: null,
            resizeTimer: null,
            resizeObserver: null,
            prevArrow: null,
            nextArrow: null,
            pills: []
        };
        
        // Resetar flag
        window._solucoesCarouselInitialized = false;
    } catch (error) {
        console.error('Erro ao limpar carrossel:', error);
    }
};

// Inicializador reutilizável para o carrossel da seção "Soluções que transformam a rotina"
window.initSolucoesCarousel = function() {
    // Limpar carrossel anterior se existir
    if (window._solucoesCarouselInitialized) {
        window.cleanupSolucoesCarousel();
    }
    
    try {
        let retryCount = 0;
        const maxRetries = 20;
        
        function initCarousel() {
            try {
                const track = document.querySelector('.solutions-cards-track');
                const cards = document.querySelectorAll('.solution-card:not(.solution-card-duplicate)');
                const pills = document.querySelectorAll('.solution-pill');
                const prevArrow = document.querySelector('.carousel-arrow-left');
                const nextArrow = document.querySelector('.carousel-arrow-right');
                const wrapper = document.querySelector('.solutions-cards-wrapper');

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
                
                // Armazenar referências aos elementos
                window._solucoesCarouselHandlers.prevArrow = prevArrow;
                window._solucoesCarouselHandlers.nextArrow = nextArrow;
                window._solucoesCarouselHandlers.pills = Array.from(pills);
                
                const totalCards = cards.length;
                let currentIndex = 0;
                let cardWidth = 0;
                let cardSpacing = 0;
                let firstCardLeftInTrack = 0;
                let measureRetries = 0;
                const maxMeasureRetries = 8;

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

                                    if (cardWidth <= 0 || cardSpacing <= 0 || isNaN(cardSpacing) || isNaN(firstCardLeftInTrack)) {
                                        if (measureRetries < maxMeasureRetries) {
                                            measureRetries++;
                                            setTimeout(updateReferenceValues, 120);
                                        } else {
                                            console.warn('[Carrossel] Medidas inválidas após', maxMeasureRetries, 'tentativas');
                                        }
                                        return;
                                    }

                                    measureRetries = 0;
                                    updateCarousel(currentIndex);
                                } catch (error) {
                                    console.error('Erro em updateReferenceValues:', error);
                                }
                            });
                        });
                    } catch (error) {
                        console.error('Erro ao atualizar valores de referência:', error);
                    }
                }

                // Observer para mudanças de layout que afetem medidas
                if (typeof ResizeObserver !== 'undefined') {
                    try {
                        if (window._solucoesCarouselHandlers.resizeObserver) {
                            window._solucoesCarouselHandlers.resizeObserver.disconnect();
                        }
                        const resizeObserver = new ResizeObserver(() => {
                            updateReferenceValues();
                        });
                        resizeObserver.observe(wrapper);
                        window._solucoesCarouselHandlers.resizeObserver = resizeObserver;
                    } catch (error) {
                        console.error('Erro ao observar redimensionamento do carrossel:', error);
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
                // Primeiro, remover handlers antigos se existirem
                if (prevArrow) {
                    // Limpar handler anterior se existir
                    if (window._solucoesCarouselHandlers.prevArrowHandler) {
                        prevArrow.onclick = null;
                    }
                    // Criar novo handler
                    window._solucoesCarouselHandlers.prevArrowHandler = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        goPrev();
                    };
                    prevArrow.onclick = window._solucoesCarouselHandlers.prevArrowHandler;
                }

                if (nextArrow) {
                    // Limpar handler anterior se existir
                    if (window._solucoesCarouselHandlers.nextArrowHandler) {
                        nextArrow.onclick = null;
                    }
                    // Criar novo handler
                    window._solucoesCarouselHandlers.nextArrowHandler = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        goNext();
                    };
                    nextArrow.onclick = window._solucoesCarouselHandlers.nextArrowHandler;
                }

                // Configurar pills
                // Limpar handlers antigos dos pills primeiro
                window._solucoesCarouselHandlers.pillHandlers = [];
                pills.forEach((pill, index) => {
                    // Limpar handler anterior se existir
                    pill.onclick = null;
                    // Criar novo handler
                    const pillHandler = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        currentIndex = index;
                        updateCarousel(currentIndex);
                    };
                    window._solucoesCarouselHandlers.pillHandlers[index] = pillHandler;
                    pill.onclick = pillHandler;
                });

                // Inicializar posicionamento
                updateReferenceValues();

                // Resize handler
                window._solucoesCarouselHandlers.resizeHandler = function() {
                    if (window._solucoesCarouselHandlers.resizeTimer) {
                        clearTimeout(window._solucoesCarouselHandlers.resizeTimer);
                    }
                    window._solucoesCarouselHandlers.resizeTimer = setTimeout(() => {
                        updateReferenceValues();
                    }, 250);
                };
                
                // Remover handler anterior se existir
                window.removeEventListener('resize', window._solucoesCarouselHandlers.resizeHandler);
                window.addEventListener('resize', window._solucoesCarouselHandlers.resizeHandler);
            } catch (error) {
                console.error('Erro ao inicializar carrossel:', error);
            }
        }

        // Executar inicialização imediatamente e com fallback
        initCarousel();
        setTimeout(function() {
            if (!window._solucoesCarouselInitialized) {
                initCarousel();
            }
        }, 300);
        setTimeout(function() {
            if (!window._solucoesCarouselInitialized) {
                initCarousel();
            }
        }, 800);
        setTimeout(function() {
            if (!window._solucoesCarouselInitialized) {
                initCarousel();
            }
        }, 1500);
    } catch (error) {
        console.error('[Carrossel] Erro crítico ao inicializar carrossel de soluções:', error);
    }
};

// Garantir inicialização também na carga completa (hard refresh)
(function garantirInitSolucoesAposLoad() {
    let attempts = 0;
    const maxAttempts = 12;
    
    function tentar() {
        const hasInitFn = typeof window.initSolucoesCarousel === 'function';
        const track = document.querySelector('.solutions-cards-track');
        const wrapper = document.querySelector('.solutions-cards-wrapper');
        
        if (hasInitFn && track && wrapper) {
            window.initSolucoesCarousel();
            return;
        }
        
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tentar, 150 + (attempts * 40));
        }
    }
    
    if (document.readyState === 'complete') {
        tentar();
    } else {
        window.addEventListener('load', tentar, { once: true });
    }
})();

// Registrar função de cleanup no sistema global
if (!window._cleanupFunctions) {
    window._cleanupFunctions = {};
}
window._cleanupFunctions.initSolucoesCarousel = window.cleanupSolucoesCarousel;

// A inicialização será feita através do sistema de funções específicas do SPA (initInicio)
</script>

<!-- Scripts carregados no layout -->
