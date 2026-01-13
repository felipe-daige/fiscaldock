{{-- Histórico de Relatórios RAF --}}
<div class="min-h-screen bg-gray-50" id="raf-historico-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page title --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Histórico de Relatórios</h1>
                    <p class="mt-1 text-sm text-gray-600">Visualize e gerencie seus relatórios RAF.</p>
                </div>
                <a 
                    href="/app/raf" 
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                    data-link
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar para RAF
                </a>
            </div>
        </div>

        {{-- Sistema de Tabs --}}
        <div class="mb-6 flex gap-2 border-b border-gray-200">
            <a 
                href="?status=pendente" 
                class="px-4 py-3 text-sm font-semibold transition-colors {{ $status_atual === 'pendente' ? 'text-amber-600 border-b-2 border-amber-600' : 'text-gray-600 hover:text-gray-900' }}"
                data-link
            >
                Pendentes ({{ $total_pendentes }})
            </a>
            <a 
                href="?status=processado" 
                class="px-4 py-3 text-sm font-semibold transition-colors {{ $status_atual === 'processado' ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-600 hover:text-gray-900' }}"
                data-link
            >
                Processados ({{ $total_processados }})
            </a>
        </div>

        {{-- Lista de Relatórios --}}
        @if($relatorios->count() > 0)
            <div class="space-y-4">
                @foreach($relatorios as $relatorio)
                    @if($status_atual === 'pendente')
                        {{-- Card Pendente --}}
                        <div class="bg-white rounded-xl border border-amber-200 shadow-md hover:shadow-lg transition-shadow" data-relatorio-id="{{ $relatorio->id }}">
                            <div class="p-6">
                                {{-- Header --}}
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <p class="text-sm font-semibold text-gray-900">{{ $relatorio->tipo_efd }}</p>
                                        @if(strtolower($relatorio->tipo_consulta ?? '') === 'gratuito')
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
                                                Gratuita — Regime + Situação Cadastral
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-700">
                                                Completa — Regime + Situação Cadastral + CND
                                            </span>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-700">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Pendente
                                    </span>
                                </div>

                                {{-- Informações --}}
                                <div class="mb-4 space-y-2 text-sm text-gray-700">
                                    <div>
                                        <strong>{{ number_format($relatorio->qtd_participantes, 0, ',', '.') }}</strong> participantes identificados
                                    </div>
                                    <div>
                                        Valor da consulta: <strong>{{ number_format($relatorio->valor_total_consulta, 0, ',', '.') }} pontos</strong>
                                    </div>
                                </div>

                                {{-- Botões --}}
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <button
                                        type="button"
                                        class="raf-detalhes-btn inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                                        data-relatorio-id="{{ $relatorio->id }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Ver Detalhes
                                    </button>
                                    <button
                                        type="button"
                                        class="raf-confirmar-btn inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-semibold shadow-sm transition hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                        data-relatorio-id="{{ $relatorio->id }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="raf-confirmar-text">Confirmar e Pagar</span>
                                        <svg class="raf-confirmar-spinner hidden w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="raf-cancelar-btn inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-red-300 bg-white text-red-700 text-sm font-semibold shadow-sm transition hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        data-relatorio-id="{{ $relatorio->id }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        <span class="raf-cancelar-text">Cancelar</span>
                                        <svg class="raf-cancelar-spinner hidden w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Footer --}}
                                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                    <span>Aguardando confirmação</span>
                                    <span>Criado em: <strong class="text-gray-700">{{ $relatorio->created_at ? $relatorio->created_at->format('d/m/Y \à\s H:i') : 'N/A' }}</strong></span>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Card Processado --}}
                        <div class="bg-white rounded-xl border border-green-200 shadow-md hover:shadow-lg transition-shadow" data-relatorio-id="{{ $relatorio->id }}">
                            <div class="p-6">
                                {{-- Header --}}
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <p class="text-sm font-semibold text-gray-900">{{ $relatorio->document_type }}</p>
                                        @if(strtolower($relatorio->consultant_type ?? '') === 'gratuito')
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
                                                Gratuita — Regime + Situação Cadastral
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-700">
                                                Completa — Regime + Situação Cadastral + CND
                                            </span>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Processado
                                    </span>
                                </div>

                                {{-- Subtítulo: Razão Social, CNPJ e Período --}}
                                @if($relatorio->razao_social_empresa || $relatorio->cnpj_empresa_analisada || $relatorio->data_inicial_analisada || $relatorio->data_final_analisada)
                                    <div class="mb-4">
                                        @if($relatorio->razao_social_empresa)
                                            <p class="text-sm text-gray-700 font-medium">{{ $relatorio->razao_social_empresa }}</p>
                                        @endif
                                        @if($relatorio->cnpj_empresa_analisada)
                                            <p class="text-xs text-gray-500">CNPJ: {{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $relatorio->cnpj_empresa_analisada) }}</p>
                                        @endif
                                        @if($relatorio->data_inicial_analisada && $relatorio->data_final_analisada)
                                            <p class="text-xs text-gray-500 mt-1">
                                                <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                Período de Análise do SPED Informado: {{ $relatorio->data_inicial_analisada->format('d/m/Y') }} a {{ $relatorio->data_final_analisada->format('d/m/Y') }}
                                            </p>
                                        @elseif($relatorio->data_inicial_analisada)
                                            <p class="text-xs text-gray-500 mt-1">
                                                <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                A partir de: {{ $relatorio->data_inicial_analisada->format('d/m/Y') }}
                                            </p>
                                        @elseif($relatorio->data_final_analisada)
                                            <p class="text-xs text-gray-500 mt-1">
                                                <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                Até: {{ $relatorio->data_final_analisada->format('d/m/Y') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif

                                {{-- Analytics --}}
                                <div class="mb-4 space-y-2 text-sm text-gray-700">
                                    <div>
                                        <strong>{{ number_format($relatorio->total_participants ?? 0, 0, ',', '.') }}</strong> fornecedores identificados 
                                        ({{ number_format($relatorio->qnt_fornecedores_cnpj ?? 0, 0, ',', '.') }} CNPJs, 
                                        {{ number_format($relatorio->qnt_fornecedores_cpf ?? 0, 0, ',', '.') }} CPFs — apenas CNPJs são analisados)
                                    </div>
                                    <div>
                                        <strong>{{ number_format($relatorio->qnt_fornecedores_cnpj ?? 0, 0, ',', '.') }}</strong> fornecedores analisados 
                                        ({{ number_format($relatorio->qnt_fornecedores_cnpj ?? 0, 0, ',', '.') }} CNPJs)
                                    </div>
                                    <div>
                                        Situações Cadastral: <strong>{{ $relatorio->qnt_situacao_nula ?? 0 }}</strong> nulas · <strong>{{ $relatorio->qnt_situacao_ativa ?? 0 }}</strong> ativas · <strong>{{ $relatorio->qnt_situacao_suspensa ?? 0 }}</strong> suspensas · <strong>{{ $relatorio->qnt_situacao_inapta ?? 0 }}</strong> inaptas · <strong>{{ $relatorio->qnt_situacao_baixada ?? 0 }}</strong> baixadas
                                    </div>
                                    <div>
                                        Regimes Tributários: <strong>{{ $relatorio->qnt_simples ?? 0 }}</strong> Simples · <strong>{{ $relatorio->qnt_presumido ?? 0 }}</strong> Presumido · <strong>{{ $relatorio->qnt_real ?? 0 }}</strong> Real · <strong>{{ $relatorio->qnt_regime_indeterminado ?? 0 }}</strong> indeterminados
                                    </div>
                                    @php
                                        $consultantType = strtolower($relatorio->consultant_type ?? '');
                                        $isGratuito = $consultantType === 'gratuito';
                                        $isCompleto = in_array($consultantType, ['completo', 'completa'], true);
                                    @endphp
                                    @if($isGratuito)
                                        <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-sm font-medium text-amber-800">Gere um relatório completo para ver informações de CND</span>
                                        </div>
                                    @elseif($isCompleto)
                                        <div>
                                            Situação Fiscal: <strong>{{ $relatorio->qnt_cnd_regular ?? 0 }}</strong> Regular(es) · <strong>{{ $relatorio->qnt_cnd_pendencia ?? 0 }}</strong> Pendente(s)
                                        </div>
                                    @endif
                                </div>

                                {{-- Botões --}}
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <a
                                        href="/app/raf/baixar/{{ $relatorio->id }}"
                                        class="raf-baixar-btn inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold shadow-sm transition hover:bg-green-700"
                                        target="_blank"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Baixar
                                    </a>
                                    <button
                                        type="button"
                                        class="raf-excluir-btn inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-red-300 bg-white text-red-700 text-sm font-semibold shadow-sm transition hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        data-relatorio-id="{{ $relatorio->id }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        <span class="raf-excluir-text">Excluir</span>
                                        <svg class="raf-excluir-spinner hidden w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Footer --}}
                                <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between text-xs text-gray-500">
                                    <span>{{ number_format($relatorio->total_price ?? 0, 0, ',', '.') }} pontos</span>
                                    <span>Processado em: <strong class="text-gray-700">{{ $relatorio->processed_at ? $relatorio->processed_at->format('d/m/Y \à\s H:i') : ($relatorio->created_at ? $relatorio->created_at->format('d/m/Y \à\s H:i') : 'N/A') }}</strong></span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            {{-- Estado vazio --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">
                    @if($status_atual === 'pendente')
                        Nenhum relatório pendente
                    @else
                        Nenhum relatório processado
                    @endif
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    @if($status_atual === 'pendente')
                        Você não possui relatórios aguardando confirmação de pagamento.
                    @else
                        Você ainda não possui relatórios processados.
                    @endif
                </p>
                @if($status_atual === 'pendente')
                    <div class="mt-6">
                        <a 
                            href="/app/raf" 
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700"
                            data-link
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Criar Novo Relatório
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- Modal de Detalhes (Pendentes) --}}
<div id="raf-detalhes-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[80vh] overflow-y-auto">
        <div class="px-4 py-4 sm:p-5">
            <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Detalhes do Relatório</h3>
                <button type="button" class="raf-modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="raf-detalhes-content" class="space-y-4">
                {{-- Conteúdo será preenchido via JavaScript --}}
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button type="button" class="raf-modal-close w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-white hover:bg-gray-700 sm:ml-3 sm:w-auto sm:text-sm">
                Fechar
            </button>
        </div>
    </div>
</div>

{{-- Modal de Confirmação de Cancelamento --}}
<div id="raf-cancelar-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="px-4 py-4 sm:p-5">
            <div class="flex items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                    <h3 class="text-lg font-semibold text-gray-900" id="cancelar-modal-title">Confirmar Cancelamento</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">Tem certeza que deseja cancelar este relatório? Esta ação não pode ser desfeita.</p>
                    </div>
                </div>
                <button type="button" class="raf-cancelar-modal-close text-gray-400 hover:text-gray-500 ml-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button type="button" id="raf-cancelar-confirm-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                Confirmar
            </button>
            <button type="button" class="raf-cancelar-modal-close mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                Cancelar
            </button>
        </div>
    </div>
</div>

{{-- Modal de Relatório Expirado --}}
<div id="raf-expirado-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-3">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 flex items-center justify-center h-9 w-9 rounded-full bg-white/20">
                    <svg class="h-5 w-5 text-amber-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-amber-900">Tempo de Confirmação Expirado</h3>
            </div>
        </div>
        <div class="px-5 py-4">
            <p class="text-sm text-gray-700 mb-3">
                O prazo para confirmar este relatório expirou. Os relatórios pendentes têm um tempo limite para confirmação.
            </p>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                <div class="flex items-start gap-2">
                    <svg class="h-4 w-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-xs text-amber-800">
                        <strong>Não se preocupe!</strong> Basta enviar o arquivo SPED novamente para gerar um novo relatório. Seus créditos não foram descontados.
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-5 py-3 flex justify-end gap-2">
            <a 
                href="/app/raf" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-semibold shadow-sm transition hover:bg-amber-600"
                data-link
                id="raf-expirado-reenviar-btn"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Enviar Novo SPED
            </a>
            <button 
                type="button" 
                class="raf-expirado-modal-close px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
            >
                Entendi
            </button>
        </div>
    </div>
</div>

{{-- Modal de Confirmação de Exclusão --}}
<div id="raf-excluir-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full overflow-hidden">
        <div class="px-4 py-4 sm:p-5">
            <div class="flex items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar Exclusão</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">Tem certeza que deseja excluir este relatório? Esta ação não pode ser desfeita.</p>
                    </div>
                </div>
                <button type="button" class="raf-excluir-modal-close text-gray-400 hover:text-gray-500 ml-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button type="button" id="raf-excluir-confirm-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                Excluir
            </button>
            <button type="button" class="raf-excluir-modal-close mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                Cancelar
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    // Função principal de inicialização - exposta globalmente para o SPA
    function initRafHistorico() {
        const container = document.getElementById('raf-historico-container');
        if (!container) {
            console.warn('[RAF Histórico] Container não encontrado');
            return;
        }

        // Prevenir inicialização dupla
        if (container.dataset.initialized === '1') {
            console.log('[RAF Histórico] Já inicializado, ignorando');
            return;
        }
        container.dataset.initialized = '1';

        console.log('[RAF Histórico] Inicializando...');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Estado do modal de cancelamento
        let pendingCancelRelatorioId = null;

        // Estado do modal de exclusão
        let pendingExcluirRelatorioId = null;

        // Função auxiliar para remover card com animação
        function removerCard(relatorioId) {
            const card = document.querySelector(`div.bg-white.rounded-xl[data-relatorio-id="${relatorioId}"]`);

            if (!card || !card.parentNode) {
                console.warn('[RAF Histórico] Card não encontrado para remoção:', relatorioId);
                return;
            }

            // Aplicar animação de fade out
            card.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
            card.style.opacity = '0';
            card.style.transform = 'translateY(-10px)';

            // Remover do DOM após animação
            setTimeout(function() {
                if (card && card.parentNode) {
                    card.remove();
                }

                // Contar cards restantes
                const cardsRestantes = document.querySelectorAll('div.bg-white.rounded-xl[data-relatorio-id]');

                if (cardsRestantes.length === 0) {
                    window.location.reload();
                }

                // Atualizar badge de pendentes na página principal RAF
                if (typeof window.updatePendentesBadge === 'function') {
                    window.updatePendentesBadge();
                }
            }, 300);
        }

        // Modal de detalhes
        const detalhesModal = document.getElementById('raf-detalhes-modal');
        const detalhesContent = document.getElementById('raf-detalhes-content');

        function openDetalhesModal() {
            if (detalhesModal) {
                detalhesModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeDetalhesModal() {
            if (detalhesModal) {
                detalhesModal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        // Modal de cancelamento
        const cancelarModal = document.getElementById('raf-cancelar-modal');

        function openCancelarModal(relatorioId) {
            pendingCancelRelatorioId = relatorioId;
            if (cancelarModal) {
                cancelarModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeCancelarModal() {
            if (cancelarModal) {
                cancelarModal.classList.add('hidden');
                document.body.style.overflow = '';
            }
            pendingCancelRelatorioId = null;
        }

        // Modal de expiração
        const expiradoModal = document.getElementById('raf-expirado-modal');
        let pendingExpiredRelatorioId = null;

        function showExpiredModal(relatorioId) {
            pendingExpiredRelatorioId = relatorioId;
            if (expiradoModal) {
                expiradoModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeExpiredModal() {
            if (expiradoModal) {
                expiradoModal.classList.add('hidden');
                document.body.style.overflow = '';
            }
            // Remover o card após fechar o modal
            if (pendingExpiredRelatorioId) {
                removerCard(pendingExpiredRelatorioId);
                pendingExpiredRelatorioId = null;
            }
        }

        // Event listeners para fechar modal de expiração
        document.querySelectorAll('.raf-expirado-modal-close').forEach(function(btn) {
            btn.addEventListener('click', closeExpiredModal);
        });

        if (expiradoModal) {
            expiradoModal.addEventListener('click', function(e) {
                if (e.target === expiradoModal) {
                    closeExpiredModal();
                }
            });
        }

        // Botão "Enviar Novo SPED" também fecha o modal e remove o card
        const reenviarBtn = document.getElementById('raf-expirado-reenviar-btn');
        if (reenviarBtn) {
            reenviarBtn.addEventListener('click', function() {
                // Remover o card antes de navegar
                if (pendingExpiredRelatorioId) {
                    removerCard(pendingExpiredRelatorioId);
                    pendingExpiredRelatorioId = null;
                }
                if (expiradoModal) {
                    expiradoModal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        }

        // Modal de exclusão
        const excluirModal = document.getElementById('raf-excluir-modal');

        function openExcluirModal(relatorioId) {
            pendingExcluirRelatorioId = relatorioId;
            if (excluirModal) {
                excluirModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeExcluirModal() {
            if (excluirModal) {
                excluirModal.classList.add('hidden');
                document.body.style.overflow = '';
            }
            pendingExcluirRelatorioId = null;
        }

        // Event listeners para fechar modal de exclusão
        document.querySelectorAll('.raf-excluir-modal-close').forEach(function(btn) {
            btn.addEventListener('click', closeExcluirModal);
        });

        if (excluirModal) {
            excluirModal.addEventListener('click', function(e) {
                if (e.target === excluirModal) {
                    closeExcluirModal();
                }
            });
        }

        // Event listeners para fechar modais de detalhes
        document.querySelectorAll('.raf-modal-close').forEach(function(btn) {
            btn.addEventListener('click', closeDetalhesModal);
        });

        if (detalhesModal) {
            detalhesModal.addEventListener('click', function(e) {
                if (e.target === detalhesModal) {
                    closeDetalhesModal();
                }
            });
        }

        // Event listeners para fechar modal de cancelamento
        document.querySelectorAll('.raf-cancelar-modal-close').forEach(function(btn) {
            btn.addEventListener('click', closeCancelarModal);
        });

        if (cancelarModal) {
            cancelarModal.addEventListener('click', function(e) {
                if (e.target === cancelarModal) {
                    closeCancelarModal();
                }
            });
        }

        // Botão Ver Detalhes (Pendentes)
        document.querySelectorAll('.raf-detalhes-btn').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const relatorioId = this.dataset.relatorioId;
                console.log('[RAF Histórico] Ver detalhes:', relatorioId);
                
                try {
                    const response = await fetch('/app/raf/detalhes/' + relatorioId, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('Erro ao buscar detalhes: ' + response.status);
                    }

                    const result = await response.json();
                    if (!result.success || !result.data) {
                        throw new Error('Dados não encontrados');
                    }

                    const data = result.data;
                    // Usar consultant_type quando disponível (relatórios processados), fallback para tipo_consulta (relatórios pendentes)
                    const consultantType = (data.consultant_type || data.tipo_consulta || '').toLowerCase();
                    const isGratuito = consultantType === 'gratuito';
                    const tipoConsultaLabel = isGratuito
                        ? 'Gratuita — Regime + Situação Cadastral' 
                        : 'Completa — Regime + Situação Cadastral + CND';

                    const custoUnitario = Number(data.custo_unitario || 0);
                    const valorTotal = Number(data.valor_total_consulta || 0);
                    const qtdParticipantes = Number(data.qtd_participantes || 0);

                    const html = '<div class="space-y-3">' +
                        '<div class="grid grid-cols-2 gap-4">' +
                            '<div>' +
                                '<p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipo de EFD</p>' +
                                '<p class="text-sm font-semibold text-gray-900 mt-1">' + (data.tipo_efd || 'N/A') + '</p>' +
                            '</div>' +
                            '<div>' +
                                '<p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipo de Consulta</p>' +
                                '<p class="text-sm font-semibold text-gray-900 mt-1">' + tipoConsultaLabel + '</p>' +
                            '</div>' +
                            '<div>' +
                                '<p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Quantidade de Participantes</p>' +
                                '<p class="text-sm font-semibold text-gray-900 mt-1">' + qtdParticipantes.toLocaleString('pt-BR') + '</p>' +
                            '</div>' +
                            '<div>' +
                                '<p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Custo Unitário</p>' +
                                '<p class="text-sm font-semibold text-gray-900 mt-1">' + custoUnitario.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' pontos</p>' +
                            '</div>' +
                            '<div class="col-span-2">' +
                                '<p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Valor Total da Consulta</p>' +
                                '<p class="text-lg font-bold text-amber-600 mt-1">' + valorTotal.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' pontos</p>' +
                            '</div>' +
                            '<div class="col-span-2">' +
                                '<p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Data e Horário de Criação</p>' +
                                '<p class="text-sm text-gray-700 mt-1">' + (data.created_at ? new Date(data.created_at).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A') + '</p>' +
                            '</div>' +
                        '</div>' +
                    '</div>';

                    if (detalhesContent) {
                        detalhesContent.innerHTML = html;
                    }
                    openDetalhesModal();
                } catch (err) {
                    console.error('[RAF Histórico] Erro ao buscar detalhes:', err);
                    alert('Erro ao buscar detalhes do relatório. Tente novamente.');
                }
            });
        });

        // Botão Confirmar e Pagar
        document.querySelectorAll('.raf-confirmar-btn').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const relatorioId = this.dataset.relatorioId;
                const confirmarText = this.querySelector('.raf-confirmar-text');
                const confirmarSpinner = this.querySelector('.raf-confirmar-spinner');
                const buttonRef = this;

                console.log('[RAF Histórico] Confirmar relatório:', relatorioId);

                buttonRef.disabled = true;
                if (confirmarText) confirmarText.classList.add('hidden');
                if (confirmarSpinner) confirmarSpinner.classList.remove('hidden');

                try {
                    const response = await fetch('/app/raf/confirmar/' + relatorioId, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json, text/csv',
                        },
                        credentials: 'same-origin',
                    });

                    const contentType = response.headers.get('content-type');

                    if (response.status === 402) {
                        const data = await response.json();
                        alert('Créditos insuficientes: ' + (data.message || 'Saldo insuficiente para esta operação.'));
                        buttonRef.disabled = false;
                        if (confirmarText) confirmarText.classList.remove('hidden');
                        if (confirmarSpinner) confirmarSpinner.classList.add('hidden');
                        return;
                    }

                    // Verificar se é erro de expiração (410 Gone)
                    if (response.status === 410) {
                        const data = await response.json().catch(function() { return {}; });
                        // Mostrar modal de expiração
                        showExpiredModal(relatorioId);
                        return;
                    }

                    if (!response.ok) {
                        const data = await response.json().catch(function() { return {}; });
                        alert('Erro ao confirmar: ' + (data.message || 'Erro ' + response.status));
                        buttonRef.disabled = false;
                        if (confirmarText) confirmarText.classList.remove('hidden');
                        if (confirmarSpinner) confirmarSpinner.classList.add('hidden');
                        return;
                    }

                    // Se a resposta é CSV, fazer download
                    if (contentType && contentType.includes('text/csv')) {
                        const blob = await response.blob();
                        const disposition = response.headers.get('content-disposition');
                        let filename = 'resultado.csv';
                        const match = disposition && disposition.match(/filename="?([^";]+)"?/i);
                        if (match && match[1]) {
                            filename = match[1];
                        }

                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);

                        // Remover card da lista
                        removerCard(relatorioId);
                    } else {
                        alert('Resposta inesperada do servidor. Tente novamente.');
                        buttonRef.disabled = false;
                        if (confirmarText) confirmarText.classList.remove('hidden');
                        if (confirmarSpinner) confirmarSpinner.classList.add('hidden');
                    }
                } catch (err) {
                    console.error('[RAF Histórico] Erro ao confirmar:', err);
                    alert('Erro ao confirmar relatório. Verifique sua conexão e tente novamente.');
                    buttonRef.disabled = false;
                    if (confirmarText) confirmarText.classList.remove('hidden');
                    if (confirmarSpinner) confirmarSpinner.classList.add('hidden');
                }
            });
        });

        // Botão Cancelar - abre modal de confirmação
        document.querySelectorAll('.raf-cancelar-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const relatorioId = this.dataset.relatorioId;
                console.log('[RAF Histórico] Abrir modal cancelar:', relatorioId);
                if (!relatorioId) {
                    console.error('[RAF Histórico] Relatório ID não encontrado');
                    return;
                }
                openCancelarModal(relatorioId);
            });
        });

        // Botão Confirmar do modal de cancelamento
        const cancelarConfirmBtn = document.getElementById('raf-cancelar-confirm-btn');
        if (cancelarConfirmBtn) {
            cancelarConfirmBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (!pendingCancelRelatorioId) {
                    console.error('[RAF Histórico] ID de relatório para cancelar não encontrado');
                    return;
                }

                const relatorioId = pendingCancelRelatorioId;
                const btn = document.querySelector('.raf-cancelar-btn[data-relatorio-id="' + relatorioId + '"]');
                const cancelarText = btn ? btn.querySelector('.raf-cancelar-text') : null;
                const cancelarSpinner = btn ? btn.querySelector('.raf-cancelar-spinner') : null;

                console.log('[RAF Histórico] Confirmar cancelamento:', relatorioId);

                // Fecha o modal imediatamente
                closeCancelarModal();

                // Desabilita o botão e mostra spinner
                if (btn) {
                    btn.disabled = true;
                    if (cancelarText) cancelarText.classList.add('hidden');
                    if (cancelarSpinner) cancelarSpinner.classList.remove('hidden');
                }

                // Aguardar resposta do servidor antes de remover o card
                try {
                    const response = await fetch('/app/raf/cancelar/' + relatorioId, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Só remove o card se o servidor confirmar sucesso
                        removerCard(relatorioId);
                    } else {
                        // Se falhar, reabilita o botão e mostra erro
                        if (btn) {
                            btn.disabled = false;
                            if (cancelarText) cancelarText.classList.remove('hidden');
                            if (cancelarSpinner) cancelarSpinner.classList.add('hidden');
                        }
                        alert('Erro ao cancelar relatório: ' + (data.message || 'Erro desconhecido. Tente novamente.'));
                    }
                } catch (err) {
                    // Se houver erro de rede, reabilita o botão
                    if (btn) {
                        btn.disabled = false;
                        if (cancelarText) cancelarText.classList.remove('hidden');
                        if (cancelarSpinner) cancelarSpinner.classList.add('hidden');
                    }
                    console.error('[RAF Histórico] Erro ao cancelar:', err);
                    alert('Erro ao cancelar relatório. Verifique sua conexão e tente novamente.');
                }
            });
        }

        // Botão Excluir - abre modal de confirmação
        document.querySelectorAll('.raf-excluir-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const relatorioId = this.dataset.relatorioId;
                console.log('[RAF Histórico] Abrir modal excluir:', relatorioId);
                if (!relatorioId) {
                    console.error('[RAF Histórico] Relatório ID não encontrado');
                    return;
                }
                openExcluirModal(relatorioId);
            });
        });

        // Botão Confirmar do modal de exclusão
        const excluirConfirmBtn = document.getElementById('raf-excluir-confirm-btn');
        if (excluirConfirmBtn) {
            excluirConfirmBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (!pendingExcluirRelatorioId) {
                    console.error('[RAF Histórico] ID de relatório para excluir não encontrado');
                    return;
                }

                const relatorioId = pendingExcluirRelatorioId;
                const btn = document.querySelector('.raf-excluir-btn[data-relatorio-id="' + relatorioId + '"]');
                const excluirText = btn ? btn.querySelector('.raf-excluir-text') : null;
                const excluirSpinner = btn ? btn.querySelector('.raf-excluir-spinner') : null;

                console.log('[RAF Histórico] Confirmar exclusão:', relatorioId);

                // Fecha o modal imediatamente
                closeExcluirModal();

                // Desabilita o botão e mostra spinner
                if (btn) {
                    btn.disabled = true;
                    if (excluirText) excluirText.classList.add('hidden');
                    if (excluirSpinner) excluirSpinner.classList.remove('hidden');
                }

                // Aguardar resposta do servidor antes de remover o card
                try {
                    const response = await fetch('/app/raf/excluir/' + relatorioId, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Só remove o card se o servidor confirmar sucesso
                        removerCard(relatorioId);
                    } else {
                        // Se falhar, reabilita o botão e mostra erro
                        if (btn) {
                            btn.disabled = false;
                            if (excluirText) excluirText.classList.remove('hidden');
                            if (excluirSpinner) excluirSpinner.classList.add('hidden');
                        }
                        alert('Erro ao excluir relatório: ' + (data.message || 'Erro desconhecido. Tente novamente.'));
                    }
                } catch (err) {
                    // Se houver erro de rede, reabilita o botão
                    if (btn) {
                        btn.disabled = false;
                        if (excluirText) excluirText.classList.remove('hidden');
                        if (excluirSpinner) excluirSpinner.classList.add('hidden');
                    }
                    console.error('[RAF Histórico] Erro ao excluir:', err);
                    alert('Erro ao excluir relatório. Verifique sua conexão e tente novamente.');
                }
            });
        }

        console.log('[RAF Histórico] Inicialização concluída');
    }

    // Expor função globalmente para o SPA
    window.initRafHistorico = initRafHistorico;

    // Auto-inicializar na primeira carga
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRafHistorico, { once: true });
    } else {
        // DOM já está pronto, inicializar imediatamente
        initRafHistorico();
    }
})();
</script>
