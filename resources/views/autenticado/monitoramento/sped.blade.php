{{-- Monitoramento - Importar do SPED --}}
<div class="min-h-screen bg-gray-50" id="monitoramento-sped-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Importar Participantes</h1>
                    <p class="mt-1 text-sm text-gray-600">Adicione CNPJs à sua lista de monitoramento a partir de arquivos SPED ou relatórios RAF.</p>
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
                        Importe CNPJs de fornecedores e clientes extraídos do seu arquivo SPED para acompanhar a situação cadastral, regime tributário e certidões de forma contínua.
                    </p>
                </div>
            </div>
        </div>

        {{-- Seção Importar de Arquivo .txt --}}
        <div class="mb-6">
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

                        {{-- Seleção de Cliente (Opcional) --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Associar a um Cliente: <span class="text-gray-400 font-normal">(opcional)</span>
                            </label>
                            <select
                                id="cliente-select"
                                name="cliente_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                            >
                                <option value="">Nao associar a um cliente</option>
                                @foreach($clientes ?? [] as $cliente)
                                    <option value="{{ $cliente->id }}">
                                        {{ $cliente->razao_social ?? $cliente->nome }}
                                        ({{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cliente->documento) }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                Associe os participantes importados a um cliente para melhor organizacao.
                            </p>
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
                                    <strong class="text-blue-900">Formato:</strong> Um CNPJ por linha (apenas numeros, 14 digitos).
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
                                    <strong class="text-blue-900">Após importar:</strong> Os CNPJs serão adicionados à sua lista de monitoramento.
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
                            <h3 class="text-base font-semibold text-gray-900">Como Funciona</h3>
                        </div>
                    </div>
                <div class="p-6 space-y-6">
                    {{-- Seção Como Funciona --}}
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Como Funciona</h4>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">1</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Importação</p>
                                    <p class="text-xs text-gray-500">Adicione CNPJs via arquivo EFD (SPED) .txt ou a partir de relatórios RAF já processados.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">2</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Consultas</p>
                                    <p class="text-xs text-gray-500">Execute consultas avulsas ou configure frequência automática (semanal, mensal ou trimestral).</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">3</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Alertas</p>
                                    <p class="text-xs text-gray-500">Receba notificações sobre alterações na situação cadastral ou fiscal dos CNPJs.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold">4</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Histórico</p>
                                    <p class="text-xs text-gray-500">Consulte o histórico completo de cada CNPJ monitorado.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Seção Planos Disponíveis --}}
                    <div class="pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Planos Disponíveis</h4>
                        <div class="space-y-2">
                            <div class="p-3 rounded-lg border border-gray-200 bg-gray-50">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-900">Básico</span>
                                    <span class="text-xs font-medium text-green-600">Grátis</span>
                                </div>
                                <p class="text-xs text-gray-600">Situacao Cadastral RFB + Simples Nacional</p>
                            </div>
                            <div class="p-3 rounded-lg border border-gray-200 bg-white">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-900">Cadastral+</span>
                                    <span class="text-xs font-medium text-blue-600">3 créditos</span>
                                </div>
                                <p class="text-xs text-gray-600">Inclui tudo do Básico + CNPJ Completo + SINTEGRA + Inscricao Estadual</p>
                            </div>
                            <div class="p-3 rounded-lg border border-gray-200 bg-white">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-900">Fiscal Federal</span>
                                    <span class="text-xs font-medium text-blue-600">6 créditos</span>
                                </div>
                                <p class="text-xs text-gray-600">Inclui tudo do Cadastral+ + CND Federal (PGFN) + CRF FGTS</p>
                            </div>
                            <div class="p-3 rounded-lg border border-gray-200 bg-white">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-900">Fiscal Completo</span>
                                    <span class="text-xs font-medium text-blue-600">12 créditos</span>
                                </div>
                                <p class="text-xs text-gray-600">Inclui tudo do Fiscal Federal + CND Estadual + CNDT Trabalhista</p>
                            </div>
                            <div class="p-3 rounded-lg border border-gray-200 bg-white">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-900">Due Diligence</span>
                                    <span class="text-xs font-medium text-purple-600">18 créditos</span>
                                </div>
                                <p class="text-xs text-gray-600">Inclui tudo do Fiscal Completo + Protestos + Processos Judiciais</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Seção de Progresso de Importação (inicialmente oculta) --}}
        <div id="importacao-progresso" class="hidden">
            <div id="progresso-card" class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                {{-- Header: Empresa e documento --}}
                <div class="flex items-start gap-3 mb-4">
                    <div id="progresso-icon" class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 id="progresso-empresa" class="font-semibold text-gray-900 truncate">
                            Aguardando dados...
                        </h3>
                        <p id="progresso-documento" class="text-sm text-gray-500 hidden">
                            {{-- Tipo SPED • Período --}}
                        </p>
                    </div>
                </div>

                {{-- Barra de progresso --}}
                <div class="mb-3">
                    <div class="flex justify-between text-sm mb-1">
                        <span id="progresso-mensagem" class="text-gray-600">Iniciando...</span>
                        <span id="progresso-porcentagem" class="font-medium text-gray-900">0%</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div id="barra-progresso" class="bg-blue-600 h-full rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
                    </div>
                </div>

                {{-- Mensagem de erro (só aparece em caso de erro) --}}
                <div id="progresso-erro" class="hidden pt-3 border-t border-red-100">
                    <p id="progresso-erro-msg" class="text-sm text-gray-700 mb-3">
                        Ocorreu um erro interno durante o processamento.
                    </p>
                    <p class="text-sm text-gray-600 mb-4">
                        Por favor, tente novamente mais tarde.<br>
                        Se o erro persistir, entre em contato com o suporte:
                    </p>
                    <a href="https://wa.me/5567999844366"
                       target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 transition mb-3">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        WhatsApp: (67) 99984-4366
                    </a>
                    <div>
                        <button type="button"
                                id="btn-tentar-novamente"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Tentar Novamente
                        </button>
                    </div>
                </div>
            </div>

            {{-- Seção de Resultados da Importação (aparece após importação concluída) --}}
            <div id="resultado-importacao" class="hidden mt-4">
                <div class="bg-white border border-green-200 rounded-lg shadow-sm">
                    {{-- Header dos Resultados --}}
                    <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Importação Concluída</h3>
                                    <p class="text-sm text-gray-600" id="resultado-empresa">-</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                id="btn-nova-importacao"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Nova Importação
                            </button>
                        </div>
                    </div>

                    {{-- Estatísticas da Importação --}}
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-2xl font-bold text-gray-900" id="resultado-total-cnpjs">0</p>
                                <p class="text-xs text-gray-500">CNPJs</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-2xl font-bold text-gray-900" id="resultado-total-cpfs">0</p>
                                <p class="text-xs text-gray-500">CPFs</p>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <p class="text-2xl font-bold text-green-600" id="resultado-novos">0</p>
                                <p class="text-xs text-gray-500">Novos</p>
                            </div>
                            <div class="text-center p-3 bg-amber-50 rounded-lg">
                                <p class="text-2xl font-bold text-amber-600" id="resultado-duplicados">0</p>
                                <p class="text-xs text-gray-500">Duplicados</p>
                            </div>
                        </div>
                    </div>

                    {{-- Lista de Participantes Importados --}}
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-semibold text-gray-900">Participantes Importados</h4>
                            <button
                                type="button"
                                id="btn-carregar-participantes"
                                class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                            >
                                Carregar lista
                            </button>
                        </div>

                        {{-- Container da lista (inicialmente mostra placeholder) --}}
                        <div id="lista-participantes-container">
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="text-sm">Clique em "Carregar lista" para ver os participantes importados</p>
                            </div>
                        </div>

                        {{-- Loading state --}}
                        <div id="lista-participantes-loading" class="hidden text-center py-8">
                            <svg class="w-8 h-8 mx-auto text-blue-600 animate-spin mb-3" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <p class="text-sm text-gray-500">Carregando participantes...</p>
                        </div>

                        {{-- Tabela de participantes (preenchida via JS) --}}
                        <div id="lista-participantes-tabela" class="hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">CNPJ</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Razão Social</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Situação</th>
                                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="participantes-tbody-resultado" class="divide-y divide-gray-200">
                                        {{-- Preenchido via JS --}}
                                    </tbody>
                                </table>
                            </div>
                            <div id="participantes-pagination" class="mt-4 flex items-center justify-between text-sm text-gray-500">
                                <span id="participantes-info">Mostrando 0 de 0</span>
                                <div class="flex gap-2">
                                    <button type="button" id="btn-prev-page" class="px-3 py-1 rounded border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50" disabled>Anterior</button>
                                    <button type="button" id="btn-next-page" class="px-3 py-1 rounded border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50" disabled>Próximo</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Ações --}}
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                            <a
                                href="/app/monitoramento"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition"
                                data-link
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                Ver Todos os Participantes
                            </a>
                            <a
                                id="link-filtrar-importacao"
                                href="/app/monitoramento/participantes?importacao="
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm hover:bg-blue-700 transition"
                                data-link
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Ver Apenas Esta Importação
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de Relatorios RAF --}}
        <div id="raf-relatorios-section" class="bg-white rounded-xl border border-gray-200 shadow-sm mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Importar de Relatórios RAF</h2>
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

        // Identificador único por aba para isolar notificações SSE
        const tabId = crypto.randomUUID ? crypto.randomUUID() :
            (Date.now().toString(36) + Math.random().toString(36).substr(2));

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
                            window.showToast(data.message || 'Participantes importados com sucesso!', 'success');
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

        // Variáveis para controle de importação
        let eventSourceTxt = null;
        let importacaoEmAndamento = false;

        // Elementos de progresso (nova UI minimalista)
        const progressoContainer = document.getElementById('importacao-progresso');
        const progressoCard = document.getElementById('progresso-card');
        const barraProgresso = document.getElementById('barra-progresso');
        const progressoPorcentagem = document.getElementById('progresso-porcentagem');
        const progressoMensagem = document.getElementById('progresso-mensagem');
        const progressoEmpresa = document.getElementById('progresso-empresa');
        const progressoDocumento = document.getElementById('progresso-documento');
        const progressoIcon = document.getElementById('progresso-icon');

        // Elementos de erro
        const progressoErro = document.getElementById('progresso-erro');
        const progressoErroMsg = document.getElementById('progresso-erro-msg');

        // Função para atualizar ícone de status
        function atualizarIconeStatus(status, errorMessage) {
            if (!progressoIcon || !progressoCard) return;

            // Reset classes do card
            progressoCard.className = 'bg-white border rounded-lg p-4 shadow-sm';

            switch (status) {
                case 'concluido':
                    progressoIcon.className = 'w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0';
                    progressoIcon.innerHTML = '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                    progressoCard.classList.add('border-green-200');
                    if (barraProgresso) barraProgresso.className = 'bg-green-600 h-full rounded-full transition-all duration-500 ease-out';
                    // Ocultar seção de erro, manter stats
                    if (progressoErro) progressoErro.classList.add('hidden');
                    break;
                case 'erro':
                case 'timeout':
                    progressoIcon.className = 'w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0';
                    progressoIcon.innerHTML = '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                    progressoCard.classList.add('border-red-200');
                    if (barraProgresso) barraProgresso.className = 'bg-red-600 h-full rounded-full transition-all duration-500 ease-out';
                    // Mostrar seção de erro
                    if (progressoErro) {
                        progressoErro.classList.remove('hidden');
                        // Atualizar mensagem de erro se fornecida
                        if (progressoErroMsg && errorMessage) {
                            progressoErroMsg.textContent = errorMessage;
                        } else if (progressoErroMsg) {
                            progressoErroMsg.textContent = status === 'timeout'
                                ? 'O processamento demorou mais do que o esperado.'
                                : 'Ocorreu um erro interno durante o processamento.';
                        }
                    }
                    break;
                default:
                    progressoIcon.className = 'w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0';
                    progressoIcon.innerHTML = '<svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>';
                    progressoCard.classList.add('border-gray-200');
                    if (barraProgresso) barraProgresso.className = 'bg-blue-600 h-full rounded-full transition-all duration-500 ease-out';
                    // Ocultar seção de erro
                    if (progressoErro) progressoErro.classList.add('hidden');
            }
        }

        // Função para atualizar UI de progresso
        function atualizarProgresso(payload) {
            const dados = payload.dados || {};
            const progresso = parseInt(payload.progresso) || 0;
            const status = payload.status || 'processando';
            const mensagem = payload.mensagem || 'Processando...';
            const errorMessage = payload.error_message || payload.mensagem || null;

            // Barra de progresso
            if (barraProgresso) barraProgresso.style.width = progresso + '%';
            if (progressoPorcentagem) progressoPorcentagem.textContent = progresso + '%';
            if (progressoMensagem) progressoMensagem.textContent = mensagem;

            // Empresa
            if (progressoEmpresa && dados.nome_empresa) {
                progressoEmpresa.textContent = dados.nome_empresa;
            }

            // Documento (tipo e período)
            if (progressoDocumento) {
                const tipo = dados.tipo_documento || '';
                const periodo = dados.data_inicial_do_documento && dados.data_final_do_documento
                    ? dados.data_inicial_do_documento + ' - ' + dados.data_final_do_documento
                    : '';
                const docText = [tipo, periodo].filter(Boolean).join(' • ');
                if (docText) {
                    progressoDocumento.textContent = docText;
                    progressoDocumento.classList.remove('hidden');
                }
            }

            // Status visual (passa mensagem de erro se for erro/timeout)
            const isError = status === 'erro' || status === 'timeout';
            atualizarIconeStatus(status, isError ? errorMessage : null);
        }

        // Função para mostrar UI de progresso
        function mostrarProgresso() {
            if (progressoContainer) progressoContainer.classList.remove('hidden');
            // Ocultar cards de upload
            const uploadSection = document.querySelector('.grid.grid-cols-1.lg\\:grid-cols-2.gap-6');
            if (uploadSection) uploadSection.classList.add('hidden');
            // Ocultar seção de relatórios RAF
            const rafSection = document.getElementById('raf-relatorios-section');
            if (rafSection) rafSection.classList.add('hidden');
        }

        // Função para ocultar UI de progresso
        function ocultarProgresso() {
            if (progressoContainer) progressoContainer.classList.add('hidden');
            // Mostrar cards de upload
            const uploadSection = document.querySelector('.grid.grid-cols-1.lg\\:grid-cols-2.gap-6');
            if (uploadSection) uploadSection.classList.remove('hidden');
            // Mostrar seção de relatórios RAF
            const rafSection = document.getElementById('raf-relatorios-section');
            if (rafSection) rafSection.classList.remove('hidden');
        }

        // Função para resetar UI de progresso
        function resetarProgresso() {
            // Resetar barra de progresso
            if (barraProgresso) {
                barraProgresso.style.width = '0%';
                barraProgresso.className = 'bg-blue-600 h-full rounded-full transition-all duration-500 ease-out';
            }
            if (progressoPorcentagem) progressoPorcentagem.textContent = '0%';
            if (progressoMensagem) progressoMensagem.textContent = 'Iniciando...';

            // Resetar header
            if (progressoEmpresa) progressoEmpresa.textContent = 'Aguardando dados...';
            if (progressoDocumento) {
                progressoDocumento.textContent = '';
                progressoDocumento.classList.add('hidden');
            }

            // Resetar ícone e card para estado inicial (processando)
            atualizarIconeStatus('processando');

            // Ocultar seção de erro
            if (progressoErro) progressoErro.classList.add('hidden');

            // Ocultar seção de resultados
            const resultadoImportacao = document.getElementById('resultado-importacao');
            if (resultadoImportacao) resultadoImportacao.classList.add('hidden');
        }

        // Elementos da seção de resultados
        const resultadoContainer = document.getElementById('resultado-importacao');
        const resultadoEmpresa = document.getElementById('resultado-empresa');
        const resultadoTotalCnpjs = document.getElementById('resultado-total-cnpjs');
        const resultadoTotalCpfs = document.getElementById('resultado-total-cpfs');
        const resultadoNovos = document.getElementById('resultado-novos');
        const resultadoDuplicados = document.getElementById('resultado-duplicados');
        const btnNovaImportacao = document.getElementById('btn-nova-importacao');
        const btnCarregarParticipantes = document.getElementById('btn-carregar-participantes');
        const linkFiltrarImportacao = document.getElementById('link-filtrar-importacao');
        const listaParticipantesContainer = document.getElementById('lista-participantes-container');
        const listaParticipantesLoading = document.getElementById('lista-participantes-loading');
        const listaParticipantesTabela = document.getElementById('lista-participantes-tabela');
        const participantesTbody = document.getElementById('participantes-tbody-resultado');

        // Variável para guardar o ID da importação atual e IDs dos participantes
        let importacaoAtualId = null;
        let participanteIdsFromSSE = null; // Array de IDs recebidos do n8n via SSE
        let participantesPage = 1;
        let participantesTotal = 0;

        // Função para mostrar seção de resultados após importação concluída
        function mostrarResultadoImportacao(dados) {
            console.log('[Monitoramento SPED] mostrarResultadoImportacao - dados recebidos:', dados);
            console.log('[Monitoramento SPED] resultadoContainer existe?', !!resultadoContainer);

            if (!resultadoContainer) {
                console.error('[Monitoramento SPED] resultadoContainer NAO ENCONTRADO!');
                return;
            }

            // Preencher dados
            console.log('[Monitoramento SPED] Preenchendo cards...');
            console.log('[Monitoramento SPED] total_cnpjs:', dados.total_cnpjs);
            console.log('[Monitoramento SPED] total_cpfs:', dados.total_cpfs);
            console.log('[Monitoramento SPED] novos_salvos:', dados.novos_salvos);
            console.log('[Monitoramento SPED] duplicados_identificados:', dados.duplicados_identificados);
            console.log('[Monitoramento SPED] participante_ids:', dados.participante_ids);

            if (resultadoEmpresa) {
                resultadoEmpresa.textContent = dados.nome_empresa || 'Importação concluída';
            }
            if (resultadoTotalCnpjs) {
                const valor = dados.total_cnpjs || dados.total_cnpjs_unicos || 0;
                console.log('[Monitoramento SPED] Setando CNPJs para:', valor);
                resultadoTotalCnpjs.textContent = valor;
            }
            if (resultadoTotalCpfs) {
                const valor = dados.total_cpfs || dados.total_cpfs_unicos || 0;
                console.log('[Monitoramento SPED] Setando CPFs para:', valor);
                resultadoTotalCpfs.textContent = valor;
            }
            if (resultadoNovos) {
                const valor = dados.novos_salvos || dados.total_a_analisar || dados.novos || 0;
                console.log('[Monitoramento SPED] Setando Novos para:', valor);
                resultadoNovos.textContent = valor;
            }
            if (resultadoDuplicados) {
                const valor = dados.duplicados_identificados || dados.total_duplicados || dados.registros_duplicados_documento || dados.duplicados || 0;
                console.log('[Monitoramento SPED] Setando Duplicados para:', valor);
                resultadoDuplicados.textContent = valor;
            }

            // Guardar ID da importação se disponível nos dados do SSE
            if (dados.importacao_id) {
                importacaoAtualId = dados.importacao_id;
                console.log('[Monitoramento SPED] importacaoAtualId setado para:', importacaoAtualId);
            }

            // Guardar IDs dos participantes se disponível (enviados pelo n8n)
            if (dados.participante_ids && Array.isArray(dados.participante_ids)) {
                participanteIdsFromSSE = dados.participante_ids;
                console.log('[Monitoramento SPED] participanteIdsFromSSE setado, total:', participanteIdsFromSSE.length);
            }

            // Atualizar link de filtro se temos o ID da importação (do SSE ou do upload inicial)
            if (importacaoAtualId && linkFiltrarImportacao) {
                linkFiltrarImportacao.href = '/app/monitoramento/participantes?importacao=' + importacaoAtualId;
            }

            // Resetar lista de participantes
            if (listaParticipantesContainer) listaParticipantesContainer.classList.remove('hidden');
            if (listaParticipantesLoading) listaParticipantesLoading.classList.add('hidden');
            if (listaParticipantesTabela) listaParticipantesTabela.classList.add('hidden');
            participantesPage = 1;

            // Mostrar seção de resultados
            resultadoContainer.classList.remove('hidden');

            // Scroll para a seção de resultados
            resultadoContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Carregar participantes automaticamente se temos IDs
            if (participanteIdsFromSSE && participanteIdsFromSSE.length > 0) {
                carregarParticipantes();
            }
        }

        // Função para carregar lista de participantes
        async function carregarParticipantes() {
            // Verificar se temos IDs dos participantes (via SSE) ou ID da importação
            if (!participanteIdsFromSSE && !importacaoAtualId) {
                console.warn('[Monitoramento SPED] Nenhum ID disponível para carregar participantes');
                return;
            }

            // Mostrar loading
            if (listaParticipantesContainer) listaParticipantesContainer.classList.add('hidden');
            if (listaParticipantesLoading) listaParticipantesLoading.classList.remove('hidden');
            if (listaParticipantesTabela) listaParticipantesTabela.classList.add('hidden');

            try {
                let response;

                // Priorizar uso de participante_ids se disponível (mais direto)
                if (participanteIdsFromSSE && participanteIdsFromSSE.length > 0) {
                    response = await fetch('/app/monitoramento/participantes/por-ids', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({
                            ids: participanteIdsFromSSE,
                            importacao_id: importacaoAtualId,
                            page: participantesPage,
                        }),
                    });
                } else {
                    // Fallback: buscar por ID da importação
                    response = await fetch('/app/monitoramento/participantes/por-importacao/' + importacaoAtualId + '?page=' + participantesPage, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                }

                if (!response.ok) {
                    throw new Error('Erro ao carregar participantes: HTTP ' + response.status);
                }

                const data = await response.json();

                // Preencher tabela
                preencherTabelaParticipantes(data.participantes || []);
                participantesTotal = data.total || 0;

                // Atualizar paginação
                atualizarPaginacao(data);

                // Mostrar tabela
                if (listaParticipantesLoading) listaParticipantesLoading.classList.add('hidden');
                if (listaParticipantesTabela) listaParticipantesTabela.classList.remove('hidden');

            } catch (err) {
                console.error('[Monitoramento SPED] Erro ao carregar participantes:', err);
                if (listaParticipantesLoading) listaParticipantesLoading.classList.add('hidden');
                if (listaParticipantesContainer) {
                    listaParticipantesContainer.classList.remove('hidden');
                    listaParticipantesContainer.innerHTML = '<div class="text-center py-8 text-red-500"><p class="text-sm">Erro ao carregar participantes. Tente novamente.</p></div>';
                }
            }
        }

        // Função para preencher tabela de participantes
        function preencherTabelaParticipantes(participantes) {
            if (!participantesTbody) return;

            participantesTbody.innerHTML = '';

            if (participantes.length === 0) {
                participantesTbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 text-sm">Nenhum participante encontrado.</td></tr>';
                return;
            }

            // Separar novos e duplicados
            const novos = participantes.filter(p => p.is_novo);
            const duplicados = participantes.filter(p => p.is_duplicado);

            // Cabeçalho "Novos" se houver
            if (novos.length > 0) {
                const headerTr = document.createElement('tr');
                headerTr.innerHTML = `<td colspan="4" class="px-4 py-2 bg-green-50 border-b border-green-200">
                    <span class="text-xs font-semibold text-green-700 uppercase">Novos participantes (${novos.length})</span>
                </td>`;
                participantesTbody.appendChild(headerTr);
                novos.forEach(p => renderRow(p, false));
            }

            // Cabeçalho "Duplicados" se houver
            if (duplicados.length > 0) {
                const headerTr = document.createElement('tr');
                headerTr.innerHTML = `<td colspan="4" class="px-4 py-2 bg-amber-50 border-t border-b border-amber-200">
                    <span class="text-xs font-semibold text-amber-600 uppercase">Ja cadastrados (${duplicados.length})</span>
                </td>`;
                participantesTbody.appendChild(headerTr);
                duplicados.forEach(p => renderRow(p, true));
            }

            function renderRow(p, isDuplicado) {
                const cnpjFormatado = p.cnpj ? p.cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5') : '-';
                const situacaoClass = p.situacao_cadastral === 'ATIVA'
                    ? 'bg-green-100 text-green-700'
                    : (p.situacao_cadastral ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700');

                const tr = document.createElement('tr');
                tr.className = isDuplicado ? 'hover:bg-gray-50 opacity-50' : 'hover:bg-gray-50';
                tr.innerHTML = `
                    <td class="px-4 py-3 text-sm font-mono text-gray-900">${cnpjFormatado}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 max-w-xs truncate" title="${p.razao_social || ''}">${p.razao_social || '-'}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium ${situacaoClass}">
                            ${p.situacao_cadastral || '-'}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="/app/monitoramento/participante/${p.id}" class="text-blue-600 hover:text-blue-700 text-sm font-medium" data-link>Ver</a>
                    </td>
                `;
                participantesTbody.appendChild(tr);
            }
        }

        // Função para atualizar paginação
        function atualizarPaginacao(data) {
            const infoEl = document.getElementById('participantes-info');
            const btnPrev = document.getElementById('btn-prev-page');
            const btnNext = document.getElementById('btn-next-page');

            if (infoEl) {
                const start = ((data.current_page || 1) - 1) * (data.per_page || 10) + 1;
                const end = Math.min(start + (data.participantes?.length || 0) - 1, data.total || 0);
                infoEl.textContent = 'Mostrando ' + start + '-' + end + ' de ' + (data.total || 0);
            }

            if (btnPrev) {
                btnPrev.disabled = !data.prev_page_url;
                btnPrev.onclick = function() {
                    if (data.prev_page_url) {
                        participantesPage--;
                        carregarParticipantes();
                    }
                };
            }

            if (btnNext) {
                btnNext.disabled = !data.next_page_url;
                btnNext.onclick = function() {
                    if (data.next_page_url) {
                        participantesPage++;
                        carregarParticipantes();
                    }
                };
            }
        }

        // Event listeners para seção de resultados
        if (btnNovaImportacao) {
            btnNovaImportacao.addEventListener('click', function() {
                // Resetar flag de importação em andamento (CRÍTICO)
                importacaoEmAndamento = false;
                // Fechar SSE se ainda estiver aberto
                if (eventSourceTxt) {
                    eventSourceTxt.close();
                    eventSourceTxt = null;
                }
                // Ocultar seção de resultados
                if (resultadoContainer) resultadoContainer.classList.add('hidden');
                // Ocultar seção de progresso
                ocultarProgresso();
                // Resetar formulário
                resetarProgresso();
                // Limpar IDs armazenados
                importacaoAtualId = null;
                participanteIdsFromSSE = null;
                // Limpar arquivo selecionado
                if (txtFileInput) txtFileInput.value = '';
                const txtFileMeta = document.getElementById('txt-file-meta');
                if (txtFileMeta) txtFileMeta.classList.add('hidden');
                const txtDropzone = document.getElementById('txt-dropzone');
                if (txtDropzone) txtDropzone.classList.remove('hidden');
                // Habilitar botão importar
                if (txtImportarBtn) {
                    txtImportarBtn.disabled = true;
                    txtImportarBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> Importar';
                }
            });
        }

        if (btnCarregarParticipantes) {
            btnCarregarParticipantes.addEventListener('click', carregarParticipantes);
        }

        // Função para conectar ao SSE (novo formato com tab_id)
        function conectarSSE() {
            if (eventSourceTxt) {
                eventSourceTxt.close();
            }

            const sseUrl = '/app/monitoramento/progresso/stream?tab_id=' + encodeURIComponent(tabId);
            console.log('[Monitoramento SPED] Conectando ao SSE:', sseUrl);
            eventSourceTxt = new EventSource(sseUrl);

            eventSourceTxt.onopen = function() {
                console.log('[Monitoramento SPED] SSE conectado');
            };

            eventSourceTxt.onmessage = function(event) {
                try {
                    const dados = JSON.parse(event.data);
                    console.log('[Monitoramento SPED] Dados SSE:', dados);
                    atualizarProgresso(dados);

                    if (dados.status === 'concluido') {
                        eventSourceTxt.close();
                        eventSourceTxt = null;
                        importacaoEmAndamento = false;

                        // Usa mensagem do n8n ou monta mensagem com dados
                        const dadosN8n = dados.dados || {};
                        console.log('[Monitoramento SPED] Status concluido - dadosN8n:', dadosN8n);
                        const totalImportados = dadosN8n.novos_salvos || dadosN8n.total_a_analisar || 0;
                        const mensagemSucesso = dados.mensagem || ('Importação concluída! ' + totalImportados + ' novos participantes adicionados.');

                        if (window.showToast) {
                            window.showToast(mensagemSucesso, 'success');
                        }

                        // Mostrar seção de resultados em vez de redirecionar
                        console.log('[Monitoramento SPED] Chamando mostrarResultadoImportacao com:', dadosN8n);
                        mostrarResultadoImportacao(dadosN8n);
                    } else if (dados.status === 'erro' || dados.status === 'timeout') {
                        eventSourceTxt.close();
                        eventSourceTxt = null;
                        importacaoEmAndamento = false;

                        // Erro/timeout é tratado pelo atualizarProgresso que mostra a seção de erro
                        // Não redireciona automaticamente - usuário decide via botão "Tentar Novamente"
                    }
                } catch (e) {
                    console.error('[Monitoramento SPED] Erro ao parsear SSE:', e);
                }
            };

            eventSourceTxt.onerror = function(err) {
                console.error('[Monitoramento SPED] Erro SSE:', err);
                eventSourceTxt.close();
                eventSourceTxt = null;

                // Se ainda estava em andamento, mostrar seção de erro
                if (importacaoEmAndamento) {
                    importacaoEmAndamento = false;
                    // Atualiza UI para mostrar erro de conexão
                    atualizarProgresso({
                        status: 'erro',
                        progresso: 0,
                        mensagem: 'Erro na conexão',
                        error_message: 'Erro na conexão com o servidor. Verifique sua internet e tente novamente.'
                    });
                }
            };
        }

        // Botão importar - funcionalidade real
        if (txtImportarBtn) {
            txtImportarBtn.addEventListener('click', async function() {
                const tipoSped = getSelectedTipoSped();
                if (!tipoSped) {
                    if (window.showToast) {
                        window.showToast('Selecione o tipo de SPED antes de importar.', 'error');
                    } else {
                        alert('Selecione o tipo de SPED antes de importar.');
                    }
                    return;
                }

                if (!txtFileInput || !txtFileInput.files || txtFileInput.files.length === 0) {
                    if (window.showToast) {
                        window.showToast('Selecione um arquivo .txt para importar.', 'error');
                    } else {
                        alert('Selecione um arquivo .txt para importar.');
                    }
                    return;
                }

                if (importacaoEmAndamento) {
                    if (window.showToast) {
                        window.showToast('Aguarde a importação em andamento terminar.', 'warning');
                    }
                    return;
                }

                // Desabilitar botão e mostrar loading
                txtImportarBtn.disabled = true;
                txtImportarBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg> Enviando...';

                try {
                    const formData = new FormData();
                    formData.append('file', txtFileInput.files[0]);
                    formData.append('tipo_efd', tipoSped === 'efd-fiscal' ? 'EFD Fiscal' : 'EFD Contribuições');
                    formData.append('tab_id', tabId);

                    const clienteSelect = document.getElementById('cliente-select');
                    if (clienteSelect && clienteSelect.value) {
                        formData.append('cliente_id', clienteSelect.value);
                    }

                    const response = await fetch('/app/sped/upload', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: formData,
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.error || data.message || 'Erro ao enviar arquivo');
                    }

                    console.log('[Monitoramento SPED] Arquivo enviado com tab_id:', tabId);

                    // Guardar ID da importação retornado pelo SpedUploadController
                    if (data.importacao_id) {
                        importacaoAtualId = data.importacao_id;
                        console.log('[Monitoramento SPED] Importação ID:', importacaoAtualId);
                    }

                    // Marcar como em andamento
                    importacaoEmAndamento = true;

                    // Mostrar UI de progresso
                    resetarProgresso();
                    mostrarProgresso();

                    // Conectar ao SSE para receber atualizações (usa tabId do escopo)
                    conectarSSE();

                } catch (err) {
                    console.error('[Monitoramento SPED] Erro ao enviar arquivo:', err);
                    if (window.showToast) {
                        window.showToast(err.message || 'Erro ao enviar arquivo.', 'error');
                    } else {
                        alert(err.message || 'Erro ao enviar arquivo.');
                    }
                } finally {
                    // Restaurar botão
                    txtImportarBtn.disabled = false;
                    txtImportarBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> Importar';
                    updateImportButtonState();
                }
            });
        }

        // Cleanup ao sair da página (para SPA)
        if (window._cleanupFunctions) {
            window._cleanupFunctions.push(function() {
                if (eventSourceTxt) {
                    eventSourceTxt.close();
                    eventSourceTxt = null;
                }
            });
        }

        // Botão "Tentar Novamente" na seção de erro
        const btnTentarNovamente = document.getElementById('btn-tentar-novamente');
        if (btnTentarNovamente) {
            btnTentarNovamente.addEventListener('click', function() {
                // Resetar flag de importação em andamento (CRÍTICO)
                importacaoEmAndamento = false;
                // Fechar SSE se ainda estiver aberto
                if (eventSourceTxt) {
                    eventSourceTxt.close();
                    eventSourceTxt = null;
                }
                ocultarProgresso();
                limparArquivoTxt();
                resetarProgresso();
                // Limpar IDs armazenados
                importacaoAtualId = null;
                participanteIdsFromSSE = null;
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
