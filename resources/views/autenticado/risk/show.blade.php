{{-- Risk Score - Detalhes do Participante --}}
<div class="min-h-screen bg-gray-50" id="risk-detail-container">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Breadcrumb --}}
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="/app/score-fiscal" data-link class="hover:text-blue-600">Score Fiscal</a></li>
                <li><span>/</span></li>
                <li class="text-gray-900 font-medium">{{ $participante->razao_social ?? 'Participante' }}</li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $participante->razao_social ?? 'N/A' }}</h1>
                    @if($participante->nome_fantasia)
                        <p class="text-gray-600">{{ $participante->nome_fantasia }}</p>
                    @endif
                    <p class="mt-1 text-sm text-gray-500">CNPJ: {{ $participante->cnpj_formatado }}</p>
                </div>
                <div class="flex items-center gap-4">
                    @if($score)
                        <div class="text-center">
                            <div class="text-4xl font-bold {{ $score->score_total >= 80 ? 'text-red-600' : ($score->score_total >= 50 ? 'text-orange-600' : ($score->score_total >= 20 ? 'text-yellow-600' : 'text-green-600')) }}">
                                {{ $score->score_total }}
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $score->classificacao_badge_class }}">
                                {{ $score->classificacao_label }}
                            </span>
                        </div>
                    @else
                        <div class="text-center">
                            <div class="text-4xl font-bold text-gray-400">-</div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                                Nao Avaliado
                            </span>
                        </div>
                    @endif
                    <button type="button" id="btn-consultar" data-id="{{ $participante->id }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Atualizar Score
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Informacoes do Participante --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informacoes Cadastrais</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm text-gray-500">Situacao Cadastral</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $participante->situacao_cadastral ?? 'Nao informado' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Regime Tributario</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $participante->regime_tributario ?? 'Nao informado' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">UF / Municipio</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $participante->uf ?? '-' }} / {{ $participante->municipio ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">CEP</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $participante->cep ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Telefone</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $participante->telefone ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Origem</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $participante->origem_tipo ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Volume de Transacoes --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Volume de Transacoes</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm text-gray-500">Como Fornecedor (Emitente)</dt>
                            <dd class="text-lg font-bold text-blue-600">R$ {{ number_format($volumeEmitente ?? 0, 2, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Como Cliente (Destinatario)</dt>
                            <dd class="text-lg font-bold text-green-600">R$ {{ number_format($volumeDestinatario ?? 0, 2, ',', '.') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Detalhes do Score --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalhamento do Score</h3>

                    @if($score)
                        <div class="space-y-4">
                            @foreach($score->scores_detalhados as $key => $item)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $item['label'] }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-500">Peso: {{ ($pesos[$key] ?? 0) * 100 }}%</span>
                                        <span class="text-sm font-bold {{ $item['score'] >= 80 ? 'text-red-600' : ($item['score'] >= 50 ? 'text-orange-600' : ($item['score'] >= 20 ? 'text-yellow-600' : 'text-green-600')) }}">
                                            {{ $item['score'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $item['score'] >= 80 ? 'bg-red-500' : ($item['score'] >= 50 ? 'bg-orange-500' : ($item['score'] >= 20 ? 'bg-yellow-500' : 'bg-green-500')) }}" style="width: {{ $item['score'] }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Legenda --}}
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Legenda dos Scores</h4>
                            <div class="flex flex-wrap gap-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                    <span class="text-gray-600">0-20: Baixo Risco</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                    <span class="text-gray-600">21-50: Medio Risco</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                                    <span class="text-gray-600">51-80: Alto Risco</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    <span class="text-gray-600">81-100: Critico</span>
                                </div>
                            </div>
                        </div>

                        {{-- Ultima atualizacao --}}
                        @if($score->ultima_consulta_em)
                        <div class="mt-4 text-sm text-gray-500">
                            Ultima atualizacao: {{ $score->ultima_consulta_em->format('d/m/Y H:i') }}
                            @if($score->isDesatualizado())
                                <span class="ml-2 text-amber-600">(Score desatualizado - mais de 30 dias)</span>
                            @endif
                        </div>
                        @endif

                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <h4 class="mt-4 text-lg font-medium text-gray-900">Score nao calculado</h4>
                            <p class="mt-2 text-sm text-gray-500">Clique em "Atualizar Score" para calcular o risco deste participante.</p>
                        </div>
                    @endif
                </div>

                {{-- Historico de Consultas (futuro) --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Dados da Ultima Consulta</h3>
                    @if($score && $score->dados_consultados)
                        <pre class="text-xs bg-gray-50 p-4 rounded-lg overflow-x-auto">{{ json_encode($score->dados_consultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    @else
                        <p class="text-sm text-gray-500">Nenhuma consulta realizada ainda.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/risk-score.js') }}"></script>
