{{-- Risk Score - Detalhes do Participante (DANFE Modernizado) --}}
@php
    // Cor/label vêm da CLASSIFICAÇÃO PERSISTIDA (RiskScoreService), não da faixa numérica:
    // o piso por certidão positiva/situação baixada pode elevar a classificação acima do
    // que o score numérico sugere — a view precisa refletir o mesmo veredito da listagem.
    $riskSvc = app(\App\Services\RiskScoreService::class);
    $situacaoUpper = strtoupper((string) ($participante->situacao_cadastral ?? ''));
    $situacaoBadge = match($situacaoUpper) {
        'ATIVA', '02' => ['label' => 'ATIVA', 'hex' => '#047857'],
        'INAPTA', 'SUSPENSA', 'NULA' => ['label' => $situacaoUpper, 'hex' => '#dc2626'],
        'BAIXADA' => ['label' => 'BAIXADA', 'hex' => '#9ca3af'],
        default => $situacaoUpper ? ['label' => $situacaoUpper, 'hex' => '#6b7280'] : null,
    };
    $regimeUpper = strtoupper((string) ($participante->regime_tributario ?? ''));
    $regimeBadge = match($regimeUpper) {
        'SIMPLES NACIONAL', 'SIMPLES' => ['label' => $regimeUpper, 'hex' => '#0f766e'],
        'LUCRO PRESUMIDO' => ['label' => $regimeUpper, 'hex' => '#d97706'],
        'LUCRO REAL' => ['label' => $regimeUpper, 'hex' => '#374151'],
        default => $regimeUpper ? ['label' => $regimeUpper, 'hex' => '#6b7280'] : null,
    };
@endphp
<div class="min-h-screen bg-gray-100" id="risk-detail-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        {{-- Breadcrumb --}}
        <nav class="mb-4">
            <ol class="flex items-center gap-2 text-[11px] text-gray-500 uppercase tracking-wide">
                <li><a href="/app/score-fiscal" data-link class="hover:text-gray-900 hover:underline">Score Fiscal</a></li>
                <li><span>/</span></li>
                <li class="text-gray-900 font-semibold">{{ $participante->razao_social ?? 'Participante' }}</li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Identificação</span>
            </div>
            <div class="p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide break-words">{{ $participante->razao_social ?? 'N/A' }}</h1>
                    @if($participante->nome_fantasia)
                        <p class="text-sm text-gray-600">{{ $participante->nome_fantasia }}</p>
                    @endif
                    <div class="mt-1 flex items-center gap-2 flex-wrap">
                        <p class="text-xs text-gray-500 font-mono">CNPJ: {{ $participante->cnpj_formatado }}</p>
                        @if($situacaoBadge)
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $situacaoBadge['hex'] }}">{{ $situacaoBadge['label'] }}</span>
                        @endif
                        @if($regimeBadge)
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $regimeBadge['hex'] }}">{{ $regimeBadge['label'] }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-between md:justify-end gap-4 w-full md:w-auto">
                    @if($score && ($score->score_total !== null || in_array($score->classificacao, ['medio','alto','critico'], true)))
                        @php
                            $classif = $score->classificacao ?? 'nao_avaliado';
                            $inconclusivo = $classif === 'inconclusivo';
                            $hex = $riskSvc->getCorClassificacao($classif);
                            // Piso aplicado: classificação persistida acima da faixa numérica do total.
                            $classifNumerica = $riskSvc->classificar($score->score_total);
                            $pisoAplicado = ! $inconclusivo && $score->score_total !== null && $classif !== $classifNumerica;
                        @endphp
                        <div class="text-center flex-shrink-0">
                            <div class="text-3xl font-bold font-mono" style="color: {{ $hex }}">{{ $inconclusivo || $score->score_total === null ? '—' : $score->score_total }}</div>
                            <span class="whitespace-nowrap inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white mt-1" style="background-color: {{ $hex }}">
                                {{ $riskSvc->getLabelClassificacao($classif) }}
                            </span>
                            @if($inconclusivo)
                                <p class="mt-1 text-[10px] text-gray-500 leading-tight max-w-[190px]">Cobertura insuficiente — exige CND Federal + 2 certidões avaliadas.</p>
                            @elseif($pisoAplicado)
                                <p class="mt-1 text-[10px] leading-tight max-w-[190px]" style="color: {{ $hex }}">Classificação elevada por irregularidade conhecida (certidão positiva ou situação cadastral) — o piso vence a média.</p>
                            @endif
                        </div>
                    @else
                        <div class="text-center flex-shrink-0">
                            <div class="text-3xl font-bold text-gray-400 font-mono">—</div>
                            <span class="whitespace-nowrap inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white mt-1" style="background-color: #9ca3af">
                                Não Avaliado
                            </span>
                        </div>
                    @endif
                    <div class="flex flex-col gap-2 flex-shrink-0">
                        <a href="/app/consulta" data-link class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded bg-gray-800 hover:bg-gray-700 text-white text-xs font-semibold transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span class="hidden sm:inline">Atualizar via Consulta</span>
                            <span class="sm:hidden">Consultar</span>
                        </a>
                        <a href="/app/participante/{{ $participante->id }}" data-link class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-semibold transition">
                            Ficha completa
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Informacoes do Participante --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Informações Cadastrais</span>
                    </div>
                    <dl class="divide-y divide-gray-100">
                        <div class="px-4 py-3">
                            <dt class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Situação Cadastral</dt>
                            <dd class="text-sm text-gray-700 mt-0.5">
                                @if($situacaoBadge)
                                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $situacaoBadge['hex'] }}">{{ $situacaoBadge['label'] }}</span>
                                @else
                                    Não informado
                                @endif
                            </dd>
                        </div>
                        <div class="px-4 py-3">
                            <dt class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Regime Tributário</dt>
                            <dd class="text-sm text-gray-700 mt-0.5">{{ $participante->regime_tributario ?? 'Não informado' }}</dd>
                        </div>
                        <div class="px-4 py-3">
                            <dt class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">UF / Município</dt>
                            <dd class="text-sm text-gray-700 mt-0.5">{{ $participante->uf ?? '-' }} / {{ $participante->municipio ?? '-' }}</dd>
                        </div>
                        <div class="px-4 py-3">
                            <dt class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">CEP</dt>
                            <dd class="text-sm text-gray-700 mt-0.5 font-mono">{{ $participante->cep ?? '-' }}</dd>
                        </div>
                        <div class="px-4 py-3">
                            <dt class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Telefone</dt>
                            <dd class="text-sm text-gray-700 mt-0.5">{{ $participante->telefone ?? '-' }}</dd>
                        </div>
                        <div class="px-4 py-3">
                            <dt class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Origem</dt>
                            <dd class="text-sm text-gray-700 mt-0.5">{{ $participante->origem_tipo ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Volume de Transacoes (acervo EFD) --}}
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Volume de Transações</span>
                    </div>
                    <dl class="divide-y divide-gray-100">
                        <div class="px-4 py-3">
                            <dt class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Movimentado em notas (EFD)</dt>
                            <dd class="text-sm font-semibold text-gray-900 font-mono mt-0.5">R$&nbsp;{{ number_format($volumeEfd ?? 0, 2, ',', '.') }}</dd>
                            @if(($volumeEfd ?? 0) > 0)
                                <a href="/app/participante/{{ $participante->id }}/notas" data-link class="mt-1 inline-block text-[11px] text-gray-600 hover:text-gray-900 hover:underline">Ver notas do participante</a>
                            @endif
                        </div>
                    </dl>
                </div>

                {{-- Crédito IBS/CBS (Reforma Tributária) --}}
                @php
                    $cr = $creditoReforma ?? null;
                    $crCor = ([
                        'verde' => '#047857', 'amarelo' => '#d97706', 'vermelho' => '#b91c1c', 'cinza' => '#9ca3af',
                    ])[$cr['flag'] ?? 'cinza'] ?? '#9ca3af';
                @endphp
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-2">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Crédito IBS/CBS — Reforma</span>
                        <span class="whitespace-nowrap inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $crCor }}">{{ $cr['gera_credito'] ?? 'Regime não identificado' }}</span>
                    </div>
                    <dl class="divide-y divide-gray-100">
                        <div class="px-4 py-3">
                            <dt class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Crédito potencial (estimado)</dt>
                            <dd class="text-sm text-gray-700 font-mono mt-0.5">R$&nbsp;{{ number_format($cr['credito_potencial'] ?? 0, 2, ',', '.') }}</dd>
                        </div>
                        <div class="px-4 py-3">
                            <dt class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Crédito em risco</dt>
                            <dd class="text-base font-bold font-mono mt-0.5" style="color: {{ $crCor }}">
                                @if(($cr['credito_em_risco'] ?? null) === null)
                                    —
                                @else
                                    R$&nbsp;{{ number_format($cr['credito_em_risco'], 2, ',', '.') }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                    <div class="px-4 py-3 border-t border-gray-100">
                        <p class="text-[11px] text-gray-400 leading-relaxed">
                            Estimativa do crédito de IBS/CBS que este fornecedor pode <strong>deixar de gerar</strong> para você,
                            pelo <strong>regime tributário</strong> dele e pelo volume de entradas escriturado (EFD). É previsão de
                            risco — não confirma recolhimento. Cenário de <strong>impacto pleno</strong> (vigência total em 2033,
                            alíquota {{ number_format((float) config('reforma.aliquota_referencia') * 100, 1, ',', '.') }}%).
                        </p>
                    </div>
                </div>
            </div>

            {{-- Detalhes do Score --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Detalhamento do Score</span>
                    </div>
                    <div class="p-5">

                    @if($score)
                        @include('autenticado.partials._score-detalhamento', [
                            'detalhamento' => $detalhamento ?? [],
                            'scoreTotal' => $score->score_total,
                            'classificacao' => $score->classificacao,
                            'comHeadline' => false,
                        ])

                        @if($score->ultima_consulta_em)
                        <div class="mt-4 text-[11px] text-gray-500">
                            Última atualização: {{ $score->ultima_consulta_em->format('d/m/Y H:i') }}
                            @if($score->isDesatualizado())
                                <span class="ml-2 font-semibold" style="color: #d97706">(Desatualizado — mais de 30 dias)</span>
                            @endif
                        </div>
                        @endif

                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <h4 class="mt-4 text-sm font-semibold text-gray-900 uppercase tracking-wide">Score não calculado</h4>
                            <p class="mt-2 text-xs text-gray-500">Faça uma Consulta de CNPJ deste participante para calcular o risco. O score é atualizado automaticamente ao final de cada consulta.</p>
                            <a href="/app/consulta" data-link class="mt-4 inline-flex items-center gap-2 px-3 py-2 rounded bg-gray-800 hover:bg-gray-700 text-white text-xs font-semibold transition">Nova consulta</a>
                        </div>
                    @endif

                    </div>
                </div>

                {{-- Certidões e blocos da última consulta — mesmo partial do "Ver detalhes"
                     da Consulta CNPJ (substitui o dump JSON de dados_consultados). --}}
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Última Consulta — Certidões e Cadastro</span>
                    </div>
                    <div class="p-5">
                        @if(!empty($detalheConsultaHtml))
                            {!! $detalheConsultaHtml !!}
                        @else
                            <p class="text-sm text-gray-500">Nenhuma consulta de certidões realizada ainda. <a href="/app/consulta" data-link class="text-gray-700 underline hover:text-gray-900">Consultar agora</a>.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/risk-score.js') }}"></script>
