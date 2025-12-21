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
    <style>
        @media (min-width: 768px) {
            body.sidebar-collapsed #app-sidebar { width: 5rem; }
            body.sidebar-collapsed .layout-shell { padding-left: 5rem; }
            body.sidebar-expanded #app-sidebar { width: 14rem; }
            body.sidebar-expanded .layout-shell { padding-left: 14rem; }
        }
    </style>
</head>
<body class="{{ $themeClass ?? 'bg-surface text-slate-900 font-sans antialiased' }}">
    <div class="min-h-screen flex">
        @include('autenticado.partials.sidebar')

        <div class="layout-shell flex-1 min-w-0 flex flex-col md:pl-56">
            <!-- Mobile Topbar (abre a sidebar como drawer) -->
            <div class="md:hidden sticky top-0 z-30 bg-white border-b border-gray-200">
                <div class="flex items-center gap-3 px-4 py-3">
                    <button id="sidebar-open-btn" type="button" class="p-2 text-gray-600 hover:text-blue-500 transition-colors" aria-label="Abrir menu">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <a href="/dashboard" class="flex items-center gap-2" data-link data-no-active>
                        <img src="{{ asset('binary_files/logo/logo-fiscaldock_whitebg-removebg.png') }}" alt="FiscalDock" class="h-7 object-contain">
                        <span class="text-base font-bold text-brand">FiscalDock</span>
                    </a>
                </div>
            </div>

            <!-- Toast Container -->
            <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

            <!-- Main Content Area -->
            <main id="app" class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @if(isset($initialView))
                    @include($initialView)
                @endif
            </main>

        </div>
    </div>
</body>
</html>


