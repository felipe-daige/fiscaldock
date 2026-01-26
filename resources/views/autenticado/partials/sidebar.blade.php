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
    <nav class="flex-1 min-h-0 px-3 py-4 overflow-y-auto overflow-x-hidden space-y-2">
        <!-- CADASTRO -->
        <div class="mt-4">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">CADASTRO</div>

            <!-- Clientes -->
            <div class="sidebar-collapsible group collapsed">
                <div class="sidebar-summary flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-gray-700 leading-5 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span class="sidebar-label font-medium truncate">Clientes</span>
                    </div>
                    <svg class="sidebar-arrow sidebar-label w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <div class="sidebar-submenu-wrapper mt-1 ml-3 pl-3 border-l border-gray-200 space-y-1">
                    <a href="/app/clientes" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Todos os Clientes</span>
                    </a>
                    <a href="/app/novo_cliente" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Novo Cliente</span>
                    </a>
                    <a href="/app/consultar_cliente" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Consultar Cliente</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- IMPORTAR CNPJs -->
        <div class="mt-4">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">IMPORTAR CNPJs</div>

            <!-- Via SPED -->
            <a href="/app/monitoramento/sped" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                </svg>
                <span class="sidebar-label font-medium">Via SPED</span>
            </a>

            <!-- Via XMLs -->
            <a href="/app/monitoramento/xml" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="sidebar-label font-medium">Via XMLs</span>
            </a>

            <!-- Manual -->
            <a href="/app/monitoramento/avulso" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span class="sidebar-label font-medium">Manual</span>
            </a>
        </div>

        <!-- MONITORAMENTO -->
        <div class="mt-4">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">MONITORAMENTO</div>

            <!-- Participantes -->
            <a href="/app/monitoramento" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="sidebar-label font-medium">Participantes</span>
            </a>

            <!-- Planos de Consulta -->
            <a href="/app/monitoramento/planos" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <span class="sidebar-label font-medium">Planos de Consulta</span>
            </a>

            <!-- Histórico -->
            <a href="/app/monitoramento/historico" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="sidebar-label font-medium">Histórico</span>
            </a>
        </div>

        <!-- VALIDAÇÃO & ANÁLISE -->
        <div class="mt-4">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">VALIDAÇÃO & ANÁLISE</div>

            <!-- Notas Fiscais -->
            <div class="sidebar-collapsible group collapsed">
                <div class="sidebar-summary flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-gray-700 leading-5 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="sidebar-label font-medium truncate">Notas Fiscais</span>
                    </div>
                    <svg class="sidebar-arrow sidebar-label w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <div class="sidebar-submenu-wrapper mt-1 ml-3 pl-3 border-l border-gray-200 space-y-1">
                    <a href="/app/validar_xml" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Validar XML</span>
                    </a>
                    <a href="/app/xml_analise_risco" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Análise de Risco</span>
                    </a>
                    <a href="/notas/historico" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Histórico</span>
                    </a>
                </div>
            </div>

            <!-- SPED -->
            <div class="sidebar-collapsible group collapsed">
                <div class="sidebar-summary flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-gray-700 leading-5 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                        <span class="sidebar-label font-medium truncate">SPED</span>
                    </div>
                    <svg class="sidebar-arrow sidebar-label w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <div class="sidebar-submenu-wrapper mt-1 ml-3 pl-3 border-l border-gray-200 space-y-1">
                    <a href="/app/raf" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">RAF</span>
                    </a>
                    <a href="/app/raf/historico" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Histórico RAF</span>
                    </a>
                    <a href="/app/sped-analise-risco" data-link class="sidebar-sublink block w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors leading-5">
                        <span class="sidebar-label font-medium">Análise de Risco</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- CERTIDÕES -->
        <div class="mt-4">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">CERTIDÕES</div>

            <!-- Painel CNDs -->
            <a href="/certidoes" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="sidebar-label font-medium">Painel CNDs</span>
            </a>

            <!-- Emitir Avulsa -->
            <a href="/certidoes/emitir" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="sidebar-label font-medium">Emitir Avulsa</span>
            </a>

            <!-- Kit Licitação -->
            <a href="/certidoes/licitacao" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span class="sidebar-label font-medium">Kit Licitação</span>
            </a>
        </div>

        <!-- CONSULTAS -->
        <div class="mt-4">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">CONSULTAS</div>

            <!-- CNPJ -->
            <a href="/app/consultar_cnpj" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span class="sidebar-label font-medium">CNPJ</span>
            </a>

            <!-- CPF -->
            <a href="/consultas/cpf" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="sidebar-label font-medium">CPF</span>
            </a>

            <!-- Inscrição Estadual -->
            <a href="/app/consultar" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                </svg>
                <span class="sidebar-label font-medium">Inscrição Estadual</span>
            </a>

            <!-- Simples Nacional -->
            <a href="/consultas/simples" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <span class="sidebar-label font-medium">Simples Nacional</span>
            </a>

            <!-- Listas Restritivas -->
            <a href="/app/consultar_listas_restritivas" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span class="sidebar-label font-medium">Listas Restritivas</span>
            </a>
        </div>

        <!-- RELATÓRIOS -->
        <div class="mt-4">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-label leading-4">RELATÓRIOS</div>

            <!-- Diagnóstico Fiscal -->
            <a href="/relatorios/diagnostico" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="sidebar-label font-medium">Diagnóstico Fiscal</span>
            </a>

            <!-- Exportar Dados -->
            <a href="/relatorios/exportar" data-link class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="sidebar-label font-medium">Exportar Dados</span>
            </a>

            <!-- Alertas -->
            <a href="/alertas" data-link class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors w-full leading-5">
                <div class="flex items-center gap-3 min-w-0">
                    <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="sidebar-label font-medium">Alertas</span>
                </div>
                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold rounded-full bg-red-100 text-red-600 sidebar-label">5</span>
            </a>
        </div>
    </nav>

    <!-- User / Logout -->
    <div class="flex-shrink-0 px-4 py-4 border-t border-gray-200">
        <a href="/app/perfil" data-link class="flex items-center gap-3 text-sm text-gray-600 mb-3 leading-5 px-2 py-2 -mx-2 rounded-lg hover:bg-gray-50 transition-colors group">
            <svg class="w-5 h-5 text-gray-400 shrink-0 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="sidebar-label font-medium truncate group-hover:text-blue-600 transition-colors">{{ Auth::user()->name ?? 'Usuário' }}</span>
        </a>

        <a href="/configuracoes" data-link class="flex items-center gap-3 text-sm text-gray-600 mb-2 leading-5 px-2 py-2 -mx-2 rounded-lg hover:bg-gray-50 transition-colors group">
            <svg class="w-5 h-5 text-gray-400 shrink-0 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span class="sidebar-label font-medium truncate group-hover:text-blue-600 transition-colors">Configurações</span>
        </a>

        <a href="/plano" data-link class="flex items-center gap-3 text-sm text-gray-600 mb-3 leading-5 px-2 py-2 -mx-2 rounded-lg hover:bg-gray-50 transition-colors group">
            <svg class="w-5 h-5 text-gray-400 shrink-0 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
            </svg>
            <span class="sidebar-label font-medium truncate group-hover:text-blue-600 transition-colors">Meu Plano</span>
        </a>

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
