{{-- Dashboard - Hub Central --}}
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <style>
            @keyframes card-slide-in {
                from { opacity: 0; transform: translateY(60px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .dash-animate {
                opacity: 0;
                animation: card-slide-in 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            @media (prefers-reduced-motion: reduce) {
                .dash-animate { opacity: 1; animation: none; }
            }
        </style>

        {{-- Header --}}
        <div class="mb-4 sm:mb-8">
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Visão geral do seu escritório</p>
        </div>

        {{-- KPI Cards --}}
        @php
            $vol = $kpis['volume_total_notas'] ?? 0;
            $volValor = $kpis['volume_valor_total'] ?? 0;
            $partTotal = $kpis['participantes_total'] ?? 0;
            $partRisco = $kpis['participantes_risco'] ?? 0;
            $cred = $kpis['creditos'] ?? 0;
            $credMes = $kpis['creditos_usados_mes'] ?? 0;
            $alertTotal = $kpis['alertas_total'] ?? 0;
            $alertAlta = $kpis['alertas_alta'] ?? 0;
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-10">

            {{-- KPI 1: Volume Processado --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Volume Processado</p>
                        <p class="text-xl sm:text-3xl font-semibold text-gray-900">
                            {{ number_format($vol, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1 sm:mt-2 truncate">R$ {{ number_format($volValor, 2, ',', '.') }}</p>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-blue-50 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- KPI 2: Participantes Monitorados --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Participantes</p>
                        <p class="text-xl sm:text-3xl font-semibold text-gray-900">
                            {{ number_format($partTotal, 0, ',', '.') }}
                        </p>
                        <p class="text-xs {{ $partRisco > 0 ? 'text-amber-500' : 'text-gray-400' }} mt-1 sm:mt-2">
                            {{ $partRisco > 0 ? $partRisco . ' em risco alto/crítico' : 'Nenhum em risco' }}
                        </p>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 {{ $partRisco > 0 ? 'bg-amber-50' : 'bg-gray-100' }} rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 {{ $partRisco > 0 ? 'text-amber-500' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- KPI 3: Créditos Disponíveis --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Créditos</p>
                        <p class="text-xl sm:text-3xl font-semibold text-green-600">
                            {{ number_format($cred, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1 sm:mt-2">{{ $credMes }} usados este mês</p>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-green-50 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- KPI 4: Alertas Ativos --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Alertas</p>
                        <p class="text-xl sm:text-3xl font-semibold {{ $alertTotal > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                            {{ $alertTotal }}
                        </p>
                        <p class="text-xs {{ $alertAlta > 0 ? 'text-amber-500' : 'text-gray-400' }} mt-1 sm:mt-2">
                            {{ $alertAlta > 0 ? $alertAlta . ' alta severidade' : 'Nenhum alerta' }}
                        </p>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 {{ $alertTotal > 0 ? 'bg-amber-50' : 'bg-gray-100' }} rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 {{ $alertTotal > 0 ? 'text-amber-500' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        {{-- Cards de Módulos --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6 mb-6 sm:mb-10">

            {{-- Notas Fiscais --}}
            <a href="/app/notas-fiscais" data-link class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6 hover:border-gray-300 hover:shadow-sm transition-all group dash-animate" style="animation-delay: 0.4s">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-violet-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-violet-600 transition-colors">Notas Fiscais</h3>
                        <p class="text-xs text-gray-500">Dashboard de notas</p>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>{{ number_format($vol, 0, ',', '.') }} notas</span>
                    <span>R$ {{ number_format($volValor, 2, ',', '.') }}</span>
                </div>
            </a>

            {{-- BI Fiscal --}}
            <a href="/app/bi/dashboard" data-link class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6 hover:border-gray-300 hover:shadow-sm transition-all group dash-animate" style="animation-delay: 0.5s">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">BI Fiscal</h3>
                        <p class="text-xs text-gray-500">Dashboard analítico</p>
                    </div>
                </div>
                <div class="text-xs text-gray-500">
                    <span>Faturamento, compras e tributos</span>
                </div>
            </a>

            {{-- Importar EFD --}}
            <a href="/app/importacao/efd" data-link class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6 hover:border-gray-300 hover:shadow-sm transition-all group dash-animate" style="animation-delay: 0.6s">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-emerald-600 transition-colors">Importar EFD</h3>
                        <p class="text-xs text-gray-500">Upload de arquivos</p>
                    </div>
                </div>
                <div class="text-xs text-gray-500">
                    @if($ultimaImportacao)
                        <span class="inline-flex items-center gap-1">
                            @if($ultimaImportacao->status === 'concluido')
                                <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                            @elseif($ultimaImportacao->status === 'processando')
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            @endif
                            Última: {{ $ultimaImportacao->created_at->format('d/m/Y') }}
                        </span>
                    @else
                        <span>Nenhuma importação ainda</span>
                    @endif
                </div>
            </a>

        </div>

        {{-- Atividade Recente --}}
        <div class="bg-white rounded-lg border border-gray-200 mb-6 sm:mb-10 dash-animate" style="animation-delay: 0.7s">
            <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Atividade Recente</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($atividadeRecente as $atividade)
                    <div class="px-4 sm:px-5 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                @if($atividade['tipo'] === 'importacao')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700">Importação</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">Consulta</span>
                                @endif
                                <span class="text-sm text-gray-900 truncate">{{ $atividade['descricao'] }}</span>
                                @if($atividade['tipo'] === 'importacao' && !empty($atividade['tipo_efd']))
                                    <span class="text-xs text-gray-400">{{ $atividade['tipo_efd'] }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0 ml-3">
                                @php
                                    $statusClass = match($atividade['status']) {
                                        'concluido' => 'bg-green-50 text-green-700',
                                        'processando' => 'bg-blue-50 text-blue-700',
                                        'erro' => 'bg-red-50 text-red-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                    $statusLabel = match($atividade['status']) {
                                        'concluido' => 'Concluído',
                                        'processando' => 'Processando',
                                        'pendente' => 'Pendente',
                                        'erro' => 'Erro',
                                        default => ucfirst($atividade['status']),
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                                <span class="text-xs text-gray-400">{{ $atividade['data']->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 sm:px-5 py-6 sm:py-8 text-center">
                        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Nenhuma atividade recente</p>
                        <a href="/app/importacao/efd" data-link class="mt-2 inline-block text-xs font-medium text-gray-600 hover:text-gray-900">
                            Fazer sua primeira importação
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Primeiros Passos (condicional) --}}
        @if($isUsuarioNovo)
            <div class="bg-white rounded-lg border border-gray-200 mb-6 sm:mb-10 dash-animate" style="animation-delay: 0.8s">
                <div class="px-4 sm:px-5 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Primeiros Passos</h2>
                </div>
                <div class="p-3 sm:p-4">
                    <ol class="space-y-3 text-sm">
                        <li class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">1</span>
                            <div>
                                <p class="font-medium text-gray-700">Cadastre Clientes e Participantes</p>
                                <p class="text-xs text-gray-500">Importe um arquivo EFD ou adicione os dados manualmente</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">2</span>
                            <div>
                                <p class="font-medium text-gray-700">Monitore sua Carteira</p>
                                <p class="text-xs text-gray-500">Acompanhe a situação fiscal de clientes e participantes em tempo real</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">3</span>
                            <div>
                                <p class="font-medium text-gray-700">Analise o Score de Risco</p>
                                <p class="text-xs text-gray-500">Identifique participantes com irregularidades cadastrais ou tributárias</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">4</span>
                            <div>
                                <p class="font-medium text-gray-700">Explore o BI Fiscal</p>
                                <p class="text-xs text-gray-500">Visualize faturamento, tributos e indicadores a partir dos seus EFDs</p>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        @endif

        {{-- Suporte --}}
        <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 dash-animate" style="animation-delay: {{ $isUsuarioNovo ? '0.9' : '0.8' }}s">
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
