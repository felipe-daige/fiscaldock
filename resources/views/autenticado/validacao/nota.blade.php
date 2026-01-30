{{-- Validacao Contabil - Detalhes da Nota --}}
<div class="min-h-screen bg-gray-50" id="validacao-nota-container">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <a href="/app/validacao" data-link class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Voltar
            </a>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Nota Fiscal #{{ $nota->numero_nota }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $nota->emit_razao_social ?? $nota->emit_cnpj }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($validacao['preview'] ?? false)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                            Preview (nao salvo)
                        </span>
                        <button type="button" id="btn-salvar-validacao" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm hover:bg-blue-700 transition" data-nota-id="{{ $nota->id }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Salvar Validacao
                        </button>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $nota->validacao_badge_class }}">
                            {{ $nota->validacao_classificacao_label }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Score Card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Score de Validacao</h3>
                    <p class="text-sm text-gray-500">Quanto menor o score, mais conforme esta a nota</p>
                </div>
                <div class="text-right">
                    <p class="text-4xl font-bold {{ ($validacao['score_total'] ?? 0) >= 50 ? 'text-red-600' : (($validacao['score_total'] ?? 0) >= 30 ? 'text-orange-600' : (($validacao['score_total'] ?? 0) >= 10 ? 'text-yellow-600' : 'text-green-600')) }}">
                        {{ $validacao['score_total'] ?? 0 }}
                    </p>
                    <p class="text-sm text-gray-500">de 100</p>
                </div>
            </div>

            {{-- Barra de Score --}}
            <div class="relative h-4 bg-gray-200 rounded-full overflow-hidden mb-6">
                <div class="absolute inset-0 bg-gradient-to-r from-green-500 via-yellow-500 to-red-500"></div>
                <div class="absolute top-0 h-full bg-gray-200" style="left: {{ $validacao['score_total'] ?? 0 }}%; right: 0;"></div>
                <div class="absolute top-0 w-1 h-full bg-gray-800" style="left: {{ $validacao['score_total'] ?? 0 }}%;"></div>
            </div>

            {{-- Scores por Categoria --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($categorias ?? [] as $key => $categoria)
                @php $scoreCategoria = $validacao['scores'][$key] ?? 0; @endphp
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">{{ $categoria['nome'] }}</p>
                    <p class="text-lg font-bold {{ $scoreCategoria >= 50 ? 'text-red-600' : ($scoreCategoria >= 30 ? 'text-orange-600' : ($scoreCategoria >= 10 ? 'text-yellow-600' : 'text-green-600')) }}">
                        {{ $scoreCategoria }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Alertas --}}
        @if(count($validacao['alertas'] ?? []) > 0)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Alertas ({{ count($validacao['alertas']) }})</h3>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach($validacao['alertas'] as $alerta)
                @php
                    $nivelClass = match($alerta['nivel']) {
                        'bloqueante' => 'bg-red-100 text-red-800 border-red-200',
                        'atencao' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                        'info' => 'bg-blue-100 text-blue-800 border-blue-200',
                        default => 'bg-gray-100 text-gray-800 border-gray-200',
                    };
                    $iconClass = match($alerta['nivel']) {
                        'bloqueante' => 'text-red-500',
                        'atencao' => 'text-yellow-500',
                        'info' => 'text-blue-500',
                        default => 'text-gray-500',
                    };
                @endphp
                <div class="px-6 py-4">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 mt-0.5">
                            @if($alerta['nivel'] === 'bloqueante')
                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            @elseif($alerta['nivel'] === 'atencao')
                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @else
                            <svg class="w-5 h-5 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $nivelClass }}">
                                    {{ ucfirst($alerta['nivel']) }}
                                </span>
                                <span class="text-xs text-gray-500">{{ $alerta['categoria'] }}</span>
                                <span class="text-xs text-gray-400">{{ $alerta['codigo'] }}</span>
                            </div>
                            <p class="text-sm font-medium text-gray-900">{{ $alerta['mensagem'] }}</p>
                            @if(!empty($alerta['detalhe']))
                            <p class="text-sm text-gray-600 mt-1">{{ $alerta['detalhe'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h4 class="font-semibold text-green-800">Nenhum alerta encontrado</h4>
                    <p class="text-sm text-green-700">Esta nota fiscal esta conforme com as regras de validacao.</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Dados da Nota --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Identificacao --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h4 class="font-semibold text-gray-900 mb-4">Identificacao</h4>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Numero</dt>
                        <dd class="font-medium text-gray-900">{{ $nota->numero_nota }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Serie</dt>
                        <dd class="font-medium text-gray-900">{{ $nota->serie }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Emissao</dt>
                        <dd class="font-medium text-gray-900">{{ $nota->data_emissao?->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Tipo</dt>
                        <dd class="font-medium text-gray-900">{{ $nota->tipo_nota_descricao }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Finalidade</dt>
                        <dd class="font-medium text-gray-900">{{ $nota->finalidade_descricao }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Chave de Acesso</dt>
                        <dd class="font-mono text-xs text-gray-900 break-all">{{ $nota->chave_acesso }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Valores --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h4 class="font-semibold text-gray-900 mb-4">Valores</h4>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Valor Total</dt>
                        <dd class="font-medium text-gray-900">{{ $nota->valor_formatado }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">ICMS</dt>
                        <dd class="font-medium text-gray-900">R$ {{ number_format($nota->icms_valor ?? 0, 2, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">ICMS-ST</dt>
                        <dd class="font-medium text-gray-900">R$ {{ number_format($nota->icms_st_valor ?? 0, 2, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">PIS</dt>
                        <dd class="font-medium text-gray-900">R$ {{ number_format($nota->pis_valor ?? 0, 2, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">COFINS</dt>
                        <dd class="font-medium text-gray-900">R$ {{ number_format($nota->cofins_valor ?? 0, 2, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">IPI</dt>
                        <dd class="font-medium text-gray-900">R$ {{ number_format($nota->ipi_valor ?? 0, 2, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between pt-3 border-t border-gray-200">
                        <dt class="text-gray-700 font-medium">Total Tributos</dt>
                        <dd class="font-bold text-gray-900">R$ {{ number_format($nota->tributos_total ?? 0, 2, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Emitente --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h4 class="font-semibold text-gray-900 mb-4">Emitente</h4>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">CNPJ</dt>
                        <dd class="font-medium text-gray-900">{{ $nota->emit_cnpj_formatado }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Razao Social</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $nota->emit_razao_social ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">UF</dt>
                        <dd class="font-medium text-gray-900">{{ $nota->emit_uf ?? '-' }}</dd>
                    </div>
                    @if($nota->emitente)
                    <div class="pt-2">
                        <a href="/app/risk/participante/{{ $nota->emit_participante_id }}" data-link class="text-sm text-blue-600 hover:text-blue-800">
                            Ver score de risco do emitente
                        </a>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Destinatario --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h4 class="font-semibold text-gray-900 mb-4">Destinatario</h4>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">CNPJ</dt>
                        <dd class="font-medium text-gray-900">{{ $nota->dest_cnpj_formatado }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Razao Social</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $nota->dest_razao_social ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">UF</dt>
                        <dd class="font-medium text-gray-900">{{ $nota->dest_uf ?? '-' }}</dd>
                    </div>
                    @if($nota->destinatario)
                    <div class="pt-2">
                        <a href="/app/risk/participante/{{ $nota->dest_participante_id }}" data-link class="text-sm text-blue-600 hover:text-blue-800">
                            Ver score de risco do destinatario
                        </a>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Metadados da Validacao --}}
        @if(!($validacao['preview'] ?? false))
        <div class="mt-6 text-sm text-gray-500 text-center">
            Validado em {{ \Carbon\Carbon::parse($validacao['validado_em'])->format('d/m/Y H:i:s') }}
        </div>
        @endif
    </div>
</div>

<script src="{{ asset('js/validacao.js') }}"></script>
