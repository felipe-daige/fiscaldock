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

    .demo-area {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-radius: 1rem;
        padding: 3rem;
        position: relative;
    }

    /* Estilos específicos de CNDs */
    @keyframes pulse-button {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(234, 179, 8, 0.7);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 0 0 10px rgba(234, 179, 8, 0);
        }
    }
    
    .pulse-button {
        animation: pulse-button 2s ease-in-out infinite;
    }
    
    @keyframes shake {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-5deg); }
        75% { transform: rotate(5deg); }
    }
    
    .shake-notification {
        animation: shake 0.5s ease-in-out infinite;
        animation-duration: 2s;
    }
    
    .health-card {
        opacity: 0;
        animation: fadeInUpHealth 0.6s ease forwards;
    }
    
    .health-card:nth-child(1) { animation-delay: 0.1s; }
    .health-card:nth-child(2) { animation-delay: 0.2s; }
    .health-card:nth-child(3) { animation-delay: 0.3s; }
    .health-card:nth-child(4) { animation-delay: 0.4s; }
    
    @keyframes fadeInUpHealth {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 640px) {
        .hero-gradient h1 {
            font-size: 2rem;
        }
        
        .hero-gradient p {
            font-size: 1rem;
        }

        .demo-area {
            padding: 2rem 1rem;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero-gradient py-12 md:py-20 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in-up">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 md:mb-6">
                Radar Fiscal: Monitoramento de CNDs em Tempo Real
            </h1>
            <p class="text-lg sm:text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-6 md:mb-8 px-4">
                Antecipe problemas. Nunca mais deixe uma certidão vencida travar os negócios do seu cliente.
            </p>
        </div>
    </div>
</section>

<!-- Seção 2: Demonstração Visual Interativa -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="demo-area">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Veja Como Funciona</h2>
                <p class="text-lg text-gray-600">Monitoramento automático de CNDs em tempo real</p>
            </div>
            <div class="flex justify-center">
                <div class="relative">
                    <img src="{{ asset('icone-gif/alerta.gif') }}" alt="Monitoramento de CNDs" class="w-full max-w-md object-contain rounded-lg shadow-lg">
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
                Nossa solução de <strong class="text-gray-900">Monitoramento Automático de CNDs</strong> transforma 
                a gestão de certidões fiscais de um processo reativo e manual em uma operação proativa e automatizada.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div class="bg-white rounded-lg p-6 text-center shadow-sm feature-card">
                <div class="text-4xl mb-4">🔄</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Monitoramento Automático</h3>
                <p class="text-gray-600">Verificação contínua da validade das CNDs de todas as empresas do seu portfólio.</p>
            </div>
            <div class="bg-white rounded-lg p-6 text-center shadow-sm feature-card">
                <div class="text-4xl mb-4">🏛️</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Consulta Automática</h3>
                <p class="text-gray-600">Sistema consulta automaticamente bases Federal, Estadual e Municipal sem intervenção manual.</p>
            </div>
            <div class="bg-white rounded-lg p-6 text-center shadow-sm feature-card">
                <div class="text-4xl mb-4">⚡</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Eliminação de Trabalho Manual</h3>
                <p class="text-gray-600">Sem necessidade de resolver captchas ou fazer consultas manuais. Tudo automatizado.</p>
            </div>
        </div>
    </div>
</section>

<!-- Seção 4: Automação Inteligente -->
<section class="py-16 bg-white section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Automação Inteligente</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Transforme a gestão de certidões fiscais de reativa para proativa
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            <!-- Card 1: Monitoramento 24/7 -->
            <div class="feature-card bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-6 md:p-8 shadow-sm">
                <div class="text-4xl md:text-5xl mb-4">📊</div>
                <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 md:mb-4">Monitoramento 24/7</h3>
                <p class="text-gray-700 mb-4 text-sm md:text-base">
                    Verificação contínua da validade das CNDs. Receba alertas imediatos sobre qualquer mudança 
                    no status das certidões, mantendo você sempre um passo à frente.
                </p>
                <p class="text-base md:text-lg font-semibold text-blue-600">
                    Sempre um passo à frente
                </p>
            </div>

            <!-- Card 2: Alertas Preventivos -->
            <div class="feature-card bg-gradient-to-br from-yellow-50 to-amber-50 rounded-lg p-6 md:p-8 shadow-sm">
                <div class="text-4xl md:text-5xl mb-4 shake-notification">🔔</div>
                <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 md:mb-4">Alertas Preventivos</h3>
                <p class="text-gray-700 mb-4 text-sm md:text-base">
                    Notificações automáticas antes do vencimento das certidões. Renove com um clique e evite 
                    bloqueios que possam impactar os negócios do seu cliente.
                </p>
                <p class="text-base md:text-lg font-semibold text-amber-600">
                    Evite bloqueios antes que aconteçam
                </p>
            </div>

            <!-- Card 3: Painel de Saúde -->
            <div class="feature-card bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-6 md:p-8 shadow-sm">
                <div class="text-4xl md:text-5xl mb-4">💚</div>
                <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 md:mb-4">Painel de Saúde</h3>
                <p class="text-gray-700 mb-4 text-sm md:text-base">
                    Visualização única de empresas saudáveis vs. empresas que exigem atenção. Tenha uma visão 
                    completa do status fiscal de todo o seu portfólio em um só lugar.
                </p>
                <p class="text-base md:text-lg font-semibold text-green-600">
                    Visão completa do seu portfólio
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Seção 5: Painel de Saúde Fiscal -->
<section class="py-20 bg-gray-50 section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Painel de Saúde Fiscal
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Visualize o status de todas as empresas do seu portfólio em tempo real
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card Empresa 1 - Regular -->
            <div class="health-card bg-white rounded-lg shadow-sm p-6 border-2 border-green-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Empresa ABC</h3>
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Federal:</span>
                        <span class="text-green-600 font-medium">Regular</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Estadual:</span>
                        <span class="text-green-600 font-medium">Regular</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Municipal:</span>
                        <span class="text-green-600 font-medium">Regular</span>
                    </div>
                </div>
            </div>

            <!-- Card Empresa 2 - Regular -->
            <div class="health-card bg-white rounded-lg shadow-sm p-6 border-2 border-green-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Empresa XYZ</h3>
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Federal:</span>
                        <span class="text-green-600 font-medium">Regular</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Estadual:</span>
                        <span class="text-green-600 font-medium">Regular</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Municipal:</span>
                        <span class="text-green-600 font-medium">Regular</span>
                    </div>
                </div>
            </div>

            <!-- Card Empresa 3 - Regular -->
            <div class="health-card bg-white rounded-lg shadow-sm p-6 border-2 border-green-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Empresa DEF</h3>
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Federal:</span>
                        <span class="text-green-600 font-medium">Regular</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Estadual:</span>
                        <span class="text-green-600 font-medium">Regular</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Municipal:</span>
                        <span class="text-green-600 font-medium">Regular</span>
                    </div>
                </div>
            </div>

            <!-- Card Empresa 4 - Alerta (Destaque) -->
            <div class="health-card bg-white rounded-lg shadow-lg p-6 border-2 border-yellow-400 hover:shadow-xl transition-shadow relative">
                <div class="absolute -top-3 -right-3">
                    <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center shake-notification">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Empresa GHI</h3>
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="space-y-2 mb-4">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Federal:</span>
                        <span class="text-green-600 font-medium">Regular</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Estadual:</span>
                        <span class="text-yellow-600 font-medium">Vence em 5 dias</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Municipal:</span>
                        <span class="text-green-600 font-medium">Regular</span>
                    </div>
                </div>
                <button class="pulse-button w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                    Renovar Agora
                </button>
            </div>
        </div>

        <!-- Notificação de Alerta -->
        <div class="mt-12 bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-600 shake-notification" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-yellow-900 mb-2">Alerta Preventivo</h3>
                    <p class="text-yellow-800">
                        Você tem <strong>1 certidão</strong> vencendo nos próximos 5 dias. Renove agora para evitar bloqueios 
                        que possam impactar os negócios do seu cliente.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção 6: Call to Action -->
<section class="hero-gradient py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
            Agora temos a proteção do cliente garantida.
        </h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
            Agende uma demonstração e veja como o Monitoramento de CNDs pode transformar a gestão fiscal 
            do seu escritório, mantendo você sempre um passo à frente.
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
