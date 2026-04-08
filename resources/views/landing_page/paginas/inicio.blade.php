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

/* ── Como Funciona ── */
.cf-step {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.55s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
}
.cf-step.cf-visible { opacity: 1; transform: translateY(0); }
.cf-step:nth-child(1) { transition-delay: 0s; }
.cf-step:nth-child(2) { transition-delay: 0.12s; }
.cf-step:nth-child(3) { transition-delay: 0.24s; }
.cf-step:nth-child(4) { transition-delay: 0.36s; }

.cf-step .cf-icon-box {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.cf-step:hover .cf-icon-box {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px -8px rgba(11, 31, 58, 0.15);
}
</style>

<!-- Hero Section (refeito) -->
<section id="hero" class="relative overflow-hidden bg-gradient-to-br from-primary-700 to-primary-500 text-white" style="background: linear-gradient(135deg, #0b1f3a 0%, #1e4fa0 100%);">
    <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-20 sm:pb-24 lg:pt-12 lg:pb-28">
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
</section>

<!-- Integrações Oficiais Banner -->
<section class="relative bg-gray-100 border-y border-gray-200 py-5 sm:py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-4">
            Dados extraídos e cruzados de fontes oficiais
        </p>
        <div class="flex flex-wrap justify-center items-center gap-x-6 gap-y-3 sm:gap-x-10 md:gap-x-14 opacity-60">
            <div class="flex items-center gap-2 text-gray-600 font-bold text-lg sm:text-xl tracking-tight transition hover:text-gray-900 cursor-default">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7"></path>
                </svg>
                Receita Federal
            </div>
            
            <div class="flex items-center gap-2 text-gray-600 font-bold text-lg sm:text-xl tracking-tight transition hover:text-gray-900 cursor-default">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                SEFAZ
            </div>
            
            <div class="flex items-center gap-2 text-gray-600 font-bold text-lg sm:text-xl tracking-tight transition hover:text-gray-900 cursor-default">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                </svg>
                Portal da Transparência
            </div>
            
            <div class="flex items-center gap-2 text-gray-600 font-bold text-lg sm:text-xl tracking-tight transition hover:text-gray-900 cursor-default">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                SINTEGRA
            </div>
            
            <div class="flex items-center gap-2 text-gray-600 font-bold text-lg sm:text-xl tracking-tight transition hover:text-gray-900 cursor-default">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                </svg>
                PGFN
            </div>
        </div>
    </div>
</section>

<!-- Como Funciona Section -->
<section id="como-funciona" class="bg-gray-50 py-20 sm:py-24 lg:py-28">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center mb-16 sm:mb-20">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-400 mb-3">Passo a passo</p>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight mb-4">Como Funciona na Prática</h2>
            <p class="text-base text-gray-500 max-w-2xl mx-auto">
                Do upload do arquivo ao monitoramento contínuo — tudo automatizado para o seu escritório.
            </p>
        </div>

        <!-- Steps -->
        <div class="relative" id="cf-grid">

            <!-- Trilha horizontal desktop -->
            <div class="hidden md:block absolute top-[52px] left-[12.5%] right-[12.5%] h-0" style="border-top: 2px dashed #d1d5db;"></div>

            <div class="grid grid-cols-1 gap-14 md:grid-cols-4 md:gap-8 lg:gap-12">

                <!-- 1 — Importe -->
                <div class="cf-step text-center">
                    <div class="relative z-10 mx-auto mb-6">
                        <div class="cf-icon-box w-[104px] h-[104px] rounded-2xl mx-auto flex items-center justify-center" style="background-color: #eef2f7; border: 1px solid #dce3ed; box-shadow: 0 2px 8px -2px rgba(11,31,58,0.06);">
                            <svg class="w-10 h-10" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                        </div>
                        <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold text-white" style="background-color: #0b1f3a;">1</div>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">Importe</h3>
                    <p class="text-sm text-gray-500 leading-relaxed mb-3">Faça upload do arquivo fiscal e a plataforma extrai automaticamente notas, participantes e catálogo.</p>
                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide text-gray-500 bg-white border border-gray-200">SPED, EFD ou XML</span>
                </div>

                <!-- 2 — Cruze -->
                <div class="cf-step text-center">
                    <div class="relative z-10 mx-auto mb-6">
                        <div class="cf-icon-box w-[104px] h-[104px] rounded-2xl mx-auto flex items-center justify-center" style="background-color: #eef2f7; border: 1px solid #dce3ed; box-shadow: 0 2px 8px -2px rgba(11,31,58,0.06);">
                            <svg class="w-10 h-10" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold text-white" style="background-color: #0b1f3a;">2</div>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">Cruze</h3>
                    <p class="text-sm text-gray-500 leading-relaxed mb-3">Cada CNPJ é verificado em tempo real nas bases oficiais — situação cadastral, inscrição estadual e impedimentos.</p>
                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide text-gray-500 bg-white border border-gray-200">Receita, SEFAZ, CEIS</span>
                </div>

                <!-- 3 — Identifique -->
                <div class="cf-step text-center">
                    <div class="relative z-10 mx-auto mb-6">
                        <div class="cf-icon-box w-[104px] h-[104px] rounded-2xl mx-auto flex items-center justify-center" style="background-color: #fef8ee; border: 1px solid #f5e6c8; box-shadow: 0 2px 8px -2px rgba(11,31,58,0.06);">
                            <svg class="w-10 h-10" style="color: #b45309;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold text-white" style="background-color: #0b1f3a;">3</div>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">Identifique</h3>
                    <p class="text-sm text-gray-500 leading-relaxed mb-3">Fornecedores inaptos, IE suspensa, empresas no CEIS — tudo sinalizado antes que vire problema.</p>
                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide text-gray-500 bg-white border border-gray-200">Alertas automáticos</span>
                </div>

                <!-- 4 — Monitore -->
                <div class="cf-step text-center">
                    <div class="relative z-10 mx-auto mb-6">
                        <div class="cf-icon-box w-[104px] h-[104px] rounded-2xl mx-auto flex items-center justify-center" style="background-color: #eefbf5; border: 1px solid #c6eed8; box-shadow: 0 2px 8px -2px rgba(11,31,58,0.06);">
                            <svg class="w-10 h-10" style="color: #047857;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold text-white" style="background-color: #0b1f3a;">4</div>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">Monitore</h3>
                    <p class="text-sm text-gray-500 leading-relaxed mb-3">Acompanhe mudanças de situação cadastral dos seus participantes sem consultar um por um.</p>
                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide text-gray-500 bg-white border border-gray-200">Atualização contínua</span>
                </div>

            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var grid = document.getElementById('cf-grid');
        if (!grid) return;
        new IntersectionObserver(function(entries, obs) {
            entries.forEach(function(e) {
                if (e.isIntersecting) {
                    grid.querySelectorAll('.cf-step').forEach(function(s) { s.classList.add('cf-visible'); });
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.15 }).observe(grid);
    });
    </script>
</section>

<!-- Soluções Section -->
<section id="funcionalidades" class="bg-white py-20 sm:py-24 lg:py-28">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center mb-16 sm:mb-20">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-400 mb-3">Funcionalidades</p>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight mb-4">
                Tudo que o seu escritório precisa
            </h2>
            <p class="text-base text-gray-500 max-w-2xl mx-auto">
                Um ecossistema completo para compliance fiscal, monitoramento contínuo e decisões mais seguras.
            </p>
        </div>

        <!-- Grid — 3 colunas top + 3 bottom -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">

            <!-- 1. SPED / EFD -->
            <div class="group">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-5" style="background-color: #eef2f7; border: 1px solid #dce3ed;">
                    <svg class="w-7 h-7" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Auditoria e Compliance</h3>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">
                    Transforme os TXTs dos seus clientes em insights. Cruzamento automático de CFOPs, alíquotas e inconsistências antes do envio ao Fisco.
                </p>
                <ul class="space-y-2">
                    <li class="flex items-start text-xs text-gray-600">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Validação de blocos C e D linha a linha
                    </li>
                    <li class="flex items-start text-xs text-gray-600">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Cruzamento exato com malha fina
                    </li>
                </ul>
                <span class="inline-block mt-4 px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide text-gray-500 bg-gray-100 border border-gray-200">SPED / EFD</span>
            </div>

            <!-- 2. XML -->
            <div class="group">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-5" style="background-color: #eef2f7; border: 1px solid #dce3ed;">
                    <svg class="w-7 h-7" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Extração de XMLs</h3>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">
                    Importe lotes de arquivos XML. A plataforma varre tags fiscais e recria consolidações de faturamento em milissegundos.
                </p>
                <ul class="space-y-2">
                    <li class="flex items-start text-xs text-gray-600">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        NFC-e, NF-e, CT-e e DF-e mapeados
                    </li>
                    <li class="flex items-start text-xs text-gray-600">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Consolidação imediata de CSOSN
                    </li>
                </ul>
                <span class="inline-block mt-4 px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide text-gray-500 bg-gray-100 border border-gray-200">XMLs / NF-e</span>
            </div>

            <!-- 3. Monitoramento -->
            <div class="group relative">
                <div class="absolute -top-1 -left-1 flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background-color: #f87171;"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5" style="background-color: #dc2626;"></span>
                </div>
                <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-5" style="background-color: #fef8ee; border: 1px solid #f5e6c8;">
                    <svg class="w-7 h-7" style="color: #b45309;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Monitoramento 24/7</h3>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">
                    Acabe com notas canceladas por IE inapta. Monitoramento automático e massivo via SEFAZ Nacional com alertas em tempo real.
                </p>
                <ul class="space-y-2">
                    <li class="flex items-start text-xs text-gray-600">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #b45309;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Alertas via WhatsApp e Email
                    </li>
                    <li class="flex items-start text-xs text-gray-600">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #b45309;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Bloqueie envios para inidôneos
                    </li>
                </ul>
                <span class="inline-block mt-4 px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide text-gray-500 bg-gray-100 border border-gray-200">Participantes</span>
            </div>

            <!-- 4. BI -->
            <div class="group">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-5" style="background-color: #eefbf5; border: 1px solid #c6eed8;">
                    <svg class="w-7 h-7" style="color: #047857;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Business Intelligence</h3>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">
                    Transforme obrigações fiscais no melhor produto do seu escritório. Relatórios interativos com evolução de CMV e margens.
                </p>
                <ul class="space-y-2">
                    <li class="flex items-start text-xs text-gray-600">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #047857;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Gráficos prontos para o empresário
                    </li>
                    <li class="flex items-start text-xs text-gray-600">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #047857;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Contabilidade de alto valor sem esforço
                    </li>
                </ul>
                <span class="inline-block mt-4 px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide text-gray-500 bg-gray-100 border border-gray-200">B.I. Fiscal</span>
            </div>

            <!-- 5. Créditos & API -->
            <div class="group">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-5" style="background-color: #eef2f7; border: 1px solid #dce3ed;">
                    <svg class="w-7 h-7" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Créditos Sob Demanda</h3>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">
                    Sem mensalidades abusivas. Pague pelo uso ou conecte via API aberta ao seu ERP para consultas automatizadas.
                </p>
                <ul class="space-y-2">
                    <li class="flex items-start text-xs text-gray-600">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Créditos que não expiram
                    </li>
                    <li class="flex items-start text-xs text-gray-600">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Integração nativa com automações
                    </li>
                </ul>
                <span class="inline-block mt-4 px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide text-gray-500 bg-gray-100 border border-gray-200">Créditos & API</span>
            </div>

            <!-- 6. BPO Premium — card destaque -->
            <div class="group rounded-xl p-6 lg:p-8 relative" style="background-color: #0b1f3a;">
                <div class="absolute top-4 right-4">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide" style="background-color: #facc15; color: #0b1f3a;">Premium</span>
                </div>
                <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-5" style="background-color: rgba(250,204,21,0.12); border: 1px solid rgba(250,204,21,0.2);">
                    <svg class="w-7 h-7" style="color: #facc15;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white mb-2">Terceirização Fiscal</h3>
                <p class="text-sm leading-relaxed mb-4" style="color: rgba(255,255,255,0.55);">
                    Sem braço operacional? Nosso time comanda o compliance como uma extensão em nuvem do seu escritório.
                </p>
                <ul class="space-y-2">
                    <li class="flex items-start text-xs" style="color: rgba(255,255,255,0.5);">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #facc15;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Auditoria humana + tecnológica
                    </li>
                    <li class="flex items-start text-xs" style="color: rgba(255,255,255,0.5);">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #facc15;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Suporte tributário exclusivo
                    </li>
                </ul>
            </div>

        </div>
    </div>
</section>



<!-- Métricas Banner -->
<section id="metricas" class="relative py-8 sm:py-10" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%); box-shadow: inset 0 1px 0 rgba(255,255,255,0.05), inset 0 -1px 0 rgba(255,255,255,0.05);">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-3xl sm:text-4xl font-extrabold" style="color: #ffffff;">R$ 47M+</div>
                <p class="mt-2 text-xs sm:text-sm font-medium" style="color: rgba(255,255,255,0.55);">em notas fiscais importadas</p>
            </div>
            <div>
                <div class="text-3xl sm:text-4xl font-extrabold" style="color: #ffffff;">1.200+</div>
                <p class="mt-2 text-xs sm:text-sm font-medium" style="color: rgba(255,255,255,0.55);">CNPJs monitorados</p>
            </div>
            <div>
                <div class="text-3xl sm:text-4xl font-extrabold" style="color: #ffffff;">18.000+</div>
                <p class="mt-2 text-xs sm:text-sm font-medium" style="color: rgba(255,255,255,0.55);">documentos fiscais analisados</p>
            </div>
            <div>
                <div class="text-3xl sm:text-4xl font-extrabold" style="color: #ffffff;">R$ 2,3M+</div>
                <p class="mt-2 text-xs sm:text-sm font-medium" style="color: rgba(255,255,255,0.55);">em riscos fiscais detectados</p>
            </div>
        </div>
    </div>
</section>

<!-- IA Financeira Section -->
<section id="notebook" class="bg-gray-50 pt-20 pb-0 sm:pt-24 lg:pt-28">
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
</section>

<!-- Para Quem É Section -->
<section id="para-quem-e" class="bg-white pt-20 pb-20 sm:pt-24 sm:pb-24 lg:pt-28 lg:pb-28">
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
</section>

<!-- Diferenciais Section -->
<section id="diferenciais" class="bg-gray-50 pt-20 pb-20 sm:pt-24 sm:pb-24 lg:pt-28 lg:pb-28">
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
</section>

<!-- Segurança e LGPD Section -->
<section id="seguranca-lgpd" class="bg-white pt-20 pb-20 sm:pt-24 sm:pb-24 lg:pt-28 lg:pb-28">
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
</section>

{{-- Estatísticas Section removida a pedido do cliente --}}
<!-- Depoimentos Section -->
<section id="depoimentos" class="bg-gray-50 pt-20 pb-20 sm:pt-24 sm:pb-24 lg:pt-28 lg:pb-28">
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
</section>

<!-- FAQ Section -->
<section id="faq" class="bg-white pt-20 pb-0 sm:pt-24 lg:pt-28">
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
</section>

<!-- Contato Section -->
<section id="contato" class="bg-gray-50 pt-20 pb-20 sm:pt-24 lg:pt-28">
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
                <form id="contact-form" class="space-y-6">
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



<!-- Scripts carregados no layout -->
