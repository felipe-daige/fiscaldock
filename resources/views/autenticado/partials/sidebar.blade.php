<div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/50 z-40 md:hidden"></div>

<aside
    id="app-sidebar"
    class="fixed inset-y-0 left-0 z-50 w-56 bg-white border-r border-gray-200 transform -translate-x-full transition-all duration-200 ease-out
           flex flex-col
           md:static md:translate-x-0 md:z-auto md:shrink-0"
    aria-label="Menu lateral"
>
    <!-- Top / Brand -->
    <div class="flex items-center justify-between gap-2 px-4 py-4 border-b border-gray-200">
        <a href="/dashboard" class="flex items-center gap-3 min-w-0" data-link data-no-active>
            <img src="{{ asset('binary_files/logo/logo-fiscaldock_whitebg-removebg.png') }}" alt="FiscalDock" class="h-8 object-contain shrink-0">
            <span class="sidebar-label truncate text-lg font-bold text-brand">FiscalDock</span>
        </a>

        <div class="flex items-center gap-2">
            <!-- Close (mobile) -->
            <button
                id="sidebar-close-btn"
                type="button"
                class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 text-gray-600 hover:text-blue-500 hover:bg-gray-50 transition-colors"
                aria-label="Fechar menu"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 min-h-0 px-3 py-4 overflow-y-auto space-y-2">
        <a href="/dashboard" data-link class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"></path>
            </svg>
            <span class="sidebar-label font-medium">Dashboard</span>
        </a>

        <div class="mt-4">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">Soluções</div>

            <div class="sidebar-collapsible group expanded">
                <div class="sidebar-summary flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-gray-700 leading-5 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span class="sidebar-label font-medium truncate">Soluções</span>
                    </div>
                    <svg class="sidebar-arrow sidebar-label w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <div class="sidebar-submenu-wrapper mt-1 ml-3 pl-3 border-l border-gray-200 space-y-1">
                    <a href="/app/solucoes" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Visão geral</span>
                    </a>
                    <a href="/app/solucoes/importacao-xml" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Importação de XMLs</span>
                    </a>
                    <a href="/app/solucoes/conciliacao-bancaria" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Conciliação Bancária</span>
                    </a>
                    <a href="/app/solucoes/gestao-cnds" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Gestão de CNDs</span>
                    </a>
                    <a href="/app/solucoes/inteligencia-tributaria" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Inteligência Tributária</span>
                    </a>
                    <a href="/app/solucoes/raf" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">RAF</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- User / Logout -->
    <div class="flex-shrink-0 px-4 py-4 border-t border-gray-200">
        <div class="flex items-center gap-3 text-sm text-gray-600 mb-3 leading-5">
            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="sidebar-label font-medium truncate">{{ Auth::user()->name ?? 'Usuário' }}</span>
        </div>

        <form action="{{ route('logout') }}" method="POST" id="logout-form-header">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-red-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span class="sidebar-label">Sair</span>
            </button>
        </form>
    </div>
</aside>

