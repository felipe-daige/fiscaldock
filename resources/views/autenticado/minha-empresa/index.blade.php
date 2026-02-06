{{-- Minha Empresa - Dashboard --}}
<div class="min-h-screen bg-gray-50" id="minha-empresa-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header com dados da empresa --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $empresa->razao_social ?? $empresa->nome }}</h1>
                            <p class="text-sm text-gray-500">CNPJ: {{ $empresa->documento_formatado }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Badge Situacao --}}
                    @php
                        $situacao = strtoupper($certidoes['situacao_cadastral'] ?? 'NAO CONSULTADO');
                        $situacaoCor = match($situacao) {
                            'ATIVA' => 'green',
                            'SUSPENSA' => 'yellow',
                            'INAPTA', 'BAIXADA' => 'red',
                            default => 'gray'
                        };
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $situacaoCor }}-100 text-{{ $situacaoCor }}-800">
                        {{ $situacao }}
                    </span>

                    {{-- Badge Simples Nacional --}}
                    @if($certidoes['simples_nacional'] === true)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            Simples Nacional
                        </span>
                    @endif

                    {{-- Badge MEI --}}
                    @if($certidoes['mei'] === true)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            MEI
                        </span>
                    @endif

                    <a href="/app/minha-empresa/configurar" data-link class="text-sm text-gray-500 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Alterar empresa
                    </a>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Score de Risco --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-500">Score de Risco</span>
                    @if($score)
                        @php
                            $scoreCor = match($score->classificacao) {
                                'baixo' => 'green',
                                'medio' => 'yellow',
                                'alto' => 'orange',
                                'critico' => 'red',
                                default => 'gray'
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $scoreCor }}-100 text-{{ $scoreCor }}-800">
                            {{ ucfirst($score->classificacao) }}
                        </span>
                    @endif
                </div>
                <div class="flex items-end gap-2">
                    @if($score)
                        <span class="text-4xl font-bold text-{{ $scoreCor }}-600">{{ $score->score_total }}</span>
                        <span class="text-lg text-gray-400 mb-1">/100</span>
                    @else
                        <span class="text-2xl font-semibold text-gray-400">Nao avaliado</span>
                    @endif
                </div>
                @if($score && $score->ultima_consulta_em)
                    <p class="text-xs text-gray-400 mt-2">Ultima consulta: {{ $score->ultima_consulta_em->format('d/m/Y') }}</p>
                @endif
            </div>

            {{-- CND Federal --}}
            @include('autenticado.minha-empresa.partials.card-certidao', [
                'nome' => 'CND Federal',
                'dados' => $certidoes['cnd_federal'],
                'icone' => 'shield-check'
            ])

            {{-- CND Estadual --}}
            @include('autenticado.minha-empresa.partials.card-certidao', [
                'nome' => 'CND Estadual',
                'dados' => $certidoes['cnd_estadual'],
                'icone' => 'map'
            ])

            {{-- FGTS (CRF) --}}
            @include('autenticado.minha-empresa.partials.card-certidao', [
                'nome' => 'CRF (FGTS)',
                'dados' => $certidoes['fgts'],
                'icone' => 'users'
            ])
        </div>

        {{-- Segunda linha de cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Alertas Recentes --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Alertas Recentes</h3>
                </div>
                <div class="p-6">
                    @if(count($alertas) > 0)
                        <div class="space-y-3">
                            @foreach($alertas as $alerta)
                                @php
                                    $alertaCor = match($alerta['tipo']) {
                                        'critico' => 'red',
                                        'atencao' => 'yellow',
                                        'info' => 'blue',
                                        default => 'gray'
                                    };
                                @endphp
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-{{ $alertaCor }}-50 border border-{{ $alertaCor }}-200">
                                    <svg class="w-5 h-5 text-{{ $alertaCor }}-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($alerta['tipo'] === 'critico')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        @elseif($alerta['tipo'] === 'atencao')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        @endif
                                    </svg>
                                    <span class="text-sm text-{{ $alertaCor }}-800 font-medium">{{ $alerta['mensagem'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Nenhum alerta no momento</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Acoes Rapidas --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Acoes Rapidas</h3>
                </div>
                <div class="p-6 space-y-3">
                    {{-- Atualizar Consultas --}}
                    <a href="/app/consultas/nova{{ $participante ? '?participante=' . $participante->id : '' }}" data-link class="flex items-center justify-between p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors">Atualizar Consultas</p>
                                <p class="text-sm text-gray-500">Realizar nova consulta fiscal</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>

                    {{-- Ver Historico --}}
                    <a href="/app/minha-empresa/historico" data-link class="flex items-center justify-between p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 group-hover:text-green-600 transition-colors">Ver Historico Completo</p>
                                <p class="text-sm text-gray-500">Todas as consultas realizadas</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>

                    {{-- Score de Risco --}}
                    @if($participante)
                    <a href="/app/risk/participante/{{ $participante->id }}" data-link class="flex items-center justify-between p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 group-hover:text-purple-600 transition-colors">Detalhes do Score</p>
                                <p class="text-sm text-gray-500">Analise completa de risco</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    @endif

                    {{-- Baixar Relatorio (se tiver consulta) --}}
                    @if($ultimaConsulta && $ultimaConsulta->lote)
                        <a href="/app/consultas/lote/{{ $ultimaConsulta->lote->id }}/baixar" class="flex items-center justify-between p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 group-hover:text-orange-600 transition-colors">Baixar Relatorio</p>
                                    <p class="text-sm text-gray-500">Ultima consulta em CSV</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-orange-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- CNDT Card adicional --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- CNDT --}}
            @include('autenticado.minha-empresa.partials.card-certidao', [
                'nome' => 'CNDT',
                'dados' => $certidoes['cndt'],
                'icone' => 'briefcase'
            ])

            {{-- Ultima Consulta Info --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 col-span-1 sm:col-span-2 lg:col-span-3">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Ultima Consulta Completa</span>
                        @if($ultimaConsulta)
                            <p class="text-lg font-semibold text-gray-900 mt-1">
                                {{ $ultimaConsulta->consultado_em ? $ultimaConsulta->consultado_em->format('d/m/Y H:i') : 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                Consultas: {{ implode(', ', $ultimaConsulta->getConsultasRealizadas()) ?: 'N/A' }}
                            </p>
                        @else
                            <p class="text-lg font-semibold text-gray-400 mt-1">Nenhuma consulta realizada</p>
                            <p class="text-sm text-gray-500">Realize uma consulta para ver os resultados</p>
                        @endif
                    </div>
                    @if(!$ultimaConsulta)
                        <a href="/app/consultas/nova{{ $participante ? '?participante=' . $participante->id : '' }}" data-link class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Consultar Agora
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
