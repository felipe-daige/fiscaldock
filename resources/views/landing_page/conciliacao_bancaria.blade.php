<style>
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
    
    .feature-card {
        transition: all 0.3s ease;
    }
    
    .feature-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .process-step {
        opacity: 0;
        animation: fadeInUp 0.6s ease forwards;
    }
    
    .process-step:nth-child(1) { animation-delay: 0.1s; }
    .process-step:nth-child(2) { animation-delay: 0.3s; }
    .process-step:nth-child(3) { animation-delay: 0.5s; }
    
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
</style>

<!-- Hero Section -->
<section class="bg-gradient-to-br from-blue-50 to-indigo-100 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                Conciliação Bancária que te Devolve o Tempo
            </h1>
            <p class="text-xl md:text-2xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Diga adeus ao 'bater' extrato. Seu financeiro e sua contabilidade em perfeita sintonia.
            </p>
        </div>
    </div>
</section>

<!-- Seção: O que fazemos -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">
                    O que fazemos
                </h2>
                <div class="space-y-4 text-gray-700 leading-relaxed">
                    <p class="text-lg">
                        Nossa solução de <strong class="text-gray-900">Conciliação Bancária Automatizada</strong> transforma 
                        um processo manual e demorado em uma operação rápida e precisa.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><strong>Leitura automática de extratos OFX:</strong> Importe extratos bancários em segundos, sem digitação manual.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><strong>Cruzamento inteligente:</strong> Sistema cruza automaticamente com Notas Fiscais e impostos já registrados.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><strong>Identificação automática:</strong> Reconhece pagamentos e recebimentos sem intervenção manual.</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="flex justify-center">
                <div class="relative">
                    <img src="{{ asset('icone-gif/process.gif') }}" alt="Processo de Conciliação" class="w-full max-w-md object-contain">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção: O Mágico do Match -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                O Mágico do <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">Match</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Tecnologia de ponta que garante precisão e economia de tempo na conciliação bancária
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Card 1: Match Inteligente -->
            <div class="feature-card bg-white rounded-lg shadow-sm p-8 border border-gray-200">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Match Inteligente</h3>
                </div>
                <p class="text-gray-600 text-center leading-relaxed">
                    Machine learning avançado para reconhecer despesas mesmo sem nota fiscal associada. 
                    O sistema aprende com seus padrões e melhora a cada conciliação.
                </p>
            </div>

            <!-- Card 2: Integridade Garantida -->
            <div class="feature-card bg-white rounded-lg shadow-sm p-8 border border-gray-200">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Integridade Garantida</h3>
                </div>
                <p class="text-gray-600 text-center leading-relaxed">
                    Encontre divergências em segundos. O sistema identifica automaticamente 
                    inconsistências entre extratos e registros contábeis, garantindo precisão total.
                </p>
            </div>

            <!-- Card 3: Foco no Excepcional -->
            <div class="feature-card bg-white rounded-lg shadow-sm p-8 border border-gray-200">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Foco no Excepcional</h3>
                </div>
                <p class="text-gray-600 text-center leading-relaxed">
                    Revise apenas os 5% que não deram match automático. Economize tempo focando 
                    apenas no que realmente precisa da sua atenção.
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

<!-- Seção: Destaque Visual do Processo -->
<section class="py-20 bg-white">
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

<!-- Call to Action -->
<section class="py-20 bg-gradient-to-br from-blue-600 to-indigo-700">
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
