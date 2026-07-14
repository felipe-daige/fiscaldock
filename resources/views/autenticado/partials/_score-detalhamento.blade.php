@php
    $comHeadline = $comHeadline ?? true;
    $detalhamento = $detalhamento ?? [];
    $scoreTotal = $scoreTotal ?? null;
    $classificacao = $classificacao ?? 'nao_avaliado';
    $isCpf = $isCpf ?? false;
    $temAvaliada = collect($detalhamento)->contains(fn ($l) => $l['avaliado'] ?? false);
    $headlineHex = \App\Services\RiskScoreService::hexSubscore($scoreTotal);
    $classLabel = app(\App\Services\RiskScoreService::class)->getLabelClassificacao($classificacao);
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
        <div class="flex items-center gap-4 mb-4 pb-4 border-b border-gray-200" data-test="score-headline-total">
            <div class="text-3xl font-bold font-mono" style="color: {{ $headlineHex }}">{{ $scoreTotal ?? '—' }}</div>
            <span class="whitespace-nowrap inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $headlineHex }}">{{ $classLabel }}</span>
        </div>
    @endif

    <p class="text-[11px] text-gray-500 mb-4">
        O score total pondera apenas as categorias efetivamente avaliadas nesta consulta —
        categorias não consultadas ou indeterminadas não entram no cálculo.
    </p>

    <div class="space-y-4">
        @foreach($detalhamento as $item)
            @php
                $avaliado = $item['avaliado'] ?? false;
                $score = (int) ($item['score'] ?? 0);
                $regular = $avaliado && $score === 0;
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
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] text-gray-500 uppercase tracking-wide">Peso: {{ $item['peso_pct'] }}%</span>
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
            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded" style="background-color: #ea580c"></div><span class="text-gray-600">51-80: Alto Risco</span></div>
            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded" style="background-color: #b91c1c"></div><span class="text-gray-600">81-100: Crítico</span></div>
        </div>
    </div>
@endif
