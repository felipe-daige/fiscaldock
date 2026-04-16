@php
    $estatisticas = $estatisticas ?? [];
    $importacoes = $importacoes ?? collect();
    $notasCriticas = $notasCriticas ?? collect();
    $categorias = $categorias ?? [];
    $escopoNotas = $escopoNotas ?? [];

    $classificacaoResumo = [
        'conforme' => ['label' => 'Conformes', 'valor' => $estatisticas['conforme'] ?? 0, 'hex' => '#047857'],
        'atencao' => ['label' => 'Atenção', 'valor' => $estatisticas['atencao'] ?? 0, 'hex' => '#d97706'],
        'irregular' => ['label' => 'Irregulares', 'valor' => $estatisticas['irregular'] ?? 0, 'hex' => '#b45309'],
        'critico' => ['label' => 'Críticas', 'valor' => $estatisticas['critico'] ?? 0, 'hex' => '#dc2626'],
    ];
@endphp

<div class="min-h-screen bg-gray-100" id="validacao-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-8 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Validação Fiscal</h1>
                <p class="text-xs text-gray-500 mt-1">Painel operacional para revisão de notas XML validadas, alertas e importações XML.</p>
            </div>
            <a href="/app/validacao/alertas" data-link class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded text-sm font-medium self-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Ver alertas
            </a>
        </div>

        <div id="validacao-error-region" class="mb-6"></div>

        <div class="bg-white rounded border border-gray-300 p-4 mb-6 border-l-4 border-l-blue-500">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Escopo da Tela</p>
            <p class="mt-2 text-sm text-gray-700">A Validação Fiscal trabalha apenas com notas importadas via XML. Notas vindas de EFD/SPED aparecem nas telas de notas fiscais e BI, mas não entram nesta listagem.</p>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-gray-50 border border-gray-200 rounded p-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Notas XML</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($escopoNotas['total_xml'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">Disponíveis para validação</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded p-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Notas EFD</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($escopoNotas['total_efd'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">Fora do escopo desta tela</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded p-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Base Unificada</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($escopoNotas['total_unificado'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">Notas vistas em telas unificadas</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo Operacional</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-6 divide-x divide-y lg:divide-y-0 divide-gray-200">
                <div class="p-4 sm:p-6 hover:bg-gray-50/50 transition-colors">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Notas</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($estatisticas['total_notas'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">Base XML disponível para análise</p>
                </div>
                <div class="p-4 sm:p-6 hover:bg-gray-50/50 transition-colors">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Validadas</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($estatisticas['total_validadas'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">{{ $estatisticas['percentual_validado'] ?? 0 }}% do volume processado</p>
                </div>
                @foreach($classificacaoResumo as $item)
                    <div class="p-4 sm:p-6 hover:bg-gray-50/50 transition-colors">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ $item['label'] }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ number_format($item['valor'], 0, ',', '.') }}</p>
                        <p class="text-[11px] mt-1">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $item['hex'] }}">{{ $item['label'] }}</span>
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        @if($notasCriticas->count() > 0)
            <div class="bg-white rounded border border-gray-300 p-4 mb-6 border-l-4 border-l-red-500">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Alertas Bloqueantes</p>
                <p class="mt-2 text-sm text-gray-700">As notas abaixo exigem revisão imediata antes de qualquer decisão operacional.</p>
                <div class="mt-4 divide-y divide-gray-100">
                    @foreach($notasCriticas->take(5) as $nota)
                        <div class="py-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <a href="/app/validacao/nota/{{ $nota->id }}" data-link class="text-sm text-gray-900 hover:text-gray-600 hover:underline">
                                    NF {{ $nota->numero_nota }} - {{ $nota->emitente->razao_social ?? $nota->emit_cnpj }}
                                </a>
                                <p class="text-[11px] text-gray-500 mt-1">{{ count($nota->validacao_alertas) }} alerta(s) registrados</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white self-start" style="background-color: #dc2626">
                                Bloqueante
                            </span>
                        </div>
                    @endforeach
                </div>
                @if($notasCriticas->count() > 5)
                    <a href="/app/validacao/alertas?nivel=bloqueante" data-link class="mt-3 inline-flex text-xs text-gray-600 hover:text-gray-900 hover:underline">
                        Ver lista completa de alertas bloqueantes
                    </a>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Importações Recentes</span>
                    <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $importacoes->count() }}</span>
                </div>

                @if($importacoes->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($importacoes as $importacao)
                            <div class="px-4 py-3 hover:bg-gray-50/50 transition-colors">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-sm text-gray-700">{{ $importacao->tipo_documento ?? 'XML' }} - {{ $importacao->created_at->format('d/m/Y H:i') }}</p>
                                        <p class="text-[11px] text-gray-500 mt-1">
                                            {{ $importacao->notas_count ?? 0 }} nota(s) | {{ $importacao->notas_validadas_count ?? 0 }} validada(s)
                                        </p>
                                    </div>
                                    @if(($importacao->notas_validadas_count ?? 0) < ($importacao->notas_count ?? 0))
                                        <button
                                            type="button"
                                            class="btn-validar-importacao px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium self-start"
                                            data-id="{{ $importacao->id }}"
                                            data-notas="{{ $importacao->notas_count }}"
                                        >
                                            Validar importação
                                        </button>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white self-start" style="background-color: #047857">
                                            Validado
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-4 py-8 text-center">
                        <p class="text-sm text-gray-700">Nenhuma importação XML concluída para validar.</p>
                        <a href="/app/importacao/xml" data-link class="mt-2 inline-flex text-xs text-gray-600 hover:text-gray-900 hover:underline">
                            Ir para importação de XML
                        </a>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Categorias de Validação</span>
                    <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ count($categorias) }}</span>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($categorias as $categoria)
                        <div class="px-4 py-3 flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm text-gray-900">{{ $categoria['nome'] }}</p>
                                <p class="text-[11px] text-gray-500 mt-1">{{ $categoria['descricao'] }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white whitespace-nowrap" style="background-color: #374151">
                                {{ (int) ($categoria['peso'] * 100) }}%
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white rounded border border-gray-300 p-4 border-l-4 border-l-blue-500">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Camadas de Validação</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3 text-sm text-gray-700">
                <div>
                    <p class="font-semibold text-gray-900">Regras locais</p>
                    <p class="text-[11px] text-gray-500 mt-1">Integridade de valores, CFOP/CST e consistência básica.</p>
                    <span class="inline-flex mt-2 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #047857">Grátis</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Validação completa</p>
                    <p class="text-[11px] text-gray-500 mt-1">Regras locais com cruzamentos adicionais por participante.</p>
                    <span class="inline-flex mt-2 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">1 crédito/participante</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Deep analysis</p>
                    <p class="text-[11px] text-gray-500 mt-1">Camada ampliada com consultas externas e análise aprofundada.</p>
                    <span class="inline-flex mt-2 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">3 créditos/participante</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-validacao" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-gray-900/40" id="modal-backdrop"></div>
        <div class="relative z-10 w-full max-w-md bg-white border border-gray-300 rounded shadow-xl overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <h3 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Confirmar Validação</h3>
            </div>
            <div id="modal-content" class="p-4 text-sm text-gray-700">
                <p>Carregando...</p>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-2 bg-white">
                <button type="button" id="modal-cancelar" class="px-4 py-2 bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded text-sm font-medium">
                    Cancelar
                </button>
                <button type="button" id="modal-confirmar" class="px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/validacao.js') }}"></script>
