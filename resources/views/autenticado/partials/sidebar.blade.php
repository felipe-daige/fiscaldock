<div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/50 z-40 md:hidden"></div>

<aside
    id="app-sidebar"
    class="fixed inset-y-0 left-0 z-50 w-56 bg-white border-r border-gray-200 transform -translate-x-full transition-all duration-200 ease-out
           flex flex-col h-screen max-h-screen
           md:relative md:inset-y-auto md:left-auto md:transform-none md:translate-x-0 md:sticky md:top-0 md:z-auto md:shrink-0 md:h-screen md:max-h-screen"
    aria-label="Menu lateral"
>
    <!-- Top / Brand -->
    <div class="flex-shrink-0 flex items-center justify-between gap-2 px-4 py-4 border-b border-gray-200">
        <a href="/dashboard" class="flex items-center gap-3 min-w-0" data-link data-no-active>
            <img src="{{ asset('binary_files/logo/logo-fiscaldock_whitebg-removebg.png') }}" alt="FiscalDock" class="h-8 object-contain shrink-0">
            <span class="sidebar-label truncate text-lg font-bold text-brand">FiscalDock</span>
        </a>

        <div class="flex items-center gap-2">
            <!-- Close (mobile) -->
            <button
                id="sidebar-close-btn"
                type="button"
                class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 text-gray-600 hover:text-blue-500 hover:bg-gray-500/8 transition-colors"
                aria-label="Fechar menu"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 min-h-0 px-3 py-4 overflow-y-auto overflow-x-hidden space-y-2">
        <!-- PRINCIPAL -->
        <div>
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">PRINCIPAL</div>

            <!-- Dashboard -->
            <a href="/dashboard" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                </svg>
                <span class="sidebar-label font-medium">Dashboard</span>
            </a>

            <!-- Minha Empresa -->
            <a href="/app/minha-empresa" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span class="sidebar-label font-medium">Minha Empresa</span>
            </a>

            <!-- Alertas -->
            <a href="/app/alertas" data-link class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors w-full leading-5">
                <div class="flex items-center gap-3 min-w-0">
                    <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="sidebar-label font-medium">Alertas</span>
                </div>
                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold rounded-full bg-red-100 text-red-600 sidebar-label">5</span>
            </a>
        </div>

        <!-- IMPORTACAO -->
        <div class="mt-4">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">IMPORTACAO</div>

            <!-- Importar XMLs -->
            <a href="/app/monitoramento/xml" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="sidebar-label font-medium">XMLs (NF-e/CT-e)</span>
            </a>

            <!-- Importar SPED -->
            <a href="/app/monitoramento/sped" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                </svg>
                <span class="sidebar-label font-medium">SPED</span>
            </a>

            <!-- CNPJ Avulso -->
            <a href="/app/monitoramento/avulso" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span class="sidebar-label font-medium">CNPJ Avulso</span>
            </a>
        </div>

        <!-- CADASTROS -->
        <div class="mt-4">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">CADASTROS</div>

            <!-- Clientes -->
            <div class="sidebar-collapsible group collapsed">
                <div class="sidebar-summary flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-gray-700 leading-5 cursor-pointer hover:bg-gray-500/8 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="sidebar-label font-medium truncate">Clientes</span>
                    </div>
                    <svg class="sidebar-arrow sidebar-label w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <div class="sidebar-submenu-wrapper mt-1 ml-3 pl-3 border-l border-gray-200 space-y-1">
                    <a href="/app/clientes" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Todos os Clientes</span>
                    </a>
                    <a href="/app/novo_cliente" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Novo Cliente</span>
                    </a>
                </div>
            </div>

            <!-- Participantes -->
            <div class="sidebar-collapsible group collapsed">
                <div class="sidebar-summary flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-gray-700 leading-5 cursor-pointer hover:bg-gray-500/8 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span class="sidebar-label font-medium truncate">Participantes</span>
                    </div>
                    <svg class="sidebar-arrow sidebar-label w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <div class="sidebar-submenu-wrapper mt-1 ml-3 pl-3 border-l border-gray-200 space-y-1">
                    <a href="/app/monitoramento/participantes" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Todos os Participantes</span>
                    </a>
                    <a href="/app/monitoramento/avulso" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Novo Participante Avulso</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- CONSULTAS (nova secao unificada RAF + Monitoramento) -->
        <div class="mt-4">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">CONSULTAS</div>

            <!-- Nova Consulta -->
            <a href="/app/consultas/nova" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span class="sidebar-label font-medium">Nova Consulta</span>
            </a>

            <!-- Planos Disponiveis -->
            <a href="/app/consultas/planos" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span class="sidebar-label font-medium">Planos Disponiveis</span>
            </a>

            <!-- Historico -->
            <a href="/app/consultas/historico" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="sidebar-label font-medium">Historico</span>
            </a>

            <!-- Relatorios -->
            <a href="/app/consultas/relatorios" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="sidebar-label font-medium">Relatorios</span>
            </a>
        </div>

        <!-- COMPLIANCE -->
        <div class="mt-4">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">COMPLIANCE</div>

            <!-- Score de Risco -->
            <a href="/app/risk" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <span class="sidebar-label font-medium">Score de Risco</span>
            </a>

            <!-- Validacao Contabil -->
            <div class="sidebar-collapsible group collapsed">
                <div class="sidebar-summary flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-gray-700 leading-5 cursor-pointer hover:bg-gray-500/8 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <span class="sidebar-label font-medium truncate">Validacao Contabil</span>
                    </div>
                    <svg class="sidebar-arrow sidebar-label w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <div class="sidebar-submenu-wrapper mt-1 ml-3 pl-3 border-l border-gray-200 space-y-1">
                    <a href="/app/validacao" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Dashboard</span>
                    </a>
                    <a href="/app/validacao/alertas" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Alertas</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- BI FISCAL -->
        <div class="mt-4">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">BI FISCAL</div>

            <!-- Dashboard BI -->
            <a href="/app/analytics" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-500/8 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="sidebar-label font-medium">Dashboard BI</span>
            </a>
        </div>
    </nav>

    <!-- User / Logout -->
    <div class="flex-shrink-0 px-4 py-4 border-t border-gray-200">
        <a href="/app/perfil" data-link class="flex items-center gap-3 text-sm text-gray-600 mb-3 leading-5 px-2 py-2 -mx-2 rounded-lg hover:bg-gray-500/8 transition-colors group">
            <svg class="w-5 h-5 text-gray-400 shrink-0 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="sidebar-label font-medium truncate group-hover:text-blue-600 transition-colors">{{ Auth::user()->name ?? 'Usuario' }}</span>
        </a>

        <a href="/app/plano" data-link class="flex items-center gap-3 text-sm text-gray-600 mb-3 leading-5 px-2 py-2 -mx-2 rounded-lg hover:bg-gray-500/8 transition-colors group">
            <svg class="w-5 h-5 text-gray-400 shrink-0 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
            </svg>
            <span class="sidebar-label font-medium truncate group-hover:text-blue-600 transition-colors">Meu Plano</span>
        </a>

        <a href="/app/configuracoes" data-link class="flex items-center gap-3 text-sm text-gray-600 mb-2 leading-5 px-2 py-2 -mx-2 rounded-lg hover:bg-gray-500/8 transition-colors group">
            <svg class="w-5 h-5 text-gray-400 shrink-0 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span class="sidebar-label font-medium truncate group-hover:text-blue-600 transition-colors">Configuracoes</span>
        </a>

        <form action="{{ route('logout') }}" method="POST" id="logout-form-header">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white/60 border border-gray-300/60 rounded-lg hover:bg-gray-500/8 hover:text-red-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span class="sidebar-label">Sair</span>
            </button>
        </form>
    </div>
</aside>
