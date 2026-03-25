<div class="sidebar__overlay" id="sidebar-overlay"></div>

<x-sidebar.layout>
    <x-sidebar.section title="PRINCIPAL">
        <x-sidebar.item href="/app/dashboard" :active="request()->is('app/dashboard')">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                </svg>
            </x-slot:icon>
            Dashboard
        </x-sidebar.item>

        <x-sidebar.group title="Dashboards" :open="request()->is('app/bi/dashboard') || request()->is('app/notas-fiscais/dashboard')">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                </svg>
            </x-slot:icon>
            
            <x-sidebar.group-item href="/app/bi/dashboard">BI</x-sidebar.group-item>
            <x-sidebar.group-item href="/app/notas-fiscais/dashboard">Notas</x-sidebar.group-item>
        </x-sidebar.group>

        <x-sidebar.item href="/app/alertas" :active="request()->is('app/alertas')">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </x-slot:icon>
            Alertas
        </x-sidebar.item>
    </x-sidebar.section>

    <x-sidebar.section title="DOCUMENTOS">
        <x-sidebar.item href="/app/notas-fiscais" :active="request()->is('app/notas-fiscais')">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </x-slot:icon>
            Todas NF-e
        </x-sidebar.item>

        <x-sidebar.group title="SPED" :open="request()->is('app/importacao/*')">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                </svg>
            </x-slot:icon>
            
            <x-sidebar.group-item href="/app/importacao/xml">XMLs</x-sidebar.group-item>
            <x-sidebar.group-item href="/app/importacao/efd">EFDs</x-sidebar.group-item>
            <x-sidebar.group-item href="/app/importacao/historico">Histórico</x-sidebar.group-item>
        </x-sidebar.group>
    </x-sidebar.section>

    <x-sidebar.section title="CONSULTAS">
        <x-sidebar.group title="Consultas" :open="request()->is('app/consultas/*')">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </x-slot:icon>
            
            <x-sidebar.group-item href="/app/consultas/nova">Nova</x-sidebar.group-item>
            <x-sidebar.group-item href="/app/consultas/historico">Histórico</x-sidebar.group-item>
            <x-sidebar.group-item href="/app/consultas/planos">Planos</x-sidebar.group-item>
        </x-sidebar.group>

        <x-sidebar.group title="Compliance" :open="request()->is('app/score-fiscal') || request()->is('app/validacao')">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </x-slot:icon>

            <x-sidebar.group-item href="/app/score-fiscal">Score Risco</x-sidebar.group-item>
            <x-sidebar.group-item href="/app/validacao">Clearance</x-sidebar.group-item>
        </x-sidebar.group>
    </x-sidebar.section>

    <x-sidebar.section title="CADASTROS">
        <x-sidebar.item href="/app/minha-empresa" :active="request()->is('app/minha-empresa')">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </x-slot:icon>
            Empresa
        </x-sidebar.item>

        <x-sidebar.group title="Clientes" :open="request()->is('app/clientes*') || request()->is('app/novo-cliente*')">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </x-slot:icon>

            <x-sidebar.group-item href="/app/clientes">Todos</x-sidebar.group-item>
            <x-sidebar.group-item href="/app/novo-cliente">Novo</x-sidebar.group-item>
        </x-sidebar.group>

        <x-sidebar.group title="Participantes" :open="request()->is('app/participantes*') || request()->is('app/novo-participante*')">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </x-slot:icon>

            <x-sidebar.group-item href="/app/participantes">Todos</x-sidebar.group-item>
            <x-sidebar.group-item href="/app/novo-participante">Novo</x-sidebar.group-item>
        </x-sidebar.group>
    </x-sidebar.section>

    <x-slot:footer>
        <div class="sidebar__user">
            <!-- Native details user menu (expands up) -->
            <details class="group/user-details flex flex-col-reverse px-3 pt-3 pb-1 marker:content-none [&::-webkit-details-marker]:hidden">
                <summary class="sidebar__user-trigger outline-none select-none">
                    <svg class="sidebar__user-avatar" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="sidebar__user-name">{{ Auth::user()->name ?? 'Usuario' }}</span>
                    <svg class="sidebar__group-arrow transition-transform duration-200 group-open/user-details:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                    </svg>
                </summary>

                <div class="sidebar__user-menu group-open/user-details:block hidden">
                    <a href="/app/perfil" data-link class="sidebar__user-menu-item">
                        <svg class="sidebar__user-menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="sidebar__item-label">Perfil</span>
                    </a>
                    <a href="/app/plano" data-link class="sidebar__user-menu-item">
                        <svg class="sidebar__user-menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span class="sidebar__item-label">Meu Plano</span>
                    </a>
                    <a href="/app/creditos" data-link class="sidebar__user-menu-item">
                        <svg class="sidebar__user-menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path>
                        </svg>
                        <span class="sidebar__item-label">Creditos</span>
                    </a>
                    <a href="/app/configuracoes" data-link class="sidebar__user-menu-item">
                        <svg class="sidebar__user-menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="sidebar__item-label">Configuracoes</span>
                    </a>
                </div>
            </details>

            <!-- Logout (always visible) -->
            <div class="sidebar__logout">
                <form action="{{ route('logout') }}" method="POST" id="logout-form-header">
                    @csrf
                    <button type="submit" class="sidebar__logout-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="sidebar__logout-label">Sair</span>
                    </button>
                </form>
            </div>
        </div>
    </x-slot:footer>
</x-sidebar.layout>
