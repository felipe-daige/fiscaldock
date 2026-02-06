{{-- Card de Certidao Reutilizavel --}}
@php
    $consultado = $dados['consultado'] ?? false;
    $status = strtoupper($dados['status'] ?? '');
    $validade = $dados['validade'] ?? null;

    // Determinar cor baseado no status
    $cor = 'gray';
    $statusLabel = 'Nao consultado';

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

    // Verificar validade
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

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <span class="text-sm font-medium text-gray-500">{{ $nome }}</span>
        @if($consultado)
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $cor }}-100 text-{{ $cor }}-800">
                {{ $statusLabel }}
            </span>
        @endif
    </div>

    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-{{ $cor }}-100 flex items-center justify-center">
            @if($icone === 'shield-check')
                <svg class="w-5 h-5 text-{{ $cor }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            @elseif($icone === 'map')
                <svg class="w-5 h-5 text-{{ $cor }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
            @elseif($icone === 'users')
                <svg class="w-5 h-5 text-{{ $cor }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            @elseif($icone === 'briefcase')
                <svg class="w-5 h-5 text-{{ $cor }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            @else
                <svg class="w-5 h-5 text-{{ $cor }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            @endif
        </div>
        <div class="min-w-0 flex-1">
            @if($consultado)
                <p class="text-lg font-semibold text-{{ $cor }}-600 truncate">{{ $statusLabel }}</p>
                @if($validade && $diasRestantes !== null)
                    <p class="text-xs text-gray-500">
                        @if($diasRestantes <= 0)
                            <span class="text-red-600">Vencida</span>
                        @elseif($diasRestantes <= 7)
                            <span class="text-yellow-600">Vence em {{ $diasRestantes }} dias</span>
                        @else
                            Val: {{ \Carbon\Carbon::parse($validade)->format('d/m/Y') }}
                        @endif
                    </p>
                @endif
            @else
                <p class="text-lg font-semibold text-gray-400">-</p>
                <p class="text-xs text-gray-400">Nao consultado</p>
            @endif
        </div>
    </div>
</div>
