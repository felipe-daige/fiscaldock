{{-- Card de Certidão — Minimalista com border-t colorida --}}
@php
    $consultado = $dados['consultado'] ?? false;
    $status = strtoupper($dados['status'] ?? '');
    $validade = $dados['validade'] ?? null;

    $cor = 'gray';
    $statusLabel = 'Não consultado';

    if ($consultado && !empty($status)) {
        if (in_array($status, ['NEGATIVA', 'REGULAR', 'REGULARIDADE'])) {
            $cor = 'green';
            $statusLabel = 'Negativa';
        } elseif (str_contains($status, 'POSITIVA COM EFEITO') || str_contains($status, 'EFEITO DE NEGATIVA')) {
            $cor = 'yellow';
            $statusLabel = 'Positiva c/ Efeito';
        } elseif (in_array($status, ['POSITIVA', 'IRREGULAR', 'IRREGULARIDADE'])) {
            $cor = 'red';
            $statusLabel = 'Positiva';
        } else {
            $cor = 'blue';
            $statusLabel = $status;
        }
    }

    $diasRestantes = null;
    if ($validade) {
        try {
            $dataValidade = \Carbon\Carbon::parse($validade);
            $diasRestantes = now()->diffInDays($dataValidade, false);
        } catch (\Exception $e) {
            $diasRestantes = null;
        }
    }
@endphp

<div class="bg-white rounded-lg border border-gray-100 border-t-2 border-t-{{ $cor }}-500 p-5">
    <span class="text-xs uppercase tracking-wide text-gray-400 font-semibold">{{ $nome }}</span>
    <div class="mt-2">
        @if($consultado)
            <p class="text-lg font-bold text-{{ $cor }}-600">{{ $statusLabel }}</p>
            @if($validade && $diasRestantes !== null)
                <p class="text-xs text-gray-400 mt-1">
                    @if($diasRestantes <= 0)
                        <span class="text-red-600 font-medium">Vencida</span>
                    @elseif($diasRestantes <= 7)
                        <span class="text-yellow-600 font-medium">Vence em {{ $diasRestantes }} dias</span>
                    @else
                        Val: {{ \Carbon\Carbon::parse($validade)->format('d/m/Y') }}
                    @endif
                </p>
            @endif
        @else
            <p class="text-lg font-semibold text-gray-300">-</p>
            <p class="text-xs text-gray-400 mt-1">Não consultado</p>
        @endif
    </div>
</div>
