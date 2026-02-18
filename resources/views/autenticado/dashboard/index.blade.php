{{-- Dashboard - Painel de Controle --}}
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Visao geral da sua carteira de participantes</p>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

            {{-- Card 1: Conformidade --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        @php
                            $conformidade = $kpi_conformidade ?? 0;
                        @endphp
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Conformidade</p>
                        <p class="text-3xl font-semibold text-gray-900">
                            {{ number_format($conformidade, 1) }}%
                        </p>
                        <p class="text-xs text-gray-400 mt-2">Participantes em baixo risco</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center ml-4">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 2: Impostos Recuperaveis --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        @php
                            $impostos = $kpi_impostos_recuperaveis ?? 0;
                        @endphp
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Creditos Fiscais</p>
                        <p class="text-3xl font-semibold text-gray-900">
                            R$ {{ number_format($impostos, 2, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">PIS/COFINS recuperaveis</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center ml-4">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 3: Creditos --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        @php
                            $creditos = $kpi_creditos ?? 0;
                        @endphp
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Creditos</p>
                        <p class="text-3xl font-semibold text-green-600">
                            {{ number_format($creditos, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">Disponiveis para consultas</p>
                    </div>
                    <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center ml-4">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 4: Alertas --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        @php
                            $alertas = $kpi_alertas_criticos ?? 0;
                            $temAlerta = $alertas > 0;
                        @endphp
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Alertas</p>
                        <p class="text-3xl font-semibold {{ $temAlerta ? 'text-amber-600' : 'text-gray-900' }}">
                            {{ $alertas }}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">Requerem atencao</p>
                    </div>
                    <div class="w-12 h-12 {{ $temAlerta ? 'bg-amber-50' : 'bg-gray-100' }} rounded-lg flex items-center justify-center ml-4">
                        <svg class="w-6 h-6 {{ $temAlerta ? 'text-amber-500' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        {{-- Layout em 2 colunas --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

            {{-- Coluna Principal (2/3) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Acoes Rapidas --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Acoes Rapidas</h2>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <a href="/app/monitoramento/xml" data-link class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-colors group">
                                <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-gray-200 transition-colors">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Importar XMLs</p>
                                    <p class="text-xs text-gray-500">NF-e, NFS-e, CT-e</p>
                                </div>
                            </a>

                            <a href="/app/monitoramento/sped" data-link class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-colors group">
                                <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-gray-200 transition-colors">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Importar SPED</p>
                                    <p class="text-xs text-gray-500">EFD Fiscal/Contrib.</p>
                                </div>
                            </a>

                            <a href="/app/monitoramento/participantes" data-link class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-colors group">
                                <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-gray-200 transition-colors">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Participantes</p>
                                    <p class="text-xs text-gray-500">Ver todos</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Ultimas Consultas --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Ultimas Consultas</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Suas consultas mais recentes</p>
                        </div>
                        <a href="/app/consultas/historico" data-link class="text-xs text-gray-500 hover:text-gray-700">
                            Ver todas
                        </a>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse($ultimasConsultas as $consulta)
                            <div class="px-5 py-3 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $consulta->plano?->nome ?? 'Consulta' }}
                                            </span>
                                            @php
                                                $badge = $consulta->status_badge;
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badge['class'] }}">
                                                {{ $badge['label'] }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                            <span>{{ $consulta->total_participantes }} participante(s)</span>
                                            <span>{{ $consulta->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                    @if($consulta->isConcluido())
                                        <a href="/app/consultas/lote/{{ $consulta->id }}/baixar" class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center">
                                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Nenhuma consulta realizada</p>
                                <a href="/app/consultas/nova" data-link class="mt-2 inline-block text-xs font-medium text-gray-600 hover:text-gray-900">
                                    Fazer primeira consulta
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- Coluna Lateral (1/3) --}}
            <div class="space-y-6">

                {{-- Modulos Disponiveis --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Modulos</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        {{-- Nova Consulta --}}
                        <a href="/app/consultas/nova" data-link class="group flex items-center gap-3 py-2.5 px-4 hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 bg-blue-50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <span class="flex-1 text-sm font-medium text-gray-900">Nova Consulta</span>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>

                        {{-- Score de Risco --}}
                        <a href="/app/risk" data-link class="group flex items-center gap-3 py-2.5 px-4 hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 bg-amber-50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <span class="flex-1 text-sm font-medium text-gray-900">Score de Risco</span>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>

                        {{-- Validacao Contabil --}}
                        <a href="/app/validacao" data-link class="group flex items-center gap-3 py-2.5 px-4 hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 bg-emerald-50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <span class="flex-1 text-sm font-medium text-gray-900">Validacao Contabil</span>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>

                        {{-- BI Fiscal --}}
                        <a href="/app/analytics" data-link class="group flex items-center gap-3 py-2.5 px-4 hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 bg-violet-50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <span class="flex-1 text-sm font-medium text-gray-900">BI Fiscal</span>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>

                        {{-- Historico Consultas --}}
                        <a href="/app/consultas/historico" data-link class="group flex items-center gap-3 py-2.5 px-4 hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="flex-1 text-sm font-medium text-gray-900">Historico Consultas</span>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Dicas --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Como comecar</h2>
                    </div>
                    <div class="p-4">
                        <ol class="space-y-3 text-sm">
                            <li class="flex gap-3">
                                <span class="flex-shrink-0 w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">1</span>
                                <div>
                                    <p class="font-medium text-gray-700">Importe seus XMLs</p>
                                    <p class="text-xs text-gray-500">NF-e, NFS-e ou CT-e em lote</p>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <span class="flex-shrink-0 w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">2</span>
                                <div>
                                    <p class="font-medium text-gray-700">Analise o Score de Risco</p>
                                    <p class="text-xs text-gray-500">Verifique a situacao dos participantes</p>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <span class="flex-shrink-0 w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">3</span>
                                <div>
                                    <p class="font-medium text-gray-700">Valide as notas fiscais</p>
                                    <p class="text-xs text-gray-500">Identifique inconsistencias contabeis</p>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>

                {{-- Suporte --}}
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-white rounded-lg border border-gray-200 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Precisa de ajuda?</p>
                            <p class="text-xs text-gray-500 mt-1">Entre em contato com nosso suporte.</p>
                            <a href="mailto:suporte@fiscaldock.com" class="inline-block mt-2 text-xs font-medium text-gray-600 hover:text-gray-900">
                                suporte@fiscaldock.com
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
