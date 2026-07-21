@php
    $comHeadline = $comHeadline ?? true;
    $detalhamento = $detalhamento ?? [];
    $scoreTotal = $scoreTotal ?? null;
    $classificacao = $classificacao ?? 'nao_avaliado';
    $isCpf = $isCpf ?? false;
    $temAvaliada = collect($detalhamento)->contains(fn ($l) => $l['avaliado'] ?? false);
    $riskScoreService = app(\App\Services\RiskScoreService::class);
    // A cor representa a classificação FINAL (incluindo piso por irregularidade), não apenas
    // a faixa numérica. Caso real: 15 pontos + CND Estadual positiva = Alto Risco vermelho.
    $headlineHex = $riskScoreService->getCorClassificacao($classificacao);
    $classLabel = $riskScoreService->getLabelClassificacao($classificacao);
    $classificacaoNumerica = $riskScoreService->classificar($scoreTotal);
    $pisoAplicado = $scoreTotal !== null
        && ! in_array($classificacao, ['nao_avaliado', 'inconclusivo'], true)
        && $classificacao !== $classificacaoNumerica;
    $inconclusivo = $classificacao === 'inconclusivo';
    $scoreExibido = $inconclusivo ? '—' : ($scoreTotal ?? '—');
    $larguraTotal = $scoreTotal === 0 && $classificacao === 'baixo'
        ? 100
        : max(0, min(100, (int) ($scoreTotal ?? 0)));
    $fontesIrregulares = collect($detalhamento)
        ->filter(fn ($linha) => ($linha['avaliado'] ?? false) && (int) ($linha['score'] ?? 0) > 0)
        ->pluck('label')
        ->filter()
        ->values();
    $fmtDecimal = fn ($valor) => number_format((float) $valor, 1, ',', '.');
@endphp

@if($scoreTotal === null && ! $temAvaliada)
    <div class="text-center py-8">
        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">{{ $isCpf ? 'Risco de crédito não avaliado' : 'Score não calculado' }}</h4>
        @if($isCpf)
            <p class="mt-2 text-xs text-gray-500 max-w-2xl mx-auto">{{ $mensagemCpf ?? \App\Services\Risk\RiscoCreditoCpfService::MENSAGEM_NAO_AVALIADO }}</p>
        @else
            <p class="mt-2 text-xs text-gray-500">Faça uma Consulta de CNPJ deste CNPJ para calcular o risco fiscal.</p>
            <a href="/app/consulta/painel" data-link class="mt-4 inline-flex items-center gap-2 px-3 py-2 rounded bg-gray-800 hover:bg-gray-700 text-white text-xs font-semibold transition">Nova consulta</a>
        @endif
    </div>
@else
    @if($comHeadline)
        <div class="mb-4 space-y-3 border-b border-gray-200 pb-4" data-test="score-headline-total">
            <div class="flex flex-wrap items-center gap-3">
                <div class="text-3xl font-bold font-mono" style="color: {{ $headlineHex }}">{{ $scoreExibido }}</div>
                @if(! $inconclusivo && $scoreTotal !== null)
                    <span class="text-[11px] font-medium text-gray-500">pontos ponderados</span>
                @endif
                <span class="whitespace-nowrap inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $headlineHex }}">{{ $classLabel }}</span>
            </div>

            @if(! $inconclusivo && $scoreTotal !== null)
                <div>
                    <div class="h-2 w-full overflow-hidden rounded bg-gray-200" data-test="score-total-bar">
                        <div class="h-2 rounded" style="width: {{ $larguraTotal }}%; background-color: {{ $headlineHex }}"></div>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">A largura representa a nota ponderada; a cor segue a classificação final.</p>
                </div>
            @endif

            @if($pisoAplicado)
                <div class="rounded border px-3 py-2 text-[11px] leading-relaxed" style="border-color: #fecaca; background-color: #fef2f2; color: #991b1b" data-test="score-piso-aplicado">
                    <strong>Classificação elevada por irregularidade conhecida.</strong>
                    A média resulta em {{ $scoreTotal }} pontos, mas {{ $fontesIrregulares->isNotEmpty() ? $fontesIrregulares->implode(', ') : 'uma fonte irregular' }} impõe{{ $fontesIrregulares->count() === 1 ? '' : 'm' }} o piso de {{ $classLabel }}.
                </div>
            @elseif($inconclusivo)
                <p class="text-[11px] text-gray-500">Cobertura insuficiente: o resultado exige CND Federal e ao menos duas certidões avaliadas.</p>
            @endif
        </div>
    @endif

    <p class="text-[11px] text-gray-500 mb-4">
        O peso efetivo é o peso-base renormalizado apenas entre as fontes avaliadas e soma 100%.
        Fontes não consultadas ou indeterminadas ficam fora do cálculo.
    </p>

    <div class="space-y-4">
        @foreach($detalhamento as $item)
            @php
                $avaliado = $item['avaliado'] ?? false;
                $score = (int) ($item['score'] ?? 0);
                $regular = $avaliado && $score === 0;
                $pesoBase = $item['peso_base_pct'] ?? $item['peso_pct'] ?? 0;
                $pesoEfetivo = $item['peso_efetivo_pct'] ?? ($avaliado ? ($item['peso_pct'] ?? null) : null);
                $contribuicao = $item['contribuicao_pontos'] ?? (
                    $pesoEfetivo !== null ? round($score * ((float) $pesoEfetivo / 100), 1) : null
                );
                // Barra = intensidade do estado, MESMA fórmula do PDF
                // (reports/partials/_score-detalhamento): regular (0) enche de verde;
                // irregular enche PELO RISCO (suspensa 50 = meia barra, baixada 100 = cheia
                // vermelha). A cor desambigua verde×vermelho. Fórmula anterior (100 − score)
                // deixava o pior caso (100) com barra VAZIA — lia-se como "sem dado".
                $largura = $regular ? 100 : max(0, min(100, $score));
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ $item['label'] }}</span>
                    <div class="flex flex-wrap items-center justify-end gap-x-2 gap-y-1 text-right">
                        @if($avaliado)
                            <span class="text-[10px] text-gray-500 uppercase tracking-wide" data-test="score-peso-efetivo">Peso efetivo: {{ $fmtDecimal($pesoEfetivo) }}%</span>
                            <span class="text-[10px] text-gray-400" title="Peso configurado antes da renormalização">base {{ $fmtDecimal($pesoBase) }}%</span>
                            <span class="text-[10px] font-semibold" style="color: {{ $item['hex'] }}" data-test="score-contribuicao">{{ $fmtDecimal($contribuicao) }} pt</span>
                        @else
                            <span class="text-[10px] text-gray-400 uppercase tracking-wide">Peso-base: {{ $fmtDecimal($pesoBase) }}% · fora do cálculo</span>
                        @endif
                        @if($regular)
                            <span class="text-[11px] font-bold uppercase tracking-wide" style="color: {{ $item['hex'] }}">Regular</span>
                        @elseif($avaliado)
                            <span class="text-sm font-bold font-mono" style="color: {{ $item['hex'] }}">{{ $item['score'] }}</span>
                        @else
                            <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Não avaliado</span>
                        @endif
                    </div>
                </div>
                <div class="w-full rounded h-2 {{ $avaliado ? 'bg-gray-200' : 'bg-gray-100 border border-dashed border-gray-300' }}">
                    @if($avaliado)
                        <div class="h-2 rounded" style="width: {{ $largura }}%; background-color: {{ $item['hex'] }}"></div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Legenda --}}
    <div class="mt-6 pt-4 border-t border-gray-200">
        <h4 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-3">Legenda dos Scores</h4>
        <div class="flex flex-wrap gap-4 text-xs">
            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded" style="background-color: #047857"></div><span class="text-gray-600">0-20: Baixo Risco</span></div>
            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded" style="background-color: #b45309"></div><span class="text-gray-600">21-50: Médio Risco</span></div>
            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded" style="background-color: #dc2626"></div><span class="text-gray-600">51-80: Alto Risco</span></div>
            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded" style="background-color: #b91c1c"></div><span class="text-gray-600">81-100: Crítico</span></div>
        </div>
    </div>
@endif
