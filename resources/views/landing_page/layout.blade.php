<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FiscalDock | Transforme SPED em Ações e Relatórios</title>
    <meta name="description" content="O FiscalDock que transforma SPED e documentos em ações, relatórios e previsibilidade. Centralize arquivos fiscais, identifique pendências e automatize cobranças.">

    <!-- Fallback crítico do CTA (caso o CSS do Vite não carregue) -->
    <style>
        .btn-cta{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;border-radius:.5rem;padding:.75rem 1.25rem;min-height:48px;font-size:1rem;border:2px solid #facc15;background:#facc15;color:#0b1f3a;font-weight:600;line-height:1.1;text-decoration:none;transform:translateY(0);box-shadow:0 20px 50px -20px rgba(250,204,21,.55);transition:transform .16s ease,box-shadow .16s ease,background-color .16s ease,border-color .16s ease;-webkit-tap-highlight-color:transparent}
        .btn-cta:hover{background:#eab308;border-color:#eab308;transform:translateY(-1px);box-shadow:0 26px 60px -22px rgba(250,204,21,.65)}
        .btn-cta:active{background:#ca8a04;border-color:#ca8a04;transform:translateY(0);box-shadow:0 14px 35px -22px rgba(250,204,21,.55)}
        .btn-cta:focus-visible{outline:none;box-shadow:0 0 0 4px rgba(250,204,21,.35),0 20px 50px -20px rgba(250,204,21,.55)}
        .btn-cta--nav{min-height:40px;padding:.5rem 1.1rem;font-size:.875rem;line-height:1.2;font-weight:600}
        .btn-cta--block{width:100%;justify-content:center}
        @media (prefers-reduced-motion:reduce){.btn-cta{transition:none}}
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/spa.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- JavaScript Files -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="{{ asset('js/layout.js') }}"></script>
    <script src="{{ asset('js/faq.js') }}"></script>
    <script src="{{ asset('js/impactos.js') }}"></script>
    <script src="{{ asset('js/solucoes.js') }}"></script>
    <script src="{{ asset('js/beneficios.js') }}"></script>
    <script src="{{ asset('js/precos.js') }}"></script>
    <script src="{{ asset('js/login.js') }}"></script>
    <script src="{{ asset('js/agendar.js') }}"></script>
    <script src="{{ asset('js/toast.js') }}"></script>
</head>
<body class="{{ $themeClass ?? 'bg-surface text-slate-900 font-sans antialiased' }}">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex justify-between items-center py-6">
                <a href="/inicio" class="flex items-center gap-3" data-link data-no-active>
                    <img src="{{ asset('binary_files/logo/logo-fiscaldock_whitebg-removebg.png') }}" alt="FiscalDock" class="h-8 md:h-10 object-contain">
                    <span class="text-xl font-bold text-brand">FiscalDock</span>
                </a>

                <!-- Desktop Navigation -->
                <ul class="hidden md:flex items-center gap-8">
                    <li class="relative group nav-dropdown-buffer">
                        <a href="/solucoes" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium flex items-center gap-1">
                            Soluções
                            <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <div class="nav-dropdown-panel pointer-events-none absolute top-full left-0 mt-0 pt-3 w-48 rounded-lg border border-gray-200 bg-white shadow-lg opacity-0 invisible group-hover:translate-y-0 group-hover:visible group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200 ease-out transform translate-y-2">
                            <a href="/solucoes/importacao-xml" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium rounded-t-lg">Importação de XMLs</a>
                            <a href="/solucoes/conciliacao-bancaria" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium">Conciliação Bancária</a>
                            <a href="/solucoes/gestao-cnds" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium">Gestão de CNDs</a>
                            <a href="/solucoes/raf" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium">RAF</a>
                            <a href="/solucoes/inteligencia-tributaria" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium">Inteligência Tributária</a>
                            <a href="/solucoes" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium rounded-b-lg">Ver Todas as Soluções</a>
                        </div>
                    </li>
                    <li class="relative group nav-dropdown-buffer">
                        <a href="/sobre" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium flex items-center gap-1">
                            Conhecer
                            <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <div class="nav-dropdown-panel pointer-events-none absolute top-full left-0 mt-0 pt-3 w-48 rounded-lg border border-gray-200 bg-white shadow-lg opacity-0 invisible group-hover:translate-y-0 group-hover:visible group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200 ease-out transform translate-y-2">
                            <a href="/sobre" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium rounded-t-lg">Sobre</a>
                            <a href="/beneficios" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium">Benefícios</a>
                            <a href="/impactos" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium">Impactos</a>
                            <a href="/faq" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium rounded-b-lg">Perguntas</a>
                        </div>
                    </li>
                    <li><a href="/precos" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium">Preços</a></li>
                    <li><a href="/login" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium">Login</a></li>
                    <li>
                        <a href="/agendar" data-link data-button="cta" class="btn-cta btn-cta--nav">
                            Testar Agora
                        </a>
                    </li>
                </ul>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 text-gray-600 hover:text-blue-500 transition-colors">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </nav>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden flex-col gap-4 py-4 border-t border-gray-200">
                <a href="/solucoes" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">Soluções</a>
                <a href="/sobre" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">Conhecer</a>
                <a href="/beneficios" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">Benefícios</a>
                <a href="/impactos" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">Impactos</a>
                <a href="/precos" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">Preços</a>
                <a href="/faq" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">Perguntas</a>
                <a href="/login" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">Login</a>
                <a href="/agendar" data-link data-button="cta" class="btn-cta btn-cta--block w-full">Testar Agora</a>
            </div>
        </div>
    </header>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Main Content Area -->
    <main id="app">
        @if(isset($initialView))
            @include("landing_page.$initialView")
        @else
            @include('landing_page.inicio')
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-200 py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('binary_files/logo/logo-fiscaldock_whitebg-removebg.png') }}" alt="FiscalDock" class="h-6 md:h-8 object-contain">
                        <h3 class="text-lg font-semibold text-brand">FiscalDock</h3>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        O centro operacional que transforma SPED e documentos em ações, relatórios e previsibilidade para escritórios contábeis e empresas.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Links Rápidos</h3>
                    <ul class="space-y-3">
                        <li><a href="/inicio" data-link class="text-gray-600 hover:text-blue-500 transition-colors">Início</a></li>
                        <li><a href="/sobre" data-link class="text-gray-600 hover:text-blue-500 transition-colors">Conhecer</a></li>
                        <li><a href="/beneficios" data-link class="text-gray-600 hover:text-blue-500 transition-colors">Benefícios</a></li>
                        <li><a href="/impactos" data-link class="text-gray-600 hover:text-blue-500 transition-colors">Impactos</a></li>
                        <li><a href="/precos" data-link class="text-gray-600 hover:text-blue-500 transition-colors">Preços</a></li>
                        <li><a href="/faq" data-link class="text-gray-600 hover:text-blue-500 transition-colors">Perguntas</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contato</h3>
                    <div class="flex items-center gap-3 mb-3 text-gray-600">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>contato@reformatributaria.com</span>
                    </div>
                    <div class="flex items-center gap-3 text-gray-600">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span>(11) 99999-9999</span>
                    </div>
                </div>
            </div>

            <div class="text-center pt-8 border-t border-gray-200 text-gray-600">
                <p>&copy; <span id="current-year"></span> FiscalDock. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>
