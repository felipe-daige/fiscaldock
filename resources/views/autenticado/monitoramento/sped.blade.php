{{-- Monitoramento - Importar do SPED --}}
<div class="min-h-screen bg-gray-50" id="monitoramento-sped-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Importar Participantes do SPED</h1>
                    <p class="mt-1 text-sm text-gray-600">Selecione um relatorio RAF processado para importar os participantes.</p>
                </div>
                <a
                    href="/app/monitoramento"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                    data-link
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
            </div>
        </div>

        {{-- Info Box --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-blue-900">Como funciona?</h3>
                    <p class="text-sm text-blue-800 mt-1">
                        Os participantes (fornecedores e clientes) identificados nos seus relatorios RAF podem ser importados para monitoramento continuo.
                        Voce pode escolher importar todos ou selecionar individualmente quais CNPJs deseja monitorar.
                    </p>
                </div>
            </div>
        </div>

        {{-- Seção Importar de Arquivo .txt --}}
        <div class="mb-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Importar Participantes de Arquivo .txt</h2>
                <p class="text-sm text-gray-600 mt-1">Envie um arquivo .txt contendo CNPJs para importar participantes diretamente.</p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Card Upload (Esquerdo) --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">Enviar Arquivo</h3>
                    </div>
                    <div class="p-6">
                        {{-- Seleção Tipo SPED --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de SPED:</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-start p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 tipo-sped-label" data-tipo="efd-fiscal">
                                    <input type="radio" name="tipo-sped" value="efd-fiscal" class="mt-1 mr-3 w-4 h-4 text-blue-600 flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-800 text-sm">EFD Fiscal</div>
                                        <div class="text-xs text-gray-600">ICMS/IPI</div>
                                    </div>
                                </label>
                                <label class="flex items-start p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 tipo-sped-label" data-tipo="efd-contrib">
                                    <input type="radio" name="tipo-sped" value="efd-contrib" class="mt-1 mr-3 w-4 h-4 text-blue-600 flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-800 text-sm">EFD Contribuições</div>
                                        <div class="text-xs text-gray-600">PIS/COFINS</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Instruções --}}
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start gap-2 mb-3">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h4 class="text-sm font-semibold text-blue-900">Instruções</h4>
                            </div>
                            <div class="space-y-2 text-xs text-blue-800">
                                <div>
                                    <strong class="text-blue-900">Formato do arquivo:</strong> Arquivo .txt com CNPJs, um por linha ou separados por vírgula/ponto e vírgula.
                                </div>
                                <div>
                                    <strong class="text-blue-900">Exemplo:</strong>
                                    <div class="mt-1 p-2 bg-white rounded border border-blue-200 font-mono text-xs">
                                        12345678000190<br>
                                        98765432000111<br>
                                        11223344000155
                                    </div>
                                </div>
                                <div>
                                    <strong class="text-blue-900">Processo:</strong> Após importar, os CNPJs serão adicionados à sua lista de participantes para monitoramento contínuo.
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div id="txt-dropzone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 min-h-[180px] flex flex-col items-center justify-center transition-colors cursor-not-allowed bg-gray-100 opacity-60 pointer-events-none" role="button" tabindex="0" aria-disabled="true">
                                <div class="mb-4">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <div class="space-y-1 text-center">
                                    <p class="text-sm font-medium text-gray-500" id="txt-dropzone-main-text">Selecione o tipo de SPED primeiro</p>
                                    <p class="text-xs text-gray-400" id="txt-dropzone-sub-text">Depois arraste o arquivo .txt aqui ou clique para selecionar</p>
                                    <p class="text-xs text-gray-400 mt-2">.txt | Máximo: 10MB</p>
                                </div>
                                <input
                                    type="file"
                                    id="txt-file-input"
                                    name="txt_file"
                                    accept=".txt"
                                    class="hidden"
                                    disabled
                                >
                            </div>
                        </div>

                        <div id="txt-file-meta" class="mb-4 hidden">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <div>
                                        <div class="text-xs font-medium text-gray-800" id="txt-file-name">arquivo.txt</div>
                                        <div class="text-xs text-gray-500" id="txt-file-size">0 MB</div>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    id="txt-change-file"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div id="txt-error-message" class="mb-4 hidden">
                            <div class="flex items-start gap-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <svg class="w-4 h-4 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-xs text-red-800" id="txt-error-text"></p>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="button"
                                id="txt-importar-btn"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled
                                title="Funcionalidade em desenvolvimento"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Importar
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Card Informações (Direito) --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="text-base font-semibold text-gray-900">Sobre o Monitoramento</h3>
                        </div>
                    </div>
                <div class="p-6 space-y-6">
                    {{-- Seção Planos Disponíveis --}}
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Planos Disponíveis</h4>
                        <div class="space-y-2">
                            <div class="p-3 rounded-lg border border-gray-200 bg-gray-50">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-900">Básico</span>
                                    <span class="text-xs font-medium text-green-600">Grátis</span>
                                </div>
                                <p class="text-xs text-gray-600">Situação + Simples Nacional</p>
                            </div>
                            <div class="p-3 rounded-lg border border-gray-200 bg-white">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-900">Cadastral+</span>
                                    <span class="text-xs font-medium text-blue-600">3 créditos</span>
                                </div>
                                <p class="text-xs text-gray-600">CNPJ + SINTEGRA + IE</p>
                            </div>
                            <div class="p-3 rounded-lg border border-gray-200 bg-white">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-900">Fiscal Federal</span>
                                    <span class="text-xs font-medium text-blue-600">6 créditos</span>
                                </div>
                                <p class="text-xs text-gray-600">CND Federal + FGTS</p>
                            </div>
                            <div class="p-3 rounded-lg border border-gray-200 bg-white">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-900">Fiscal Completo</span>
                                    <span class="text-xs font-medium text-blue-600">12 créditos</span>
                                </div>
                                <p class="text-xs text-gray-600">Federal + Estadual + CNDT</p>
                            </div>
                            <div class="p-3 rounded-lg border border-gray-200 bg-white">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-900">Due Diligence</span>
                                    <span class="text-xs font-medium text-purple-600">18 créditos</span>
                                </div>
                                <p class="text-xs text-gray-600">Completo + Protestos + Processos</p>
                            </div>
                        </div>
                    </div>

                    {{-- Seção Como Funciona --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Como Funciona</h4>
                        <div class="space-y-3">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <p class="text-xs text-gray-600">
                                    <strong class="text-gray-900">Importação:</strong> Envie um arquivo .txt com CNPJs ou importe de relatórios RAF processados.
                                </p>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <p class="text-xs text-gray-600">
                                    <strong class="text-gray-900">Monitoramento:</strong> Os participantes são monitorados continuamente conforme o plano escolhido.
                                </p>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <p class="text-xs text-gray-600">
                                    <strong class="text-gray-900">Consultas:</strong> Realize consultas avulsas ou configure assinaturas mensais para monitoramento automático.
                                </p>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <p class="text-xs text-gray-600">
                                    <strong class="text-gray-900">Histórico:</strong> Acompanhe todas as consultas realizadas e resultados obtidos.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de Relatorios RAF --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Relatorios RAF Processados</h2>
            </div>

            @if(isset($relatorios) && $relatorios->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($relatorios as $relatorio)
                        <div class="p-6 hover:bg-gray-50 transition-colors" data-relatorio-id="{{ $relatorio->id }}">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                {{-- Info do Relatorio --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                            {{ $relatorio->document_type }}
                                        </span>
                                        @if(strtolower($relatorio->consultant_type ?? '') === 'gratuito')
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
                                                Gratuita
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-700">
                                                Completa
                                            </span>
                                        @endif
                                    </div>

                                    @if($relatorio->razao_social_empresa)
                                        <h3 class="text-base font-semibold text-gray-900 truncate">{{ $relatorio->razao_social_empresa }}</h3>
                                    @endif

                                    @if($relatorio->cnpj_empresa_analisada)
                                        <p class="text-sm text-gray-600">
                                            CNPJ: {{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $relatorio->cnpj_empresa_analisada) }}
                                        </p>
                                    @endif

                                    <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <strong>{{ number_format($relatorio->qnt_fornecedores_cnpj ?? 0, 0, ',', '.') }}</strong> CNPJs identificados
                                        </span>
                                        @if($relatorio->data_inicial_analisada && $relatorio->data_final_analisada)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $relatorio->data_inicial_analisada->format('d/m/Y') }} - {{ $relatorio->data_final_analisada->format('d/m/Y') }}
                                            </span>
                                        @endif
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $relatorio->processed_at ? $relatorio->processed_at->format('d/m/Y H:i') : $relatorio->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Acoes --}}
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="btn-ver-participantes inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                                        data-relatorio-id="{{ $relatorio->id }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Ver Participantes
                                    </button>
                                    <button
                                        type="button"
                                        class="btn-importar-todos inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700"
                                        data-relatorio-id="{{ $relatorio->id }}"
                                        data-total-cnpjs="{{ $relatorio->qnt_fornecedores_cnpj ?? 0 }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        Importar Todos
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if(method_exists($relatorios, 'hasPages') && $relatorios->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $relatorios->links() }}
                    </div>
                @endif
            @else
                {{-- Estado vazio --}}
                <div class="p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum relatorio RAF processado</h3>
                    <p class="text-sm text-gray-600 mb-4">Voce precisa processar um arquivo SPED no RAF antes de importar participantes.</p>
                    <a
                        href="/app/raf"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700"
                        data-link
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Criar Relatorio RAF
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Ver Participantes --}}
<div id="modal-ver-participantes" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Participantes do Relatorio</h3>
                    <p class="text-sm text-gray-600 mt-1" id="modal-subtitulo">Selecione os CNPJs que deseja importar para monitoramento.</p>
                </div>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Busca e selecao --}}
        <div class="px-6 py-3 border-b border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="relative flex-1 max-w-sm">
                    <input
                        type="text"
                        id="busca-participante-modal"
                        placeholder="Buscar por CNPJ ou razao social..."
                        class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" id="modal-select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Selecionar todos
                    </label>
                    <span class="text-sm text-gray-500">(<span id="modal-count-selecionados">0</span> selecionados)</span>
                </div>
            </div>
        </div>

        {{-- Lista de participantes --}}
        <div class="flex-1 overflow-y-auto px-6 py-4" id="modal-participantes-lista">
            <div class="flex items-center justify-center py-8">
                <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-3 text-gray-600">Carregando participantes...</span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    <span id="modal-total-participantes">0</span> participantes no total
                </p>
                <div class="flex items-center gap-3">
                    <button type="button" class="modal-close px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="button" id="btn-importar-selecionados" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Importar Selecionados
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Confirmar Importacao --}}
<div id="modal-confirmar-importacao" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Confirmar Importacao</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="px-6 py-4">
            <div class="flex items-center gap-4 mb-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-base font-semibold text-gray-900">Importar <span id="confirmar-count">0</span> participantes?</p>
                    <p class="text-sm text-gray-600">Os CNPJs serao adicionados a sua lista de monitoramento.</p>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-xs text-amber-800">
                        CNPJs duplicados serao ignorados. Apenas CNPJs que voce ainda nao possui serao importados.
                    </p>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
            <button type="button" class="modal-close px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                Cancelar
            </button>
            <button type="button" id="btn-confirmar-importacao" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                <span class="btn-text">Confirmar Importacao</span>
                <svg class="btn-spinner hidden w-4 h-4 animate-spin" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    function initMonitoramentoSped() {
        const container = document.getElementById('monitoramento-sped-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Monitoramento SPED] Inicializando...');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const modalVerParticipantes = document.getElementById('modal-ver-participantes');
        const modalConfirmarImportacao = document.getElementById('modal-confirmar-importacao');
        const participantesLista = document.getElementById('modal-participantes-lista');
        const btnImportarSelecionados = document.getElementById('btn-importar-selecionados');
        const modalSelectAll = document.getElementById('modal-select-all');
        const countSelecionados = document.getElementById('modal-count-selecionados');
        const buscaModal = document.getElementById('busca-participante-modal');

        let relatorioAtual = null;
        let participantesData = [];
        let cnpjsSelecionados = [];

        // Funcao para atualizar contagem de selecionados
        function atualizarContagem() {
            const checkboxes = participantesLista.querySelectorAll('.participante-check:checked');
            cnpjsSelecionados = Array.from(checkboxes).map(cb => cb.value);
            countSelecionados.textContent = cnpjsSelecionados.length;
            btnImportarSelecionados.disabled = cnpjsSelecionados.length === 0;

            // Atualizar checkbox "selecionar todos"
            const total = participantesLista.querySelectorAll('.participante-check').length;
            if (modalSelectAll) {
                modalSelectAll.checked = cnpjsSelecionados.length === total && total > 0;
                modalSelectAll.indeterminate = cnpjsSelecionados.length > 0 && cnpjsSelecionados.length < total;
            }
        }

        // Funcao para renderizar participantes
        function renderizarParticipantes(participantes, filtro = '') {
            if (!participantes || participantes.length === 0) {
                participantesLista.innerHTML = '<div class="text-center py-8 text-gray-500">Nenhum participante encontrado neste relatorio.</div>';
                return;
            }

            const filtrados = filtro
                ? participantes.filter(p =>
                    (p.cnpj && p.cnpj.includes(filtro)) ||
                    (p.razao_social && p.razao_social.toLowerCase().includes(filtro.toLowerCase()))
                )
                : participantes;

            if (filtrados.length === 0) {
                participantesLista.innerHTML = '<div class="text-center py-8 text-gray-500">Nenhum participante encontrado para o filtro informado.</div>';
                return;
            }

            let html = '<div class="space-y-2">';
            filtrados.forEach(function(p) {
                const cnpjFormatado = p.cnpj ? p.cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5') : '-';
                const checked = cnpjsSelecionados.includes(p.cnpj) ? 'checked' : '';

                html += '<label class="flex items-center gap-4 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">';
                html += '<input type="checkbox" class="participante-check rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="' + (p.cnpj || '') + '" ' + checked + '>';
                html += '<div class="flex-1 min-w-0">';
                html += '<p class="text-sm font-medium text-gray-900 truncate">' + (p.razao_social || 'Razao Social nao informada') + '</p>';
                html += '<p class="text-xs text-gray-500 font-mono">' + cnpjFormatado + '</p>';
                html += '</div>';

                // Badge de situacao
                if (p.situacao_cadastral) {
                    let badgeClass = 'bg-gray-100 text-gray-700';
                    if (p.situacao_cadastral === 'ATIVA') badgeClass = 'bg-green-100 text-green-700';
                    else if (p.situacao_cadastral === 'BAIXADA' || p.situacao_cadastral === 'INAPTA') badgeClass = 'bg-red-100 text-red-700';
                    else if (p.situacao_cadastral === 'SUSPENSA') badgeClass = 'bg-amber-100 text-amber-700';

                    html += '<span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium ' + badgeClass + '">' + p.situacao_cadastral + '</span>';
                }

                html += '</label>';
            });
            html += '</div>';

            participantesLista.innerHTML = html;
            document.getElementById('modal-total-participantes').textContent = participantes.length;

            // Adicionar event listeners aos checkboxes
            participantesLista.querySelectorAll('.participante-check').forEach(function(cb) {
                cb.addEventListener('change', atualizarContagem);
            });
        }

        // Carregar participantes do relatorio
        async function carregarParticipantes(relatorioId) {
            participantesLista.innerHTML = '<div class="flex items-center justify-center py-8"><svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span class="ml-3 text-gray-600">Carregando participantes...</span></div>';

            try {
                const response = await fetch('/app/monitoramento/participantes-raf/' + relatorioId, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Erro ao carregar participantes');
                }

                const data = await response.json();
                participantesData = data.participantes || [];
                cnpjsSelecionados = [];
                renderizarParticipantes(participantesData);
                atualizarContagem();
            } catch (err) {
                console.error('[Monitoramento SPED] Erro:', err);
                participantesLista.innerHTML = '<div class="text-center py-8 text-red-600">Erro ao carregar participantes. Tente novamente.</div>';
            }
        }

        // Botoes "Ver Participantes"
        document.querySelectorAll('.btn-ver-participantes').forEach(function(btn) {
            btn.addEventListener('click', function() {
                relatorioAtual = this.dataset.relatorioId;
                modalVerParticipantes.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                carregarParticipantes(relatorioAtual);
            });
        });

        // Botoes "Importar Todos"
        document.querySelectorAll('.btn-importar-todos').forEach(function(btn) {
            btn.addEventListener('click', function() {
                relatorioAtual = this.dataset.relatorioId;
                const totalCnpjs = this.dataset.totalCnpjs || '0';
                cnpjsSelecionados = ['__ALL__']; // Marcador especial para importar todos
                document.getElementById('confirmar-count').textContent = totalCnpjs;
                modalConfirmarImportacao.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });
        });

        // Selecionar todos no modal
        if (modalSelectAll) {
            modalSelectAll.addEventListener('change', function() {
                const checkboxes = participantesLista.querySelectorAll('.participante-check');
                checkboxes.forEach(function(cb) {
                    cb.checked = modalSelectAll.checked;
                });
                atualizarContagem();
            });
        }

        // Busca no modal
        if (buscaModal) {
            let debounceTimer;
            buscaModal.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    renderizarParticipantes(participantesData, buscaModal.value.trim());
                }, 300);
            });
        }

        // Botao importar selecionados
        if (btnImportarSelecionados) {
            btnImportarSelecionados.addEventListener('click', function() {
                document.getElementById('confirmar-count').textContent = cnpjsSelecionados.length;
                modalVerParticipantes.classList.add('hidden');
                modalConfirmarImportacao.classList.remove('hidden');
            });
        }

        // Confirmar importacao
        const btnConfirmar = document.getElementById('btn-confirmar-importacao');
        if (btnConfirmar) {
            btnConfirmar.addEventListener('click', async function() {
                const btnText = btnConfirmar.querySelector('.btn-text');
                const btnSpinner = btnConfirmar.querySelector('.btn-spinner');

                btnConfirmar.disabled = true;
                if (btnText) btnText.classList.add('hidden');
                if (btnSpinner) btnSpinner.classList.remove('hidden');

                try {
                    const payload = {
                        relatorio_id: relatorioAtual,
                        cnpjs: cnpjsSelecionados.includes('__ALL__') ? [] : cnpjsSelecionados,
                        importar_todos: cnpjsSelecionados.includes('__ALL__'),
                    };

                    const response = await fetch('/app/monitoramento/importar-raf/' + relatorioAtual, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Fechar modal e redirecionar
                        modalConfirmarImportacao.classList.add('hidden');
                        document.body.style.overflow = '';

                        if (window.showToast) {
                            window.showToast('success', data.message || 'Participantes importados com sucesso!');
                        } else {
                            alert(data.message || 'Participantes importados com sucesso!');
                        }

                        // Redirecionar para monitoramento
                        setTimeout(function() {
                            const link = document.createElement('a');
                            link.href = '/app/monitoramento';
                            link.setAttribute('data-link', '');
                            link.click();
                        }, 1000);
                    } else {
                        throw new Error(data.message || 'Erro ao importar participantes');
                    }
                } catch (err) {
                    console.error('[Monitoramento SPED] Erro ao importar:', err);
                    alert('Erro ao importar participantes: ' + err.message);
                } finally {
                    btnConfirmar.disabled = false;
                    if (btnText) btnText.classList.remove('hidden');
                    if (btnSpinner) btnSpinner.classList.add('hidden');
                }
            });
        }

        // Fechar modais
        document.querySelectorAll('.modal-close').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const modal = btn.closest('[id^="modal-"]');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        });

        // Fechar modal clicando fora
        [modalVerParticipantes, modalConfirmarImportacao].forEach(function(modal) {
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                });
            }
        });

        // ===== Funcionalidade de Upload de Arquivo .txt =====
        const txtDropzone = document.getElementById('txt-dropzone');
        const txtFileInput = document.getElementById('txt-file-input');
        const txtFileMeta = document.getElementById('txt-file-meta');
        const txtFileName = document.getElementById('txt-file-name');
        const txtFileSize = document.getElementById('txt-file-size');
        const txtChangeFile = document.getElementById('txt-change-file');
        const txtImportarBtn = document.getElementById('txt-importar-btn');
        const txtErrorMessage = document.getElementById('txt-error-message');
        const txtErrorText = document.getElementById('txt-error-text');
        const tipoSpedRadios = document.querySelectorAll('input[name="tipo-sped"]');

        const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

        // Função para obter tipo SPED selecionado
        function getSelectedTipoSped() {
            const selected = Array.from(tipoSpedRadios).find(radio => radio.checked);
            return selected ? selected.value : '';
        }

        // Função para atualizar visual dos labels do tipo SPED
        function updateTipoSpedLabels() {
            const selectedValue = getSelectedTipoSped();
            document.querySelectorAll('.tipo-sped-label').forEach(function(label) {
                const radio = label.querySelector('input[type="radio"]');
                if (radio && radio.value === selectedValue) {
                    label.classList.remove('border-gray-300', 'hover:border-blue-400');
                    label.classList.add('border-blue-600', 'bg-blue-50');
                } else {
                    label.classList.remove('border-blue-600', 'bg-blue-50');
                    label.classList.add('border-gray-300', 'hover:border-blue-400');
                }
            });
        }

        // Função para atualizar estado do dropzone
        function updateDropzoneState() {
            const hasTipoSped = getSelectedTipoSped() !== '';
            const dropzoneMainText = document.getElementById('txt-dropzone-main-text');
            const dropzoneSubText = document.getElementById('txt-dropzone-sub-text');
            
            if (txtDropzone && txtFileInput) {
                if (hasTipoSped) {
                    // Habilitar dropzone
                    txtDropzone.classList.remove('border-gray-300', 'bg-gray-100', 'opacity-60', 'cursor-not-allowed', 'pointer-events-none');
                    txtDropzone.classList.add('border-gray-300', 'bg-gray-50', 'hover:border-blue-400', 'hover:bg-blue-50', 'cursor-pointer');
                    txtDropzone.setAttribute('aria-disabled', 'false');
                    txtFileInput.disabled = false;
                    
                    // Atualizar ícone
                    const svg = txtDropzone.querySelector('svg');
                    if (svg) {
                        svg.classList.remove('text-gray-400');
                        svg.classList.add('text-blue-400');
                    }
                    
                    // Atualizar textos
                    if (dropzoneMainText) {
                        dropzoneMainText.textContent = 'Arraste o arquivo .txt aqui';
                        dropzoneMainText.classList.remove('text-gray-500');
                        dropzoneMainText.classList.add('text-gray-700', 'font-medium');
                    }
                    if (dropzoneSubText) {
                        dropzoneSubText.textContent = 'ou clique para selecionar';
                        dropzoneSubText.classList.remove('text-gray-400');
                        dropzoneSubText.classList.add('text-gray-500');
                    }
                } else {
                    // Desabilitar dropzone
                    txtDropzone.classList.remove('border-gray-300', 'bg-gray-50', 'hover:border-blue-400', 'hover:bg-blue-50', 'cursor-pointer');
                    txtDropzone.classList.add('border-gray-300', 'bg-gray-100', 'opacity-60', 'cursor-not-allowed', 'pointer-events-none');
                    txtDropzone.setAttribute('aria-disabled', 'true');
                    txtFileInput.disabled = true;
                    
                    // Atualizar ícone
                    const svg = txtDropzone.querySelector('svg');
                    if (svg) {
                        svg.classList.remove('text-blue-400');
                        svg.classList.add('text-gray-400');
                    }
                    
                    // Atualizar textos
                    if (dropzoneMainText) {
                        dropzoneMainText.textContent = 'Selecione o tipo de SPED primeiro';
                        dropzoneMainText.classList.remove('text-gray-700', 'font-medium');
                        dropzoneMainText.classList.add('text-gray-500');
                    }
                    if (dropzoneSubText) {
                        dropzoneSubText.textContent = 'Depois arraste o arquivo .txt aqui ou clique para selecionar';
                        dropzoneSubText.classList.remove('text-gray-500');
                        dropzoneSubText.classList.add('text-gray-400');
                    }
                }
            }
        }

        // Função para atualizar habilitação do botão
        function updateImportButtonState() {
            const hasTipoSped = getSelectedTipoSped() !== '';
            const hasFile = txtFileInput && txtFileInput.files && txtFileInput.files.length > 0;
            
            if (txtImportarBtn) {
                txtImportarBtn.disabled = !(hasTipoSped && hasFile);
            }
        }

        // Função para validar arquivo
        function validarArquivoTxt(file) {
            if (!file) return false;

            // Validar extensão
            const fileName = file.name.toLowerCase();
            const isTxt = fileName.endsWith('.txt') || file.type === 'text/plain';
            if (!isTxt) {
                mostrarErroTxt('Apenas arquivos .txt são permitidos. Por favor, selecione um arquivo .txt.');
                return false;
            }

            // Validar tamanho
            if (file.size > MAX_FILE_SIZE) {
                mostrarErroTxt('O arquivo excede o limite de 10MB. Por favor, selecione um arquivo menor.');
                return false;
            }

            return true;
        }

        // Função para mostrar erro
        function mostrarErroTxt(mensagem) {
            if (txtErrorText) txtErrorText.textContent = mensagem;
            if (txtErrorMessage) txtErrorMessage.classList.remove('hidden');
        }

        // Função para ocultar erro
        function ocultarErroTxt() {
            if (txtErrorMessage) txtErrorMessage.classList.add('hidden');
        }

        // Função para atualizar UI do arquivo
        function atualizarUITxt(file) {
            if (!file) {
                if (txtFileMeta) txtFileMeta.classList.add('hidden');
                updateImportButtonState();
                return;
            }

            if (txtFileName) txtFileName.textContent = file.name;
            if (txtFileSize) {
                const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                txtFileSize.textContent = sizeMB + ' MB';
            }
            if (txtFileMeta) txtFileMeta.classList.remove('hidden');
            updateImportButtonState();
        }

        // Função para limpar arquivo
        function limparArquivoTxt() {
            if (txtFileInput) txtFileInput.value = '';
            atualizarUITxt(null);
            ocultarErroTxt();
            updateImportButtonState();
        }

        // Função para processar arquivo selecionado
        function processarArquivoTxt(file) {
            ocultarErroTxt();

            if (!validarArquivoTxt(file)) {
                limparArquivoTxt();
                return;
            }

            atualizarUITxt(file);
        }

        // Click no dropzone
        if (txtDropzone && txtFileInput) {
            txtDropzone.addEventListener('click', function() {
                if (txtFileInput.disabled) return;
                txtFileInput.click();
            });

            // Drag and drop
            txtDropzone.addEventListener('dragover', function(e) {
                if (txtFileInput.disabled) return;
                e.preventDefault();
                txtDropzone.classList.remove('border-gray-300', 'bg-gray-50', 'hover:border-blue-400', 'hover:bg-blue-50');
                txtDropzone.classList.add('border-blue-500', 'bg-blue-50');
            });

            txtDropzone.addEventListener('dragleave', function() {
                if (txtFileInput.disabled) return;
                txtDropzone.classList.remove('border-blue-500', 'bg-blue-50');
                txtDropzone.classList.add('border-gray-300', 'bg-gray-50', 'hover:border-blue-400', 'hover:bg-blue-50');
            });

            txtDropzone.addEventListener('drop', function(e) {
                if (txtFileInput.disabled) return;
                e.preventDefault();
                txtDropzone.classList.remove('border-blue-500', 'bg-blue-50');
                txtDropzone.classList.add('border-gray-300', 'bg-gray-50', 'hover:border-blue-400', 'hover:bg-blue-50');

                const file = e.dataTransfer?.files?.[0];
                if (file) {
                    processarArquivoTxt(file);
                    // Atualizar input file
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    txtFileInput.files = dt.files;
                }
            });
        }

        // Change no input file
        if (txtFileInput) {
            txtFileInput.addEventListener('change', function(e) {
                const file = e.target.files?.[0];
                if (file) {
                    processarArquivoTxt(file);
                } else {
                    limparArquivoTxt();
                }
            });
        }

        // Botão trocar arquivo
        if (txtChangeFile) {
            txtChangeFile.addEventListener('click', function(e) {
                e.stopPropagation();
                limparArquivoTxt();
                if (txtFileInput) txtFileInput.click();
            });
        }

        // Event listeners para radio buttons do tipo SPED
        if (tipoSpedRadios && tipoSpedRadios.length > 0) {
            tipoSpedRadios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    updateTipoSpedLabels();
                    updateDropzoneState();
                    updateImportButtonState();
                });
            });
        }

        // Botão importar (por enquanto apenas mostra mensagem)
        if (txtImportarBtn) {
            txtImportarBtn.addEventListener('click', function() {
                const tipoSped = getSelectedTipoSped();
                if (!tipoSped) {
                    if (window.showToast) {
                        window.showToast('error', 'Selecione o tipo de SPED antes de importar.');
                    } else {
                        alert('Selecione o tipo de SPED antes de importar.');
                    }
                    return;
                }
                
                // Por enquanto não faz nada - funcionalidade em desenvolvimento
                if (window.showToast) {
                    window.showToast('info', 'Funcionalidade de importação em desenvolvimento.');
                } else {
                    alert('Funcionalidade de importação em desenvolvimento.');
                }
            });
        }

        // Inicializar estado inicial
        updateTipoSpedLabels();
        updateDropzoneState();
        updateImportButtonState();

        console.log('[Monitoramento SPED] Inicializacao concluida');
    }

    // Expor globalmente para SPA
    window.initMonitoramentoSped = initMonitoramentoSped;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMonitoramentoSped, { once: true });
    } else {
        initMonitoramentoSped();
    }
})();
</script>
