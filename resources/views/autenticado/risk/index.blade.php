{{-- Risk Score - Dashboard --}}
<div class="min-h-screen bg-gray-50" id="risk-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Score de Risco</h1>
                    <p class="mt-1 text-sm text-gray-600">Avalie o risco fiscal e de compliance dos seus participantes.</p>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Filtro de Classificacao --}}
                    <select id="filtro-classificacao" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="todos" {{ ($filtroClassificacao ?? 'todos') === 'todos' ? 'selected' : '' }}>Todas as Classificacoes</option>
                        <option value="baixo" {{ ($filtroClassificacao ?? '') === 'baixo' ? 'selected' : '' }}>Baixo Risco</option>
                        <option value="medio" {{ ($filtroClassificacao ?? '') === 'medio' ? 'selected' : '' }}>Medio Risco</option>
                        <option value="alto" {{ ($filtroClassificacao ?? '') === 'alto' ? 'selected' : '' }}>Alto Risco</option>
                        <option value="critico" {{ ($filtroClassificacao ?? '') === 'critico' ? 'selected' : '' }}>Critico</option>
                    </select>
                    {{-- Busca --}}
                    <div class="relative">
                        <input type="text" id="busca-participante" placeholder="Buscar CNPJ ou razao social..." value="{{ $filtroBusca ?? '' }}" class="w-64 px-4 py-2 pl-10 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            {{-- Total Avaliados --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $estatisticas['total_avaliados'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Avaliados</p>
                    </div>
                </div>
            </div>

            {{-- Baixo Risco --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600">{{ $estatisticas['baixo_risco'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Baixo Risco</p>
                    </div>
                </div>
            </div>

            {{-- Medio Risco --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-yellow-600">{{ $estatisticas['medio_risco'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Medio Risco</p>
                    </div>
                </div>
            </div>

            {{-- Alto Risco --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-orange-600">{{ $estatisticas['alto_risco'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Alto Risco</p>
                    </div>
                </div>
            </div>

            {{-- Critico --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600">{{ $estatisticas['critico'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Critico</p>
                    </div>
                </div>
            </div>
        </div>

        @if(($emRiscoCritico ?? collect())->count() > 0)
        {{-- Alertas de Risco Critico --}}
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h4 class="font-semibold text-red-800">Atencao: Participantes em Risco Critico</h4>
                    <ul class="mt-2 space-y-1">
                        @foreach($emRiscoCritico as $score)
                            <li class="text-sm text-red-700">
                                <a href="/app/risk/participante/{{ $score->participante_id }}" data-link class="hover:underline">
                                    {{ $score->participante->razao_social ?? 'N/A' }} ({{ $score->participante->cnpj_formatado ?? '' }}) - Score: {{ $score->score_total }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        {{-- Lista de Participantes --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Participantes</h3>
                <span class="text-sm text-gray-500">{{ $participantes->total() ?? 0 }} participante(s)</span>
            </div>

            @if(($participantes ?? collect())->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participante</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CNPJ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UF</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Classificacao</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ultima Consulta</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($participantes as $participante)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $participante->razao_social ?? 'N/A' }}</div>
                                @if($participante->nome_fantasia)
                                    <div class="text-sm text-gray-500">{{ $participante->nome_fantasia }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $participante->cnpj_formatado }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $participante->uf ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($participante->score)
                                    <span class="text-lg font-bold {{ $participante->score->score_total >= 80 ? 'text-red-600' : ($participante->score->score_total >= 50 ? 'text-orange-600' : ($participante->score->score_total >= 20 ? 'text-yellow-600' : 'text-green-600')) }}">
                                        {{ $participante->score->score_total }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($participante->score)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $participante->score->classificacao_badge_class }}">
                                        {{ $participante->score->classificacao_label }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        Nao Avaliado
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                @if($participante->score && $participante->score->ultima_consulta_em)
                                    {{ $participante->score->ultima_consulta_em->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <a href="/app/risk/participante/{{ $participante->id }}" data-link class="text-blue-600 hover:text-blue-900 mr-3">Ver Detalhes</a>
                                <button type="button" class="btn-consultar text-green-600 hover:text-green-900" data-id="{{ $participante->id }}">Consultar</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginacao --}}
            @if($participantes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $participantes->withQueryString()->links() }}
            </div>
            @endif

            @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum participante encontrado</h3>
                <p class="mt-2 text-sm text-gray-500">Importe participantes via SPED ou XMLs.</p>
                <a href="/app/monitoramento/xml" data-link class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Importar XMLs
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<script src="{{ asset('js/risk-score.js') }}"></script>
