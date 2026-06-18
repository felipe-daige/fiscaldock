@php
    $pendentes = collect($triagem)->filter(fn ($i) => ($i['count'] ?? 0) > 0)->count();
    $total = count($triagem);
@endphp

@if($pendentes === 0)
    {{-- Estado limpo: carteira em dia. Preenche a altura do card com calma em vez de 4 zeros. --}}
    <div class="flex-1 flex flex-col items-center justify-center text-center px-4 py-10">
        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full mb-3" style="background-color: #dcfce7">
            <svg class="w-5 h-5" style="color: #16a34a" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </span>
        <p class="text-sm font-semibold text-gray-700">Carteira em dia</p>
        <p class="text-xs text-gray-500 mt-0.5">Nenhuma pendência aberta</p>
    </div>
@else
    <div class="divide-y divide-gray-100">
        @foreach($triagem as $item)
            @if(($item['count'] ?? 0) > 0)
                {{-- Acionável: barra de acento + pílula na cor da severidade (hex inline; nunca classe Tailwind de bg). --}}
                <a href="{{ $item['url'] }}" data-link class="group relative flex items-center justify-between gap-3 pl-5 pr-4 py-3 hover:bg-gray-50 transition-colors">
                    <span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-r" style="background-color: {{ $item['hex'] }}"></span>
                    <span class="min-w-0 text-sm text-gray-800 truncate">{{ $item['label'] }}</span>
                    <span class="flex-shrink-0 flex items-center gap-1.5">
                        <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 rounded-full text-[11px] font-bold text-white" style="background-color: {{ $item['hex'] }}">{{ number_format($item['count'], 0, ',', '.') }}</span>
                        <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                </a>
            @else
                {{-- Resolvido: apagado e calmo. --}}
                <a href="{{ $item['url'] }}" data-link class="flex items-center justify-between gap-3 pl-5 pr-4 py-3 hover:bg-gray-50/50 transition-colors">
                    <span class="min-w-0 flex items-center gap-2 text-sm text-gray-400">
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background-color: #d1d5db"></span>
                        <span class="truncate">{{ $item['label'] }}</span>
                    </span>
                    <span class="text-sm font-semibold text-gray-300 flex-shrink-0">{{ number_format($item['count'], 0, ',', '.') }}</span>
                </a>
            @endif
        @endforeach
    </div>
    {{-- Rodapé ancorado: resumo + catch-all. mt-auto empurra pra base e preenche o vazio com propósito. --}}
    <div class="mt-auto border-t border-gray-200 px-4 py-2.5 flex items-center justify-between gap-2">
        <span class="text-[11px] text-gray-500">{{ $pendentes }} de {{ $total }} {{ $pendentes === 1 ? 'pede' : 'pedem' }} ação</span>
        <a href="/app/alertas" data-link class="text-[11px] font-semibold text-gray-600 hover:text-gray-900 inline-flex items-center gap-1">
            Central de alertas
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>
@endif
