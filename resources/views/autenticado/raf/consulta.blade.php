{{-- RAF Consulta - Selecao de Participantes --}}
<div class="min-h-screen bg-gray-50" id="consulta-lote-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gerar Relatorio RAF</h1>
                    <p class="mt-1 text-sm text-gray-500">Selecione os participantes para gerar um relatorio de analise fiscal.</p>
                </div>
                <a
                    href="/app/raf/historico"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-700 text-sm font-medium transition hover:bg-gray-50"
                    data-link
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Historico
                </a>
            </div>
        </div>

        @if($totalParticipantes === 0)
            {{-- Estado vazio: nenhum participante --}}
            <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="text-base font-semibold text-gray-800 mb-1">Nenhum participante encontrado</h3>
                <p class="text-sm text-gray-500 mb-6">Importe XMLs ou SPEDs primeiro para adicionar participantes.</p>
                <div class="flex gap-3 justify-center">
                    <a href="/app/monitoramento/xml" data-link class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Importar XMLs
                    </a>
                    <a href="/app/monitoramento/sped" data-link class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 bg-white text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Importar SPED
                    </a>
                </div>
            </div>
        @else
            {{-- Layout 2 colunas --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Coluna Esquerda: Filtros e Lista de Participantes (2/3) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg border border-gray-200">
                        {{-- Filtros --}}
                        <div class="px-5 py-4 border-b border-gray-100">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                {{-- Filtro Grupo --}}
                                <div>
                                    <select id="filtro-grupo" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700">
                                        <option value="">Todos os grupos</option>
                                        @foreach($grupos as $grupo)
                                            <option value="{{ $grupo->id }}">{{ $grupo->nome }} ({{ $grupo->participantes_count }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filtro Origem --}}
                                <div>
                                    <select id="filtro-origem" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700">
                                        <option value="">Todas as origens</option>
                                        <option value="NFE">NF-e</option>
                                        <option value="NFSE">NFS-e</option>
                                        <option value="CTE">CT-e</option>
                                        <option value="SPED_EFD_FISCAL">SPED EFD Fiscal</option>
                                        <option value="SPED_EFD_CONTRIB">SPED EFD Contribuicoes</option>
                                        <option value="MANUAL">Manual</option>
                                    </select>
                                </div>

                                {{-- Filtro Cliente --}}
                                <div>
                                    <select id="filtro-cliente" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700">
                                        <option value="">Todos os clientes</option>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->id }}">{{ $cliente->razao_social ?? $cliente->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Busca --}}
                                <div>
                                    <input
                                        type="text"
                                        id="filtro-busca"
                                        placeholder="Buscar CNPJ ou razao social..."
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 text-sm text-gray-700"
                                    >
                                </div>
                            </div>

                            {{-- Acoes em massa --}}
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                                <div class="flex items-center gap-4 text-sm">
                                    <button type="button" id="btn-selecionar-todos" class="text-gray-600 hover:text-gray-900">
                                        Selecionar todos
                                    </button>
                                    <button type="button" id="btn-limpar-selecao" class="text-gray-500 hover:text-gray-700">
                                        Limpar
                                    </button>
                                </div>
                                <span id="contador-selecionados" class="text-xs text-gray-500">
                                    <span id="total-selecionados">0</span> selecionados
                                </span>
                            </div>
                        </div>

                        {{-- Tabela de Participantes --}}
                        <div>
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="w-10 px-4 py-3 text-left">
                                            <input type="checkbox" id="checkbox-todos" class="w-4 h-4 text-gray-600 rounded border-gray-300">
                                        </th>
                                        <th class="w-40 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CNPJ</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razao Social</th>
                                        <th class="w-16 px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">UF</th>
                                    </tr>
                                </thead>
                                <tbody id="tabela-participantes" class="divide-y divide-gray-100">
                                    {{-- Preenchido via JS --}}
                                    <tr id="loading-row">
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                            <svg class="animate-spin h-5 w-5 text-gray-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="text-sm">Carregando...</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Paginacao --}}
                        <div id="paginacao-container" class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
                            <div class="text-xs text-gray-500">
                                <span id="pag-inicio">0</span>-<span id="pag-fim">0</span> de <span id="pag-total">0</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" id="btn-pag-anterior" class="px-2.5 py-1 border border-gray-200 rounded text-xs text-gray-600 hover:bg-gray-50 disabled:opacity-40" disabled>
                                    Anterior
                                </button>
                                <span id="pag-atual" class="text-xs text-gray-500">1</span>
                                <button type="button" id="btn-pag-proximo" class="px-2.5 py-1 border border-gray-200 rounded text-xs text-gray-600 hover:bg-gray-50 disabled:opacity-40" disabled>
                                    Proximo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Coluna Direita: Tipo de Analise e Resumo (1/3) --}}
                <div class="space-y-4">
                    {{-- Card Tipo de Analise --}}
                    <div class="bg-white rounded-lg border border-gray-200">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-900">Tipo de Analise</h3>
                        </div>
                        <div class="p-4 space-y-2">
                            @foreach($planos as $plano)
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-gray-300 hover:bg-gray-50 transition plano-label" data-plano-id="{{ $plano->id }}">
                                    <input
                                        type="radio"
                                        name="plano_id"
                                        value="{{ $plano->id }}"
                                        class="w-4 h-4 text-gray-600 border-gray-300"
                                        data-custo="{{ $plano->custo_creditos }}"
                                        data-gratuito="{{ $plano->is_gratuito ? '1' : '0' }}"
                                        {{ $loop->first ? 'checked' : '' }}
                                    >
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900">{{ $plano->nome }}</span>
                                            @if($plano->is_gratuito)
                                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">Grátis</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">{{ $plano->custo_creditos }} créditos</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $plano->descricao }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Card Resumo --}}
                    <div class="bg-white rounded-lg border border-gray-200">
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total</span>
                                <span id="resumo-custo-total" class="px-3 py-1 bg-blue-100 text-blue-700 text-base font-semibold rounded-full">0 créditos</span>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500">Participantes</span>
                                    <span id="resumo-participantes" class="text-gray-900 font-medium">0</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500">Custo unitário</span>
                                    <span id="resumo-custo-unitario" class="px-2 py-0.5 bg-blue-50 text-blue-600 text-xs font-medium rounded-full">0 créditos</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                                    <span class="text-gray-500">Seu saldo</span>
                                    <span id="resumo-saldo" class="px-2 py-0.5 bg-green-50 text-green-600 text-xs font-medium rounded-full">{{ number_format($credits, 0, ',', '.') }} créditos</span>
                                </div>
                            </div>

                            {{-- Alerta créditos --}}
                            <div id="alerta-creditos-insuficientes" class="hidden mt-3 p-2 bg-red-50 border border-red-100 rounded-lg text-xs text-red-600">
                                Créditos insuficientes
                            </div>

                            <button type="button" id="btn-gerar-relatorio" class="w-full mt-4 py-2.5 rounded-lg text-sm font-medium transition" style="background-color: #d1d5db; color: #6b7280; cursor: not-allowed;" disabled>
                                Gerar Relatorio
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal de Progresso --}}
            <div id="modal-progresso" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-lg max-w-xs w-full mx-4 p-5">
                    <div class="text-center">
                        <svg class="animate-spin h-6 w-6 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <h3 id="progresso-titulo" class="text-sm font-semibold text-gray-900 mb-1">Gerando relatorio...</h3>
                        <p id="progresso-mensagem" class="text-xs text-gray-500 mb-3">Aguarde enquanto processamos.</p>
                        <div class="w-full bg-gray-100 rounded-full h-1 mb-1">
                            <div id="progresso-barra" class="bg-gray-900 h-1 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="progresso-percentual" class="text-xs text-gray-400">0%</p>
                    </div>
                </div>
            </div>

            {{-- Modal de Sucesso --}}
            <div id="modal-sucesso" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-lg max-w-xs w-full mx-4 p-5">
                    <div class="text-center">
                        <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Relatorio gerado</h3>
                        <p class="text-xs text-gray-500 mb-4">Download iniciado automaticamente.</p>
                        <div class="flex gap-2">
                            <a id="link-download-manual" href="#" class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Baixar
                            </a>
                            <button type="button" id="btn-fechar-sucesso" class="flex-1 py-2 border border-gray-200 bg-white text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
                                Fechar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal de Erro --}}
            <div id="modal-erro" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-lg mx-4 p-4" style="max-width: 280px;">
                    <div class="text-center">
                        <div class="w-8 h-8 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Erro</h3>
                        <p id="erro-mensagem" class="text-xs text-gray-500 mb-3 break-words">Ocorreu um erro inesperado.</p>
                        <button type="button" id="btn-fechar-erro" class="w-full py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition text-sm font-medium">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Dados para JS --}}
<script>
    window.consultaData = {
        credits: {{ $credits ?? 0 }},
        csrfToken: '{{ csrf_token() }}',
        routes: {
            getParticipantes: '/app/raf/consulta/participantes',
            getParticipantesGrupo: '/app/raf/consulta/participantes/grupo/',
            calcularCusto: '/app/raf/consulta/calcular-custo',
            executar: '/app/raf/consulta/executar',
            progressoStream: '/app/raf/consulta/progresso/stream',
            baixarLote: '/app/raf/lote/{id}/baixar'
        }
    };
</script>
<script src="/js/consulta-lote.js"></script>
