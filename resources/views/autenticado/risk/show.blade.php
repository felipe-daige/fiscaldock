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
        // BAIXADA gera subscore 100 + piso crítico — badge no vermelho crítico, não cinza apagado.
        'BAIXADA' => ['label' => 'BAIXADA', 'hex' => '#b91c1c'],
        default => $situacaoUpper ? ['label' => $situacaoUpper, 'hex' => '#6b7280'] : null,
    };
    $regimeLabel = trim((string) ($participante->regime_tributario ?? ''));
    $regimeLabel = $regimeLabel !== '' && $regimeLabel !== '—' ? $regimeLabel : 'Não consultado';
    $regimeBadge = ['label' => mb_strtoupper($regimeLabel), 'hex' => \App\Support\Reports\ReportTheme::regimeHex($regimeLabel)];
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
                    <p class="mt-1 text-xs text-gray-500 font-mono">CNPJ: {{ $participante->cnpj_formatado }}</p>
                    <div class="mt-1.5 flex items-center gap-2 flex-wrap">
                        @if($situacaoBadge)
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $situacaoBadge['hex'] }}">{{ $situacaoBadge['label'] }}</span>
                        @endif
                        @if($regimeBadge)
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $regimeBadge['hex'] }}">{{ $regimeBadge['label'] }}</span>
                        @endif
                        @if(!empty($papel))
                            @php
                                $papelHex = match($papel) {
                                    'Fornecedor' => '#2563eb',
                                    'Comprador' => '#059669',
                                    default => '#7c3aed', // Fornecedor e Comprador
                                };
                            @endphp
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $papelHex }}">{{ $papel }}</span>
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
                        {{-- ?participantes= pré-seleciona o CNPJ no painel de consulta (consulta-lote.js) --}}
                        <a href="/app/consulta/painel?participantes={{ $participante->id }}" data-link class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded bg-gray-800 hover:bg-gray-700 text-white text-xs font-semibold transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span class="hidden sm:inline">Atualizar via Consulta</span>
                            <span class="sm:hidden">Consultar</span>
                        </a>
                        <a href="/app/participante/{{ $participante->id }}" data-link class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-semibold transition">
                            Ficha completa
                        </a>
                        {{-- Download: sem data-link (regra SPA — link de arquivo nunca navega no shell) --}}
                        <a href="/app/participante/{{ $participante->id }}/dossie" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-semibold transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Dossiê PDF
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
                            <dd class="text-sm text-gray-700 mt-0.5">
                                <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $regimeBadge['hex'] }}">{{ $regimeBadge['label'] }}</span>
                            </dd>
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
                            <dd class="text-sm text-gray-700 mt-0.5">{{ $origemLabel ?? ($participante->origem_tipo ?? '—') }}</dd>
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
                            @if(!empty($volumePeriodo))
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    Emissões de {{ $volumePeriodo['inicio'] }}{{ $volumePeriodo['fim'] !== $volumePeriodo['inicio'] ? ' a '.$volumePeriodo['fim'] : '' }} (acumulado do acervo importado)
                                </p>
                            @endif
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
                        'verde' => '#047857', 'amarelo' => '#b45309', 'vermelho' => '#b91c1c', 'cinza' => '#9ca3af',
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
                    {{-- Como é calculado — números REAIS deste fornecedor (fonte: CreditoRiscoReformaService) --}}
                    @php
                        $crAliquotaPct = number_format((float) ($cr['aliquota'] ?? config('reforma.aliquota_referencia')) * 100, 1, ',', '.');
                        $crFator = $cr['fator'] ?? null;
                    @endphp
                    <div class="px-4 py-3 border-t" style="background-color: #f8fafc; border-color: #e2e8f0;">
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-2" style="color: #475569;">Como é calculado</p>
                        <div class="space-y-1 text-[11px] leading-relaxed" style="color: #334155;">
                            <p>
                                <span class="font-semibold">1. Crédito potencial</span> = volume de entradas (EFD{{ !empty($volumePeriodo) ? ', emissões '.$volumePeriodo['inicio'].($volumePeriodo['fim'] !== $volumePeriodo['inicio'] ? ' a '.$volumePeriodo['fim'] : '') : '' }}) × alíquota IBS+CBS de referência:
                                <span class="font-mono whitespace-nowrap">R$ {{ number_format($volumeEfd ?? 0, 2, ',', '.') }} × {{ $crAliquotaPct }}% = R$ {{ number_format($cr['credito_potencial'] ?? 0, 2, ',', '.') }}</span>
                            </p>
                            <p>
                                <span class="font-semibold">2. Crédito em risco</span> = potencial × (1 − fator do regime do fornecedor):
                                @if($crFator === null)
                                    <span class="font-mono">regime não identificado — sem estimativa.</span>
                                @else
                                    <span class="font-mono whitespace-nowrap">R$ {{ number_format($cr['credito_potencial'] ?? 0, 2, ',', '.') }} × {{ number_format(1 - $crFator, 2, ',', '.') }} = R$ {{ number_format($cr['credito_em_risco'] ?? 0, 2, ',', '.') }}</span>
                                @endif
                            </p>
                            <p class="pt-1" style="color: #64748b;">
                                Fator por regime: <strong>Normal</strong> (Lucro Real/Presumido) = 1,00 gera crédito integral ·
                                <strong>Simples Nacional</strong> = {{ number_format((float) config('reforma.fator_simples_sem_opcao'), 2, ',', '.') }} crédito parcial ·
                                <strong>MEI</strong> = 0,00 não gera crédito. O regime vem da última Consulta de CNPJ.
                            </p>
                        </div>
                        <p class="mt-2 text-[10px] leading-relaxed" style="color: #94a3b8;">
                            Estimativa de risco — não confirma recolhimento. O valor é o <strong>acumulado do período
                            escriturado no seu acervo</strong> (não uma taxa anual): importou mais SPED, o volume e o
                            risco crescem juntos. Cenário de impacto pleno da Reforma (vigência total em 2033, alíquota
                            {{ number_format((float) config('reforma.aliquota_referencia') * 100, 1, ',', '.') }}%).
                            Detalhe da metodologia: LC 214/2025.
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
                                <span class="ml-2 font-semibold" style="color: #b45309">(Desatualizado — mais de 30 dias)</span>
                            @endif
                        </div>
                        @endif

                        {{-- Metodologia (mesma fonte do PDF: RiskScoreService::metodologia) --}}
                        @if(!empty($metodologia))
                            <details class="mt-4 border border-gray-200 rounded">
                                <summary class="px-3 py-2 text-[11px] font-semibold text-gray-600 uppercase tracking-wide cursor-pointer select-none bg-gray-50 hover:bg-gray-100">Como o risco é classificado</summary>
                                <div class="px-3 py-3 space-y-3 text-[11px] text-gray-600 leading-relaxed border-t border-gray-200">
                                    <div>
                                        <p class="font-semibold text-gray-700 mb-1">1. Fontes, pesos e penalidades</p>
                                        <table class="w-full text-[11px]">
                                            <thead>
                                                <tr class="text-left text-[10px] text-gray-400 uppercase tracking-wide">
                                                    <th class="py-1">Fonte</th><th class="py-1 text-right">Peso</th><th class="py-1 text-right">Se irregular</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($metodologia['categorias'] as $cat)
                                                    <tr>
                                                        <td class="py-1">{{ $cat['label'] }}</td>
                                                        <td class="py-1 text-right font-mono">{{ $cat['peso_pct'] }}%</td>
                                                        <td class="py-1 text-right font-mono">{{ $cat['penalidade'] !== null ? '+'.$cat['penalidade'].' pts' : 'até +100 pts' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <p class="mt-1 text-gray-500">Média ponderada só das fontes avaliadas — fonte não consultada não penaliza. Situação cadastral: ATIVA = 0 · SUSPENSA = 50 · INAPTA/BAIXADA/NULA = 100.</p>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700 mb-1">2. Piso por irregularidade conhecida</p>
                                        <p class="text-gray-500 mb-1">Débito ou situação irregular conhecida nunca fica "Baixo Risco" — a classificação mínima vence a média:</p>
                                        <ul class="space-y-0.5">
                                            @foreach($metodologia['pisos'] as $p)
                                                <li>{{ $p['fonte'] }} → mínimo <strong>{{ $p['piso'] }}</strong></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <p class="text-gray-500"><strong>Cobertura mínima:</strong> {{ $metodologia['cobertura'] }}</p>
                                </div>
                            </details>
                        @endif

                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <h4 class="mt-4 text-sm font-semibold text-gray-900 uppercase tracking-wide">Score não calculado</h4>
                            <p class="mt-2 text-xs text-gray-500">Faça uma Consulta de CNPJ deste participante para calcular o risco. O score é atualizado automaticamente ao final de cada consulta.</p>
                            <a href="/app/consulta/painel" data-link class="mt-4 inline-flex items-center gap-2 px-3 py-2 rounded bg-gray-800 hover:bg-gray-700 text-white text-xs font-semibold transition">Nova consulta</a>
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
                            <p class="text-sm text-gray-500">Nenhuma consulta de certidões realizada ainda. <a href="/app/consulta/painel" data-link class="text-gray-700 underline hover:text-gray-900">Consultar agora</a>.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/risk-score.js') }}"></script>
