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

    @keyframes pulse-badge {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.05);
            opacity: 0.9;
        }
    }
    
    .match-badge {
        animation: pulse-badge 2s ease-in-out infinite;
    }
    
    .process-step {
        opacity: 0;
        animation: fadeInUpStep 0.6s ease forwards;
    }
    
    .process-step:nth-child(1) { animation-delay: 0.1s; }
    .process-step:nth-child(2) { animation-delay: 0.3s; }
    .process-step:nth-child(3) { animation-delay: 0.5s; }
    
    @keyframes fadeInUpStep {
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
                Conciliação Bancária que te Devolve o Tempo
            </h1>
            <p class="text-lg sm:text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-6 md:mb-8 px-4">
                Diga adeus ao 'bater' extrato. Seu financeiro e sua contabilidade em perfeita sintonia.
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
                <p class="text-lg text-gray-600">Conciliação bancária automatizada em ação</p>
            </div>
            <div class="flex justify-center">
                <div class="relative">
                    <img src="{{ asset('icone-gif/process.gif') }}" alt="Processo de Conciliação" class="w-full max-w-md object-contain rounded-lg shadow-lg">
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
                Nossa solução de <strong class="text-gray-900">Conciliação Bancária Automatizada</strong> transforma 
                um processo manual e demorado em uma operação rápida e precisa.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div class="bg-white rounded-lg p-6 text-center shadow-sm feature-card">
                <div class="text-4xl mb-4">📊</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Leitura Automática</h3>
                <p class="text-gray-600">Importe extratos bancários OFX em segundos, sem digitação manual.</p>
            </div>
            <div class="bg-white rounded-lg p-6 text-center shadow-sm feature-card">
                <div class="text-4xl mb-4">🔗</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Cruzamento Inteligente</h3>
                <p class="text-gray-600">Sistema cruza automaticamente com Notas Fiscais e impostos já registrados.</p>
            </div>
            <div class="bg-white rounded-lg p-6 text-center shadow-sm feature-card">
                <div class="text-4xl mb-4">✅</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Identificação Automática</h3>
                <p class="text-gray-600">Reconhece pagamentos e recebimentos sem intervenção manual.</p>
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
                Tecnologia de ponta que garante precisão e economia de tempo na conciliação bancária
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            <!-- Card 1: Match Inteligente -->
            <div class="feature-card bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-6 md:p-8 shadow-sm">
                <div class="text-4xl md:text-5xl mb-4">🤖</div>
                <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 md:mb-4">Match Inteligente</h3>
                <p class="text-gray-700 mb-4 text-sm md:text-base">
                    Machine learning avançado para reconhecer despesas mesmo sem nota fiscal associada. 
                    O sistema aprende com seus padrões e melhora a cada conciliação.
                </p>
                <p class="text-base md:text-lg font-semibold text-blue-600">
                    Aprende e melhora continuamente
                </p>
            </div>

            <!-- Card 2: Integridade Garantida -->
            <div class="feature-card bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-6 md:p-8 shadow-sm">
                <div class="text-4xl md:text-5xl mb-4">🛡️</div>
                <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 md:mb-4">Integridade Garantida</h3>
                <p class="text-gray-700 mb-4 text-sm md:text-base">
                    Encontre divergências em segundos. O sistema identifica automaticamente 
                    inconsistências entre extratos e registros contábeis, garantindo precisão total.
                </p>
                <p class="text-base md:text-lg font-semibold text-green-600">
                    Precisão total garantida
                </p>
            </div>

            <!-- Card 3: Foco no Excepcional -->
            <div class="feature-card bg-gradient-to-br from-yellow-50 to-amber-50 rounded-lg p-6 md:p-8 shadow-sm">
                <div class="text-4xl md:text-5xl mb-4">⚡</div>
                <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 md:mb-4">Foco no Excepcional</h3>
                <p class="text-gray-700 mb-4 text-sm md:text-base">
                    Revise apenas os 5% que não deram match automático. Economize tempo focando 
                    apenas no que realmente precisa da sua atenção.
                </p>
                <p class="text-base md:text-lg font-semibold text-amber-600">
                    Economize tempo focando no essencial
                </p>
            </div>
        </div>

        <!-- Badge de Match Rate -->
        <div class="mt-12 text-center">
            <div class="inline-block match-badge bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-8 py-4 rounded-full shadow-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-2xl font-bold">MATCH 95%</span>
                </div>
            </div>
            <p class="text-gray-600 mt-4 text-lg">Taxa média de conciliação automática</p>
        </div>
    </div>
</section>

<!-- Seção 5: Como Funciona -->
<section class="py-20 bg-gray-50 section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Como Funciona
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Um processo simples em três passos que transforma horas de trabalho em minutos
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Passo 1: Upload -->
            <div class="process-step text-center">
                <div class="relative mb-6">
                    <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto">
                        <img src="{{ asset('icone-gif/secure-payment.gif') }}" alt="Upload" class="w-16 h-16 object-contain">
                    </div>
                    <div class="absolute -top-2 -right-2 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-sm">
                        1
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Upload do Extrato</h3>
                <p class="text-gray-600 leading-relaxed">
                    Faça upload do arquivo OFX do seu banco. O sistema processa automaticamente 
                    todas as transações em segundos.
                </p>
            </div>

            <!-- Passo 2: Processamento -->
            <div class="process-step text-center">
                <div class="relative mb-6">
                    <div class="w-24 h-24 bg-indigo-100 rounded-full flex items-center justify-center mx-auto">
                        <img src="{{ asset('icone-gif/analyse.gif') }}" alt="Processamento" class="w-16 h-16 object-contain">
                    </div>
                    <div class="absolute -top-2 -right-2 w-8 h-8 bg-indigo-500 text-white rounded-full flex items-center justify-center font-bold text-sm">
                        2
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Cruzamento Automático</h3>
                <p class="text-gray-600 leading-relaxed">
                    O sistema cruza automaticamente com Notas Fiscais, impostos e lançamentos 
                    contábeis usando inteligência artificial.
                </p>
            </div>

            <!-- Passo 3: Resultado -->
            <div class="process-step text-center">
                <div class="relative mb-6">
                    <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                        <img src="{{ asset('icone-gif/accounting.gif') }}" alt="Resultado" class="w-16 h-16 object-contain">
                    </div>
                    <div class="absolute -top-2 -right-2 w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold text-sm">
                        3
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Revisão e Aprovação</h3>
                <p class="text-gray-600 leading-relaxed">
                    Revise apenas os casos excepcionais. Aprove os matches automáticos e 
                    ajuste apenas o necessário.
                </p>
            </div>
        </div>

        <!-- Ilustração Visual -->
        <div class="mt-16 flex justify-center">
            <div class="relative max-w-4xl w-full">
                <!-- Linha conectando os passos -->
                <div class="hidden md:block absolute top-12 left-0 right-0 h-1 bg-gradient-to-r from-blue-200 via-indigo-200 to-green-200"></div>
                
                <!-- Círculos de conexão -->
                <div class="hidden md:flex justify-between items-center relative z-10">
                    <div class="w-6 h-6 bg-blue-500 rounded-full border-4 border-white shadow-lg"></div>
                    <div class="w-6 h-6 bg-indigo-500 rounded-full border-4 border-white shadow-lg"></div>
                    <div class="w-6 h-6 bg-green-500 rounded-full border-4 border-white shadow-lg"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção 6: Call to Action -->
<section class="hero-gradient py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
            Pronto para economizar horas de trabalho?
        </h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
            Agende uma demonstração e veja como a Conciliação Bancária Automatizada pode transformar 
            o dia a dia do seu financeiro e contabilidade.
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
