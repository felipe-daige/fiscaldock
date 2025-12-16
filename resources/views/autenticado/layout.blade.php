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
    <script src="{{ asset('js/toast.js') }}"></script>
</head>
<body class="{{ $themeClass ?? 'theme-default' }}">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
    </header>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Main Content Area -->
    <main id="app">
        @if(isset($initialView))
            @include("autenticado.$initialView")
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-200 py-12 mt-16">
    </footer>
</body>
</html>
