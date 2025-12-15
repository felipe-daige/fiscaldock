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
                Inteligência Tributária: Apuração de Impostos no Piloto Automático
            </h1>
            <p class="text-lg sm:text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-6 md:mb-8 px-4">
                Do Simples Nacional ao Lucro Presumido: troque planilhas complexas por precisão absoluta
            </p>
        </div>
    </div>
</section>

<!-- Seção 2: Descrição Principal -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Automatize o cálculo e a geração de guias de impostos com precisão absoluta e zero erro humano
            </h2>
            <p class="text-lg md:text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                O Rubi analisa o faturamento e as notas de entrada/saída, aplica as regras do regime tributário (incluindo anexos do Simples e retenções) e calcula o imposto devido em segundos. Elimine planilhas complexas e reduza o tempo de apuração de horas para minutos.
            </p>
        </div>
    </div>
</section>

<!-- Seção 3: Três Pilares -->
<section class="py-16 bg-gray-50 section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Os Três Pilares da Inteligência Tributária</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Tecnologia que transforma horas de trabalho manual em minutos de automação inteligente
            </p>
        </div>

        <div class="space-y-6">
            <!-- Pilar 1: Cálculo à Prova de Falhas -->
            <div class="feature-card flex flex-col md:flex-row items-start gap-6 p-6 bg-white rounded-lg border border-blue-100 shadow-sm">
                <div class="flex-shrink-0 w-16 h-16 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Cálculo à Prova de Falhas</h3>
                    <p class="text-gray-700 leading-relaxed text-lg">
                        Eliminação completa do erro humano. O sistema aplica automaticamente todas as regras tributárias, considerando anexos do Simples Nacional, retenções, alíquotas progressivas e todas as nuances da legislação vigente. Cada cálculo é auditável e rastreável.
                    </p>
                </div>
            </div>
            
            <!-- Pilar 2: Geração Instantânea -->
            <div class="feature-card flex flex-col md:flex-row items-start gap-6 p-6 bg-white rounded-lg border border-yellow-100 shadow-sm">
                <div class="flex-shrink-0 w-16 h-16 bg-yellow-500 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Geração Instantânea</h3>
                    <p class="text-gray-700 leading-relaxed text-lg">
                        Emissão de guias de impostos com um único clique. Todas as guias necessárias são geradas automaticamente no formato correto, prontas para pagamento. Economize horas de trabalho manual e reduza drasticamente o tempo de processamento.
                    </p>
                </div>
            </div>
            
            <!-- Pilar 3: Entrega Direta -->
            <div class="feature-card flex flex-col md:flex-row items-start gap-6 p-6 bg-white rounded-lg border border-green-100 shadow-sm">
                <div class="flex-shrink-0 w-16 h-16 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Entrega Direta</h3>
                    <p class="text-gray-700 leading-relaxed text-lg">
                        Envio automático das guias geradas diretamente para o cliente através do portal. O cliente recebe tudo organizado, com explicações claras e pode acompanhar o histórico completo. Menos retrabalho e mais transparência.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção 4: Comparativo Visual de Tempo -->
<section class="py-16 bg-white section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gray-50 rounded-lg p-8 md:p-12 border border-gray-200 shadow-sm">
            <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8 text-center">Redução de Tempo: De Horas para Minutos</h3>
            <div class="space-y-6 max-w-3xl mx-auto">
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-lg font-semibold text-gray-700">Processo Manual</span>
                        <span class="text-xl font-bold text-red-600">4 horas</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-6">
                        <div class="bg-red-500 h-6 rounded-full flex items-center justify-end pr-2" style="width: 100%">
                            <span class="text-white text-sm font-semibold">100%</span>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-lg font-semibold text-gray-700">Com Rubi</span>
                        <span class="text-xl font-bold text-green-600">2 minutos</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-6">
                        <div class="bg-green-500 h-6 rounded-full flex items-center justify-end pr-2" style="width: 0.83%">
                            <span class="text-white text-sm font-semibold">0.83%</span>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-center text-xl text-gray-700 mt-8">
                <span class="font-bold text-green-600 text-2xl">99,2% de redução</span> no tempo de processamento
            </p>
        </div>
    </div>
</section>

<!-- Seção 5: Selo de Atualização Legislativa -->
<section class="py-16 bg-gray-50 section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg p-8 md:p-12 text-center shadow-lg">
            <div class="flex items-center justify-center gap-4 mb-4">
                <svg class="w-12 h-12 md:w-16 md:h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <p class="text-3xl md:text-4xl font-bold text-white">
                    100% Atualizado com a Legislação
                </p>
            </div>
            <p class="text-green-100 mt-4 text-lg md:text-xl max-w-3xl mx-auto">
                Nossas regras são atualizadas automaticamente conforme mudanças na legislação tributária
            </p>
        </div>
    </div>
</section>

<!-- Seção 6: Funcionalidades Técnicas -->
<section class="py-16 bg-white section-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Como Funciona</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Um processo automatizado que transforma horas de trabalho manual em minutos de precisão
            </p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-8 md:p-12 max-w-4xl mx-auto">
            <ul class="space-y-4 text-gray-700">
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Análise automática do faturamento e notas fiscais de entrada e saída</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Aplicação automática das regras do regime tributário (Simples Nacional, Lucro Presumido, etc.)</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Cálculo preciso considerando anexos do Simples, retenções e alíquotas progressivas</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Geração automática de todas as guias de impostos no formato correto</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Envio automático das guias para o cliente através do portal</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Auditoria completa e rastreabilidade de todos os cálculos realizados</span>
                </li>
                <li class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-lg">Atualização automática conforme mudanças na legislação tributária</span>
                </li>
            </ul>
        </div>
    </div>
</section>

<!-- Seção 7: Call to Action -->
<section class="hero-gradient py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
            Pronto para automatizar a apuração de impostos?
        </h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
            Agende uma demonstração e veja como a Inteligência Tributária pode transformar 
            o processo de apuração de impostos do seu escritório.
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
