{{-- Validacao Contabil Inteligente - Dashboard --}}
<div class="min-h-screen bg-gray-50" id="validacao-container">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Validacao Contabil</h1>
                    <p class="text-xs text-gray-500 mt-1">Analise e valide suas notas fiscais com base em regras contabeis brasileiras.</p>
                </div>
                <a href="/app/validacao/alertas" data-link class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Ver Alertas
                </a>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="space-y-6">
            <style>
                @keyframes card-slide-in {
                    from { opacity: 0; transform: translateY(60px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .vc-animate {
                    opacity: 0;
                    animation: card-slide-in 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                }
                @media (prefers-reduced-motion: reduce) {
                    .vc-animate { opacity: 1; animation: none; }
                }
            </style>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 vc-animate" style="animation-delay: 0.05s">
            {{-- Total Notas --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900">{{ $estatisticas['total_notas'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Notas</p>
                    </div>
                </div>
            </div>

            {{-- Validadas --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900">{{ $estatisticas['total_validadas'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Validadas ({{ $estatisticas['percentual_validado'] ?? 0 }}%)</p>
                    </div>
                </div>
            </div>

            {{-- Conforme --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-green-600">{{ $estatisticas['conforme'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Conforme</p>
                    </div>
                </div>
            </div>

            {{-- Atencao --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-yellow-600">{{ $estatisticas['atencao'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Atencao</p>
                    </div>
                </div>
            </div>

            {{-- Irregular --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-orange-600">{{ $estatisticas['irregular'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Irregular</p>
                    </div>
                </div>
            </div>

            {{-- Critico --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-red-600">{{ $estatisticas['critico'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Critico</p>
                    </div>
                </div>
            </div>
        </div>

        @if(($notasCriticas ?? collect())->count() > 0)
        {{-- Alertas Criticos --}}
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 vc-animate" style="animation-delay: 0.1s">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="flex-1">
                    <h4 class="font-semibold text-red-800">Notas com Alertas Bloqueantes</h4>
                    <ul class="mt-2 space-y-1">
                        @foreach($notasCriticas->take(5) as $nota)
                            <li class="text-sm text-red-700">
                                <a href="/app/validacao/nota/{{ $nota->id }}" data-link class="hover:underline">
                                    NF {{ $nota->numero_nota }} - {{ $nota->emitente->razao_social ?? $nota->emit_cnpj }}
                                    ({{ count($nota->validacao_alertas) }} alerta(s))
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    @if($notasCriticas->count() > 5)
                        <a href="/app/validacao/alertas?nivel=bloqueante" data-link class="mt-2 inline-block text-sm text-red-700 hover:underline font-medium">
                            Ver todos os {{ $notasCriticas->count() }} alertas
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 vc-animate" style="animation-delay: 0.15s">
            {{-- Importacoes para Validar --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Importacoes Recentes</h3>
                </div>

                @if(($importacoes ?? collect())->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($importacoes as $importacao)
                    <div class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $importacao->tipo_documento ?? 'XML' }} - {{ $importacao->created_at->format('d/m/Y H:i') }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $importacao->notas_count ?? 0 }} nota(s) |
                                    {{ $importacao->notas_validadas_count ?? 0 }} validada(s)
                                </p>
                            </div>
                            <div class="flex items-center gap-2 ml-4">
                                @if(($importacao->notas_validadas_count ?? 0) < ($importacao->notas_count ?? 0))
                                    <button type="button"
                                            class="btn-validar-importacao inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold shadow-sm hover:bg-blue-700 transition"
                                            data-id="{{ $importacao->id }}"
                                            data-notas="{{ $importacao->notas_count }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                        Validar
                                    </button>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Validado
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhuma importacao encontrada</h3>
                    <p class="mt-2 text-sm text-gray-500">Importe XMLs para comecar a validar.</p>
                    <a href="/app/importacao/xml" data-link class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Importar XMLs
                    </a>
                </div>
                @endif
            </div>

            {{-- Categorias de Validacao --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Categorias de Validacao</h3>
                    <p class="text-sm text-gray-500 mt-1">Regras aplicadas em cada validacao</p>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach($categorias ?? [] as $key => $categoria)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $categoria['nome'] }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $categoria['descricao'] }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $categoria['peso'] * 100 }}%
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Informacoes de Precificacao --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 vc-animate" style="animation-delay: 0.2s">
            <h4 class="font-semibold text-blue-900 mb-3">Sobre a Validacao</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm font-medium text-blue-800">Regras Locais</p>
                    <p class="text-xs text-blue-700 mt-1">Integridade de valores, CFOP/CST, NCM</p>
                    <p class="text-xs font-semibold text-green-700 mt-2">GRATIS</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-800">Validacao Completa</p>
                    <p class="text-xs text-blue-700 mt-1">Todas acima + cruzamento com RiskScore</p>
                    <p class="text-xs font-semibold text-blue-900 mt-2">1 credito/participante</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-800">Deep Analysis</p>
                    <p class="text-xs text-blue-700 mt-1">Completa + consultas InfoSimples (SINTEGRA, TCU)</p>
                    <p class="text-xs font-semibold text-blue-900 mt-2">3 creditos/participante</p>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

{{-- Modal de Confirmacao --}}
<div id="modal-validacao" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" id="modal-backdrop"></div>
        <div class="relative z-10 w-full max-w-md p-6 mx-auto bg-white rounded-xl shadow-xl">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirmar Validacao</h3>
            <div id="modal-content">
                <p class="text-sm text-gray-600">Carregando...</p>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="modal-cancelar" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="button" id="modal-confirmar" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/validacao.js') }}"></script>
