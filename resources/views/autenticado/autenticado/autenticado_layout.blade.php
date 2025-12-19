<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reforma Tributária 2026 | Soluções Inteligentes</title>
    <meta name="description" content="Prepare sua empresa para a Reforma Tributária de 2026 com soluções inteligentes. Otimize créditos, automatize processos e garanta conformidade fiscal.">
    
    @vite(['resources/css/app.css', 'resources/js/spa.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="{{ asset('js/layout.js') }}"></script>
    <script src="{{ asset('js/toast.js') }}"></script>
</head>
<body class="{{ $themeClass ?? 'bg-surface text-slate-900 font-sans antialiased' }}">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex justify-between items-center py-6">
                <a href="/dashboard" class="flex items-center gap-3" data-link>
                    <img src="{{ asset('binary_files/logo/logo-fiscaldock_whitebg-removebg.png') }}" alt="FiscalDock" class="h-8 md:h-10 object-contain">
                    <span class="text-xl font-bold text-brand-static">FiscalDock</span>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-6">
                    <a href="/dashboard" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium">Dashboard</a>
                    <li class="relative group nav-dropdown-buffer">
                        <a href="/app/solucoes" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium flex items-center gap-1">
                            Soluções
                            <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <div class="nav-dropdown-panel pointer-events-none absolute top-full left-0 mt-0 pt-3 w-48 rounded-lg border border-gray-200 bg-white shadow-lg opacity-0 invisible group-hover:translate-y-0 group-hover:visible group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200 ease-out transform translate-y-2">
                            <a href="/app/solucoes/importacao-xml" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium rounded-t-lg">Importação de XMLs</a>
                            <a href="/app/solucoes/conciliacao-bancaria" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium">Conciliação Bancária</a>
                            <a href="/app/solucoes/gestao-cnds" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium">Gestão de CNDs</a>
                            <a href="/app/solucoes/raf" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium">RAF</a>
                            <a href="/app/solucoes/inteligencia-tributaria" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium">Inteligência Tributária</a>
                            <a href="/app/solucoes" data-link class="block px-4 py-3 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors font-medium rounded-b-lg">Ver Todas as Soluções</a>
                        </div>
                    </li>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="font-medium">{{ Auth::user()->name ?? 'Usuário' }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form-header">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-red-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Sair
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 text-gray-600 hover:text-blue-500 transition-colors">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </nav>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden flex-col gap-4 py-4 border-t border-gray-200">
                <a href="/dashboard" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">Dashboard</a>
                <a href="/app/solucoes" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">Soluções</a>
                <div class="pl-4 flex flex-col gap-2 border-l-2 border-gray-200">
                    <a href="/app/solucoes/importacao-xml" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-1 text-sm">Importação de XMLs</a>
                    <a href="/app/solucoes/conciliacao-bancaria" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-1 text-sm">Conciliação Bancária</a>
                    <a href="/app/solucoes/gestao-cnds" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-1 text-sm">Gestão de CNDs</a>
                    <a href="/app/solucoes/raf" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-1 text-sm">RAF</a>
                    <a href="/app/solucoes/inteligencia-tributaria" data-link class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-1 text-sm">Inteligência Tributária</a>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600 py-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="font-medium">{{ Auth::user()->name ?? 'Usuário' }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST" id="logout-form-mobile">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-red-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Main Content Area -->
    <main id="app">
        @if(isset($initialView))
            @include($initialView)
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
                        <h3 class="text-lg font-semibold text-brand-static">FiscalDock</h3>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        O centro operacional que transforma SPED e documentos em ações, relatórios e previsibilidade para escritórios contábeis e empresas.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Links Rápidos</h3>
                    <ul class="space-y-3">
                        <li><a href="/dashboard" data-link class="text-gray-600 hover:text-blue-500 transition-colors">Dashboard</a></li>
                        <li><a href="/inicio" data-link class="text-gray-600 hover:text-blue-500 transition-colors">Início</a></li>
                        <li><a href="/sobre" data-link class="text-gray-600 hover:text-blue-500 transition-colors">Sobre</a></li>
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
