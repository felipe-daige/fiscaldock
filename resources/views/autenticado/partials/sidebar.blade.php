<div class="sidebar__overlay" id="sidebar-overlay"></div>

@php
    // Gate bi_completo: itens gateados ganham pill de cadeado pro Free — a tela abre
    // em paywall (blur + card explicativo), então o link continua real.
    $__biCompleto = auth()->check()
        && app(\App\Services\Entitlements\EntitlementService::class)->permits(auth()->user(), 'bi_completo');
@endphp

<x-sidebar.layout>
    {{-- PAINEL --}}
    <x-sidebar.section title="PAINEL">
        <x-sidebar.item href="/app/dashboard">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                </svg>
            </x-slot:icon>
            Dashboard
        </x-sidebar.item>

        <x-sidebar.item href="/app/alertas" :badge="($alertasAtivosCount ?? 0) > 0 ? $alertasAtivosCount : null" badge-label="Alertas ativos">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </x-slot:icon>
            Alertas
        </x-sidebar.item>

        <x-sidebar.item href="/app/status" :badge="(\App\Models\IntegracaoStatus::problemasCount() ?: null)" badge-label="Integrações com problema">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                </svg>
            </x-slot:icon>
            Status dos serviços
        </x-sidebar.item>
    </x-sidebar.section>

    {{-- DOCUMENTOS --}}
    <x-sidebar.section title="DOCUMENTOS">
        <x-sidebar.group title="Notas Fiscais" :open="request()->is('app/notas*') || request()->is('app/catalogo*')">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 3h12v18l-3-2-3 2-3-2-3 2V3zm3 4h6m-6 4h6m-6 4h3"></path>
                </svg>
            </x-slot:icon>

            <x-sidebar.group-item href="/app/notas">Listagem</x-sidebar.group-item>
            <x-sidebar.group-item href="/app/notas/dashboard">Dashboard</x-sidebar.group-item>
            <x-sidebar.group-item href="/app/catalogo">Catálogo</x-sidebar.group-item>
        </x-sidebar.group>

        <x-sidebar.group title="Importação" :open="request()->is('app/importacao/*')">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V4m0 0L8 8m4-4l4 4M5 14v5h14v-5"></path>
                </svg>
            </x-slot:icon>

            <x-sidebar.group-item href="/app/importacao/efd">EFD</x-sidebar.group-item>
            <x-sidebar.group-item href="/app/importacao/xml">XML</x-sidebar.group-item>
            <x-sidebar.group-item href="/app/importacao/historico">Histórico</x-sidebar.group-item>
        </x-sidebar.group>

        <x-sidebar.item href="/app/arquivos">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path>
                </svg>
            </x-slot:icon>
            Meus Arquivos
        </x-sidebar.item>
    </x-sidebar.section>

    {{-- INTELIGÊNCIA --}}
    <x-sidebar.section title="INTELIGÊNCIA">
        <x-sidebar.group title="Clearance NF-e" :open="request()->is('app/clearance*')">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3h7l4 4v14H7V3zm7 0v5h5M9.5 14l2 2 4-4"></path>
                </svg>
            </x-slot:icon>

            <x-sidebar.group-item href="/app/clearance/dashboard">Dashboard</x-sidebar.group-item>
            <x-sidebar.group-item href="/app/clearance/notas">Verificar Notas</x-sidebar.group-item>
            @if(config('clearance.busca_avulsa.habilitada'))
                <x-sidebar.group-item href="/app/clearance/buscar">Buscar Notas</x-sidebar.group-item>
            @endif
        </x-sidebar.group>

        <x-sidebar.item href="/app/bi/dashboard">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5v14h16M7 15l4-5 3 3 5-7"></path>
                </svg>
            </x-slot:icon>
            BI Fiscal
        </x-sidebar.item>

        <x-sidebar.item href="/app/bi/catalogo-itens" pill="Novo" pill-until="2026-07-31" :lock="! $__biCompleto">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
            </x-slot:icon>
            Catálogo de Itens
        </x-sidebar.item>

        <x-sidebar.item href="/app/bi/cruzamentos" pill="Novo" pill-until="2026-07-31" :lock="! $__biCompleto">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
            </x-slot:icon>
            Cruzamentos
        </x-sidebar.item>

        <x-sidebar.item href="/app/resumo-fiscal">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
            </x-slot:icon>
            Resumo Fiscal
        </x-sidebar.item>
    </x-sidebar.section>

    {{-- CONSULTAS --}}
    <x-sidebar.section title="CONSULTAS">
        {{-- Link direto (sem submenu): Histórico e Planos são alcançados pelos botões
             no header de /app/consulta/painel — mesmo padrão de Clientes/Participantes. --}}
        <x-sidebar.item href="/app/consulta/painel">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </x-slot:icon>
            Consulta CNPJ
        </x-sidebar.item>

        {{-- Item único (sem submenu): Histórico e o freio de consumo são alcançados
             dentro do próprio painel — mesmo padrão de Consulta CNPJ. --}}
        <x-sidebar.item href="/app/monitoramento/painel">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </x-slot:icon>
            Monitoramento
        </x-sidebar.item>

        <x-sidebar.item href="/app/score-fiscal">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16a8 8 0 0116 0M12 16l4-6M7 20h10"></path>
                </svg>
            </x-slot:icon>
            Score Fiscal
        </x-sidebar.item>
    </x-sidebar.section>

    {{-- CADASTROS --}}
    <x-sidebar.section title="CADASTROS">
        <x-sidebar.item href="/app/minha-empresa">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </x-slot:icon>
            Empresa
        </x-sidebar.item>

        <x-sidebar.item href="/app/clientes">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5h18v14H3V5zm6.5 5a2 2 0 11-4 0 2 2 0 014 0zM5 16c.4-1.7 1.2-2.5 2.5-2.5S9.6 14.3 10 16m3-7h5m-5 4h5"></path>
                </svg>
            </x-slot:icon>

            Clientes
        </x-sidebar.item>

        <x-sidebar.item href="/app/participantes">
            <x-slot:icon>
                <svg class="sidebar__item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </x-slot:icon>

            Participantes
        </x-sidebar.item>
    </x-sidebar.section>

    <x-slot:footer>
        @php
            $__actor = $actorUser ?? auth()->user();
            $__u = isset($accountContext) ? $accountContext->owner() : auth()->user();
            // Fonte única: User::hasActiveTrial() (inclui trial_credits_remaining > 0, mesma regra dos gates)
            $__trialOn = $__u && $__u->hasActiveTrial();
        @endphp
        @if($__trialOn)
            @php
                $__dias = max(0, (int) ceil(now()->diffInDays($__u->trial_expires_at)));
                $__diasTotal = max(1, (int) config('trial.validade_dias'));
                $__pct = max(4, min(100, (int) round($__dias / $__diasTotal * 100)));
                $__creditos = (int) $__u->trial_credits_remaining;
            @endphp
            <a href="/app/planos" data-link data-trial-widget class="block mb-3 px-3 py-2.5 rounded border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-colors">
                <span class="sidebar__trial-mini" title="Trial — {{ $__dias }} {{ $__dias === 1 ? 'dia restante' : 'dias restantes' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Trial</span>
                    <span class="text-[11px] font-semibold text-gray-700">@brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency($__creditos))</span>
                </div>
                <div class="mt-2 rounded-full overflow-hidden" style="height:3px;background-color:#e5e7eb;">
                    <div style="height:100%;width:{{ $__pct }}%;background-color:#1f2937;"></div>
                </div>
                <div class="mt-2 flex items-center justify-between gap-2 text-[11px]">
                    <span class="text-gray-500">{{ $__dias }} {{ $__dias === 1 ? 'dia restante' : 'dias restantes' }}</span>
                    <span class="font-medium text-gray-700">Ver planos</span>
                </div>
            </a>
        @endif
        <div class="sidebar__user">
            <details class="group/user-details sidebar__user-panel flex flex-col-reverse marker:content-none [&::-webkit-details-marker]:hidden">
                <summary class="sidebar__user-trigger outline-none select-none">
                    <svg class="sidebar__user-avatar" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    @php
                        $__pricing = app(\App\Services\PricingCatalogService::class);
                        $__saldoBrl = $__pricing->creditsToCurrency((int) ($__u?->credits ?? 0));
                        $__planoAtual = $__u ? app(\App\Services\Entitlements\EntitlementService::class)->planFor($__u) : null;
                        $__planoNome = $__trialOn ? 'Trial' : ($__planoAtual?->nome ?? 'Free');
                    @endphp
                    <span class="min-w-0 flex-1">
                        <span class="sidebar__user-name">{{ $__actor?->name ?? 'Usuário' }}</span>
                        {{-- Plano + saldo no lugar do rótulo estático "Conta" — não ocupa espaço extra --}}
                        <span class="sidebar__user-role" title="Plano atual e saldo disponível">
                            <span>{{ $__planoNome }}</span>
                            <span aria-hidden="true">·</span>
                            <span data-sidebar-saldo style="color: #047857;">@brl($__saldoBrl)</span>
                        </span>
                    </span>
                    <svg class="sidebar__group-arrow transition-transform duration-200 group-open/user-details:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                    </svg>
                </summary>

                <div class="sidebar__user-menu group-open/user-details:block hidden">
                    <div class="sidebar__user-menu-heading">Conta</div>
                    <a href="/app/perfil" data-link data-sidebar-user-link class="sidebar__user-menu-item">
                        <svg class="sidebar__user-menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="sidebar__item-label">Perfil</span>
                    </a>
                    @if(isset($accountContext) && $accountContext->canManageTeam())
                        <a href="/app/equipe" data-link data-sidebar-user-link class="sidebar__user-menu-item">
                            <svg class="sidebar__user-menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="sidebar__item-label">Equipe e acessos</span>
                        </a>
                    @endif
                    <a href="/app/privacidade" data-link data-sidebar-user-link class="sidebar__user-menu-item">
                        <svg class="sidebar__user-menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 10V8a5 5 0 0110 0v2m-9 0h8a2 2 0 012 2v7H6v-7a2 2 0 012-2zm4 4v2"></path>
                        </svg>
                        <span class="sidebar__item-label">Privacidade</span>
                    </a>
                    <a href="/app/configuracoes" data-link data-sidebar-user-link class="sidebar__user-menu-item">
                        <svg class="sidebar__user-menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="sidebar__item-label">Configurações</span>
                    </a>

                    <div class="sidebar__user-menu-heading">Financeiro</div>
                    <a href="/app/planos" data-link data-sidebar-user-link class="sidebar__user-menu-item">
                        <svg class="sidebar__user-menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 7l7-4 7 4-7 4-7-4zm0 5l7 4 7-4M5 17l7 4 7-4"></path>
                        </svg>
                        <span class="sidebar__item-label">Planos</span>
                    </a>
                    <a href="/app/saldo" data-link data-sidebar-user-link class="sidebar__user-menu-item">
                        <svg class="sidebar__user-menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7.5A2.5 2.5 0 016.5 5H18a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V7.5zm0 0A2.5 2.5 0 006.5 10H20m-4 3h4v4h-4a2 2 0 010-4z"></path>
                        </svg>
                        <span class="sidebar__item-label">Saldo</span>
                    </a>
                    <a href="/app/faixa-comercial" data-link data-sidebar-user-link class="sidebar__user-menu-item">
                        <svg class="sidebar__user-menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 20V10m5 10V6m5 14v-7m5 7V4M3 20h18"></path>
                        </svg>
                        <span class="sidebar__item-label">Faixa Comercial</span>
                    </a>

                    <div class="sidebar__user-menu-divider"></div>
                    <a href="/app/suporte" data-link data-sidebar-user-link class="sidebar__user-menu-item">
                        <svg class="sidebar__user-menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 13v-2a8 8 0 0116 0v2M4 13h3v6H6a2 2 0 01-2-2v-4zm16 0h-3v6h1a2 2 0 002-2v-4zm-3 6c0 1.1-.9 2-2 2h-3"></path>
                        </svg>
                        <span class="sidebar__item-label">Suporte</span>
                    </a>

                    @if(auth()->user()?->is_admin)
                        <div class="sidebar__user-menu-heading">Admin</div>
                        <a href="/app/admin" data-link data-sidebar-user-link class="sidebar__user-menu-item">
                            <svg class="sidebar__user-menu-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span class="sidebar__item-label">Admin — Visão Geral</span>
                        </a>
                    @endif
                </div>
            </details>

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
