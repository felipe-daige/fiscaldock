<style>
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
    
    .feature-card {
        transition: all 0.3s ease;
    }
    
    .feature-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .health-card {
        opacity: 0;
        animation: fadeInUp 0.6s ease forwards;
    }
    
    .health-card:nth-child(1) { animation-delay: 0.1s; }
    .health-card:nth-child(2) { animation-delay: 0.2s; }
    .health-card:nth-child(3) { animation-delay: 0.3s; }
    .health-card:nth-child(4) { animation-delay: 0.4s; }
    
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
                Radar Fiscal: Monitoramento de CNDs em Tempo Real
            </h1>
            <p class="text-xl md:text-2xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Antecipe problemas. Nunca mais deixe uma certidão vencida travar os negócios do seu cliente.
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
                        Nossa solução de <strong class="text-gray-900">Monitoramento Automático de CNDs</strong> transforma 
                        a gestão de certidões fiscais de um processo reativo e manual em uma operação proativa e automatizada.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><strong>Monitoramento automático diário:</strong> Verificação contínua da validade das CNDs de todas as empresas do seu portfólio.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><strong>Consulta automática nas bases do governo:</strong> Sistema consulta automaticamente bases Federal, Estadual e Municipal sem intervenção manual.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><strong>Eliminação de trabalho manual:</strong> Sem necessidade de resolver captchas ou fazer consultas manuais. Tudo automatizado.</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="flex justify-center">
                <div class="relative">
                    <img src="{{ asset('icone-gif/alerta.gif') }}" alt="Monitoramento de CNDs" class="w-full max-w-md object-contain">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção: Sua Gestão de Risco -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Sua Gestão de <span class="bg-linear-to-r from-blue-500 to-blue-600 bg-clip-text text-transparent">Risco</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Transforme a gestão de certidões fiscais de reativa para proativa
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Card 1: Monitoramento 24/7 -->
            <div class="feature-card bg-white rounded-lg shadow-sm p-8 border border-gray-200">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Monitoramento 24/7</h3>
                </div>
                <p class="text-gray-600 text-center leading-relaxed">
                    Verificação contínua da validade das CNDs. Receba alertas imediatos sobre qualquer mudança 
                    no status das certidões, mantendo você sempre um passo à frente.
                </p>
            </div>

            <!-- Card 2: Alertas Preventivos -->
            <div class="feature-card bg-white rounded-lg shadow-sm p-8 border border-gray-200">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-lg flex items-center justify-center mx-auto mb-4 shake-notification">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Alertas Preventivos</h3>
                </div>
                <p class="text-gray-600 text-center leading-relaxed">
                    Notificações automáticas antes do vencimento das certidões. Renove com um clique e evite 
                    bloqueios que possam impactar os negócios do seu cliente.
                </p>
            </div>

            <!-- Card 3: Painel de Saúde -->
            <div class="feature-card bg-white rounded-lg shadow-sm p-8 border border-gray-200">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Painel de Saúde</h3>
                </div>
                <p class="text-gray-600 text-center leading-relaxed">
                    Visualização única de empresas saudáveis vs. empresas que exigem atenção. Tenha uma visão 
                    completa do status fiscal de todo o seu portfólio em um só lugar.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Seção: Destaque Visual - Painel de Saúde -->
<section class="py-20 bg-white">
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

<!-- Call to Action -->
<section class="py-20 bg-gradient-to-br from-blue-600 to-indigo-700">
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
