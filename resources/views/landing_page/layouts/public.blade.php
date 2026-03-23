<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seo['title'] ?? 'FiscalDock | Inteligência Fiscal para Contadores' }}</title>
    <meta name="description" content="{{ $seo['description'] ?? 'Importe seus arquivos SPED, monitore participantes e detecte riscos fiscais antes da auditoria. Plataforma completa para contadores e escritórios contábeis.' }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('binary_files/logo/Logo FiscalDock.png') }}">

    <!-- Open Graph (WhatsApp, Facebook) -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? 'FiscalDock | Inteligência Fiscal para Contadores' }}">
    <meta property="og:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? 'Importe seus arquivos SPED, monitore participantes e detecte riscos fiscais antes da auditoria. Plataforma completa para contadores e escritórios contábeis.' }}">
    <meta property="og:image" content="{{ asset('binary_files/logo/Logo FiscalDock.png') }}">

    <!-- Twitter Card (X) -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? 'FiscalDock | Inteligência Fiscal para Contadores' }}">
    <meta name="twitter:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? 'Importe seus arquivos SPED, monitore participantes e detecte riscos fiscais antes da auditoria. Plataforma completa para contadores e escritórios contábeis.' }}">
    <meta name="twitter:image" content="{{ asset('binary_files/logo/Logo FiscalDock.png') }}">

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
    <script src="{{ asset('js/layout.js') }}?v={{ filemtime(public_path('js/layout.js')) }}"></script>
    <script src="{{ asset('js/faq.js') }}?v={{ filemtime(public_path('js/faq.js')) }}"></script>
    <script src="{{ asset('js/solucoes.js') }}?v={{ filemtime(public_path('js/solucoes.js')) }}"></script>
    <script src="{{ asset('js/precos.js') }}?v={{ filemtime(public_path('js/precos.js')) }}"></script>
    <script src="{{ asset('js/login.js') }}?v={{ filemtime(public_path('js/login.js')) }}"></script>
    <script src="{{ asset('js/agendar.js') }}?v={{ filemtime(public_path('js/agendar.js')) }}"></script>
    <script src="{{ asset('js/toast.js') }}?v={{ filemtime(public_path('js/toast.js')) }}"></script>
    @stack('structured-data')
</head>
<body class="{{ $themeClass ?? 'bg-surface text-slate-900 font-sans antialiased' }}">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex justify-between items-center py-6">
                <a href="/inicio" class="flex items-center gap-3">
                    <img src="{{ asset('binary_files/logo/logo-fiscaldock_whitebg-removebg.png') }}" alt="FiscalDock" class="h-8 md:h-10 object-contain">
                    <span class="text-xl font-bold text-brand">FiscalDock</span>
                </a>

                <!-- Desktop Navigation -->
                <ul class="hidden md:flex items-center gap-8">
                    <li class="flex items-center"><a href="/solucoes" class="text-gray-600 hover:text-blue-500 transition-colors font-medium inline-flex items-center" style="min-height: 40px; line-height: 1.2;">Soluções</a></li>
                    <li class="flex items-center"><a href="/precos" class="text-gray-600 hover:text-blue-500 transition-colors font-medium inline-flex items-center" style="min-height: 40px; line-height: 1.2;">Preços</a></li>
                    <li class="flex items-center"><a href="/faq" class="text-gray-600 hover:text-blue-500 transition-colors font-medium inline-flex items-center" style="min-height: 40px; line-height: 1.2;">FAQ</a></li>
                    <li class="flex items-center"><a href="/blog" class="text-gray-600 hover:text-blue-500 transition-colors font-medium inline-flex items-center" style="min-height: 40px; line-height: 1.2;">Blog</a></li>
                    <li class="flex items-center"><a href="/login" class="text-gray-600 hover:text-blue-500 transition-colors font-medium inline-flex items-center" style="min-height: 40px; line-height: 1.2;">Login</a></li>
                    <li class="flex items-center">
                        <a href="/agendar" class="btn-cta btn-cta--nav inline-flex items-center" style="min-height: 40px;">
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
                <a href="/solucoes" class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">Soluções</a>
                <a href="/precos" class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">Preços</a>
                <a href="/faq" class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">FAQ</a>
                <a href="/blog" class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">Blog</a>
                <a href="/login" class="text-gray-600 hover:text-blue-500 transition-colors font-medium py-2">Login</a>
                <a href="/agendar" class="btn-cta btn-cta--block w-full">Testar Agora</a>
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
            @include('landing_page.paginas.inicio')
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
                        <li><a href="/inicio" class="text-gray-600 hover:text-blue-500 transition-colors">Home</a></li>
                        <li><a href="/solucoes" class="text-gray-600 hover:text-blue-500 transition-colors">Soluções</a></li>
                        <li><a href="/precos" class="text-gray-600 hover:text-blue-500 transition-colors">Preços</a></li>
                        <li><a href="/faq" class="text-gray-600 hover:text-blue-500 transition-colors">FAQ</a></li>
                        <li><a href="/blog" class="text-gray-600 hover:text-blue-500 transition-colors">Blog</a></li>
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
