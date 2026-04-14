<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seo['title'] ?? 'FiscalDock | Inteligência Fiscal para Contadores' }}</title>
    <meta name="description" content="{{ $seo['description'] ?? 'Importe seus arquivos SPED, monitore participantes e detecte riscos fiscais antes da auditoria. Plataforma completa para contadores e escritórios contábeis.' }}">
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">
    <meta name="robots" content="{{ $seo['robots'] ?? 'index,follow,max-image-preview:large' }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('binary_files/logo/Logo FiscalDock.png') }}">

    <!-- Open Graph (WhatsApp, Facebook) -->
    <meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
    <meta property="og:site_name" content="FiscalDock">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:url" content="{{ $seo['canonical'] ?? url()->current() }}">
    <meta property="og:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? 'FiscalDock | Inteligência Fiscal para Contadores' }}">
    <meta property="og:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? 'Importe seus arquivos SPED, monitore participantes e detecte riscos fiscais antes da auditoria. Plataforma completa para contadores e escritórios contábeis.' }}">
    <meta property="og:image" content="{{ $seo['og_image'] ?? asset('binary_files/logo/Logo FiscalDock.png') }}">

    <!-- Twitter Card (X) -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? 'FiscalDock | Inteligência Fiscal para Contadores' }}">
    <meta name="twitter:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? 'Importe seus arquivos SPED, monitore participantes e detecte riscos fiscais antes da auditoria. Plataforma completa para contadores e escritórios contábeis.' }}">
    <meta name="twitter:image" content="{{ $seo['og_image'] ?? asset('binary_files/logo/Logo FiscalDock.png') }}">

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
    <script src="{{ asset('js/inicio.js') }}?v={{ filemtime(public_path('js/inicio.js')) }}"></script>
    <script src="{{ asset('js/duvidas.js') }}?v={{ filemtime(public_path('js/duvidas.js')) }}"></script>
    <script src="{{ asset('js/solucoes.js') }}?v={{ filemtime(public_path('js/solucoes.js')) }}"></script>
    <script src="{{ asset('js/precos.js') }}?v={{ filemtime(public_path('js/precos.js')) }}"></script>
    <script src="{{ asset('js/login.js') }}?v={{ filemtime(public_path('js/login.js')) }}"></script>
    <script src="{{ asset('js/criar-conta.js') }}?v={{ filemtime(public_path('js/criar-conta.js')) }}"></script>
    <script src="{{ asset('js/agendar.js') }}?v={{ filemtime(public_path('js/agendar.js')) }}"></script>
    <script src="{{ asset('js/toast.js') }}?v={{ filemtime(public_path('js/toast.js')) }}"></script>
</head>
<body class="{{ $themeClass ?? 'bg-surface text-slate-900 font-sans antialiased' }}">
    @include('landing_page.partials.header')

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
    <footer class="bg-white border-t border-gray-200 py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-10">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('binary_files/logo/logo-fiscaldock_whitebg-removebg.png') }}" alt="FiscalDock" class="h-6 md:h-8 object-contain">
                        <span class="text-lg font-bold text-gray-900">FiscalDock</span>
                    </div>
                    <p class="text-sm text-gray-500 leading-relaxed max-w-xs">
                        O centro operacional que transforma SPED e documentos em ações, relatórios e previsibilidade para escritórios contábeis e empresas.
                    </p>
                </div>

                <!-- Quick Links -->
                <div class="md:flex md:justify-center">
                    <div>
                        <h3 class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400 mb-5">Links Rápidos</h3>
                        <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                            <a href="/inicio" class="text-sm" style="color: #1e4fa0">Home</a>
                            <a href="/duvidas" class="text-sm" style="color: #1e4fa0">Dúvidas</a>
                            <a href="/solucoes" class="text-sm" style="color: #1e4fa0">Soluções</a>
                            <a href="/blog" class="text-sm" style="color: #1e4fa0">Blog</a>
                            <a href="/precos" class="text-sm" style="color: #1e4fa0">Preços</a>
                            <a href="/agendar" class="text-sm" style="color: #1e4fa0">Contato</a>
                            <a href="/criar-conta" class="text-sm" style="color: #1e4fa0">Criar conta</a>
                            <a href="/login" class="text-sm" style="color: #1e4fa0">Login</a>
                        </div>
                    </div>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400 mb-5">Contato</h3>
                    <div class="flex items-center gap-3 mb-3 text-gray-600">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-sm">contato@fiscaldock.com.br</span>
                    </div>
                    <div class="flex items-center gap-3 mb-3 text-gray-600">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span class="text-sm">(67) 99984-4366</span>
                    </div>
                    <a href="{{ route('agendar') }}" class="inline-flex items-center justify-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Falar com especialista
                    </a>
                    <a href="https://instagram.com/fiscaldock" target="_blank" rel="noopener" class="flex items-center gap-3 text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                        </svg>
                        <span class="text-sm">@fiscaldock</span>
                    </a>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 pt-8 border-t border-gray-200">
                <p class="text-[11px] text-gray-400 uppercase tracking-wide">&copy; <span id="current-year"></span> FiscalDock. Todos os direitos reservados.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('termos') }}" class="text-[11px] text-gray-400 uppercase tracking-wide hover:text-gray-600 transition-colors">Termos de Uso</a>
                    <span class="text-gray-300">·</span>
                    <a href="{{ route('privacidade') }}" class="text-[11px] text-gray-400 uppercase tracking-wide hover:text-gray-600 transition-colors">Privacidade</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('structured-data')
</body>
</html>
