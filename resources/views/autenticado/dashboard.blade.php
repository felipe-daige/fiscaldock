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
                        <p class="text-3xl font-semibold text-gray-900">
                            {{ number_format($creditos, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">Disponiveis para consultas</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center ml-4">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                {{-- Tabela de Participantes --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Participantes</h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $participantes->total() ?? 0 }} registro(s)
                                @if(!empty($filtroBusca))
                                    para "{{ $filtroBusca }}"
                                @endif
                            </p>
                        </div>
                        <div class="w-full sm:w-64">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <input
                                    type="text"
                                    id="busca-participante"
                                    placeholder="Buscar CNPJ ou nome..."
                                    value="{{ $filtroBusca ?? '' }}"
                                    class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Participante
                                    </th>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Situacao
                                    </th>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        UF
                                    </th>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Score
                                    </th>
                                    <th scope="col" class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">

                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($participantes as $participante)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        {{-- Nome e CNPJ --}}
                                        <td class="px-5 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $participante->nome_fantasia ?: $participante->razao_social ?: '-' }}
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $participante->cnpj_formatado }}</div>
                                        </td>

                                        {{-- Situacao --}}
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            @php
                                                $situacao = strtoupper($participante->situacao_cadastral ?? '');
                                                $situacaoClass = match($situacao) {
                                                    'ATIVA' => 'bg-emerald-50 text-emerald-700',
                                                    'SUSPENSA' => 'bg-amber-50 text-amber-700',
                                                    'BAIXADA', 'INAPTA', 'NULA' => 'bg-red-50 text-red-700',
                                                    default => 'bg-gray-100 text-gray-600',
                                                };
                                                $situacaoLabel = $situacao ?: 'N/D';
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $situacaoClass }}">
                                                {{ $situacaoLabel }}
                                            </span>
                                        </td>

                                        {{-- UF --}}
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-600">{{ $participante->uf ?: '-' }}</span>
                                        </td>

                                        {{-- Score --}}
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            @if($participante->score)
                                                @php
                                                    $score = $participante->score;
                                                    $scoreTotal = $score->score_total;
                                                    $classificacao = $score->classificacao;
                                                    $scoreClass = match($classificacao) {
                                                        'baixo' => 'bg-emerald-50 text-emerald-700',
                                                        'medio' => 'bg-amber-50 text-amber-700',
                                                        'alto' => 'bg-orange-50 text-orange-700',
                                                        'critico' => 'bg-red-50 text-red-700',
                                                        default => 'bg-gray-100 text-gray-600',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $scoreClass }}">
                                                    {{ $scoreTotal }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>

                                        {{-- Acoes --}}
                                        <td class="px-5 py-4 whitespace-nowrap text-right">
                                            <a href="/app/risk/participante/{{ $participante->id }}" data-link class="text-gray-500 hover:text-gray-700 text-sm">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-12 text-center">
                                            <div class="text-gray-400">
                                                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                                <p class="mt-2 text-sm text-gray-500">Nenhum participante encontrado</p>
                                                <p class="mt-1 text-xs text-gray-400">
                                                    @if(!empty($filtroBusca))
                                                        Tente uma busca diferente
                                                    @else
                                                        Importe XMLs ou SPEDs para comecar
                                                    @endif
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginacao --}}
                    @if($participantes->hasPages())
                        <div class="px-5 py-3 border-t border-gray-100">
                            {{ $participantes->links() }}
                        </div>
                    @endif
                </div>

            </div>

            {{-- Coluna Lateral (1/3) --}}
            <div class="space-y-6">

                {{-- Modulos Disponiveis --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Modulos</h2>
                    </div>
                    <div class="p-3 divide-y divide-gray-100">
                        <a href="/app/analytics" data-link class="flex items-center gap-3 py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 bg-slate-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <span class="flex-1 text-sm font-medium text-gray-900">BI Fiscal</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>

                        <a href="/app/risk" data-link class="flex items-center gap-3 py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 bg-slate-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <span class="flex-1 text-sm font-medium text-gray-900">Score de Risco</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>

                        <a href="/app/validacao" data-link class="flex items-center gap-3 py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 bg-slate-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <span class="flex-1 text-sm font-medium text-gray-900">Validacao Contabil</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>

                        <a href="/app/raf" data-link class="flex items-center gap-3 py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 bg-slate-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <span class="flex-1 text-sm font-medium text-gray-900">Relatorio RAF</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

{{-- Script de busca com debounce --}}
<script>
(function() {
    const input = document.getElementById('busca-participante');
    if (!input) return;

    let debounceTimer;
    const debounceDelay = 400;

    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            const busca = input.value.trim();
            const url = new URL(window.location.href);

            if (busca) {
                url.searchParams.set('busca', busca);
            } else {
                url.searchParams.delete('busca');
            }

            url.searchParams.delete('page');

            if (window.spa && typeof window.spa.navigate === 'function') {
                window.spa.navigate(url.pathname + url.search);
            } else {
                window.location.href = url.toString();
            }
        }, debounceDelay);
    });

    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            clearTimeout(debounceTimer);
            const busca = input.value.trim();
            const url = new URL(window.location.href);

            if (busca) {
                url.searchParams.set('busca', busca);
            } else {
                url.searchParams.delete('busca');
            }

            url.searchParams.delete('page');

            if (window.spa && typeof window.spa.navigate === 'function') {
                window.spa.navigate(url.pathname + url.search);
            } else {
                window.location.href = url.toString();
            }
        }
    });
})();
</script>
