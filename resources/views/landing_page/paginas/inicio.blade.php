@push('structured-data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Organization",
    "name": "FiscalDock",
    "url": "https://fiscaldock.com",
    "logo": "{{ asset('binary_files/logo/Logo FiscalDock.png') }}",
    "description": "Plataforma de inteligencia fiscal para contadores e escritorios contabeis. Importacao de SPED, monitoramento de participantes e compliance automatizado.",
    "sameAs": []
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebSite",
    "name": "FiscalDock",
    "url": "https://fiscaldock.com"
}
</script>
@endpush

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

/* Coluna direita: ícone + métrica */
.solution-card-inner > .grid > div:last-child {
    min-height: 280px;
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
    
    /* Em mobile, colunas empilhadas - ícone/métrica primeiro, texto depois */
    .solution-card-inner .grid {
        grid-template-columns: 1fr;
    }

    .solution-card-inner > .grid > div:first-child {
        min-height: auto;
        order: 2;
        padding: 1.25rem;
    }

    .solution-card-inner > .grid > div:last-child {
        order: 1;
        padding: 1.5rem;
        min-height: 200px;
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
                    Plataforma de inteligência fiscal para contadores
                </div>

                <!-- Título -->
                <h1 class="font-extrabold leading-tight tracking-tight text-3xl sm:text-4xl xl:text-5xl">
                    Importe seu SPED e descubra
                    <span class="block text-white [text-shadow:0_1px_4px_rgba(0,0,0,0.45),0_0_1px_rgba(0,0,0,0.35),0_0_8px_rgba(0,0,0,0.25)]">riscos fiscais em minutos</span>
                </h1>

                <!-- Subtítulo -->
                <p class="mt-4 text-base sm:text-lg text-white/80 max-w-2xl">
                    Cruze dados do SPED com a Receita Federal, SINTEGRA e CEIS para identificar fornecedores irregulares, notas com problemas e oportunidades de crédito tributário — antes que o fisco encontre.
                </p>

                <!-- CTAs -->
                <div class="mt-5">
                    <a href="/agendar" data-link data-button="cta" class="btn-cta">
                        <span class="whitespace-nowrap">Testar Gratuitamente</span>
                        <svg class="h-5 w-5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>

                <!-- Frase de apoio -->
                <p class="mt-3 text-sm text-white/70 max-w-2xl">
                    Feito para contadores e escritórios contábeis que querem proteger seus clientes contra riscos fiscais.
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
                        <p class="text-white/80 text-sm mt-1">Contadores que confiam no FiscalDock</p>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita: Mockup -->
            <div class="lg:col-span-7 xl:col-span-6">
                <div class="relative rounded-3xl border border-white/15 bg-white/5 p-2 shadow-lg shadow-blue-950/10">
                    <img
                        src="{{ asset('binary_files/mockups/dashboard-mockup.jpg') }}"
                        alt="Dashboard do FiscalDock"
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
                Soluções que <span class="text-brand">protegem</span> seu escritório
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Ferramentas integradas para importação, monitoramento e compliance fiscal
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

                    <!-- Card Duplicado: Importação XML (para loop infinito - início) -->
                    <div class="solution-card solution-card-duplicate flex-shrink-0 w-full px-4" data-index="duplicate" data-solution="xml">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <div class="flex flex-col justify-between p-8 lg:p-10 bg-white">
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 mb-4">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                            Importação
                                        </span>
                                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3">Notas fiscais organizadas automaticamente</h3>
                                        <p class="text-base text-gray-600 mb-6 leading-relaxed">Importe XMLs de NFe e o FiscalDock extrai chave de acesso, valores, CFOP e dados do emitente/destinatário.</p>
                                        <ul class="space-y-3 text-gray-600">
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Upload de XMLs com validacao automatica</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Chave de acesso e valores extraidos por nota</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Organizado por emitente, destinatario e CFOP</span></li>
                                        </ul>
                                    </div>
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="relative bg-gradient-to-br from-primary-700 to-primary-500 p-8 lg:p-12 flex flex-col items-center justify-center overflow-hidden">
                                    <div class="w-24 h-24 lg:w-32 lg:h-32 mb-8 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                        <img src="{{ asset('binary_files/icone-gif/page-optimization.gif') }}" alt="Importacao de XML" class="w-16 h-16 lg:w-20 lg:h-20 object-contain" loading="lazy">
                                    </div>
                                    <div class="text-center">
                                        <div class="text-4xl lg:text-5xl font-bold text-white mb-2">Automatico</div>
                                        <p class="text-base text-white/80">extracao inteligente de dados fiscais</p>
                                    </div>
                                    <div class="mt-6 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full">
                                        <p class="text-sm text-white/90 font-medium">Sem digitacao manual</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 1: Importação de SPED -->
                    <div class="solution-card active flex-shrink-0 w-full px-4" data-index="0" data-solution="sped" style="opacity: 1 !important;">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <div class="flex flex-col justify-between p-8 lg:p-10 bg-white">
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-violet-50 text-violet-700 mb-4">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            SPED/EFD
                                        </span>
                                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3">Importe SPED em segundos, nao em horas</h3>
                                        <p class="text-base text-gray-600 mb-6 leading-relaxed">Upload de EFD ICMS/IPI e PIS/COFINS com extracao automatica de participantes, notas e valores por bloco.</p>
                                        <ul class="space-y-3 text-gray-600">
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Arraste e importe arquivos .txt direto na plataforma</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Extracao automatica por bloco (A, C, D)</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Participantes identificados instantaneamente</span></li>
                                        </ul>
                                    </div>
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="relative bg-gradient-to-br from-primary-700 to-primary-500 p-8 lg:p-12 flex flex-col items-center justify-center overflow-hidden">
                                    <div class="w-24 h-24 lg:w-32 lg:h-32 mb-8 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                        <img src="{{ asset('binary_files/icone-gif/page-optimization.gif') }}" alt="Importacao de SPED" class="w-16 h-16 lg:w-20 lg:h-20 object-contain" loading="lazy">
                                    </div>
                                    <div class="text-center">
                                        <div class="text-4xl lg:text-5xl font-bold text-white mb-2">Segundos</div>
                                        <p class="text-base text-white/80">o que levava horas de trabalho manual</p>
                                    </div>
                                    <div class="mt-6 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full">
                                        <p class="text-sm text-white/90 font-medium">Extracao 100% automatica</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Monitoramento de Participantes -->
                    <div class="solution-card flex-shrink-0 w-full px-4" data-index="1" data-solution="monitoramento">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <div class="flex flex-col justify-between p-8 lg:p-10 bg-white">
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 mb-4">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                            Monitoramento
                                        </span>
                                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3">Saiba antes da fiscalizacao quando algo muda</h3>
                                        <p class="text-base text-gray-600 mb-6 leading-relaxed">Acompanhe fornecedores e clientes em tempo real. Alertas automaticos de CNPJ irregular, IE suspensa e mudancas na Receita Federal.</p>
                                        <ul class="space-y-3 text-gray-600">
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Consulta automatica de situacao cadastral</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Alertas de CNPJ baixado, IE suspensa ou CEIS</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Agrupamento por categoria e prioridade</span></li>
                                        </ul>
                                    </div>
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="relative bg-gradient-to-br from-primary-700 to-primary-500 p-8 lg:p-12 flex flex-col items-center justify-center overflow-hidden">
                                    <div class="w-24 h-24 lg:w-32 lg:h-32 mb-8 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                        <img src="{{ asset('binary_files/icone-gif/checklist.gif') }}" alt="Monitoramento de Participantes" class="w-16 h-16 lg:w-20 lg:h-20 object-contain" loading="lazy">
                                    </div>
                                    <div class="text-center">
                                        <div class="text-4xl lg:text-5xl font-bold text-white mb-2">450+</div>
                                        <p class="text-base text-white/80">empresas monitoradas</p>
                                    </div>
                                    <div class="mt-6 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full">
                                        <p class="text-sm text-white/90 font-medium">Alertas antes da fiscalizacao</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Consultas Tributárias em Lote -->
                    <div class="solution-card flex-shrink-0 w-full px-4" data-index="2" data-solution="consultas">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <div class="flex flex-col justify-between p-8 lg:p-10 bg-white">
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 mb-4">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                            Consultas
                                        </span>
                                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3">Centenas de CNPJs consultados de uma vez</h3>
                                        <p class="text-base text-gray-600 mb-6 leading-relaxed">Situacao cadastral, regime tributario, Simples Nacional e IE — com resultados em tempo real.</p>
                                        <ul class="space-y-3 text-gray-600">
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Upload de planilha ou selecao de participantes</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Progresso por CNPJ em tempo real</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Resultados exportaveis com alertas automaticos</span></li>
                                        </ul>
                                    </div>
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="relative bg-gradient-to-br from-primary-700 to-primary-500 p-8 lg:p-12 flex flex-col items-center justify-center overflow-hidden">
                                    <div class="w-24 h-24 lg:w-32 lg:h-32 mb-8 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                        <img src="{{ asset('binary_files/icone-gif/analyse.gif') }}" alt="Consultas em Lote" class="w-16 h-16 lg:w-20 lg:h-20 object-contain" loading="lazy">
                                    </div>
                                    <div class="text-center">
                                        <div class="text-4xl lg:text-5xl font-bold text-white mb-2">Tempo real</div>
                                        <p class="text-base text-white/80">resultados via Server-Sent Events</p>
                                    </div>
                                    <div class="mt-6 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full">
                                        <p class="text-sm text-white/90 font-medium">Consulte centenas de CNPJs de uma vez</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Dashboard e BI Fiscal -->
                    <div class="solution-card flex-shrink-0 w-full px-4" data-index="3" data-solution="dashboard">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <div class="flex flex-col justify-between p-8 lg:p-10 bg-white">
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 mb-4">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                            BI Fiscal
                                        </span>
                                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3">Visualize riscos e oportunidades em um clique</h3>
                                        <p class="text-base text-gray-600 mb-6 leading-relaxed">Dashboards interativos com faturamento, compras, tributos e analise inteligente por CFOP e participante.</p>
                                        <ul class="space-y-3 text-gray-600">
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Graficos interativos com filtros avancados</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Analise por CFOP, participante e tipo de EFD</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Visao consolidada de entradas, saidas e tributos</span></li>
                                        </ul>
                                    </div>
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="relative bg-gradient-to-br from-primary-700 to-primary-500 p-8 lg:p-12 flex flex-col items-center justify-center overflow-hidden">
                                    <div class="w-24 h-24 lg:w-32 lg:h-32 mb-8 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                        <img src="{{ asset('binary_files/icone-gif/clipboard-gear.gif') }}" alt="Dashboard e BI Fiscal" class="w-16 h-16 lg:w-20 lg:h-20 object-contain" loading="lazy">
                                    </div>
                                    <div class="text-center">
                                        <div class="text-4xl lg:text-5xl font-bold text-white mb-2">6 abas</div>
                                        <p class="text-base text-white/80">de analise fiscal integrada</p>
                                    </div>
                                    <div class="mt-6 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full">
                                        <p class="text-sm text-white/90 font-medium">Dados atualizados automaticamente</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 5: Importação de XML -->
                    <div class="solution-card flex-shrink-0 w-full px-4" data-index="4" data-solution="xml">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <div class="flex flex-col justify-between p-8 lg:p-10 bg-white">
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 mb-4">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                            Importacao
                                        </span>
                                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3">Notas fiscais organizadas automaticamente</h3>
                                        <p class="text-base text-gray-600 mb-6 leading-relaxed">Importe XMLs de NFe e o FiscalDock extrai chave de acesso, valores, CFOP e dados do emitente/destinatario.</p>
                                        <ul class="space-y-3 text-gray-600">
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Upload de XMLs com validacao automatica</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Chave de acesso e valores extraidos por nota</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Organizado por emitente, destinatario e CFOP</span></li>
                                        </ul>
                                    </div>
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="relative bg-gradient-to-br from-primary-700 to-primary-500 p-8 lg:p-12 flex flex-col items-center justify-center overflow-hidden">
                                    <div class="w-24 h-24 lg:w-32 lg:h-32 mb-8 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                        <img src="{{ asset('binary_files/icone-gif/page-optimization.gif') }}" alt="Importacao de XML" class="w-16 h-16 lg:w-20 lg:h-20 object-contain" loading="lazy">
                                    </div>
                                    <div class="text-center">
                                        <div class="text-4xl lg:text-5xl font-bold text-white mb-2">Automatico</div>
                                        <p class="text-base text-white/80">extracao inteligente de dados fiscais</p>
                                    </div>
                                    <div class="mt-6 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full">
                                        <p class="text-sm text-white/90 font-medium">Sem digitacao manual</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Duplicado: Importação SPED (para loop infinito - fim) -->
                    <div class="solution-card solution-card-duplicate flex-shrink-0 w-full px-4" data-index="duplicate-end" data-solution="sped">
                        <div class="rounded-2xl shadow-xl overflow-hidden solution-card-inner">
                            <div class="grid grid-cols-1 lg:grid-cols-2 h-full">
                                <div class="flex flex-col justify-between p-8 lg:p-10 bg-white">
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-violet-50 text-violet-700 mb-4">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            SPED/EFD
                                        </span>
                                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3">Importe SPED em segundos, nao em horas</h3>
                                        <p class="text-base text-gray-600 mb-6 leading-relaxed">Upload de EFD ICMS/IPI e PIS/COFINS com extracao automatica de participantes, notas e valores por bloco.</p>
                                        <ul class="space-y-3 text-gray-600">
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Arraste e importe arquivos .txt direto na plataforma</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Extracao automatica por bloco (A, C, D)</span></li>
                                            <li class="flex items-start gap-3"><div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div><span class="text-sm">Participantes identificados instantaneamente</span></li>
                                        </ul>
                                    </div>
                                    <div class="mt-6">
                                        <a href="#solucoes-detalhadas" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                                            Saiba mais
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="relative bg-gradient-to-br from-primary-700 to-primary-500 p-8 lg:p-12 flex flex-col items-center justify-center overflow-hidden">
                                    <div class="w-24 h-24 lg:w-32 lg:h-32 mb-8 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                        <img src="{{ asset('binary_files/icone-gif/page-optimization.gif') }}" alt="Importacao de SPED" class="w-16 h-16 lg:w-20 lg:h-20 object-contain" loading="lazy">
                                    </div>
                                    <div class="text-center">
                                        <div class="text-4xl lg:text-5xl font-bold text-white mb-2">Segundos</div>
                                        <p class="text-base text-white/80">o que levava horas de trabalho manual</p>
                                    </div>
                                    <div class="mt-6 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full">
                                        <p class="text-sm text-white/90 font-medium">Extracao 100% automatica</p>
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
                    <button class="solution-pill active px-4 sm:px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="sped">
                        Importacao SPED
                    </button>
                    <button class="solution-pill px-4 sm:px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="monitoramento">
                        Monitoramento
                    </button>
                    <button class="solution-pill px-4 sm:px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="consultas">
                        Consultas em Lote
                    </button>
                    <button class="solution-pill px-4 sm:px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="dashboard">
                        Dashboard e BI
                    </button>
                    <button class="solution-pill px-4 sm:px-5 py-2 rounded-full text-sm font-semibold transition-all duration-300" data-target="xml">
                        Importacao XML
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
                Por que contadores escolhem o <span class="text-brand">FiscalDock</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Automatize horas de trabalho manual e proteja seus clientes contra riscos fiscais
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('binary_files/icone-gif/financial-decline.gif') }}" alt="Financial Decline" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Importe SPED e identifique riscos em minutos — o que levava dias agora é automático</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('binary_files/icone-gif/contract.gif') }}" alt="Contract" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Fornecedores irregulares, notas com problemas e créditos indevidos no radar antes da auditoria</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('binary_files/icone-gif/process.gif') }}" alt="Process" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Consulte centenas de CNPJs de uma vez com resultados em tempo real</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('binary_files/icone-gif/budgeting.gif') }}" alt="Budgeting" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Dashboards interativos com faturamento, compras, tributos e análise por CFOP</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('binary_files/icone-gif/customer-service.gif') }}" alt="Customer Service" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Alertas automáticos quando a situação cadastral de um participante muda</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 hover:border-blue-500 text-center">
                <div class="mb-4">
                    <img src="{{ asset('binary_files/icone-gif/investment.gif') }}" alt="Investment" class="w-16 h-16 mx-auto object-contain" loading="lazy" decoding="async" width="64" height="64">
                </div>
                <p class="text-gray-600 font-medium">Dados de SPED, participantes e notas fiscais centralizados em um único lugar</p>
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

<!-- Métricas Section -->
<section id="metricas" class="bg-gray-50 pt-8 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-3xl sm:text-4xl font-bold text-blue-600">R$ 2,3M+</div>
                <p class="text-gray-600 mt-2 text-sm">em riscos detectados</p>
            </div>
            <div>
                <div class="text-3xl sm:text-4xl font-bold text-blue-600">450+</div>
                <p class="text-gray-600 mt-2 text-sm">empresas monitoradas</p>
            </div>
            <div>
                <div class="text-3xl sm:text-4xl font-bold text-blue-600">12.000+</div>
                <p class="text-gray-600 mt-2 text-sm">notas analisadas</p>
            </div>
            <div>
                <div class="text-3xl sm:text-4xl font-bold text-blue-600">99,8%</div>
                <p class="text-gray-600 mt-2 text-sm">uptime da plataforma</p>
            </div>
        </div>
    </div>
</section>

<!-- IA Financeira Section -->
<section id="notebook" class="bg-gray-50 pt-4 pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <div class="text-sm font-semibold text-blue-600 uppercase tracking-wide">
                    Inteligência fiscal automatizada
                </div>
                <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 leading-tight">
                    Deixe o FiscalDock cuidar do compliance fiscal
                </h2>
                <p class="text-lg text-gray-600">
                    Importe seus arquivos SPED, monitore participantes e receba alertas de risco automaticamente. Tudo em uma plataforma simples e intuitiva feita para contadores.
                </p>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="/agendar"
                       class="inline-flex items-center justify-center px-6 py-3 rounded-full text-base font-semibold text-blue-900 bg-yellow-400 hover:bg-yellow-300 transition">
                        Testar Gratuitamente
                    </a>
                    <a href="/solucoes"
                       class="inline-flex items-center justify-center px-6 py-3 rounded-full text-base font-semibold text-blue-600 border-2 border-blue-500 hover:text-white hover:bg-blue-500 transition">
                        Ver Soluções
                    </a>
                </div>
            </div>
            <div class="relative">
                <div class="absolute inset-0 -z-10">
                    <div class="w-48 h-48 bg-blue-100 rounded-full blur-3xl opacity-50 absolute top-0 left-6"></div>
                    <div class="w-40 h-40 bg-yellow-100 rounded-full blur-3xl opacity-50 absolute bottom-6 right-0"></div>
                </div>
                <img src="{{ asset('binary_files/mockups/macbook-mockup.png') }}" alt="Dashboard do FiscalDock em um notebook" class="w-full max-w-2xl mx-auto drop-shadow-2xl">
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
                O FiscalDock atende escritórios contábeis, empresas e contadores autônomos que precisam de compliance fiscal, monitoramento e agilidade
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
                        <span>Importação de SPED com extração automática de participantes e notas</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Monitoramento contínuo de fornecedores e clientes na Receita Federal</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Alertas automáticos de riscos fiscais e situação cadastral</span>
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
                        <span>Visibilidade completa dos fornecedores e sua situação cadastral</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Dashboards com faturamento, compras e análise tributária</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Consultas em lote de CNPJ com resultados em tempo real</span>
                    </li>
                </ul>
            </div>

            <div class="bg-white rounded-lg p-8 border border-gray-200 hover:border-blue-500 transition-colors">
                <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Contadores Autônomos</h3>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Planos acessíveis com créditos para consultas tributárias</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Dashboard simplificado para acompanhar seus clientes</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">✓</span>
                        <span>Importação de SPED e XML em uma interface intuitiva</span>
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
                Por que o FiscalDock é <span class="text-brand">diferente</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Não substitui seu sistema contábil: complementa com inteligência fiscal, monitoramento e compliance automatizado
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
                            Não substitui Domínio, Alterdata ou Contmatic. O FiscalDock atua como uma camada de inteligência fiscal: importa seus SPEDs, monitora participantes e gera alertas automaticamente.
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
                            Não é só análise de dados: é importação, extração, monitoramento, consulta e alerta — tudo integrado e automatizado no mesmo lugar.
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
                            Cada consulta, cada importação, cada alerta é registrado. Saiba exatamente quando um fornecedor mudou de status e qual ação foi tomada.
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
                            Importação de SPED com extração automática, consultas em lote com progresso em tempo real, alertas disparados sem intervenção manual.
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
                    Resultados reais de escritórios contábeis que transformaram seu compliance fiscal com o FiscalDock
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
                            <div class="text-sm text-gray-600">Sócia, Silva Contabilidade</div>
                            <div class="verified-badge inline-flex items-center gap-1 mt-1 px-2 py-0.5 bg-green-50 text-green-600 rounded-full text-xs font-semibold">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                Verificado
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-metric mb-4 relative z-10">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-md">80% menos tempo</span>
                    </div>
                    <p class="testimonial-text text-gray-700 mb-6 leading-relaxed relative z-10 text-base">
                        "Antes do FiscalDock, levávamos dias para revisar fornecedores de um único cliente. Agora importamos o SPED e temos tudo em minutos."
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
                            <div class="text-sm text-gray-600">Contador, Santos & Associados</div>
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
                        "As consultas em lote e os alertas automáticos reduziram em 70% o tempo que gastávamos verificando situação cadastral de participantes."
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
                            <div class="text-sm text-gray-600">Contadora, Costa Assessoria Contábil</div>
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
                        "Com o monitoramento contínuo e os dashboards, nosso compliance fiscal está 100% em dia. Identificamos um fornecedor no CEIS antes da auditoria."
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
                    <span>O que é o FiscalDock e para quem é?</span>
                    <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer hidden">
                    <div class="px-6 py-4 text-gray-600 bg-white border-t border-gray-100">
                        O FiscalDock é uma plataforma de inteligência fiscal para contadores e escritórios contábeis. Permite importar arquivos SPED (EFD ICMS/IPI e PIS/COFINS), monitorar a situação cadastral de participantes, fazer consultas tributárias em lote e visualizar tudo em dashboards interativos.
                    </div>
                </div>
            </div>

            <div class="faq-item border border-gray-200 rounded-lg mb-4 hover:border-blue-500 transition-colors overflow-hidden">
                <button class="faq-question w-full text-left px-6 py-4 font-semibold text-gray-900 hover:text-blue-500 flex justify-between items-center">
                    <span>Como funciona a importação de SPED?</span>
                    <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer hidden">
                    <div class="px-6 py-4 text-gray-600 bg-white border-t border-gray-100">
                        Você faz o upload do arquivo .txt do SPED e o FiscalDock extrai automaticamente participantes, notas fiscais e valores organizados por bloco (A, C, D). O progresso é exibido em tempo real e, ao final, você tem um resumo completo com totais por bloco e por participante.
                    </div>
                </div>
            </div>

            <div class="faq-item border border-gray-200 rounded-lg mb-4 hover:border-blue-500 transition-colors overflow-hidden">
                <button class="faq-question w-full text-left px-6 py-4 font-semibold text-gray-900 hover:text-blue-500 flex justify-between items-center">
                    <span>O FiscalDock substitui meu sistema contábil?</span>
                    <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer hidden">
                    <div class="px-6 py-4 text-gray-600 bg-white border-t border-gray-100">
                        Não. O FiscalDock complementa sistemas como Domínio, Alterdata e Contmatic. Ele atua como uma camada de inteligência fiscal: importa seus SPEDs, monitora participantes na Receita Federal e SINTEGRA, e gera alertas automáticos de riscos.
                    </div>
                </div>
            </div>

            <div class="faq-item border border-gray-200 rounded-lg mb-4 hover:border-blue-500 transition-colors overflow-hidden">
                <button class="faq-question w-full text-left px-6 py-4 font-semibold text-gray-900 hover:text-blue-500 flex justify-between items-center">
                    <span>Quais riscos fiscais o FiscalDock detecta?</span>
                    <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer hidden">
                    <div class="px-6 py-4 text-gray-600 bg-white border-t border-gray-100">
                        O FiscalDock cruza dados do SPED com fontes como Receita Federal, SINTEGRA e CEIS para identificar fornecedores com CNPJ irregular, IE suspensa, empresas inidôneas e divergências em notas fiscais. Alertas são gerados automaticamente quando algo muda.
                    </div>
                </div>
            </div>

            <div class="faq-item border border-gray-200 rounded-lg mb-4 hover:border-blue-500 transition-colors overflow-hidden">
                <button class="faq-question w-full text-left px-6 py-4 font-semibold text-gray-900 hover:text-blue-500 flex justify-between items-center">
                    <span>Posso testar antes de assinar?</span>
                    <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer hidden">
                    <div class="px-6 py-4 text-gray-600 bg-white border-t border-gray-100">
                        Sim! Oferecemos um período de teste gratuito para que você conheça todas as funcionalidades. Agende uma demonstração e comece a proteger seus clientes contra riscos fiscais.
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
                Agende uma demonstração gratuita e descubra como o FiscalDock pode transformar o compliance fiscal do seu escritório
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
                            <div class="text-gray-900 font-semibold">contato@fiscaldock.com</div>
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
                        Agende uma demonstração sem compromisso e descubra como o FiscalDock pode proteger seus clientes contra riscos fiscais.
                    </p>
                    <a href="/agendar" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors inline-block">Agendar Agora</a>
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
